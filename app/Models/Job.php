<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    //protected fillable
    protected $fillable = [
        'name','phone','user_id','company_name','company_location','address1','address2','city','organization_type','company_size',
        'state','zipcode','company_link','job_title','type_of_role','job_location','contract_type','application_receive_type',
        'submit_resume','min_salary','max_salary','job_description','job_status','job_type','find_candidate','can_job_publish'
    ];

    protected $casts = [
        'find_candidate' => 'array',
      ];

    public function users()
    {
        return $this->belongsTo('App\Models\User','employeer_id');
    }

    public function applied_jobs()
    {
        return $this->hasMany('App\Models\UserJob','job_id')->where('job_status','!=','REJECTED');
    }

    public function saved_jobs()
    {
        return $this->hasMany('App\Models\UserSavedJob','job_id');
    }

    public function rejected_candidate()
    {
        return $this->hasMany('App\Models\UserJob','job_id')->where('job_status','REJECTED');
    }

    public function saved_notes()
    {
        return $this->hasMany('App\Models\SavedJobNote');
    }

    public function create_update_job($request, $user)
    {
        $findingjob = Job::firstorNew(['id' => $request->id]);
        $findingjob->name = $request->name ?: $user->first_name.' '.$user->last_name;
        $findingjob->phone = $request->phone ?: 1234567890;
        $findingjob->role = $request->role ?: "NULL";
        $findingjob->employeer_location = $request->employeer_location ?: "NULL";
        $findingjob->company_name = $request->company_name;
        $findingjob->company_location = $request->company_location ?: "NULL";
        $findingjob->address1 = $request->address1;
        $findingjob->address2 = $request->address2;
        $findingjob->city = $request->city;
        $findingjob->organization_type = $request->organization_type;
        $findingjob->zipcode = $request->zipcode;
        $findingjob->company_size = $request->company_size ?: "NULL";
        $findingjob->state = $request->state;
        $findingjob->company_link = $request->company_link ?: "NULL";
        $findingjob->job_title = $request->job_title ?: "NULL";
        $findingjob->type_of_role = $request->type_of_role ?: "NULL";
        $findingjob->job_location = $request->job_location ?: "NULL";
        $findingjob->contract_type = $request->contract_type ?: "NULL";
        $findingjob->application_receive_type = $request->application_receive_type ?: "NULL";
        $findingjob->submit_resume = $request->submit_resume ?: 1;
        $findingjob->min_salary = $request->min_salary ?: "NULL";
        $findingjob->max_salary = $request->max_salary ?: "NULL";
        $findingjob->job_description = $request->job_description ?: "NULL";
        $findingjob->job_status = "OPEN";
        $findingjob->job_type = $request->job_type;
        $findingjob->Can_job_publish = 1;
        $findingjob->employeer_id = $user->id;
        $findingjob->find_candidate = $request->find_candidate ?: NULL;

        $findingjob->save();

        return $findingjob;
    }

    public function serach_job($request,$user)
    {
        $limit = 10;
        $offset = 0;
        if(isset($request->limit) && !empty($request->limit))
            $limit = $request->limit;

        if(isset($request->offset) && !empty($request->offset))
            $offset = $request->offset;

        $query = Job::query();
        
        // dd($query->get());        
        if(isset($request->search_text) && !empty($request->search_text))
        {
            $query->where(function ($q) use ($request) {
                $q->where(\DB::raw('CONCAT(name," ",role)'), 'like', '%' . $request->search_text . '%')
                    ->orWhere('job_title','like','%'.$request->search_text.'%')  
                    ->orWhere('company_location','like','%'.$request->search_text.'%')
                    ->orWhere('job_status','like','%'.$request->search_text.'%');
            });
        }

        if(isset($request->filter_status) && $request->filter_status != "")
        {
            if($request->filter_status == 'publish_job')
            {
                $query->where('can_job_publish','=',1);
            }
            else
            {
                $query->where('can_job_publish','=',0);
            }
        }
        
        $total_query = $query->where('employeer_id',$user->id)->withCount('applied_jobs','saved_jobs','rejected_candidate');

        if(isset($request->short_by) && !empty($request->short_by))
        {
            $query->where('job_status','=',$request->short_by);
        }
        $query->limit($limit)->offset($offset);
        $data = $query;
        
        return  $data;
    }

    public function serach_job_login_user($request)
    {
        $limit = 10;
        $offset = 0;
        if(isset($request->limit) && !empty($request->limit))
            $limit = $request->limit;

        if(isset($request->offset) && !empty($request->offset))
            $offset = $request->offset;

        $query = Job::query();
        if(isset($request->search_text) && !empty($request->search_text))
        {
            $query->where(function ($q) use ($request) {
                $q->where(\DB::raw('CONCAT(name," ",role)'), 'like', '%' . $request->search_text . '%')
                    ->orWhere('job_title','like','%'.$request->search_text.'%')  
                    ->orWhere('company_name','like','%'.$request->search_text.'%');
            });
        }

        if(isset($request->filter_status) && $request->filter_status != "")
        {
            $query->where(function ($q) use ($request) {
                $q->Where('city','like','%'.$request->filter_status.'%')
                    ->orWhere('state','like','%'.$request->filter_status.'%')
                    ->orWhere('zipcode','like','%'.$request->filter_status.'%');
            });
        }

        if(isset($request->salary) && $request->salary != "")
        {
            $query->where(function ($q) use ($request) {
                $q->Where('min_salary','>=',$request->filter_status)
                    ->orWhere('max_salary','>=',$request->filter_status);
            });
        }

        if(isset($request->distance) && $request->distance != "")
        {
            
        }

        if(isset($request->job_type) && $request->job_type != "")
        {
            $query->where(function ($q) use ($request) {
                $q->Where('type_of_role','like','%'.$request->job_type.'%');
            });
        }

        if(isset($request->job_location) && $request->job_location != "")
        {
            $query->where(function ($q) use ($request) {
                $q->Where('job_location','like','%'.$request->job_location.'%');
            });
        }

        if(isset($request->contract_type) && $request->contract_type != "")
        {
            $query->where(function ($q) use ($request) {
                $q->Where('contract_type','like','%'.$request->contract_type.'%');
            });
        }
       
        $query->where([
            ['job_status','!=','CLOSED'],
            ['job_status','!=','PAUSED'],
            ['can_job_publish','=', '1']
        ]);
        $total_query = $query;
        $total_result  = $total_query->get();
        $total = count($total_result);

        if(isset($request->short_by) && !empty($request->short_by))
        {
            if($request->short_by == 'latest')
                $query->orderBy('created_at','DESC');
            elseif($request->short_by == '')
                $query->orderBy('created_at','ASC');
        }
        $query->limit($limit)->offset($offset)->orderBy('id','desc');
        $data = $query;
        
        return  $data;
    }

    public function admin_serach_job($request)
    {
        $limit = 10;
        $offset = 0;
        if(isset($request->limit) && !empty($request->limit))
            $limit = $request->limit;

        if(isset($request->offset) && !empty($request->offset))
            $offset = $request->offset;

        $query = Job::query();
        if(isset($request->search_text) && !empty($request->search_text))
        {
            $query->where(function ($q) use ($request) {
                $q->where(\DB::raw('CONCAT(name," ",role)'), 'like', '%' . $request->search_text . '%')
                    ->orWhere('job_title','like','%'.$request->search_text.'%')  
                    ->orWhere('company_location','like','%'.$request->search_text.'%')
                    ->orWhere('job_location','like','%'.$request->search_text.'%');
            });
        }

        if(isset($request->filter_status) && $request->filter_status != "")
        {
           if($request->job_status == "OPEN")
            {
                $query->where('job_status','=','OPEN');
            }
            else
            {
              $query->where('status','=',$request->filter_status);
            }
        }

        // $query->where([
        //     ['job_status','!=','CLOSED'],
        //     ['job_status','!=','PAUSED'],
        //     ['can_job_publish','=', '1'],
        // ]);
        
        $total_query = $query;
        $total_result  = $total_query->get();
        $total = count($total_result);

        if(isset($request->short_by) && !empty($request->short_by))
        {
            if($request->short_by == 'job_title-asc')
                $query->orderBy('job_title','ASC');
            elseif($request->short_by == 'job_title-desc')
                $query->orderBy('job_title','DESC');
            elseif($request->short_by == 'company_location-asc')
                $query->orderBy('company_location','ASC');
            elseif($request->short_by == 'company_location-desc')
                $query->orderBy('company_location','DESC');
            elseif($request->short_by == 'job_status-asc')
                $query->orderBy('job_status','ASC');
            elseif($request->short_by == 'job_status-desc')
                $query->orderBy('job_status','DESC');
        }
        $query->limit($limit)->offset($offset)->orderBy('id','desc');
        $data = $query;
        
        return  $data;
    }
}
