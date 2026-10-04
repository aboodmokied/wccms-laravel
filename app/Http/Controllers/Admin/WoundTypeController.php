<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WcWoundType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WoundTypeController extends Controller
{
    public function index(Request $request): View
    {
        $query = WcWoundType::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('WC_WTYPE_NAME', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('WC_WTYPE_IS_ACTIVE', (int) $request->input('status'));
        }

        $items = $query->orderBy('WC_WTYPE_IS_ACTIVE', 'desc')
            ->orderBy('WC_WTYPE_NAME', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.master_data.index', [
            'items' => $items,
            'domainTitle' => 'أنواع الجروح',
            'codeField' => 'WC_WTYPE_CODE',
            'nameField' => 'WC_WTYPE_NAME',
            'activeField' => 'WC_WTYPE_IS_ACTIVE',
            'indexRouteName' => 'admin.wound_types.index',
            'storeRoute' => route('admin.wound_types.store'),
            'updateRouteName' => 'admin.wound_types.update',
            'toggleRouteName' => 'admin.wound_types.toggle',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:wc_wound_type_tb,WC_WTYPE_NAME'],
        ], [
            'name.required' => 'اسم نوع الجرح مطلوب',
            'name.unique' => 'اسم نوع الجرح مستخدم بالفعل',
        ]);

        WcWoundType::create([
            'WC_WTYPE_CODE' => (string) Str::uuid(),
            'WC_WTYPE_NAME' => trim($validated['name']),
            'WC_WTYPE_IS_ACTIVE' => 1,
        ]);

        return redirect()->back()->with('success', 'تم إضافة نوع الجرح الجديد بنجاح');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $item = WcWoundType::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:wc_wound_type_tb,WC_WTYPE_NAME,'.$id.',WC_WTYPE_CODE'],
        ], [
            'name.required' => 'اسم نوع الجرح مطلوب',
            'name.unique' => 'اسم نوع الجرح مستخدم بالفعل',
        ]);

        $item->update([
            'WC_WTYPE_NAME' => trim($validated['name']),
        ]);

        return redirect()->back()->with('success', 'تم تحديث اسم نوع الجرح بنجاح');
    }

    public function toggleActive(string $id): RedirectResponse
    {
        $item = WcWoundType::findOrFail($id);
        $newStatus = ((int) $item->WC_WTYPE_IS_ACTIVE === 1) ? 0 : 1;

        $item->update([
            'WC_WTYPE_IS_ACTIVE' => $newStatus,
        ]);

        $statusText = $newStatus === 1 ? 'تفعيل' : 'تعطيل';

        return redirect()->back()->with('success', "تم {$statusText} نوع الجرح بنجاح");
    }
}
