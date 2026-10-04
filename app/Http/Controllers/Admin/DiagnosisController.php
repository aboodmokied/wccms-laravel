<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WcDiagnosis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DiagnosisController extends Controller
{
    public function index(Request $request): View
    {
        $query = WcDiagnosis::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('WC_DIAG_NAME', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('WC_DIAG_IS_ACTIVE', (int) $request->input('status'));
        }

        $items = $query->orderBy('WC_DIAG_IS_ACTIVE', 'desc')
            ->orderBy('WC_DIAG_NAME', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.master_data.index', [
            'items' => $items,
            'domainTitle' => 'التشخيصات',
            'codeField' => 'WC_DIAG_CODE',
            'nameField' => 'WC_DIAG_NAME',
            'activeField' => 'WC_DIAG_IS_ACTIVE',
            'indexRouteName' => 'admin.diagnoses.index',
            'storeRoute' => route('admin.diagnoses.store'),
            'updateRouteName' => 'admin.diagnoses.update',
            'toggleRouteName' => 'admin.diagnoses.toggle',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:wc_diagnosis_tb,WC_DIAG_NAME'],
        ], [
            'name.required' => 'اسم التشخيص مطلوب',
            'name.unique' => 'اسم التشخيص مستخدم بالفعل',
        ]);

        WcDiagnosis::create([
            'WC_DIAG_CODE' => (string) Str::uuid(),
            'WC_DIAG_NAME' => trim($validated['name']),
            'WC_DIAG_IS_ACTIVE' => 1,
        ]);

        return redirect()->back()->with('success', 'تم إضافة التشخيص الجديد بنجاح');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $item = WcDiagnosis::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:wc_diagnosis_tb,WC_DIAG_NAME,'.$id.',WC_DIAG_CODE'],
        ], [
            'name.required' => 'اسم التشخيص مطلوب',
            'name.unique' => 'اسم التشخيص مستخدم بالفعل',
        ]);

        $item->update([
            'WC_DIAG_NAME' => trim($validated['name']),
        ]);

        return redirect()->back()->with('success', 'تم تحديث اسم التشخيص بنجاح');
    }

    public function toggleActive(string $id): RedirectResponse
    {
        $item = WcDiagnosis::findOrFail($id);
        $newStatus = ((int) $item->WC_DIAG_IS_ACTIVE === 1) ? 0 : 1;

        $item->update([
            'WC_DIAG_IS_ACTIVE' => $newStatus,
        ]);

        $statusText = $newStatus === 1 ? 'تفعيل' : 'تعطيل';

        return redirect()->back()->with('success', "تم {$statusText} التشخيص بنجاح");
    }
}
