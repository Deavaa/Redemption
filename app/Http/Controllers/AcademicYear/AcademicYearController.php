<?php
namespace App\Http\Controllers\AcademicYear;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Services\AcademicYearTransitionService;
use Illuminate\Http\Request;
class AcademicYearController extends Controller
{
    public function index()
    {
        $data = AcademicYear::orderBy('id','desc')->paginate(10);
        return view('admin.AcademicYear.index', compact('data'));
    }
    public function create()
    {
        return view('admin.AcademicYear.create');
    }
    public function store(Request $request)
    {
        $input = $this->validateAcademicYear($request);
        if (!empty($input['is_current'])) {
            AcademicYear::where('is_current', 1)->update(['is_current' => 0]);
        } else {
            $input['is_current'] = 0;
        }
        AcademicYear::create($input);
        return redirect()->route('admin.academic-years.index')->with('success','Created');
    }
    public function show($id)
    {
        $item = AcademicYear::findOrFail($id);
        return view('admin.AcademicYear.show', compact('item'));
    }

    public function edit($id)
    {
        $data = AcademicYear::findOrFail($id);
        return view('admin.AcademicYear.edit', compact('data'));
    }
    public function update(Request $request, $id)
    {
        $item = AcademicYear::findOrFail($id);
        $input = $this->validateAcademicYear($request);
        if (!empty($input['is_current'])) {
            AcademicYear::where('is_current', 1)->where('id', '!=', $id)->update(['is_current' => 0]);
        } else {
            $input['is_current'] = 0;
        }
        $item->update($input);
        return redirect()->route('admin.academic-years.index')->with('success','Updated');
    }

    /**
     * Validate academic year input and return only the safe fields.
     * Replaces $request->all() which previously trusted any input.
     */
    private function validateAcademicYear(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_current' => 'sometimes|boolean',
        ]);
    }
    public function destroy($id)
    {
        $item = AcademicYear::findOrFail($id);
        if ($item->is_current) {
            return redirect()->back()->with('error','Cannot delete the current academic year');
        }
        AcademicYear::destroy($id);
        return redirect()->route('admin.academic-years.index')->with('success','Deleted');
    }

    /**
     * Show the academic year transition form for carrying forward
     * classes, sections, and teacher assignments to a new academic year.
     */
    public function transitionForm()
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $currentAy = AcademicYear::where('is_current', true)->first();

        return view('admin.AcademicYear.transition', compact('academicYears', 'currentAy'));
    }

    /**
     * Preview the transition — show what will be carried forward.
     */
    public function transitionPreview(Request $request)
    {
        $validated = $request->validate([
            'source_academic_year_id' => 'required|exists:academic_years,id',
            'target_academic_year_id' => 'required|exists:academic_years,id|different:source_academic_year_id',
        ]);

        $service = new AcademicYearTransitionService();
        $preview = $service->preview($validated['source_academic_year_id'], $validated['target_academic_year_id']);

        return response()->json($preview);
    }

    /**
     * Process the academic year transition.
     */
    public function processTransition(Request $request)
    {
        $validated = $request->validate([
            'source_academic_year_id' => 'required|exists:academic_years,id',
            'target_academic_year_id' => 'required|exists:academic_years,id|different:source_academic_year_id',
            'carry_classes' => 'nullable|boolean',
            'carry_sections' => 'nullable|boolean',
            'carry_assignments' => 'nullable|boolean',
            'clear_teacher_ids' => 'nullable|boolean',
        ]);

        $options = [
            'carry_classes' => $request->has('carry_classes'),
            'carry_sections' => $request->has('carry_sections'),
            'carry_assignments' => $request->has('carry_assignments'),
            'clear_teacher_ids' => $request->has('clear_teacher_ids'),
        ];

        // At least one carry option must be selected
        if (! $options['carry_classes'] && ! $options['carry_sections'] && ! $options['carry_assignments']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Please select at least one item to carry forward (classes, sections, or teacher assignments).');
        }

        $service = new AcademicYearTransitionService();
        $results = $service->execute(
            $validated['source_academic_year_id'],
            $validated['target_academic_year_id'],
            $options
        );

        if (! empty($results['errors'])) {
            return redirect()->route('admin.academic-years.transition')
                ->with('error', 'Transition failed: ' . implode(', ', $results['errors']));
        }

        $targetAy = AcademicYear::find($validated['target_academic_year_id']);

        $message = sprintf(
            'Transition completed! %d class(es) created, %d skipped (already exist), %d section(s) created, %d teacher assignment(s) created, %d assignment(s) skipped.',
            $results['classes_created'],
            $results['classes_skipped'],
            $results['sections_created'],
            $results['assignments_created'],
            $results['assignments_skipped']
        );

        // If teacher IDs were cleared, redirect to the Teacher Reassignment page
        // so admin can assign teachers for the new academic year
        if ($options['clear_teacher_ids']) {
            $message .= ' Teacher assignments were cleared. Please reassign teachers for the new academic year.';
            return redirect()->route('admin.teacher-reassignment.index', ['academic_year_id' => $targetAy->id])
                ->with('success', $message);
        }

        return redirect()->route('admin.academic-years.show', $targetAy->id)
            ->with('success', $message);
    }
}
