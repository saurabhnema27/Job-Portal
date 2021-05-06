<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cms;
use Validator;

class CmsController extends Controller
{
    public function addUpdateCms(Request $request)
    {
        $rules =  [
            'type_of_content' => 'required|numeric',
            'content_data' => 'required|array',
        ];

        $validator = Validator::make($request->all(),$rules);
        if ($validator->fails()) 
        {
          return $this->sendError($validator->messages()->first());
        }

        $cmsModel = new Cms;
        $savingUpdateData = $cmsModel->add_update_content($request);
        if($savingUpdateData)
        {
            if($request->has('id'))
            {
                return $this->sendSuccess(trans('message.cms_updated'),$savingUpdateData); 
            }
            return $this->sendSuccess(trans('message.cms_added'),$savingUpdateData); 
        }
        return $this->sendError(trans('message.something_went'));
    }

    public function viewContent($id)
    {
        $cms = Cms::where('type_of_content',$id)->get();
        if(count($cms) > 0)
        {
            return $this->sendSuccess(trans('message.cms_reterived'),$cms); 
        }
        return $this->sendError(trans('message.cms_not_found'));
            
    }
}
