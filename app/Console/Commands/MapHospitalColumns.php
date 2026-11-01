<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

#[Signature('wccms:map-hospital-columns {--dry-run : List what would change without writing any file}')]
#[Description('Rename the hospital patient/employee columns throughout the code to the names in config/hospital.php')]
class MapHospitalColumns extends Command
{
    /**
     * Folders whose PHP and Blade files reference the hospital columns. config/ is left out on purpose:
     * config/hospital.php holds the map itself.
     */
    private const SCAN_PATHS = ['app', 'resources/views', 'routes', 'database', 'tests'];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $map = $this->columnMap();

        if ($map === null) {
            return self::FAILURE;
        }

        if ($map === []) {
            $this->info('config/hospital.php has no column to rename; nothing to do.');

            return self::SUCCESS;
        }

        $this->table(['WCCMS column', 'Hospital column'], collect($map)->map(fn ($to, $from) => [$from, $to])->values());

        $patterns = collect($map)->mapWithKeys(fn (string $to, string $from): array => [
            '/(?<![A-Za-z0-9_])'.preg_quote($from, '/').'(?![A-Za-z0-9_])/' => $to,
        ]);

        $changedFiles = 0;
        $replacements = 0;

        foreach ($this->files() as $file) {
            $contents = $file->getContents();
            $count = 0;
            $updated = preg_replace($patterns->keys()->all(), $patterns->values()->all(), $contents, -1, $count);

            if ($count === 0) {
                continue;
            }

            $changedFiles++;
            $replacements += $count;
            $this->line(sprintf('  %4d  %s', $count, Str::after($file->getPathname(), base_path().DIRECTORY_SEPARATOR)));

            if (! $this->option('dry-run')) {
                File::put($file->getPathname(), $updated);
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%s %d replacement(s) in %d file(s).',
            $this->option('dry-run') ? 'Would make' : 'Made',
            $replacements,
            $changedFiles
        ));

        return self::SUCCESS;
    }

    /**
     * The non-empty entries of config('hospital.columns'), upper-cased (Oracle returns unquoted names in upper case).
     *
     * @return array<string, string>|null null when the map is invalid
     */
    private function columnMap(): ?array
    {
        $map = collect(config('hospital.columns', []))
            ->filter(fn ($to): bool => is_string($to) && trim($to) !== '')
            ->map(fn (string $to): string => strtoupper(trim($to)))
            ->reject(fn (string $to, string $from): bool => $to === $from);

        $invalid = $map->reject(fn (string $to): bool => (bool) preg_match('/^[A-Z][A-Z0-9_$#]{0,127}$/', $to));

        if ($invalid->isNotEmpty()) {
            $this->error('Invalid Oracle column name(s): '.$invalid->implode(', '));

            return null;
        }

        $duplicates = $map->duplicates();

        if ($duplicates->isNotEmpty()) {
            $this->error('Two WCCMS columns cannot map to the same hospital column: '.$duplicates->unique()->implode(', '));
            $this->line('For a column the hospital does not have, leave it null (see MIGRATION_GUIDE.md).');

            return null;
        }

        $clashes = $map->filter(fn (string $to): bool => array_key_exists($to, config('hospital.columns')));

        if ($clashes->isNotEmpty()) {
            $this->error('A target name is itself a WCCMS column name: '.$clashes->implode(', '));

            return null;
        }

        return $map->all();
    }

    /**
     * @return iterable<SplFileInfo>
     */
    private function files(): iterable
    {
        foreach (self::SCAN_PATHS as $path) {
            foreach (File::allFiles(base_path($path)) as $file) {
                if ($file->getExtension() === 'php') {
                    yield $file;
                }
            }
        }
    }
}
