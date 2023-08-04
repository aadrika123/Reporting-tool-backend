<?php

namespace App\Observers;

use App\Models\VtString;
use Illuminate\Support\Facades\Redis;

class VtStringObserver
{
    /**
     * Handle the VtString "created" event.
     *
     * @param  \App\Models\VtString  $vtString
     * @return void
     */
    public function created(VtString $vtString)
    {
        Redis::del('vt_strings');
    }

    /**
     * Handle the VtString "updated" event.
     *
     * @param  \App\Models\VtString  $vtString
     * @return void
     */
    public function updated(VtString $vtString)
    {
        Redis::del('vt_strings');
    }

    /**
     * Handle the VtString "deleted" event.
     *
     * @param  \App\Models\VtString  $vtString
     * @return void
     */
    public function deleted(VtString $vtString)
    {
        Redis::del('vt_strings');
    }

    /**
     * Handle the VtString "restored" event.
     *
     * @param  \App\Models\VtString  $vtString
     * @return void
     */
    public function restored(VtString $vtString)
    {
        Redis::del('vt_strings');
    }

    /**
     * Handle the VtString "force deleted" event.
     *
     * @param  \App\Models\VtString  $vtString
     * @return void
     */
    public function forceDeleted(VtString $vtString)
    {
        Redis::del('vt_strings');
    }
}
