<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VtTemplatePagelayout extends Model
{
    use HasFactory;
    protected $guarded = [];


    /**
     * | Get Templates by Template Id
     */
    public function getLayoutByTempId($tempId)
    {
        return VtTemplatePagelayout::where('report_template_id', $tempId)
            ->get();
    }
}
