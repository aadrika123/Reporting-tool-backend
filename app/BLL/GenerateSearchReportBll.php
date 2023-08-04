<?php

namespace App\BLL;

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
        $customVars = collect();
        $this->_parameters = $template['parameters'];
        $linkName = array();
        foreach ($req->params as $item) {
            $item = (object)$item;
            $parameter = $this->_parameters->where('id', $item->id)->first();
            $linkName = $parameter->link_name;
            $customVars->put('$' . $linkName, "'" . $item->controlValue . "'");
        }
        $query = $template['templates']->detail_sql;
        foreach ($customVars as $key => $item) {
            $query = Str::replace($key, $item, $query);
        }
        if (isset($template['templates']->module_id)) {
            if ($template['templates']->module_id == 1)                             // Property
                $queryResult = DB::connection('conn_juidco_prop')->select($query);
        } else
            $queryResult = DB::select($query);
        return $queryResult;
    }
}
