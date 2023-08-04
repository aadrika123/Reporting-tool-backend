<?php

namespace App\Repository\Api;

use App\Models\VtResource;
use App\Models\VtSearchGroup;
use App\Models\VtString;
use App\Models\VtTemplate;
use App\Models\VtTemplateDeatil;
use App\Models\VtTemplatePagelayout;
use App\Models\VtTemplateFooter;

use App\Http\Requests\Request;

use Illuminate\Support\Facades\DB;
use Exception;
use App\Traits\Api\MasterApi;

/**
 * Repository for Fetching The Api master 
 * ---------------------------------------------------------------------------------------------------------
 * Created On- 
 * Created By-
 * ---------------------------------------------------------------------------------------------------------
 */

class MasterApiRepository
{
    use MasterApi;

    /**
     * Store a new Tempalte.
     *
     * @param  \App\Http\Requests\TemplateRequest  $data
     * @model App\Models\VtTemplate VtTemplate
     * @trait App\Traits\Api\MasterApi $this
     * @return Illuminate\Http\Response
     */
    public function InsTemplate($data)
    {
        // $validated = $data->validated();
        // if ($validated->fails()) {    
        //     return response()->json(['Message' => $validated->messages()]);
        // }
        try {
            $res = new VtTemplate;
            return $this->InsertData($res, $data->request);
        } catch (Exception $e) {
            return response()->json([$e, 400]);
        }
    }


    /**
     * Update Tempalte.
     *
     * @param  \App\Http\Requests\TemplateRequest  $data
     * @model App\Models\VtTemplate VtTemplate
     * @trait App\Traits\Api\MasterApi $this
     * @return Illuminate\Http\Response
     */
    public function upTemplate($data)
    {
        try {
            $res = VtTemplate::find($data->id);
            if ($res) {
                return $this->UpdateData($res, $data->request);
            } else {
                return response()->json('Id Not Found', 404);
            }
        } catch (Exception $e) {
            return response()->json([$e, 400]);
        }
    }
}
