<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchiveJob extends Model
{
    //
    protected $fillable = [
        'user_id','job_id'
    ];

    public function jobs()
    {
        return $this->belongsTo('App\Models\Job','job_id');
    }

    public function users()
    {
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function saved_job($request,$user)
    {
        $uj = new ArchiveJob;
        $uj->user_id = $user->id;
        $uj->job_id = $request->job_id;

        $uj->save();
        return $uj;
    }
}
