<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\Auth\UserBannedException;
use App\Exceptions\Auth\UserSuspendedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\UserServices\UserAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LoginController extends Controller
{
    public function __construct(
        private readonly UserAuthService $authService
    ) {}

    /**
     * Login for Nuxt frontend (web)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credential = $request->input('mobile') ?? $request->input('email');
            $tokenObject = null;

            // Authenticate with password or OTP
            if ($request->filled('password')) {
                $tokenObject = $this->authService->loginWithPassword(
                    $credential,
                    $request->input('password'),
                    'nuxt'
                );
            } elseif ($request->filled('otp')) {
                $tokenObject = $this->authService->loginWithOtp(
                    $credential,
                    $request->input('otp'),
                    'nuxt'
                );
            }

            if (! $tokenObject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            // Return response compatible with @qirolab/nuxt-sanctum-authentication (token mode)
            return response()->json([
                'token' => $tokenObject->plainTextToken,
            ]);
        } catch (UserBannedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (UserSuspendedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login for mobile devices (Android/iOS) - requires device_type
     */
    public function loginMobile(LoginRequest $request): JsonResponse
    {
        try {
            $credential = $request->input('mobile') ?? $request->input('email');
            $deviceType = $request->input('device_type'); // android or ios (required)

            if (! $deviceType) {
                return response()->json([
                    'success' => false,
                    'message' => 'device_type is required (android or ios)',
                ], 422);
            }

            $tokenObject = null;

            // Authenticate with password or OTP
            if ($request->filled('password')) {
                $tokenObject = $this->authService->loginWithPassword(
                    $credential,
                    $request->input('password'),
                    $deviceType
                );
            } elseif ($request->filled('otp')) {
                $tokenObject = $this->authService->loginWithOtp(
                    $credential,
                    $request->input('otp'),
                    $deviceType
                );
            }

            if (! $tokenObject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            return response()->json([
                'token' => $tokenObject->plainTextToken,
            ]);
        } catch (UserBannedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (UserSuspendedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout from current device
     */
    public function logout(Request $request): JsonResponse
    {
        // Check if user is authenticated
        if ($request->user()) {
            $this->authService->logout($request);
        }

        // Always return success even if not authenticated
        // This ensures frontend can clear local state
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request);

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices',
        ]);
    }

    /**
     * Logout from all devices of specific type (android, ios, nuxt)
     */
    public function logoutDeviceType(Request $request): JsonResponse
    {
        $deviceType = $request->input('device_type', 'nuxt');
        $this->authService->logoutDeviceType($request, $deviceType);

        return response()->json([
            'success' => true,
            'message' => "Logged out from all {$deviceType} devices",
        ]);
    }
}
