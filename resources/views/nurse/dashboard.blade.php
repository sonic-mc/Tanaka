@extends('layouts.app')

@section('title', 'Nurse Dashboard')

@section('content')
<div id="nurseDashboard" class="dashboard-section">

    <style>
        /* Calendar styles */
        .calendar-container .card.perspective {
            perspective: 1000px;
            border: 0;
        }
        .calendar-container .card-inner {
            position: relative;
            width: 100%;
            min-height: 420px;
            transform-style: preserve-3d;
            transition: transform 0.6s ease;
        }
        .calendar-container .card-inner.flipped {
            transform: rotateY(180deg);
        }
        .calendar-container .card-face {
            position: absolute;
            inset: 0;
            backface-visibility: hidden;
            background: #fff;
            border-radius: .5rem;
            overflow: hidden;
        }
        .calendar-container .card-back {
            transform: rotateY(180deg);
        }

        .calendar-header {
            border-bottom: 1px solid #f1f3f5;
            background: #fafbfc;
        }
        .calendar-nav {
            cursor: pointer;
            font-size: 1.1rem;
            user-select: none;
            padding: .25rem .5rem;
            border-radius: .25rem;
        }
        .calendar-nav:hover {
            background: #eef2f7;
        }

        .day-header {
            color: #6b7280;
            font-size: .85rem;
            padding-bottom: .25rem;
        }

        .calendar-day {
            height: 44px;
            line-height: 44px;
            text-align: center;
            margin: 2px 0;
            border-radius: .375rem;
            font-weight: 600;
            color: #111827;
            position: relative;
        }
        .calendar-day.other-month {
            color: #9ca3af;
        }
        .calendar-day.today {
            outline: 2px solid #3b82f6;
            outline-offset: -2px;
            background: #eff6ff;
        }
        .calendar-day.has-event::after {
            content: "";
            width: 6px;
            height: 6px;
            background: #ef4444;
            border-radius: 50%;
            position: absolute;
            bottom: 6px;
            left: 50%;
            transform: translateX(-50%);
        }

        .events-indicator {
            position: absolute;
            top: .5rem;
            right: .75rem;
            z-index: 2;
            background: rgba(59,130,246,0.1);
            color: #1d4ed8;
            border: 1px solid rgba(59,130,246,0.25);
            border-radius: 9999px;
            font-size: .8rem;
            padding: .2rem .6rem;
        }
    </style>

    <div class="row">
        <!-- Main Content -->
        <div class="container-fluid px-4 py-4">
            <div class="w-100">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    @php
                        use Carbon\Carbon;
                        $hour = Carbon::now()->format('H');
                        if ($hour < 12) {
                            $greeting = 'Good Morning';
                        } elseif ($hour < 18) {
                            $greeting = 'Good Afternoon';
                        } else {
                            $greeting = 'Good Evening';
                        }
                    @endphp

                    <div>
                        <p class="text-muted mb-1">{{ now()->format('l, d F Y') }}</p>
                        <h2 class="fw-bold mb-1">
                            {{ $greeting }}, {{ Auth::user()->name }}
                        </h2>
                        <p class="text-muted">
                            You have <span class="text-danger fw-bold">{{ $patientsCount ?? 0 }} patients</span> assigned to you today.
                        </p>
                    </div>
                </div>

                <!-- Welcome Card -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="fw-bold mb-2">Patient Care Overview</h4>
                            <p class="mb-0 text-muted">Monitor and track patient progress, incidents, and therapy attendance</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div style="font-size: 70px;">👩‍⚕️</div>
                            <a href="{{ route('admissions.create') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-person-plus me-1"></i> Admit Patient
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="mb-2 rounded-circle d-inline-block p-3" style="background: linear-gradient(135deg, #00d4aa, #00a693);">
                                    <i class="bi bi-people text-white"></i>
                                </div>
                                <h4 class="fw-bold mb-0">{{ $patientsCount }}</h4>
                                <small class="text-muted">Patients</small>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="mb-2 rounded-circle d-inline-block p-3" style="background: linear-gradient(135deg, #f43f5e, #ec4899);">
                                    <i class="bi bi-calendar-check text-white"></i>
                                </div>
                                <h4 class="fw-bold mb-0">{{ $assignedPatients }}</h4>
                                <small class="text-muted">Appointments</small>
                            </div>
                        </div>
                    </div> --}}

                    <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="mb-2 rounded-circle d-inline-block p-3" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                                    <i class="bi bi-clipboard-check text-white"></i>
                                </div>
                                <h4 class="fw-bold mb-0">{{ $reportsCount }}</h4>
                                <small class="text-muted">Reports Today</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="mb-2 rounded-circle d-inline-block p-3" style="background: linear-gradient(135deg, #f59e0b, #f97316);">
                                    <i class="bi bi-exclamation-triangle text-white"></i>
                                </div>
                                <h4 class="fw-bold mb-0">{{ $incidentsCount }}</h4>
                                <small class="text-muted">Incidents</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Patient Requests -->
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold">My Assigned Patients</h5>
                            <a href="{{ route('patients.index') }}" class="text-primary text-decoration-none">Show All →</a>
                        </div>
                    
                        @forelse ($patients as $patient)
                            <div class="request-card card mb-3 shadow-sm border-0">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($patient->first_name . ' ' . $patient->last_name) }}&background=00d4aa&color=fff"
                                             class="rounded-circle me-3" width="50" alt="{{ $patient->first_name }}">
                                        <div>
                                            <h6 class="mb-1">{{ $patient->first_name }} {{ $patient->last_name }}</h6>
                                            <p class="text-muted small mb-0">
                                                {{ $patient->admission_reason ?? 'General Care' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-light text-dark me-2">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            {{ \Carbon\Carbon::parse($patient->admission_date)->format('d M Y') }}
                                        </span>
                                        <span class="badge bg-warning text-dark">
                                            {{ $patient->room_number ?? 'No Room' }}
                                        </span>
                                        <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-sm btn-outline-primary ms-2">
                                            See Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">
                                No patients currently assigned to you.
                            </div>
                        @endforelse
                    </div>
                    

                    <!-- Calendar & Quick Actions -->
                    <div class="col-md-4 calendar-container">
                        <!-- Calendar Card -->
                        <div class="card border-0 shadow-lg perspective mb-4 position-relative">
                            <div class="events-indicator" id="eventsIndicator" style="display:none;">
                                <span id="eventCount">0</span> events
                            </div>

                            <div class="card-inner" id="calendarCard">
                                <!-- Front Face -->
                                <div class="card-face card-front">
                                    <div class="card-body p-0">
                                        <div class="calendar-header p-3 d-flex justify-content-between align-items-center">
                                            <i class="bi bi-chevron-left calendar-nav" id="prevMonth" aria-label="Previous month"></i>
                                            <h5 class="mb-0 fw-bold text-center flex-fill" id="calendarMonthYear"></h5>
                                            <i class="bi bi-chevron-right calendar-nav" id="nextMonth" aria-label="Next month"></i>
                                        </div>

                                        <!-- Day Headers -->
                                        <div class="row g-0 text-center fw-bold px-3 mb-2">
                                            <div class="col day-header">Sun</div>
                                            <div class="col day-header">Mon</div>
                                            <div class="col day-header">Tue</div>
                                            <div class="col day-header">Wed</div>
                                            <div class="col day-header">Thu</div>
                                            <div class="col day-header">Fri</div>
                                            <div class="col day-header">Sat</div>
                                        </div>

                                        <!-- Calendar Days -->
                                        <div id="calendarDays" class="row g-0 px-3"></div>
                                    </div>
                                </div>

                                <!-- Back Face -->
                                <div class="card-face card-back">
                                    <div class="card-body p-0">
                                        <div class="calendar-header p-3 d-flex justify-content-between align-items-center">
                                            <i class="bi bi-chevron-left calendar-nav"></i>
                                            <h5 class="mb-0 fw-bold text-center flex-fill" id="calendarMonthYearBack"></h5>
                                            <i class="bi bi-chevron-right calendar-nav"></i>
                                        </div>

                                        <!-- Day Headers -->
                                        <div class="row g-0 text-center fw-bold px-3 mb-2">
                                            <div class="col day-header">Sun</div>
                                            <div class="col day-header">Mon</div>
                                            <div class="col day-header">Tue</div>
                                            <div class="col day-header">Wed</div>
                                            <div class="col day-header">Thu</div>
                                            <div class="col day-header">Fri</div>
                                            <div class="col day-header">Sat</div>
                                        </div>

                                        <!-- Calendar Days -->
                                        <div id="calendarDaysBack" class="row g-0 px-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-lightning-charge-fill me-2 text-primary"></i> Quick Actions
                                </h6>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('incidents.index') }}" class="btn btn-outline-danger d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-exclamation-circle me-2"></i> Report Incident</span>
                                        <i class="bi bi-arrow-right-circle"></i>
                                    </a>

                                    <a href="{{ route('progress-reports.index') }}" class="btn btn-outline-success d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-plus-circle me-2"></i> Add Progress Note</span>
                                        <i class="bi bi-arrow-right-circle"></i>
                                    </a>

                                    <a href="{{ route('therapy-sessions.index') }}" class="btn btn-outline-warning d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-check2-square me-2"></i> Create Therapy Session</span>
                                        <i class="bi bi-arrow-right-circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div> <!-- /Calendar & Quick Actions -->
                </div> <!-- /row -->
            </div>
        </div>
    </div>
