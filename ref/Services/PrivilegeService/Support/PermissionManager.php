<?php

namespace App\Services\PrivilegeService\Support;

use Illuminate\Foundation\Auth\User as Authenticatable;

class PermissionManager
{
    protected Authenticatable $record;

    /**
     * PermissionManager constructor.
     *
     * @param Authenticatable $record
     */
    public function __construct(Authenticatable $record)
    {
        $this->record = $record;
    }

    /**
     * Create a new instance.
     *
     * @param Authenticatable $record
     * @return static
     */
    public static function make(Authenticatable $record): static
    {
        return new static($record);
    }


    public function onboarding(bool $enable = true): void
    {
        if ($enable){
            $this->record->givePermissionTo('page_Onboarding');
        }else{
            $this->record->revokePermissionTo('page_Onboarding');
        }
    }


    public function enableBasePages(): void
    {
        $this->record->givePermissionTo(['page_MyProfile','page_ChangePassword','page_MyPreference']);
    }


    public function subscription(bool $enable = true): void
    {
        if ($enable)
        {
            $this->record->givePermissionTo('page_SubscribeMembership');
        }else{
            $this->record->revokePermissionTo('page_SubscribeMembership');
        }
    }

    public function enableWallet(bool $manage = true): void
    {
        $permissions = [
            'page_MyWallet',
            'view_wallet::wallet','view_any_wallet::wallet',
            'view_any_wallet::transaction','view_wallet::transaction'];
        if ($manage)
        {
            $permissions = array_merge($permissions,[
                'create_wallet::wallet',
            ]);
        }
        $this->record->givePermissionTo($permissions);
    }


    public function enableMemberUser(bool $manage = true): void
    {
        $permissions = ['view_user','view_any_user'];
        if ($manage)
        {
            $permissions = array_merge($permissions,['update_user','create_user']);
        }
        $this->record->givePermissionTo($permissions);
    }


    public function enableLocalization(): void
    {
        $permissions = [
            'view_localization::address','view_any_localization::address',
            'create_localization::address','update_localization::address',
            'delete_localization::address'
        ];
        $this->record->givePermissionTo($permissions);
    }





    public function enableShop(bool $manage = true): void
    {
        $this->record->givePermissionTo(['page_MyCart']);
        $this->enableCategory($manage);
        $this->enableProduct($manage);
        $this->enableOrder($manage);
    }




    public function enableCategory(bool $manage = true): void
    {
        $permissions = [
            'view_any_store::category','view_store::category',
        ];
        if ($manage)
        {
            $permissions = array_merge($permissions,[
                'create_store::category',
                'update_store::category',
            ]);
        }
        $this->record->givePermissionTo($permissions);

    }

    public function enableProduct(bool $manage = true): void
    {
        $permissions = ['view_store::product::product','view_any_store::product::product',];
        if ($manage)
        {
            $permissions = array_merge($permissions,[]);
        }
        $this->record->givePermissionTo($permissions);
    }

    public function enableOrder(bool $manage = true): void
    {
        $permissions = ['view_store::order::order','view_any_store::order::order'];
        if ($manage)
        {
            $permissions = array_merge($permissions,[]);
        }
        $this->record->givePermissionTo($permissions);
    }




}
