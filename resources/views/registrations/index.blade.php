@extends('layouts.app')

@section('title', 'المرضى المسجلون حالياً')

@push('styles')
    @vite(['resources/css/pages/registrations.css'])
@endpush

@section('content')
<x-card title="المرضى المسجلون بالمركز" icon="bi-people" subtitle="إدارة سجلات المرضى النشطين وتخريج الحالات">
    <x-search-filter placeholder="ابحث برقم التسجيل، اسم المريض..." />

    <x-data-table :headers="['كود التسجيل', 'اسم المريض', 'القسم الطبي', 'الممرض المسجل', 'حالة الملف', 'تاريخ التسجيل', 'الإجراءات']">
        <tr>
            <td class="fw-bold">REG-2026-101</td>
            <td>فاطمة سعد الغامدي</td>
            <td>الباطنية (Medical)</td>
            <td>أ. فاطمة الزهراني</td>
            <td><x-status-badge status="ACTIVE" /></td>
            <td>2026-09-01</td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-success">{{ __('ui.buttons.view_details') }}</button>
            </td>
        </tr>
    </x-data-table>
</x-card>
@endsection
