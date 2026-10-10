@php
    $genderLabel = match ($selectedPatient->MR_PAT_GENDER) {
        'MALE', 'M' => 'ذكر',
        'FEMALE', 'F' => 'أنثى',
        default => 'غير محدد',
    };
    $patientAge = $selectedPatient->MR_PAT_DOB
        ? \Illuminate\Support\Carbon::parse($selectedPatient->MR_PAT_DOB)->age
        : null;
@endphp

<section class="patient-demographics" aria-labelledby="patient-demographics-name">
    <div class="patient-demographics-avatar" aria-hidden="true">
        <i class="bi bi-person-fill"></i>
    </div>

    <div class="patient-demographics-content">
        <p class="patient-record-eyebrow mb-1">بيانات المريض</p>
        <h2 class="patient-demographics-name" id="patient-demographics-name">{{ $selectedPatient->MR_PAT_FULL_NAME }}</h2>

        <dl class="patient-demographics-grid">
            <div>
                <dt><i class="bi bi-person-vcard" aria-hidden="true"></i>رقم الهوية</dt>
                <dd dir="ltr">{{ $selectedPatient->MR_PAT_ID_NO ?: 'غير متوفر' }}</dd>
            </div>
            <div>
                <dt><i class="bi bi-folder2" aria-hidden="true"></i>رقم الملف</dt>
                <dd dir="ltr">{{ $selectedPatient->MR_PAT_CODE }}</dd>
            </div>
            <div>
                <dt><i class="bi bi-gender-ambiguous" aria-hidden="true"></i>الجنس</dt>
                <dd>{{ $genderLabel }}</dd>
            </div>
            <div>
                <dt><i class="bi bi-calendar3" aria-hidden="true"></i>العمر</dt>
                <dd>{{ $patientAge !== null ? $patientAge . ' سنة' : 'غير محدد' }}</dd>
            </div>
            <div>
                <dt><i class="bi bi-telephone" aria-hidden="true"></i>الهاتف</dt>
                <dd dir="ltr">{{ $selectedPatient->MR_PAT_PHONE ?: 'غير متوفر' }}</dd>
            </div>
        </dl>
    </div>
</section>
