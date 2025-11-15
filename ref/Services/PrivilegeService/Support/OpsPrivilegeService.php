<?php

namespace App\Services\PrivilegeService\Support;

use App\Casts\PanelGuardCast;
use App\Models\Office\Employee;
use App\Models\Operation\Ops;
use App\Services\PrivilegeService\PrivilegeServiceContract;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class OpsPrivilegeService implements PrivilegeServiceContract
{

    protected Ops $record;

    /**
     * @param Ops $record
     */
    public function __construct(Ops $record)
    {
        $this->record = $record;
    }


    public static function make(Ops|Model $record):static
    {
        return new static($record);
    }

    /**
     * @return void
     */
    public function afterRegister(): void
    {
        $defaultRole = Role::firstWhere('guard_name',PanelGuardCast::OPS->getPanelGuard())->name;
        $this->record->syncRoles($defaultRole);;

        // If Role Already Assign then Comes For Permissions
        $this->record->givePermissionTo(['page_MyProfile','page_ChangePassword','page_MyPreference']);


        // Give Permission
        $this->record->givePermissionTo(['view_user','view_any_user','create_user']);

        $this->record->givePermissionTo(['page_MyWallet','view_wallet::wallet','view_any_wallet::wallet','create_wallet::wallet','view_any_wallet::transaction','view_wallet::transaction']);


    }

    /**
     * @return void
     */
    public function afterOnboarding():void
    {

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

