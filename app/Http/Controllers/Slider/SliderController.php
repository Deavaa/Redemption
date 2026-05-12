<?php
namespace App\Http\Controllers\Slider;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;

class SliderController extends Controller
{
    public function index(Request $r)
    {
        $q = Slider::query();
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('title', 'LIKE', "%$s%");
        }
        $data = $q->orderBy('sort_order')->paginate(20);
        $totalSliders = Slider::count();
        return view('admin.Slider.index', compact('data', 'totalSliders'));
    }

    public function create() { return view('admin.Slider.create'); }

    public function store(Request $r)
    {
        $r->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'link' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data = $r->only(['title','subtitle','link','sort_order','is_active']);
        if ($r->hasFile('image_path')) {
            $data['image_path'] = $r->file('image_path')->store('sliders', 'public');
        }
        Slider::create($data);
        return redirect()->route("admin.sliders.index")->with('success','Slider created successfully');
    }

    public function show(Slider $item) { return view('admin.Slider.show', compact('item')); }
    public function edit(Slider $item) { return view('admin.Slider.edit', compact('item')); }

    public function update(Request $r, Slider $item)
    {
        $r->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'link' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data = $r->only(['title','subtitle','link','sort_order','is_active']);
        if ($r->hasFile('image_path')) {
            $data['image_path'] = $r->file('image_path')->store('sliders', 'public');
        }
        $item->update($data);
        return redirect()->route("admin.sliders.index")->with('success','Slider updated successfully');
    }

    public function destroy(Slider $item) { $item->delete(); return back()->with('success','Slider deleted successfully'); }
}
