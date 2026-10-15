<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\Due;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['due', 'user', 'verifier'])
            ->latest()
            ->get();

        return view('finance.verification', [
            'payments' => $payments,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'due_id' => 'required|exists:dues,id',
            'payment_method' => 'required|in:TRANSFER,QRIS,CASH,EWALLET',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $due = Due::findOrFail($request->due_id);
        $user = Auth::user();

        $path = $request->file('proof')->store('proofs', 'public');

        $method = $request->payment_method;
        if ($method === 'EWALLET') {
            $method = 'TRANSFER';
        }

        Payment::updateOrCreate(
            ['due_id' => $due->id, 'user_id' => $user->id],
            [
                'amount_paid' => $due->amount,
                'status' => 'MENUNGGU_VERIFIKASI',
                'payment_method' => $method,
                'proof_file' => $path,
                'rejection_reason' => null,
            ]
        );

        return redirect()->route('kas.index')->with('success', 'Pembayaran berhasil dikirim dan menunggu verifikasi.');
    }

    public function verify(Request $request, Payment $payment)
    {
        if ($request->has('action')) {
            $mappedStatus = match ($request->action) {
                'setujui' => 'LUNAS',
                'tolak' => 'DITOLAK',
                default => $request->action,
            };
            $request->merge(['status' => $mappedStatus]);
        }

        $request->validate([
            'status' => 'required|in:LUNAS,DITOLAK',
            'rejection_reason' => 'required_if:status,DITOLAK|string|nullable',
        ]);

        DB::transaction(function () use ($request, $payment) {
            $payment->update([
                'status' => $request->status,
                'verified_by_user_id' => Auth::id(),
                'verified_at' => now(),
                'rejection_reason' => $request->status === 'DITOLAK' ? $request->rejection_reason : null,
            ]);

            if ($request->status === 'LUNAS') {
                // Ensure no duplicate transaction exists for this payment
                $exists = CashTransaction::where('payment_id', $payment->id)->exists();
                if (! $exists) {
                    CashTransaction::create([
                        'type' => 'INCOME',
                        'amount' => $payment->amount_paid,
                        'payment_id' => $payment->id,
                        'description' => 'Pembayaran '.$payment->due->title.' oleh '.$payment->user->name,
                        'transaction_date' => now(),
                        'created_by' => Auth::id(),
                    ]);
                }
            } elseif ($request->status === 'DITOLAK') {
                CashTransaction::where('payment_id', $payment->id)->delete();
            }
        });

        return redirect()->route('kas.index')->with('success', 'Status pembayaran berhasil diperbarui.');
    }
}
