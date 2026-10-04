<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WcPmh;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PmhController extends Controller
{
    public function index(Request $request): View
    {
        $query = WcPmh::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('WC_PMH_NAME', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('WC_PMH_IS_ACTIVE', (int) $request->input('status'));
        }

        $items = $query->orderBy('WC_PMH_IS_ACTIVE', 'desc')
            ->orderBy('WC_PMH_NAME', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.master_data.index', [
            'items' => $items,
            'domainTitle' => 'التاريخ الطبي',
            'codeField' => 'WC_PMH_CODE',
            'nameField' => 'WC_PMH_NAME',
            'activeField' => 'WC_PMH_IS_ACTIVE',
            'indexRouteName' => 'admin.pmh.index',
            'storeRoute' => route('admin.pmh.store'),
            'updateRouteName' => 'admin.pmh.update',
            'toggleRouteName' => 'admin.pmh.toggle',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:wc_pmh_tb,WC_PMH_NAME'],
        ], [
            'name.required' => 'اسم التاريخ الطبي مطلوب',
            'name.unique' => 'اسم التاريخ الطبي مستخدم بالفعل',
        ]);

        WcPmh::create([
            'WC_PMH_CODE' => (string) Str::uuid(),
            'WC_PMH_NAME' => trim($validated['name']),
            'WC_PMH_IS_ACTIVE' => 1,
        ]);

        return redirect()->back()->with('success', 'تم إضافة بند التاريخ الطبي بنجاح');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $item = WcPmh::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:wc_pmh_tb,WC_PMH_NAME,'.$id.',WC_PMH_CODE'],
        ], [
            'name.required' => 'اسم التاريخ الطبي مطلوب',
            'name.unique' => 'اسم التاريخ الطبي مستخدم بالفعل',
        ]);

        $item->update([
            'WC_PMH_NAME' => trim($validated['name']),
        ]);

        return redirect()->back()->with('success', 'تم تحديث اسم التاريخ الطبي بنجاح');
    }

    public function toggleActive(string $id): RedirectResponse
    {
        $item = WcPmh::findOrFail($id);
        $newStatus = ((int) $item->WC_PMH_IS_ACTIVE === 1) ? 0 : 1;

        $item->update([
            'WC_PMH_IS_ACTIVE' => $newStatus,
        ]);

        $statusText = $newStatus === 1 ? 'تفعيل' : 'تعطيل';

        return redirect()->back()->with('success', "تم {$statusText} بند التاريخ الطبي بنجاح");
    }
}
