<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WcDepartment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = WcDepartment::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('WC_DEPT_NAME', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('WC_DEPT_IS_ACTIVE', (int) $request->input('status'));
        }

        $items = $query->orderBy('WC_DEPT_IS_ACTIVE', 'desc')
            ->orderBy('WC_DEPT_NAME', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.master_data.index', [
            'items' => $items,
            'domainTitle' => 'الأقسام',
            'codeField' => 'WC_DEPT_CODE',
            'nameField' => 'WC_DEPT_NAME',
            'activeField' => 'WC_DEPT_IS_ACTIVE',
            'indexRouteName' => 'admin.departments.index',
            'storeRoute' => route('admin.departments.store'),
            'updateRouteName' => 'admin.departments.update',
            'toggleRouteName' => 'admin.departments.toggle',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:wc_department_tb,WC_DEPT_NAME'],
        ], [
            'name.required' => 'اسم القسم مطلوب',
            'name.unique' => 'اسم القسم مستخدم بالفعل',
        ]);

        WcDepartment::create([
            'WC_DEPT_CODE' => (string) Str::uuid(),
            'WC_DEPT_NAME' => trim($validated['name']),
            'WC_DEPT_IS_ACTIVE' => 1,
        ]);

        return redirect()->back()->with('success', 'تم إضافة القسم الجديد بنجاح');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $department = WcDepartment::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:wc_department_tb,WC_DEPT_NAME,'.$id.',WC_DEPT_CODE'],
        ], [
            'name.required' => 'اسم القسم مطلوب',
            'name.unique' => 'اسم القسم مستخدم بالفعل',
        ]);

        $department->update([
            'WC_DEPT_NAME' => trim($validated['name']),
        ]);

        return redirect()->back()->with('success', 'تم تحديث اسم القسم بنجاح');
    }

    public function toggleActive(string $id): RedirectResponse
    {
        $department = WcDepartment::findOrFail($id);
        $newStatus = ((int) $department->WC_DEPT_IS_ACTIVE === 1) ? 0 : 1;

        $department->update([
            'WC_DEPT_IS_ACTIVE' => $newStatus,
        ]);

        $statusText = $newStatus === 1 ? 'تفعيل' : 'تعطيل';

        return redirect()->back()->with('success', "تم {$statusText} القسم بنجاح");
    }
}
