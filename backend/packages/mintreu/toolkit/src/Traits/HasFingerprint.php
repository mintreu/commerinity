<?php

namespace Mintreu\Toolkit\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasFingerprint
{

    /**
     * Get a fingerprint (short & safe for URLs/JSON).
     */
    public function fingerprint(?Model $model = null): ?string
    {
        $raw = $this->getRawIdentity($model);

        return $this->secureFingerprint($raw, 16);
    }

    /**
     * Get a full fingerprint (longer, more detailed).
     */
    public function fingerprintFull(?Model $model = null): ?string
    {
        $raw = $this->getRawIdentity($model);

        return $this->secureFingerprint($raw, 64);
    }

    /**
     * Alias → semantic name for full fingerprint.
     */
    public function signature(?Model $model = null): ?string
    {
        return $this->fingerprintFull($model);
    }

    /**
     * Check if the given fingerprint matches this model.
     */
    public function matchesFingerprint(string $fingerprint, ?Model $model = null): bool
    {
        return hash_equals($fingerprint, $this->fingerprint($model))
            || hash_equals($fingerprint, $this->fingerprintFull($model));
    }

    /**
     * Alias for interface compatibility
     */
    public function validateFingerprint(string $fingerprint, ?Model $model = null): bool
    {
        return $this->matchesFingerprint($fingerprint);
    }

    /**
     * Generate a raw identity string before hashing.
     */
    protected function getRawIdentity(?Model $model = null): ?string
    {
        $model = $model ?? $this;
        if (!$model instanceof Model) {
            return null;
        }

        // 1. Explicit property
        if (property_exists($model, 'uniqueId') && !empty($model->uniqueId)) {
            return $model->uniqueId;
        }

        // 2. Common unique columns
        $candidates = ['uuid', 'ulid', 'slug', 'url', 'username', 'email'];
        foreach ($candidates as $column) {
            if (isset($model->{$column}) && !empty($model->{$column})) {
                return (string) $model->{$column};
            }
        }

        // 3. Deterministic fallback
        if (isset($model->id) && isset($model->created_at)) {
            $idPart        = $model->id;
            $createdAtPart = $model->created_at?->timestamp ?? 0;
            $appKeyPart    = substr(hash('sha256', config('app.key')), 0, 12);

            return "{$idPart}_{$createdAtPart}_{$appKeyPart}";
        }

        return null;
    }

    /**
     * Secure the raw identity using deterministic HMAC + Base64-url encoding.
     */
    protected function secureFingerprint(?string $raw, int $length = 32): ?string
    {
        if (is_null($raw)) {
            return null;
        }

        $hash = hash_hmac('sha256', $raw, config('app.key'), true);
        $encoded = rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');

        return substr($encoded, 0, $length);
    }




    /**
     * Return the current model record fingerprint (short).
     */
    public function currentFingerprint(): ?string
    {
        return $this->fingerprint($this); // delegates to existing logic using this model
    }

    /**
     * Return the current model record fingerprint (full).
     */
    public function currentFingerprintFull(): ?string
    {
        return $this->fingerprintFull($this);
    }

    /**
     * Alias for current full fingerprint (semantic).
     */
    public function currentSignature(): ?string
    {
        return $this->currentFingerprintFull();
    }



}
