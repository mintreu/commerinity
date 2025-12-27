<?php

declare(strict_types=1);

namespace App\Services\Recruitment;

use App\Casts\JobApplicationStatusCast;
use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Contracts\Services\JobApplicationServiceInterface;
use App\Models\Address;
use App\Models\Recruitment\JobApplication;
use App\Models\Recruitment\Recruitment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Payment\DTOs\PaymentInitiateRequest;
use App\Services\Payment\PaymentService;
use App\Services\Wallet\UserWalletService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Service class for handling job applications.
 *
 * Manages the complete application lifecycle including:
 * - Application creation and submission
 * - Payment processing for paid recruitments
 * - Application status management
 */
final class JobApplicationService implements JobApplicationServiceInterface
{
    private User $applicant;

    private Recruitment $recruitment;

    /** @var array<string, mixed> */
    private array $formData = [];

    private ?JobApplication $application = null;

    private ?Transaction $transaction = null;

    private ?Address $address = null;

    private ?string $error = null;

    /** @var array<string, string> */
    private array $errors = [];

    private ?string $returnUrl = null;

    private function __construct(Recruitment $recruitment)
    {
        $this->recruitment = $recruitment;
    }

    /**
     * Create a new service instance for a recruitment.
     */
    public static function make(Recruitment $recruitment): self
    {
        return new self($recruitment);
    }

    /**
     * Set the applicant (user) for the application.
     */
    public function forUser(User $user): self
    {
        $this->applicant = $user;
        $this->applicant->load(['addresses']);

        return $this;
    }

    /**
     * Process the job application.
     *
     * @param  array<string, mixed>  $data  Form data including:
     *                                      - guardian_name (required)
     *                                      - address_id (optional, uses default if not provided)
     *                                      - educations (optional array)
     *                                      - skills (optional array)
     *                                      - experiences (optional array)
     *                                      - reference_name (optional)
     *                                      - reference_contact (optional)
     */
    public function apply(array $data = []): self
    {
        $this->formData = $data;
        $this->processApplication();

        return $this;
    }

    /**
     * Check if the application was successful.
     */
    public function successful(): bool
    {
        return $this->error === null && $this->application !== null;
    }

    /**
     * Check if the application failed.
     */
    public function failed(): bool
    {
        return $this->error !== null;
    }

    /**
     * Get the error message if any.
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Get all errors.
     *
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the created/found application.
     */
    public function getApplication(): ?JobApplication
    {
        return $this->application;
    }

    /**
     * Get the transaction if payment is required.
     */
    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    /**
     * Get the return URL for payment redirect.
     */
    public function getReturnUrl(): ?string
    {
        return $this->returnUrl;
    }

    /**
     * Check if payment is required.
     */
    public function requiresPayment(): bool
    {
        return $this->recruitment->is_payable && ! $this->application?->is_paid;
    }

    /**
     * Get the result as an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->successful(),
            'recruitment' => $this->recruitment->only(['id', 'uuid', 'slug', 'title', 'fees', 'is_payable']),
            'application' => $this->application,
            'requires_payment' => $this->requiresPayment(),
            'payment_url' => $this->returnUrl,
            'transaction' => $this->transaction,
            'error' => $this->error,
            'errors' => $this->errors,
        ];
    }

    /**
     * Submit an existing application after payment.
     */
    public function submitAfterPayment(JobApplication $application, Transaction $transaction): JobApplication
    {
        $application->loadMissing(['recruitment', 'transaction']);

        $application->update([
            'is_paid' => true,
            'transaction_id' => $transaction->id,
            'status' => JobApplicationStatusCast::Submitted,
            'submitted_at' => now(),
        ]);

        return $application->fresh();
    }

    /**
     * Get all applications for a user.
     */
    public static function getUserApplications(User $user): Collection
    {
        return $user->jobApplications()
            ->with(['recruitment:id,uuid,slug,title,role,employment_type,status'])
            ->latest()
            ->get();
    }

    /**
     * Find a user's application by UUID.
     */
    public static function findUserApplication(User $user, string $uuid): ?JobApplication
    {
        return $user->jobApplications()
            ->where('uuid', $uuid)
            ->with(['recruitment', 'address', 'transaction'])
            ->first();
    }

    /**
     * Withdraw an application.
     */
    public static function withdraw(JobApplication $application, ?string $reason = null): JobApplication
    {
        if (! $application->can_withdraw) {
            throw new RuntimeException('This application cannot be withdrawn.');
        }

        $application->withdraw($reason);

        return $application->fresh();
    }

