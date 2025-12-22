<?php

namespace Mintreu\Toolkit\Support;

trait HasErrors
{

    protected array $errors = [];

    public function setError(string $error_text):void
    {
        $this->errors[] = $error_text;
    }

    public function getErrors():array
    {
        return $this->errors;
    }

    public function getError():?string
    {
        return count($this->errors) ? $this->errors[0] : null;
    }

}
