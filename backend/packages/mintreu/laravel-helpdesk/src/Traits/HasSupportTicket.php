<?php

namespace Mintreu\LaravelHelpdesk\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Mintreu\LaravelHelpdesk\Models\Helpdesk;

trait HasSupportTicket
{


    public function tickets(): MorphMany
    {
        return $this->morphMany(Helpdesk::class,'authorable');
    }




}
