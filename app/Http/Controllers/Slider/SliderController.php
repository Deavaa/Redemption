<?php
namespace App\Http\Controllers\Slider;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use App\Helpers\ImageCompressor;

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
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:51200',
            'link' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data = $r->only(['title','subtitle','link','sort_order']);
        $data['is_active'] = $r->has('is_active') ? 1 : 0;
        if ($r->hasFile('image_path') && $r->file('image_path')->isValid()) {
            try {
                $data['image_path'] = ImageCompressor::compressAndStore($r->file('image_path'), 'sliders', 2048, 1920);
            } catch (\Throwable $e) {
                \Log::error('Slider image upload failed: ' . $e->getMessage());
                $data['image_path'] = $r->file('image_path')->store('sliders', 'public');
                $this->copyToPublicStorage($data['image_path']);
            }
        }
        Slider::create($data);
        return redirect()->route("admin.sliders.index")->with('success','Slider created successfully');
    }

    public function show(Slider $slider) { return view('admin.Slider.show', ['item' => $slider]); }
    public function edit(Slider $slider) { return view('admin.Slider.edit', ['item' => $slider]); }

    public function update(Request $r, Slider $slider)
    {
        $r->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:51200',
            'link' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data = $r->only(['title','subtitle','link','sort_order']);
        $data['is_active'] = $r->has('is_active') ? 1 : 0;
        if ($r->hasFile('image_path') && $r->file('image_path')->isValid()) {
            try {
                $data['image_path'] = ImageCompressor::compressAndStore($r->file('image_path'), 'sliders', 2048, 1920);
            } catch (\Throwable $e) {
                \Log::error('Slider image upload failed: ' . $e->getMessage());
                $data['image_path'] = $r->file('image_path')->store('sliders', 'public');
                $this->copyToPublicStorage($data['image_path']);
            }
        }
        $slider->update($data);
        return redirect()->route("admin.sliders.index")->with('success','Slider updated successfully');
    }

    public function destroy(Slider $slider) { $slider->delete(); return back()->with('success','Slider deleted successfully'); }

    private function copyToPublicStorage($relativePath)
    {
        try {
            $sourcePath = \Illuminate\Support\Facades\Storage::disk('public')->path($relativePath);
            $destinationPath = public_path('storage/' . $relativePath);
            $destinationDir = dirname($destinationPath);
            if (!is_dir($destinationDir)) {
                mkdir($destinationDir, 0755, true);
            }
            if (file_exists($sourcePath)) {
                copy($sourcePath, $destinationPath);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to copy file to public storage fallback: ' . $e->getMessage());
        }
    }
}
