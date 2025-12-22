<?php

namespace App\Http\Controllers\Api\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Mintreu\LaravelTransaction\Casts\TransactionStatusCast;
use Mintreu\LaravelTransaction\Events\TransactionConfirmed;
use Mintreu\LaravelTransaction\Events\TransactionFailed;
use Mintreu\LaravelTransaction\LaravelTransaction;
use Mintreu\LaravelTransaction\Models\Transaction;

class TransactionActionController extends Controller
{

    public function confirmTransaction(Transaction $transaction,Request $request): RedirectResponse
    {
        $validated = LaravelTransaction::make($transaction)->callback($request)->validate();

        //dd($transaction->redirectOnSuccess(),$transaction->redirectOnFailure(),$transaction);

        if ($validated)
        {
            $transaction->update([
                'verified' => true,
                'status'  => TransactionStatusCast::COMPLETED
            ]);
            event(new TransactionConfirmed($transaction));
            return redirect()->to($transaction->redirectOnSuccess());
        }else{
            event(new TransactionFailed($transaction));
            return redirect()->to($transaction->redirectOnFailure());
        }
    }


    public function failureTransaction(Transaction $transaction,Request $request): RedirectResponse
    {
        //LaravelTransaction::make($transaction)->callback($request)->failed();
        $transaction->update([
            'status'  => TransactionStatusCast::FAILED
        ]);
        event(new TransactionFailed($transaction));
        return redirect()->to($transaction->redirectOnFailure());
    }

}
