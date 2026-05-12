<?php
namespace App\Http\Controllers\AcademicYear;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
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
        $input = $request->all();
        if (!empty($input['is_current'])) {
            AcademicYear::where('is_current', 1)->update(['is_current' => 0]);
        } else {
            $input['is_current'] = 0;
        }
        AcademicYear::create($input);
        return redirect()->route('admin.academic-years.index')->with('success','Created');
    }
    public function edit($id)
    {
        $data = AcademicYear::findOrFail($id);
        return view('admin.AcademicYear.edit', compact('data'));
    }
    public function update(Request $request, $id)
    {
        $item = AcademicYear::findOrFail($id);
        $input = $request->all();
        if (!empty($input['is_current'])) {
            AcademicYear::where('is_current', 1)->where('id', '!=', $id)->update(['is_current' => 0]);
        } else {
            $input['is_current'] = 0;
        }
        $item->update($input);
        return redirect()->route('admin.academic-years.index')->with('success','Updated');
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
}
