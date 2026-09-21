@extends('layouts.app')

@section('title', 'الرئيسية - لوحة التحكم')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <x-card class="bg-primary text-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-white-50 small mb-1">طلبات التسجيل المعلقة</h6>
                    <h3 class="fw-bold mb-0 text-white">3</h3>
                </div>
                <i class="bi bi-file-earmark-text fs-1 opacity-50"></i>
            </div>
        </x-card>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-card class="bg-success text-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-white-50 small mb-1">المرضى المسجلون حالياً</h6>
                    <h3 class="fw-bold mb-0 text-white">17</h3>
                </div>
                <i class="bi bi-people fs-1 opacity-50"></i>
            </div>
        </x-card>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-card class="bg-warning text-dark">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-dark-50 small mb-1">مراجعات اليوم</h6>
                    <h3 class="fw-bold mb-0 text-dark">5</h3>
                </div>
                <i class="bi bi-calendar-event fs-1 opacity-50"></i>
            </div>
        </x-card>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-card class="bg-info text-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-white-50 small mb-1">إجمالي التقييمات التمريضية</h6>
                    <h3 class="fw-bold mb-0 text-white">142</h3>
                </div>
                <i class="bi bi-activity fs-1 opacity-50"></i>
            </div>
        </x-card>
    </div>
</div>

<x-card title="أحدث النشاطات والزيارات" icon="bi-clock-history">
    <p class="text-muted">مرحباً بك في لوحة تحكم مركز العناية بالجروح (WCCMS).</p>
</x-card>
@endsection
