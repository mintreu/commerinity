<?php

namespace App\Services\PrivilegeService;

use App\Models\Office\Employee;
use App\Models\Operation\Ops;
use App\Models\Recruitment\Applicant;
use App\Models\User;
use App\Services\PrivilegeService\Support\ApplicantPrivilegeService;
use App\Services\PrivilegeService\Support\OpsPrivilegeService;
use App\Services\PrivilegeService\Support\StaffPrivilegeService;
use App\Services\PrivilegeService\Support\UserPrivilegeService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class PrivilegeService
{

    protected Model|Authenticatable $record;
    protected ?PrivilegeServiceContract $activePrivilegeService = null;

    /**
     * @param Authenticatable|Model $record
     */
    public function __construct(Model|Authenticatable $record)
    {
        $this->record = $record;
        $this->activePrivilegeService = $this->getAccountPrivilegeService();
    }


    public static function make(Authenticatable|Model $record):?PrivilegeServiceContract
    {
        $instance = new static($record);
        return $instance->activePrivilegeService;
    }

    protected function getAccountPrivilegeService():?PrivilegeServiceContract
    {
        $class = get_class($this->record);

        // Match the class and return the corresponding privilege service
        return match ($class) {
            User::class => UserPrivilegeService::make($this->record),
            Applicant::class => ApplicantPrivilegeService::make($this->record),
            Employee::class => StaffPrivilegeService::make($this->record),
            Ops::class => OpsPrivilegeService::make($this->record),
            default => null
        };
    }







}
