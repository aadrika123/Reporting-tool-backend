<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VtTemplateFooter extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * | get Template footer by template id
     */
    public function getFooterByTempId($tempId)
    {
        return VtTemplateFooter::where('report_template_id', $tempId)
            ->get();
    }
}
