<?php

namespace App\BLL;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * | Author-Anshu Kumar
 * | Created On-28-07-2023 
 * | Get Template Details by id
 * | Status-Closed
 */
class GenerateSearchReportBll
{
    private $_parameters;
    /**
     * | Generate
     * | @param request
     * | @param template 
     */
    public function generate($req, $template)
    {
        $perPage = $req->perPage ?? 10;
        $page = ($req->page ?? 1);
        $offset = ($page * $perPage) - $perPage;

        $customVars = collect();
        $this->_parameters = $template['parameters'];
        $linkName = array();
        foreach ($req->params as $item) {
            $item = (object)$item;
            $parameter = $this->_parameters->where('id', $item->id)->first();
            if (collect($parameter)->isEmpty())
                throw new Exception("Parameter Not Available");
            $linkName = $parameter->link_name;
            $customVars->put('$' . $linkName, "'" . $item->controlValue . "'");
        }
        $query = $template['templates']->detail_sql;
        foreach ($customVars as $key => $item) {
            $query = Str::replace($key, $item, $query);
        }

        $totalRecordSql = "select count(*) as count from ($query) total";
        $query = $query . " limit $perPage offset $offset";

        if (isset($template['templates']->module_id)) {
            if ($template['templates']->module_id == 1)                             // Property
            {
                $totalRecord = DB::connection('conn_juidco_prop')->select($totalRecordSql);
                $data["dataSet"] = DB::connection('conn_juidco_prop')->select($query);
            }
        } else {
            $totalRecord = DB::select($totalRecordSql);
            $data["dataSet"] = DB::select($query);
        }

        $data["totalRecords"] = (collect($totalRecord)->first()->count ?? 0);
        $data["totalPages"] = ceil($data["totalRecords"] / $perPage);                   // With round figured data
        $data["currentPage"] = $page;

        return $data;
    }
}
