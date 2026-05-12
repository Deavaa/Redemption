<?php
namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
class SettingController extends Controller{
    public function index(){
        $settings=Setting::all()->groupBy("group");
        $groupLabels=["general"=>"General Settings","academic"=>"Academic Settings","contact"=>"Contact Information","social"=>"Social Media Links","about"=>"About Page Content","appearance"=>"Appearance","email"=>"Email Settings","fees"=>"Fee Settings"];
        return view("admin.settings",compact("settings","groupLabels"));
    }
    public function update(Request $request){
        $d=$request->validate(["settings"=>"required|array","settings.*"=>"nullable|string"]);
        foreach($d["settings"] as $k=>$v){
            $p=explode("__",$k,2);
            Setting::updateOrCreate(["key"=>$p[1]??$k],["value"=>$v??"","group"=>$p[0]??"general"]);
        }
        return redirect()->back()->with("success","Settings updated successfully.");
    }
}