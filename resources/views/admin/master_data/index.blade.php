@extends('layouts.app')

@section('title', 'إدارة البيانات المرجعية - ' . $domainTitle)

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-sliders text-primary me-2"></i>إدارة البيانات المرجعية الأساسية
            </h3>
            <p class="text-muted small mb-0">إدارة ومراجعة القوائم المرجعية الجداول الأساسية بالنظام (تفعيل/تعطيل دون حذف)</p>
        </div>
        <div class="mt-3 mt-md-0">
            <button type="button" class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#createItemModal">
                <i class="bi bi-plus-lg me-1"></i>إضافة {{ $domainTitle }} جديد
            </button>
        </div>
    </div>

    {{-- Domain Navigation Tabs --}}
    @include('admin.master_data.partials.domain-nav')

    {{-- Session Flash Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i><strong>يرجى تصحيح الأخطاء التالية:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    {{-- Main Content Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            {{-- Filter and Search Bar --}}
            <form action="{{ route($indexRouteName) }}" method="GET" class="mb-4">
                <div class="row g-2 align-items-end">
                    <div class="col-md-6 col-lg-5">
                        <label class="form-label small fw-bold">البحث في {{ $domainTitle }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="ابحث باسم العنصر المرجعي..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3 col-lg-3">
                        <label class="form-label small fw-bold">تصفية حسب الحالة</label>
                        <select name="status" class="form-select">
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>جميع الحالات</option>
                            <option value="1" {{ request('status') === '1' || !request()->has('status') ? 'selected' : '' }}>العناصر النشطة فقط</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>العناصر غير النشطة فقط</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-lg-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-filter me-1"></i>تصفية
                        </button>
                        <a href="{{ route($indexRouteName) }}" class="btn btn-outline-secondary" title="إعادة ضبط">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>

            {{-- Table List --}}
            @include('admin.master_data.partials.item-table')

        </div>
    </div>
</div>

{{-- Modals --}}
@include('admin.master_data.partials.item-modal')
@endsection
