<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WoundPhotoStorage
{
    /** @param array<int, UploadedFile>|null $files
     * @return list<array{code: string, path: string, caption: string, taken_at: string}>
     */
    public function persist(?array $files, string $caption): array
    {
        $photos = [];

        try {
            foreach ($files ?? [] as $file) {
                $code = (string) Str::uuid();
                $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $directory = 'wound_photos/'.now()->format('Y/m');
                $path = Storage::disk('public')->putFileAs($directory, $file, "{$code}.{$extension}");

                if (! is_string($path) || ! Storage::disk('public')->exists($path)) {
                    throw new \RuntimeException('تعذر حفظ صورة الجرح.');
                }

                $photos[] = [
                    'code' => $code,
                    'path' => $path,
                    'caption' => $caption,
                    'taken_at' => now()->toDateTimeString(),
                ];
            }
        } catch (\Throwable $exception) {
            $this->cleanup($photos);

            throw $exception;
        }

        return $photos;
    }

    /** @param list<array{path: string}> $photos */
    public function cleanup(array $photos): void
    {
        Storage::disk('public')->delete(array_column($photos, 'path'));
    }
}
