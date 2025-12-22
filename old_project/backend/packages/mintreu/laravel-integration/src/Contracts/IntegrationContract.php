<?php

namespace Mintreu\LaravelIntegration\Contracts;

interface IntegrationContract
{

    public function getIntegration();

    public function getModel();

    public function setError(string $error_text): void;

    public function getError(): ?string;

}
