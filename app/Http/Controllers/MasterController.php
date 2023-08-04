<?php

namespace App\Http\Controllers;

use App\BLL\GetTemplateByIdBll;
use App\BLL\SaveTemplateBll;
use App\Repository\Api\MasterApiRepository as MasterApiRepository;
use App\Models\VtResource;
use App\Models\VtSearchGroup;
use App\Models\VtString;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests\Resource as ResourceRequest;
use App\Http\Requests\TemplateRequest;
use App\Models\ModuleMaster;
use App\Models\VtTemplate;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Redis;

/*******************************************************************************
 * Report tool api                                                              *                                                                            
 * Version: 1.0                                                                 *
 * Date:    2022-08-26                                                          *
 * Author:  Shashi Kumar Sharma                                                 *
 * ------------------------------------------------------------------------------
 * Version: 2.0
 * Controller No-01
 * Date: 2023-07-27
 * Author - Anshu Kumar
 * Status-Closed
 *******************************************************************************/

class MasterController extends Controller
{
    protected $mstr;
    public $pdfapi;
    public $layout;
    private $_mstr;

    public function __construct(MasterApiRepository $mstr)
    {
        $this->_mstr = $mstr;
    }
    /************** Resource Master Start **************/


    // For save data in vt_resources table
    public function resourceSave(ResourceRequest $resource)
    {

        try {
            $imagePath = "";
            $relativePath = "images/";
            if ($resource->image) {
                $imagePath = time() . '.' . $resource->image->extension();
                $resource->image->move(public_path($relativePath), $imagePath);
            }

            $vtres = new VtResource();
            $vtres->resource_name = $resource->resource_name;
            $vtres->image_path = $imagePath;
            $vtres->status = 1;
            $vtres->relative_path = $relativePath;
            $vtres->save();
            return responseMsgs(true, "Successfully Saved", [], "RP0101", "1.0", $resource->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "RP0101", "1.0", $resource->deviceId);
        }
    }

    // For update data in vt_resources table
    public function resourceUpdate(Request $resource)
    {
        $validator = Validator::make($resource->all(), [
            'id' => 'required|integer',
            'resource_name' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:1024'
        ]);

        if ($validator->fails()) {
            return validationError($validator);
        }
        try {
            $res = VtResource::find($resource->id);
            $relativePath = "images/";
            if ($res) {
                $imagePath = "";
                if ($resource->image) {
                    $imagePath = time() . '.' . $resource->image->extension();
                    $resource->image->move(public_path($relativePath), $imagePath);
                }

                $res->resource_name = $resource->resource_name;
                $res->image_path = $imagePath;
                $res->relative_path = $relativePath;
                $res->save();
                return responseMsgs(true, "Updated Successfully", [], "RP0102", "1.0", $resource->deviceId);
            } else
                throw new Exception("id not found");

            return responseMsgs(true, "Successfully Updated", [], "RP0102", "1.0", $resource->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "RP0102", "1.0", $resource->deviceId);
        }
    }

    // For get data according to the id in vt_resources table
    public function getresource(Request $resource)
    {
        try {
            $arr = array();
            $imgUrl = url('/');
            $baseQuery = DB::table('vt_resources')
                ->select("*")
                ->addSelect(DB::raw("concat('$imgUrl/',relative_path,image_path) as image_full_path"))
                ->where('status', 1);

            if ($resource->id)
                $arr = $baseQuery->where('id', $resource->id)->first();
            else
                $arr = $this->resourseList($baseQuery);                     // (1)

            return responseMsgs(true, "Fetched Data", remove_null($arr), "RP0103", "1.0", $resource->deviceId);
        } catch (Exception $e) {
            return responseMsgs(true, $e->getMessage(), [], "RP0103", "1.0", $resource->deviceId);
        }
    }

    // Resources List (1)
    public function resourseList($baseQuery)
    {
        $resources = Redis::get('vt_resources');
        if (isset($resources))
            $arr = json_decode($resources, true);
        else {
            $arr = $baseQuery->orderByDesc('id')->get();
            Redis::set('vt_resources', json_encode($arr));
        }
        return $arr;
    }

