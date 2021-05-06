<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Job;
use App\Models\UserJob;
use App\Models\UserSavedJob;
use App\Models\SavedJobNote;
use App\Models\Interview;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Validator;

class EmployeersController extends Controller
{
    //Create a Job Function

    /**
     * This is the function used to create a job
     * @param Request
     * @return Object 
     */
    public function createJob(Request $request)
    {
        $user = Auth::user();
        $createjobmodel = new Job;
        try
        {
            $rules =  [
                'name' => 'string',
                'phone' => 'numeric',
                'role' => 'string',
                'employeer_location' => 'string',
                'company_name' => 'required|string',
                'company_location' => 'string',
                'address1' => 'required|string',
                'address2' => 'required|string',
                'city' => 'required|string',
                'state' => 'required|string',
                'zipcode' => 'required|numeric',
                'organization_type' => 'required|string',
                'company_link' => 'string',
                'job_title' => 'string',
                'type_of_role' => 'string',
                'job_location' => 'string',
                'contract_type' => 'string',
                'application_receive_type' => 'string',
                'submit_resume' => 'numeric',
                'job_description' => 'string',
            ];
    
            $validator = Validator::make($request->all(),$rules);
            if ($validator->fails()) 
            {
              return $this->sendError($validator->messages()->first());
            }

            $saving_job = $createjobmodel->create_update_job($request,$user);
            if($saving_job)
            {
                // notification 
                $notification_title = "Job Created";
                $notification_content = trans('message.job_created');
                $nf = new Notification;
                $nf->create_notification($user,$notification_title,$notification_content);
                return $this->sendSuccess(trans('message.job_created'),$saving_job);
            }
            return $this->sendError(trans('message.job_creation_error'));


        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

     /**
     * This is the function used to update a job
     * @param Request
     * @return Object 
     */
    public function updateJob(Request $request)
    {
        $user = Auth::user();
        $createjobmodel = new Job;
        try
        {
            $rules =  [
                'name' => 'string',
                'phone' => 'numeric',
                'role' => 'string',
                'employeer_location' => 'string',
                'company_name' => 'required|string',
                'company_location' => 'string',
                'address1' => 'required|string',
                'address2' => 'required|string',
                'city' => 'required|string',
                'state' => 'required|string',
                'zipcode' => 'required|numeric',
                'organization_type' => 'required|string',
                'company_link' => 'string',
                'job_title' => 'string',
                'type_of_role' => 'string',
                'job_location' => 'string',
                'contract_type' => 'string',
                'application_receive_type' => 'string',
                'submit_resume' => 'numeric',
                'job_description' => 'string',
            ];
    
            $validator = Validator::make($request->all(),$rules);
            if ($validator->fails()) 
            {
              return $this->sendError($validator->messages()->first());
            }

            $saving_job = $createjobmodel->create_update_job($request,$user);
            if($saving_job)
            {
                // notification 
                $notification_title = "Job Updated";
                $notification_content = trans('message.job_updated');
                $nf = new Notification;
                $nf->create_notification($user,$notification_title,$notification_content);
                return $this->sendSuccess(trans('message.job_updated'),$saving_job);
            }
            return $this->sendError(trans('message.job_updating_error'));


        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

     /**
     * This is the function used to Reterive all job
     * @param Array["searchable"]
     * @return Object 
     */
    public function getAllJob(Request $request)
    {
        try
        {
            $user = Auth::user();
            $jobModel = new Job;
            $jobs = $jobModel->serach_job($request,$user);
            $jobs = $jobs->paginate(10);
            if($jobs)
            {
                return $this->sendSuccess(trans('message.jobs_reterived'),$jobs);
            }
            return $this->sendError(trans('message.jobs_not_found'),404);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

    /**
     * This is the get a specific job
     * @param id
     * @return Object 
     */
    public function specificJob($id)
    {
        try
        {
            $getJob = Job::find($id);
            if($getJob)
            {
                $all_users_applied_for_jobs =  UserJob::where('job_id',$getJob->id)
                ->select('user_jobs.*','resumes.resume_name','users.first_name','users.image','users.last_name')
                ->leftjoin('users','users.id','=','user_jobs.user_id')
                ->leftjoin( 'resumes','resumes.user_id','=','users.id')
                ->get();
                
                foreach($all_users_applied_for_jobs as $key => $value)
                {
                    $all_users_applied_for_jobs[$key]->resume_url = env('APP_URL').'/storage/user_resume/'.$all_users_applied_for_jobs[$key]->resume_name;
                    $all_users_applied_for_jobs[$key]->image = env('APP_URL').'/storage/user_images/'.$all_users_applied_for_jobs[$key]->image;
                }
                $data = [
                    'job_detail' => $getJob,
                    'users_applied_job' => $all_users_applied_for_jobs
                ];
                return $this->sendSuccess(trans('message.jobs_reterived'),$data);
            }
            return $this->sendError(trans('message.jobs_not_found'),404);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

    /**
     * This is the function used to change a job status
     * @param Request
     * @return Object 
     */
    public function changeJobStatus(Request $request)
    {
        try
        {
            $user = Auth::user();

            $getJob = Job::where([
                'id' => $request->job_id,
                'employeer_id' => $user->id
            ])->first();

            if($getJob)
            {
                $status_changes = $getJob->job_status = $request->job_status;
                return $this->sendSuccess(trans('message.jobs_status_changes'),$getJob);
            }
            return $this->sendError(trans('message.jobs_not_found'),404);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

    /**
     * This is the function used to delete a job
     * @param id
     * @return Object ?: NULL
     */
    public function deleteJob($id)
    {
        try
        {
            $user = Auth::user();

            $getJob = Job::where([
                'id' => $id,
                'employeer_id' => $user->id
            ])->first();

            if($getJob)
            {
                $getJob->delete();
                return $this->sendSuccess(trans('message.job_deleted'));
            }
            return $this->sendError(trans('message.jobs_not_found'),404);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

    /**
     * This is the function used to search all job
     * @param Array["searchable"]
     * @return Object 
     */
    public function searchJob(Request $request)
    {
        try
        {
            $user = Auth::user();
            $jobmodel = new Job;
            $searched_data = $jobmodel->serach_job($request,$user);
            if(count($searched_data) > 0) 
            {
                return $this->sendSuccess(trans('message.jobs_reterived'),$searched_data);
            }
            return $this->sendError(trans('message.jobs_not_found'),404);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }
    }

    /**
     * This is the function used to Reterive all jobskeer who applied for the job created buy this login user
     * @param Array["searchable"]
     * @return Object 
     */

    public function ListOfJobseeker()
    {
        $user = Auth::user();
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

        $get_users = UserJob::whereIn('job_id',$job_id)
        ->with(['users.resume','jobs.saved_notes' => function($q) use($user){
            $q->where('user_id',$user->id);
        }])     
        ->get();

        if(count($get_users) > 0)
        {
            return $this->sendSuccess(trans("message.user_list"),$get_users);
        }
        return $this->sendError(trans("message.no_one_aaplied"),404);
    }

    /**
     * This is the function used to Reterive the resume of a jobseekr
     * @param id
     * @return Object 
     */
    public function viewResume($id)
    {
        try
        {
            $find = Resume::where([
                'user_id' => $id
            ])->first();

            if($find)
            {
                $resume_url = env('APP_URL').'/storage/user_resume/'.$find->resume_name;
                return $this->sendSuccess(trans('message.resume_link_fetched'),$resume_url);
            }
            return $this->sendError(trans('message.resume_not_found'),404);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

    /**
     * This is the function used to change the jobseeker status
     * @param Request
     * @return Object 
     */
    public function changeJobSeekerStatus(Request $request)
    {   
        try
        {
            $find = UserJob::find($request->id);
            if($find)
            {
                $find->update([
                    'job_status' => strtoupper($request->status)
                ]); 

                // notification 
                $notification_title = "Job Status";
                $notification_content = "Your Job Status is changed";
                $user_value = $find;
                $nf = new Notification;
                $nf->create_notification($user_value,$notification_title,$notification_content);
                return $this->sendSuccess(trans("message.jobskeer_job_update_status"),$find);
            }
            return $this->sendError(trans('message.jobs_not_found'),404);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

    /**
     * This is the function used to Setup the interview
     * @param Request
     * @return Object 
     */
    public function setUpInterview(Request $request)
    {
        try
        {
            $rules =  [
                'time_zone' => 'required',
                'time_zone_format' => 'required',
                'date' => 'required',
                'start_time' => 'required',
                'end_time' => 'required',
                'interview_type' => 'required|string',
                'interview_address' => 'string',
                'message' => 'string'
            ];
    
            $validator = Validator::make($request->all(),$rules);
            if ($validator->fails()) 
            {
              return $this->sendError($validator->messages()->first());
            }
            
            $job_present = Job::find($request->job_id);
            if(!$job_present)
            {
                return $this->sendError(trans('message.oops_job_not_found'));
            }

            $user = Auth::user();
            $interviewModel = new Interview;
            $saved_interview = $interviewModel->create_update($request,$user);
            if($saved_interview)
            {
                /**
                 * Mail sending to that Jobseeker if any
                 */

                // notification 
                $notification_title = "Interview is Sheduled";
                $notification_content = trans("message.interview_created");
                $nf = new Notification;
                $id = (int)$request->user_id;
                $user = User::find($id);
                $nf->create_notification($user,$notification_title,$notification_content);
                return $this->sendSuccess(trans("message.interview_created"),$saved_interview);
            }
            return $this->sendError(trans("message.job_creation_error"));
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

    /**
     * This is the function used to update the Interview
     * @param Request
     * @return Object 
     */
    public function updateInterview(Request $request)
    {
        try
        {
            $rules =  [
                'time_zone' => 'required',
                'time_zone_format' => 'required',
                'date' => 'required',
                'start_time' => 'required',
                'end_time' => 'required',
                'interview_type' => 'required|string',
                'interview_address' => 'string',
                'message' => 'string'
            ];
    
            $validator = Validator::make($request->all(),$rules);
            if ($validator->fails()) 
            {
              return $this->sendError($validator->messages()->first());
            }

            $user = Auth::user();
            $interviewModel = new Interview;
            $saved_interview = $interviewModel->create_update($request,$user);
            if($saved_interview)
            {
                /**
                 * Update mail sending if any to Jobseeker
                 */

                $notification_title = "Interview is Updated";
                $notification_content = trans("message.interview_updated");
                $nf = new Notification;
                $id = (int)$request->user_id;
                $user = User::find($id);
                $nf->create_notification($user,$notification_title,$notification_content);

                return $this->sendSuccess(trans("message.interview_updated"),$saved_interview);
            }
            return $this->sendError(trans("message.job_creation_error"));
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

    /**
     * This is the function used to delete the Interview
     * @param id
     * @return Object 
     */
    public function deleteInterview($id)
    {
        $find = Interview::find($id);
        if($find)
        {
            $find->delete();
            /**
             * delete mail sending to Jobseeker if any
             */

            $notification_title = "Interview is Updated";
            $notification_content = trans("message.interview_deleted");
            $nf = new Notification;
            $user = $find->jobseeker_id;
            $nf->create_notification($user,$notification_title,$notification_content);

            return $this->sendSuccess(trans("message.interview_deleted"));
        }
        return $this->sendError(trans("message.job_creation_error"),404);
    }

    /**
     * This is the function used to Reterive teh single list of interview
     * @param id
     * @return Object 
     */
    public function getSingleInterview($id)
    {
        $find = Interview::where('id',$id)->with('jobs','users')->get();
        if($find)
        {
            $find = $find;
            return $this->sendSuccess(trans("message.interview_list"),$find);
        }
        return $this->sendError(trans("message.job_creation_error"),404);
    }

    /**
     * This is the function used to Reterive all Interview
     * @param Array["searchable"]
     * @return Object 
     */
    public function AllInterviewDetail()
    {
        $user = Auth::user();
        $interview = Interview::where('user_id',$user->id)->get();
        if(count($interview) > 0)
        {
            return $this->sendSuccess(trans("message.interview_list"),$interview);
        }
        return $this->sendError(trans("message.interview_not_found"),404);
    }

    /**
     * This is the function used to Add the note on jobseeker
     * @param Request
     * @return Object 
     */
    public function addNoteToJob(Request $request)
    {
        try
        {
            $user = Auth::user();

            $rules =  [
                'job_id' => 'required|integer',
                'note' => 'required|string'
            ];
    
            $validator = Validator::make($request->all(),$rules);
            if ($validator->fails()) 
            {   
              return $this->sendError($validator->messages()->first());
            }
            
            $job_present = Job::find($request->job_id);
            if(!$job_present)
            {
                return $this->sendError(trans('message.oops_job_not_found'));
            }

            $noteModel = new SavedJobNote;
            $saved_note = $noteModel->add_note($request,$user);
            if($saved_note)
            {  
                return $this->sendSuccess(trans('message.note_saved'),$saved_note);  
            }  
            return $this->sendError(trans("message.job_creation_error"));
        }
        catch( \Exception $e)
        {  
            $msg = $e->getMessage();  
            return $this->sendError($msg);
        }
    }

    /**
     * This is the function used to delete the note on jobseeker
     * @param id
     * @return Object 
     */
    public function deleteNote($id)
    {
        $get_note = SavedJobNote::find($id);
        if($get_note)
        {
            $get_note->delete();
            return $this->sendSuccess(trans("message.note_deleted"));
        }
        return $this->sendError(trans("message.note_not_found"),404);
    }


    /**
     * This is the function used to generate the Report
     * @param NA
     * @return Object 
     */
    public function ReportGeneration()
    {
        $user = Auth::user();

        $userModel = new User;
        $get_reports = $userModel->employeer_reports_generations($user);
        
        /**
         * if pdf export will be there then it'll come here
         */
        return $this->sendSuccess(trans('message.jobseeker_report'),$get_reports);
    }

    /**
     * This is the function used to get all notifications of employeer
     * @param NA
     * @return Object 
     */
    public function Notifications()
    {
        $user = Auth::user();
        $nf = Notification::where('user_id',$user->id)->get();

        $rnf = Notification::where([
            'user_id' => $user->id,
            'is_viewed' => 1
        ])->get()->count();

        $urnf = Notification::where([
            'user_id' => $user->id,
            'is_viewed' => 0
        ])->get()->count();

        $success = [
            'All_Notifications' => $nf,
            'Read_Notifications' => $rnf,
            'Unread_Notifications' => $urnf,
            'Total_Notifications' => count($nf)
        ];

        return $this->sendSuccess(trans('message.notification'),$success);
    }

    /**
     * This is the function used to read a specific notification
     * @param ID
     * @return Object 
     */
    public function ReadNotification($id)
    {
        $get_nf = Notification::find($id);

        if($get_nf)
        {
            $get_nf->update([
                'is_viewed' => 1
            ]);
            return $this->sendSuccess(trans('message.notification_updated'),$success); 
        }
        return $this->sendError(trans('message.notification_not_found'),404);
    }
}
