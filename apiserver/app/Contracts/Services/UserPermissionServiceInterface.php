<?php

declare(strict_types=1);

namespace App\Contracts\Services;

/**
 * UserPermissionServiceInterface
 *
 * Contract for user permission services.
 * Implementation uses constructor injection with User.
 *
 * Usage:
 * ```php
 * $permissions = UserPermissionService::for($user);
 * $permissions->hasPermission('wallet.send');
 * $permissions->getPermissions();
 * ```
 */
interface UserPermissionServiceInterface
{
    /**
     * Get all permissions for the user.
     *
     * @return array<string> List of permission strings
     */
    public function getPermissions(): array;

    /**
     * Check if user has specific permission.
     */
    public function hasPermission(string $permission): bool;

    /**
     * Check if user has any of the given permissions.
     *
     * @param  array<string>  $permissions
     */
    public function hasAnyPermission(array $permissions): bool;

    /**
     * Check if user has all of the given permissions.
     *
     * @param  array<string>  $permissions
     */
    public function hasAllPermissions(array $permissions): bool;

    /**
     * Get permissions as array for API response.
     * Format compatible with Spatie permission output.
     *
     * @return array{
     *   permissions: array<string>,
     *   can: array<string, bool>
     * }
     */
    public function toArray(): array;

    /**
     * Get permission flags for common checks.
     *
     * @return array<string, bool>
     */
    public function getPermissionFlags(): array;
}
