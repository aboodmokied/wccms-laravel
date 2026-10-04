<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WcProcedure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProcedureController extends Controller
{
    public function index(Request $request): View
    {
        $query = WcProcedure::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('WC_PROC_NAME', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('WC_PROC_IS_ACTIVE', (int) $request->input('status'));
        }

        $items = $query->orderBy('WC_PROC_IS_ACTIVE', 'desc')
            ->orderBy('WC_PROC_NAME', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.master_data.index', [
            'items' => $items,
            'domainTitle' => 'الإجراءات السريرية',
            'codeField' => 'WC_PROC_CODE',
            'nameField' => 'WC_PROC_NAME',
            'activeField' => 'WC_PROC_IS_ACTIVE',
            'indexRouteName' => 'admin.procedures.index',
            'storeRoute' => route('admin.procedures.store'),
            'updateRouteName' => 'admin.procedures.update',
            'toggleRouteName' => 'admin.procedures.toggle',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:wc_procedure_tb,WC_PROC_NAME'],
        ], [
            'name.required' => 'اسم الإجراء السريري مطلوب',
            'name.unique' => 'اسم الإجراء السريري مستخدم بالفعل',
        ]);

        WcProcedure::create([
            'WC_PROC_CODE' => (string) Str::uuid(),
            'WC_PROC_NAME' => trim($validated['name']),
            'WC_PROC_IS_ACTIVE' => 1,
        ]);

        return redirect()->back()->with('success', 'تم إضافة الإجراء السريري الجديد بنجاح');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $item = WcProcedure::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:wc_procedure_tb,WC_PROC_NAME,'.$id.',WC_PROC_CODE'],
        ], [
            'name.required' => 'اسم الإجراء السريري مطلوب',
            'name.unique' => 'اسم الإجراء السريري مستخدم بالفعل',
        ]);

        $item->update([
            'WC_PROC_NAME' => trim($validated['name']),
        ]);

        return redirect()->back()->with('success', 'تم تحديث اسم الإجراء السريري بنجاح');
    }

    public function toggleActive(string $id): RedirectResponse
    {
        $item = WcProcedure::findOrFail($id);
        $newStatus = ((int) $item->WC_PROC_IS_ACTIVE === 1) ? 0 : 1;

        $item->update([
            'WC_PROC_IS_ACTIVE' => $newStatus,
        ]);

        $statusText = $newStatus === 1 ? 'تفعيل' : 'تعطيل';

        return redirect()->back()->with('success', "تم {$statusText} الإجراء السريري بنجاح");
    }
}
