<?php

namespace App\BLL;

use App\Models\VtSearchGroup;
use App\Models\VtTemplate;
use App\Models\VtTemplateDeatil;
use App\Models\VtTemplateFooter;
use App\Models\VtTemplatePagelayout;
use App\Models\VtTemplateParameter;
use Illuminate\Support\Facades\DB;

/**
 * | Version-1.0 
 * | Save Templates Bll 
 * | Author - Anshu Kumar
 * | Created On-27-07-2023 
 * | Status-Closed
 */

class SaveTemplateBll
{
    private $_mVtTemplates;
    private $_mTempLayout;
    private $_mTempDtls;
    private $_mTempFooter;
    private $_mTempParameters;
    public $_mVtSearchGroups;
    public $_isPdfReport;
    /**
     * | Initialization of Variables
     */
    public function __construct()
    {
        $this->_mVtTemplates = new VtTemplate();
        $this->_mTempLayout = new VtTemplatePagelayout();
        $this->_mTempDtls = new VtTemplateDeatil();
        $this->_mTempFooter = new VtTemplateFooter();
        $this->_mVtSearchGroups = new VtSearchGroup();
        $this->_mTempParameters = new VtTemplateParameter();
    }

    /**
     * | Save 
     */
    public function store($req)
    {
        $templateReq = (object)$req->template;
        $metaReqs = [
            "search_group_id" => $templateReq->searchGroupId ?? null,
            "template_code" => $templateReq->templateCode ?? null,
            "template_name" => $templateReq->templateName ?? null,
            "paper_size_enum" => $templateReq->paperSizeEnum ?? null,
            "detail_layout" => $templateReq->detailLayout ?? null,
            "header_height" => $templateReq->headerHeight ?? null,
            "header_height_page2" => $templateReq->headerHeightPage2 ?? null,
            "footer_height" => $templateReq->footerHeight ?? null,
            "detail_line_spacing" => $templateReq->detailLineSpacing ?? null,
            "layout_sql" => $templateReq->layoutSql ?? null,
            "detail_sql" => $templateReq->detailSql ?? null,
            "footer_sql" => $templateReq->footerSql ?? null,
            "is_default" => $templateReq->isDefault ?? null,
            "is_landscape" => $templateReq->isLandscape ?? null,
            "is_global_header" => $templateReq->isGlobalHeader ?? null,
            "is_render_global_header" => $templateReq->isRenderGlobalHeader ?? null,
            "is_page_layout_in_pager2" => $templateReq->isPageLayoutInPager2 ?? null,
            "groupby_expression" => $templateReq->groupbyExpression ?? null,
            "is_show_grid_line" => $templateReq->isShowGridLine ?? null,
            "header_distance" => $templateReq->headerDistance ?? null,
            "screen_display_string" => $templateReq->screenDisplayString ?? null,
            "parent_id" => $templateReq->parentId ?? null,
            "label_row_count" => $templateReq->labelRowCount ?? null,
            "label_column_count" => $templateReq->labelColumnCount ?? null,
            "is_detail_wordwrap" => $templateReq->isDetailWordwrap ?? null,
            "is_compact_footer" => $templateReq->isCompactFooter ?? null,
            "module_id" => $templateReq->moduleId ?? null,
        ];
        DB::beginTransaction();
        if ($req->isUpdation == true) {                                         // In Case Of Updation
            $reportTemplateId = $templateReq->id;
            $this->_mVtTemplates->editTemplateById($reportTemplateId, $metaReqs);
        }
        if ($req->isUpdation == false) {                                        // In Case of New Store
            $createdTemplate = $this->_mVtTemplates->create($metaReqs);
            $reportTemplateId = $createdTemplate->id;
        }

        $req->merge(['reportTemplateId' => $reportTemplateId]);
        if ($this->_isPdfReport == true) {                          // for Pdf Reports
            if ($req->isUpdation) {                                 // Updation
                $this->updateTempPageLayouts($req);                       // Save Page Layouts
                $this->updateTempDetails($req);                           // Save Template Details
                $this->updateTempFooter($req);                            // Save Template Footer
            }

            if ($req->isUpdation == false) {
                $this->saveTempPageLayouts($req);                       // Save Page Layouts
                $this->saveTempDetails($req);                           // Save Template Details
                $this->saveTempFooter($req);                            // Save Template Footer
            }
        }

        if ($this->_isPdfReport == false)                           // For non printable reports
        {
            if ($req->isUpdation)
                $this->updateTempParameters($req);                  // Updation
            else
                $this->saveTempParameters($req);
        }
        DB::commit();
    }

