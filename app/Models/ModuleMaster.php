<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleMaster extends Model
{
    use HasFactory;
    protected $connection = 'conn_juidco_prop';

    // Get All Modules
    public function moduleList()
    {
        return ModuleMaster::orderBy('module_name')->get();
    }
}
