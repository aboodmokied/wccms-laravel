<div class="card border-0 shadow-xs rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('patients.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control bg-light border-0 fs-7" placeholder="أدخل رقم الهوية الوطنية، رقم الملف، أو اسم المريض للبحث...">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4 rounded-2 fw-bold shadow-sm">
                    <i class="bi bi-search me-1"></i>بحث
                </button>
                @if(request('search'))
                    <a href="{{ route('patients.index') }}" class="btn btn-light px-3 rounded-2 text-dark">إلغاء</a>
                @endif
            </div>
        </form>
    </div>
</div>
