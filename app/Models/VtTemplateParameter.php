<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VtTemplateParameter extends Model
{
    use HasFactory;
    protected $guarded = [];


    /**
     * | Get Template Parameter by TemplateId
     */
    public function getParamByTempId($templateId)
    {
        return VtTemplateParameter::where('report_template_id', $templateId)->get();
    }
}
