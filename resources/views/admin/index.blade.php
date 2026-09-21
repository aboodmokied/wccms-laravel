@extends('layouts.app')

@section('title', 'إدارة البيانات المرجعية')

@push('styles')
    @vite(['resources/css/pages/admin.css'])
@endpush

@push('scripts')
    @vite(['resources/js/pages/admin.js'])
@endpush

@section('content')
<x-card title="إدارة البيانات المرجعية للعيادة" icon="bi-gear" subtitle="إدارة القوائم المرجعية: الضمادات، العلاجات، الإجراءات التمريضية، والتشخيصات">
    <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold" id="dressings-tab" data-bs-toggle="tab" data-bs-target="#dressings">الضمادات الطبية</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="medications-tab" data-bs-toggle="tab" data-bs-target="#medications">العلاجات الموضعية</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="procedures-tab" data-bs-toggle="tab" data-bs-target="#procedures">الإجراءات التمريضية</button>
        </li>
    </ul>

    <div class="tab-content" id="adminTabsContent">
        <div class="tab-pane fade show active" id="dressings" role="tabpanel">
            <x-data-table :headers="['كود الضمادة', 'اسم الضمادة', 'الحالة', 'الإجراءات']">
                <tr>
                    <td class="fw-bold">DRS-001</td>
                    <td>رغوية (Foam)</td>
                    <td><span class="badge bg-success">مفعل</span></td>
                    <td><button type="button" class="btn btn-sm btn-outline-secondary">{{ __('ui.buttons.edit') }}</button></td>
                </tr>
            </x-data-table>
        </div>
    </div>
</x-card>
@endsection
