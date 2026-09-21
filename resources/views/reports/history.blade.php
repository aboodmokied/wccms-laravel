@extends('layouts.app')

@section('title', 'سجل المريض والتقارير')

@section('content')
<x-card title="سجل المريض والتقارير التجميعية" icon="bi-file-earmark-bar-graph" subtitle="عرض التاريخ السريري الشامل وتتبع التئام الجروح بالتقارير البيانية">
    <x-search-filter placeholder="ابحث برقم المريض السريري..." />

    <x-card title="ملخص تاريخ المريض" icon="bi-person-lines-fill">
        <p class="text-muted">اختر مريضاً من القائمة لعرض السجل التقييمي والتاريخ السريري الكامل.</p>
    </x-card>
</x-card>
@endsection
