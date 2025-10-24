<?php

namespace App\Http\Controllers;

use App\Models\ConsultationFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConsultationFeeController extends Controller
{
    /**
     * Display a paginated listing of the resource with optional filtering and sorting.
     */
    public function index(Request $request): View
    {
        $query = ConsultationFee::query();

        // Filtering
        if ($request->filled('age_group') && in_array($request->age_group, ['child', 'adult'])) {
            $query->where('age_group', $request->age_group);
        }

        // Sorting
        $allowedSorts = ['fee_amount', 'age_group', 'created_at'];
        $sort = in_array($request->get('sort', 'created_at'), $allowedSorts) ? $request->get('sort', 'created_at') : 'created_at';
        $direction = $request->get('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $fees = $query->orderBy($sort, $direction)->paginate(10)->withQueryString();

        return view('consultation_fees.index', compact('fees', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('consultation_fees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'age_group' => 'required|in:child,adult',
            'fee_amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($data) {
            ConsultationFee::create($data);
        });

        return redirect()->route('consultation_fees.index')->with('success', 'Fee added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ConsultationFee $consultation_fee): View
    {
        return view('consultation_fees.show', compact('consultation_fee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConsultationFee $consultation_fee): View
    {
        return view('consultation_fees.edit', compact('consultation_fee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ConsultationFee $consultation_fee): RedirectResponse
    {
        $data = $request->validate([
            'age_group' => 'required|in:child,adult',
            'fee_amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($consultation_fee, $data) {
            $consultation_fee->update($data);
        });

        return redirect()->route('consultation_fees.index')->with('success', 'Fee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConsultationFee $consultation_fee): RedirectResponse
    {
        DB::transaction(function () use ($consultation_fee) {
            $consultation_fee->delete();
        });

        return redirect()->route('consultation_fees.index')->with('success', 'Fee deleted.');
    }
}
