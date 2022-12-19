@extends('layouts.app')
@section('title', 'Page Title')
@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-lg">
              <div class="card-body">
                <h1 class="m-0">Changelog</h1>
                <br>

       


                {{__('name')}}
                <h2>v2.0 - -- -- 2022 - Major Update</h2>
                <ul>
                  <li>Update Notification </li>       
                  <li>Multi Language Support</li>
                  <li>Customize Invoices</li>  
                  <li>Recurring Invoices</li>   
                  <li>Unpaid Invoice Reminders</li>    
                  <li>Customize Quotes</li>

                              
                </ul>

                <h2>v1.2 - 10 jun 2022 </h2>
                <ul>
                  <li>Project Bug fixed</li>       
                  <li>Modified Invoice</li>
                  <li>Modified Quote</li>      
                              
                </ul>
                
                <h2>v1.1 - 15 mar 2021  </h2>
                <ul>
                  <li>Bugs Fixed</li>       
                  <li>Added Paypal Support</li>          
                  <li>Added Razorpay Support</li>          
                  <li>Added Stripe Support</li>                  
                </ul>


                <h2>v1.0 - 07 mar 2021- Initial Release </h2>
                  <ul>
                    <li>Initial beta release of Gmax CRM v1.0! Lots more coming soon though 😁</li>                  
                  </ul>

              </div>
            </div>
        </div>
    </div>

@endsection