    // Deactivate Resource
    public function deactivateResource(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return validationError($validator);
        }

        try {
            $resource = VtResource::find($req->id);
            if (collect($resource)->isEmpty())
                throw new Exception("Resource not Found");
            $resource->update([
                'status' => 0
            ]);
            return responseMsgs(true, "Deleted Successfully", [], "RP0104", "1.0", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(true, $e->getMessage(), [], "RP0104", "1.0", $req->deviceId);
        }
    }

    /************** Resource Master End **************/

    /************** Search Group master Start **************/

    // For save data in vt_search_groups table
    public function saveGroup(Request $data)
    {
        $validator = Validator::make($data->all(), [
            'searchGroup' => 'required',
            'isReport' => 'required'
        ]);

        if ($validator->fails())
            return validationError($validator);

        try {

            $vtgrp = new VtSearchGroup();
            $vtgrp->search_group = $data->searchGroup;
            $vtgrp->is_report = ($data->isReport == 'Yes') ? true : false;
            $vtgrp->status = 1;
            $vtgrp->parent_id = isset($data->parentId) ? $data->parentId : null;
            $vtgrp->save();
            return responseMsgs(true, "Saved Successfully", [], "RP0105", "1.0", $data->deviceId);
        } catch (Exception $e) {
            return responseMsgs(true, $e->getMessage(), [], "RP0105", "1.0", $data->deviceId);
        }
    }

    // For update data in vt_search_groups table
    public function updateGroup(Request $data)
    {
        $validator = Validator::make($data->all(), [
            'id' => 'required|integer',
            'searchGroup' => 'required',
            'isReport' => 'required'
        ]);

        if ($validator->fails()) {
            return validationError($validator);
        }
        try {
            $res = VtSearchGroup::find($data->id);
            if ($res) {
                $res->search_group = $data->searchGroup;
                $res->is_report = ($data->isReport == 'Yes') ? true : false;
                $res->status = 1;
                $res->parent_id = isset($data->parentId) ? $data->parentId : null;
                $res->save();
                return responseMsgs(true, "Updated Successfully", [], "RP0106", "1.0", $data->deviceId);
            } else {
                throw new Exception("Id not Found");
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "RP0106", "1.0", $data->deviceId);
        }
    }

    // Get data for view and list in vt_search_groups table
    public function getGroup(Request $resource)
    {
        try {
            $arr = array();
            // Check id found form request
            if ($resource->id)
                $arr = DB::table('vt_search_groups')->where('id', $resource->id)->first(); // Particular single record based on id
            else
                $arr = $this->groupList();

            return responseMsgs(true, "Fetched Data", remove_null($arr), "RP0107", "1.0", $resource->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "RP0107", "1.0", $resource->deviceId);
        }
    }

    // Group List
    public function groupList()
    {
        $cachedList = Redis::get('vt_search_groups');
        if (isset($cachedList))
            $arr = json_decode($cachedList, true);
        else {
            $arr = DB::table('vt_search_groups')->where('status', 1)->orderByDesc('id')->get(); // All records from table
            Redis::set('vt_search_groups', json_encode($arr));
        }
        return $arr;
    }

