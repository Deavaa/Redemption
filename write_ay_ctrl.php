<?php
 $ctrl = <<<'CTRL'
<?php

namespace App\Http\Controllers\AcademicYear;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index()
    {
        $data = AcademicYear::orderBy('id','desc')->get();
        return view('admin.AcademicYear.index', compact('data'));
    }

    public function create()
    {
        return view('admin.AcademicYear.create');
    }

    public function store(Request $request)
    {
        AcademicYear::create($request->all());
        return redirect()->route('admin.academic-years.index')->with('success','Academic Year created!');
    }

    public function edit($id)
    {
        $data = AcademicYear::findOrFail($id);
        return view('admin.AcademicYear.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $item = AcademicYear::findOrFail($id);
        $item->update($request->all());
        return redirect()->route('admin.academic-years.index')->with('success','Academic Year updated!');
    }

    public function destroy($id)
    {
        AcademicYear::destroy($id);
        return redirect()->route('admin.academic-years.index')->with('success','Academic Year deleted!');
    }
}
CTRL;

 $dir = app_path('Http/Controllers/AcademicYear');
if(!is_dir($dir)) mkdir($dir, 0755, true);
file_put_contents($dir.'/AcademicYearController.php', $ctrl);
echo "DONE: AcademicYearController written\n";
