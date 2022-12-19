<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use Artisan;
use Illuminate\Support\Facades\App;
class AdminController extends Controller
{
    public function index(Request $request)
    {
        $users = User::orderBy('id','desc')->paginate(15);
        return view('admin.listofadmin')->with(['users' =>$users]);
    }

    public function createnewadmin(Request $request)
    {
        if($request->name!=NULL){
        $user =new User();
        $user->name =$request->name;
        $user->email  =$request->email;
        $user->password =Hash::make($request['password']);
        $user->usertype = $request->usertype;
        $user->save();        
        }
        return redirect('/admin/listofusers');
    }

    public function updateadmin(Request $request)
    {
        if($request->name!=NULL){
            $user =User::find($request->id);
            $user->name =$request->name;
            $user->email  =$request->email;
                if($request->password!=NULL){
                    $user->password =Hash::make($request['password']); 
                }
            $user->usertype = $request->usertype;
            $user->save();        
            }
            return redirect('/admin/listofusers');
    }

    public function deleteadmin(Request $request)
    {
        $user = User::find($request->id);
        $user->delete();
        return redirect('/admin/listofusers');
    }


    /// system info and update

    public function updatesystem(Request $request)
    {      
        return view('app.update');
    }

    public function languageswitch(Request $request)
    {   
        app()->setLocale($request->lang);
        session()->put('locale', $request->lang);      
        return redirect('/dashboard');
    }

    public function softwareupdate(Request $request)
    {      
        return view('app.softwareupdate');
    }

    public function runupdate(Request $request)
    {      
       // run migration
       Artisan::call('migrate');
       return view('app.softwareupdatecomplete');
    }
    
}
