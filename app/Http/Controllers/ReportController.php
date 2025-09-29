<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\{
    Patient,
    Discharge,
    Evaluation,
    IncidentReport,
    ProgressReport,
    BillingStatement,
    TherapySession,
    Medication,
    Invoice,
    Payment,
    Prescription,
    Appointment
};

class ReportController extends Controller
{
    /**
     * Show report preview.
     */
    public function index(Request $request)
    {
        $modules = $request->input('modules', []);
        $patientId = $request->input('patient_id');
        $data = [];

        // Fetch per patient if selected
        if ($patientId) {
            $patient = Patient::findOrFail($patientId);

            if (in_array('patients', $modules)) {
                $data['patient'] = $patient;
            }
            if (in_array('discharges', $modules)) {
                $data['discharges'] = Discharge::where('patient_id', $patientId)->get();
            }
            if (in_array('evaluations', $modules)) {
                $data['evaluations'] = Evaluation::where('patient_id', $patientId)->get();
            }
            if (in_array('incident_reports', $modules)) {
                $data['incident_reports'] = IncidentReport::where('patient_id', $patientId)->get();
            }
            if (in_array('progress_reports', $modules)) {
                $data['progress_reports'] = ProgressReport::where('patient_id', $patientId)->get();
            }
            if (in_array('billing_statements', $modules)) {
                $data['billing_statements'] = BillingStatement::where('patient_id', $patientId)->get();
            }
            if (in_array('therapy_sessions', $modules)) {
                $data['therapy_sessions'] = TherapySession::where('patient_id', $patientId)->get();
            }
            if (in_array('invoices', $modules)) {
                $data['invoices'] = Invoice::where('patient_id', $patientId)->get();
            }
            if (in_array('payments', $modules)) {
                $data['payments'] = Payment::where('patient_id', $patientId)->get();
            }
            if (in_array('prescriptions', $modules)) {
                $data['prescriptions'] = Prescription::where('patient_id', $patientId)->get();
            }
            if (in_array('appointments', $modules)) {
                $data['appointments'] = Appointment::where('patient_id', $patientId)->get();
            }

        } else {
            // System-wide reports
            if (in_array('patients', $modules)) {
                $data['patients'] = Patient::latest()->take(20)->get();
            }
            if (in_array('discharges', $modules)) {
                $data['discharges'] = Discharge::latest()->take(20)->get();
            }
            if (in_array('evaluations', $modules)) {
                $data['evaluations'] = Evaluation::latest()->take(20)->get();
            }
            if (in_array('incident_reports', $modules)) {
                $data['incident_reports'] = IncidentReport::latest()->take(20)->get();
            }
            if (in_array('progress_reports', $modules)) {
                $data['progress_reports'] = ProgressReport::latest()->take(20)->get();
            }
            if (in_array('billing_statements', $modules)) {
                $data['billing_statements'] = BillingStatement::latest()->take(20)->get();
            }
            if (in_array('therapy_sessions', $modules)) {
                $data['therapy_sessions'] = TherapySession::latest()->take(20)->get();
            }
            if (in_array('medications', $modules)) {
                $data['medications'] = Medication::latest()->take(20)->get();
            }
            if (in_array('invoices', $modules)) {
                $data['invoices'] = Invoice::latest()->take(20)->get();
            }
            if (in_array('payments', $modules)) {
                $data['payments'] = Payment::latest()->take(20)->get();
            }
            if (in_array('prescriptions', $modules)) {
                $data['prescriptions'] = Prescription::latest()->take(20)->get();
            }
            if (in_array('appointments', $modules)) {
                $data['appointments'] = Appointment::latest()->take(20)->get();
            }
        }

        return view('reports.index', compact('data', 'modules', 'patientId'));
    }

    /**
     * Export reports as PDF.
     */
    public function export(Request $request)
    {
        $modules = $request->input('modules', []);
        $patientId = $request->input('patient_id');
        $data = [];

        // reuse same logic from index
        $request->merge(['modules' => $modules, 'patient_id' => $patientId]);
        $data = $this->index($request)->getData()['data'];

        $pdf = Pdf::loadView('reports.pdf', compact('data', 'modules', 'patientId'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('reports.pdf');
    }
}
