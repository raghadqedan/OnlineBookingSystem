<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\ServiceQueue;
class ControlQueues extends Controller
{

    public function getCurrentCustomer($queue_id){

            $company_type=Company::selectRaw('type')->where('id',auth()->user()->company_id)->where('status',1)->first();
                if( $company_type->type==0){//if companytype is number
                //return the turned customer id to this queue if exist
                        $currentCustomerId=Booking::selectRaw('customer_id')->where('queue_id',$queue_id)->where('status',0)->where('priority',100)->orderBy('number', 'asc')->first();

                        if($currentCustomerId){
                                $customer= Customer::where('id',$currentCustomerId->customer_id)->first();
                                return response()->json(['customer'=>$customer ]);
                        }else{
                            $currentCustomerId=Booking::selectRaw('customer_id')->where('queue_id',$queue_id)->where('status',0)->orderBy('number', 'asc')->first();
                            if($currentCustomerId){
                                    $customer= Customer::where('id',$currentCustomerId->customer_id)->first();
                                    return response()->json(['customer'=>$customer ]);}
                            }
                                return  response()->json(['message'=>"operation failed" ]);
                    }else{
                            //if company type is time
                            $allAppointment_id=Booking::selectRaw('appointment_id')->where('queue_id',$queue_id)->where('status',0)->where('priority',100)->get();
                            if(count($allAppointment_id)){
                                        $min_start_time_appointment_id;
                                        $lock=1;
                                        foreach($allAppointment_id as $obj){
                                                $appointment=Appointment::where('id',$obj->appointment_id)->where('status',1)->first();
                                                if($appointment){
                                                        if($lock){
                                                            $min_start_time_appointment=$appointment;
                                                            $lock=0; }

                                                        if($appointment->start_time<=$min_start_time_appointment->start_time){
                                                                    $min_start_time_appointment=$appointment;}


                                                }return  response()->json(['message'=>"operation failed" ]);
                                            }
                                        $customer_id=Booking::selectRaw('customer_id')->where('appointment',$min_start_time_appointment->id)->first();
                                        return response()->json(['customer'=>$customer=Customer::where('id',$customer_id->id)->first()]);

                            }
                            else{
                                $allAppointment_id=Booking::selectRaw('appointment_id')->where('queue_id',$queue_id)->where('status',0)->get();
                                if(count($allAppointment_id)){
                                        $min_start_time_appointment_id;
                                        $lock=1;
                                        foreach($allAppointment_id as $obj){
                                                    $appointment=Appointment::where('id',$obj->appointment_id)->where('status',1)->first();
                                                    if($appointment){
                                                            if($lock){
                                                                    $min_start_time_appointment=$appointment;
                                                                    $lock=0;
                                                                }
                                                            if($appointment->start_time<=$min_start_time_appointment->start_time&&$appointment->start_time<= date("h:i:sa")){
                                                                $min_start_time_appointment=$appointment;
                                                                }


                                                    }return   response()->json(['message'=>"operation failed" ]);

                                            }
                                        $customer_id=Booking::selectRaw('customer_id')->where('appointment',$min_start_time_appointment->id)->first();
                                        return response()->json(['customer'=>$customer=Customer::where('id',$customer_id->id)->first()]);
                                    }
                                }



            }
    }






    function turnCustomer($booking_id,$service_id){

            $customer_id=Booking::selectRaw('customer_id')->where('id',$booking_id)->first();
            $sq=ServiceQueue::selectRaw('queue_id')->where('service_id',$service_id)->get();

            if(count($sq)){
                                $min_count;
                                $min_queue;
                                $lock=1;

                        foreach($sq as $obj){
                                $booking=Booking::where('queue_id',$obj->queue_id)->where('status',0)->get();

                                if($booking){
                                        if($lock){
                                                $min_count=count($booking);
                                                $queue_id=$obj->queue_id;
                                                $lock=0;
                                        }
                                    if(count($booking)<=$min_count){
                                        $min_count=count($booking);
                                        $min_queue=$obj->queue_id;
                                    }


                                }

                        }

                        $result=Booking::create([
                            'service_id'=>$service_id,
                            'queue_id'=>$min_queue,
                            'customer_id'=>$customer_id->customer_id,
                            'status'=>"0",
                            'priority'=>"100",
                            'date'=>date('y-m-d')


                        ]);
                    if($result){
                            $b=Booking::where('id',$booking_id)->first();
                            $b->update(['status'=>1]);
                            return  response()->json(['message'=>"Turned customer successfully" ]);

                    }
        }

            return  response()->json(['message'=>"operation failed" ]);

    }









    function CheckOut($booking_id){
            //when check out customer set appointment atatus=0,booking status=2
            $booking=Booking::where('id',$booking_id)->where('status',0)->first();
            if($booking){
                    $booking->update(['status'=>3]);
                    $appointment=Appointment::where('id',$booking->appointment_id)->where('status',1)->first();
                    $appointment->update(['status'=>0]);
            }

    }








//note in get appointment get the allappointment with start_time >current_time







}
