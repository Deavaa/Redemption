<?php
namespace App\Http\Controllers\Slider;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;

class SliderController extends Controller
{
    public function index() { $data = Slider::latest()->paginate(20); return view('admin.Slider.index', compact('data')); }
    public function create() { return view('admin.Slider.create'); }
    public function store(Request $r) { Slider::create($r->all()); return redirect()->route("admin.sliders.index")->with('success','Created successfully'); }
    public function show(Slider $item) { return view('admin.Slider.show', compact('item')); }
    public function edit(Slider $item) { return view('admin.Slider.edit', compact('item')); }
    public function update(Request $r, Slider $item) { $item->update($r->all()); return redirect()->route("admin.sliders.index")->with('success','Updated successfully'); }
    public function destroy(Slider $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}