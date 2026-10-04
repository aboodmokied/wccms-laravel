<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WcMedication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MedicationController extends Controller
{
    public function index(Request $request): View
    {
        $query = WcMedication::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('WC_MED_NAME', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('WC_MED_IS_ACTIVE', (int) $request->input('status'));
        }

        $items = $query->orderBy('WC_MED_IS_ACTIVE', 'desc')
            ->orderBy('WC_MED_NAME', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.master_data.index', [
            'items' => $items,
            'domainTitle' => 'الأدوية والعلاجات',
            'codeField' => 'WC_MED_CODE',
            'nameField' => 'WC_MED_NAME',
            'activeField' => 'WC_MED_IS_ACTIVE',
            'indexRouteName' => 'admin.medications.index',
            'storeRoute' => route('admin.medications.store'),
            'updateRouteName' => 'admin.medications.update',
            'toggleRouteName' => 'admin.medications.toggle',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:wc_medication_tb,WC_MED_NAME'],
        ], [
            'name.required' => 'اسم العلاج مطلوب',
            'name.unique' => 'اسم العلاج مستخدم بالفعل',
        ]);

        WcMedication::create([
            'WC_MED_CODE' => (string) Str::uuid(),
            'WC_MED_NAME' => trim($validated['name']),
            'WC_MED_IS_ACTIVE' => 1,
        ]);

        return redirect()->back()->with('success', 'تم إضافة العلاج الجديد بنجاح');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $item = WcMedication::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:wc_medication_tb,WC_MED_NAME,'.$id.',WC_MED_CODE'],
        ], [
            'name.required' => 'اسم العلاج مطلوب',
            'name.unique' => 'اسم العلاج مستخدم بالفعل',
        ]);

        $item->update([
            'WC_MED_NAME' => trim($validated['name']),
        ]);

        return redirect()->back()->with('success', 'تم تحديث اسم العلاج بنجاح');
    }

    public function toggleActive(string $id): RedirectResponse
    {
        $item = WcMedication::findOrFail($id);
        $newStatus = ((int) $item->WC_MED_IS_ACTIVE === 1) ? 0 : 1;

        $item->update([
            'WC_MED_IS_ACTIVE' => $newStatus,
        ]);

        $statusText = $newStatus === 1 ? 'تفعيل' : 'تعطيل';

        return redirect()->back()->with('success', "تم {$statusText} العلاج بنجاح");
    }
}