</div>

<script>
    // Optional: provide events from the backend.
    // In your controller, pass $calendarEvents as ['YYYY-MM-DD' => ['Event A','Event B'], ...]
    window.calendarEvents = @json($calendarEvents ?? []);

    class FlipCalendar {
        constructor() {
            this.currentDate = new Date();
            this.currentMonth = this.currentDate.getMonth();
            this.currentYear = this.currentDate.getFullYear();
            this.isAnimating = false;

            this.calendarCard = document.getElementById('calendarCard');
            this.calendarMonthYear = document.getElementById('calendarMonthYear');
            this.calendarMonthYearBack = document.getElementById('calendarMonthYearBack');
            this.calendarDays = document.getElementById('calendarDays');
            this.calendarDaysBack = document.getElementById('calendarDaysBack');
            this.eventsIndicator = document.getElementById('eventsIndicator');
            this.eventCount = document.getElementById('eventCount');

            // Fallback sample events if none provided
            this.events = Object.keys(window.calendarEvents || {}).length ? window.calendarEvents : {
                // samples (safe to remove)
                '2025-10-03': ['Workshop'],
                '2025-10-10': ['Presentation']
            };

            this.init();
        }

        init() {
            // Initial render on the front face
            this.renderCalendar(this.calendarDays, this.currentMonth, this.currentYear, 'front');
            this.updateEventsIndicator();

            // Pre-render the back face with the next month so flip is smooth
            const { month: nextM, year: nextY } = this.offsetMonth(1);
            this.renderCalendar(this.calendarDaysBack, nextM, nextY, 'back');

            // Bind navigation
            document.getElementById('prevMonth').addEventListener('click', () => {
                if (!this.isAnimating) this.flip('prev');
            });
            document.getElementById('nextMonth').addEventListener('click', () => {
                if (!this.isAnimating) this.flip('next');
            });
        }

        monthName(m) {
            return ['January','February','March','April','May','June','July','August','September','October','November','December'][m];
        }

        fmt(y, m, d) {
            return `${y}-${String(m + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        }

        getMonthEventsCount(month, year) {
            const keys = Object.keys(this.events || {});
            return keys.filter(k => {
                const [Y, M] = k.split('-').map(n => parseInt(n, 10));
                return Y === year && (M - 1) === month;
            }).length;
        }

        updateEventsIndicator() {
            const count = this.getMonthEventsCount(this.currentMonth, this.currentYear);
            if (count > 0) {
                this.eventCount.textContent = count;
                this.eventsIndicator.style.display = 'block';
            } else {
                this.eventsIndicator.style.display = 'none';
            }
        }

        offsetMonth(delta) {
            let m = this.currentMonth + delta;
            let y = this.currentYear;
            while (m > 11) { m -= 12; y += 1; }
            while (m < 0)  { m += 12; y -= 1; }
            return { month: m, year: y };
        }

        renderCalendar(target, month, year, face = 'front') {
            const monthYearText = `${this.monthName(month)} ${year}`;
            if (face === 'front') this.calendarMonthYear.textContent = monthYearText;
            if (face === 'back')  this.calendarMonthYearBack.textContent = monthYearText;

            // Build grid
            target.innerHTML = '';
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrevMonth = new Date(year, month, 0).getDate();

            let dayCount = 1;
            let nextMonthDay = 1;
            const totalCells = 42; // 6 weeks

            const tempCells = [];
            for (let i = 0; i < totalCells; i++) {
                const div = document.createElement('div');
                div.className = 'calendar-day';

                if (i < firstDay) {
                    // Previous month
                    const prevDay = daysInPrevMonth - (firstDay - i - 1);
                    div.textContent = prevDay;
                    div.classList.add('other-month');
                } else if (dayCount <= daysInMonth) {
                    // Current month
                    div.textContent = dayCount;

                    const today = new Date();
                    if (dayCount === today.getDate() &&
                        month === today.getMonth() &&
                        year === today.getFullYear()) {
                        div.classList.add('today');
                    }

                    const dateKey = this.fmt(year, month, dayCount);
                    if (this.events[dateKey] && this.events[dateKey].length) {
                        div.classList.add('has-event');
                        div.title = this.events[dateKey].join(', ');
                    }

                    dayCount++;
                } else {
                    // Next month
                    div.textContent = nextMonthDay++;
                    div.classList.add('other-month');
                }

                tempCells.push(div);
            }

            // Convert to 6 rows x 7 cols
            for (let week = 0; week < 6; week++) {
                const row = document.createElement('div');
                row.className = 'row g-0 mb-1';
                for (let day = 0; day < 7; day++) {
                    const col = document.createElement('div');
                    col.className = 'col';
                    const cell = tempCells[week * 7 + day];
                    col.appendChild(cell);
                    row.appendChild(col);
                }
                target.appendChild(row);
            }
        }

        flip(direction) {
            this.isAnimating = true;

            // Compute target month/year
            const delta = direction === 'next' ? 1 : -1;
            const { month: targetMonth, year: targetYear } = this.offsetMonth(delta);

            // Render the back face with target month before flip
            this.renderCalendar(this.calendarDaysBack, targetMonth, targetYear, 'back');

            // Flip
            this.calendarCard.classList.add('flipped');

            // After animation completes, swap faces and update internal state
            setTimeout(() => {
                // Swap inner HTML of faces to keep front as the current month
                const frontGrid = this.calendarDays.innerHTML;
                const backGrid  = this.calendarDaysBack.innerHTML;
                this.calendarDays.innerHTML = backGrid;
                this.calendarDaysBack.innerHTML = frontGrid;

                // Update labels: front must show target after swap
                this.calendarMonthYear.textContent = `${this.monthName(targetMonth)} ${targetYear}`;

                // Set new current
                this.currentMonth = targetMonth;
                this.currentYear = targetYear;

                // Pre-render next back face for smoother subsequent flips
                const { month: nextPrepM, year: nextPrepY } = this.offsetMonth(direction === 'next' ? 1 : -1);
                this.renderCalendar(this.calendarDaysBack, nextPrepM, nextPrepY, 'back');

                // Unflip and unlock
                this.calendarCard.classList.remove('flipped');
                this.updateEventsIndicator();

                setTimeout(() => { this.isAnimating = false; }, 100);
            }, 600); // must match CSS transition duration
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        new FlipCalendar();
    });
</script>
@endsection
