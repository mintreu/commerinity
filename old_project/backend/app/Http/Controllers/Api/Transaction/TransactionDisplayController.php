<?php

namespace App\Http\Controllers\Api\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Resources\Transaction\TransactionResource;
use App\Models\Lifecycle\UserSubscription;
use App\Models\Order\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mintreu\LaravelTransaction\Casts\TransactionStatusCast;
use Mintreu\LaravelTransaction\Casts\TransactionTypeCast;
use Mintreu\LaravelTransaction\Models\Transaction;

class TransactionDisplayController extends Controller
{
    /**
     * Transaction Index Method With Filter And Sort
     * Fetches all user transactions from orders, memberships, and optionally wallet
     *
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request): JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $user = $request->user();
        $wallet = $user->wallet;

        // Get IDs using query builder (safe from null issues)
        $orderIds = $user->orders()->pluck('id')->all();
        $membershipIds = $user->membership()->pluck('id')->all();

        // Build query with all conditions
        $query = Transaction::where(function ($q) use ($wallet, $orderIds, $membershipIds) {
            $hasCondition = false;

            // Transactions by wallet_id (if wallet exists)
            if ($wallet) {
                $q->where('wallet_id', $wallet->id);
                $hasCondition = true;
            }

            // Transactions polymorphic to orders
            if (!empty($orderIds)) {
                if ($hasCondition) {
                    $q->orWhere(function ($sub) use ($orderIds) {
                        $sub->where('transactionable_type', Order::class)
                            ->whereIn('transactionable_id', $orderIds);
                    });
                } else {
                    $q->where('transactionable_type', Order::class)
                        ->whereIn('transactionable_id', $orderIds);
                    $hasCondition = true;
                }
            }

            // Transactions polymorphic to membership
            if (!empty($membershipIds)) {
                if ($hasCondition) {
                    $q->orWhere(function ($sub) use ($membershipIds) {
                        $sub->where('transactionable_type', UserSubscription::class)
                            ->whereIn('transactionable_id', $membershipIds);
                    });
                } else {
                    $q->where('transactionable_type', UserSubscription::class)
                        ->whereIn('transactionable_id', $membershipIds);
                    $hasCondition = true;
                }
            }

            // Transactions polymorphic to wallet (if wallet exists)
            if ($wallet) {
                $q->orWhere(function ($sub) use ($wallet) {
                    $sub->where('transactionable_type', get_class($wallet))
                        ->where('transactionable_id', $wallet->id);
                });
            }

            // If no conditions, return empty result
            if (!$hasCondition) {
                $q->whereRaw('1 = 0');
            }
        })
            ->distinct()
            ->with('integration');

        // Apply type filter
        if ($type = $request->get('type')) {
            $validTypes = array_map(fn($case) => $case->value, TransactionTypeCast::cases());
            if ($type !== 'all' && in_array($type, $validTypes, true)) {
                $query->where('type', $type);
            }
        }

        // Apply status filter
        if ($status = $request->get('status')) {
            $validStatuses = array_map(fn($case) => $case->value, TransactionStatusCast::cases());
            if ($status !== 'all' && in_array($status, $validStatuses, true)) {
                $query->where('status', $status);
            }
        }

        // Apply sorting
        $sortColumn = $request->get('sort', 'created_at');
        $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedColumns = ['created_at', 'updated_at', 'amount', 'status', 'type', 'expire_at'];
        $query->orderBy(in_array($sortColumn, $allowedColumns) ? $sortColumn : 'created_at', $direction);

        // Paginate
        $perPage = min(max((int) $request->get('per_page', 10), 1), 100);
        $transactions = $query->paginate($perPage);

        return TransactionResource::collection($transactions);
    }



    public function show(Transaction $transaction,Request $request)
    {
        $transaction->load([
            'integration',
            'transactionable'
        ]);
        return TransactionResource::make($transaction);
    }



    public function sendInvoiceToEmail(Transaction $transaction,Request $request)
    {
        $transaction->load([
            'integration',
            'transactionable',
            'transactionable.customer',     // for order, membership etc.
            'transactionable.walletable',  // only for wallet transaction when transactionable is wallet itself
        ]);


        $pdf = Pdf::loadView('invoices.transaction_invoice',[
            'logo' => asset('images/logo.png'),
            'company' => [
              'name' => config('app.name'),
              'email' => config('app.mail'),
            ],
            'transaction' => $transaction
        ])->setPaper('a4')->setWarnings(false);

        return $pdf->stream();

    }



}