    /************** Template page layout Start **************/

    // For save data in vt_template_pagelayouts table
    public function saveTempPageLayouts($datas)
    {
        $relativePath = "images/";
        foreach ($datas->layouts as $res) {
            $data = (object)$res;
            $imagePath = null;
            if (isset($data->file)) {
                $imagePath = "TL" . time() . $data->file->extension();
                file_put_contents(public_path($relativePath) . "/" . $imagePath, $data->file);
            }

            $this->_mTempLayout->report_template_id = $datas->reportTemplateId;
            $this->_mTempLayout->field_type = $data->fieldType;
            $this->_mTempLayout->caption = $data->caption;
            $this->_mTempLayout->field_name = $data->fieldName;
            $this->_mTempLayout->resource = $imagePath;
            $this->_mTempLayout->page_no = $data->pageNo;
            $this->_mTempLayout->x = $data->x;
            $this->_mTempLayout->y = $data->y;
            $this->_mTempLayout->width = $data->width;
            $this->_mTempLayout->height = $data->height;
            $this->_mTempLayout->font_name = $data->fontName;
            $this->_mTempLayout->font_size = $data->fontSize;
            $this->_mTempLayout->is_underline = $data->isUnderline;
            $this->_mTempLayout->is_bold = $data->isBold;
            $this->_mTempLayout->is_italic = $data->isItalic;
            $this->_mTempLayout->is_visible = $data->isVisible;
            $this->_mTempLayout->alignment = $data->alignment;
            $this->_mTempLayout->color = $data->color;
            $this->_mTempLayout->status = 1;
            $this->_mTempLayout->relative_path = $relativePath;
            $this->_mTempLayout->save();
        }
    }
    /************** Template page layout End **************/

    /************** Template details Start **************/

    // For save data in vt_template_deatils table
    public function saveTempDetails($datas)
    {
        foreach ($datas->details as $res) {
            $data = (object)$res;
            $this->_mTempDtls->report_template_id = $datas->reportTemplateId;
            $this->_mTempDtls->x = $data->x;
            $this->_mTempDtls->y = $data->y;
            $this->_mTempDtls->field_type = $data->fieldType;
            $this->_mTempDtls->field_name = $data->fieldName;
            $this->_mTempDtls->font_name = $data->fontName;
            $this->_mTempDtls->font_size = $data->fontSize;
            $this->_mTempDtls->width = $data->width;
            $this->_mTempDtls->is_underline = $data->isUnderline;
            $this->_mTempDtls->is_bold = $data->isBold;
            $this->_mTempDtls->is_italic = $data->isItalic;
            $this->_mTempDtls->is_visible = $data->isVisible;
            $this->_mTempDtls->is_boxed = $data->isBoxed;
            $this->_mTempDtls->alignment = $data->alignment;
            $this->_mTempDtls->color = $data->color;
            $this->_mTempDtls->status = 1;
            $this->_mTempDtls->save();
        }
    }

    /************** Template footer Start **************/

