<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Job;
use App\models\userJob;
use App\models\UserSavedJob;
use App\Models\Resume;
use App\models\ContactUs;
use App\models\Newsletter;
use App\Models\Notification;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminController extends Controller
{
    //login function
    public function login(Request $request)
    {
        try
        {
            $rules =  [
                'email' => 'required|email',
                'password' => 'required',
            ];

            $validator = Validator::make($request->all(),$rules);
            if ($validator->fails()) 
            {
              return $this->sendError($validator->messages()->first());
            }

            $findEmail = User::where('email',$request->email)->first();
            if($findEmail)
            {
                if(Auth::attempt(['email' => $request->email, 'password' => $request->password]))
                {
                    $user = Auth::user(); 
                    $success = $user;
                    $success['token'] =  $user->createToken('MyApp')->accessToken; 
                    return $this->sendSuccess(trans('message.login_success'),$success);
                }
                return $this->sendError(trans('message.invalid'));
            }
            return $this->sendError(trans('message.invalid'));
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }
    }

    public function getDetails()
    {
        $user = Auth::user();
        // $user->image = env('APP_URL').'/storage/user_images/'.$user->image;
        return $this->sendSuccess(trans('message.admin_details'),$user);
    }

    public function jobSeekerList(Request $request)
    {
        $userModel = new User;
        $list = $userModel->serach_user($request);
        $list = $list->where('user_type',1)->paginate(10);
        if(count($list) > 0)
        {
            // foreach($list as $key => $value)
            // {
            //     $value->image = env('APP_URL').'/storage/user_images/'.$value->image;
            // }
            return $this->sendSuccess(trans('message.jobseeker_list'),$list);
        }
        return $this->sendError(trans('message.no_Jobseeker_found'));
    }

    public function employeerList(Request $request)
    {
        $userModel = new User;
        $list = $userModel->serach_user($request);
        $list = $list->where('user_type',2)->paginate(10);
        if(count($list) > 0)
        {
            // foreach($list as $key => $value)
            // {
            //     $value->image = env('APP_URL').'/storage/user_images/'.$value->image;
            // }
            return $this->sendSuccess(trans('message.employeer_list'),$list);
        }
        return $this->sendError(trans('message.no_employeer_found'));
    }

    public function activateDeactivateJobSeeker($id)
    {
        $get_user = User::find($id);
        if($get_user)
        {
            if($get_user->user_status == 1)
            {
                $get_user->user_status = 0;
                $get_user->save();
                return $this->sendSuccess(trans("message.user_status_changes"),$get_user);
            }
            else
            {
                $get_user->user_status = 1;
                $get_user->save();
                return $this->sendSuccess(trans("message.user_status_changes"),$get_user);
            }
        }
        return $this->sendError(trans("message.user_not_found"));
    }

    public function blockUnblock($id)
    {
        $get_user = User::find($id);
        if($get_user)
        {
            if($get_user->block_unblock == 1)
            {
                $get_user->block_unblock = 0;
                $get_user->save();
                return $this->sendSuccess(trans("message.user_block_changes"),$get_user);
            }
            else
            {
                $get_user->block_unblock = 1;
                $get_user->save();
                return $this->sendSuccess(trans("message.user_block_changes"),$get_user);
            }
        }
        return $this->sendError(trans("message.user_not_found"));
    }

    public function deleteUser($id)
    {
        $get_user = User::find($id);
        if($get_user)
        {
            $get_user->delete();
            return $this->sendSuccess(trans("message.user_deleted"));
        }
        return $this->sendError(trans("message.user_not_found"));
    }

    public function viewSpecificUser($id)
    {
        $get_user = User::find($id);
        if($get_user)
        {
            return $this->sendSuccess(trans("message.user_found"),$get_user);
        }
        return $this->sendError(trans("message.user_not_found"));
    }

    public function changePassword(Request $request)
    {
        $rules =  [
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required|same:new_password',
        ];

        $validator = Validator::make($request->all(),$rules);
        if ($validator->fails()) 
        {
          return $this->sendError($validator->messages()->first());
        }

        $user = Auth::user();

        if(Hash::check($request->new_password,$user->password))
        {
            return $this->sendError(trans('message.old_new_cant_same'));
        }

        // if(!Hash::check($request->old_password,$user->password))
        // {
        //     return $this->sendError(trans('message.old_password_unmatch'));
        // }

        if (Hash::check($request->old_password, $user->password)) 
        {
            $user->password = bcrypt($request->new_password);
            $user->save();
            $user->token()->revoke();

            // Notification
            $notification_title = "Password Changed";
            $notification_content = trans('message.updated_success');
            $nf = new Notification;
            $nf->create_notification($user,$notification_title,$notification_content);

            return $this->sendSuccess(trans('message.updated_success'),$user);
        }
        return $this->sendError(trans("message.old_password"));
    }

    public function logout()
    {
        try
        {
            $accessToken = Auth::user()->token();
            \DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true
            ]);

            $accessToken->revoke();
            return $this->sendSuccess(trans('Msg.logout_success'));
        } 
        catch ( \Exception $e)  
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }
    }

    public function allJobList(Request $request)
    {
        try
        {
            $jobmodel = new Job;
            $searched_data = $jobmodel->admin_serach_job($request);
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

    public function changeJobPublishStatus($id)
    {
        try
        {
            $getJob = Job::find($id);

            if($getJob)
            {
                if($getJob->can_job_publish == 0)
                {
                    $getJob->can_job_publish = 1;
                    $getJob->save();
                    return $this->sendSuccess(trans('message.job_publish_status'),$getJob);
                }
                $getJob->can_job_publish = 0;
                $getJob->save();

                $notification_title = "Job Status Changed by Admin";
                $notification_content = trans('message.job_publish_status');
                $nf = new Notification;
                $user = User::find($getJob->employeer_id);
                $nf->create_notification($user,$notification_title,$notification_content);
                return $this->sendSuccess(trans('message.job_publish_status'),$getJob);
            }
            return $this->sendError(trans('message.jobs_not_found'),404);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

    public function specificJobDetails($id)
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

    public function deleteSpecificJob($id)
    {
        try
        {
            $user = Auth::user();

            $getJob = Job::find($id);

            if($getJob)
            {
                $getJob->delete();

                // 
                $notification_title = "Job Deleted Successfully";
                $notification_content = trans('message.job_deleted');
                $nf = new Notification;
                $user = User::find($getJob->employeer_id);
                $nf->create_notification($user,$notification_title,$notification_content);

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

    public function seeUserResume($id)
    {
        try
        {
            $find = Resume::where([
                'user_id' => $id
            ])->first();

            if($find)
            {
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

    public function downloadResume($id)
    {
        try
        {
            $find = Resume::where([
                'user_id' => $id
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

    public function jobseekerActivities($id)
    {
        $user = User::find($id);
        if($user)
        {
            $user_details = User::where('id',$user->id)->with('applied_jobs.jobs','saved_jobs.jobs','interview_list.jobs')->get();
            return $this->sendSuccess(trans("message.jobseeker_list"),$user_details);
        }

        return $this->sendError(trans("message.no_Jobseeker_found"),404);
       
    }

    public function EmployeerActivities($id)
    {
        $user = User::find($id);
        if($user)
        {
            $user_details = User::where('id',$user->id)->with('jobs','interview_owner')->get();
            return $this->sendSuccess(trans("message.jobseeker_list"),$user_details);
        }

        return $this->sendError(trans("message.no_Jobseeker_found"),404);
       
    }

    public function CandidatesAppliedForJob()
    {
        $all_jobs = UserJob::with('users','jobs','get_list_of_interviews')->get();
        if(count($all_jobs) > 0)
        {
            return $this->sendSuccess(trans("message.candidate_list"),$all_jobs);
        }    
        return $this->sendError(trans("message.candidate_not_found"),404);
    }

    public function ListofSpecificCandidatesAppliedForJob($id)
    {
        $get_job_details = User::where('id',$id)->with('applied_jobs.jobs','saved_jobs.jobs')->get();
        return $this->sendSuccess(trans("message.candidate_list"),$get_job_details);
    }

    public function ContactUs(Request $request)
    {
        $contactUsModel = new ContactUs;
        $contact_us_list = $contactUsModel->serach_contact_us($request);
        if($contact_us_list)
        {
            return $this->sendSuccess(trans("message.contact_list"),$contact_us_list);
        }
        return $this->sendError(trans("message.contact_list_not_found"),404);
    }

    public function specificContactUs($id)
    {
        $get = ContactUs::find($id);
        if($get)
        {
            return $this->sendSuccess(trans("message.contact_list"),$get);
        }
        return $this->sendError(trans("message.contact_list_not_found"),404);
    }

    public function deleteContactUs($id)
    {
        $get = ContactUs::find($id);
        if($get)
        {
            $get->update([
                'is_deleted' => 1
            ]);

            // 
            $notification_title = "Contact Us Deleted";
            $notification_content = trans("message.contact_list");
            $nf = new Notification;
            $user = Auth::user();
            $nf->create_notification($user,$notification_title,$notification_content);
            return $this->sendSuccess(trans("message.contact_list"),$get);
        }
        return $this->sendError(trans("message.contact_list_not_found"),404);
    }

    public function Newsletter(Request $request)
    {
        $NewsletterModel = new Newsletter;
        $newsletterList = $NewsletterModel->serach_newsletter($request);
        if($newsletterList)
        {
            return $this->sendSuccess(trans("message.newsletter_list"),$newsletterList);
        }
        return $this->sendError(trans("message.newsletter_not_found"),404);
    }

    public function deleteNewsletter($id)
    {
        $get = Newsletter::find($id);
        if($get)
        {
            $get->delete();
            $notification_title = "Newsletter Deleted";
            $notification_content = trans("message.newsletter_deleted");
            $nf = new Notification;
            $user = Auth::user();
            $nf->create_notification($user,$notification_title,$notification_content);
            return $this->sendSuccess(trans("message.newsletter_deleted"),$get);
        }
        return $this->sendError(trans("message.newsletter_not_found"),404);
    }

    public function generateReport($id)
    {
        $user = User::find($id);
        $userModel = new User;
        if($user->user_type == 1)
        {
            $get_reports = $userModel->jobseeker_report_genration($user);
        }
        else
        {
            $get_reports = $userModel->employeer_reports_generations($user);
        }

        /**
         * if pdf export will be there then it'll come here
         */
        
        return $this->sendSuccess(trans('message.jobseeker_report'),$get_reports); 
    }

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

    public function deleteNotification($id)
    {
        $get_nf = Notification::find($id);

        if($get_nf)
        {
           $get_nf->delete();            
           return $this->sendSuccess(trans('message.notification_delete')); 
        }
        return $this->sendError(trans('message.notification_not_found'),404);
    }

    public function addUpdatePlan(Request $request)
    {
        try
        {
            $rules =  [
                'plan_title' => 'required|email',
                'plan_price' => 'required',
            ];

            $validator = Validator::make($request->all(),$rules);
            if ($validator->fails()) 
            {
              return $this->sendError($validator->messages()->first());
            }

            $sp = new SubscriptionPlan;
            $save_data = $sp->add_update_plan($request);
            if($save_data)
            {
                if($request->has('id'))
                {
                    return $this->sendSuccess(trans('message.sub_added'),$save_data); 
                }
                return $this->sendSuccess(trans('message.sub_modif'),$save_data);     
            }
            return $this->sendError(trans('message.something_went'),404);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }
    }

    public function AllPlans(Request $request)
    {
        $sp = new SubscriptionPlan;
        $save_data = $sp->serach_plans($request);
        if($save_data)
        {
            $save_data = $save_data->paginate(10);
            return $this->sendSuccess(trans('message.subs_reterive'),$save_data);     
        }
        return $this->sendError(trans('message.subs_not_found'),404);
    }

    public function deletePlan($id)
    {
        $getPlan = SubscriptionPlan::findorFail($id);
        $getPlan->delete();
        
        // or softdelete
        // $getPlan->update([
        //     'is_active' => 0
        // ]);

        return $this->sendSuccess(trans('message.subs_deleted')); 
    }

    public function getSinglePlan($id)
    {
        $getPlan = SubscriptionPlan::findorFail($id);
        return $this->sendSuccess(trans('message.subs_reterive'),$getPlan); 
    }
}
