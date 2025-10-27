@php
  $newPatients = $notifications['new_patients'] ?? collect();
  $pendingCount = $notifications['pending_unevaluated_count'] ?? 0;
  $windowDays   = $notifications['window_days'] ?? 7;
@endphp

<div class="col-lg-6 mb-4">
  <div class="card modern-card h-100 position-relative">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="chart-title mb-0">Notifications</h5>
        <button id="notifBell" class="btn btn-outline-secondary rounded-circle position-relative" title="Notifications">
          <i class="bi bi-bell"></i>
          @if($pendingCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              {{ $pendingCount }}
            </span>
          @endif
        </button>
      </div>

      <p class="text-muted mb-3">
        Recently registered patients ({{ $windowDays }} day{{ $windowDays == 1 ? '' : 's' }})  
        • Awaiting evaluation: <strong>{{ $pendingCount }}</strong>
      </p>

      <div id="notifPanel" class="border rounded p-3 shadow-sm" style="display:none; max-height:380px; overflow-y:auto;">
        @forelse($newPatients as $patient)
          <div class="d-flex justify-content-between align-items-start border-bottom py-2">
            <div>
              <strong>{{ $patient->full_name ?? ($patient->first_name.' '.$patient->last_name) }}</strong><br>
              <small class="text-muted">
                Code: {{ $patient->patient_code }} • Registered {{ $patient->created_at->diffForHumans() }}
              </small>
            </div>
            <div class="d-flex flex-column gap-1">
              <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-sm btn-light">View</a>
              <a href="{{ route('evaluations.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary">Evaluate</a>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-3">
            No recently registered unevaluated patients.
          </div>
        @endforelse

        <div class="text-end mt-3">
          <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-sm">View all patients</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  const bell = document.getElementById('notifBell');
  const panel = document.getElementById('notifPanel');
  if (!bell || !panel) return;

  const toggle = (open) => {
    panel.style.display = open ? 'block' : 'none';
    bell.setAttribute('aria-expanded', open);
  };

  bell.addEventListener('click', e => {
    e.stopPropagation();
    toggle(panel.style.display !== 'block');
  });
  document.addEventListener('click', () => toggle(false));
  panel.addEventListener('click', e => e.stopPropagation());
  document.addEventListener('keydown', e => e.key === 'Escape' && toggle(false));
})();
</script>
