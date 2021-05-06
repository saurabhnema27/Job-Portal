<?php

use Illuminate\Http\Request;

/*  
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1', 'middleware' => 'cors'], function () {

    Route::post('/user/signup','API\UserLoginSignUpController@register');
    Route::post('/user/login','API\UserLoginSignUpController@login');
    Route::post('user/forgetpassword', 'API\UserLoginSignUpController@ForgetPassword');
    Route::post('password/find', 'API\UserLoginSignUpController@find');
    Route::post('password/resetpassword', 'API\UserLoginSignUpController@reset');
    Route::post('/search/job/free','API\UserLoginSignUpController@searchFreeJobs');
    Route::get('/view/job-details/{id}','API\UserLoginSignUpController@FreeJobDetails');
    Route::post('/contact-us','API\UserLoginSignUpController@contactUs');
    Route::post('/subscribe/newsletter','API\UserLoginSignUpController@subscribeNewsLetter');
    Route::get('/front-page/details','API\UserLoginSignUpController@frontPageDetails');
    Route::get('/get/cms/{id}','API\CmsController@viewContent');

    Route::group(['prefix' => 'admin'], function () {
        
        Route::post('/login','API\AdminController@login');
        
        Route::group(['middleware' => ['auth:api','onlyadmin']], function () {
            
            Route::get('/get-details','API\AdminController@getDetails');
            Route::get('/logout','API\AdminController@logout');
            Route::post('/all-jobseeker','API\AdminController@jobSeekerList');
            Route::post('/all-employeer','API\AdminController@employeerlist');
            Route::put('/activiate/deactivate/user/{id}','API\AdminController@activateDeactivateJobSeeker');
            Route::put('/block/unblock/user/{id}','API\AdminController@blockUnblock');
            Route::delete('/delete/user/{id}','API\AdminController@deleteUser');
            Route::get('/get/specific/user/{id}','API\AdminController@viewSpecificUser');
            Route::put('/change/password','API\AdminController@changePassword');
            Route::post('/all-job-list','API\AdminController@allJobList');
            Route::put('/publish/unpublish/job/{id}','API\AdminController@changeJobPublishStatus');
            Route::get('/get/specific/job/{id}','API\AdminController@specificJobDetails');
            Route::delete('/delete-job/{id}','API\AdminController@deleteSpecificJob');

            Route::get('/see/resume/{resume_id}','API\AdminController@seeUserResume');
            Route::get('/download/resume/{id}','API\AdminController@downloadResume');

            Route::get('/jobseeker/activities/{id}','API\AdminController@jobseekerActivities');
            Route::get('/employeer/activities/{id}','API\AdminController@EmployeerActivities');

            Route::get('/list-of-jobseeker/applied-for-job','API\AdminController@CandidatesAppliedForJob');
            Route::get('/get-specific-candidate/applied-for-job/{id}','API\AdminController@ListofSpecificCandidatesAppliedForJob');
            Route::post('/contactUs','API\AdminController@contactUs');
            Route::get('/get-specific-contact-us/{id}','API\AdminController@specificContactUs');
            Route::delete('/delete/contactus/{id?}','API\AdminController@deleteContactUs');
            Route::post('/newsletter','API\AdminController@Newsletter');
            Route::delete('/delete/newsletter/{id}','API\AdminController@deleteNewsletter');

            Route::get('/get/report/{id}','API\AdminController@generateReport');
            Route::get('/notification','API\AdminController@Notifications');
            Route::get('/read/notification/{id}','API\AdminController@ReadNotification');
            Route::delete('/delete/notification/{id}','API\AdminController@deleteNotification');

            Route::post('/add/update/cms','API\CmsController@addUpdateCms');

            Route::get('/get/single/subs/plan/{id}','API\AdminController@getSinglePlan');
            Route::post('/all/subscrption-plans','API\AdminController@AllPlans');
            Route::post('/add/update/subscription/plan','API\AdminController@addUpdatePlan');
            Route::delete('/delete/subs/plan/{id}','API\AdminController@deletePlan');
            
        });
    });


    Route::group(['middleware' => ['auth:api']], function () {

        Route::post('/update-profile','API\UserLoginSignUpController@updateProfile');
        Route::delete('/delete-image','API\UserLoginSignUpController@deleteImage');
        Route::get('/logout','API\UserLoginSignUpController@logout');
        Route::get('/user/details','API\UserLoginSignUpController@userDetails');

        Route::group(['prefix' => 'jobseeker', 'middleware' => 'JobSeeker'], function () {
            
            Route::get('/dashboard','API\JobSeekerController@Dashboard');
            Route::get('/change-email-notification','API\JobSeekerController@changeEmailNotification');
            Route::post('/upload/resume','API\JobSeekerController@UploadResume');
            Route::get('/see/resume/{resume_id}','API\JobSeekerController@seeResume');
            Route::delete('/delete/resume/{id}','API\JobSeekerController@deleteResume');
            Route::get('/download/resume/{id}','API\JobSeekerController@downloadResume');
            Route::post('/search-job','API\JobSeekerController@searchJob');
            Route::post('/apply-job','API\JobSeekerController@applyJob');
            Route::get('/all-applied-jobs','API\JobSeekerController@getUserAppliedJobs');
            Route::post('/save-job','API\JobSeekerController@savedJob');
            Route::post('/unsaved-job','API\JobSeekerController@UnsavedJob');
            Route::get('/all-saved-jobs','API\JobSeekerController@getUserSavedJobs');
            Route::get('/get/specific/job/{id}','API\JobSeekerController@specificJob');
            Route::post('/apply-from-saved-job','API\JobSeekerController@applyFromSavedJob');
            Route::get('/get-all-interview-details','API\JobSeekerController@getListOfInterview');
            Route::post('/add-note','API\JobSeekerController@addNoteToJob');
            Route::delete('/delete-note/{id}','API\JobSeekerController@deleteNote');
            Route::post('/archivedjob','API\JobSeekerController@archivedSavedJob');
            Route::get('/all-archived-jobs','API\JobSeekerController@getUserArchivedJobs');
            Route::delete('/delete/archived-job/{id}','API\JobSeekerController@deleteArchivedJob');
            Route::get('/report/genrations','API\JobSeekerController@ReportGeneration');
            Route::get('/notification','API\JobSeekerController@Notifications');
            Route::get('/read/notification/{id}','API\JobSeekerController@ReadNotification');
        });

        Route::group(['prefix' => 'employeer', 'middleware' => 'Employeer'], function () {
           
            Route::post('/create-job','API\EmployeersController@createJob');
            Route::put('/update-job','API\EmployeersController@updateJob');
            Route::post('/all-jobs','API\EmployeersController@getAllJob');
            Route::get('/get/specific/job/{id}','API\EmployeersController@specificJob');
            Route::post('/change/job/status','API\EmployeersController@changeJobStatus');
            Route::delete('/delete-job/{id}','API\EmployeersController@deleteJob');
            Route::post('/serach-job','API\EmployeersController@searchJob');
            Route::post('/list-of-jobseekeer','API\EmployeersController@ListOfJobseeker');
            Route::post('/change/jobseeker/job/status','API\EmployeersController@changeJobSeekerStatus');
            Route::post('/setup-interview','API\EmployeersController@setUpInterview');
            Route::post('/update-interview','API\EmployeersController@updateInterview');
            Route::delete('/delete-interview/{id}','API\EmployeersController@deleteInterview');
            Route::get('/single-interview-detail/{id}','API\EmployeersController@getSingleInterview');
            Route::get('/all-interview-details','API\EmployeersController@AllInterviewDetail');
            Route::get('/report/genrations','API\EmployeersController@ReportGeneration');
            Route::post('/add-note','API\EmployeersController@addNoteToJob');
            Route::delete('/delete-note/{id}','API\EmployeersController@deleteNote');
            Route::get('/notification','API\EmployeersController@Notifications');
            Route::get('/read/notification/{id}','API\EmployeersController@ReadNotification');
        });

    });

});
