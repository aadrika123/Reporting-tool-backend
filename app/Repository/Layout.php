<?php

namespace App\Repository;

use App\ReportFieldType\FieldType;
use Codedge\Fpdf\Fpdf\Fpdf;

class Layout extends FPDF
{
    public $fieldType;
    private $fpdf;
    public function __construct(FieldType $fieldType)
    {
        $this->fieldType = $fieldType;
    }



    /*
    *********************************************************
    * Create dynamic report template through FPDF
    *********************************************************
    */
    public function create_report($report)
    {
        $this->fpdf = new Fpdf();


        // Check template look is default or according to the tempalte attributs for styling
        if ($report['is_default'] == false) {
            $ori = ($report['is_landscape'] == true) ? 'L' : 'P';
            $size = ($report['paper_size_enum']) ? $report['paper_size_enum'] : '210,297';
            $this->fpdf = new Fpdf($ori, 'mm', explode(',', $size));
            $this->fpdf->Ln($report['footer_height']);
        }



        //$this->fpdf->SetTopMargin($report['header_height']);
        $this->fpdf->Ln($report['header_distance']);

        if ($report['detail_layout'] == 'General') {
            $this->fpdf->AliasNbPages();
            $this->fpdf->AddPage();
            $this->fpdf->Ln($report['header_height']);
            $this->GeneralLayout($report, $this->fpdf);
            $this->fpdf->Ln($report['footer_height']);
        }


        // if ($report['detail_layout'] == 'Label') {
        //     $this->fpdf->AliasNbPages();
        //     $this->fpdf->AddPage();
        //     $this->fpdf->Ln($report['header_height']);
        //     $this->LabelLayout($report, $this->fpdf);
        //     $this->fpdf->Ln($report['footer_height']);
        // }


        // if ($report['detail_layout'] == 'Form') {

        //     $newArr = array();

        //     for ($i = 0; $i < count($report['layout']); $i++) {
        //         $data = array();
        //         $data['layout'] = $report['layout'][$i];
        //         $data['layout_data'] = $report['layout_data'];
        //         if (count($report['details']) > 0 && $i < count($report['details']))
        //             $data['details'] = $report['details'][$i];
        //         if (count($report['footer']) > 0 && $i < count($report['footer']))
        //             $data['footer'] = $report['footer'][$i];
        //         $newArr[] = $data;
        //     }

        //     foreach ($newArr as $lat) {
        //         $last = last($report['layout']);
        //         if ($last != $lat) {
        //             $ori = ($report['is_landscape'] == true) ? 'L' : 'P';
        //             $size = ($report['paper_size_enum']) ? $report['paper_size_enum'] : '210,297';
        //             $headerHeight = $report['header_height'];
        //             if ($this->fpdf->PageNo() > 0)
        //                 $headerHeight = $report['header_height_page2'];
        //             $this->fpdf->SetTopMargin(10 + $headerHeight);
        //             $this->fpdf->AddPage($ori, explode(',', $size));
        //             $this->FormLayout($lat, $this->fpdf);
        //             $this->fpdf->Ln($report['footer_height']);
        //         }
        //         //$newArr
        //     }
        // }

        // if ($report['detail_layout'] == 'Document') {

        //     $docarr = collect($report['layout'])->groupBy('page_no');
        //     for ($i = 1; $i <= count($docarr); $i++) {
        //         $ori = ($report['is_landscape'] == true) ? 'L' : 'P';
        //         $size = ($report['paper_size_enum']) ? $report['paper_size_enum'] : '210,297';
        //         $headerHeight = $report['header_height'];
        //         if ($this->fpdf->PageNo() > 0)
        //             $headerHeight = $report['header_height_page2'];
        //         $this->fpdf->SetTopMargin(10 + $headerHeight);
        //         $this->fpdf->AddPage($ori, explode(',', $size));
        //         $this->DocumentLayout($docarr[$i], $this->fpdf);
        //         $this->fpdf->Ln($report['footer_height']);
        //     }
        // }



        $this->fpdf->Output();
    }



