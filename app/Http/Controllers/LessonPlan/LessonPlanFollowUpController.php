<?php

namespace App\Http\Controllers\LessonPlan;

use App\Http\Controllers\Controller;
use App\Models\LessonPlanFollowUp;
use App\Models\LessonPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonPlanFollowUpController extends Controller
{
    public function store(Request $request, LessonPlan $lessonPlan)
    {
        $request->validate([
            'follow_up_date'     => 'required|date',
            'completion_status'  => 'required|in:not_started,in_progress,completed,skipped',
            'objectives_achieved'=> 'nullable|string',
            'challenges'         => 'nullable|string',
            'adjustments'        => 'nullable|string',
            'student_engagement' => 'nullable|string',
            'remarks'            => 'nullable|string',
        ]);

        LessonPlanFollowUp::create([
            'lesson_plan_id'     => $lessonPlan->id,
            'followed_up_by'     => Auth::id(),
            'follow_up_date'     => $request->follow_up_date,
            'completion_status'  => $request->completion_status,
            'objectives_achieved'=> $request->objectives_achieved,
            'challenges'         => $request->challenges,
            'adjustments'        => $request->adjustments,
            'student_engagement' => $request->student_engagement,
            'remarks'            => $request->remarks,
        ]);

        return back()->with('success', 'Follow-up recorded successfully.');
    }

    public function update(Request $request, LessonPlan $lessonPlan, LessonPlanFollowUp $followUp)
    {
        $request->validate([
            'follow_up_date'     => 'required|date',
            'completion_status'  => 'required|in:not_started,in_progress,completed,skipped',
            'objectives_achieved'=> 'nullable|string',
            'challenges'         => 'nullable|string',
            'adjustments'        => 'nullable|string',
            'student_engagement' => 'nullable|string',
            'remarks'            => 'nullable|string',
        ]);

        $followUp->update($request->only([
            'follow_up_date', 'completion_status', 'objectives_achieved',
            'challenges', 'adjustments', 'student_engagement', 'remarks',
        ]));

        return back()->with('success', 'Follow-up updated successfully.');
    }

    public function destroy(LessonPlan $lessonPlan, LessonPlanFollowUp $followUp)
    {
        $followUp->delete();
        return back()->with('success', 'Follow-up deleted.');
    }
}
