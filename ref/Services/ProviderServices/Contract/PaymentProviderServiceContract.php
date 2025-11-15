<?php

namespace App\Services\ProviderServices\Contract;

use App\Services\ProviderServices\PaymentService\Contracts\ActionContract\UtilityActionContract;
use Illuminate\Database\Eloquent\Model;

interface PaymentProviderServiceContract
{
    public function getApi();

    public function getModel():Model;
    public function getEnv(): string;
    public function getKey(): string;
    public function getSecret(): string;
    public function getWebhookSecret(): string;
    public function utility(): UtilityActionContract;
}
