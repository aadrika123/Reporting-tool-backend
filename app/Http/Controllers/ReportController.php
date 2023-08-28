<?php

namespace App\Http\Controllers;

use App\BLL\GenerateReportBll;
use App\BLL\GenerateSearchReportBll;
use App\BLL\GetTemplateByIdBll;
use App\Models\VtSearchGroup;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * | Author - Anshu Kumar
 * | Created On-21-07-2023 
 * | Creation for - Report Creation 
 * | Version-1.0 
 * | Status-Closed
 */

class ReportController extends Controller
{
    private $_generateReportBll;
    public function __construct()
    {
        $this->_generateReportBll = new GenerateReportBll;
    }

    //
    public function generateReport(Request $req)
    {
        try {
            $mVtSearchGroup = new VtSearchGroup();
            $searchGroup = $mVtSearchGroup::find($req->template['searchGroupId']);
            if (collect($searchGroup)->isEmpty())
                throw new Exception("Search Group not Available");
            // preview Only available for pdf reports
            if ($searchGroup->is_report == true) {
                $response = $this->_generateReportBll->createReport($req);
                return response($response, 200, [
                    'Content-type'        => 'application/pdf',
                ]);
            } else
                throw new Exception("Preview Not Available for the Search Reports");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "RP0201", "1.0", $req->deviceId);
        }
    }

    /**
     * | Get Query Result
     */
    public function queryResult(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'requestQuery' => 'required|string',
            'moduleId' => 'nullable|integer',
            'perPage' => 'nullable|integer',
            'page' => 'nullable|integer'
        ]);

        if ($validator->fails())
            return validationError($validator);

        try {
            $perPage = $req->perPage ?? 10;
            $page = ($req->page ?? 1);
            $offset = ($page * $perPage) - $perPage;

            $query = $req->requestQuery;
            $lowerQuery = Str::lower($query);
            $strQuery = Str::contains($lowerQuery, ['update', 'delete', 'insert']);
            if ($strQuery)
                throw new Exception("Unauthorized Query");
            $strQuery = Str::contains($lowerQuery, ['limit']);
            if ($strQuery)
                throw new Exception("Limit has been set default dont mention it on query");

            $totalRecordSql = "select count(*) as count from ($query) total";
            $query = $query . " limit $perPage offset $offset";
            $data = [];

            if (isset($req->moduleId) && $req->moduleId == 1) {                 // For Property Module
                $totalRecord = DB::connection('conn_juidco_prop')->select($totalRecordSql);
                $data["dataSet"] = DB::connection('conn_juidco_prop')->select($query);
            } else {                                                            // for others
                $totalRecord = DB::select($query);
                $data["dataSet"] = DB::select($query);
            }

            $data["totalRecords"] = (collect($totalRecord)->first()->count ?? 0);
            $data["totalPages"] = ceil($data["totalRecords"] / $perPage);                   // With round figured data
            $data["currentPage"] = $page;

            return responseMsgs(true, "Query Result", remove_null($data), "RP0202", "1.0", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "RP0202", "1.0", $req->deviceId);
        }
    }

    /**
     * | Generate Search Type Reports
     */
    public function generateSearchReport(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|integer'
        ]);

        if ($validator->fails())
            return validationError($validator);

        try {
            $getTemplateByIdBll = new GetTemplateByIdBll;
            $generateSearchReportBll = new GenerateSearchReportBll;
            $template = $getTemplateByIdBll->getTemplate($req->id);
            $response = $generateSearchReportBll->generate($req, $template);
            return responseMsgs(true, "Template Details", remove_null($response), "RP0203", "1.0", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "RP0203", "1.0", $req->deviceId);
        }
    }
}
