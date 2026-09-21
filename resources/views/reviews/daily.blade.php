@extends('layouts.app')

@section('title', 'التقييم والزيارة اليومية')

@push('styles')
    @vite(['resources/css/pages/reviews.css'])
@endpush

@push('scripts')
    @vite(['resources/js/pages/reviews.js'])
@endpush

@section('content')
<x-card title="استمارة التقييم والزيارة اليومية للجروح" icon="bi-clipboard2-pulse" subtitle="تسجيل قياسات أبعاد الجرح، كمية الإفرازات، درجة الألم، والضمادات المطبقة">
    <form method="POST" action="{{ url('/reviews/daily') }}">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <x-form-group label="طول الجرح (سم)" name="length_cm" type="number" required="true" />
            </div>
            <div class="col-md-4">
                <x-form-group label="عرض الجرح (سم)" name="width_cm" type="number" required="true" />
            </div>
            <div class="col-md-4">
                <x-form-group label="عمق الجرح (سم)" name="depth_cm" type="number" required="true" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-form-group label="درجة الألم (0 - 10)" name="pain_score" type="number" required="true" />
            </div>
            <div class="col-md-6">
                <x-form-group label="كمية الإفرازات" name="exudate_amount" required="true" />
            </div>
        </div>

        <x-form-group label="ملاحظات التقييم التمريضي" name="notes" type="textarea" />

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="submit" class="btn btn-primary px-4">{{ __('ui.buttons.save') }}</button>
        </div>
    </form>
</x-card>
@endsection
