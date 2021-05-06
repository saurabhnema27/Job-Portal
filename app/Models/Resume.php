<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    //fillable property
    protected $fillable = ['user_id','resume_name','resume_status','resume_title'];

    protected $appends = ['full_resume_url'];

    public function getFullResumeUrlAttribute()
    {
        return env('APP_URL').'/storage/user_resume/'.$this->resume_name;
    }

    public function add_delete_resume($request,$user)
    {
        $resume_name = $this->upload_resume($request);
        
        $resumemodel = new Resume;
        $resumemodel->user_id = $user->id;
        $resumemodel->resume_title = $request->resume_title ?: NULL;
        $resumemodel->resume_name = $resume_name;
        $resumemodel->resume_status = 1;
        $resumemodel->save();

        return $resumemodel;
    }

    protected function upload_resume($request)
    {
        $destinationPath = 'storage/user_resume/';
        $file = $request->file('resume');
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
}
