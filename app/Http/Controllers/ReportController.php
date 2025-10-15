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
            $patient = Patient::with(['admittedBy', 'assignedNurse', 'careLevel'])->findOrFail($patientId);
    
            if (in_array('patients', $modules)) {
                $data['patient'] = $patient;
            }
            if (in_array('discharges', $modules)) {
                $data['discharges'] = Discharge::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('evaluations', $modules)) {
                $data['evaluations'] = Evaluation::with('patient', 'evaluator')->where('patient_id', $patientId)->get();
            }
            if (in_array('incident_reports', $modules)) {
                $data['incident_reports'] = IncidentReport::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('progress_reports', $modules)) {
                $data['progress_reports'] = ProgressReport::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('billing_statements', $modules)) {
                $data['billing_statements'] = BillingStatement::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('therapy_sessions', $modules)) {
                $data['therapy_sessions'] = TherapySession::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('invoices', $modules)) {
                $data['invoices'] = Invoice::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('payments', $modules)) {
                $data['payments'] = Payment::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('prescriptions', $modules)) {
                $data['prescriptions'] = Prescription::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('appointments', $modules)) {
                $data['appointments'] = Appointment::with('patient')->where('patient_id', $patientId)->get();
            }
    
        } else {
            // System-wide reports
            if (in_array('patients', $modules)) {
                $data['patients'] = Patient::with(['admittedBy', 'assignedNurse', 'careLevel'])->latest()->take(20)->get();
            }
            if (in_array('discharges', $modules)) {
                $data['discharges'] = Discharge::with('patient')->latest()->take(20)->get();
            }
            if (in_array('evaluations', $modules)) {
                $data['evaluations'] = Evaluation::with('patient', 'evaluator')->latest()->take(20)->get();
            }
            if (in_array('incident_reports', $modules)) {
                $data['incident_reports'] = IncidentReport::with('patient')->latest()->take(20)->get();
            }
            if (in_array('progress_reports', $modules)) {
                $data['progress_reports'] = ProgressReport::with('patient')->latest()->take(20)->get();
            }
            if (in_array('billing_statements', $modules)) {
                $data['billing_statements'] = BillingStatement::with('patient')->latest()->take(20)->get();
            }
            if (in_array('therapy_sessions', $modules)) {
                $data['therapy_sessions'] = TherapySession::with('patient')->latest()->take(20)->get();
            }
            if (in_array('medications', $modules)) {
                $data['medications'] = Medication::latest()->take(20)->get();
            }
            if (in_array('invoices', $modules)) {
                $data['invoices'] = Invoice::with('patient')->latest()->take(20)->get();
            }
            if (in_array('payments', $modules)) {
                $data['payments'] = Payment::with('patient')->latest()->take(20)->get();
            }
            if (in_array('prescriptions', $modules)) {
                $data['prescriptions'] = Prescription::with('patient')->latest()->take(20)->get();
            }
            if (in_array('appointments', $modules)) {
                $data['appointments'] = Appointment::with('patient')->latest()->take(20)->get();
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
    
        if ($patientId) {
            $patient = Patient::with(['admittedBy', 'assignedNurse', 'careLevel'])->findOrFail($patientId);
    
            if (in_array('patients', $modules)) {
                $data['patient'] = $patient;
            }
            if (in_array('discharges', $modules)) {
                $data['discharges'] = Discharge::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('evaluations', $modules)) {
                $data['evaluations'] = Evaluation::with('patient', 'evaluator')->where('patient_id', $patientId)->get();
            }
            if (in_array('incident_reports', $modules)) {
                $data['incident_reports'] = IncidentReport::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('progress_reports', $modules)) {
                $data['progress_reports'] = ProgressReport::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('billing_statements', $modules)) {
                $data['billing_statements'] = BillingStatement::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('therapy_sessions', $modules)) {
                $data['therapy_sessions'] = TherapySession::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('invoices', $modules)) {
                $data['invoices'] = Invoice::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('payments', $modules)) {
                $data['payments'] = Payment::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('prescriptions', $modules)) {
                $data['prescriptions'] = Prescription::with('patient')->where('patient_id', $patientId)->get();
            }
            if (in_array('appointments', $modules)) {
                $data['appointments'] = Appointment::with('patient')->where('patient_id', $patientId)->get();
            }
    
        } else {
            if (in_array('patients', $modules)) {
                $data['patients'] = Patient::with(['admittedBy', 'assignedNurse', 'careLevel'])->latest()->take(20)->get();
            }
            if (in_array('discharges', $modules)) {
                $data['discharges'] = Discharge::with('patient')->latest()->take(20)->get();
            }
            if (in_array('evaluations', $modules)) {
                $data['evaluations'] = Evaluation::with('patient', 'evaluator')->latest()->take(20)->get();
            }
            if (in_array('incident_reports', $modules)) {
                $data['incident_reports'] = IncidentReport::with('patient')->latest()->take(20)->get();
            }
            if (in_array('progress_reports', $modules)) {
                $data['progress_reports'] = ProgressReport::with('patient')->latest()->take(20)->get();
            }
            if (in_array('billing_statements', $modules)) {
                $data['billing_statements'] = BillingStatement::with('patient')->latest()->take(20)->get();
            }
            if (in_array('therapy_sessions', $modules)) {
                $data['therapy_sessions'] = TherapySession::with('patient')->latest()->take(20)->get();
            }
            if (in_array('medications', $modules)) {
                $data['medications'] = Medication::latest()->take(20)->get();
            }
            if (in_array('invoices', $modules)) {
                $data['invoices'] = Invoice::with('patient')->latest()->take(20)->get();
            }
            if (in_array('payments', $modules)) {
                $data['payments'] = Payment::with('patient')->latest()->take(20)->get();
            }
            if (in_array('prescriptions', $modules)) {
                $data['prescriptions'] = Prescription::with('patient')->latest()->take(20)->get();
            }
            if (in_array('appointments', $modules)) {
                $data['appointments'] = Appointment::with('patient')->latest()->take(20)->get();
            }
        }
    
        $pdf = Pdf::loadView('reports.pdf', compact('data', 'modules', 'patientId'));
        return $pdf->download('report.pdf');
    }
    
}
