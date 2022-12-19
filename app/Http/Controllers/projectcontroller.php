<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\client;
use Spatie\QueryBuilder\QueryBuilder;
use App\Models\project;
use App\Models\projecttask;
use App\Models\projectnote;
use App\Models\projectupdates;
use App\Models\User;
use App\Models\invoice;
use App\Models\tasktodos;
use App\Models\notifications;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\expensemanager;


class projectcontroller extends Controller
{
    
    public function listofprojects(Request $request)
    { 
        $client = client::all();
        $projects = QueryBuilder::for(project::class)
        ->allowedFilters(['projectname','client','status'])
        ->orderBy('id','desc')->paginate(15);       
        return view('app.listofprojects')->with(['projects' =>$projects])->with(['clients'=> $client]);     
    }

    public function createnewproject(Request $request)
    {   
        $project =new project();
        $project->projectname=$request->projectname;
        $project->client =$request->client;
        $project->description =$request->description;
        $project->startdate =$request->startdate;
        $project->deadline =$request->deadline;
        $project->status =1;
        $project->save();        
        $lastid = $project->id;
        $project =new projectnote();
        $project->pjid=$lastid;
        $project->admin = Auth::id();  
        $project->note ='Add Something';
        $project->save();

        return redirect('/projects')->with('success', 'Project Created');  
    }

    public function deleteproject(Request $request)
    {
     $project = project::findOrFail($request->id);      
     $project->delete();
     return redirect('/projects')->with('success', 'Project Deleted');  
    }

    public function viewproject(Request $request)
    {         
        $client = client::all();
        $project = project::findOrFail($request->id);  
        $projectupdates = projectupdates::where('projectid',$request->id)->orderby('id','desc')->paginate(5);
        $startdate = Carbon::parse($project->startdate);
        $deadline = Carbon::parse($project->deadline);
        $totaldays =   $startdate->diffInDays($deadline);           
        $today = Carbon::now();
        $balancedays = $today->diffInDays($deadline);  
         if($totaldays==0){ $totaldays=1; }
        $percentage = $balancedays * 100 / $totaldays;

        $counts=[];
        $counts['income']=invoice::where('projectid',$request->id)->sum('paidamount');
        $counts['expense']=expensemanager::where('prid',$request->id)->sum('amount');
        $counts['balance']= $counts['income'] - $counts['expense'];
        
        $invoices = invoice::where('type',2)->where('projectid',$request->id)->orderby('id','desc')->paginate(3);   

        return view('app.projectview')->with(['project' =>$project])->with(['prid' =>$request->id])->with(['percentage' =>$percentage])
        ->with(['balancedays' =>$balancedays])->with(['invoices' =>$invoices])->with(['projectupdates' =>$projectupdates])->with('counts', $counts); 
    }

    public function viewtasks(Request $request)
    { 
        $client = client::all();
        $users = User::all();
        $task = Projecttask::where('prid',$request->id)->paginate(30);                
        return view('app.projectviewtasks')->with(['tasks' =>$task])->with(['prid' =>$request->id])->with(['users' =>$users]);     
    }

    public function viewnote(Request $request)
    { 
        $client = client::all();
        $note = projectnote::where('pjid',$request->id)->first();            
        return view('app.projectviewnote')->with(['note' =>$note])->with(['prid' =>$request->id]);      
    }
    

    public function createprjcttask(Request $request)
    {   
        $project =new projecttask();
        $project->prid=$request->prid;
        $project->aid = Auth::id();  
        $project->task =$request->task;
        $project->assignedto =$request->assignedto;
        $project->type =$request->type;
        $project->status =1;
        $project->save();    

        //send notification 
        if($request->assignedto){
            $notif =new notifications();
            $notif->fromid =Auth::id();  
            $notif->toid =$request->assignedto;
            $notif->message ='New Project Task Assigned #'.$project->id;
            $notif->link ='/mytasks/view/'.$project->id;
            $notif->style =$request->type;
            $notif->type ='task';
            $notif->status =1;
            $notif->save();  
        }

        return redirect()->back()->with('success', 'Task Created');
    }

    public function notificationupdate(Request $request)
    {   
        $project = notifications::findOrFail($request->id);     
        $project->status =0;
        $project->save();     
        return redirect()->back()->with('success', 'Notification Updated');
    }

    public function updatenote(Request $request)
    {   
        $project = projectnote::findOrFail($request->id);       
        $project->admin = Auth::id();  
        $project->note =$request->note;
        $project->save();     
        return redirect()->back()->with('success', 'Note Updated');
    }

