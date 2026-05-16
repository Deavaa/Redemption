<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Training;
use App\Models\TrainingParticipant;
use App\Models\User;

class TrainingController extends Controller
{
    public function index(Request $r)
    {
        $q = Training::with(['creator', 'participants']);

        if ($r->filled('search')) {
            $s = $r->search;
            $q->where(function ($x) use ($s) {
                $x->where('title', 'LIKE', "%$s%")
                  ->orWhere('provider', 'LIKE', "%$s%")
                  ->orWhere('facilitator', 'LIKE', "%$s%")
                  ->orWhere('venue', 'LIKE', "%$s%");
            });
        }
        if ($r->filled('status'))     $q->where('status', $r->status);
        if ($r->filled('type'))       $q->where('type', $r->type);
        if ($r->filled('category'))   $q->where('category', $r->category);
        if ($r->filled('target_audience')) $q->where('target_audience', $r->target_audience);

        $data = $q->latest()->paginate(20);

        $totalTrainings  = Training::count();
        $plannedCount    = Training::where('status', 'planned')->count();
        $ongoingCount    = Training::where('status', 'ongoing')->count();
        $completedCount  = Training::where('status', 'completed')->count();
        $totalParticipants = TrainingParticipant::count();
        $completedParticipants = TrainingParticipant::where('status', 'completed')->count();
        $totalBudget     = Training::where('status', '!=', 'cancelled')->sum('cost');

        return view('admin.Training.index', compact(
            'data', 'totalTrainings', 'plannedCount', 'ongoingCount',
            'completedCount', 'totalParticipants', 'completedParticipants', 'totalBudget'
        ));
    }

    public function create()
    {
        return view('admin.Training.create');
    }

