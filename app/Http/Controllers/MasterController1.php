<?php

namespace App\Http\Controllers;

use App\Repository\Api\MasterApiRepository as MasterApiRepository;
use App\Models\VtResource;
use App\Models\VtSearchGroup;
use App\Models\VtString;
use App\Models\VtTemplateDeatil;
use App\Models\VtTemplatePagelayout;
use App\Models\VtTemplateFooter;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests\Resource as ResourceRequest;
use App\Http\Requests\TemplateRequest;
use App\Models\VtTemplate;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use SebastianBergmann\Template\Template;

/*******************************************************************************
 * Report tool api                                                              *
 *                                                                              *
 * Version: 1.0                                                                 *
 * Date:    2022-08-26                                                          *
 * Author:  Shashi Kumar Sharma                                                 *
 *******************************************************************************/

class MasterController1 extends Controller
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
            return responseMsgs(true, "Successfully Saved", []);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), []);
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
                return responseMsgs(true, "Updated Successfully", []);
            } else
                throw new Exception("id not found");

            return responseMsgs(true, "Successfully Updated", []);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), []);
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
                $arr = $baseQuery
                    ->where('id', $resource->id)
                    ->first();
            else
                $arr = $baseQuery
                    ->orderByDesc('id')
                    ->get();

            return responseMsgs(true, "Fetched Data", remove_null($arr));
        } catch (Exception $e) {
            return responseMsgs(true, $e->getMessage(), []);
        }
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
            return responseMsgs(true, "Deleted Successfully", []);
        } catch (Exception $e) {
            return responseMsgs(true, $e->getMessage(), []);
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
            return response()->json(['status' => true, 'Message' => "Save successfully"], 200);
        } catch (Exception $e) {
            return response()->json([$e, 400]);
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
                return responseMsgs(true, "Updated Successfully", []);
            } else {
                throw new Exception("Id not Found");
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), []);
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
                $arr = DB::table('vt_search_groups')->where('status', 1)->orderByDesc('id')->get(); // All records from table

            return responseMsgs(true, "Fetched Data", remove_null($arr));
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), []);
        }
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
            return responseMsgs(true, "Deleted Successfully", []);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), []);
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
            return responseMsgs(true, "Successfully Saved", []);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), []);
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
                return responseMsgs(true, "Successfully Updated", []);
            } else {
                throw new Exception("No Id Available");
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), []);
        }
    }

    // Get data for view and list in vt_strings table
    public function getString(Request $resource)
    {
        try {
            $arr = array();
            // Check id found form request
            if ($resource->id)
                $arr = DB::table('vt_strings')->where('id', $resource->id)->first(); // Particular single record based on id
            else
                $arr = DB::table('vt_strings')->where('status', 1)->orderByDesc('id')->get(); // All records from table

            return responseMsgs(true, "Fetched Data", remove_null($arr));
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), []);
        }
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
            return responseMsgs(true, "Deleted Successfully", []);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), []);
        }
    }
    /************** String master End **************/

    /************** Create template Start **************/

    // For save data in vt_templates table
    public function saveTemplate(TemplateRequest $req)
    {
        try {
            $mVtTemplates = new VtTemplate();
            $templateReq = (object)$req->template;
            $metaReqs = [
                "search_group_id" => $templateReq->searchGroupId,
                "template_code" => $templateReq->templateCode,
                "template_name" => $templateReq->templateName,
                "paper_size_enum" => $templateReq->paperSizeEnum,
                "detail_layout" => $templateReq->detailLayout,
                "header_height" => $templateReq->headerHeight,
                "header_height_page2" => $templateReq->headerHeightPage2,
                "footer_height" => $templateReq->footerHeight,
                "detail_line_spacing" => $templateReq->detailLineSpacing,
                "layout_sql" => $templateReq->layoutSql,
                "detail_sql" => $templateReq->detailSql,
                "footer_sql" => $templateReq->footerSql,
                "is_default" => $templateReq->isDefault,
                "is_landscape" => $templateReq->isLandscape,
                "is_global_header" => $templateReq->isGlobalHeader,
                "is_render_global_header" => $templateReq->isRenderGlobalHeader,
                "is_page_layout_in_pager2" => $templateReq->isPageLayoutInPager2,
                "groupby_expression" => $templateReq->groupbyExpression,
                "is_show_grid_line" => $templateReq->isShowGridLine,
                "header_distance" => $templateReq->headerDistance,
                "screen_display_string" => $templateReq->screenDisplayString,
                "parent_id" => $templateReq->parentId,
                "label_row_count" => $templateReq->labelRowCount,
                "label_column_count" => $templateReq->labelColumnCount,
                "is_detail_wordwrap" => $templateReq->isDetailWordwrap,
                "is_compact_footer" => $templateReq->isCompactFooter,
            ];
            DB::beginTransaction();
            $createdTemplate = $mVtTemplates->create($metaReqs);
            $req->merge(['reportTemplateId' => $createdTemplate->id]);
            $this->saveTempPageLayouts($req);                   // Save Page Layouts
            $this->saveTempDetails($req);                       // Save Template Details
            $this->saveTempFooter($req);                        // Save Template Footer
            DB::commit();
            return responseMsgs(true, "Successfully Saved the template", []);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), []);
        }
    }

    // For update data in vt_strings table
    // public function updateTemplate(TemplateRequest $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'id' => 'required|integer',
    //     ]);

    //     if ($validator->fails())
    //         return validationError($validator);

    //     try {
    //         $mVtTemplates = new VtTemplate();
    //         $template = $mVtTemplates::find($req->id);
    //         $metaReqs = [
    //             "search_group_id" => $req->searchGroupId,
    //             "template_code" => $req->templateCode,
    //             "template_name" => $req->templateName,
    //             "paper_size_enum" => $req->paperSizeEnum,
    //             "detail_layout" => $req->detailLayout,
    //             "header_height" => $req->headerHeight,
    //             "header_height_page2" => $req->headerHeightPage2,
    //             "footer_height" => $req->footerHeight,
    //             "detail_line_spacing" => $req->detailLineSpacing,
    //             "layout_sql" => $req->layoutSql,
    //             "detail_sql" => $req->detailSql,
    //             "footer_sql" => $req->footerSql,
    //             "is_default" => $req->isDefault,
    //             "is_landscape" => $req->isLandscape,
    //             "is_global_header" => $req->isGlobalHeader,
    //             "is_render_global_header" => $req->isRenderGlobalHeader,
    //             "is_page_layout_in_pager2" => $req->isPageLayoutInPager2,
    //             "groupby_expression" => $req->groupbyExpression,
    //             "is_show_grid_line" => $req->isShowGridLine,
    //             "header_distance" => $req->headerDistance,
    //             "screen_display_string" => $req->screenDisplayString,
    //             "parent_id" => $req->parentId,
    //             "label_row_count" => $req->labelRowCount,
    //             "label_column_count" => $req->labelColumnCount,
    //             "is_detail_wordwrap" => $req->isDetailWordwrap,
    //             "is_compact_footer" => $req->isCompactFooter,
    //         ];
    //         $template->update($metaReqs);
    //         return responseMsgs(true, "Successfully Updated the template", []);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), []);
    //     }
    // }

    // //Get data for view and list in vt_templates table
    // public function getTemplate(Request $resource)
    // {
    //     try {
    //         // Check id found form request
    //         if ($resource->id) {
    //             $res = DB::table('vt_templates')->where('id', $resource->id)->first(); // Particular single record based on id
    //             if (collect($res)->isEmpty())
    //                 throw new Exception("Resourse Template Not Found");
    //         } else
    //             $res = DB::table('vt_templates')->where('status', 1)->orderByDesc('id')->get(); // All records from table

    //         return responseMsgs(true, "Fetched Templates", remove_null($res));
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), []);
    //     }
    // }
    /************** Create template End **************/

    /************** Template page layout Start **************/

    // For save data in vt_template_pagelayouts table
    public function saveTempPageLayouts(Request $datas)
    {
        $relativePath = "images/";
        foreach ($datas->layouts as $res) {
            $data = (object)$res;
            $imagePath = null;
            if (isset($data->file)) {
                $imagePath = "TL" . time() . $data->file->extension();
                file_put_contents(public_path($relativePath) . "/" . $imagePath, $data->file);
            }

            $temp_layout = new VtTemplatePagelayout();
            $temp_layout->report_template_id = $datas->reportTemplateId;
            $temp_layout->field_type = $data->fieldType;
            $temp_layout->caption = $data->caption;
            $temp_layout->field_name = $data->fieldName;
            $temp_layout->resource = $imagePath;
            $temp_layout->page_no = $data->pageNo;
            $temp_layout->x = $data->x;
            $temp_layout->y = $data->y;
            $temp_layout->width = $data->width;
            $temp_layout->height = $data->height;
            $temp_layout->font_name = $data->fontName;
            $temp_layout->font_size = $data->fontSize;
            $temp_layout->is_underline = $data->isUnderline;
            $temp_layout->is_bold = $data->isBold;
            $temp_layout->is_italic = $data->isItalic;
            $temp_layout->is_visible = $data->isVisible;
            $temp_layout->alignment = $data->alignment;
            $temp_layout->color = $data->color;
            $temp_layout->status = 1;
            $temp_layout->relative_path = $relativePath;
            $temp_layout->save();
        }
    }

    // For update data in vt_template_pagelayouts table
    // public function updateTempPageLayouts(Request $data)
    // {
    //     $validator = Validator::make($data->all(), [
    //         'reportTemplateId' => 'required',
    //         'fieldType' => 'required',
    //         'caption' => 'required',
    //         'fieldName' => 'required',
    //         'pageNo' => 'required',
    //         'x' => 'required',
    //         'y' => 'required',
    //         'width' => 'required',
    //         'height' => 'required',
    //         'fontName' => 'required',
    //         'fontSize' => 'required',
    //         'isUnderline' => 'required|bool',
    //         'isBold' => 'required|bool',
    //         'isItalic' => 'required|bool',
    //         'isVisible' => 'required|bool',
    //         'alignment' => 'required',
    //         'color' => 'required'
    //     ]);

    //     if ($validator->fails())
    //         return validationError($validator);

    //     try {
    //         $templayout = VtTemplatePagelayout::find($data->id);
    //         if (collect($templayout)->isEmpty())
    //             throw new Exception("Id Not Available");

    //         $templayout->report_template_id = $data->templateId;
    //         $templayout->field_type = $data->fieldType;
    //         $templayout->caption = $data->caption;
    //         $templayout->field_name = $data->fieldName;
    //         $templayout->resource = $data->resource;
    //         $templayout->page_no = $data->pageNo;
    //         $templayout->x = $data->x;
    //         $templayout->y = $data->y;
    //         $templayout->width = $data->width;
    //         $templayout->height = $data->height;
    //         $templayout->font_name = $data->fontName;
    //         $templayout->font_size = $data->fontSize;
    //         $templayout->is_underline = $data->isUnderline;
    //         $templayout->is_bold = $data->isBold;
    //         $templayout->is_italic = $data->isItalic;
    //         $templayout->is_visible = $data->isVisible;
    //         $templayout->alignment = $data->alignment;
    //         $templayout->save();

    //         return responseMsgs(true, "Successfully Updated the Record", []);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), []);
    //     }
    // }

    // //Get data for view and list in vt_template_pagelayouts table
    // public function getTempPageLayouts(Request $resource)
    // {
    //     $validator = Validator::make($resource->all(), [
    //         'id' => 'nullable|integer'
    //     ]);

    //     if ($validator->fails())
    //         return validationError($validator);

    //     try {
    //         $arr = array();
    //         // Check id found form request
    //         if ($resource->id) {
    //             $arr = DB::table('vt_template_pagelayouts')
    //                 ->where('id', $resource->id)
    //                 ->first(); // Particular single record based on id
    //         } else {
    //             $arr = DB::table('vt_template_pagelayouts')
    //                 ->where('status', 1)
    //                 ->orderByDesc('id')
    //                 ->get(); // All records from table
    //         }
    //         return responseMsgs(true, "Fetched Template Layouts", remove_null($arr));
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), []);
    //     }
    // }
    /************** Template page layout End **************/

    /************** Template details Start **************/

    // For save data in vt_template_deatils table
    public function saveTempDetails(Request $datas)
    {
        $temp_dtls = new VtTemplateDeatil();
        foreach ($datas->details as $res) {
            $data = (object)$res;
            $temp_dtls->report_template_id = $datas->reportTemplateId;
            $temp_dtls->x = $data->x;
            $temp_dtls->y = $data->y;
            $temp_dtls->field_type = $data->fieldType;
            $temp_dtls->field_name = $data->fieldName;
            $temp_dtls->font_name = $data->fontName;
            $temp_dtls->font_size = $data->fontSize;
            $temp_dtls->width = $data->width;
            $temp_dtls->is_underline = $data->isUnderline;
            $temp_dtls->is_bold = $data->isBold;
            $temp_dtls->is_italic = $data->isItalic;
            $temp_dtls->is_visible = $data->isVisible;
            $temp_dtls->is_boxed = $data->isBoxed;
            $temp_dtls->alignment = $data->alignment;
            $temp_dtls->color = $data->color;
            $temp_dtls->status = 1;
            $temp_dtls->save();
        }
    }

    // For update data in vt_template_deatils table
    // public function updateTempDetails(Request $data)
    // {
    //     $validator = Validator::make($data->all(), [
    //         'reportTemplateId' => 'required',
    //         'fieldType' => 'required',
    //         'x' => 'required',
    //         'y' => 'required',
    //         'width' => 'required',
    //         'fontName' => 'required',
    //         'fontSize' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['Message' => $validator->messages()]);
    //     }
    //     try {
    //         $temp_dtls = VtTemplateDeatil::find($data->id);
    //         if ($temp_dtls) {
    //             $temp_dtls->report_template_id = $data->templateId;
    //             $temp_dtls->x = $data->x;
    //             $temp_dtls->y = $data->y;
    //             $temp_dtls->field_type = $data->fieldType;
    //             $temp_dtls->field_name = $data->fieldName;
    //             $temp_dtls->font_name = $data->fontName;
    //             $temp_dtls->font_size = $data->fontSize;
    //             $temp_dtls->width = $data->width;
    //             $temp_dtls->is_underline = ($data->isUnderline == 'Yes') ? true : false;
    //             $temp_dtls->is_bold = ($data->isBold  == 'Yes') ? true : false;
    //             $temp_dtls->is_italic = ($data->isItalic  == 'Yes') ? true : false;
    //             $temp_dtls->is_visible = ($data->isVisible == 'Yes') ? true : false;
    //             $temp_dtls->is_boxed = ($data->isBoxed == 'Yes') ? true : false;
    //             $temp_dtls->alignment = $data->alignment;
    //             $temp_dtls->color = $data->color;
    //             $temp_dtls->save();

    //             return response()->json(['status' => true, 'Message' => "Updated successfully"], 200);
    //         } else {
    //             return response()->json('Id Not Found', 404);
    //         }
    //     } catch (Exception $e) {
    //         return response()->json([$e, 400]);
    //     }
    // }

    // //Get data for view and list in vt_template_deatils table
    // public function getTempDetails(Request $resource)
    // {
    //     try {
    //         $arr = array();
    //         // Check id found form request
    //         if ($resource->id) {
    //             $res = DB::table('vt_template_deatils')
    //                 ->where('id', $resource->id)
    //                 ->get(); // Particular single record based on id
    //         } else {
    //             $res = DB::table('vt_template_deatils')
    //                 ->where('status', 1)
    //                 ->orderByDesc('id')
    //                 ->get(); // All records from table
    //         }

    //         foreach ($res as $data) {

    //             $val['id'] = $data->id;
    //             $val['report_template_id'] = $data->report_template_id;
    //             $val['field_type'] = $data->field_type;
    //             $val['field_name'] = $data->field_name;
    //             $val['x'] = $data->x;
    //             $val['y'] = $data->y;
    //             $val['width'] = $data->width;
    //             $val['height'] = $data->height;
    //             $val['font_name'] = $data->font_name;
    //             $val['font_size'] = $data->font_size;
    //             $val['is_underline'] = $data->is_underline;
    //             $val['is_bold'] = $data->is_bold;
    //             $val['is_italic'] = $data->is_italic;
    //             $val['is_visible'] = $data->is_visible;
    //             $val['is_boxed'] = $data->is_boxed;
    //             $val['alignment'] = $data->alignment;
    //             $val['color'] = $data->color;
    //             $val['status'] = $data->status;
    //             array_push($arr, $val);
    //         }
    //         return response($arr, 200);
    //     } catch (Exception $e) {
    //         return response()->json($e, 400);
    //     }
    // }
    /************** Template Details End **************/

    /************** Template footer Start **************/

    // For save data in vt_template_footers table
    public function saveTempFooter(Request $datas)
    {
        foreach ($datas->footer as $res) {
            $data = (object)$res;
            $temp_footer = new VtTemplateFooter();
            $temp_footer->report_template_id = $datas->reportTemplateId;
            $temp_footer->serial_no = $data->serialNo;
            $temp_footer->field_type = $data->fieldType;
            $temp_footer->caption = $data->caption;
            $temp_footer->field_name = $data->fieldName;
            //$temp_footer->resource = $data->resource;
            $temp_footer->x = $data->x;
            $temp_footer->y = $data->y;
            $temp_footer->width = $data->width;
            $temp_footer->height = $data->height;
            $temp_footer->fontname = $data->fontname;
            $temp_footer->size = $data->size;
            $temp_footer->is_underline = $data->isUnderline;
            $temp_footer->is_bold = $data->isBold;
            $temp_footer->is_italic = $data->isItalic;
            $temp_footer->is_visible = $data->isVisible;
            $temp_footer->alignment = $data->alignment;
            $temp_footer->color = $data->color;
            $temp_footer->status = 1;
            $temp_footer->save();
        }
    }

    // For update data in vt_template_footers table
    // public function UpdateTempFooters(Request $data)
    // {
    //     $validator = Validator::make($data->all(), [
    //         'reportTemplate_id' => 'required',
    //         'fieldType' => 'required',
    //         'x' => 'required',
    //         'y' => 'required',
    //         'width' => 'required',
    //         'fontname' => 'required',
    //         'size' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['Message' => $validator->messages()]);
    //     }
    //     try {
    //         $temp_footer = VtTemplateFooter::find($data->id);
    //         if ($temp_footer) {
    //             $temp_footer->report_template_id = $data->templateId;
    //             $temp_footer->serial_no = $data->serialNo;
    //             $temp_footer->field_type = $data->fieldType;
    //             $temp_footer->caption = $data->caption;
    //             $temp_footer->field_name = $data->fieldName;
    //             $temp_footer->resource = $data->resource;
    //             $temp_footer->x = $data->x;
    //             $temp_footer->y = $data->y;
    //             $temp_footer->width = $data->width;
    //             $temp_footer->height = $data->height;
    //             $temp_footer->fontname = $data->fontname;
    //             $temp_footer->size = $data->size;
    //             $temp_footer->is_underline = ($data->isUnderline == 'Yes') ? true : false;
    //             $temp_footer->is_bold = ($data->isBold  == 'Yes') ? true : false;
    //             $temp_footer->is_italic = ($data->isItalic  == 'Yes') ? true : false;
    //             $temp_footer->is_visible = ($data->isVisible == 'Yes') ? true : false;
    //             $temp_footer->alignment = $data->alignment;
    //             $temp_footer->color = $data->color;
    //             $temp_footer->save();

    //             return response()->json(['status' => true, 'Message' => "Updated successfully"], 200);
    //         } else {
    //             return response()->json('Id Not Found', 404);
    //         }
    //     } catch (Exception $e) {
    //         return response()->json([$e, 400]);
    //     }
    // }

    // //Get data for view and list in vt_template_footers table
    // public function GetTempFooters(Request $resource)
    // {
    //     try {
    //         $arr = array();
    //         // Check id found form request
    //         if ($resource->id) {
    //             $res = DB::table('vt_template_footers')
    //                 ->where('id', $resource->id)
    //                 ->get(); // Particular single record based on id
    //         } else {
    //             $res = DB::table('vt_template_footers')
    //                 ->where('status', 1)
    //                 ->orderByDesc('id')
    //                 ->get(); // All records from table
    //         }

    //         foreach ($res as $data) {

    //             $val['id'] = $data->id;
    //             $val['report_template_id'] = $data->report_template_id;
    //             $val['serial_no'] = $data->serial_no;
    //             $val['field_type'] = $data->field_type;
    //             $val['caption'] = $data->caption;
    //             $val['field_name'] = $data->field_name;
    //             $val['resource'] = $data->resource;
    //             $val['x'] = $data->x;
    //             $val['y'] = $data->y;
    //             $val['width'] = $data->width;
    //             $val['height'] = $data->height;
    //             $val['fontname'] = $data->fontname;
    //             $val['size'] = $data->size;
    //             $val['is_underline'] = $data->is_underline;
    //             $val['is_bold'] = $data->is_bold;
    //             $val['is_italic'] = $data->is_italic;
    //             $val['is_visible'] = $data->is_visible;
    //             $val['alignment'] = $data->alignment;
    //             $val['color'] = $data->color;
    //             $val['status'] = $data->status;
    //             array_push($arr, $val);
    //         }
    //         return response($arr, 200);
    //     } catch (Exception $e) {
    //         return response()->json($e, 400);
    //     }
    // }
    /************** Template Footers End **************/

    public function MenuList()
    {
        try {
            $menuarr = array();

            $grpList = DB::table('vt_search_groups')
                ->where('status', 1)
                ->get();
            $arr = array();
            $subarr = array();
            $pid = 0;
            foreach ($grpList as $grp) {
                //if((empty($grp->parent_id) || $grp->parent_id == 0))
                {
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
            return response()->json($e, 400);
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
}
