<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

use DB;
use App\Models\Entry;


class Massage extends Model
{

    protected $table = 'massage_entries';

    public static function totalShiftData(){
        $check_shift = Entry::checkShift();
        
        $total_shift_cash = 0;
        $total_shift_upi = 0;       

        $last_hour_cash_total = 0;
        $last_hour_upi_total = 0;

        // $from_time = date('Y-m-d H:00:00');
        // $to_time = date('Y-m-d H:59:59');

        // dd($from_time);

        $p_date = Entry::getPDate();

        // if($type == 2){
        //     $p_date = date("Y-m-d",strtotime("-1 day",strtotime($p_date)));
        // }

        $shift_date = date("d-m-Y",strtotime($p_date));

        $total_shift_upi = Massage::where('date',$p_date)->where('added_by',Auth::id())->where('deleted',0)->where('pay_type',2)->sum("paid_amount");

        $total_shift_cash = Massage::where('date',$p_date)->where('added_by',Auth::id())->where('deleted',0)->where('pay_type',1)->sum("paid_amount");

        $last_hour_upi_total = Massage::where('date',$p_date)->where('added_by',Auth::id())->where('deleted',0)->where('pay_type',2)->where('created_at', '>=', \DB::raw('DATE_SUB(NOW(), INTERVAL 1 HOUR)'))->sum("paid_amount"); 

        $last_hour_cash_total = Massage::where('date',$p_date)->where('added_by',Auth::id())->where('deleted',0)->where('pay_type',1)->where('created_at', '>=', \DB::raw('DATE_SUB(NOW(), INTERVAL 1 HOUR)'))->sum("paid_amount");

        $total_collection = $total_shift_upi + $total_shift_cash;
        $last_hour_total = $last_hour_upi_total + $last_hour_cash_total;
        $data['total_shift_upi'] = $total_shift_upi;
        $data['total_shift_cash'] = $total_shift_cash;
        $data['total_collection'] = $total_collection;
        $data['last_hour_upi_total'] = $last_hour_upi_total;
        $data['last_hour_cash_total'] = $last_hour_cash_total;
        $data['last_hour_total'] = $last_hour_total;
        $data['check_shift'] = $check_shift;
        $data['shift_date'] = $shift_date;


        return $data;
    } 

    public static function totalShiftDataOld(){
        $check_shift = Entry::checkShift();

        $total_shift_cash = 0;
        $total_shift_upi = 0;       

        $last_hour_cash_total = 0;
        $last_hour_upi_total = 0;

        $from_time = date('H:00:00');
        $to_time = date('H:59:59');

        $p_date = Entry::getPDate();
        $shift_date = date("d-m-Y",strtotime($p_date));

        $total_shift_upi = Massage::where('date',$p_date)->where('shift', $check_shift)->where('deleted',0)->where('pay_type',2)->sum("paid_amount");

        $total_shift_cash = Massage::where('date',$p_date)->where('shift', $check_shift)->where('deleted',0)->where('pay_type',1)->sum("paid_amount");

        $last_hour_upi_total = Massage::where('date',$p_date)->where('shift', $check_shift)->where('deleted',0)->where('pay_type',2)->whereBetween('in_time', [$from_time, $to_time])->sum("paid_amount"); 

        $last_hour_cash_total = Massage::where('date',$p_date)->where('shift', $check_shift)->where('deleted',0)->where('pay_type',1)->whereBetween('in_time', [$from_time, $to_time])->sum("paid_amount");

        $total_collection = $total_shift_upi + $total_shift_cash;
        $last_hour_total = $last_hour_upi_total + $last_hour_cash_total;
        $data['total_shift_upi'] = $total_shift_upi;
        $data['total_shift_cash'] = $total_shift_cash;
        $data['total_collection'] = $total_collection;
        $data['last_hour_upi_total'] = $last_hour_upi_total;
        $data['last_hour_cash_total'] = $last_hour_cash_total;
        $data['last_hour_total'] = $last_hour_total;
        $data['check_shift'] = $check_shift;
        $data['shift_date'] = $shift_date;


        return $data;
    } 




}