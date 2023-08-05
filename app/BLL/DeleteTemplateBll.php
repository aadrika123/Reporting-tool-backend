<?php

namespace App\BLL;

use App\Models\VtTemplateDeatil;
use App\Models\VtTemplateFooter;
use App\Models\VtTemplatePagelayout;
use App\Models\VtTemplateParameter;
use Illuminate\Support\Facades\DB;

/**
 * | Author - Anshu Kumar
 * | Created On-05-08-2023 
 * | Created for the Delete templates by its id
 * | Status-Closed
 */

class DeleteTemplateBll
{
    private $_isReport;
    private $_templateId;
    private $_template;
    private $_mVtTemplateDetails;
    private $_mVtTemplateLayouts;
    private $_mVtTemplateFooter;
    private $_mVtTemplateParameters;

    public function __construct()
    {
        $this->_mVtTemplateDetails = new VtTemplateDeatil();
        $this->_mVtTemplateLayouts = new VtTemplatePagelayout();
        $this->_mVtTemplateFooter = new VtTemplateFooter();
        $this->_mVtTemplateParameters = new VtTemplateParameter();
    }
    /**
     * | Delete Template(01)
     * | @param isReport type of report pdf or ui
     * | @param templateId 
     */
    public function deleteTemplate($isReport, $template)
    {
        $this->_template = $template;
        $this->_isReport = $isReport;
        $this->_templateId = $template->id;

        $this->deleteDLF();             // delete template Details, Layouts and footers for pdf report(1.1)

        $this->deleteParameter();       // delete parameter in case of ui report(1.2)

        $template->delete();
    }


    /**
     * | Serial - 1.1
     * | Delete template details layouts and footer for pdf type reports
     */
    public function deleteDLF()
    {
        if ($this->_isReport == true) {
            $readTemplateDetails = $this->_mVtTemplateDetails->getDetailsByTempId($this->_templateId);
            $readTemplateLayouts = $this->_mVtTemplateLayouts->getLayoutByTempId($this->_templateId);
            $readTemplateFooter = $this->_mVtTemplateFooter->getFooterByTempId($this->_templateId);

            DB::beginTransaction();

            if ($readTemplateDetails)
                $readTemplateDetails->each->delete();
            if ($readTemplateLayouts)
                $readTemplateLayouts->each->delete();
            if ($readTemplateFooter)
                $readTemplateFooter->each->delete();
        }
    }

    /**
     * | Serial - 1.2
     * | Delete parameter for non pdf or ui type reports
     */
    public function deleteParameter()
    {
        if ($this->_isReport == false) {
            $readTemplateParams = $this->_mVtTemplateParameters->getParamByTempId($this->_templateId);
            DB::beginTransaction();
            if ($readTemplateParams)
                $readTemplateParams->each->delete();
        }
    }
}
