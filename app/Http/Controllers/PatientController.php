<?php

namespace App\Http\Controllers;

use App\Models\PatientDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class PatientController extends Controller
{
    // List patients with search and status filter
    public function index(Request $request)
    {
        $query = PatientDetail::query();

        // Status filter: active (default), trashed, all
        $status = $request->get('status', 'active');
        if ($status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        }

        // Search across multiple fields
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_code', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('national_id_number', 'like', "%{$search}%")
                  ->orWhere('passport_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        $patients = $query->latest()->paginate(20)->withQueryString();

        return view('patients.index', compact('patients', 'status', 'search'));
    }

    // Show form to create new patient
    public function create()
    {
        $patient = new PatientDetail();

        // Predict next code for display only (actual code generated on store)
        $patient->patient_code = $this->predictNextPatientCode();

        return view('patients.create', compact('patient'));
    }

    // Store new patient (patient_code is auto-generated, not accepted from input)
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Identification (patient_code removed; it's generated)
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'dob' => ['nullable', 'date'],
            'national_id_number' => ['nullable', 'string', 'max:255', 'unique:patient_details,national_id_number'],
            'passport_number' => ['nullable', 'string', 'max:255', 'unique:patient_details,passport_number'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Contact & Demographics
            'email' => ['nullable', 'email'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'residential_address' => ['nullable', 'string', 'max:255'],
            'race' => ['nullable', 'string', 'max:50'],
            'religion' => ['nullable', 'string', 'max:50'],
            'language' => ['nullable', 'string', 'max:50'],
            'denomination' => ['nullable', 'string', 'max:50'],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'occupation' => ['nullable', 'string', 'max:100'],

            // Medical Info
            'blood_group' => ['nullable', 'string', 'max:10'],
            'allergies' => ['nullable', 'string', 'max:255'],
            'disabilities' => ['nullable', 'string', 'max:255'],
            'special_diet' => ['nullable', 'string', 'max:255'],
            'medical_aid_provider' => ['nullable', 'string', 'max:100'],
            'medical_aid_number' => ['nullable', 'string', 'max:50'],
            'special_medical_requirements' => ['nullable', 'string'],
            'current_medications' => ['nullable', 'string'],
            'past_medical_history' => ['nullable', 'string'],

            // Next of Kin
            'next_of_kin_name' => ['nullable', 'string', 'max:100'],
            'next_of_kin_relationship' => ['nullable', 'string', 'max:50'],
            'next_of_kin_contact_number' => ['nullable', 'string', 'max:20'],
            'next_of_kin_email' => ['nullable', 'email'],
            'next_of_kin_address' => ['nullable', 'string', 'max:255'],
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('patients', 'public');
        }

        $validated['created_by'] = Auth::id();

        // Generate unique patient_code safely with retries
        $maxAttempts = 5;
        $attempt = 0;
        do {
            try {
                DB::transaction(function () use (&$validated) {
                    // Lock the last code row to reduce race windows
                    $validated['patient_code'] = $this->generateNextPatientCode();
                    PatientDetail::create($validated);
                });

                // Success
                return redirect()->route('patients.index')->with('success', 'Patient registered successfully.');
            } catch (QueryException $e) {
                // Unique violation on patient_code -> retry
                if ($this->isUniqueConstraintViolation($e)) {
                    $attempt++;
                    usleep(random_int(10_000, 50_000));
                    continue;
                }
                throw $e;
            }
        } while ($attempt < $maxAttempts);

        return back()->withInput()->withErrors(['patient_code' => 'Could not generate a unique patient code. Please try again.']);
    }

    // Show single patient
    public function show($id)
    {
        $patient = PatientDetail::withTrashed()->findOrFail($id);
        return view('patients.show', compact('patient'));
    }

    // Show form to edit patient
    public function edit(PatientDetail $patient)
    {
        // patient_code shown as read-only in the form
        return view('patients.edit', compact('patient'));
    }

    // Update patient (patient_code is not updatable)
    public function update(Request $request, PatientDetail $patient)
    {
        $validated = $request->validate([
            // patient_code intentionally excluded to prevent updates
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'dob' => ['nullable', 'date'],
            'national_id_number' => ['nullable', 'string', 'max:255', Rule::unique('patient_details', 'national_id_number')->ignore($patient->id)],
            'passport_number' => ['nullable', 'string', 'max:255', Rule::unique('patient_details', 'passport_number')->ignore($patient->id)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Contact & Demographics
            'email' => ['nullable', 'email'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'residential_address' => ['nullable', 'string', 'max:255'],
            'race' => ['nullable', 'string', 'max:50'],
            'religion' => ['nullable', 'string', 'max:50'],
            'language' => ['nullable', 'string', 'max:50'],
            'denomination' => ['nullable', 'string', 'max:50'],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'occupation' => ['nullable', 'string', 'max:100'],

            // Medical Info
            'blood_group' => ['nullable', 'string', 'max:10'],
            'allergies' => ['nullable', 'string', 'max:255'],
            'disabilities' => ['nullable', 'string', 'max:255'],
            'special_diet' => ['nullable', 'string', 'max:255'],
            'medical_aid_provider' => ['nullable', 'string', 'max:100'],
            'medical_aid_number' => ['nullable', 'string', 'max:50'],
            'special_medical_requirements' => ['nullable', 'string'],
            'current_medications' => ['nullable', 'string'],
            'past_medical_history' => ['nullable', 'string'],

            // Next of Kin
            'next_of_kin_name' => ['nullable', 'string', 'max:100'],
            'next_of_kin_relationship' => ['nullable', 'string', 'max:50'],
            'next_of_kin_contact_number' => ['nullable', 'string', 'max:20'],
            'next_of_kin_email' => ['nullable', 'email'],
            'next_of_kin_address' => ['nullable', 'string', 'max:255'],
        ]);

        // Handle photo upload and cleanup old file
        if ($request->hasFile('photo')) {
            if ($patient->photo && Storage::disk('public')->exists($patient->photo)) {
                Storage::disk('public')->delete($patient->photo);
            }
            $validated['photo'] = $request->file('photo')->store('patients', 'public');
        }

        $validated['last_modified_by'] = Auth::id();

        // Never update patient_code here
        $patient->update($validated);

        return redirect()->route('patients.index')->with('success', 'Patient updated successfully.');
    }

    // Soft delete patient
    public function destroy(PatientDetail $patient)
    {
        $patient->delete();
        return redirect()->route('patients.index')->with('success', 'Patient archived.');
    }

    // Restore soft-deleted patient
    public function restore($id)
    {
        $patient = PatientDetail::onlyTrashed()->findOrFail($id);
        $patient->restore();

        return redirect()->route('patients.index', ['status' => 'trashed'])->with('success', 'Patient restored.');
    }

    // Permanently delete patient (and photo)
    public function forceDelete($id)
    {
        $patient = PatientDetail::onlyTrashed()->findOrFail($id);

        if ($patient->photo && Storage::disk('public')->exists($patient->photo)) {
            Storage::disk('public')->delete($patient->photo);
        }

        $patient->forceDelete();

        return redirect()->route('patients.index', ['status' => 'trashed'])->with('success', 'Patient permanently deleted.');
    }

    // Lightweight JSON lookup for patient search (code, name, ID, passport)
    public function lookup(Request $request)
    {
        $q = (string) $request->get('q', '');
        $patients = PatientDetail::query()
            ->select('id', 'patient_code', 'first_name', 'middle_name', 'last_name', 'gender', 'dob', 'contact_number')
            ->when($q, function ($query) use ($q) {
                $query->where('patient_code', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhere('middle_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('national_id_number', 'like', "%{$q}%")
                    ->orWhere('passport_number', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'label' => trim($p->first_name . ' ' . ($p->middle_name ? $p->middle_name . ' ' : '') . $p->last_name) . " ({$p->patient_code})",
                    'code' => $p->patient_code,
                    'name' => trim($p->first_name . ' ' . ($p->middle_name ? $p->middle_name . ' ' : '') . $p->last_name),
                    'gender' => $p->gender,
                    'dob' => optional($p->dob)->format('Y-m-d'),
                    'contact' => $p->contact_number,
                ];
            });

        return response()->json(['data' => $patients]);
    }

    // Predict next code for display only (non-transactional, may be off by one if concurrent)
    protected function predictNextPatientCode(string $prefix = 'PAT', int $pad = 5): string
    {
        $last = PatientDetail::withTrashed()
            ->where('patient_code', 'like', $prefix.'%')
            ->orderBy('patient_code', 'desc')
            ->value('patient_code');

        $num = 0;
        if ($last && preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $last, $m)) {
            $num = (int) $m[1];
        }
        return $prefix . str_pad($num + 1, $pad, '0', STR_PAD_LEFT);
    }

    // Generate next unique code atomically (transaction required by caller)
    protected function generateNextPatientCode(string $prefix = 'PAT', int $pad = 5): string
    {
        // Lock the last matching row to reduce race windows; requires InnoDB and must be inside a transaction
        $last = PatientDetail::withTrashed()
            ->where('patient_code', 'like', $prefix.'%')
            ->orderBy('patient_code', 'desc')
            ->lockForUpdate()
            ->value('patient_code');

        $num = 0;
        if ($last && preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $last, $m)) {
            $num = (int) $m[1];
        }
        return $prefix . str_pad($num + 1, $pad, '0', STR_PAD_LEFT);
    }

    protected function isUniqueConstraintViolation(QueryException $e): bool
    {
        // SQLSTATE 23000: integrity constraint violation
        if (($e->errorInfo[0] ?? null) === '23000') {
            return true;
        }
        // Fallback by message check
        return str_contains(strtolower($e->getMessage()), 'unique') && str_contains($e->getMessage(), 'patient_code');
    }
}
