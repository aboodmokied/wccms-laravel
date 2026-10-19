{{--
    Case table in workbook column order (FR-004).
    $rows: list of ['case' => CaseRow, 'previousRegisteredAt' => ?Carbon, 'previousOutcome' => ?CaseOutcome].
    $withPrevious: show the previous-registration columns (readmissions).
--}}
@use('App\Services\Reports\CaseOutcome')
@use('App\Services\Reports\ReportFormat')
@php
    $outcomeClass = fn (CaseOutcome $outcome): string => match ($outcome) {
        CaseOutcome::Ongoing => 'bg-primary-subtle text-primary',
        CaseOutcome::Discharged => 'bg-success-subtle text-success',
        CaseOutcome::Death => 'bg-dark-subtle text-dark',
        CaseOutcome::HospitalTransfer => 'bg-warning-subtle text-warning-emphasis',
    };
@endphp

<div class="table-responsive">
    <table class="table table-hover align-middle m8-report-table mb-0">
        <thead>
            <tr>
                @foreach([...ReportFormat::CASE_COLUMNS, ...($withPrevious ? ReportFormat::PREVIOUS_COLUMNS : [])] as $column)
                    <th scope="col">{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                @php($case = $row['case'])
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $case->patientName }}</td>
                    <td>{{ ReportFormat::month($case->month()) }}</td>
                    <td>{{ $case->idNumber }}</td>
                    <td>{{ $case->gender }}</td>
                    <td>{{ $case->birthYear }}</td>
                    <td class="m8-report-wrap">{{ ReportFormat::list($case->diagnoses) }}</td>
                    <td class="m8-report-wrap">{{ ReportFormat::list($case->woundLocations) }}</td>
                    <td>{{ ReportFormat::date($case->hospitalAdmissionDate) }}</td>
                    <td class="m8-report-wrap">{{ ReportFormat::list($case->pastMedicalHistory) }}</td>
                    <td class="m8-report-wrap">{{ ReportFormat::list($case->woundTypes) }}</td>
                    <td class="m8-report-wrap">{{ ReportFormat::list($case->dressings) }}</td>
                    <td class="m8-report-wrap">{{ ReportFormat::list($case->medications) }}</td>
                    <td class="m8-report-wrap">{{ ReportFormat::list($case->procedures) }}</td>
                    <td>{{ $case->department }}</td>
                    <td>{{ $case->photoCount }}</td>
                    <td>{{ ReportFormat::date($case->dischargeDate) }}</td>
                    <td><span class="badge rounded-pill {{ $outcomeClass($case->outcome) }}">{{ $case->outcome->label() }}</span></td>
                    @if($withPrevious)
                        <td>{{ ReportFormat::date($row['previousRegisteredAt']) }}</td>
                        <td>{{ $row['previousOutcome']?->label() }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
