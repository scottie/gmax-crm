<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\client;
use App\Models\invoice;
use Carbon\Carbon;
use App\Models\Invoicemeta;
use App\Models\paymentrecepit;
use Illuminate\Support\Facades\Mail;
use App\Mail\invoicemail;
use App\Mail\quotemail;
use App\Models\setting;
use App\Models\notifications;
use App\Models\project;
use App\Models\projecttask;
use App\Models\business;
use App\Models\expensemanager;
use App\Models\paymentgateway;
use Spatie\QueryBuilder\QueryBuilder;

use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function dashboard(Request $request)
    { 
        $today =date("Y-m-d");
        $client = client::all();
        $data=[];
        for($x=0; $x<40; $x++){
          $seldate = Carbon::now()->subDays($x)->toDateString();
          $invoice = paymentrecepit::whereDate('created_at', $seldate)->get();
          $count = $invoice->sum('amount');
          $data[$x]['count']=$count;
          $data[$x]['date']=$seldate;
        }        
        $quotedata=[];
        for($x=0; $x<40; $x++){
          $seldate = Carbon::now()->subDays($x)->toDateString();
          $expense = expensemanager::whereDate('date', $seldate)->get();
          $count = $expense->sum('amount');
          $quotedata[$x]['count']=$count;
          $quotedata[$x]['date']=$seldate;
        }

        $counts=[];
        $counts['unpaid']=invoice::where('invostatus',1)->count();
        $counts['paid']=invoice::where('invostatus',3)->count();
        $counts['quotes']=invoice::where('type',1)->count();
        $counts['prjnotstart']=project::where('status',1)->count();
        $counts['prjinprogress']=project::where('status',2)->count();
        $counts['prjinreview']=project::where('status',3)->count();
        $counts['prjincompleted']=project::where('status',5)->count();

        $tasks = Projecttask::where('assignedto',Auth::id())->where('status',1)->orderby('id','desc')->get();   
        $invoices = invoice::where('invostatus','1')->orWhere('invostatus','2')->orderby('id','desc')->paginate(3);   
        $notifications = notifications::where('status',1)->where('toid',Auth::id())->orderby('id','desc')->paginate(5);   
        return view('dashboard')->with(['invoices' =>$invoices])->with(['clients'=> $client])->with('datas', $data)
        ->with('quotedata', $quotedata)->with('counts', $counts)->with('tasks', $tasks)->with('notifications', $notifications);  
    }

    public function listofinvoices(Request $request)
    { 
        $counts=[];
        $counts['unpaid']=invoice::where('invostatus',1)->count();
        $counts['partpaid']=invoice::where('invostatus',2)->count();
        $counts['paid']=invoice::where('invostatus',3)->count();
        $counts['canceled']=invoice::where('invostatus',5)->count();
   

        $client = client::all();
        $invoices = QueryBuilder::for(invoice::class)
        ->allowedFilters(['title','userid','invoid','invostatus'])
        ->where('type',2)->orderBy('id','desc')->paginate(15);
        //$invoices = invoice::orderby('id','desc')->where('type',2)->paginate(15);     
        return view('app.listofinvoices')->with(['invoices' =>$invoices])->with(['clients'=> $client])->with('counts', $counts);     
    }

    public function createnewinvoice(Request $request)
    {       
        $todaydate = date('Y-m-d');
        $invoices = Invoice::where('type',2)->orderby('id','desc')->first();  
        $invoice =new Invoice();
        $invoice->type=2;
        $invoice->title =$request->title;
        $invoice->userid =$request->userid;
        $invoice->adminid = Auth::id();  
        if(Invoice::where('type',2)->count()==0){
        $invoice->invoid =1; }
        else{
            $invoice->invoid =$invoices->invoid+1; 
        }
        $invoice->invodate = $todaydate;       
        $invoice->duedate = $todaydate;   
        $invoice->totalamount =0;       
        $invoice->paidamount = 0;     
        $invoice->invostatus = 1;        
        $invoice->projectid = $request->projectid;     
        $invoice->save();        
        $lastid = $invoice->id;
       return redirect('/invoice/edit/'.$lastid);
    }

    
    public function editinvoicedata(Request $request)
    {
     $invoice = Invoice::findOrFail($request->invoiceid);   
     $invoice->title =$request->title;   
     $invoice->invodate = $request->invodate;
     $invoice->duedate = $request->duedate;
     $invoice->save();  
     return redirect()->back()->with('success', 'Invoice Updated'); 
    }

    public function editinvoice(Request $request)
    {
     $invoices = Invoice::findOrFail($request->id);      
     $invometas = Invoicemeta::where('invoiceid',$request->id)->paginate(100);  
     $paymentrecepit = paymentrecepit::where('invoiceid',$request->id)->paginate(100);
     return view('app.editinvoice')->with(['invoice'=> $invoices])->with(['invometas'=> $invometas])->with(['payments'=> $paymentrecepit]);
    }
 
    public function newinvoicemeta(Request $request)
     { 
        $invoices = Invoice::findOrFail($request->invoiceid);   
        $settings =setting::find(1);   
        $gettaxedamount =0;                  
         $invoicemeta =new Invoicemeta();
         $invoicemeta->invoiceid=$request->invoiceid;
         $invoicemeta->authid = Auth::id();  
         $invoicemeta->qty =$request->qty;
         $invoicemeta->qtykey =$request->qtykey;
         $invoicemeta->meta =$request->meta;
         if($invoices->taxable==1){
             $ttcost = $request->qty * $request->amount;
             $gettaxedamount = $settings->taxpercent * $ttcost /100;
            $invoicemeta->tax =$gettaxedamount;
         }
         $invoicemeta->amount = $request->amount;
         $invoicemeta->total =$request->qty * $request->amount + $gettaxedamount;                   
         $invoicemeta->save();      

          //get invoice updated             
          $invoicemetadata =Invoicemeta::where('invoiceid',$request->invoiceid)->sum('total');                             
         $invoices->totalamount = $invoicemetadata;  
          $invoices->save();               
        return redirect()->back();   
     }

     public function editinvoicemeta(Request $request)
     {             
        if($request->meta!=NULL)
        {
        $invoices = Invoice::findOrFail($request->invoiceid);   
        $settings =setting::find(1);   
        $gettaxedamount =0;     
        $invoicemeta =Invoicemeta::findOrFail($request->metaid);         
         $invoicemeta->authid = Auth::id();  
         $invoicemeta->invoiceid=$request->invoiceid;
         $invoicemeta->qty =$request->qty;
         $invoicemeta->qtykey =$request->qtykey;
         $invoicemeta->meta =$request->meta;
         if($invoices->taxable==1){
            $ttcost = $request->qty * $request->amount;
            $gettaxedamount = $settings->taxpercent * $ttcost /100;
           $invoicemeta->tax =$gettaxedamount;
        }
         $invoicemeta->amount = $request->amount;
         $invoicemeta->total =$request->qty * $request->amount  + $gettaxedamount;                               
         $invoicemeta->save();            

         //get invoice updated   
      
         $invoicemetadata =Invoicemeta::where('invoiceid',$request->invoiceid)->get(); 
         $totalamt = 0;      
         foreach ($invoicemetadata as $value) {
           $totalamt += $value->total;
         }                   
         $invoices->totalamount = $totalamt;  
         $invoices->save();                    
         
         return redirect()->back()->with('success', 'Invoice Updated'); 
        }
        else
        {
            return redirect()->back()->with('warning', 'Particular cannot be blank');
        }

          
     }

     public function deleteinvoicemeta(Request $request)
     {       
         $invometa =$request->id;      
         $invoid =$request->invo;        
         if($invometa!=NULL)
         {
            $meta = Invoicemeta::where('invoiceid',$invoid)->where('id',$invometa)->first();
           $meta->delete();

          //get invoice updated   
          $invoices = Invoice::findOrFail($request->invo); 
          $invoicemetadata =Invoicemeta::where('invoiceid',$request->invo)->get(); 
          $totalamt = 0;      
          foreach ($invoicemetadata as $value) {
            $totalamt += $value->total;
          }                   
         $invoices->totalamount = $totalamt;  
          $invoices->save();   

         return redirect()->back()->with('success', 'Item Deleted');
         }
         else
         {
             return redirect()->back()->with('danger', 'Please Try Again');
         }

         
 
     }

     public function invoicetaxenable(Request $request)
    {    
        $invoice =Invoice::findOrFail($request->id);       
        $invoice->taxable=$request->taxable;             
        $invoice->save();  
        return redirect()->back()->with('success', ' Tax Info Updated');
    }


     public function invopaymentsave(Request $request)
    { 
        if($request->amount!=NULL)
        {
            $paymentrecepit =new paymentrecepit();
            $paymentrecepit->invoiceid=$request->invoiceid;
            $paymentrecepit->adminid = Auth::id();  
            $paymentrecepit->amount =$request->amount;
            $paymentrecepit->date =$request->date;
            $paymentrecepit->transation =$request->transation;
            $paymentrecepit->note =$request->note;                          
            $paymentrecepit->save();      

            $invoices = Invoice::findOrFail($request->invoiceid);
            $currentpaid =  $invoices->paidamount;
            $invoices->paidamount = $currentpaid + $request->amount; 
            $nowpaid=  $currentpaid + $request->amount;
            if($invoices->totalamount==$nowpaid)
            {
                $invoices->invostatus = 3;        
            }   
            else
            {
                $invoices->invostatus = 2;    
            }                    
            $invoices->save();  

            

            return redirect()->back()->with('success', 'Payment Added');
        }
        else
        {
            return redirect()->back()->with('warning', 'Amount cannot be blank');
        }
        
    }

    public function deletepayment(Request $request)
     {       
         $payment =$request->id;      
         $invoid =$request->invo;        
         if($invoid!=NULL)
         {
           $meta = paymentrecepit::where('invoiceid',$invoid)->where('id',$payment)->first();
           $getamount = $meta->amount;
           $invoices = Invoice::findOrFail($request->invo); 
           $getpaidamount = $invoices->paidamount;
           $revesedpayment = $getpaidamount - $getamount;
           $invoices->paidamount =$revesedpayment;
           $invoices->save();              
           $meta->delete();
       
         return redirect()->back()->with('success', 'Payment Reversed');
         }
         else
         {
             return redirect()->back()->with('danger', 'Please Try Again');
         }
     }


     public function cancelinvoice(Request $request)
     {
        if($request->id!=NULL)
        {
            $invoices = Invoice::findOrFail($request->id);         
            $invoices->invostatus = 5;            
            $invoices->save();  
            return redirect()->back()->with('success', 'Invoice Cancelled');
        }
        else
        {
            return redirect()->back()->with('danger', 'Please Try Again');
        }
     }


     public function cancelrecurring(Request $request)
     {
        if($request->id!=NULL)
        {
            $invoices = Invoice::findOrFail($request->id);         
            $invoices->recorring =NULL;            
            $invoices->save();  
            return redirect()->back()->with('success', 'Recurring Invoice Cancelled');
        }
        else
        {
            return redirect()->back()->with('danger', 'Please Try Again');
        }
     }

     public function refundinvoice(Request $request)
     {
        if($request->amount!=NULL)
        {
            $paymentrecepit =new paymentrecepit();
            $paymentrecepit->invoiceid=$request->invoiceid;
            $paymentrecepit->adminid = Auth::id();  
            $paymentrecepit->amount =-$request->amount;
            $paymentrecepit->date =$request->date;
            $paymentrecepit->transation =$request->transation;
            $paymentrecepit->note =$request->note;                          
            $paymentrecepit->save();      

            $invoices = Invoice::findOrFail($request->invoiceid);
            $currentpaid =  $invoices->paidamount;
            $invoices->paidamount = $currentpaid - $request->amount; 
            $invoices->invostatus = 4;                            
            $invoices->save();              

            return redirect()->back()->with('success', 'Refund Issued');
        }
        else
        {
            return redirect()->back()->with('warning', 'Amount cannot be blank');
        }
     }

     public function viewinvoice(Request $request)
     {
      
      $invoices = Invoice::findOrFail($request->id);      
      $business = business::find(1); 
      $invometas = Invoicemeta::where('invoiceid',$request->id)->paginate(100);  
      $paymentrecepit = paymentrecepit::where('invoiceid',$request->id)->paginate(100);
      return view('app.viewinvoice')->with(['invoice'=> $invoices])->with(['invometas'=> $invometas])->with(['payments'=> $paymentrecepit])->with(['business'=> $business]);
     }


     public function emailinvoice(Request $request)
     {
      $invoices = Invoice::findOrFail($request->id);      
      Mail::to($invoices->clientdata->email)->send(new invoicemail($invoices));
      return redirect()->back()->with('success', 'Mail Sent!');
     }


     public function payinvoice(Request $request)
     {
      
      $invoices = Invoice::findOrFail($request->id);      
      $business = business::find(1); 
      $gateways = paymentgateway::where('status',1)->get();
      $invometas = Invoicemeta::where('invoiceid',$request->id)->paginate(100);  
      $paymentrecepit = paymentrecepit::where('invoiceid',$request->id)->paginate(100);
      return view('app.payinvoice')->with(['invoice'=> $invoices])->with(['invometas'=> $invometas])->with(['payments'=> $paymentrecepit])->with(['business'=> $business])->with(['gateways'=> $gateways]);
     }
     
     public function deleteinvoice(Request $request)
     {
      $invoices = Invoice::findOrFail($request->id);      
      $invoices->delete();
      return redirect('/dashboard')->with('success', 'Record Deleted');  
     }



     /****************************** Quotes ******************************* */

     public function listofquotes(Request $request)
     { 
         $client = client::all();
         $invoices = QueryBuilder::for(invoice::class)
        ->allowedFilters(['title','userid','quoteid','quotestat'])
        ->where('type',1)->orderBy('id','desc')->paginate(15);
        // $invoices = invoice::orderby('id','desc')->where('type',1)->paginate(15);     
         return view('app.listofquotes')->with(['invoices' =>$invoices])->with(['clients'=> $client]);     
     }
 
     public function createnewquotes(Request $request)
     {       
         $todaydate = date('Y-m-d');
         $invoices = Invoice::orderby('id','desc')->where('type',1)->first();  
         $invoice =new Invoice();
         $invoice->type=1;
         $invoice->title =$request->title;
         $invoice->userid =$request->userid;
         $invoice->adminid = Auth::id();  
         if(Invoice::where('type',1)->count()==0){
         $invoice->quoteid =1; }
         else{
             $invoice->quoteid =$invoices->quoteid+1; 
         }
         $invoice->invodate = $todaydate;       
         $invoice->duedate = $todaydate;   
         $invoice->totalamount =0;       
         $invoice->paidamount = 0;     
         $invoice->quotestat = 1;             
         $invoice->save();        
         $lastid = $invoice->id;
        return redirect('/quote/edit/'.$lastid);
     }
 
     public function editquote(Request $request)
     {
      $invoices = Invoice::findOrFail($request->id);      
      $invometas = Invoicemeta::where('invoiceid',$request->id)->paginate(100);  
      $paymentrecepit = paymentrecepit::where('invoiceid',$request->id)->paginate(100);
      return view('app.editquote')->with(['invoice'=> $invoices])->with(['invometas'=> $invometas])->with(['payments'=> $paymentrecepit]);
     }

     
     public function emailquote(Request $request)
     {
      $invoices = Invoice::findOrFail($request->id);      
      Mail::to($invoices->clientdata->email)->send(new quotemail($invoices));
      return redirect()->back()->with('success', 'Mail Sent!');
     }

     public function viewquote(Request $request)
     {
      
      $invoices = Invoice::findOrFail($request->id);      
      $business = business::find(1); 
      $invometas = Invoicemeta::where('invoiceid',$request->id)->paginate(100);  
      $paymentrecepit = paymentrecepit::where('invoiceid',$request->id)->paginate(100);
      return view('app.viewquote')->with(['invoice'=> $invoices])->with(['invometas'=> $invometas])->with(['payments'=> $paymentrecepit])->with(['business'=> $business]);
     }

     public function viewquotepublic(Request $request)
     {
      
      $invoices = Invoice::findOrFail($request->id);      
      $business = business::find(1); 
      $invometas = Invoicemeta::where('invoiceid',$request->id)->paginate(100);  
      $paymentrecepit = paymentrecepit::where('invoiceid',$request->id)->paginate(100);
      return view('app.viewquotepublic')->with(['invoice'=> $invoices])->with(['invometas'=> $invometas])->with(['payments'=> $paymentrecepit])->with(['business'=> $business]);
     }

     public function quotestatuschange(Request $request)
     {
      $invoices = Invoice::findOrFail($request->id);    
      $invoices->quotestat =$request->stat;             
      $invoices->save();   
      return redirect()->back()->with('success', 'Status Updated!');  
     }
     
     public function quotestatuschangepublic(Request $request)
     {
      if($request->stat==2 || $request->stat==4){
      $invoices = Invoice::findOrFail($request->id);    
      $invoices->quotestat =$request->stat;             
      $invoices->save();   
      return redirect()->back()->with('success', 'Status Updated!');  
      }
      else
      {
        return redirect()->back()->with('warning', 'Error Please Try Again! ');  
      }
     }


     /********* invoice ******* */
     
     
     public function converttoinvo(Request $request)
     {
      $invoicesold = Invoice::orderby('id','desc')->where('type',2)->first();  
      $invoices = Invoice::findOrFail($request->id);    
      if(Invoice::where('type',2)->count()==0){
        $invoices->invoid =1; }
        else{
            $invoices->invoid =$invoicesold->invoid+1; 
        }
      $invoices->invostatus =1;
      $invoices->type =2;             
      $invoices->save();   
      return redirect('/invoice/edit/'.$request->id)->with('success', 'Converted as Invoice');  
     }
     
     /*****************expense manager and cashbook************** */
     
     public function expensemanagerlist(Request $request)
     { 
         $projects = project::all();
         $expenses = QueryBuilder::for(expensemanager::class)
         ->allowedFilters(['prid','date','status'])
         ->orderBy('id','desc')->paginate(15);   

         $thisday = Carbon::now()->format('Y-m-d');  

        $counts=[];
        $counts['today']=expensemanager::where('date',$thisday)->sum('amount');
        $counts['thismonth']=expensemanager::whereMonth('date',date('m'))->sum('amount');
        $counts['lastmonth']=expensemanager::whereMonth('date', '=', Carbon::now()->subMonth()->month)->sum('amount');
        $counts['thisyear']=expensemanager::whereYear('date', date('Y'))->sum('amount');

         return view('app.listofexpenses')->with(['expenses' =>$expenses])->with(['projects'=> $projects])->with('counts', $counts);       
     }

     public function createnewexpense(Request $request)
     {
      
             $expense =new expensemanager(); 
             $expense->prid =$request->prid;  
             $expense->item =$request->item;  
             $expense->amount =$request->amount;  
             $expense->date =$request->date;         
             $expense->auth =Auth::id();  
             $expense->status =1;       
             $bill = $request->bill;
             if($bill!=NULL) {               
                 $filename    = time().'.'.$request->bill->extension();  
                 $request->bill->move(public_path('storage/uploads/'), $filename);                        
                 $expense->bill =$filename;       
             }                      
             $expense->save();        
             return redirect()->back()->with('success', 'Expense Added');  
     }

     public function editexpense(Request $request)
     {
      
             $expense =expensemanager::findOrFail($request->id);    
             $expense->prid =$request->prid;  
             $expense->item =$request->item;  
             $expense->amount =$request->amount;  
             $expense->date =$request->date;           
             $bill = $request->bill;
             if($bill!=NULL) {               
                 $filename    = time().'.'.$request->bill->extension();  
                 $request->bill->move(public_path('storage/uploads/'), $filename);                        
                 $expense->bill =$filename;       
             }                      
             $expense->save();        
             return redirect()->back()->with('success', 'Expense Updated');  
     }
 

     public function cashbooklist(Request $request)
     { 
         $projects = project::all();


         $expenses = QueryBuilder::for(expensemanager::class)
         ->allowedFilters(['date',])
         ->orderBy('id','desc')->get();   

         $paymentrecepit = QueryBuilder::for(paymentrecepit::class)
         ->allowedFilters(['date'])
         ->orderBy('id','desc')->get();   

       return view('app.cashbook')->with(['expenses' =>$expenses])->with(['recepits' =>$paymentrecepit])->with(['projects'=> $projects]);     
     }



     /**************file manager*********** */
     public function filemanager(Request $request)
     {   
         return view('app.filemanager');     
     }


     /************ recorring invoice ************/

     public function createrecorringinvoice(Request $request)
     {       
         $todaydate = date('Y-m-d');
         $invoices = Invoice::where('type',2)->orderby('id','desc')->first();  
         $invoice =new Invoice();
         $invoice->type=2;
         $invoice->title =$request->title;
         $invoice->userid =$request->userid;
         $invoice->adminid = Auth::id();  
         if(Invoice::where('type',2)->count()==0){
         $invoice->invoid =1; }
         else{
             $invoice->invoid =$invoices->invoid+1; 
         }         
         $invoice->totalamount =0;       
         $invoice->paidamount = 0;     
         $invoice->invostatus = 1;        
         $invoice->projectid = $request->projectid;     

         $invoice->recorring = 1;     
         $invoice->recorringtype = $request->recorringtype; 
         
         $getinvocedate = new Carbon($request->invodate);
         $invoice->invodate = $request->invodate;       
         $invoice->duedate = $request->invodate;  

         if($request->recorringtype==1) //daily
         {           
             $invoice->recorringnextdate = $getinvocedate->addDay(); 
        }
        if($request->recorringtype==2) //weekly
         {
              $invoice->recorringnextdate = $getinvocedate->addWeek(); 
         } 
        if($request->recorringtype==3) //monthly
         {
             $invoice->recorringnextdate =$getinvocedate->addMonth(); 
         }
        if($request->recorringtype==4) //yearly
         {
             $invoice->recorringnextdate = $getinvocedate->addYear(); 
         }                       
         $invoice->save();        
         $lastid = $invoice->id;
        return redirect('/invoice/edit/'.$lastid);
     }

     // run this as cron once everyday to create recorring invoices


     public function recorringinvoicecron(Request $request)
     {  
        $todaydate = date('Y-m-d');

        echo "Gmax CRM Daily Cron job 🚀 <br>";

        //create a loop here and check any pending invoice
        $reccur = Invoice::where('recorring',1)->whereDate('recorringnextdate',$todaydate)->get();

        foreach($reccur as $rcinvo)
        {   
            echo "New Invoice Created <br>";   
            $invoices = Invoice::where('type',2)->orderby('id','desc')->first();  
            $invoice =new Invoice();
            $invoice->type=2;
            $invoice->title =$rcinvo->title;
            $invoice->userid =$rcinvo->userid;
            $invoice->adminid = $rcinvo->adminid;  
            if(Invoice::where('type',2)->count()==0){
            $invoice->invoid =1; }
            else{
                $invoice->invoid =$invoices->invoid+1; 
            }         
            $invoice->totalamount =0;       
            $invoice->paidamount = 0;     
            $invoice->invostatus = 1;        
            $invoice->projectid = $rcinvo->projectid;     

            $invoice->recorring = 1;     
            $invoice->recorringtype = $rcinvo->recorringtype; 
            
            $getinvocedate = new Carbon($todaydate);
            $invoice->invodate = $todaydate;       
            $invoice->duedate = $todaydate;  

            if($rcinvo->recorringtype==1) //daily
            {           
                $invoice->recorringnextdate = $getinvocedate->addDay(); 
            }
            if($rcinvo->recorringtype==2) //weekly
            {
                $invoice->recorringnextdate = $getinvocedate->addWeek(); 
            } 
            if($rcinvo->recorringtype==3) //monthly
            {
                $invoice->recorringnextdate =$getinvocedate->addMonth(); 
            }
            if($rcinvo->recorringtype==4) //yearly
            {
                $invoice->recorringnextdate = $getinvocedate->addYear(); 
            }                       

            $invoice->save();   
                 
            echo "New Invoice Saved <br>";   
            // adding metas into it
            $invometas = Invoicemeta::where('invoiceid',$rcinvo->id)->get();
            foreach($invometas as $recrmeta)
            { 
                echo "New Meta Added <br>";   
                $settings =setting::find(1);   
                $gettaxedamount =0;                  
                 $invoicemeta =new Invoicemeta();
                 $invoicemeta->invoiceid=$invoice->id;
                 $invoicemeta->authid = Auth::id();  
                 $invoicemeta->qty =$recrmeta->qty;
                 $invoicemeta->qtykey =$recrmeta->qtykey;
                 $invoicemeta->meta =$recrmeta->meta;
                 if($invoice->taxable==1){
                     $ttcost = $recrmeta->qty * $recrmeta->amount;
                     $gettaxedamount = $settings->taxpercent * $ttcost /100;
                    $invoicemeta->tax =$gettaxedamount;
                 }
                 $invoicemeta->amount = $recrmeta->amount;
                 $invoicemeta->total =$recrmeta->qty * $recrmeta->amount + $gettaxedamount;                   
                 $invoicemeta->save();      
        
                  //get invoice updated             
                  $invoicemetadata =Invoicemeta::where('invoiceid',$recrmeta->invoiceid)->sum('total');                             
                 $invoice->totalamount = $invoicemetadata;  
                  $invoice->save();               
            }

            echo "New Invoice Completed <br>";   

        }    

      
     }


}
