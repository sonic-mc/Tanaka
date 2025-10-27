@php
  $newPatients = $notifications['new_patients'] ?? collect();
  $pendingCount = $notifications['pending_unevaluated_count'] ?? 0;
  $windowDays   = $notifications['window_days'] ?? 7;
@endphp

<style>
  .notif-bell { position: relative; display: inline-block; cursor: pointer; }
  .notif-badge {
    position: absolute; top: -6px; right: -6px;
    background: #dc2626; color: #fff; border-radius: 9999px;
    font-size: 12px; padding: 3px 6px; min-width: 20px; text-align: center;
  }
  .notif-panel {
    position: absolute; right: 0; margin-top: 8px; width: 360px;
    background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
    box-shadow: 0 10px 20px rgba(0,0,0,.08);
    z-index: 50; display: none;
  }
  .notif-header { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; }
  .notif-list { max-height: 380px; overflow-y:auto; }
  .notif-item { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; display:flex; gap:10px; }
  .notif-empty { padding: 16px; text-align: center; color: #6b7280; }
  .notif-actions a {
    display:inline-block; font-size: 12px; padding: 6px 10px;
    border-radius: 6px; text-decoration: none; border: 1px solid #e5e7eb;
    background: #f9fafb; color: #111;
  }
  .notif-actions a:hover { background: #f3f4f6; }
  .notif-footer { padding: 8px 12px; text-align: right; }
</style>

<div class="notif-wrapper" style="position: relative;">
  <button id="notifBell" class="notif-bell" aria-haspopup="true" aria-expanded="false" aria-controls="notifPanel" title="Notifications">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
      <path d="M12 3a6 6 0 0 0-6 6v2.764c0 .53-.21 1.039-.586 1.414L4 14.592V16h16v-1.408l-1.414-1.414A2 2 0 0 1 18 11.764V9a6 6 0 0 0-6-6Z" stroke="#111" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M9 18a3 3 0 0 0 6 0" stroke="#111" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    @if($pendingCount > 0)
      <span class="notif-badge">{{ $pendingCount }}</span>
    @endif
  </button>

  <div id="notifPanel" class="notif-panel">
    <div class="notif-header">
      <div>
        <div style="font-weight:600;">Recently Registered ({{ $windowDays }} day{{ $windowDays == 1 ? '' : 's' }})</div>
        <div style="font-size:12px; color:#6b7280;">Total awaiting evaluation: {{ $pendingCount }}</div>
      </div>
    </div>

    <div class="notif-list">
      @forelse($newPatients as $patient)
        <div class="notif-item">
          <div style="flex:1;">
            <div style="font-weight:600;">
              {{ $patient->full_name ?? ($patient->first_name.' '.$patient->last_name) }}
            </div>
            <div style="font-size:12px; color:#6b7280;">
              Code: {{ $patient->patient_code }} • Registered {{ $patient->created_at->diffForHumans() }}
            </div>
          </div>
          <div class="notif-actions">
            <a href="{{ route('patients.show', $patient->id, false) }}">View</a>
            <a href="{{ route('evaluations.create', $patient->id, false) }}">Evaluate</a>
          </div>
        </div>
      @empty
        <div class="notif-empty">No recently registered unevaluated patients.</div>
      @endforelse
    </div>

    <div class="notif-footer">
      <a href="{{ route('patients.index', [], false) }}" class="notif-actions">View all patients</a>
    </div>
  </div>
</div>

<script>
  (() => {
    const bell = document.getElementById('notifBell');
    const panel = document.getElementById('notifPanel');
    if (!bell || !panel) return;

    function toggle(open) {
      panel.style.display = open ? 'block' : 'none';
      bell.setAttribute('aria-expanded', open);
    }

    bell.addEventListener('click', e => {
      e.stopPropagation();
      toggle(panel.style.display !== 'block');
    });
    document.addEventListener('click', () => toggle(false));
    panel.addEventListener('click', e => e.stopPropagation());
    document.addEventListener('keydown', e => e.key === 'Escape' && toggle(false));
  })();
</script>
