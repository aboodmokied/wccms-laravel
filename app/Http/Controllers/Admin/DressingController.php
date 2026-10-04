<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WcDressing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DressingController extends Controller
{
    public function index(Request $request): View
    {
        $query = WcDressing::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('WC_DRESS_NAME', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('WC_DRESS_IS_ACTIVE', (int) $request->input('status'));
        }

        $items = $query->orderBy('WC_DRESS_IS_ACTIVE', 'desc')
            ->orderBy('WC_DRESS_NAME', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.master_data.index', [
            'items' => $items,
            'domainTitle' => 'الضمادات',
            'codeField' => 'WC_DRESS_CODE',
            'nameField' => 'WC_DRESS_NAME',
            'activeField' => 'WC_DRESS_IS_ACTIVE',
            'indexRouteName' => 'admin.dressings.index',
            'storeRoute' => route('admin.dressings.store'),
            'updateRouteName' => 'admin.dressings.update',
            'toggleRouteName' => 'admin.dressings.toggle',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:wc_dressing_tb,WC_DRESS_NAME'],
        ], [
            'name.required' => 'اسم الضمادة مطلوب',
            'name.unique' => 'اسم الضمادة مستخدم بالفعل',
        ]);

        WcDressing::create([
            'WC_DRESS_CODE' => (string) Str::uuid(),
            'WC_DRESS_NAME' => trim($validated['name']),
            'WC_DRESS_IS_ACTIVE' => 1,
        ]);

        return redirect()->back()->with('success', 'تم إضافة الضمادة الجديدة بنجاح');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $item = WcDressing::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:wc_dressing_tb,WC_DRESS_NAME,'.$id.',WC_DRESS_CODE'],
        ], [
            'name.required' => 'اسم الضمادة مطلوب',
            'name.unique' => 'اسم الضمادة مستخدم بالفعل',
        ]);

        $item->update([
            'WC_DRESS_NAME' => trim($validated['name']),
        ]);

        return redirect()->back()->with('success', 'تم تحديث اسم الضمادة بنجاح');
    }

    public function toggleActive(string $id): RedirectResponse
    {
        $item = WcDressing::findOrFail($id);
        $newStatus = ((int) $item->WC_DRESS_IS_ACTIVE === 1) ? 0 : 1;

        $item->update([
            'WC_DRESS_IS_ACTIVE' => $newStatus,
        ]);

        $statusText = $newStatus === 1 ? 'تفعيل' : 'تعطيل';

        return redirect()->back()->with('success', "تم {$statusText} الضمادة بنجاح");
    }
}
