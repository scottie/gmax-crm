<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\setting;
use App\Models\User;
use App\Models\business;
class SettingsController extends Controller
{
    public function settings(Request $request)
    {
       $setings = setting::first();
       return view('settings.settings')->with(['settings' =>$setings]);
    }
   public function updatesettings(Request $request)
{
    // Check if the business name is not NULL (optional)
    if ($request->businessname != NULL) {
        // Find the settings record
        $settings = setting::find(1);
        $settings->companyname = $request->businessname;

        // Check if a logo file has been uploaded
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            
            // Validate the file type to ensure it's an allowed image format (e.g., PNG, JPEG)
            $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif']; // Define allowed file extensions
            $extension = $file->getClientOriginalExtension();

            if (in_array(strtolower($extension), $allowedExtensions)) {
                // Generate a unique filename to prevent overwriting existing files
                $filename = uniqid() . '.' . $extension;

                // Move the uploaded file to a secure directory
                $file->move(public_path('storage/uploads/'), $filename);

                // Update the settings with the new filename
                $settings->logo = $filename;
            } else {
                // Handle the case when an invalid file type is uploaded
                return redirect()->back()->with('error', 'Invalid file type. Only PNG, JPG, JPEG, and GIF are allowed.');
            }
        }

        // Save the updated settings
        $settings->save();
    }

    return redirect()->back()->with('success', 'Software information updated');
}

    public function billingsetting(Request $request)
    {
       $setings = setting::first();
       return view('settings.billing')->with(['settings' =>$setings]);
    }

    public function billingsettingsave(Request $request)
    {
     
            $settings =setting::find(1);            
            $settings->prefix =$request->prefix;   
            $settings->suffix =$request->suffix;
            $settings->taxstatus =$request->taxstatus;   
            $settings->taxname =$request->taxname;
            $settings->taxpercent =$request->taxpercent;   
            $settings->invoicenote =$request->invoicenote;
            $settings->quotenote =$request->quotenote;           
            $settings->save();        
            return redirect()->back()->with('success', 'Billing information updated'); 
    }

    public function businesssetting(Request $request)
    {
       $business = business::find(1);
       return view('settings.business')->with(['business' =>$business]);
    }

    public function businesssettingsave(Request $request)
    {
     
            $settings =business::find(1);            
            $settings->businessname =$request->businessname;   
            $settings->email =$request->email;
            $settings->contactnum =$request->contactnum;   
            $settings->taxid =$request->taxid;
            $settings->address =$request->address;   
            $settings->address2 =$request->address2;
            $settings->city =$request->city;  
            $settings->state =$request->state;   
            $settings->country =$request->country;
            $imagefile = $request->logo;
            if($imagefile!=NULL) {               
                $filename    = time().'.'.$request->logo->extension();  
                $request->logo->move(public_path('storage/uploads/'), $filename);                        
                $settings->logo =$filename;       
            }        
            $settings->status =1;              
            $settings->save();        
            return redirect()->back()->with('success', 'Business information updated');  
    }


    public function invoicesettings(Request $request)
    {
       $business = business::find(1);
       return view('settings.invoicesettings')->with(['business' =>$business]);
    }

    public function invoicesettingssave(Request $request)
    {
     
            $settings =business::find(1); 
            $settings->enablelogo =$request->enablelogo;       
            $headerimage = $request->headerimage;
            if($headerimage!=NULL) {               
                $filename    = time().'.'.$request->headerimage->extension();  
                $request->headerimage->move(public_path('storage/uploads/'), $filename);                        
                $settings->headerimage =$filename;       
            }  
            $footerimage = $request->footerimage;
            if($footerimage!=NULL) {               
                $filename    = time().'.'.$request->footerimage->extension();  
                $request->footerimage->move(public_path('storage/uploads/'), $filename);                        
                $settings->footerimage =$filename;       
            }  
                
            $settings->save();        
            return redirect()->back()->with('success', 'Invoice Settings Updated');  
    }

    
    
}