    /**
     * Initiate payment for an application awaiting payment.
     *
     * @param  string  $paymentMethodString  Payment method (cashfree, razorpay)
     * @return array{success: bool, checkout_url?: string, transaction_uuid?: string, message?: string}
     */
    public static function initiatePayment(
        JobApplication $application,
        User $user,
        string $paymentMethodString
    ): array {
        $paymentMethod = PaymentMethodCast::from($paymentMethodString);

        return DB::transaction(function () use ($application, $user, $paymentMethod) {
            $application->loadMissing(['recruitment']);

            // Get wallet (required for transaction)
            $walletService = app(UserWalletService::class);
            $wallet = $walletService->getOrCreateWallet($user);

            $clientBaseUrl = config('app.client_url', config('app.frontend_url', config('app.url')));
            $successUrl = "{$clientBaseUrl}/career/applications/{$application->uuid}?payment=success";
            $failureUrl = "{$clientBaseUrl}/career/applications/{$application->uuid}?payment=failed";

            // Create transaction record
            $transaction = Transaction::create([
                'uuid' => 'TXN-'.Str::upper(Str::random(12)),
                'wallet_id' => $wallet->id,
                'transactionable_type' => JobApplication::class,
                'transactionable_id' => $application->id,
                'type' => TransactionTypeCast::DEBIT,
                'status' => TransactionStatusCast::PENDING,
                'amount' => $application->amount,
                'currency' => 'INR',
                'payment_method' => $paymentMethod,
                'purpose' => 'Job Application Fee',
                'description' => "{$user->name} - Application for {$application->recruitment->title}",
                'expires_at' => now()->addMinutes(60),
                'is_verified' => false,
                'metadata' => [
                    'customer' => [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'mobile' => $user->mobile,
                    ],
                    'redirect_success_url' => $successUrl,
                    'redirect_failure_url' => $failureUrl,
                    'application_uuid' => $application->uuid,
                ],
            ]);

            // Use PaymentService to initiate payment
            $paymentService = app(PaymentService::class);

            $paymentRequest = new PaymentInitiateRequest(
                amountInPaisa: $application->amount,
                currency: 'INR',
                method: $paymentMethod,
                userId: $user->id,
                walletId: $wallet->id,
                transactionId: $transaction->uuid,
                customerName: $user->name,
                customerEmail: $user->email,
                customerPhone: $user->mobile,
                purpose: 'Job Application Fee',
                description: $transaction->description,
                metadata: $transaction->metadata ?? [],
                callbackUrl: $successUrl,
                expiresInMinutes: 60
            );

            try {
                $paymentResponse = $paymentService->initiate($paymentRequest);

                if ($paymentResponse->success || $paymentResponse->status === 'pending') {
                    $transaction->update([
                        'provider_order_id' => $paymentResponse->providerOrderId,
                        'provider_transaction_id' => $paymentResponse->providerTransactionId,
                        'checkout_url' => $paymentResponse->checkoutUrl,
                        'status' => $paymentResponse->getStatusEnum(),
                        'provider_response' => $paymentResponse->metadata,
                    ]);

                    return [
                        'success' => true,
                        'checkout_url' => route('checkout.show', ['transaction' => $transaction->uuid]),
                        'transaction_uuid' => $transaction->uuid,
                    ];
                }

                // Failed to initiate payment
                $transaction->update([
                    'status' => TransactionStatusCast::FAILED,
                    'provider_response' => $paymentResponse->metadata,
                ]);

                return [
                    'success' => false,
                    'message' => $paymentResponse->message ?? 'Failed to initiate payment.',
                ];
            } catch (\Exception $e) {
                $transaction->update([
                    'status' => TransactionStatusCast::FAILED,
                    'provider_response' => ['error' => $e->getMessage()],
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to initiate payment: '.$e->getMessage(),
                ];
            }
        });
    }

    // ========================================
    // Private Methods
    // ========================================

    private function processApplication(): void
    {
        // Validate applicant
        if (! isset($this->applicant)) {
            $this->setError('Applicant not found.');

            return;
        }

        // Validate recruitment is open
        if (! $this->recruitment->is_open) {
            $this->setError('This recruitment is no longer accepting applications.');

            return;
        }

        // Check for existing application
        $existing = $this->applicant->jobApplications()
            ->where('recruitment_id', $this->recruitment->id)
            ->first();

        if ($existing) {
            $this->setError('You have already applied for this position. Application ID: '.$existing->uuid);
            $this->application = $existing;

            return;
        }

        // Validate address
        $this->resolveAddress();
        if ($this->error) {
            return;
        }

        // Create application
        $this->createApplication();

        // Handle payment if required
        if ($this->recruitment->is_payable) {
            $this->handlePayment();
        } else {
            // Free application - submit directly
            $this->application->update([
                'status' => JobApplicationStatusCast::Submitted,
                'submitted_at' => now(),
            ]);
        }
    }

    private function resolveAddress(): void
    {
        // Use provided address or default
        $addressId = $this->formData['address_id'] ?? null;

        if ($addressId) {
            $this->address = $this->applicant->addresses()
                ->where('id', $addressId)
                ->first();
        } else {
            // Use default address
            $this->address = $this->applicant->addresses()
                ->where('default', true)
                ->first()
                ?? $this->applicant->addresses()->first();
        }

        if (! $this->address) {
            $this->setError('Please add an address to your profile before applying.');
        }
    }

    private function createApplication(): void
    {
        $this->application = $this->applicant->jobApplications()->create([
            'uuid' => 'APP-'.now()->format('ym').'-'.strtoupper(Str::random(8)),
            'recruitment_id' => $this->recruitment->id,
            'guardian_name' => $this->formData['guardian_name'] ?? '',
            'address_id' => $this->address?->id,
            'educations' => $this->formData['educations'] ?? null,
            'skills' => $this->formData['skills'] ?? null,
            'experiences' => $this->formData['experiences'] ?? null,
            'reference_name' => $this->formData['reference_name'] ?? null,
            'reference_contact' => $this->formData['reference_contact'] ?? null,
            'amount' => $this->recruitment->fees,
            'status' => JobApplicationStatusCast::Draft,
        ]);
    }

    private function handlePayment(): void
    {
        // Set application to awaiting payment
        $this->application->update([
            'status' => JobApplicationStatusCast::AwaitingPayment,
        ]);

        // Generate payment URL
        $clientBaseUrl = config('app.client_url', config('app.url'));
        $this->returnUrl = "{$clientBaseUrl}/career/applications/{$this->application->uuid}/pay";
    }

    private function setError(string $message): void
    {
        $this->error = $message;
        $this->errors['general'] = $message;
    }
}
