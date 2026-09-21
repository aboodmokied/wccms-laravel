@extends('layouts.app')

@section('title', 'تسجيل الدخول')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-12 col-md-6 col-lg-5">
        <x-card title="تسجيل الدخول للنظام" icon="bi-shield-lock" subtitle="منظومة إدارة مركز العناية بالجروح">
            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <x-form-group label="اسم المستخدم" name="username" required="true" />
                <x-form-group label="كلمة المرور" name="password" type="password" required="true" />
                
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        {{ __('ui.buttons.save') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection
