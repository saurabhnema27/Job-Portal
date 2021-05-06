<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Resume;
use App\Models\User;
use App\Models\Job;
use App\Models\UserJob;
use App\Models\UserSavedJob;
use App\Models\ArchiveJob;
use App\Models\Interview;
use App\Models\SavedJobNote;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Validator;

class JobSeekerController extends Controller
{
    //upload a Resume

    /**
     * This is the function used to upload the resume of a jobseekr
     * @param Request
     * @return Object 
     */
    public function UploadResume(Request $request): Object
    {
        try
        {
            $user = Auth::user();
            $rules =  [
                'resume' => 'required|file|mimes:doc,docx,pdf|max:204800',
            ];
    
            $validator = Validator::make($request->all(),$rules);
            if ($validator->fails()) 
            {
              return $this->sendError($validator->messages()->first());
            }

            $find = Resume::where([
                'user_id' => $user->id
            ])->first();

            if($find)
            {
                $resume_url = env('APP_URL').'/storage/user_resume/'.$find->resume_name;
                $find->delete();
            }

            $resumemodel = new Resume;
            $uploaded = $resumemodel->add_delete_resume($request,$user);
            if($uploaded)
            {
                // notification 
                $notification_title = "Resume Uploaded";
                $notification_content = trans('message.resume_uploaded');
                $nf = new Notification;
                $nf->create_notification($user,$notification_title,$notification_content);
                return $this->sendSuccess(trans('message.resume_uploaded'),$uploaded);
            }
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }
    }

    /**
     *
     * @param id
     * @return Object 
     */
    public function seeResume($id)
    {
        try
        {
            $user = Auth::user();

            $find = Resume::where([
                'id' => $id,
                'user_id' => $user->id
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
     * This is the function used to delete the resume of a jobseekr
     * @param id
     * @return Object 
     */
    public function deleteResume($id)
    {
        try
        {
            $user = Auth::user();

            $find = Resume::where([
                'id' => $id,
                'user_id' => $user->id
            ])->first();

            if($find)
            {
                $check = \unlink(storage_path('user_resume/'.$find->resume_name));
                $find->delete();

                // notification 
                $notification_title = "Resume Deleted";
                $notification_content = trans('message.resume_deleted');
                $nf = new Notification;
                $nf->create_notification($user,$notification_title,$notification_content);
                return $this->sendSuccess(trans('message.resume_deleted'));
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
     * This is the function used to download the resume of a jobseekr
     * @param id
     * @return Object 
     */
    public function downloadResume($id)
    {
        try
        {
            $user = Auth::user();

            $find = Resume::where([
                'id' => $id,
                'user_id' => $user->id
            ])->first();

            if($find)
            {
                $resume_url = storage_path('user_resume/'.$find->resume_name);
                return response()->download($resume_url);
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
     * This is the function used to Dashboard of jobseekr
     * @param Request
     * @return Object 
     */
    public function Dashboard()
    {
        $user = Auth::user();
        $applied_job = UserJob::where('user_id',$user->id)->with('jobs')->get();
        $success = [
            'searched_appered' => 0,
            'saved_job' => count($user->saved_jobs),
            'interiewing' => count($user->interview_list),
            'applied_jobs' => $applied_job
        ];

        return $this->sendSuccess(trans('message.dashboard'),$success);
    }

    /**
     * This is the function used to search a job
     * @param Request
     * @return Object 
     */
    public function searchJob(Request $request)
    {
        try
        {
            $jobmodel = new Job;
            $searched_data = $jobmodel->serach_job_login_user($request);
            $searched_data = $searched_data->paginate(10);
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
     * This is the function used to Apply for a job
     * @param Request
     * @return Object 
     */
    public function applyJob(Request $request)
    {
        try
        {
            $user = Auth::user();
            $jobmodel = new UserJob;
            $check = UserJob::where([
                'job_id' => $request->job_id,
                'user_id' => $user->id
            ])->first();

            if($check)
            {
                return $this->sendError(trans('message.already_applyed_job'));
            }

            $job_present = Job::find($request->job_id);
            if(!$job_present)
            {
                return $this->sendError(trans('message.oops_job_not_found'));
            }

            $saved_data = $jobmodel->apply_job($request,$user); #apply_job is a function in App\Models\UserJob 
            if($saved_data)
            {
                /**
                 * Mail sending if any 
                 */

                // notification 
                $notification_title = "Job Applied";
                $notification_content = trans('message.applyed_job');
                $nf = new Notification;
                $nf->create_notification($user,$notification_title,$notification_content);

                return $this->sendSuccess(trans('message.applyed_job'),$saved_data);
            }
            return $this->sendError(trans('message.job_creation_error'),404);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }
    }

    /**
     * This is the function used to Reterive the all applied jobs of a jobseekr
     * @param NA
     * @return Object 
     */
    public function getUserAppliedJobs()
    {
        $user = Auth::user();
        $applied = UserJob::where('user_id',$user->id)->with('jobs','get_list_of_interviews')
        ->get();

        if(count($applied) > 0)
        {
            return $this->sendSuccess(trans('message.jobs_reterived'),$applied);
        }
        return $this->sendError(trans('message.you_havent'),404);

    }

    /**
     * This is the function used to Reterive the savedjob of a jobseekr
     * @param NA
     * @return Object 
     */
    public function getUserSavedJobs()
    {
        $user = Auth::user();
        $applied = UserSavedJob::where('user_id',$user->id)->with('jobs.saved_notes')
        ->get();

        if(count($applied) > 0)
        {
            return $this->sendSuccess(trans('message.saved_jobs_reterived'),$applied);
        }
        return $this->sendError(trans('message.saveed_job_not_found'),404);
    }

    /**
     * This is the function used save a job via jobseekr
     * @param Request
     * @return Object 
     */
    public function savedJob(Request $request)
    {
        try
        {
            $user = Auth::user();
            $jobmodel = new UserSavedJob;
            $job_present = Job::find($request->job_id);
            if(!$job_present)
            {
                return $this->sendError(trans('message.oops_job_not_found'));
            }
            $saved_data = $jobmodel->saved_job($request,$user);
            if($saved_data)
            {
                // notification 
                $notification_title = "Saved Job";
                $notification_content = trans('message.job_saved');
                $nf = new Notification;
                $nf->create_notification($user,$notification_title,$notification_content);
                return $this->sendSuccess(trans('message.job_saved'),$saved_data);
            }
            return $this->sendError(trans('message.job_creation_error'),404);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }
    }

    /**
     * This is the function used to unsaved a job via jobseekr
     * @param Request
     * @return Object 
     */
    public function UnsavedJob(Request $request)
    {
        try
        {
            $user = Auth::user();
            $unsaved_data = UserSavedJob::where([
                'id' => $request->id,
                'user_id' => $user->id
            ])->first();

            if($unsaved_data)
            {
                $unsaved_data->delete();
                // notification 
                $notification_title = "Removed Saved Job";
                $notification_content = trans('message.unsaved_job');
                $nf = new Notification;
                $nf->create_notification($user,$notification_title,$notification_content);
                return $this->sendSuccess(trans('message.unsaved_job'),$saved_data);
            }
            return $this->sendError(trans('message.job_creation_error'),404);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }
    }

    /**
     * This is the function used to Reterive a specific job of a jobseekr
     * @param id
     * @return Object 
     */
    public function specificJob($id)
    {
        try
        {
            $getJob = Job::findorFail($id);
            return $this->sendSuccess(trans('message.jobs_reterived'),$getJob);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

    /**
     *
     * @param Request
     * @return Object 
     */
    public function applyFromSavedJob(Request $request)
    {
        try
        {
            $user = Auth::user();
            $get_saved_job_id = UserSavedJob::where([
                'user_id' => $user->id,
                'id' => $request->id
            ])->first();
            
            $check = UserJob::where([
                'job_id' => $request->job_id,
                'user_id' => $user->id
            ])->first();

            if($check)
            {
                return $this->sendSuccess(trans('message.already_applyed_job'),$check);
            }

            if($get_saved_job_id)
            {
                $jobmodel = new UserJob;
                $saved_data = $jobmodel->apply_job($get_saved_job_id,$user);
                if($saved_data)
                {
                    $get_saved_job_id->delete();
                    return $this->sendSuccess(trans('message.applyed_job'),$saved_data);
                }
                return $this->sendError(trans('message.job_creation_error'),404);
            }
            return $this->sendError(trans('message.saveed_job_not_found'),404);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

    /**
     *
     * @param NA
     * @return Object 
     */
    public function getListOfInterview()
    {
        try
        {
            $user = Auth::user();
            $list_of_interviews = Interview::where('jobseeker_id',$user->id)->with('jobs')->get();
            if(count($list_of_interviews) > 0)
            {
                return $this->sendSuccess(trans("message.interview_list"),$list_of_interviews);
            }
            return $this->sendError(trans('message.no_interviews'),404);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

    /**
     *
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

            $noteModel = new SavedJobNote;
            $job_present = Job::find($request->job_id);
            if(!$job_present)
            {
                return $this->sendError(trans('message.oops_job_not_found'));
            }
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
     *
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
     *
     * @param Request
     * @return Object 
     */
    public function archivedSavedJob(Request $request)
    {
        try
        {
            $user = Auth::user();
            $jobmodel = new ArchiveJob;
            $saved_data = $jobmodel->saved_job($request,$user);
            if($saved_data)
            {
                $find = UserSavedJob::find($request->id);
                if($find)
                {
                    $find->delete();
                }
                return $this->sendSuccess(trans('message.archived_job'),$saved_data);
            }
            return $this->sendError(trans('message.job_creation_error'),404);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }
    }

    /**
     *
     * @param NA
     * @return Object 
     */
    public function getUserArchivedJobs()
    {
        $user = Auth::user();
        $applied = ArchiveJob::where('user_id',$user->id)->with('jobs')
        ->get();

        if(count($applied) > 0)
        {
            return $this->sendSuccess(trans('message.archived_jobs_reterived'),$applied);
        }
        return $this->sendError(trans('message.archived_job_not_found'),404);
    }

    /**
     *
     * @param id
     * @return Object 
     */
    public function deleteArchivedJob($id)
    {
        $applied = ArchiveJob::find($id);

        if($applied) 
        {
            $applied->delete();
            return $this->sendSuccess(trans('message.archived_jobs_deleted'),$applied);
        }
        return $this->sendError(trans('message.archived_job_not_found'),404);
    }

    /**
     *
     * @param NA
     * @return Object 
     */
    public function changeEmailNotification()
    {
        $user = Auth::user();

        if($user->email_notification == 1)
        {
            $user->update([
                'email_notification' => 0
            ]);
        }
        else
        {
            $user->update([
                'email_notification' => 1
            ]);
        }

        // notification 
        $notification_title = "Email Notification Changed";
        $notification_content = trans('message.email_notification');
        $nf = new Notification;
        $nf->create_notification($user,$notification_title,$notification_content);
        return $this->sendSuccess(trans('message.email_notification'),$user);
    }

    /**
     *
     * @param NA
     * @return Object 
     */
    public function ReportGeneration()
    {
        $user = Auth::user();

        $userModel = new User;
        $get_reports = $userModel->jobseeker_report_genration($user);
        
        /**
         * if pdf export will be there then it'll come here
         */

        return $this->sendSuccess(trans('message.jobseeker_report'),$get_reports);
    }

    /**
     *
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
     *
     * @param id
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
