<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserJob extends Model
{
    //fillable data
    protected $fillable = [
        'user_id','job_id','job_status'
    ];

    public function jobs()
    {
        return $this->belongsTo('App\Models\Job','job_id');
    }

    public function get_list_of_interviews()
    {
        return $this->hasMany('App\Models\Interview','job_id');
    }

    public function users()
    {
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function saved_notes()
    {
        return $this->hasMany('App\Models\SavedJobNote','user_id');
    }

    public function apply_job($request,$user)
    {
        $uj = new UserJob;
        $uj->user_id = $user->id;
        $uj->job_id = $request->job_id;
        $uj->job_status = "PENDING";

        $uj->save();
        return $uj;
    }
}
