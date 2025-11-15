<?php

namespace App\Services\PrivilegeService;

interface PrivilegeServiceContract
{


    public function afterRegister():void;

    public function afterOnboarding():void;



    public function afterSubscribe():void;



}
