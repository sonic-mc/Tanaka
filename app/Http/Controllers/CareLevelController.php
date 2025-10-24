<?php

namespace App\Http\Controllers;

use App\Models\CareLevel;
use Illuminate\Http\Request;

class CareLevelController extends Controller
{
    /**
     * A curated list of common care level names and optional descriptions
     * to help users who are unsure what to add.
     *
     * The key is the suggested name and the value is a short description.
     */
    protected function suggestions(): array
    {
        return [
            'General Ward' => 'Standard inpatient ward for patients requiring routine nursing and medical care.',
            'High Dependency Unit (HDU)' => 'For patients needing closer monitoring and higher nurse-to-patient ratio than a general ward.',
            'Intensive Care Unit (ICU)' => 'Critical care unit for patients requiring life-support and continuous monitoring.',
            'Step-down Unit' => 'Transitional unit for patients recovering from ICU-level care who still need support.',
            'Observation / Short Stay' => 'Area for short-term observation and assessment (usually <24–72 hours).',
            'Psychiatric Ward' => 'Specialised ward for inpatient psychiatric care and observation.',
            'Rehabilitation' => 'Unit focused on therapy and functional recovery after acute illness or injury.',
            'Emergency Observation' => 'Observation area attached to ER for immediate post-triage monitoring.',
            'Palliative Care' => 'Care focused on comfort and quality of life for patients with life-limiting conditions.',
            'Outpatient / Ambulatory Care' => 'Care level for clinic-based or same-day procedures (not admitted).',
        ];
    }

    public function index()
    {
        $levels = CareLevel::orderBy('name')->get();
        return view('care_levels.index', compact('levels'));
    }

    public function create()
    {
        $suggestions = $this->suggestions();
        return view('care_levels.create', compact('suggestions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:care_levels,name',
            'description' => 'nullable|string|max:2000',
        ]);

        CareLevel::create($validated);

        return redirect()->route('care_levels.index')->with('success', 'Care level added.');
    }

    public function show(CareLevel $care_level)
    {
        return view('care_levels.show', compact('care_level'));
    }

    public function edit(CareLevel $care_level)
    {
        $suggestions = $this->suggestions();
        return view('care_levels.edit', compact('care_level', 'suggestions'));
    }

    public function update(Request $request, CareLevel $care_level)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:care_levels,name,' . $care_level->id,
            'description' => 'nullable|string|max:2000',
        ]);

        $care_level->update($validated);

        return redirect()->route('care_levels.index')->with('success', 'Care level updated.');
    }

    public function destroy(CareLevel $care_level)
    {
        $care_level->delete();
        return redirect()->route('care_levels.index')->with('success', 'Care level deleted.');
    }
}
