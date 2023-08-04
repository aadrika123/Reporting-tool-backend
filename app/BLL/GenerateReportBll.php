<?php

namespace App\BLL;

use App\Traits\Api\PdfHelpers;
use Codedge\Fpdf\Fpdf\Fpdf;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * | Author-Anshu Kumar
 * | Created on-21-07-2023 
 * | Created for Reports layout previews
 * | Status-Closed
 */

class GenerateReportBll
{
    use PdfHelpers;
    private $_fPdf;
    private $_REQUEST;
    private array $_dtlQueryResult;
    private $_readLineItems;
    // Initialization
    public function __construct()
    {
        $this->_fPdf = new Fpdf();
    }

    // Create Report
    public function createReport($req)
    {
        $this->_REQUEST = $req;
        // Check template look is default or according to the tempalte attributs for styling
        if ($req->template['isDefault'] == false) {
            $ori = ($req->template['isLandscape'] == true) ? 'L' : 'P';
            $size = ($req->template['paperSizeEnum']) ? $req->template['paperSizeEnum'] : '210,297';
            $this->_fPdf = new Fpdf($ori, 'mm', explode(',', $size));
            $this->_fPdf->Ln($req->template['footerHeight']);
        }

        $this->_fPdf->AliasNbPages();
        $this->_fPdf->AddPage();

        $this->generateLayout($req);                    // (1.1) == Generate Layouts
        $this->generateDetails($req);                   // (1.2) == Generate Details
        $this->generateFooter($req);                    // (1.3) == Generate Footers

        $response = $this->_fPdf->Output();
        return $response;
    }

    /**
     * |
     *  +-----------------------+
     * |  Layout!   |
     * +-----------------------+
     * |
     */
    // Layout Generation  (1.1)
    public function generateLayout($req)
    {
        if (isset($req->layouts)) {                                         // Layout 
            foreach ($req->layouts as $layout) {
                $layout = (object)$layout;
                $this->readFieldType($layout);
            }
        }
    }

    /**
     * | Read Field Types
     */
    public function readFieldType($item)
    {
        if ($item->fieldType == 'caption')                       // Caption
            $this->generateCaption($item);
        elseif ($item->fieldType == 'resourse')                  // logo/image 
            $this->generateResource($item);
        elseif ($item->fieldType == 'line')                       // line
            $this->generateLine($item);
        elseif ($item->fieldType == 'box')                        // Box Rectrangular or other
            $this->generateBox($item);
    }

    // generate Caption
    public function generateCaption($layout)
    {
        $fontWeight = $this->style($layout->isBold, $layout->isItalic, $layout->isUnderline);
        $this->_fPdf->SetTextColor((int)$layout->color);
        $this->_fPdf->SetFont($layout->fontName, $fontWeight, $layout->fontSize);

        $alignment = false;
        if (isset($layout->alignment)) {
            $alignment = $layout->alignment;
            $split = str_split($alignment);
            $alignment = ucfirst($split[0]);                    // (L,R,C)
        }
        $this->_fPdf->SetXY($layout->x, $layout->y);            // Set X and Y
        $this->_fPdf->Cell($layout->width, $layout->height, $layout->caption, 0, 0,  $alignment);
    }

    // Generate Resourse
    public function generateResource($layout)
    {
        $this->_fPdf->Image($layout->resoursePath, $layout->x, $layout->y, $layout->width, $layout->height);
    }

    // Generate Line
    public function generateLine($layout)
    {
        $this->_fPdf->SetDrawColor((int)$layout->color);
        $this->_fPdf->SetLineWidth($layout->fontSize);

        if (isset($layout->height) && isset($layout->width))
            throw new Exception("Co Ordinates must be identical");

        if (isset($layout->height))      // Line to be Vertical
            $this->_fPdf->Line($layout->x, $layout->y, $layout->x, $layout->height);

        if (isset($layout->width))       // Line to be Horizontal
            $this->_fPdf->Line($layout->x, $layout->y, $layout->width, $layout->y);
    }

    // Generate Box
    public function generateBox($layout)
    {
        $yPosition = $this->_fPdf->GetY() + $layout->y;
        $this->_fPdf->SetDrawColor((int)$layout->color);
        $this->_fPdf->SetLineWidth($layout->fontSize);
        $this->_fPdf->Rect($layout->x, $yPosition, $layout->width, $layout->height);
    }

    /**
     * |
     *  +-----------------------+
     * |  Details!   | (1.5)
     * +-----------------------+
     * |
     */
    public function generateDetails($req)
    {
        $this->_fPdf->Ln($req->template['headerDistance']);                     // Line Break
        $detailsQuery = $req->template['detailSql'];
        if (isset($detailsQuery))
            $this->_dtlQueryResult = DB::select($detailsQuery . " limit 10");

        $this->_readLineItems = collect($req->details)->where('fieldType', 'lineItem');
        if ($this->_readLineItems->isNotEmpty())
            $this->generateLineItem();
    }

    // Generate Line Item
    public function generateLineItem()
    {
        $lineItemFields = $this->_readLineItems->pluck('fieldName');
        $queryResults = collect($this->_dtlQueryResult);
        if ($queryResults->isEmpty())
            throw new Exception("No Results for the Detail Query");

        foreach ($queryResults as $result) {
            foreach ($this->_readLineItems as $key => $field) {
                $isBreak = array_key_last($lineItemFields->toArray()) == $key ? 1 : 0;
                $fieldName = $field['fieldName'];

                $fontWeight = $this->style($field['isBold'], $field['isItalic'], $field['isUnderline']);
                $this->_fPdf->SetTextColor((int)$field['color']);
                $this->_fPdf->SetFont($field['fontName'], $fontWeight, $field['fontSize']);

                $this->_fPdf->SetX($this->_fPdf->GetX() + $field['x']);                     // Set X
                $this->_fPdf->Cell($field['width'], 7, $result->$fieldName, $field['isBoxed'],  $isBreak, 'C', false);
            }
        }
    }


    /**
     * |
     * +-----------------------+
     * |  Footer!   |
     * +-----------------------+
     * |
     */
    public function generateFooter($req)
    {
        $this->_fPdf->Ln($req->template['footerHeight']);                   // Line Break
        if (isset($req->footer)) {                                          // Layout 
            foreach ($req->footer as $item) {
                $item = (object)$item;
                $this->readFieldType($item);
            }
        }
    }
}
