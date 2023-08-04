<?php

namespace App\Bll;

use Codedge\Fpdf\Fpdf\Fpdf;


/**
 * | Author-Anshu Kumar
 * | Created on-21-07-2023 
 * | Created for Reports layout previews
 */

class GenerateReportBllRef
{
    private $_fPdf;
    // Initialization
    public function __construct()
    {
        $this->_fPdf = new Fpdf();
    }

    // Create Report
    public function createReport($req)
    {
        // Check template look is default or according to the tempalte attributs for styling
        if ($req['is_default'] == false) {
            $ori = ($req['is_landscape'] == true) ? 'L' : 'P';
            $size = ($req['paper_size_enum']) ? $req['paper_size_enum'] : '210,297';
            $this->_fPdf = new Fpdf($ori, 'mm', explode(',', $size));
            $this->_fPdf->Ln($req['footer_height']);
        }



        //$this->_fPdf->SetTopMargin($req['header_height']);
        $this->_fPdf->Ln($req['header_distance']);

        if ($req['detail_layout'] == 'General') {
            $this->_fPdf->AliasNbPages();
            $this->_fPdf->AddPage();
            $this->_fPdf->Ln($req['header_height']);
            $this->generalLayout($req);
            $this->_fPdf->Ln($req['footer_height']);
        }

        $this->_fPdf->Output();
    }


    // Generate General Reports
    public function generalLayout($req)
    {
        /*
        *********************************************************
        * Create dynamic template layout of every elements 
        *********************************************************
        */
        foreach ($req['layout'] as $lat) {
            $lat = (object) $lat;
            $x = $this->_fPdf->GetX();
            $y = $this->_fPdf->GetY();
            $lat->border = '0';
            $lat->position = '1';
            $this->getElement($lat, false, $this->_fPdf);
            $this->_fPdf->SetXY($x + $lat->width, $y + $lat->height);
        }

        $this->_fPdf->Ln($req['detail_line_spacing']);


        /*
        *****************************************************************************
        * Create dynamic Template Details of according to template details_sql 
        *****************************************************************************
        */
        $this->_fPdf->SetFillColor(255);
        // Header for details table

        foreach ($req['details'] as $h) {
            $h = (object) $h;
            $h->height = '8';
            $h->border = '1';
            $h->position = '0';
            $this->getElement($h, true, $this->_fPdf);
        }
        $this->_fPdf->Ln();

        // Data for details table
        $fill = false;
        foreach ($req['details_data'] as $row) {
            $row = (object) $row;
            foreach ($req['details'] as $h) {
                $h = (object) $h;
                $res = str_replace(' ', '_', str_replace('.', '', strtolower(trim($h->field_name))));
                $h->field_data = ($row->{$res}) ? $row->{$res} : '-';
                $h->height = '8';
                $h->border = '1';
                $h->position = '0';
                $this->getElement($h, $fill, $this->_fPdf);
                $fill = !$fill;
            }
            $this->_fPdf->Ln();
        }
        $this->_fPdf->Ln(10);


        /*
        *****************************************************************************
        * Template Footer according to template footer 
        *****************************************************************************
        */
        foreach ($req['footer'] as $foot) {
            $foot = (object) $foot;
            $x = $this->_fPdf->GetX();
            $y = $this->_fPdf->GetY();
            $foot->border = '0';
            $foot->position = '1';
            $foot->font_name = $foot->fontname;
            $foot->font_size = $foot->size;
            $this->_fPdf->SetXY($x + $foot->x, $y + $foot->y);
            $this->getElement($foot, $fill, $this->_fPdf);

            //$fpdf->Ln();

        }
    }

    /**
     * Create Line in template
     * @param fieldtype $field, all data for parameter in array $data, for cell using true or false value $fill, get current create pdf page $fpdf
     * @return element as image with position
     */

    public function getElement(object $data, $fill, $fpdf)
    {
        //print_r($data);
        $elem = '';
        $field = $data->field_type;
        $style = $this->style($data->is_bold, $data->is_italic, $data->is_underline);
        $fpdf->SetFont($data->font_name, $style, $data->font_size);
        $fpdf->SetTextColor(hexdec($data->color));
        $align = ucfirst(substr($data->alignment, 0, 1));


        if ($data->is_visible) {
            //echo $data->width;
            if ($field == 'line') {
                if ($data->width)
                    $elem = $fpdf->Line($data->x, $data->y, $data->width, $data->y);
                else
                    $elem = $fpdf->Line($data->x, $data->y, $data->height, $data->y);
            }

            if ($field == 'image') {
                $url = public_path('images') . "/" . $data->resource;
                if (file_exists($url))
                    $elem = $fpdf->Image($url, $data->x, $data->y, $data->width, $data->height, '');
            }


            if ($field == 'field') {
                $colval = $data->field_name;
                if (isset($data->field_data))
                    $colval = $data->field_data;
                if ($data->position == 'multicell')
                    $elem = $fpdf->MultiCell($data->width, $data->height, $colval, $data->border, $align, $fill);
                else
                    $elem = $fpdf->Cell($data->width, $data->height, $colval, $data->border, $data->position, $align, $fill);
            }

            if ($field == 'caption') {
                if ($data->position == 'multicell')
                    $elem = $fpdf->MultiCell($data->width, $data->height, $data->caption, $data->border, $align, $fill);
                else
                    $elem = $fpdf->Cell($data->width, $data->height, $data->caption, $data->border, $data->position, $align, $fill);
            }


            if ($field == 'box')
                $elem = $fpdf->Rect($data->x, $data->y, $data->width, $data->height, 'DF');



            return $elem;
        }
    }


    /*
    *************************************
    * Check wich style is prefer
    *************************************
    */

    static function style($bold, $italic, $underline)
    {
        $style = '';
        if ($bold == true && $italic == true && $underline == true) {
            $style = 'BIU';
        } else if ($bold == true && $italic == true) {
            $style = 'BI';
        } else if ($italic == true && $underline == true) {
            $style = 'IU';
        } else if ($bold == true && $underline == true) {
            $style = 'BU';
        } else if ($bold == true) {
            $style = 'B';
        } else if ($italic == true) {
            $style = 'I';
        } else if ($underline == true) {
            $style = 'U';
        }
        return $style;
    }
}
