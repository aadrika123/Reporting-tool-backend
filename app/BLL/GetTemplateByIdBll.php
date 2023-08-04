<?php

namespace App\BLL;

use App\Models\VtSearchGroup;
use App\Models\VtTemplate;
use App\Models\VtTemplateDeatil;
use App\Models\VtTemplateFooter;
use App\Models\VtTemplatePagelayout;
use App\Models\VtTemplateParameter;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * | Author-Anshu Kumar
 * | Created On-28-07-2023 
 * | Get Template Details by id
 * | Status-Closed
 */
class GetTemplateByIdBll
{
    private $_mVtSearchGroup;
    private $_mVtTemplates;
    private $_mVtTemplateDetails;
    private $_mVtTemplateFooters;
    private $_mVtTemplateParameters;
    private $_mVtTemplateLayout;
    private $_templateId;
    private array $_GRID;
    /**
     * | Initializing Variables
     */
    public function __construct()
    {
        $this->_mVtSearchGroup = new VtSearchGroup();
        $this->_mVtTemplates = new VtTemplate();
        $this->_mVtTemplateDetails = new VtTemplateDeatil();
        $this->_mVtTemplateFooters = new VtTemplateFooter();
        $this->_mVtTemplateParameters = new VtTemplateParameter();
        $this->_mVtTemplateLayout = new VtTemplatePagelayout();
    }

    /**
     * | Get Template 
     * | @param id TemplateID
     */
    public function getTemplate($id)
    {
        $this->_templateId = $id;
        $template = $this->_mVtTemplates::find($id);
        if (collect($template)->isEmpty())
            throw new Exception("Template Not Found");
        $this->_GRID['templates'] = $template;
        $searchGroup = $this->_mVtSearchGroup::find($template->search_group_id);

        if (collect($searchGroup)->isEmpty())
            throw new Exception("Search Group not available");

        if ($searchGroup->is_report)
            $this->readReportTbls();
        else
            $this->readParameterTbl();

        return $this->_GRID;
    }


    /**
     * | Read Report Tables
     */
    public function readReportTbls()
    {
        $details = $this->_mVtTemplateDetails::where('report_template_id', $this->_templateId)
            ->where('status', 1)
            ->get();
        $footers = $this->_mVtTemplateFooters::where('report_template_id', $this->_templateId)
            ->where('status', 1)
            ->get();
        $layouts = $this->_mVtTemplateLayout::where('report_template_id', $this->_templateId)
            ->where('status', 1)
            ->get();
        $this->_GRID['details'] = $details;
        $this->_GRID['footers'] = $footers;
        $this->_GRID['layouts'] = $layouts;
    }

    /**
     * | Read Parameter table
     */
    public function readParameterTbl()
    {
        $parameters = $this->_mVtTemplateParameters::where('report_template_id', $this->_templateId)
            ->where('status', 1)
            ->get();

        foreach ($parameters as $parameter) {
            if ($parameter->control_type == 'Combo') {              // If input field is of type select box or combo
                if (isset($parameter->source_sql)) {
                    $queryResult = DB::select($parameter->source_sql);
                    $parameter->queryResult = $queryResult ?? [];
                }
            }
        }

        $this->_GRID['parameters'] = $parameters;
    }
}