    public function projecttaskupdate(Request $request)
    {   
        $project = projecttask::findOrFail($request->id);     
        $project->status =$request->status;
        $project->save();     
        return redirect()->back()->with('success', 'Task Updated');
    }

    public function deletetasks(Request $request)
    {
     $project = projecttask::findOrFail($request->id);      
     $project->delete();
     return redirect()->back()->with('success', 'Task Deleted');
    }

    
    public function updateproject(Request $request)
    {   
        $project = project::findOrFail($request->id);     
        $project->projectname =$request->projectname;      
        $project->startdate =$request->startdate;
        $project->deadline =$request->deadline;     
        $project->save();     
        return redirect()->back()->with('success', 'Project Updated');
    }

    public function updateprojectdescript(Request $request)
    {   
        $project = project::findOrFail($request->id);         
        $project->description =$request->description;   
        $project->save();     
        return redirect()->back()->with('success', 'Project Updated');
    }

    
    public function projectstatuschange(Request $request)
    {   
        $project = project::findOrFail($request->id);       
        $project->status =$request->status;     
        $project->save();     
        return redirect()->back()->with('success', 'Status Updated');
    }

    public function mytasks(Request $request)
    {   
        $task = Projecttask::where('assignedto',Auth::id())->orderby('id','desc')->paginate(20);                
        return view('app.mytasks')->with(['tasks' =>$task]);   
    } 
    
    public function taskcomplete(Request $request)
    {   
        $project = Projecttask::findOrFail($request->id);       
        $project->status =2;     
        $project->save();     
        return redirect()->back()->with('success', 'Status Updated');
    }

    public function todostatusupdate(Request $request)
    {   
        $project = tasktodos::findOrFail($request->id);       
        $project->status = $request->status;     
        $project->save();     
        return redirect()->back()->with('success', 'Status Updated');
    }

    public function tasktododelete(Request $request)
    {   
        $project = tasktodos::findOrFail($request->id);       
         $project->delete();
        return redirect()->back()->with('success', 'Status Updated');
    }


    public function viewtask(Request $request)
    {   
        $task = Projecttask::where('assignedto',Auth::id())->where('id',$request->id)->firstOrFail();         
        $todos = tasktodos::where('taskid',$request->id)->orderby('id','desc')->get();           
        $taskcomments =  projectupdates::where('taskid',$request->id)->orderby('id','desc')->paginate(7);          
        return view('app.viewtask')->with(['task' =>$task])->with(['todos' =>$todos])->with(['taskcomments' =>$taskcomments]);   
    }

       
    public function addtasktodo(Request $request)
    {   
        $updates = new tasktodos();         
        $updates->taskid =$request->taskid;   
        $updates->task =$request->task;   
        $updates->auth =Auth::id();  
        $updates->status =0;     
        $updates->save();     
        return redirect()->back()->with('success', 'Task ToDo Added');
    }





    public function addprojectupdates(Request $request)
    {   
        $updates = new projectupdates();         
        $updates->projectid =$request->projectid;   
        $updates->taskid =$request->taskid;   
        $updates->auth =Auth::id();  
        $updates->message =$request->message;     
        $updates->save();     
        return redirect()->back()->with('success', 'Comment Added');
    }

    public function editprojectupdates(Request $request)
    {   
        $updates = projectupdates::find($request->id); 
        $updates->message =$request->message;     
        $updates->save();     
        return redirect()->back()->with('success', 'Comment Updated');
    }


    public function deleteupdates(Request $request)
    {
     $project = projectupdates::findOrFail($request->id);      
     $project->delete();
     return redirect()->back()->with('success', ' Update Deleted');
    }


    /********expense manager******** */

    public function viewprojectexpense(Request $request)
    { 
        $projects = project::all();
         $expenses = QueryBuilder::for(expensemanager::class)
         ->allowedFilters(['prid','date','status'])
         ->where('prid',$request->id)->orderBy('id','desc')->paginate(15);   
         return view('app.projectexpenses')->with(['expenses' =>$expenses])->with(['projects'=> $projects])->with(['prid' =>$request->id]);         
   
    }

    public function deleteexpense(Request $request)
    {
     $expense = expensemanager::findOrFail($request->id);      
     $expense->delete();
     return redirect()->back()->with('success', ' Expense Deleted');
    }

    
    
    
}
