<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use App\Models\UserJob;
use App\Models\UserSavedJob;
use App\Models\Interview;
use App\Models\Job;
use App\Models\ArchiveJob;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'email', 'password','last_name','user_type','mobile','user_status','disability_id','disability_comment','image',
        'email_notification','block_unblock'
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return  env('APP_URL').'/storage/user_images/'.$this->image;
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','password'
    ];

    public function resume()
    {
        return $this->hasMany('App\Models\Resume','user_id');
    }

    public function jobs()
    {
        return $this->hasMany('App\Models\Job','employeer_id');
    }

    public function applied_jobs()
    {
        return $this->hasMany('App\Models\UserJob','user_id');
    }

    public function saved_jobs()
    {
        return $this->hasMany('App\Models\UserSavedJob','user_id');
    }

    public function interview_list()
    {
        return $this->hasMany('App\Models\Interview','jobseeker_id');
    }

    public function interview_owner()
    {
        return $this->hasMany('App\Models\Interview','user_id');
    }

    public function notifications()
    {
        return $this->hasMany('App\Models\Notification','user_id');
    }

    public function add_update_user($request)
    {
        $findingUser = User::firstorNew(['id' => $request->id]);
        // dd($findingUser);
        $findingUser->first_name = $request->first_name;
        $findingUser->last_name = $request->last_name;
        $findingUser->email = $request->email;
        $findingUser->image = $findingUser->image ?: 'default.jpg';
        if(isset($request->password) && !empty($request->password))
        {
            $findingUser->password = bcrypt($request->password);
        }

        if($request->has('image') || $request->has('mobile'))
        {
            if(isset($request->image) && $request->image != "")
            {
                $image = $this->add_profile_image($request);
                $findingUser->image = $image;
            }
            if($request->has('mobile'))
            {
                $findingUser->mobile = $request->mobile;
            }
        }
        if($request->user_type == 1)
        {
            $findingUser->user_type = 1;
            $findingUser->user_status = 1;
            $findingUser->disibility = $request->disibility ?: NULL;
            $findingUser->disability_comment = $request->disability_comment ?: NULL;
            $findingUser->email_notification = 1;
        }
        elseif($request->user_type == 2)
        {
            $findingUser->user_type = 2;
            $findingUser->user_status = 1;
        }

        else
        {
            $findingUser->user_type = 3;
            $findingUser->user_status = 1;
        }
        $findingUser->block_unblock = 0;
        $findingUser->save();
        return $findingUser;
    }

    protected function add_profile_image($request)
    {
        $destinationPath = 'storage/user_images/';
        $file = $request->file('image');
        $filenameWithExt = $file->getClientOriginalName();
        // Get just filename
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);            
        // Get just extension
        $extension = $file->getClientOriginalExtension();
        //Filename to store
        $fileNameToStore = $filename.'_'.time().'.'.$extension;   

        $file->move($destinationPath,$fileNameToStore);

        return $fileNameToStore;
        
    }

    public function serach_user($request)
    {
        $limit = 10;
        $offset = 0;
        if(isset($request->limit) && !empty($request->limit))
            $limit = $request->limit;

        if(isset($request->offset) && !empty($request->offset))
            $offset = $request->offset;

        $query = User::query();
        if(isset($request->search_text) && !empty($request->search_text))
        {
            $query->where(function ($q) use ($request) {
                $q->Where('first_name','like','%'.$request->search_text.'%')  
                    ->orWhere('last_name','like','%'.$request->search_text.'%')
                    ->orWhere('mobile','like','%'.$request->search_text.'%')
                    ->orWhere('email','like','%'.$request->search_text.'%');
            });
        }

        if(isset($request->user_status) && $request->user_status != "")
        {
           if($request->user_status == 1)
            {
                $query->where('user_status','=',1);
            }
            else
            {
              $query->where('user_status','=',$request->user_status);
            }
        }

        if(isset($request->block_unblock) && $request->block_unblock != "")
        {
           if($request->block_unblock == 0)
            {
                $query->where('block_unblock','=',0);
            }
            else
            {
              $query->where('block_unblock','=',$request->block_unblock);
            }
        }

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

    public function jobseeker_report_genration($user)
    {
        $month_report_applied_jobs = UserJob::where([
            'user_id' => $user->id,
            ['created_at', '>=', Carbon::now()->subDays(30)->toDateTimeString()]
        ])->get()->count();

        $all_applied_jobs = UserJob::where([
            'user_id' => $user->id,
        ])->get()->count();

        $saved_jobs_in_month = UserSavedJob::where([
            'user_id' => $user->id,
            ['created_at', '>=', Carbon::now()->subDays(30)->toDateTimeString()]
        ])->get()->count();

        $all_saved_jobs = UserSavedJob::where([
            'user_id' => $user->id,
        ])->get()->count();

        $interview_in_month = Interview::where([
            'jobseeker_id' => $user->id,
            ['created_at', '>=', Carbon::now()->subDays(30)->toDateTimeString()]
        ])->get()->count();

        $interview = Interview::where([
            'jobseeker_id' => $user->id,
        ])->get()->count();

        $archieved_jobs_in_month = ArchiveJob::where([
            'user_id' => $user->id,
            ['created_at', '>=', Carbon::now()->subDays(30)->toDateTimeString()]
        ])->get()->count();

        $archieved_jobs = ArchiveJob::where([
            'user_id' => $user->id,
        ])->get()->count();

        $success = [
            'applied_job_this_month' => $month_report_applied_jobs,
            'all_applied_jobs' => $all_applied_jobs,
            'saved_jobs_in_month' => $saved_jobs_in_month,
            'all_saved_jobs' => $all_saved_jobs,
            'interview_in_month' => $interview_in_month,
            'interview' => $interview,
            'archieved_jobs_in_month' => $archieved_jobs_in_month,
            'archieved_jobs' => $archieved_jobs
        ];

        return $success;
    }

    public function employeer_reports_generations($user)
    {
        $created_job_this_month = Job::where([
            'employeer_id' => $user->id,
            ['created_at', '>=', Carbon::now()->subDays(30)->toDateTimeString()]
        ])->get()->count();

        $all_created_job = Job::where([
            'employeer_id' => $user->id,
        ])->get()->count();

        $jobs = Job::where('employeer_id',$user->id)->get();
        
        $i=0;
        $job_id = array();
        
        foreach($jobs as $a)
        {
            if(isset($jobs[$i]) === true)
            {
                $job_id[$i] = $a->id;
            }
            
            $i++;
        }
        if(empty($block))
        {
            $block = [0];
        }
        
        $pending_jobs_this_month = UserJob::whereIn('job_id',$job_id)
        ->where([
            'job_status' => 'PENDING',
            ['created_at', '>=', Carbon::now()->subDays(30)->toDateTimeString()]
        ])
        ->get()->count();

        $all_pending_jobs = UserJob::whereIn('job_id',$job_id)
        ->where([
            'job_status' => 'PENDING'
        ])
        ->get()->count();

        $interview_in_month = Interview::where([
            'user_id' => $user->id,
            ['created_at', '>=', Carbon::now()->subDays(30)->toDateTimeString()]
        ])->get()->count();

        $interview = Interview::where([
            'user_id' => $user->id,
        ])->get()->count();

        $success = [
            'created_job_this_month' => $created_job_this_month,
            'all_created_job' => $all_created_job,
            'pending_jobs_this_month' => $pending_jobs_this_month,
            'all_pending_jobs' => $all_pending_jobs,
            'interview_in_month' =>$interview_in_month,
            'all_interview' => $interview
        ];    

        return $success;
    }

}