<?php

namespace App\Observers;

use App\Models\VtTemplate;
use Illuminate\Support\Facades\Redis;

class VtTemplateObserver
{
    /**
     * Handle the VtTemplate "created" event.
     *
     * @param  \App\Models\VtTemplate  $vtTemplate
     * @return void
     */
    public function created(VtTemplate $vtTemplate)
    {
        Redis::del('vt_templates');
    }

    /**
     * Handle the VtTemplate "updated" event.
     *
     * @param  \App\Models\VtTemplate  $vtTemplate
     * @return void
     */
    public function updated(VtTemplate $vtTemplate)
    {
        Redis::del('vt_templates');
    }

    /**
     * Handle the VtTemplate "deleted" event.
     *
     * @param  \App\Models\VtTemplate  $vtTemplate
     * @return void
     */
    public function deleted(VtTemplate $vtTemplate)
    {
        Redis::del('vt_templates');
    }

    /**
     * Handle the VtTemplate "restored" event.
     *
     * @param  \App\Models\VtTemplate  $vtTemplate
     * @return void
     */
    public function restored(VtTemplate $vtTemplate)
    {
        Redis::del('vt_templates');
    }

    /**
     * Handle the VtTemplate "force deleted" event.
     *
     * @param  \App\Models\VtTemplate  $vtTemplate
     * @return void
     */
    public function forceDeleted(VtTemplate $vtTemplate)
    {
        Redis::del('vt_templates');
    }
}
