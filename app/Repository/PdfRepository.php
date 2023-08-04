<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Repository for Fetching The Api master 
 * ---------------------------------------------------------------------------------------------------------
 * Created On- 
 * Created By-
 * ---------------------------------------------------------------------------------------------------------
 */


class PdfRepository
{

    /**
     * Get Data from vt_templates table behalp of template id
     * @param api-id $id
     * @return resposne
     */
    public function GetTempData($id)
    {
        try {
            $template = DB::table('vt_templates')
                        ->where('id',$id)
                        ->where('status', 1)
                        ->first();
            return $template;
        } catch (Exception $e) {
            return response()->json($e, 400);
        }
    }


    /**
     * Get Data from vt_template_pagelayouts table behalp of template id
     * @param api-id $temp_id
     * @return resposne
     */
    public function GetTempPageLayout($temp_id)
    {
        try {
            $temp_layout = DB::table('vt_template_pagelayouts')
                            ->where('report_template_id', $temp_id)
                            ->where('status', 1)
                            ->get();
            return $temp_layout;
        } catch (Exception $e) {
            return response()->json($e, 400);
        }
    }

    /**
     * Get Data from vt_template_deatils table behalp of template id
     * @param api-id $temp_id
     * @return resposne
     */
    public function GetTempDetails($temp_id)
    {
        try {
            $temp_details = DB::table('vt_template_deatils')
                            ->where('report_template_id', $temp_id)
                            ->where('status', 1)
                            ->orderby('id')
                            ->get();
            return $temp_details;
        } catch (Exception $e) {
            return response()->json($e, 400);
        }
    }


    /**
     * Get Data from vt_template_footers table behalp of template id
     * @param api-id $temp_id
     * @return resposne
     */
    public function GetTempFooter($temp_id)
    {
        try {
            $temp_footer = DB::table('vt_template_footers')
                            ->where('report_template_id', $temp_id)
                            ->where('status', 1)
                            ->orderby('id')
                            ->get();
            return $temp_footer;
        } catch (Exception $e) {
            return response()->json($e, 400);
        }
    }


    /**
     * Get Data from sql query according to sql columns in template vt_templates table
     * @param api-id $sql
     * @return resposne
     */
    public function GetDatafromQuery($sql)
    {
        try {
            if(!empty($sql))
            {
                $query_data = DB::select($sql);
                return $query_data;
            }else{
                return array();
            }
            
        } catch (Exception $e) {
            return response()->json($e, 400);
        }
    }

}