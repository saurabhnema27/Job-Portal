<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Interview extends Model
{
    //fillable
    protected $fillable = [
        'user_id','jobseeker_id','time_zone','date','start_time','end_time','job_id','interview_type','interview_address','message'
    ];

    public function jobseeker()
    {
        return $this->belongsTo('App\Models\User','jobseeker_id');
    }

    public function jobs()
    {
        return $this->belongsTo('App\Models\Job','job_id');
    }

    public function users()
    {
        return $this->belongsTo('App\Models\User','jobseeker_id');
    }

    public function create_update($request, $user)
    {
        $interview_model = Interview::firstorNew(['id' => $request->id]);
        $interview_model->user_id = $user->id;
        $interview_model->jobseeker_id = $request->user_id;
        $interview_model->time_zone = $request->time_zone ?: NULL;
        $interview_model->time_zone_format = $request->time_zone_format ?: NULL;
        $interview_model->date = $request->date;
        $interview_model->start_time = $request->start_time;
        $interview_model->end_time = $request->end_time;
        $interview_model->job_id = $request->job_id;
        $interview_model->interview_type = $request->interview_type ?: NULL;
        $interview_model->interview_address = $request->interview_address ?: NULL;
        $interview_model->message = $request->message ?: NULL;

        $interview_model->save();
        return $interview_model;
    }
}
