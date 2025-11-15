<?php

namespace App\Services\PrivilegeService\Support;

use App\Casts\PanelGuardCast;
use App\Models\Office\Employee;
use App\Services\PrivilegeService\PrivilegeServiceContract;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class StaffPrivilegeService implements PrivilegeServiceContract
{

    protected Employee $record;

    /**
     * @param Employee $record
     */
    public function __construct(Employee $record)
    {
        $this->record = $record;
    }


    public static function make(Employee|Model $record):static
    {
        return new static($record);
    }

    /**
     * @return void
     */
    public function afterRegister(): void
    {
        $defaultRole = Role::firstWhere('guard_name',PanelGuardCast::OFFICE->getPanelGuard())->name;
        $this->record->syncRoles($defaultRole);;

        $this->record->givePermissionTo(['page_Onboarding',]);
    }

    /**
     * @return void
     */
    public function afterOnboarding():void
    {
        // Set Role First
        if ($this->record->roles->count() == 0){ $this->afterRegister(); }

        // If Role Already Assign then Comes For Permissions
        $this->record->givePermissionTo(['page_MyProfile','page_ChangePassword','page_MyPreference']);

        // Remove Permissions
        $this->record->revokePermissionTo('page_Onboarding');


    }

    /**
     * @return void
     */
    public function afterSubscribe():void
    {
        // TODO: Implement afterSubscribe() method.
    }
}
