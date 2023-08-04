<?php

namespace App\Traits\Api;

/**
 * PdfHelpers is a Trait of helpers
 * Created On-
 * Created By-
 * 
 */
trait PdfHelpers
{
    /*
    *************************************
    * Check wich style is prefer
    *************************************
    */

    static function style($bold,$italic,$underline)
    {
        $style = '';
        if($bold == true && $italic == true && $underline == true){
            $style = 'BIU';
        }else if($bold == true && $italic == true){
            $style = 'BI';
        }else if($italic == true && $underline == true){
            $style = 'IU';
        }else if($bold == true && $underline == true){
            $style = 'BU';
        }else if($bold == true){
            $style = 'B';
        }else if($italic == true){
            $style = 'I';
        }else if($underline == true){
            $style = 'U';
        }
        return $style;
    }

}
