<?php

namespace App\Services\PrivilegeService\Support;

use App\Casts\PanelGuardCast;
use App\Models\Enums\AuthStatusCast;
use App\Models\User;
use App\Services\PrivilegeService\PrivilegeServiceContract;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class UserPrivilegeService implements PrivilegeServiceContract
{

    protected User $record;

    /**
     * @param User $record
     */
    public function __construct(User $record)
    {
        $this->record = $record;
    }


    public static function make(User|Model $record):static
    {
        return new static($record);
    }

    /**
     * @return void
     */
    public function afterRegister(): void
    {
        $defaultRole = Role::firstWhere('guard_name',PanelGuardCast::APP->getPanelGuard())->name;
        $this->record->syncRoles($defaultRole);
        // Give Permissions
        PermissionManager::make($this->record)->onboarding();
    }

    /**
     * @return void
     */
    public function afterOnboarding():void
    {
        // Give Permissions
        PermissionManager::make($this->record)->enableBasePages();
        PermissionManager::make($this->record)->subscription();
        PermissionManager::make($this->record)->enableWallet(false);
        PermissionManager::make($this->record)->enableShop(false);
        PermissionManager::make($this->record)->enableLocalization();
        PermissionManager::make($this->record)->enableMemberUser(false);
        // Remove Permissions
        PermissionManager::make($this->record)->onboarding(false);
    }

    /**
     * @return void
     */
    public function afterSubscribe():void
    {
        // Give Permissions
        PermissionManager::make($this->record)->enableMemberUser();
        PermissionManager::make($this->record)->enableShop(false);
        // Remove Permissions
        PermissionManager::make($this->record)->subscription(false);

    }

    public function afterFirstReferralJoining():void
    {
        // Update Status
        //$this->record->fill(['status' => AuthStatusCast::ACTIVE])->save();
    }

}
