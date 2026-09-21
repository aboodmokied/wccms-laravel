@extends('layouts.app')

@section('title', 'طلبات التسجيل')

@push('styles')
    @vite(['resources/css/pages/requests.css'])
@endpush

@push('scripts')
    @vite(['resources/js/pages/requests.js'])
@endpush

@section('content')
<x-card title="قائمة طلبات التسجيل الجديدة" icon="bi-file-earmark-plus" subtitle="مراجعة وتدقيق طلبات الإحالة من الأقسام الطبية">
    <x-search-filter placeholder="ابحث برقم الطلب، اسم المريض، أو الهوية..." />

    <x-data-table :headers="['كود الطلب', 'اسم المريض', 'رقم المستشفى', 'القسم المحول', 'الحالة', 'تاريخ الطلب', 'الإجراءات']">
        <tr>
            <td class="fw-bold">REQ-2026-001</td>
            <td>أحمد محمد علي</td>
            <td>HP-10025</td>
            <td>العناية المركزة (ICU)</td>
            <td><x-status-badge status="PENDING" /></td>
            <td>2026-09-20</td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-primary">{{ __('ui.buttons.view_details') }}</button>
            </td>
        </tr>
    </x-data-table>
</x-card>
@endsection
