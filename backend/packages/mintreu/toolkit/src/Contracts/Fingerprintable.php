<?php

namespace Mintreu\Toolkit\Contracts;

interface Fingerprintable
{
    public function fingerprint(): ?string;
    public function fingerprintFull(): ?string;
    public function validateFingerprint(string $fingerprint): bool;
}
