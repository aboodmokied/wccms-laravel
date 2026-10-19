<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAssessmentChoiceRequest;
use App\Models\WcAssessmentChoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentChoiceController extends Controller
{
    public function index(Request $request): View
    {
        $categories = WcAssessmentChoice::query()->distinct()->orderBy('WC_ACH_CATEGORY')->pluck('WC_ACH_CATEGORY');
        $choices = WcAssessmentChoice::query()
            ->when($request->filled('category'), fn ($query) => $query->where('WC_ACH_CATEGORY', $request->string('category')))
            ->orderBy('WC_ACH_CATEGORY')->orderBy('WC_ACH_DISPLAY_ORDER')->paginate(30)->withQueryString();

        return view('admin.master_data.assessment-choices', compact('choices', 'categories'));
    }

    public function update(UpdateAssessmentChoiceRequest $request, WcAssessmentChoice $assessmentChoice): RedirectResponse
    {
        $assessmentChoice->update([
            'WC_ACH_LABEL_EN' => trim($request->validated('label_en')),
            'WC_ACH_LABEL_AR' => trim($request->validated('label_ar')),
            'WC_ACH_DISPLAY_ORDER' => $request->integer('display_order'),
        ]);

        return redirect()->back()->with('success', 'تم تحديث تسمية الخيار وترتيبه.');
    }

    public function toggle(WcAssessmentChoice $assessmentChoice): RedirectResponse
    {
        $assessmentChoice->update(['WC_ACH_IS_ACTIVE' => ! $assessmentChoice->WC_ACH_IS_ACTIVE]);

        return redirect()->back()->with('success', $assessmentChoice->WC_ACH_IS_ACTIVE ? 'تم تفعيل الخيار.' : 'تم تعطيل الخيار مع إبقائه في السجل التاريخي.');
    }
}
