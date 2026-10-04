@extends('layouts.app')

@section('title', 'لوحة إدارة النظام والبيانات المرجعية')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-gear-fill text-primary me-2"></i>إدارة النظام والبيانات المرجعية
            </h3>
            <p class="text-muted small mb-0">لوحة التحكم المركزية لإدارة مستخدمي النظام والجداول المرجعية الأساسية للعيادة</p>
        </div>
    </div>

    {{-- Quick Links Cards --}}
    <div class="row g-3 mb-4">

        {{-- Master Data Cards --}}
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary-subtle text-primary p-3 me-3">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">الأقسام الطبية</h5>
                            <small class="text-muted">إدارة أقسام المستشفى والعيادات</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-primary btn-sm w-100">
                        إدارة الأقسام <i class="bi bi-arrow-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-info-subtle text-info p-3 me-3">
                            <i class="bi bi-journal-medical fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">التاريخ الطبي</h5>
                            <small class="text-muted">إدارة خيارات الأمراض والتاريخ الطبي</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.pmh.index') }}" class="btn btn-outline-info btn-sm w-100">
                        إدارة التاريخ الطبي <i class="bi bi-arrow-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success-subtle text-success p-3 me-3">
                            <i class="bi bi-activity fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">التشخيصات</h5>
                            <small class="text-muted">إدارة تشخيصات الجروح والقرحات</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.diagnoses.index') }}" class="btn btn-outline-success btn-sm w-100">
                        إدارة التشخيصات <i class="bi bi-arrow-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-warning-subtle text-warning p-3 me-3">
                            <i class="bi bi-patch-exclamation fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">أنواع الجروح</h5>
                            <small class="text-muted">تصنيفات وأنواع الجروح والقرحات</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.wound_types.index') }}" class="btn btn-outline-warning btn-sm w-100">
                        إدارة أنواع الجروح <i class="bi bi-arrow-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-danger-subtle text-danger p-3 me-3">
                            <i class="bi bi-bandaid fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">الضمادات الطبية</h5>
                            <small class="text-muted">قائمة الضمادات الطبية المستخدمة</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.dressings.index') }}" class="btn btn-outline-danger btn-sm w-100">
                        إدارة الضمادات <i class="bi bi-arrow-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100 border-start border-secondary border-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-secondary-subtle text-secondary p-3 me-3">
                            <i class="bi bi-capsule fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">الأدوية والعلاجات</h5>
                            <small class="text-muted">إدارة قائمة العلاجات الموضعية والدوائية</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.medications.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        إدارة الأدوية <i class="bi bi-arrow-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100 border-start border-dark border-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-dark-subtle text-dark p-3 me-3">
                            <i class="bi bi-scissors fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">الإجراءات السريرية</h5>
                            <small class="text-muted">إدارة قائمة الإجراءات التمريضية</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.procedures.index') }}" class="btn btn-outline-dark btn-sm w-100">
                        إدارة الإجراءات <i class="bi bi-arrow-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-4 bg-primary-subtle">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary text-white p-3 me-3">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">إدارة المستخدمين</h5>
                            <small class="text-muted">تعيين الأدوار والصلاحيات وتفعيل الحسابات</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-sm w-100">
                        إدارة المستخدمين <i class="bi bi-arrow-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
