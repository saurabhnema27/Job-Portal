<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    //fillable
    protected $fillable = [
        'email','first_name','last_name','mobile','organization','mail_is_about','message','is_deleted'
    ];

    public function add_contact_us($request)
    {
        $contactus = new ContactUs;
        $contactus->email = $request->email;
        $contactus->first_name = $request->first_name;
        $contactus->last_name = $request->last_name;
        $contactus->mobile = $request->mobile;
        $contactus->organization = $request->organization;
        $contactus->mail_is_about = $request->mail_is_about;
        $contactus->message = $request->message;

        $contactus->save();
        return $contactus;
    }

    public function serach_contact_us($request)
    {
        $limit = 10;
        $offset = 0;
        if(isset($request->limit) && !empty($request->limit))
            $limit = $request->limit;

        if(isset($request->offset) && !empty($request->offset))
            $offset = $request->offset;

        $query = ContactUs::query();
        if(isset($request->search_text) && !empty($request->search_text))
        {
            $query->where(function ($q) use ($request) {
                $q->Where('first_name','like','%'.$request->search_text.'%')  
                    ->orWhere('last_name','like','%'.$request->search_text.'%')
                    ->orWhere('mobile','like','%'.$request->search_text.'%')
                    ->orWhere('email','like','%'.$request->search_text.'%');
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

        $total_query = $query->where('is_deleted',0);
        $total_result  = $total_query->get();
        $total = count($total_result);

        if(isset($request->short_by) && !empty($request->short_by))
        {
            if($request->short_by == 'first_name-asc')
                $query->orderBy('first_name','ASC');
            elseif($request->short_by == 'first_name-desc')
                $query->orderBy('first_name','DESC');
            elseif($request->short_by == 'email-asc')
                $query->orderBy('email','ASC');
            elseif($request->short_by == 'email-desc')
                $query->orderBy('email','DESC');
            elseif($request->short_by == 'last_name-asc')
                $query->orderBy('last_name','ASC');
            elseif($request->short_by == 'last_name-desc')
                $query->orderBy('last_name','DESC');
        }
        $query->limit($limit)->offset($offset)->orderBy('id','desc');
        $data = $query->get();
        
        return  $data;
    }
}
