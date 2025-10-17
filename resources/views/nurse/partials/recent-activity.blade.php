<div class="col-lg-6 mb-4">
    <div class="card modern-card chart-card">
        <h5 class="chart-title d-flex justify-content-between align-items-center">
            Recent Activity
            <span class="badge bg-primary">{{ number_format($notificationCount ?? 0) }} new</span>
        </h5>

        @php
            // Icon map by module or fallback by severity
            $moduleIcons = [
                'patients' => ['icon' => 'fas fa-user-plus',        'class' => 'text-primary'],
                'admissions' => ['icon' => 'fas fa-user-plus',      'class' => 'text-primary'],
                'progress' => ['icon' => 'fas fa-file-medical',     'class' => 'text-info'],
                'reports'  => ['icon' => 'fas fa-file-medical',     'class' => 'text-info'],
                'billing'  => ['icon' => 'fas fa-credit-card',      'class' => 'text-primary'],
                'payments' => ['icon' => 'fas fa-check-circle',     'class' => 'text-success'],
                'incidents'=> ['icon' => 'fas fa-exclamation-triangle','class' => 'text-warning'],
                'auth'     => ['icon' => 'fas fa-sign-in-alt',      'class' => 'text-secondary'],
                'queue'    => ['icon' => 'fas fa-tasks',            'class' => 'text-secondary'],
            ];
            $severityFallback = [
                'info'     => ['icon' => 'fas fa-info-circle',       'class' => 'text-info'],
                'warning'  => ['icon' => 'fas fa-exclamation-triangle','class' => 'text-warning'],
                'critical' => ['icon' => 'fas fa-times-circle',      'class' => 'text-danger'],
            ];

            // Helper: derive a human line from description (JSON or plain)
            $describe = function ($log) {
                $raw = $log->description;
                if (is_string($raw) && strlen($raw) > 0) {
                    $firstChar = substr(trim($raw), 0, 1);
                    if ($firstChar === '{' || $firstChar === '[') {
                        $decoded = json_decode($raw, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            if (is_array($decoded)) {
                                // Prefer 'message' if available
                                if (isset($decoded['message']) && is_string($decoded['message'])) {
                                    return $decoded['message'];
                                }
                                // Otherwise build a brief from top-level keys
                                return 'Details: ' . implode(', ', array_map(
                                    fn($k, $v) => $k . ': ' . (is_scalar($v) ? $v : (is_array($v) ? '[...]' : (is_object($v) ? '{...}' : ''))),
                                    array_keys($decoded), array_values($decoded)
                                ));
                            }
                        }
                    }
                    // Plain text fallback
                    return $raw;
                }
                return null;
            };
        @endphp

        @forelse(($auditLogs ?? []) as $log)
            @php
                $iconData = $moduleIcons[$log->module] ?? $severityFallback[strtolower($log->severity) ?: 'info'] ?? $severityFallback['info'];
                $line1 = $log->action; // e.g. "Created invoice"
                $line2 = $describe($log) ?: ($log->module ? ucfirst($log->module) : 'System event');
                $when  = $log->timestamp?->diffForHumans() ?? '';
                $sevClass = match (strtolower($log->severity)) {
                    'warning'  => 'badge bg-warning text-dark',
                    'critical' => 'badge bg-danger',
                    default    => 'badge bg-secondary',
                };
            @endphp
            <div class="activity-item d-flex align-items-start mb-3">
                <i class="{{ $iconData['icon'] }} {{ $iconData['class'] }} me-3 mt-1" aria-hidden="true"></i>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <div class="fw-semibold">
                            {{ $line1 }}
                            @if($log->module)
                                <span class="ms-2 {{ $sevClass }}">{{ ucfirst($log->module) }}</span>
                            @endif
                        </div>
                        <small class="text-muted">{{ $when }}</small>
                    </div>
                    <small class="text-muted d-block">
                        {!! e($line2) !!}
                        @if($log->user)
                            <span class="ms-2">• by {{ $log->user->name }}</span>
                        @endif
                    </small>
                </div>
            </div>
        @empty
            <div class="text-muted">No recent activity.</div>
        @endforelse
    </div>
</div>
