<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    //fillable property
    protected $fillable = [
        'notification_content','notification_title','is_viewed','user_id','updated_at'
    ];

    public function users()
    {
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function create_notification($user, $notification_title, $notification_content)
    {
        $nf = new Notification;
        $nf->notification_title = $notification_title;
        $nf->notification_content = $notification_content;
        $nf->user_id = $user->id;
        $nf->is_viewed = 0;
        $nf->save();

        return $nf;
    }
}
