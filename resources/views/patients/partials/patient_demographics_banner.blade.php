@php
    $genderLabel = $selectedPatient->sex_label ?: 'غير معروف';
    $patientAge = $selectedPatient->MRP_DOB
        ? \Illuminate\Support\Carbon::parse($selectedPatient->MRP_DOB)->age
        : null;
@endphp

<section class="patient-demographics" aria-labelledby="patient-demographics-name">
    <div class="patient-demographics-avatar" aria-hidden="true">
        <i class="bi bi-person-fill"></i>
    </div>

    <div class="patient-demographics-content">
        <p class="patient-record-eyebrow mb-1">بيانات المريض</p>
        <h2 class="patient-demographics-name" id="patient-demographics-name">{{ $selectedPatient->full_name }}</h2>

        <dl class="patient-demographics-grid">
            <div>
                <dt><i class="bi bi-person-vcard" aria-hidden="true"></i>رقم الهوية</dt>
                <dd dir="ltr">{{ $selectedPatient->MRP_ID ?: 'غير متوفر' }}</dd>
            </div>
            <div>
                <dt><i class="bi bi-folder2" aria-hidden="true"></i>رقم الملف</dt>
                <dd dir="ltr">{{ $selectedPatient->MRP_PATREC_CODE }}</dd>
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
                <dd dir="ltr">{{ $selectedPatient->MRP_MOBILE_NO ?: 'غير متوفر' }}</dd>
            </div>
        </dl>
    </div>
</section>
