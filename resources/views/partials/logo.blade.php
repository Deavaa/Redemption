@php
 $logoPath = DB::table('settings')->where('key', 'school_logo')->value('value');
 $logoUrl = '';
if($logoPath) {
    if(file_exists(public_path('storage/' . $logoPath))) {
        $logoUrl = asset('storage/' . $logoPath);
    } elseif(file_exists(public_path($logoPath))) {
        $logoUrl = asset($logoPath);
    } else {
        $logoUrl = asset($logoPath);
    }
}
@endphp
