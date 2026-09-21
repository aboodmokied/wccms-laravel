@extends('layouts.app')

@section('title', 'جدول المواعيد والمراجعات')

@push('styles')
    @vite(['resources/css/pages/reviews.css'])
@endpush

@section('content')
<x-card title="جدول المواعيد والزيارات المجدولة" icon="bi-calendar-check" subtitle="إدارة مواعيد المتابعة التمريضية وإبطال/إلغاء المواعيد المعلقة">
    <x-search-filter placeholder="ابحث بتاريخ الموعد، اسم المريض..." />

    <x-data-table :headers="['كود الموعد', 'اسم المريض', 'تاريخ الموعد المجدول', 'الحالة', 'الإجراءات']">
        <tr>
            <td class="fw-bold">SCH-2026-501</td>
            <td>عبدالرحمن يوسف القحطاني</td>
            <td>2026-09-22</td>
            <td><x-status-badge status="PENDING" /></td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger">{{ __('ui.buttons.cancel') }}</button>
            </td>
        </tr>
    </x-data-table>
</x-card>
@endsection