    public function store(Request $r)
    {
        $r->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'type'            => 'required|in:workshop,seminar,online_course,on_the_job,certification,conference,mentorship,induction',
            'category'        => 'required|in:pedagogical,administrative,technical,leadership,safety,curriculum,pastoral,general',
            'provider'        => 'nullable|string|max:255',
            'facilitator'     => 'nullable|string|max:255',
            'venue'           => 'nullable|string|max:255',
            'start_date'      => 'nullable|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'duration_hours'  => 'nullable|integer|min:0',
            'target_audience' => 'required|in:all,teachers,admins,staff,specific',
            'cost'            => 'nullable|numeric|min:0',
            'budget_source'   => 'nullable|string|max:255',
            'max_participants'=> 'nullable|integer|min:0',
            'status'          => 'required|in:planned,ongoing,completed,cancelled',
            'objectives'      => 'nullable|string',
            'outcomes'        => 'nullable|string',
            'notes'           => 'nullable|string',
        ]);

        $data = $r->only([
            'title','description','type','category','provider','facilitator','venue',
            'start_date','end_date','duration_hours','target_audience','cost',
            'budget_source','max_participants','status','objectives','outcomes','notes'
        ]);
        $data['created_by'] = auth()->id();

        Training::create($data);

        return redirect()->route('admin.trainings.index')
            ->with('success', 'Training program created successfully.');
    }

    public function show(Training $training)
    {
        $training->load(['creator', 'participants.employee', 'participants.nominator']);

        // Available employees to add as participants
        $existingIds = $training->participants()->pluck('employee_id')->toArray();
        $availableEmployees = User::whereIn('role', ['admin', 'teacher', 'staff', 'librarian', 'branch_principal'])
            ->whereNotIn('id', $existingIds)
            ->orderBy('name')
            ->get();

        return view('admin.Training.show', compact('training', 'availableEmployees'));
    }

    public function edit(Training $training)
    {
        return view('admin.Training.edit', ['item' => $training]);
    }

    public function update(Request $r, Training $training)
    {
        $r->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'type'            => 'required|in:workshop,seminar,online_course,on_the_job,certification,conference,mentorship,induction',
            'category'        => 'required|in:pedagogical,administrative,technical,leadership,safety,curriculum,pastoral,general',
            'provider'        => 'nullable|string|max:255',
            'facilitator'     => 'nullable|string|max:255',
            'venue'           => 'nullable|string|max:255',
            'start_date'      => 'nullable|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'duration_hours'  => 'nullable|integer|min:0',
            'target_audience' => 'required|in:all,teachers,admins,staff,specific',
            'cost'            => 'nullable|numeric|min:0',
            'budget_source'   => 'nullable|string|max:255',
            'max_participants'=> 'nullable|integer|min:0',
            'status'          => 'required|in:planned,ongoing,completed,cancelled',
            'objectives'      => 'nullable|string',
            'outcomes'        => 'nullable|string',
            'notes'           => 'nullable|string',
        ]);

        $data = $r->only([
            'title','description','type','category','provider','facilitator','venue',
            'start_date','end_date','duration_hours','target_audience','cost',
            'budget_source','max_participants','status','objectives','outcomes','notes'
        ]);

        $training->update($data);

        return redirect()->route('admin.trainings.index')
            ->with('success', 'Training program updated successfully.');
    }

    public function destroy(Training $training)
    {
        $training->delete();
        return back()->with('success', 'Training program deleted successfully.');
    }

    /* ── Participant Management ── */

    public function addParticipant(Request $r, Training $training)
    {
        $r->validate([
            'employee_id' => 'required|exists:users,id',
            'status'      => 'nullable|in:invited,enrolled,attended,completed,absent,dropped',
        ]);

        // Check if already a participant
        $exists = $training->participants()->where('employee_id', $r->employee_id)->exists();
        if ($exists) {
            return back()->with('error', 'This employee is already a participant.');
        }

        // Check capacity
        if ($training->max_participants > 0 && $training->enrolled_count >= $training->max_participants) {
            return back()->with('error', 'Training has reached maximum participant capacity.');
        }

        TrainingParticipant::create([
            'training_id'  => $training->id,
            'employee_id'  => $r->employee_id,
            'status'       => $r->status ?? 'invited',
            'nominated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Participant added successfully.');
    }

    public function addBulkParticipants(Request $r, Training $training)
    {
        $r->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:users,id',
        ]);

        $existingIds = $training->participants()->pluck('employee_id')->toArray();
        $added = 0;

        foreach ($r->employee_ids as $eid) {
            if (in_array($eid, $existingIds)) continue;
            if ($training->max_participants > 0 && ($training->enrolled_count + $added) >= $training->max_participants) break;

            TrainingParticipant::create([
                'training_id'  => $training->id,
                'employee_id'  => $eid,
                'status'       => 'invited',
                'nominated_by' => auth()->id(),
            ]);
            $added++;
        }

        return back()->with('success', "$added participant(s) added successfully.");
    }

    public function updateParticipant(Request $r, Training $training, $participantId)
    {
        $participant = TrainingParticipant::where('training_id', $training->id)
            ->where('id', $participantId)->firstOrFail();

        $r->validate([
            'status'           => 'required|in:invited,enrolled,attended,completed,absent,dropped',
            'completion_date'  => 'nullable|date',
            'score'            => 'nullable|numeric|min:0|max:100',
            'grade'            => 'nullable|string|max:10',
            'certificate_number' => 'nullable|string|max:100',
            'certificate_issued' => 'nullable|boolean',
            'feedback'         => 'nullable|string',
            'remarks'          => 'nullable|string',
        ]);

        $data = $r->only([
            'status','completion_date','score','grade',
            'certificate_number','certificate_issued','feedback','remarks'
        ]);

        // Auto-set completion date if status changed to completed
        if ($r->status === 'completed' && !$r->filled('completion_date')) {
            $data['completion_date'] = now()->toDateString();
        }

        // Auto-issue certificate if status is completed
        if ($r->status === 'completed' && !$r->filled('certificate_issued')) {
            $data['certificate_issued'] = true;
        }

        $participant->update($data);

        return back()->with('success', 'Participant status updated successfully.');
    }

    public function removeParticipant(Training $training, $participantId)
    {
        $participant = TrainingParticipant::where('training_id', $training->id)
            ->where('id', $participantId)->firstOrFail();
        $participant->delete();

        return back()->with('success', 'Participant removed from training.');
    }
}
