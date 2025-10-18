<?php

namespace App\Http\Controllers;

use App\Models\TherapySession;
use App\Models\Patient;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Traits\AuditLogger;

class TherapySessionController extends Controller
{
    use AuditLogger;

    public function __construct()
    {
        $this->middleware('auth');
    }

    // List all sessions
    public function index()
    {
        $sessions = TherapySession::with(['patient', 'clinician'])
            ->orderBy('session_start', 'desc')
            ->paginate(10);

        // Totals across all sessions (not just current page)
        $totalSessions = TherapySession::count();
        $completedSessions = TherapySession::where('status', 'Completed')->count();
        $scheduledSessions = TherapySession::where('status', 'Scheduled')->count();
        $cancelledSessions = TherapySession::where('status', 'Canceled')->count();

        $patients = Patient::orderBy('first_name')->orderBy('last_name')->get();
        // Build the selectable “clinicians” from users.role = psychiatrist or nurse
        $clinicians = User::clinicalStaff()->orderBy('name')->get();

        // Only admins can assign clinician; nurses/psychiatrists auto-assign to themselves
        $canAssignClinician = Auth::user()->role === 'admin';

        return view('nurse.therapy.index', compact(
            'sessions',
            'totalSessions',
            'completedSessions',
            'scheduledSessions',
            'cancelledSessions',
            'patients',
            'clinicians',
            'canAssignClinician'
        ));
    }

    // Optional standalone create route: we keep create inside the tab on index
    public function create()
    {
        return redirect()->route('therapy-sessions.index')->with('info', 'Use the Create Session tab to add a new session.');
    }

    // Store new session
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'clinician_id' => 'nullable|exists:users,id', // admin can pick; clinicians self-assign
            'session_start' => 'required|date',
            'session_end' => 'nullable|date|after_or_equal:session_start',
            'session_type' => 'required|string|in:individual,group,family',
            'mode' => 'required|string|in:in-person,online',
            'session_number' => 'nullable|integer|min:1',
            'presenting_issues' => 'nullable|string',
            'mental_status_exam' => 'nullable|string',
            'interventions' => 'nullable|string',
            'observations' => 'nullable|string',
            'plan' => 'nullable|string',
            'goals_progress' => 'nullable', // parse JSON manually
            'status' => 'required|string|in:Scheduled,Completed,Canceled',
        ]);

        // Determine clinician assignment
        if (Auth::user()->role === 'admin') {
            // Admin may choose from psychiatrists or nurses; fallback to self if none provided
            $validated['clinician_id'] = $validated['clinician_id'] ?? Auth::id();
        } else {
            // Nurses/Psychiatrists self-assign
            $validated['clinician_id'] = Auth::id();
        }

        // Parse goals_progress JSON from textarea
        $goalsRaw = $request->input('goals_progress');
        if (is_string($goalsRaw) && strlen(trim($goalsRaw)) > 0) {
            $decoded = json_decode($goalsRaw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withInput()->withErrors(['goals_progress' => 'Goals Progress must be valid JSON.']);
            }
            $validated['goals_progress'] = $decoded;
        } else {
            $validated['goals_progress'] = null;
        }

        $session = TherapySession::create($validated);

        if (method_exists($this, 'audit')) {
            $this->audit('therapy_session_created', [
                'therapy_session_id' => $session->id,
                'by_user_id' => Auth::id(),
            ]);
        }

        return redirect()->route('therapy-sessions.index')
            ->with('success', 'Therapy session created successfully.');
    }

    // Show single session
    public function show(TherapySession $therapySession)
    {
        $therapySession->load(['patient', 'clinician']);
        return view('nurse.therapy.show', compact('therapySession'));
    }

    // Show edit form
    public function edit(TherapySession $therapySession)
    {
        $therapySession->load(['patient', 'clinician']);
        $patients = Patient::orderBy('first_name')->orderBy('last_name')->get();
        $clinicians = User::clinicalStaff()->orderBy('name')->get();

        // Only admins can assign a clinician in edit
        $canAssignClinician = Auth::user()->role === 'admin';

        return view('nurse.therapy.edit', compact('therapySession', 'patients', 'clinicians', 'canAssignClinician'));
    }

    // Update session
    public function update(Request $request, TherapySession $therapySession)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'clinician_id' => 'nullable|exists:users,id',
            'session_start' => 'required|date',
            'session_end' => 'nullable|date|after_or_equal:session_start',
            'session_type' => 'required|string|in:individual,group,family',
            'mode' => 'required|string|in:in-person,online',
            'session_number' => 'nullable|integer|min:1',
            'presenting_issues' => 'nullable|string',
            'mental_status_exam' => 'nullable|string',
            'interventions' => 'nullable|string',
            'observations' => 'nullable|string',
            'plan' => 'nullable|string',
            'goals_progress' => 'nullable',
            'status' => 'required|string|in:Scheduled,Completed,Canceled',
        ]);

        if (Auth::user()->role === 'admin') {
            // Admin may reassign to a psychiatrist or nurse
            $validated['clinician_id'] = $validated['clinician_id'] ?? $therapySession->clinician_id;
        } else {
            // Nurses/Psychiatrists keep themselves
            $validated['clinician_id'] = Auth::id();
        }

        $goalsRaw = $request->input('goals_progress');
        if (is_string($goalsRaw) && strlen(trim($goalsRaw)) > 0) {
            $decoded = json_decode($goalsRaw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withInput()->withErrors(['goals_progress' => 'Goals Progress must be valid JSON.']);
            }
            $validated['goals_progress'] = $decoded;
        } else {
            $validated['goals_progress'] = null;
        }

        $therapySession->update($validated);

        if (method_exists($this, 'audit')) {
            $this->audit('therapy_session_updated', [
                'therapy_session_id' => $therapySession->id,
                'by_user_id' => Auth::id(),
            ]);
        }

        return redirect()->route('therapy-sessions.index')
            ->with('success', 'Therapy session updated successfully.');
    }

    // Delete session
    public function destroy(TherapySession $therapySession)
    {
        $therapySession->delete();

        if (method_exists($this, 'audit')) {
            $this->audit('therapy_session_deleted', [
                'therapy_session_id' => $therapySession->id,
                'by_user_id' => Auth::id(),
            ]);
        }

        return redirect()->route('therapy-sessions.index')
            ->with('success', 'Therapy session deleted successfully.');
    }
}
