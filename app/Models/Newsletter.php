<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    //fillable
    protected $fillable = ['email'];

    public function addNewsLetter($request)
    {
        $newsletter = new Newsletter;
        $newsletter->email = $request->email;

        $newsletter->save();
        return $newsletter;
    }

    public function serach_newsletter($request)
    {
        $limit = 10;
        $offset = 0;
        if(isset($request->limit) && !empty($request->limit))
            $limit = $request->limit;

        if(isset($request->offset) && !empty($request->offset))
            $offset = $request->offset;

        $query = Newsletter::query();
        if(isset($request->search_text) && !empty($request->search_text))
        {
            $query->where(function ($q) use ($request) {
                $q->Where('email','like','%'.$request->search_text.'%');
            });
        }

        if(isset($request->filter_status) && $request->filter_status != "")
        {
        //    if($request->job_status == "OPEN")
        //     {
        //         $query->where('job_status','=','OPEN');
        //     }
        //     else
        //     {
        //       $query->where('status','=',$request->filter_status);
        //     }
        }

        $total_query = $query;
        $total_result  = $total_query->get();
        $total = count($total_result);

        if(isset($request->short_by) && !empty($request->short_by))
        {
            if($request->short_by == 'email-asc')
                $query->orderBy('email','ASC');
            elseif($request->short_by == 'email-desc')
                $query->orderBy('email','DESC');
        }
        $query->limit($limit)->offset($offset)->orderBy('id','desc');
        $data = $query->get();
        
        return  $data;
    }
}