    // For save data in vt_template_footers table
    public function saveTempFooter($datas)
    {
        foreach ($datas->footer as $res) {
            $data = (object)$res;
            $this->_mTempFooter->report_template_id = $datas->reportTemplateId;
            $this->_mTempFooter->serial_no = $data->serialNo;
            $this->_mTempFooter->field_type = $data->fieldType;
            $this->_mTempFooter->caption = $data->caption;
            $this->_mTempFooter->field_name = $data->fieldName;
            //$this->_mTempFooter->resource = $data->resource;
            $this->_mTempFooter->x = $data->x;
            $this->_mTempFooter->y = $data->y;
            $this->_mTempFooter->width = $data->width;
            $this->_mTempFooter->height = $data->height;
            $this->_mTempFooter->fontname = $data->fontname;
            $this->_mTempFooter->size = $data->size;
            $this->_mTempFooter->is_underline = $data->isUnderline;
            $this->_mTempFooter->is_bold = $data->isBold;
            $this->_mTempFooter->is_italic = $data->isItalic;
            $this->_mTempFooter->is_visible = $data->isVisible;
            $this->_mTempFooter->alignment = $data->alignment;
            $this->_mTempFooter->color = $data->color;
            $this->_mTempFooter->status = 1;
            $this->_mTempFooter->save();
        }
    }

    // ╔═══════════════════════════════════════════════════════════════════════════╗
    // ║            ✅ Template Layout,Details,Footer Updation ✅                 ║ 
    // ╚═══════════════════════════════════════════════════════════════════════════╝ 

    // Update Template Layout
    public function updateTempPageLayouts($req)
    {
        $getTblLayouts = $this->_mTempLayout->getLayoutByTempId($req->template['id']);
        $reqLayouts = collect($req->layouts);
        $toUpdateIds = $reqLayouts->pluck('id');
        $toBeDeletedLayouts = $getTblLayouts->whereNotIn('id', $toUpdateIds);

        if (collect($toBeDeletedLayouts)->isNotEmpty()) {               // To Delete Layouts
            foreach ($toBeDeletedLayouts as $item) {
                $item->update(['status' => 0]);
            }
        }

        foreach ($toUpdateIds as $id) {
            $item = $reqLayouts->where('id', $id)->first();
            if (collect($item)->isNotEmpty()) {
                $item = (object)$item;
                $updateItem = $getTblLayouts->where('id', $id)->first();
                if ($updateItem) {
                    $updateItem->update([
                        "report_template_id" => $req->reportTemplateId,
                        "field_type" => $item->fieldType ?? null,
                        "caption" => $item->caption ?? null,
                        "field_name" => $item->fieldName ?? null,
                        "page_no" => $item->pageNo ?? null,
                        "x" => $item->x ?? null,
                        "y" => $item->y ?? null,
                        "width" => $item->width ?? null,
                        "height" => $item->height ?? null,
                        "font_name" => $item->fontName ?? null,
                        "font_size" => $item->fontSize ?? null,
                        "is_underline" => $item->isUnderline ?? null,
                        "is_bold" => $item->isBold ?? null,
                        "is_italic" => $item->isItalic ?? null,
                        "is_visible" => $item->isVisible ?? null,
                        "alignment" => $item->alignment ?? null,
                        "color" => $item->color ?? null,
                        "status" => 1,
                        "relative_path" => $item->relativePath ?? null,
                    ]);
                } else {
                    $this->_mTempLayout::create([
                        "report_template_id" => $req->reportTemplateId,
                        "field_type" => $item->fieldType ?? null,
                        "caption" => $item->caption ?? null,
                        "field_name" => $item->fieldName ?? null,
                        "page_no" => $item->pageNo ?? null,
                        "x" => $item->x ?? null,
                        "y" => $item->y ?? null,
                        "width" => $item->width ?? null,
                        "height" => $item->height ?? null,
                        "font_name" => $item->fontName ?? null,
                        "font_size" => $item->fontSize ?? null,
                        "is_underline" => $item->isUnderline ?? null,
                        "is_bold" => $item->isBold ?? null,
                        "is_italic" => $item->isItalic ?? null,
                        "is_visible" => $item->isVisible ?? null,
                        "alignment" => $item->alignment ?? null,
                        "color" => $item->color ?? null,
                        "status" => 1,
                        "relative_path" => $item->relativePath ?? null,
                    ]);
                }
            }
        }
    }

