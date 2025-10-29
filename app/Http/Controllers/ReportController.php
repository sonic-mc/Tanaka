<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\{
    Admission,
    PatientDetail,
    DischargedPatient,
    PatientEvaluation,
    IncidentReport,
    PatientProgressReport,
    BillingStatement,
    TherapySession,
    Invoice,
    InvoicePayment
};

class ReportController extends Controller
{
    // Central list of supported modules for both preview and export
    private const ALLOWED_MODULES = [
        'patients',
        'admissions',
        'discharges',
        'evaluations',
        'incident_reports',
        'progress_reports',
        'billing_statements',
        'therapy_sessions',
        'invoices',
        'payments',
    ];

    /**
     * Show report preview.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'modules' => ['array'],
            'modules.*' => ['in:' . implode(',', self::ALLOWED_MODULES)],
            'patient_id' => ['nullable', 'exists:patient_details,id'],
        ]);

        $modules = $validated['modules'] ?? [];
        $patientId = $validated['patient_id'] ?? null;
        $patient = null;

        if ($patientId) {
            $patient = PatientDetail::findOrFail($patientId);
        }

        $data = $this->buildReportData($modules, $patientId);

        return view('reports.index', [
            'data' => $data,
            'modules' => $modules,
            'patientId' => $patientId,
            'patient' => $patient,
            'allowedModules' => self::ALLOWED_MODULES,
        ]);
    }

    /**
     * Export reports as PDF.
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'modules' => ['array'],
            'modules.*' => ['in:' . implode(',', self::ALLOWED_MODULES)],
            'patient_id' => ['nullable', 'exists:patient_details,id'],
        ]);

        $modules = $validated['modules'] ?? [];
        $patientId = $validated['patient_id'] ?? null;
        $patient = null;

        if ($patientId) {
            $patient = PatientDetail::findOrFail($patientId);
        }

        $data = $this->buildReportData($modules, $patientId);

        $pdf = Pdf::loadView('reports.pdf', [
            'data' => $data,
            'modules' => $modules,
            'patientId' => $patientId,
            'patient' => $patient,
        ]);

        return $pdf->download('report.pdf');
    }

    /**
     * Build the report data map keyed by module.
     * - Returns only modules requested.
     * - Applies per-patient filtering when patientId is provided.
     */
    private function buildReportData(array $modules, ?int $patientId): array
    {
        if (empty($modules)) {
            return [];
        }

        $modules = array_values(array_intersect($modules, self::ALLOWED_MODULES));

        $data = [];

        $limit = 50; // sane cap for system-wide reports

        $perPatient = function ($query) use ($patientId) {
            return $patientId ? $query->where('patient_id', $patientId) : $query;
        };

        foreach ($modules as $module) {
            switch ($module) {
                case 'patients':
                    // For preview, we don’t need heavy relations; include creator/lastModifier for display
                    $query = PatientDetail::with(['creator', 'lastModifier'])->orderByDesc('id');
                    if ($patientId) {
                        $data['patient'] = PatientDetail::with(['creator', 'lastModifier'])->find($patientId);
                    } else {
                        $data['patients'] = $query->take($limit)->get();
                    }
                    break;

                case 'admissions':
                    $query = Admission::with(['patient', 'evaluation', 'careLevel', 'assignedNurses']);
                    $data['admissions'] = $perPatient($query)->latest()->take($limit)->get();
                    break;

                case 'discharges':
                    $query = DischargedPatient::with(['patient', 'dischargedBy']); // patient relation fixed in model below
                    $data['discharges'] = $perPatient($query)->latest()->take($limit)->get();
                    break;

                case 'evaluations':
                    $query = PatientEvaluation::with(['patient', 'psychiatrist', 'creator', 'lastModifier']);
                    $data['evaluations'] = $perPatient($query)->latest()->take($limit)->get();
                    break;

                case 'incident_reports':
                    $query = IncidentReport::with(['patient', 'reportedBy']);
                    $data['incident_reports'] = $perPatient($query)->latest()->take($limit)->get();
                    break;

                case 'progress_reports':
                    $query = PatientProgressReport::with(['patient', 'admission', 'evaluation', 'clinician', 'creator']);
                    $data['progress_reports'] = $perPatient($query)->latest()->take($limit)->get();
                    break;

                case 'billing_statements':
                    $query = BillingStatement::with(['patient']);
                    $data['billing_statements'] = $perPatient($query)->latest('last_updated')->take($limit)->get();
                    break;

                case 'therapy_sessions':
                    $query = TherapySession::with(['patient', 'clinician']);
                    $data['therapy_sessions'] = $perPatient($query)->latest('session_start')->take($limit)->get();
                    break;

                case 'invoices':
                    $query = Invoice::with(['patient', 'payments', 'creator']);
                    $data['invoices'] = $perPatient($query)->latest('issue_date')->take($limit)->get();
                    break;

                case 'payments':
                    $query = InvoicePayment::with(['patient', 'invoice', 'receiver']);
                    $data['payments'] = $perPatient($query)->latest('paid_at')->take($limit)->get();
                    break;
            }
        }

        return $data;
    }
}
