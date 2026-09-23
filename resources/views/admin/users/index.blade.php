@extends('layouts.app')

@section('title', 'إدارة المستخدمين والصلاحيات')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">إدارة المستخدمين والصلاحيات</h1>
            <p class="text-muted small mb-0">تعيين أدوار المستخدمين وتفعيل حسابات الوصول بربطها بالموظفين في العيادة</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">اسم المستخدم (رمز الهوية)</th>
                            <th>الموظف المرتبط</th>
                            <th>دور النظام (Role)</th>
                            <th>حالة الوصول (WCCMS)</th>
                            <th class="text-end pe-4">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">
                                    {{ $user->PER_USER_USERNAME }}
                                    <span class="d-block text-muted x-small font-monospace">{{ $user->PER_USER_CODE }}</span>
                                </td>
                                <td>
                                    @if($user->employee)
                                        <span class="fw-semibold text-primary">{{ $user->employee->PER_EMP_FULL_NAME }}</span>
                                        <span class="d-block text-muted small">{{ $user->employee->PER_EMP_JOB_TITLE ?? 'غير مخصص' }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">غير مرتبط بموظف</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->PER_USER_ROLE === 'ADMIN')
                                        <span class="badge bg-danger">مسؤول (ADMIN)</span>
                                    @elseif($user->PER_USER_ROLE === 'NURSE')
                                        <span class="badge bg-info text-dark">ممرض (NURSE)</span>
                                    @else
                                        <span class="badge bg-secondary">غير معين (NULL)</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->PER_USER_IS_ACTIVE)
                                        <span class="badge bg-success">نشط (Active)</span>
                                    @else
                                        <span class="badge bg-danger">موقوف / قيد المراجعة</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <form action="{{ route('admin.users.update', $user->PER_USER_CODE) }}" method="POST" class="d-inline-flex gap-2 align-items-center">
                                        @csrf
                                        @method('PUT')
                                        <select name="PER_USER_ROLE" class="form-select form-select-sm" style="width: 140px;">
                                            <option value="" {{ is_null($user->PER_USER_ROLE) ? 'selected' : '' }}>-- بدون دور --</option>
                                            <option value="NURSE" {{ $user->PER_USER_ROLE === 'NURSE' ? 'selected' : '' }}>ممرض (NURSE)</option>
                                            <option value="ADMIN" {{ $user->PER_USER_ROLE === 'ADMIN' ? 'selected' : '' }}>مسؤول (ADMIN)</option>
                                        </select>

                                        <select name="PER_USER_IS_ACTIVE" class="form-select form-select-sm" style="width: 130px;">
                                            <option value="1" {{ $user->PER_USER_IS_ACTIVE ? 'selected' : '' }}>تفعيل (1)</option>
                                            <option value="0" {{ ! $user->PER_USER_IS_ACTIVE ? 'selected' : '' }}>إيقاف (0)</option>
                                        </select>

                                        @if(! $user->employee && count($unlinkedEmployees) > 0)
                                            <select name="PER_EMP_CODE" class="form-select form-select-sm" style="width: 160px;">
                                                <option value="">-- ربط بموظف --</option>
                                                @foreach($unlinkedEmployees as $emp)
                                                    <option value="{{ $emp->PER_EMP_CODE }}">{{ $emp->PER_EMP_FULL_NAME }}</option>
                                                @endforeach
                                            </select>
                                        @endif

                                        <button type="submit" class="btn btn-sm btn-primary">
                                            حفظ التعديل
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">لا يوجد مستخدمون مسجلون حالياً</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
