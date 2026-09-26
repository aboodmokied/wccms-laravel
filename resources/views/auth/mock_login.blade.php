@extends('layouts.app')

@section('title', 'بوابة الدخول الموحد للمستشفى — المحاكاة الافتراضية')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-hospital fs-1"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">بوابة الدخول الموحد لمجمع الشفاء الطبي</h3>
                <p class="text-muted small">نظام المصادقة الهجين الافتراضي للاختبار والتطوير (Virtual Hospital SSO)</p>
            </div>

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- 1. Quick Personas Selection -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary bg-opacity-10 fw-bold text-primary py-3">
                    <i class="bi bi-lightning-charge-fill me-2"></i>تسجيل الدخول السريع لشخصيات الاختيار (1-Click Test Personas)
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <!-- Ward Nurse Persona -->
                        <div class="col-md-6">
                            <form action="{{ route('mock_hospital_auth.login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="username" value="ward_nurse_demo">
                                <input type="hidden" name="role" value="NURSE">
                                <input type="hidden" name="full_name" value="ممرض قسم الجراحة (وردية أ)">
                                <button type="submit" class="btn btn-outline-primary w-100 p-3 text-start h-100 rounded-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle p-2 me-3">
                                            <i class="bi bi-person-workspace fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">ممرض قسم الإيواء (Ward Nurse)</h6>
                                            <span class="badge bg-primary mb-1">دور: NURSE</span>
                                            <p class="text-muted small mb-0">تقديم طلبات الإحالة والتحويل لعيادة الجروح</p>
                                        </div>
                                    </div>
                                </button>
                            </form>
                        </div>

                        <!-- Clinic Nurse Persona -->
                        <div class="col-md-6">
                            <form action="{{ route('mock_hospital_auth.login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="username" value="clinic_nurse_demo">
                                <input type="hidden" name="role" value="NURSE">
                                <input type="hidden" name="full_name" value="ممرض عيادة الجروح والتخاريج">
                                <button type="submit" class="btn btn-outline-info w-100 p-3 text-start h-100 rounded-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-info text-white rounded-circle p-2 me-3">
                                            <i class="bi bi-heart-pulse fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">ممرض عيادة الجروح (Clinic Nurse)</h6>
                                            <span class="badge bg-info mb-1">دور: NURSE</span>
                                            <p class="text-muted small mb-0">استلام طلبات الإحالة وإجراء زيارات التقييم والقبول</p>
                                        </div>
                                    </div>
                                </button>
                            </form>
                        </div>

                        <!-- Clinic Admin Persona -->
                        <div class="col-md-6">
                            <form action="{{ route('mock_hospital_auth.login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="username" value="clinic_admin_demo">
                                <input type="hidden" name="role" value="ADMIN">
                                <input type="hidden" name="full_name" value="د. مشرف عيادة الجروح">
                                <button type="submit" class="btn btn-outline-dark w-100 p-3 text-start h-100 rounded-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-dark text-white rounded-circle p-2 me-3">
                                            <i class="bi bi-shield-lock-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">مشرف العيادة (Clinic Admin)</h6>
                                            <span class="badge bg-dark mb-1">دور: ADMIN</span>
                                            <p class="text-muted small mb-0">إدارة المستخدمين، المراجعين، والجداول المرجعية</p>
                                        </div>
                                    </div>
                                </button>
                            </form>
                        </div>

                        <!-- Pending User Persona -->
                        <div class="col-md-6">
                            <form action="{{ route('mock_hospital_auth.login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="username" value="new_user_demo">
                                <input type="hidden" name="role" value="PENDING">
                                <button type="submit" class="btn btn-outline-warning w-100 p-3 text-start h-100 rounded-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-warning text-dark rounded-circle p-2 me-3">
                                            <i class="bi bi-hourglass-split fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">مستخدم جديد (Pending Approval)</h6>
                                            <span class="badge bg-warning text-dark mb-1">دور: NULL</span>
                                            <p class="text-muted small mb-0">اختبار شاشة قيد الاعتماد والمراجعة</p>
                                        </div>
                                    </div>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Custom Login Form -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light fw-bold text-secondary py-3">
                    <i class="bi bi-sliders me-2"></i>تسجيل دخول مخصص برمز مستخدم جديد
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('mock_hospital_auth.login') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label for="username" class="form-label fw-semibold">اسم المستخدم (Hospital Username) <span class="text-danger">*</span></label>
                                <input type="text" name="username" id="username" class="form-control" placeholder="مثال: nurse_mohammed" required>
                            </div>
                            <div class="col-md-4">
                                <label for="role" class="form-label fw-semibold">الصلاحية للنظام <span class="text-danger">*</span></label>
                                <select name="role" id="role" class="form-select">
                                    <option value="NURSE">ممرض (NURSE)</option>
                                    <option value="ADMIN">مسؤول (ADMIN)</option>
                                    <option value="PENDING">قيد المراجعة (PENDING - NO ROLE)</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> دخول النظام
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4 text-muted small">
                <i class="bi bi-info-circle me-1"></i> يتم إصدار رمز JWT مشفر تلقائياً وتعيين الكوكيز <code>hospital_token</code> للتصفح الآمن.
            </div>
        </div>
    </div>
</div>
@endsection
