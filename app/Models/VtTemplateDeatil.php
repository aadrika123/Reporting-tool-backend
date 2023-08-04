<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VtTemplateDeatil extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function getDetailsByTempId($tempId)
    {
        return VtTemplateDeatil::where('report_template_id', $tempId)
            ->get();
    }
}
