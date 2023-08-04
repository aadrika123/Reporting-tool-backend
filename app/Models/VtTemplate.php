<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VtTemplate extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * | Get Ui Reports Only
     */
    public function getTemplateByType($isReport)
    {
        return DB::table('vt_templates as t')
            ->select('t.*', 'g.is_report')
            ->join('vt_search_groups as g', 'g.id', '=', 't.search_group_id')
            ->where('g.is_report', $isReport)
            ->get();
    }

    /**
     * | Edit Template By Id
     */
    public function editTemplateById($id, $reqs): void
    {
        VtTemplate::where('id', $id)
            ->update($reqs);
    }
}
