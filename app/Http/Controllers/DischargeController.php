<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\DischargedPatient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Traits\AuditLogger;

class DischargeController extends Controller
{
    use AuditLogger;

    /**
     * List all discharges
     */
    public function index(Request $request)
    {
        $query = DischargedPatient::query()
            ->with(['patient', 'admission', 'dischargedBy'])
            ->latest();

        // Optional filters
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->integer('patient_id'));
        }
        if ($request->filled('admission_id')) {
            $query->where('admission_id', $request->integer('admission_id'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('discharge_date', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('discharge_date', '<=', $request->date('date_to'));
        }

        $discharges = $query->paginate(20)->withQueryString();

        return view('discharges.index', compact('discharges'));
    }

    /**
     * Show the discharge form for an active admission.
     */
    public function create(Admission $admission)
    {
        if ($admission->status !== 'active') {
            return back()->withErrors(['admission' => 'Only active admissions can be discharged.']);
        }

        // Prevent duplicate discharge for the same admission
        $alreadyDischarged = DischargedPatient::where('admission_id', $admission->id)->exists();
        if ($alreadyDischarged) {
            return redirect()
                ->route('discharges.index')
                ->with('warning', 'This admission has already been discharged.');
        }

        return view('discharges.create', compact('admission'));
    }

    /**
     * Store the discharge and update the admission status to discharged.
     */
    public function store(Request $request, Admission $admission)
    {
        if ($admission->status !== 'active') {
            return back()->withErrors(['admission' => 'Only active admissions can be discharged.']);
        }

        // Validate fields
        $validated = $request->validate([
            'discharge_date'    => ['required', 'date', 'after_or_equal:' . $admission->admission_date->format('Y-m-d')],
            'discharge_notes'   => ['nullable', 'string'],
            'follow_up_plan'    => ['nullable', 'string', 'max:255'],
            'referral_facility' => ['nullable', 'string', 'max:255'],
            'requires_follow_up'=> ['sometimes', 'boolean'],
        ]);

        // Check no duplicate discharge for this admission
        if (DischargedPatient::where('admission_id', $admission->id)->exists()) {
            return back()
                ->withErrors(['admission' => 'This admission already has a discharge record.'])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $admission) {
            $discharge = DischargedPatient::create([
                'patient_id'         => $admission->patient_id,
                'admission_id'       => $admission->id,
                'discharged_by'      => Auth::id(),
                'discharge_date'     => $validated['discharge_date'],
                'discharge_notes'    => $validated['discharge_notes'] ?? null,
                'follow_up_plan'     => $validated['follow_up_plan'] ?? null,
                'referral_facility'  => $validated['referral_facility'] ?? null,
                'requires_follow_up' => (bool)($validated['requires_follow_up'] ?? false),
                'created_by'         => Auth::id(),
                'last_modified_by'   => Auth::id(),
            ]);

            $admission->update([
                'status'            => 'discharged',
                'last_modified_by'  => Auth::id(),
            ]);

            // Audit log
            if (method_exists($this, 'logAudit')) {
                $this->logAudit('patient_discharged', [
                    'discharge_id' => $discharge->id,
                    'admission_id' => $admission->id,
                    'patient_id'   => $admission->patient_id,
                    'discharged_by'=> Auth::id(),
                ]);
            }
        });

        return redirect()
            ->route('discharges.index')
            ->with('success', 'Patient discharged successfully.');
    }

    /**
     * Show a specific discharge details
     */
    public function show(DischargedPatient $discharge)
    {
        $discharge->load(['patient', 'admission', 'dischargedBy', 'createdBy', 'lastModifiedBy']);

        return view('discharges.show', compact('discharge'));
    }

    /**
     * Edit a discharge (does not change the admission status).
     */
    public function edit(DischargedPatient $discharge)
    {
        $discharge->load(['admission', 'patient']);

        return view('discharges.edit', compact('discharge'));
    }

    /**
     * Update a discharge record.
     */
    public function update(Request $request, DischargedPatient $discharge)
    {
        $admission = $discharge->admission;

        $validated = $request->validate([
            'discharge_date'    => ['required', 'date', 'after_or_equal:' . $admission->admission_date->format('Y-m-d')],
            'discharge_notes'   => ['nullable', 'string'],
            'follow_up_plan'    => ['nullable', 'string', 'max:255'],
            'referral_facility' => ['nullable', 'string', 'max:255'],
            'requires_follow_up'=> ['sometimes', 'boolean'],
        ]);

        $discharge->update([
            'discharge_date'     => $validated['discharge_date'],
            'discharge_notes'    => $validated['discharge_notes'] ?? null,
            'follow_up_plan'     => $validated['follow_up_plan'] ?? null,
            'referral_facility'  => $validated['referral_facility'] ?? null,
            'requires_follow_up' => (bool)($validated['requires_follow_up'] ?? false),
            'last_modified_by'   => Auth::id(),
        ]);

        if (method_exists($this, 'logAudit')) {
            $this->logAudit('patient_discharge_updated', [
                'discharge_id' => $discharge->id,
                'admission_id' => $admission->id,
                'patient_id'   => $admission->patient_id,
                'updated_by'   => Auth::id(),
            ]);
        }

        return redirect()
            ->route('discharges.show', $discharge)
            ->with('success', 'Discharge updated successfully.');
    }

    /**
     * Soft delete a discharge record.
     * NOTE: This does not change the admission status; adjust if you need to re-open admissions.
     */
    public function destroy(DischargedPatient $discharge)
    {
        $discharge->delete();

        if (method_exists($this, 'logAudit')) {
            $this->logAudit('patient_discharge_deleted', [
                'discharge_id' => $discharge->id,
                'admission_id' => $discharge->admission_id,
                'patient_id'   => $discharge->patient_id,
                'deleted_by'   => Auth::id(),
            ]);
        }

        return redirect()
            ->route('discharges.index')
            ->with('success', 'Discharge record deleted.');
    }
}
