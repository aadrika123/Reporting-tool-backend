<?php
   
namespace App\ReportFieldType;
use Illuminate\Support\Facade;
use App\Traits\Api\PdfHelpers;

   
class FieldType{
    use PdfHelpers;
    
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
        $style = $this->style($data->is_bold,$data->is_italic,$data->is_underline);
        $fpdf->SetFont($data->font_name,$style,$data->font_size);
        $fpdf->SetTextColor(hexdec($data->color));
        $align = ucfirst(substr($data->alignment, 0, 1));
        
        
        if($data->is_visible){
            //echo $data->width;
            if($field == 'line')
            {
                if($data->width)
                    $elem = $fpdf->Line($data->x, $data->y, $data->width, $data->y);
                else
                    $elem = $fpdf->Line($data->x, $data->y, $data->height, $data->y);
            }

            if($field == 'image')
            {
                $url = public_path('images')."/".$data->resource;
                if(file_exists($url))
                    $elem = $fpdf->Image($url,$data->x, $data->y, $data->width, $data->height,'');
            }
                

            if($field == 'field')
            {
                $colval = $data->field_name;
                if(isset($data->field_data))
                    $colval = $data->field_data;
                if($data->position == 'multicell')
                    $elem = $fpdf->MultiCell($data->width, $data->height, $colval, $data->border, $align, $fill);
                else
                    $elem = $fpdf->Cell($data->width, $data->height, $colval, $data->border, $data->position, $align, $fill);
            }

            if($field == 'caption')
            {
                if($data->position == 'multicell')
                    $elem = $fpdf->MultiCell($data->width, $data->height, $data->caption, $data->border, $align, $fill);
                else
                    $elem = $fpdf->Cell($data->width, $data->height, $data->caption, $data->border, $data->position, $align, $fill);
            }
                

            if($field == 'box')
                $elem = $fpdf->Rect($data->x, $data->y, $data->width, $data->height, 'DF');
            
            
            
            return $elem;
        }
    }

}