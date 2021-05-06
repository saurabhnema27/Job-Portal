<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'plan_title','plan_price','daily_budget','montly_budget','is_active','updated_at'
    ];

    public function add_update_plan($request)
    {
        $get_plan = SubscriptionPlan::firstorNew(['id',$request->id]);
        $get_plan->plan_title = $request->plan_title;
        $get_plan->plan_price = $request->plan_price;
        $get_plan->daily_budget = $request->daily_budget;
        $get_plan->is_active = $request->is_active;

        $get_plan->save();
        return $get_plan;
    }

    public function serach_plans($request,$user)
    {
        $limit = 10;
        $offset = 0;
        if(isset($request->limit) && !empty($request->limit))
            $limit = $request->limit;

        if(isset($request->offset) && !empty($request->offset))
            $offset = $request->offset;

        $query = SubscriptionPlan::query();
        
        // dd($query->get());        
        if(isset($request->search_text) && !empty($request->search_text))
        {
            $query->where(function ($q) use ($request) {
                $q->where(\DB::raw('CONCAT(name," ",role)'), 'like', '%' . $request->search_text . '%')
                    ->orWhere('plan_title','like','%'.$request->search_text.'%')  
                    ->orWhere('plan_price','like','%'.$request->search_text.'%')
                    ->orWhere('montly_budget','like','%'.$request->search_text.'%')
                    ->orWhere('daily_budget','like','%'.$request->search_text.'%');
            });
        }

        if(isset($request->filter_status) && $request->filter_status != "")
        {
            $query->where('is_active','=',$request->filter_status);
        }

        $query->limit($limit)->offset($offset);
        $data = $query;
        
        return  $data;
    }
}