    public function GeneralLayout($report, $fpdf)
    {
        /*
        *********************************************************
        * Create dynamic template layout of every elements 
        *********************************************************
        */
        foreach ($report['layout'] as $lat) {
            $lat = (object) $lat;
            $x = $fpdf->GetX();
            $y = $fpdf->GetY();
            $lat->border = '0';
            $lat->position = '1';
            $this->fieldType->getElement($lat, false, $fpdf);
            $fpdf->SetXY($x + $lat->width, $y + $lat->height);
        }

        $fpdf->Ln($report['detail_line_spacing']);


        /*
        *****************************************************************************
        * Create dynamic Template Details of according to template details_sql 
        *****************************************************************************
        */
        $fpdf->SetFillColor(255);
        // Header for details table

        foreach ($report['details'] as $h) {
            $h = (object) $h;
            $h->height = '8';
            $h->border = '1';
            $h->position = '0';
            $this->fieldType->getElement($h, true, $fpdf);
        }
        $fpdf->Ln();

        // Data for details table
        $fill = false;
        foreach ($report['details_data'] as $row) {
            $row = (object) $row;
            foreach ($report['details'] as $h) {
                $h = (object) $h;
                $res = str_replace(' ', '_', str_replace('.', '', strtolower(trim($h->field_name))));
                $h->field_data = ($row->{$res}) ? $row->{$res} : '-';
                $h->height = '8';
                $h->border = '1';
                $h->position = '0';
                $this->fieldType->getElement($h, $fill, $fpdf);
                $fill = !$fill;
            }
            $fpdf->Ln();
        }
        $fpdf->Ln(10);


        /*
        *****************************************************************************
        * Template Footer according to template footer 
        *****************************************************************************
        */
        foreach ($report['footer'] as $foot) {
            $foot = (object) $foot;
            $x = $fpdf->GetX();
            $y = $fpdf->GetY();
            $foot->border = '0';
            $foot->position = '1';
            $foot->font_name = $foot->fontname;
            $foot->font_size = $foot->size;
            $fpdf->SetXY($x + $foot->x, $y + $foot->y);
            $this->fieldType->getElement($foot, $fill, $fpdf);

            //$fpdf->Ln();

        }
    }


    public function LabelLayout($report, $fpdf)
    {

        /*
        *********************************************************
        * Create dynamic template layout of every elements 
        *********************************************************
        */

        $paper_size = explode(',', $report['paper_size_enum']);
        $hH = $report['header_height'] + 10;
        $fH = $report['footer_height'] + 30;
        $pH = $fpdf->GetPageHeight();
        $pW = $fpdf->GetPageWidth();
        $rC = $report['label_row_count'];
        $cC = $report['label_column_count'];

        $h = (($pH - ($hH + $fH)) / $rC);
        $w = (($pW - 20) / $cC);

        $fpdf->SetFont('Arial', '', 14);

        //Table with no of rows and no. of columns
        $warr = array();
        $contents = array();

        for ($i = 1; $i <= $cC; $i++) {
            $warr[] = $w;
            $contents[] = $report['layout'];
        }

        $this->SetWidths($warr);
        for ($i = 1; $i <= $rC; $i++)
            $this->Row($h, $contents, $fpdf);
    }


