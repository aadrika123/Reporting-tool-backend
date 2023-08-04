<?php

namespace App\Observers;

use App\Models\VtResource;
use Illuminate\Support\Facades\Redis;

class VtResourceObserver
{

    /**
     * Handle the VtResource "created" event.
     *
     * @param  \App\Models\VtResource  $vtResource
     * @return void
     */
    public function created(VtResource $vtResource)
    {
        Redis::del('vt_resources');
    }

    /**
     * Handle the VtResource "updated" event.
     *
     * @param  \App\Models\VtResource  $vtResource
     * @return void
     */
    public function updated(VtResource $vtResource)
    {
        Redis::del('vt_resources');
    }

    /**
     * Handle the VtResource "deleted" event.
     *
     * @param  \App\Models\VtResource  $vtResource
     * @return void
     */
    public function deleted(VtResource $vtResource)
    {
        Redis::del('vt_resources');
    }

    /**
     * Handle the VtResource "restored" event.
     *
     * @param  \App\Models\VtResource  $vtResource
     * @return void
     */
    public function restored(VtResource $vtResource)
    {
        Redis::del('vt_resources');
    }

    /**
     * Handle the VtResource "force deleted" event.
     *
     * @param  \App\Models\VtResource  $vtResource
     * @return void
     */
    public function forceDeleted(VtResource $vtResource)
    {
        Redis::del('vt_resources');
    }
}