    // Deactive group
    public function deactivateGroup(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return validationError($validator);
        }
        try {
            $resource = VtSearchGroup::find($req->id);
            if (collect($resource)->isEmpty())
                throw new Exception("Search Group not Found");
            $resource->update([
                'status' => 0
            ]);
            return responseMsgs(true, "Deleted Successfully", [], "RP0108", "1.0", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "RP0108", "1.0", $req->deviceId);
        }
    }

    /************** Search Group master End **************/


    /************** String master Start **************/

    // For save data in vt_strings table
    public function saveString(Request $data)
    {
        $validator = Validator::make($data->all(), [
            'fieldName' => 'required',
            'description' => 'required'
        ]);

        if ($validator->fails())
            return validationError($validator);

        try {
            $vtstr = new VtString();
            $vtstr->field_name = $data->fieldName;
            $vtstr->description = $data->description;
            $vtstr->status = 1;
            $vtstr->save();
            return responseMsgs(true, "Successfully Saved", [], "RP0109", "1.0", $data->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "RP0109", "1.0", $data->deviceId);
        }
    }

    // For update data in vt_strings table
    public function updateString(Request $data)
    {
        $validator = Validator::make($data->all(), [
            'id' => 'required|integer',
            'fieldName' => 'required',
            'description' => 'required'
        ]);

        if ($validator->fails())
            return validationError($validator);

        try {
            $res = VtString::find($data->id);
            if ($res) {
                $res->field_name = $data->fieldName;
                $res->description = $data->description;
                $res->save();
                return responseMsgs(true, "Successfully Updated", [], "RP0110", "1.0", $data->deviceId);
            } else {
                throw new Exception("No Id Available");
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "RP0110", "1.0", $data->deviceId);
        }
    }

    // Get data for view and list in vt_strings table
    public function getString(Request $resource)
    {
        try {
            $arr = array();
            // Check id found form request
            if ($resource->id)
                $arr = DB::table('vt_strings')->where('id', $resource->id)->first();        // Particular single record based on id
            else
                $arr = $this->stringList();

            return responseMsgs(true, "Fetched Data", remove_null($arr), "RP01011", "1.0", $resource->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "RP01011", "1.0", $resource->deviceId);
        }
    }

    // String Lists
    public function stringList()
    {
        $vtStrings = Redis::get('vt_strings');
        if (isset($vtStrings))
            $arr = json_decode($vtStrings, true);
        else {
            $arr = DB::table('vt_strings')->where('status', 1)->orderByDesc('id')->get();       // All records from table
            Redis::set('vt_strings', json_encode($arr));
        }
        return $arr;
    }

    // Deactivate String
    public function deactivateString(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return validationError($validator);
        }
        try {
            $resource = VtString::find($req->id);
            if (collect($resource)->isEmpty())
                throw new Exception("Search Group not Found");
            $resource->update([
                'status' => 0
            ]);
            return responseMsgs(true, "Deleted Successfully", [], "RP0112", "1.0", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "RP0112", "1.0", $req->deviceId);
        }
    }
    /************** String master End **************/

    /************** Create template Start **************/

    // For save data in vt_templates table
    public function saveTemplate(TemplateRequest $req)
    {
        try {
            $req->merge(['isUpdation' => false]);
            $saveTemplateBll = new SaveTemplateBll;
            $saveTemplateBll->_isPdfReport = $req->isPdfReport;
            $saveTemplateBll->store($req);
            return responseMsgs(true, "Successfully Saved the template", [], "RP0113", "1.0", $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "RP0113", "1.0", $req->deviceId);
        }
    }

    // For Update data in vt_templates table
    public function updateTemplate(TemplateRequest $req)
    {
        $validator = Validator::make($req->all(), [
            'template.id' => 'required|integer'
        ]);

        if ($validator->fails())
            return validationError($validator);

        try {
            $req->merge(['isUpdation' => true]);
            $saveTemplateBll = new SaveTemplateBll;
            $saveTemplateBll->_isPdfReport = $req->isPdfReport;
            $saveTemplateBll->store($req);
            return responseMsgs(true, "Successfully Updated the template", [], "RP0114", "1.0", $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "RP0114", "1.0", $req->deviceId);
        }
    }

    /************** Create template End **************/

    /************** View Template End ****************/
    public function getTemplateById(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|integer'
        ]);

        if ($validator->fails())
            return validationError($validator);

        try {
            $getTemplateByIdBll = new GetTemplateByIdBll;
            $template = $getTemplateByIdBll->getTemplate($req->id);
            return responseMsgs(true, "Template Details", remove_null($template), "RP0114", "1.0", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "RP0114", "1.0", $req->deviceId);
        }
    }
    /************** View Template End ****************/


    /************** Template Lists ****************** */
    public function templateList(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'reportType' => 'required|string|In:ui,print,all'
        ]);

        if ($validator->fails())
            return validationError($validator);

        try {
            $mTemplate = new VtTemplate();

            if ($req->reportType == 'ui')                                                 // UI Templates Only
                $template = $mTemplate->getTemplateByType(false);

            if ($req->reportType == 'print')                                              // Pritable Templates Only
                $template = $mTemplate->getTemplateByType(true);

            if ($req->reportType == 'all')                                              // All Templates
            {
                $templates = Redis::get('vt_templates');
                if (isset($templates)) {
                    $template = json_decode($templates, true);
                } else {
                    $template = $mTemplate::orderBy('id', 'desc')
                        ->get();
                    Redis::set('vt_templates', json_encode($template));                    // Redis key is deleting on Observer
                }
            }
            return responseMsgs(true, "Template Details", remove_null($template), "RP0115", "1.0", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "RP0115", "1.0", $req->deviceId);
        }
    }
    /************** Template Lists ****************** */

    public function MenuList()
    {
        try {
            $redisConn = Redis::connection();
            $menuarr = array();
            $cachedList = $redisConn->get('vt_search_groups');
            if (isset($cachedList))
                $grpList = json_decode($cachedList, true);
            else {
                $grpList = DB::table('vt_search_groups')
                    ->where('status', 1)
                    ->get();
                $redisConn->set('vt_search_groups', json_encode($grpList));
            }

            $arr = array();
            $subarr = array();
            $pid = 0;
            foreach ($grpList as $grp) { {
                    $grp = (object)$grp;
                    $pid = $grp->id;
                    $arr['menu_id'] = $grp->id;
                    $arr['menu_name'] = $grp->search_group;
                    $arr['type'] = 'Parent Group';
                    $arr['sub_menu'] = $this->ChildMenu($grp->id);
                    array_push($menuarr, $arr);
                }
            }

            return response($menuarr, 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function ChildMenu($parentmenu_id)
    {
        $menuarr = array();
        $tempList = DB::table('vt_templates')
            ->where('search_group_id', $parentmenu_id)
            ->get();

        foreach ($tempList as $temp) {
            $childarr['menu_id'] = $temp->id;
            $childarr['menu_name'] = $temp->template_name;
            $childarr['menu_code'] = $temp->template_code;
            $childarr['type'] = 'Child template';
            $submenu = array();
            if ($temp->detail_layout == 'General' || $temp->detail_layout == 'Form') {
                $childarr1['menu_id'] = 1;
                $childarr1['menu_name'] = 'Layout';
                $childarr1['menu_code'] = 'Layout';
                $childarr1['type'] = $temp->detail_layout . ' template';
                array_push($submenu, $childarr1);

                $childarr2['menu_id'] = 2;
                $childarr2['menu_name'] = 'Details';
                $childarr2['menu_code'] = 'Details';
                $childarr2['type'] = $temp->detail_layout . ' template';
                array_push($submenu, $childarr2);

                $childarr3['menu_id'] = 3;
                $childarr3['menu_name'] = 'Footer';
                $childarr3['menu_code'] = 'Footer';
                $childarr3['type'] = $temp->detail_layout . ' template';
                array_push($submenu, $childarr3);
            }

            if ($temp->detail_layout == 'Label' || $temp->detail_layout == 'Document') {
                $childarr1['menu_id'] = 1;
                $childarr1['menu_name'] = 'Layout';
                $childarr1['menu_code'] = 'Layout';
                $childarr1['type'] = $temp->detail_layout . ' template';
                array_push($submenu, $childarr1);
            }
            $childarr['submenu'] = $submenu;
            array_push($menuarr, $childarr);
        }
        //echo "<pre/>";print_r($menuarr);
        return $menuarr;
    }

    /**
     * | Get All Modules
     */
    public function moduleList(Request $req)
    {
        try {
            $mModuleMstr = new ModuleMaster();
            $modules = $mModuleMstr->moduleList();
            return responseMsgs(true, "Module List", remove_null($modules), "RP0118", "1.0", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "RP0118", "1.0", $req->deviceId);
        }
    }
}
