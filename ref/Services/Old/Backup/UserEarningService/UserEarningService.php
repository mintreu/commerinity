<?php

namespace App\Services\Backup\UserEarningService;

use App\Models\User;

class UserEarningService
{
    protected User $record;

    /**
     * @param User $record
     */
    public function __construct(User $record)
    {
        $this->record = $record;
    }

    public function make(User $record): static
    {
        return new static($record);
    }


    public function calculate()
    {

    }





}
