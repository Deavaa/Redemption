@extends('layouts.admin')
@section('title', 'Academic Calendar')

@push('styles')
<style>
.cal-page { animation: calFadeIn 0.4s ease-out; }
@keyframes calFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

.cal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.cal-header-left { flex: 1; }
.cal-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.cal-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }

.cal-layout { display: grid; grid-template-columns: 1fr 340px; gap: 1.25rem; align-items: start; }
.cal-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
.cal-card-header { padding: 1rem 1.25rem; border-bottom: 1px solid #f0f0f0; background: #fafbfc; font-weight: 700; color: #1a1a2e; font-size: 0.92rem; display: flex; align-items: center; gap: 0.5rem; }
.cal-card-body { padding: 1.25rem; }

/* FullCalendar overrides */
#calendar { font-family: 'Inter', sans-serif; }
#calendar .fc-toolbar-title { font-size: 1.15rem !important; font-weight: 700 !important; }
#calendar .fc-button { border-radius: 8px !important; font-weight: 600 !important; font-size: 0.85rem !important; border: none !important; background: #f3f4f6 !important; color: #374151 !important; padding: 0.4rem 0.85rem !important; transition: all 0.2s !important; }
#calendar .fc-button:hover { background: #e5e7eb !important; }
#calendar .fc-button-active, #calendar .fc-button-primary:not(:disabled).fc-button-active { background: #4361ee !important; color: #fff !important; box-shadow: 0 2px 6px rgba(67,97,238,0.3) !important; }
#calendar .fc-daygrid-day-number { font-weight: 600; color: #374151; padding: 0.4rem; }
#calendar .fc-day-today { background: #eff6ff !important; }
#calendar .fc-event { border-radius: 6px !important; padding: 2px 6px !important; font-size: 0.78rem !important; font-weight: 600 !important; border: none !important; cursor: pointer; }
#calendar .fc-col-header-cell { font-size: 0.82rem; font-weight: 600; color: #6b7280; text-transform: uppercase; }
#calendar .fc-daygrid-day { cursor: pointer; transition: background 0.15s; }
#calendar .fc-daygrid-day:hover { background: #f0f4ff !important; }

/* Sidebar form */
.cal-form-group { margin-bottom: 1rem; }
.cal-form-label { display: block; font-weight: 600; color: #374151; margin-bottom: 0.35rem; font-size: 0.85rem; }
.cal-form-control { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.6rem 0.85rem; font-size: 0.88rem; color: #1a1a2e; transition: all 0.2s; }
.cal-form-control:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.cal-form-check { display: flex; align-items: center; gap: 0.5rem; font-size: 0.88rem; }
.cal-form-check input { width: 18px; height: 18px; accent-color: #4361ee; }

/* Category pills */
.cal-categories { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 1rem; }
.cal-cat-pill { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.65rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: 1.5px solid transparent; }
.cal-cat-pill:hover { transform: scale(1.05); }
.cal-cat-pill.active { border-color: currentColor; }
.cal-cat-dot { width: 8px; height: 8px; border-radius: 50%; }

/* Upcoming events */
.cal-upcoming-item { display: flex; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f3f4f6; }
.cal-upcoming-item:last-child { border-bottom: none; }
.cal-upcoming-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
.cal-upcoming-info { flex: 1; }
.cal-upcoming-title { font-size: 0.88rem; font-weight: 600; color: #1a1a2e; }
.cal-upcoming-date { font-size: 0.78rem; color: #6b7280; }
.cal-upcoming-cat { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; }

/* Event Detail Modal */
.cal-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 9999; display: flex; align-items: center; justify-content: center; animation: calModalIn 0.2s ease; }
.cal-modal { background: #fff; border-radius: 16px; width: 480px; max-width: 90vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0,0,0,0.15); }
.cal-modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
.cal-modal-title { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.cal-modal-close { width: 32px; height: 32px; border-radius: 8px; border: none; background: #f3f4f6; cursor: pointer; font-size: 1rem; color: #6b7280; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.cal-modal-close:hover { background: #e5e7eb; color: #1a1a2e; }
.cal-modal-body { padding: 1.5rem; }
.cal-modal-row { display: flex; padding: 0.5rem 0; }
.cal-modal-label { width: 120px; flex-shrink: 0; font-weight: 600; color: #6b7280; font-size: 0.85rem; }
.cal-modal-value { color: #1a1a2e; font-size: 0.88rem; }
.cal-modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; display: flex; gap: 0.5rem; justify-content: flex-end; }

/* Quick Add Modal */
.quick-add-hint { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.82rem; color: #1e40af; display: flex; align-items: center; gap: 0.5rem; }

@keyframes calModalIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }

@media (max-width: 992px) { .cal-layout { grid-template-columns: 1fr; } }
@media (max-width: 768px) { .cal-header { flex-direction: column; } .cal-title { font-size: 1.35rem; } }
</style>
@endpush

@section('content')
<div class="cal-page">
    <div class="cal-header">
        <div class="cal-header-left">
            <h1 class="cal-title">Academic Calendar</h1>
            <p class="cal-subtitle">Manage school events, holidays, exams, and important dates &mdash; click any date to add an event</p>
        </div>
    </div>

    <div class="cal-layout">
        {{-- Calendar --}}
        <div class="cal-card">
            <div id="calendar"></div>
        </div>

        {{-- Sidebar --}}
        <div>
            {{-- Quick Add Hint --}}
            @if($canAddEvents)
            <div class="quick-add-hint">
                <i class="fas fa-mouse-pointer"></i>
                <span>Click on any calendar date to quickly add an event!</span>
            </div>
            @endif

            {{-- Add Event Form --}}
            @if($canAddEvents)
            <div class="cal-card" style="margin-bottom:1.25rem;">
                <div class="cal-card-header"><i class="fas fa-plus-circle" style="color:#4361ee"></i> Add Event</div>
                <div class="cal-card-body">
                    <form method="POST" action="{{ route('admin.calendar.store') }}" id="eventForm">
                        @csrf
                        <div class="cal-form-group">
                            <label class="cal-form-label">Title *</label>
                            <input type="text" name="title" class="cal-form-control" required placeholder="Event title" id="eventTitle">
                        </div>
                        <div class="cal-form-group">
                            <label class="cal-form-label">Category *</label>
                            <select name="category" class="cal-form-control" required id="eventCategory">
                                @foreach(\App\Models\CalendarEvent::categoryList() as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="cal-form-group">
                            <label class="cal-form-label">Start Date *</label>
                            <input type="date" name="start_date" class="cal-form-control" required id="eventStartDate">
                        </div>
                        <div class="cal-form-group">
                            <label class="cal-form-label">End Date</label>
                            <input type="date" name="end_date" class="cal-form-control" id="eventEndDate">
                        </div>
                        <div class="cal-form-group">
                            <div class="cal-form-check">
                                <input type="checkbox" name="is_all_day" id="isAllDay" value="1" checked>
                                <label for="isAllDay">All day event</label>
                            </div>
                        </div>
                        <div class="cal-form-group" id="timeFields" style="display:none;">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.65rem;">
                                <div>
                                    <label class="cal-form-label">Start Time</label>
                                    <input type="time" name="start_time" class="cal-form-control">
                                </div>
                                <div>
                                    <label class="cal-form-label">End Time</label>
                                    <input type="time" name="end_time" class="cal-form-control">
                                </div>
                            </div>
                        </div>
                        <div class="cal-form-group">
                            <label class="cal-form-label">Description</label>
                            <textarea name="description" class="cal-form-control" rows="2" placeholder="Optional description"></textarea>
                        </div>
                        <div class="cal-form-group">
                            <label class="cal-form-label">Academic Year</label>
                            <select name="academic_year_id" class="cal-form-control">
                                <option value="">-- None --</option>
                                @foreach($academicYears as $ay)
                                    <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($branches->count() > 1)
                        <div class="cal-form-group">
                            <label class="cal-form-label">Branch</label>
                            <select name="branch_id" class="cal-form-control">
                                <option value="">-- All Branches --</option>
                                @foreach($branches as $br)
                                    <option value="{{ $br->id }}">{{ $br->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <input type="hidden" name="color" id="eventColor">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i> Add Event</button>
                    </form>
                </div>
            </div>
            @else
            {{-- Teacher: Cannot add events --}}
            <div class="cal-card" style="margin-bottom:1.25rem;">
                <div class="cal-card-body" style="text-align:center;padding:2rem;">
                    <i class="fas fa-calendar-times" style="font-size:2rem;color:#9ca3af;margin-bottom:0.75rem;display:block;"></i>
                    <p style="color:#6b7280;font-size:0.9rem;margin:0;">Teachers cannot add calendar events.<br>Only branch principals and managers can add events.</p>
                </div>
            </div>
            @endif

            {{-- Category Legend --}}
            <div class="cal-card" style="margin-bottom:1.25rem;">
                <div class="cal-card-header"><i class="fas fa-palette" style="color:#4361ee"></i> Categories</div>
                <div class="cal-card-body">
                    <div class="cal-categories">
                        @foreach(\App\Models\CalendarEvent::categoryColors() as $key => $color)
                            <span class="cal-cat-pill active" data-category="{{ $key }}" style="background:{{ $color }}15;color:{{ $color }};" onclick="filterCategory('{{ $key }}')">
                                <span class="cal-cat-dot" style="background:{{ $color }}"></span>
                                {{ \App\Models\CalendarEvent::categoryList()[$key] }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Upcoming Events --}}
            <div class="cal-card">
                <div class="cal-card-header"><i class="fas fa-clock" style="color:#4361ee"></i> Upcoming Events</div>
                <div class="cal-card-body" id="upcomingEvents">
                    <p style="color:#9ca3af;font-size:0.85rem;text-align:center;margin:0;">Loading...</p>
                </div>
            </div>

            {{-- Pending Approval (GM/Admin only) --}}
            @if($canApproveEvents && $pendingApprovalCount > 0)
            <div class="cal-card" style="margin-top:1.25rem;border-color:#f59e0b;">
                <div class="cal-card-header" style="background:#fffbeb;color:#92400e;"><i class="fas fa-hourglass-half" style="color:#f59e0b"></i> Pending Approval ({{ $pendingApprovalCount }})</div>
                <div class="cal-card-body" id="pendingEvents">
                    <p style="color:#9ca3af;font-size:0.85rem;text-align:center;margin:0;">Loading...</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Quick Add Event Modal (appears when clicking a calendar date) --}}
<div id="quickAddModal" class="cal-modal-overlay" style="display:none;" onclick="if(event.target===this)closeQuickAdd()">
    <div class="cal-modal">
        <div class="cal-modal-header">
            <h5 class="cal-modal-title"><i class="fas fa-calendar-plus me-2" style="color:#4361ee"></i>Add Event</h5>
            <button class="cal-modal-close" onclick="closeQuickAdd()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('admin.calendar.store') }}" id="quickAddForm">
            @csrf
            <div class="cal-modal-body">
                <div class="quick-add-hint" id="quickAddDateHint">
                    <i class="fas fa-calendar-day"></i>
                    <span>Adding event for: <strong id="quickAddDateText"></strong></span>
                </div>
                <div class="cal-form-group">
                    <label class="cal-form-label">Title *</label>
                    <input type="text" name="title" class="cal-form-control" required placeholder="Event title" id="quickAddTitle" autofocus>
                </div>
                <div class="cal-form-group">
                    <label class="cal-form-label">Category *</label>
                    <select name="category" class="cal-form-control" required id="quickAddCategory">
                        @foreach(\App\Models\CalendarEvent::categoryList() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="start_date" id="quickAddStartDate">
                <input type="hidden" name="end_date" id="quickAddEndDate">
                <input type="hidden" name="is_all_day" value="1">
                <input type="hidden" name="color" id="quickAddColor">
                <div class="cal-form-group">
                    <label class="cal-form-label">Description</label>
                    <textarea name="description" class="cal-form-control" rows="2" placeholder="Optional description"></textarea>
                </div>
                <div class="cal-form-group">
                    <label class="cal-form-label">Academic Year</label>
                    <select name="academic_year_id" class="cal-form-control">
                        <option value="">-- None --</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if($branches->count() > 1)
                <div class="cal-form-group">
                    <label class="cal-form-label">Branch</label>
                    <select name="branch_id" class="cal-form-control">
                        <option value="">-- All Branches --</option>
                        @foreach($branches as $br)
                            <option value="{{ $br->id }}">{{ $br->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            <div class="cal-modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeQuickAdd()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Add Event</button>
            </div>
        </form>
    </div>
</div>

{{-- Event Detail Modal --}}
<div id="eventModal" class="cal-modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal()">
    <div class="cal-modal">
        <div class="cal-modal-header">
            <h5 class="cal-modal-title" id="modalTitle">Event</h5>
            <button class="cal-modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="cal-modal-body" id="modalBody"></div>
        <div class="cal-modal-footer">
            <button class="btn btn-danger btn-sm" id="modalDeleteBtn" onclick="deleteEvent()"><i class="fas fa-trash me-1"></i> Delete</button>
            <button class="btn btn-success btn-sm" id="modalApproveBtn" style="display:none;" onclick="approveEvent()"><i class="fas fa-check me-1"></i> Approve</button>
            <button class="btn btn-warning btn-sm" id="modalRejectBtn" style="display:none;" onclick="rejectEvent()"><i class="fas fa-times me-1"></i> Reject</button>
            <button class="btn btn-secondary btn-sm" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
const categoryColors = @json(\App\Models\CalendarEvent::categoryColors());
const categoryLabels = @json(\App\Models\CalendarEvent::categoryList());
const canApproveEvents = {{ $canApproveEvents ? 'true' : 'false' }};
let currentEventId = null;
let currentEventApproved = false;
let activeCategory = null;

// All-day toggle
const isAllDay = document.getElementById('isAllDay');
const timeFields = document.getElementById('timeFields');
isAllDay.addEventListener('change', function() {
    timeFields.style.display = this.checked ? 'none' : 'block';
});

// Auto-set color based on category
const catSelect = document.getElementById('eventCategory');
const colorInput = document.getElementById('eventColor');
function updateColor() { colorInput.value = categoryColors[catSelect.value] || '#4361ee'; }
catSelect.addEventListener('change', updateColor);
updateColor();

// Quick Add modal auto-color
const quickCatSelect = document.getElementById('quickAddCategory');
const quickColorInput = document.getElementById('quickAddColor');
function updateQuickColor() { quickColorInput.value = categoryColors[quickCatSelect.value] || '#4361ee'; }
quickCatSelect.addEventListener('change', updateQuickColor);
updateQuickColor();

// Initialize FullCalendar
const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,listWeek'
    },
    height: 'auto',
    events: function(fetchInfo, successCallback, failureCallback) {
        let url = '{{ route("admin.calendar.api.events") }}?start=' + fetchInfo.startStr.substring(0,10) + '&end=' + fetchInfo.endStr.substring(0,10);
        if (activeCategory) url += '&category=' + activeCategory;
        fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(r => { if(!r.ok) throw new Error('Network error'); return r.json(); })
            .then(data => {
                console.log('Calendar events loaded:', data.length);
                successCallback(data);
                updateUpcoming(data);
            })
            .catch(function(err) { console.error('Calendar fetch error:', err); failureCallback(err); });
    },
    eventClick: function(info) {
        info.jsEvent.preventDefault();
        showEventDetail(info.event);
    },
    dateClick: function(info) {
        // Open quick add modal with the clicked date
        openQuickAdd(info.dateStr);
    }
});
calendar.render();

function openQuickAdd(dateStr) {
    const dateObj = new Date(dateStr + 'T00:00:00');
    const formatted = dateObj.toLocaleDateString(undefined, {weekday:'long', year:'numeric', month:'long', day:'numeric'});

    document.getElementById('quickAddStartDate').value = dateStr;
    document.getElementById('quickAddEndDate').value = dateStr;
    document.getElementById('quickAddDateText').textContent = formatted;
    document.getElementById('quickAddTitle').value = '';
    document.getElementById('quickAddModal').style.display = 'flex';

    setTimeout(function() {
        document.getElementById('quickAddTitle').focus();
    }, 200);
}

function closeQuickAdd() {
    document.getElementById('quickAddModal').style.display = 'none';
}

// Handle sidebar event form submission via AJAX so calendar updates without page reload
document.getElementById('eventForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Adding...';

    fetch(form.action, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r => {
        if (r.ok) {
            calendar.refetchEvents();
            form.reset();
            document.getElementById('isAllDay').checked = true;
            document.getElementById('timeFields').style.display = 'none';
            updateColor();
            // Flash success on the button
            submitBtn.innerHTML = '<i class="fas fa-check me-1"></i> Added!';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-success');
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.classList.remove('btn-success');
                submitBtn.classList.add('btn-primary');
                submitBtn.disabled = false;
            }, 1500);
        } else {
            return r.json().then(data => {
                alert('Error: ' + (data.message || 'Failed to add event'));
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }
    })
    .catch(err => {
        // Fallback: submit normally
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        form.submit();
    });
});

// Handle quick add form submission via AJAX so we don't lose calendar state
document.getElementById('quickAddForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r => {
        if (r.ok) {
            closeQuickAdd();
            calendar.refetchEvents();
        } else {
            return r.json().then(data => {
                alert('Error: ' + (data.message || 'Failed to add event'));
            });
        }
    })
    .catch(err => {
        // Fallback: submit normally
        form.submit();
    });
});

function showEventDetail(event) {
    currentEventId = event.id;
    const props = event.extendedProps;
    currentEventApproved = props.is_approved;
    let html = '';
    if (!props.is_approved) {
        html += '<div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:0.6rem 1rem;margin-bottom:1rem;font-size:0.85rem;color:#92400e;display:flex;align-items:center;gap:0.5rem;"><i class="fas fa-hourglass-half"></i> <strong>Pending Approval</strong> — This event is waiting for manager approval.</div>';
    }
    html += '<div class="cal-modal-row"><div class="cal-modal-label">Category</div><div class="cal-modal-value"><span style="background:' + (categoryColors[props.category] || '#6b7280') + '15;color:' + (categoryColors[props.category] || '#6b7280') + ';padding:0.15rem 0.6rem;border-radius:50px;font-size:0.78rem;font-weight:600;">' + (categoryLabels[props.category] || props.category) + '</span></div></div>';
    html += '<div class="cal-modal-row"><div class="cal-modal-label">Date</div><div class="cal-modal-value">' + event.start.toLocaleDateString(undefined, {weekday:'long',year:'numeric',month:'long',day:'numeric'}) + '</div></div>';
    if (event.end) {
        html += '<div class="cal-modal-row"><div class="cal-modal-label">End Date</div><div class="cal-modal-value">' + event.end.toLocaleDateString(undefined, {weekday:'long',year:'numeric',month:'long',day:'numeric'}) + '</div></div>';
    }
    if (!event.allDay && event.start) {
        html += '<div class="cal-modal-row"><div class="cal-modal-label">Time</div><div class="cal-modal-value">' + event.start.toLocaleTimeString(undefined, {hour:'2-digit',minute:'2-digit'}) + '</div></div>';
    }
    if (props.description) {
        html += '<div class="cal-modal-row"><div class="cal-modal-label">Description</div><div class="cal-modal-value">' + props.description + '</div></div>';
    }
    if (props.academic_year) {
        html += '<div class="cal-modal-row"><div class="cal-modal-label">Academic Year</div><div class="cal-modal-value">' + props.academic_year + '</div></div>';
    }
    if (props.branch) {
        html += '<div class="cal-modal-row"><div class="cal-modal-label">Branch</div><div class="cal-modal-value">' + props.branch + (props.scope === 'branch' ? ' (Branch Only)' : '') + '</div></div>';
    }
    if (props.scope) {
        html += '<div class="cal-modal-row"><div class="cal-modal-label">Scope</div><div class="cal-modal-value">' + (props.scope === 'school' ? 'School-wide' : 'Branch-specific') + '</div></div>';
    }
    document.getElementById('modalTitle').textContent = event.title.replace(' (Pending)', '');
    document.getElementById('modalBody').innerHTML = html;
    document.getElementById('eventModal').style.display = 'flex';

    // Show/hide approve/reject buttons
    const approveBtn = document.getElementById('modalApproveBtn');
    const rejectBtn = document.getElementById('modalRejectBtn');
    if (canApproveEvents && !props.is_approved) {
        approveBtn.style.display = 'inline-block';
        rejectBtn.style.display = 'inline-block';
    } else {
        approveBtn.style.display = 'none';
        rejectBtn.style.display = 'none';
    }
}

function closeModal() {
    document.getElementById('eventModal').style.display = 'none';
    currentEventId = null;
}

function deleteEvent() {
    if (!currentEventId) return;
    if (!confirm('Are you sure you want to delete this event?')) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    fetch('{{ route("admin.calendar.destroy", 0) }}'.replace('/0', '/' + currentEventId), {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_method=DELETE&_token=' + csrfToken
    }).then(() => {
        closeModal();
        calendar.refetchEvents();
    });
}

function approveEvent() {
    if (!currentEventId) return;
    if (!confirm('Approve this event? It will be visible to all relevant users and notifications will be sent.')) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    fetch('{{ route("admin.calendar.approve", 0) }}'.replace('/0', '/' + currentEventId), {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_token=' + csrfToken
    }).then(r => r.json()).then(data => {
        alert(data.message || 'Event approved successfully.');
        closeModal();
        calendar.refetchEvents();
    }).catch(err => {
        alert('Error approving event.');
    });
}

function rejectEvent() {
    if (!currentEventId) return;
    if (!confirm('Reject and delete this event?')) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    fetch('{{ route("admin.calendar.reject", 0) }}'.replace('/0', '/' + currentEventId), {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_token=' + csrfToken
    }).then(r => r.json()).then(data => {
        alert(data.message || 'Event rejected.');
        closeModal();
        calendar.refetchEvents();
    }).catch(err => {
        alert('Error rejecting event.');
    });
}

function filterCategory(cat) {
    activeCategory = activeCategory === cat ? null : cat;
    document.querySelectorAll('.cal-cat-pill').forEach(pill => {
        pill.classList.toggle('active', activeCategory === pill.dataset.category || !activeCategory);
    });
    calendar.refetchEvents();
}

function updateUpcoming(events) {
    const today = new Date().toISOString().split('T')[0];
    const upcoming = events.filter(e => e.start >= today).sort((a,b) => a.start.localeCompare(b.start)).slice(0, 8);
    const container = document.getElementById('upcomingEvents');
    if (upcoming.length === 0) {
        container.innerHTML = '<p style="color:#9ca3af;font-size:0.85rem;text-align:center;margin:0;">No upcoming events</p>';
        return;
    }
    container.innerHTML = upcoming.map(e => {
        const date = new Date(e.start).toLocaleDateString(undefined, {month:'short',day:'numeric',year:'numeric'});
        return '<div class="cal-upcoming-item"><div class="cal-upcoming-dot" style="background:' + e.backgroundColor + '"></div><div class="cal-upcoming-info"><div class="cal-upcoming-title">' + e.title + '</div><div class="cal-upcoming-date">' + date + '</div><div class="cal-upcoming-cat">' + (categoryLabels[e.extendedProps?.category] || '') + '</div></div></div>';
    }).join('');
}

// Keyboard shortcut to close modals
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeModal(); closeQuickAdd(); }
});
</script>
@endpush