    // Update Template Details
    public function updateTempDetails($req)
    {
        $getTblDetails = $this->_mTempDtls->getDetailsByTempId($req->template['id']);
        $reqDetails = collect($req->details);
        $toUpdateIds = $reqDetails->pluck('id');
        $toBeDeletedDetails = $getTblDetails->whereNotIn('id', $toUpdateIds);

        if (collect($toBeDeletedDetails)->isNotEmpty()) {                   // To Delete Template Details
            foreach ($toBeDeletedDetails as $item) {
                $item->update(['status' => 0]);
            }
        }

        foreach ($toUpdateIds as $id) {
            $item = $reqDetails->where('id', $id)->first();
            if (collect($item)->isNotEmpty()) {
                $item = (object)$item;
                $updateItem = $getTblDetails->where('id', $id)->first();
                if ($updateItem) {
                    $updateItem->update([
                        "report_template_id" => $req->reportTemplateId,
                        "x" => $item->x,
                        "y" => $item->y,
                        "field_type" => $item->fieldType,
                        "field_name" => $item->fieldName,
                        "font_name" => $item->fontName,
                        "font_size" => $item->fontSize,
                        "width" => $item->width,
                        "is_underline" => $item->isUnderline,
                        "is_bold" => $item->isBold,
                        "is_italic" => $item->isItalic,
                        "is_visible" => $item->isVisible,
                        "is_boxed" => $item->isBoxed,
                        "alignment" => $item->alignment,
                        "color" => $item->color,
                        "status" => 1,
                    ]);
                } else {
                    $this->_mTempDtls::create([
                        "report_template_id" => $req->reportTemplateId,
                        "x" => $item->x,
                        "y" => $item->y,
                        "field_type" => $item->fieldType,
                        "field_name" => $item->fieldName,
                        "font_name" => $item->fontName,
                        "font_size" => $item->fontSize,
                        "width" => $item->width,
                        "is_underline" => $item->isUnderline,
                        "is_bold" => $item->isBold,
                        "is_italic" => $item->isItalic,
                        "is_visible" => $item->isVisible,
                        "is_boxed" => $item->isBoxed,
                        "alignment" => $item->alignment,
                        "color" => $item->color,
                    ]);
                }
            }
        }
    }

    // Update Template Footer
    public function updateTempFooter($req)
    {
        $getTblFooter = $this->_mTempFooter->getFooterByTempId($req->template['id']);
        $reqFooter = collect($req->footer);
        $toUpdateIds = $reqFooter->pluck('id');
        $toBeDeletedFooters = $getTblFooter->whereNotIn('id', $toUpdateIds);

        if (collect($toBeDeletedFooters)->isNotEmpty()) {                   // To Delete Template Details
            foreach ($toBeDeletedFooters as $item) {
                $item->update(['status' => 0]);
            }
        }

        foreach ($toUpdateIds as $id) {
            $item = $reqFooter->where('id', $id)->first();
            if (collect($item)->isNotEmpty()) {
                $item = (object)$item;
                $updateItem = $getTblFooter->where('id', $id)->first();
                if ($updateItem) {
                    $updateItem->update([
                        "report_template_id" => $req->reportTemplateId,
                        "serial_no" => $item->serialNo,
                        "field_type" => $item->fieldType,
                        "caption" => $item->caption,
                        "field_name" => $item->fieldName,
                        "x" => $item->resource,
                        "y" => $item->x,
                        "width" => $item->y,
                        "height" => $item->width,
                        "fontname" => $item->height,
                        "size" => $item->fontname,
                        "is_underline" => $item->size,
                        "is_bold" => $item->isUnderline,
                        "is_italic" => $item->isBold,
                        "is_visible" => $item->isItalic,
                        "alignment" => $item->isVisible,
                        "color" => $item->alignment,
                        "status" => $item->color,
                    ]);
                } else {
                    $this->_mTempFooter::create([
                        "report_template_id" => $req->reportTemplateId,
                        "serial_no" => $item->serialNo,
                        "field_type" => $item->fieldType,
                        "caption" => $item->caption,
                        "field_name" => $item->fieldName,
                        "x" => $item->resource,
                        "y" => $item->x,
                        "width" => $item->y,
                        "height" => $item->width,
                        "fontname" => $item->height,
                        "size" => $item->fontname,
                        "is_underline" => $item->size,
                        "is_bold" => $item->isUnderline,
                        "is_italic" => $item->isBold,
                        "is_visible" => $item->isItalic,
                        "alignment" => $item->isVisible,
                        "color" => $item->alignment,
                        "status" => $item->color,
                    ]);
                }
            }
        }
    }
    /************** Template Footers End **************/

