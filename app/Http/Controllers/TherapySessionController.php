<?php

namespace App\Http\Controllers;

use App\Models\TherapySession;
use App\Models\Patient;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;



class TherapySessionController extends Controller
{
    // List all sessions
    public function index()
    {
        $sessions = TherapySession::with(['patient', 'clinician'])->latest()->paginate(10);
        $totalSessions = $sessions->count();
        $completedSessions = $sessions->where('status','Completed')->count();
        $scheduledSessions = $sessions->where('status','Scheduled')->count();
        $cancelledSessions = $sessions->where('status','Canceled')->count();
        $patients = Patient::all();
        $clinicians = User::query()->role('clinician')->get();


        return view('nurse.therapy.index', compact('sessions', 'totalSessions', 'completedSessions', 'scheduledSessions', 'cancelledSessions', 'patients', 'clinicians'));
    }

    // Show create form
    public function create()
    {
        $patients = Patient::all();
        return view('therapy_sessions.create', compact('patients'));
    }

    // Store new session
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'session_start' => 'required|date',
            'session_end' => 'nullable|date|after_or_equal:session_start',
            'session_type' => 'required|string',
            'mode' => 'required|string',
            'presenting_issues' => 'nullable|string',
            'mental_status_exam' => 'nullable|string',
            'interventions' => 'nullable|string',
            'observations' => 'nullable|string',
            'plan' => 'nullable|string',
            'goals_progress' => 'nullable|array',
            'status' => 'required|string',
        ]);

        $validated['clinician_id'] = auth()->id();

        TherapySession::create($validated);

        return redirect()->route('therapy-sessions.index')
            ->with('success', 'Therapy session created successfully.');
    }

    // Show single session
    public function show(TherapySession $therapySession)
    {
        $therapySession->load(['patient', 'clinician']);
        return view('therapy_sessions.show', compact('therapySession'));
    }

    // Show edit form
    public function edit(TherapySession $therapySession)
    {
        $patients = Patient::all();
        return view('therapy_sessions.edit', compact('therapySession', 'patients'));
    }

    // Update session
    public function update(Request $request, TherapySession $therapySession)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'session_start' => 'required|date',
            'session_end' => 'nullable|date|after_or_equal:session_start',
            'session_type' => 'required|string',
            'mode' => 'required|string',
            'presenting_issues' => 'nullable|string',
            'mental_status_exam' => 'nullable|string',
            'interventions' => 'nullable|string',
            'observations' => 'nullable|string',
            'plan' => 'nullable|string',
            'goals_progress' => 'nullable|array',
            'status' => 'required|string',
        ]);

        $therapySession->update($validated);

        return redirect()->route('therapy-sessions.index')
            ->with('success', 'Therapy session updated successfully.');
    }

    // Delete session
    public function destroy(TherapySession $therapySession)
    {
        $therapySession->delete();
        return redirect()->route('therapy-sessions.index')
            ->with('success', 'Therapy session deleted successfully.');
    }
}
