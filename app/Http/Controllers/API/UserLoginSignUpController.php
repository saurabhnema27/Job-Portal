<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Job;
use App\Models\ContactUs;
use App\Models\Newsletter;
use App\Models\Interview;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\Models\PasswordReset;

class UserLoginSignUpController extends Controller
{
    //function to login and signup

    /**
     * This is the function used for login for jobseeker, employeer
     * @param Request
     * @return Object 
     */
    public function register(Request $request)
    {
        try
        {
            $rules =  [
                'email' => 'required|email|unique:users',
                'first_name' => 'string',
                'last_name' => 'string',
                'password' => 'required|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%_@]).*$/',
                'confirm_password' => 'same:password',
                'mobile' => 'numeric|unique:users',
            ];

            $message = [
                'password.regex' => "The password contains characters from at least three of the following five categories: (A – Z), (a – z), (0 – 9), (!, $, #,_,@ or %), Unicode characters"
            ];

            $validator = Validator::make($request->all(),$rules,$message);
            if ($validator->fails()) 
            {
              return $this->sendError($validator->messages()->first());
            }

            $usermodel = new User;

            $saveuser = $usermodel->add_update_user($request);
            $user = Auth::loginUsingId($saveuser->id);
            $success = $user;
            $success['token'] = $user->createToken('MyApp')->accessToken;
            return $this->sendSuccess(trans('message.registration_success'),$success);
            
            
        }
        catch ( \Exception $e)  
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }
    }

    /**
     * This is the function used for login for jobseeker, employeer
     * @param Request
     * @return Object 
     */
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

            $usermodel = new User;
            $findEmail = User::where('email',$request->email)->first();
            if($findEmail)
            {
                if($findEmail->user_status == 0 || $findEmail->block_unblock == 1)
                {
                    return $this->sendError(trans("message.admin_blocked"));
                }
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
        catch ( \Exception $e)  
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }
    }

    /**
     * This is the function used to update the profile for admin, jobseeker, employeer
     * @param Request
     * @return Object 
     */
    public function updateProfile(Request $request)
    {

        $rules =  [
            'email' => 'required|email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'password' => 'nullable|string|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%_@]).*$/',
            'new_password' => 'nullable|string|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%_@]).*$/',
            'confirm_password' => 'same:new_password',
            // 'mobile' => 'numeric',
        ];

        $message = [
            'new_password.regex' => "The password contains characters from at least three of the following five categories: (A – Z), (a – z), (0 – 9), (!, $, #,_,@ or %), Unicode characters"
        ];

        $validator = Validator::make($request->all(),$rules,$message);
        if ($validator->fails()) 
        {
          return $this->sendError($validator->messages()->first());
        }

        $user = Auth::user();
        $usermodel = new User;
        $email = User::where('email',$request->email)->whereNotNull('email')->first();
        $mobile = User::where('mobile', $request->mobile)->whereNotNull('mobile')->first();

        if($email && $email->email != $user->email)
        {
            return $this->sendError(trans('message.email_taken'));
        }
        if($mobile && $mobile->mobile != $user->mobile)
        {
            return $this->sendError(trans('message.mobile_taken'));
        }

        if(isset($request->new_password))
        {
            if(!Hash::check($request->password,$user->password))
            {
                return $this->sendError(trans('message.old_password_didnt_match'));
            }

            if(Hash::check($request->new_password,$user->password))
            {
                return $this->sendError(trans('message.old_new_cant_same'));
            }
            if (Hash::check($request->password, $user->password)) 
            {
                $request['user_type'] = $user->user_type;
                $request->password = $request->new_password;
                $saveuser = $usermodel->add_update_user($request);
                $user->token()->revoke();
                return $this->sendSuccess(trans('message.updated_success'),$saveuser);
            }
            return $this->sendError(trans("message.old_password"));
        }

        // $request['password'] = $user->password;
        $request['user_type'] = $user->user_type;
        $saveuser = $usermodel->add_update_user($request);
        return $this->sendSuccess(trans('message.updated_success'),$saveuser);
    }


    /**
     * This is the function used to delete the image for jobseeker, admin, employeer
     * @param Request
     * @return Object 
     */
    public function deleteImage()
    {
        $user = Auth::user();
        $image = $user->image;
        if(isset($image) || !empty($image || $image != ""))
        {
            // dd($image);
            $user->update([
                'image' => null
            ]);
            $check = \unlink(storage_path('user_images/'.$image));
            return $this->sendSuccess(trans('message.image_delete'),$user);
        }

        return $this->sendError(trans('message.image_not_found'));
    }

    /**
     * This is the function used for forget password for jobseeker, employeer, admin
     * @param Request
     * @return Object 
     */
    public function ForgetPassword(Request $request)
    {
        try
        {
            $rules =  [
                'email' => 'required|email'
            ];
    
            $validator = Validator::make($request->all(),$rules);
            if ($validator->fails()) 
            {
              return $this->sendError($validator->messages()->first());
            }   

            $user = User::where('email', $request->email)->first();
            if(!$user)
            {
                return $this->sendError(trans('message.email_not_found'));
            }
            $passwordReset = PasswordReset::updateOrCreate(
                ['email' => $user->email],
                [
                    'email' => $user->email,
                    'token' => str_random(4)
                 ]
            );

            if ($user && $passwordReset)
            {
                $user->notify(
                    new PasswordResetRequest($passwordReset->token)
                );
            }

            return $this->sendSuccess(trans('message.emailed_password_link'));


        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }
    }

    /**
     * This is the function used to Reterive the token for forget password
     * @param Request
     * @return Object 
     */
    public function find(Request $request)
    {
        try
        {
            $passwordReset = PasswordReset::where('token', $request->otp)->first();
            if (!$passwordReset)
            {
                return $this->sendError(trans('message.password_token_invalid'));
            }
            if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) 
            {
                $passwordReset->delete();
                return $this->sendError(trans('message.password_token_invalid'));
            }

            return $this->sendSuccess(trans('message.password_reset_token_found'),$passwordReset);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }
    }

    /**
     * This is the function used to reset the password it's common for jobseeker, admin, employeer
     * @param Request
     * @return Object 
     */
    public function reset(Request $request)
    {
        $rules =  [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'confirm_password' => 'required|same:password',
            'otp' => 'required|string'
        ];

        $validator = Validator::make($request->all(),$rules);
        if ($validator->fails()) 
        {
          return $this->sendError($validator->messages()->first());
        }  

        $passwordReset = PasswordReset::where([
            ['token', $request->otp],
            ['email', $request->email]
        ])->first();

        if (!$passwordReset)
        {
            return $this->sendError(trans('message.password_token_invalid'));
        }

        $user = User::where('email', $passwordReset->email)->first();
        if (!$user)
        {
            return $this->sendError(trans('message.email_not_found'));
        }

        $user->password = bcrypt($request->password);
        $user->save();
        $passwordReset->delete();
        $user->notify(new PasswordResetSuccess($passwordReset));
        return $this->sendSuccess(trans('message.password_reset_success'),$user);
    }

    /**
     * This is the function used to logout for jobseeker, employeer, admin
     * @param NA
     * @return Object 
     */
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
            return $this->sendSuccess(trans('message.logout_success'));
        } 
        catch ( \Exception $e)  
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }
    }

    /**
     * This is the function used to serach the free jobs for guest
     * @param Request
     * @return Object 
     */
    public function searchFreeJobs(Request $request)
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
            return $this->sendSuccess(trans('message.jobs_not_found'),$searched_data);
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }
    }

    /**
     * This is the function used to Reterive free job details
     * @param id
     * @return Object 
     */
    public function FreeJobDetails($id)
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
     * This is the function used to create the contactus
     * @param Request
     * @return Object 
     */
    public function contactUs(Request $request)
    {
        try
        {
            $rules =  [
                'email' => 'required|email',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'mobile' => 'required|numeric',
                'organization' => 'required|string',
                'mail_is_about' => 'required|string',
                'message' => 'required|string'
            ];
    
    
            $validator = Validator::make($request->all(),$rules);
            if ($validator->fails()) 
            {
              return $this->sendError($validator->messages()->first());
            }

            $contactUsModel = new ContactUs;
            $saved_contact_us = $contactUsModel->add_contact_us($request);
            if($saved_contact_us)
            {
                return $this->sendSuccess(trans('message.contact_saved'),$saved_contact_us);
            }
            return $this->sendSuccess(trans('message.something_went'));
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

    /**
     * This is the function used to subscribe the newletter
     * @param Request
     * @return Object 
     */
    public function subscribeNewsLetter(Request $request)
    {
        try
        {
            $rules =  [
                'email' => 'required|email|unique:newsletters',
            ];
        
            $validator = Validator::make($request->all(),$rules);
            if ($validator->fails()) 
            {
              return $this->sendError($validator->messages()->first());
            }

            $newsletterModel = new Newsletter;
            $saved_newsletter = $newsletterModel->addNewsLetter($request);
            if($saved_newsletter)
            {
                return $this->sendSuccess(trans('message.newsletter_sub'),$saved_newsletter);
            }
            return $this->sendSuccess(trans('message.something_went'));
        }
        catch( \Exception $e)
        {
            $msg = $e->getMessage();
            return $this->sendError($msg);
        }  
    }

    /**
     * This is the function used to Reterive thefromt page details
     * @param NA
     * @return Object 
     */
    public function frontPageDetails()
    {
        $active_employeer = User::where([
            'user_type' => 2,
            'user_status' => 1
        ])->get()->count();

        $jobs = Job::where('can_job_publish', 1)->get()->count();

        $interview = Interview::get()->count();
        
        $success = [
            'active_employeer' => $active_employeer,
            'jobs' => $jobs,
            'interview' => $interview
        ];
        return $this->sendSuccess("Content is reterived Successfully.",$success);
    }

    /**
     * This is the function used to get the login user details
     * @param NA
     * @return Object 
     */
    public function userDetails()
    {
        $user = Auth::user();
        return $this->sendSuccess(trans('message.user_reterive'),$user);
    }
}
