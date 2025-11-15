<?php

namespace App\Services\PrivilegeService\Support;

use App\Casts\PanelGuardCast;
use App\Models\Enums\AuthStatusCast;
use App\Models\Recruitment\Applicant;
use App\Services\PrivilegeService\PrivilegeServiceContract;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class ApplicantPrivilegeService implements PrivilegeServiceContract
{


    protected Applicant $record;

    /**
     * @param Applicant $record
     */
    public function __construct(Applicant $record)
    {
        $record->loadMissing('roles','roles.permissions');
        $this->record = $record;
    }


    public static function make(Applicant|Model $record):static
    {
        return new static($record);
    }


    /**
     * @return void
     */
    public function afterRegister(): void
    {
        $defaultRole = Role::firstWhere('guard_name',PanelGuardCast::APPLICANT->getPanelGuard())->name;
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
        // Give Recruitment Related Permissions
        $this->record->givePermissionTo([
            'view_recruitment::recruitment',
            'view_any_recruitment::recruitment',
            'update_recruitment::recruitment', // Apply Page Based On Edit Page

            'view_recruitment::job::application', // Applicant View Job Application of His Own
            'view_any_recruitment::job::application',


//            'create_recruitment::job::application',
//            'update_recruitment::job::application',

            'view_recruitment::applicant',  // applicant
        ]);

        // Remove Permissions
        $this->record->revokePermissionTo('page_Onboarding');


        // Update User Status
        $this->record->fill(['status' => AuthStatusCast::ACTIVE])->save();

    }

    /**
     * @return void
     */
    public function afterSubscribe():void
    {
        // TODO: Implement afterSubscribe() method.
    }
}
