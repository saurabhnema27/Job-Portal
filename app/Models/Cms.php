<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cms extends Model
{
    protected $fillable = [
        'type_of_content','content_data','updated_at'
    ];

    protected $casts = [
        'content_data' => 'array',
    ];

    public function add_update_content($request)
    {
        $cms = Cms::firstorNew(['id' => $request->id]);
        $cms->type_of_content = $request->type_of_content;
        $cms->content_data = $request->content_data;
        $cms->save();

        return $cms;
    }
}