    var $widths;
    var $aligns;
    function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths = $w;
    }
    function SetAligns($a)
    {
        //Set the array of column alignments
        $this->aligns = $a;
    }

    function Row($h, $data, $fpdf)
    {
        //Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';

            //Save the current position
            $x = $fpdf->GetX();
            $y = $fpdf->GetY();

            //Draw the border
            $fpdf->Rect($x, $y, $w, $h);
            $nx = round($x + $h);
            $ny = round($y + $w);
            //Print the element
            foreach ($data[$i] as $lat) {
                $lat = (object) $lat;
                if (($lat->x >= $x &&  $lat->x <= $nx) && ($lat->y >= $y && $lat->y <= $ny)) {
                    $lat->border = '0';
                    $lat->width = $w;
                    $lat->position = "multicell";
                    $this->fieldType->getElement($lat, false, $fpdf);
                }
            }

            //Put the position to the right of the cell
            $fpdf->SetXY($x + $w, $y);
        }
        //Go to the next line
        $fpdf->Ln($h);
    }

    function CheckPageBreak($h, $fpdf)
    {
        //If the height h would cause an overflow, add a new page immediately
        if ($fpdf->GetY() + $h > $this->PageBreakTrigger)
            $fpdf->AddPage($fpdf->CurOrientation);
    }


    public function FormLayout($report, $fpdf)
    {
        // Print layout
        $report['layout']->border = '0';
        $report['layout']->position = 'multicell';
        $this->fieldType->getElement($report['layout'], false, $fpdf);
        $fpdf->Ln(10);

        //echo "<pre/>";print_r($report['layout_data']);
        $fieldsname = array();
        foreach ($report['layout_data'][0] as $key => $value) {
            $report['layout']->border = '1';
            $report['layout']->position = '0';
            $report['layout']->field_type = 'field';
            $report['layout']->alignment = 'left';
            $report['layout']->width = ($fpdf->GetPageWidth() - 20) / count((array)$report['layout_data'][0]);
            $report['layout']->field_name = ucfirst(str_replace('_', ' ', trim($key)));
            $fieldsname[] = trim($key);
            $this->fieldType->getElement($report['layout'], false, $fpdf);
        }
        $fpdf->Ln();

        // Data for details table
        foreach ($report['layout_data'] as $row) {
            $row = (object) $row;
            foreach ($fieldsname as $field) {
                $report['layout']->field_data = $row->{$field};
                $report['layout']->height = '8';
                $report['layout']->border = '1';
                $report['layout']->position = '0';
                $this->fieldType->getElement($report['layout'], false, $fpdf);
            }
            $fpdf->Ln();
        }
        $fpdf->Ln(10);

        // Print Detail
        // if(array_key_exists('details', $report))
        // {
        //     $report['details']->height = '8';
        //     $report['details']->border = '1';
        //     $report['details']->position = '0';
        //     $this->fieldType->getElement($report['details'], false, $fpdf);
        //     $fpdf->Ln(10);
        // }


        // // Print Footer
        // if(array_key_exists('footer', $report))
        // {
        //     $report['footer']->height = '8';
        //     $report['footer']->border = '1';
        //     $report['footer']->position = '0';
        //     $this->fieldType->getElement($report['footer'], true, $fpdf);
        //     $fpdf->Ln(10);
        // }


    }

    public function DocumentLayout($report, $fpdf)
    {
        // Print layout
        foreach ($report as $lat) {
            $lat = (object) $lat;
            $x = $fpdf->GetX();
            $y = $fpdf->GetY();

            $lat->border = '0';
            $lat->position = 'multicell';
            $this->fieldType->getElement($lat, false, $fpdf);
            $previousH = 0;
            if ($lat->field_type == 'field' || $lat->field_type == 'caption')
                $previousH = $this->GetMultiCellHeight($lat->width, $lat->height, $lat->caption, $fpdf) + 10;

            $fpdf->SetXY($x + $lat->width, $y + $previousH);
        }
    }

    function GetMultiCellHeight($w, $h, $txt, $fpdf, $border = null, $align = 'J')
    {
        // Calculate MultiCell with automatic or explicit line breaks height
        // $border is un-used, but I kept it in the parameters to keep the call
        //   to this function consistent with MultiCell()
        $cw = &$fpdf->CurrentFont['cw'];
        if ($w == 0)
            $w = $fpdf->w - $fpdf->rMargin - $fpdf->x;
        $wmax = ($w - 2 * $fpdf->cMargin) * 1000 / $fpdf->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $ns = 0;
        $height = 0;
        while ($i < $nb) {
            // Get next character
            $c = $s[$i];
            if ($c == "\n") {
                // Explicit line break
                if ($fpdf->ws > 0) {
                    $fpdf->ws = 0;
                    $fpdf->_out('0 Tw');
                }
                //Increase Height
                $height += $h;
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
                $ls = $l;
                $ns++;
            }
            $l += $cw[$c];
            if ($l > $wmax) {
                // Automatic line break
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                    if ($fpdf->ws > 0) {
                        $fpdf->ws = 0;
                        $fpdf->_out('0 Tw');
                    }
                    //Increase Height
                    $height += $h;
                } else {
                    if ($align == 'J') {
                        $fpdf->ws = ($ns > 1) ? ($wmax - $ls) / 1000 * $fpdf->FontSize / ($ns - 1) : 0;
                        $fpdf->_out(sprintf('%.3F Tw', $fpdf->ws * $fpdf->k));
                    }
                    //Increase Height
                    $height += $h;
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
            } else
                $i++;
        }
        // Last chunk
        if ($fpdf->ws > 0) {
            $fpdf->ws = 0;
            $fpdf->_out('0 Tw');
        }
        //Increase Height
        $height += $h;

        return $height;
    }
}
