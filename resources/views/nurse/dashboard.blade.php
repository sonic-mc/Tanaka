@extends('layouts.app')

@section('title', 'Nurse Dashboard')

@section('content')
<div id="nurseDashboard" class="dashboard-section">

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
            
                <div class="col-md-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-2 rounded-circle d-inline-block p-3" style="background: linear-gradient(135deg, #f43f5e, #ec4899);">
                                <i class="bi bi-calendar-check text-white"></i>
                            </div>
                            <h4 class="fw-bold mb-0">{{ $assignedPatients}}</h4>
                            <small class="text-muted">Appointments</small>
                        </div>
                    </div>
                </div>
            
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
                        <h5 class="fw-bold">Patient Requests</h5>
                        <a href="#" class="text-primary text-decoration-none">Show All →</a>
                    </div>
            
                    @foreach ([
                        ['name' => 'Emily Chen', 'case' => 'Anxiety Management', 'date' => '10 May 2025', 'time' => '10:00 AM'],
                        ['name' => 'James Wilson', 'case' => 'Depression Treatment', 'date' => '10 May 2025', 'time' => '02:00 PM'],
                        ['name' => 'Maria Garcia', 'case' => 'PTSD Recovery', 'date' => '11 May 2025', 'time' => '11:00 AM']
                    ] as $req)
                    <div class="request-card card mb-3 shadow-sm border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($req['name']) }}&background=00d4aa&color=fff"
                                    class="rounded-circle me-3" width="50" alt="">
                                <div>
                                    <h6 class="mb-1">{{ $req['name'] }}</h6>
                                    <p class="text-muted small mb-0">{{ $req['case'] }}</p>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark me-2">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $req['date'] }}
                                </span>
                                <span class="badge bg-warning text-dark">{{ $req['time'] }}</span>
                                <button class="btn btn-sm btn-outline-primary ms-2">See Details</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            
                <!-- Calendar & Quick Actions -->
                <div class="col-md-4 calendar-container">
                    <!-- Calendar Card -->
                    <div class="card border-0 shadow-lg perspective mb-4">
                        <div class="events-indicator" id="eventsIndicator" style="display: none;">
                            <span id="eventCount">0</span> events
                        </div>
                        
                        <div class="card-inner" id="calendarCard">
                            <!-- Front Face -->
                            <div class="card-face card-front">
                                <div class="card-body p-0">
                                    <div class="calendar-header p-3 d-flex justify-content-between align-items-center">
                                        <i class="bi bi-chevron-left calendar-nav" id="prevMonth"></i>
                                        <h5 class="mb-0 fw-bold text-center flex-fill" id="calendarMonthYear"></h5>
                                        <i class="bi bi-chevron-right calendar-nav" id="nextMonth"></i>
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
                    <div class="quick-actions">
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="#" class="quick-action-btn w-100 d-flex flex-column align-items-center py-2">
                                    <i class="bi bi-calendar-plus fs-4"></i>
                                    <span>Add Event</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="#" class="quick-action-btn w-100 d-flex flex-column align-items-center py-2">
                                    <i class="bi bi-list-ul fs-4"></i>
                                    <span>View All</span>
                                </a>
                            </div>
                        </div>
                        <div class="row g-2 mt-2">
                            <div class="col-6">
                                <a href="#" class="quick-action-btn w-100 d-flex flex-column align-items-center py-2">
                                    <i class="bi bi-clock fs-4"></i>
                                    <span>Reminders</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="#" class="quick-action-btn w-100 d-flex flex-column align-items-center py-2">
                                    <i class="bi bi-calendar-event fs-4"></i>
                                    <span>Today</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            

                    <script>
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
                                
                                // Sample events data
                                this.events = {
                                    '2025-09-15': ['Team Meeting'],
                                    '2025-09-18': ['Project Deadline', 'Client Call'],
                                    '2025-09-22': ['Conference'],
                                    '2025-09-25': ['Review Session'],
                                    '2025-10-03': ['Workshop'],
                                    '2025-10-10': ['Presentation']
                                };
                                
                                this.init();
                            }
                
                            init() {
                                this.renderCalendar(this.calendarDays, this.currentMonth, this.currentYear);
                                this.updateEventsIndicator();
                                this.bindEvents();
                            }
                
                            bindEvents() {
                                document.getElementById('prevMonth').addEventListener('click', () => {
                                    if (!this.isAnimating) {
                                        this.flipCalendar('prev');
                                    }
                                });
                                
                                document.getElementById('nextMonth').addEventListener('click', () => {
                                    if (!this.isAnimating) {
                                        this.flipCalendar('next');
                                    }
                                });
                            }
                
                            renderCalendar(target, month, year) {
                                const monthNames = [
                                    'January', 'February', 'March', 'April', 'May', 'June',
                                    'July', 'August', 'September', 'October', 'November', 'December'
                                ];
                
                                // Update month/year display
                                const monthYearText = `${monthNames[month]} ${year}`;
                                if (target === this.calendarDays) {
                                    this.calendarMonthYear.textContent = monthYearText;
                                } else {
                                    this.calendarMonthYearBack.textContent = monthYearText;
                                }
                
                                // Clear previous days
                                target.innerHTML = '';
                
                                const firstDay = new Date(year, month, 1).getDay();
                                const daysInMonth = new Date(year, month + 1, 0).getDate();
                                const daysInPrevMonth = new Date(year, month, 0).getDate();
                                
                                let dayCount = 1;
                                let nextMonthDay = 1;
                
                                // Calculate total cells needed (6 rows x 7 days)
                                const totalCells = 42;
                
                                for (let i = 0; i < totalCells; i++) {
                                    const dayElement = document.createElement('div');
                                    dayElement.className = 'calendar-day';
                
                                    if (i < firstDay) {
                                        // Previous month days
                                        const prevMonthDay = daysInPrevMonth - (firstDay - i - 1);
                                        dayElement.textContent = prevMonthDay;
                                        dayElement.classList.add('other-month');
                                    } else if (dayCount <= daysInMonth) {
                                        // Current month days
                                        dayElement.textContent = dayCount;
                                        
                                        // Check if it's today
                                        const today = new Date();
                                        if (dayCount === today.getDate() && 
                                            month === today.getMonth() && 
                                            year === today.getFullYear()) {
                                            dayElement.classList.add('today');
                                        }
                                        
                                        // Check for events
                                        const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(dayCount).padStart(2, '0')}`;
                                        if (this.events[dateString]) {
                                            dayElement.classList.add('has-event');
                                            dayElement.title = this.events[dateString].join(', ');
                                        }
                                        
                                        dayCount++;
                                    } else {
                                        // Next month days
                                        dayElement.textContent = nextMonthDay;
                                        dayElement.classList.add('other-month');
                                        nextMonthDay++;
                                    }
                
                                    target.appendChild(dayElement);
                                }
                
                                // Create rows
                                const days = Array.from(target.children);
                                target.innerHTML = '';
                                
                                for (let week = 0; week < 6; week++) {
                                    const weekRow = document.createElement('div');
                                    weekRow.className = 'row g-0 mb-1';
                                    
                                    for (let day = 0; day < 7; day++) {
                                        const dayIndex = week * 7 + day;
                                        if (dayIndex < days.length) {
                                            const col = document.createElement('div');
                                            col.className = 'col';
                                            col.appendChild(days[dayIndex]);
                                            weekRow.appendChild(col);
                                        }
                                    }
                                    
                                    target.appendChild(weekRow);
                                }
                            }
                
                            updateEventsIndicator() {
                                const currentMonthEvents = Object.keys(this.events).filter(date => {
                                    const eventDate = new Date(date);
                                    return eventDate.getMonth() === this.currentMonth && 
                                           eventDate.getFullYear() === this.currentYear;
                                });
                
                                if (currentMonthEvents.length > 0) {
                                    this.eventCount.textContent = currentMonthEvents.length;
                                    this.eventsIndicator.style.display = 'block';
                                } else {
                                    this.eventsIndicator.style.display = 'none';
                                }
                            }
                
                            flipCalendar(direction) {
                                this.isAnimating = true;
                                
                                // Start flip animation
                                this.calendarCard.classList.add('flipped');
                
                                setTimeout(() => {
                                    // Update month/year
                                    if (direction === 'next') {
                                        this.currentMonth++;
                                        if (this.currentMonth > 11) {
                                            this.currentMonth = 0;
                                            this.currentYear++;
                                        }
                                    } else {
                                        this.currentMonth--;
                                        if (this.currentMonth < 0) {
                                            this.currentMonth = 11;
                                            this.currentYear--;
                                        }
                                    }
                
                                    // Render new calendar on the front face (which is now hidden)
                                    this.renderCalendar(this.calendarDays, this.currentMonth, this.currentYear);
                                    this.updateEventsIndicator();
                                    
                                    // Flip back to show the updated calendar
                                    this.calendarCard.classList.remove('flipped');
                                    
                                    setTimeout(() => {
                                        this.isAnimating = false;
                                    }, 600);
                                }, 300);
                
                                // Render the back face with new content
                                setTimeout(() => {
                                    let newMonth = this.currentMonth;
                                    let newYear = this.currentYear;
                                    
                                    if (direction === 'next') {
                                        newMonth++;
                                        if (newMonth > 11) {
                                            newMonth = 0;
                                            newYear++;
                                        }
                                    } else {
                                        newMonth--;
                                        if (newMonth < 0) {
                                            newMonth = 11;
                                            newYear--;
                                        }
                                    }
                                    
                                    this.renderCalendar(this.calendarDaysBack, newMonth, newYear);
                                }, 50);
                            }
                        }
                
                        // Initialize calendar when DOM is loaded
                        document.addEventListener('DOMContentLoaded', () => {
                            new FlipCalendar();
                        });
                    </script>
        
                
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
        
                </div>
            </div>

        </div>
    </div>
    </div>

</div>


@endsection



