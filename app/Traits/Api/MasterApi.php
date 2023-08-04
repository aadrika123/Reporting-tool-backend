<?php

namespace App\Traits\Api;

/**
 * Trait for Saving and updating Api 
 * Created On-29-06-2022 
 * Created By-Anshu Kumar
 * 
 * Code Tested By-
 * Feedback-
 */
trait MasterApi
{

    // Insert Data
    public function InsertData($table, $data)
    {   
        foreach($data as $key=>$value)
        {
            $field_name = strtolower(preg_replace("/([^A-Z-])([A-Z])/", "$1_$2", $key));
            $table->{$field_name} = $value;
        }
        $table->save();
        return response()->json(['status' => true, 'Message' => "Save successfully"], 200); 
    }

    // Update Data
    public function UpdateData($table, $data)
    {   
        foreach($data as $key=>$value)
        {
            $field_name = strtolower(preg_replace("/([^A-Z-])([A-Z])/", "$1_$2", $key));
            $table->{$field_name} = $value;
        }
        $table->save();
        return response()->json(['status' => true, 'Message' => "Updated successfully"], 200); 
    }

    
}