    /************** Template Parameters for no pdf reports ********** */
    public function saveTempParameters($req)
    {
        $parameters = $req->parameters;
        foreach ($parameters as $item) {
            $item = (object)$item;
            $arrayReq = [
                "report_template_id" => $req->reportTemplateId,
                "serial" => $item->serial,
                "control_name" => $item->controlName,
                "display_string" => $item->displayString,
                "control_type" =>  $item->controlType,
                "link_name" =>  $item->linkName,
                "source_sql" =>  $item->sourceSql,
                "bound_column" =>  $item->boundColumn,
                "display_column" =>  $item->displayColumn,
                "dependency_control_code" =>  $item->dependencyControlCode,
            ];
            $this->_mTempParameters->create($arrayReq);
        }
    }

    /********************** Update Template Parameters *********************************** */

    public function updateTempParameters($req)
    {
        $getTblParameters = $this->_mTempParameters->getParamByTempId($req->template['id']);
        $reqParameters = collect($req->parameters);
        $toUpdateIds = $reqParameters->pluck('id');
        $toBeDeletedParams = $getTblParameters->whereNotIn('id', $toUpdateIds);

        if ($toBeDeletedParams->isNotEmpty()) {                         // To Deleting Columns
            foreach ($toBeDeletedParams as $item) {
                $item->update(['status' => 0]);
            }
        }

        foreach ($toUpdateIds as $id) {                                 // Create Or Update Column
            $item = $reqParameters->where('id', $id)->first();
            if (collect($item)->isNotEmpty()) {
                $item = (object)$item;
                $updateItem = $getTblParameters->where('id', $id)->first();
                if ($updateItem) {
                    $updateItem->Update(
                        [
                            "report_template_id" => $req->reportTemplateId,
                            "serial" => $item->serial,
                            "control_name" => $item->controlName,
                            "display_string" => $item->displayString,
                            "control_type" =>  $item->controlType,
                            "link_name" =>  $item->linkName,
                            "source_sql" =>  $item->sourceSql,
                            "bound_column" =>  $item->boundColumn,
                            "display_column" =>  $item->displayColumn,
                            "dependency_control_code" =>  $item->dependencyControlCode,
                            "status" => 1
                        ]
                    );
                } else {
                    $this->_mTempParameters::create(
                        [
                            "report_template_id" => $req->reportTemplateId,
                            "serial" => $item->serial,
                            "control_name" => $item->controlName,
                            "display_string" => $item->displayString,
                            "control_type" =>  $item->controlType,
                            "link_name" =>  $item->linkName,
                            "source_sql" =>  $item->sourceSql,
                            "bound_column" =>  $item->boundColumn,
                            "display_column" =>  $item->displayColumn,
                            "dependency_control_code" =>  $item->dependencyControlCode
                        ]
                    );
                }
            }
        }
    }
    /************** Template Parameters for no pdf reports ********** */
}
