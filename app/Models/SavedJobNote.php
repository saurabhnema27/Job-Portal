<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedJobNote extends Model
{
    //fillable
    protected $fillable = [
        'job_id','user_id','note'
    ];

    public function jobs()
    {
        return $this->belongsTo('App\Models\Job','job_id');
    }

    public function add_note($request,$user)
    {
        $get_note = SavedJobNote::firstorNew(['id' => $request->id]);
        $get_note->job_id = $request->job_id;
        $get_note->user_id = $user->id;
        $get_note->note = $request->note;

        $get_note->save();
        return $get_note;
    }

}
