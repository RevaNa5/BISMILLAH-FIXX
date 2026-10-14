<?php

namespace App\Http\Controllers;

use App\Models\Due;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DueController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isFinanceManager = $user->hasRole(['ADMIN', 'KETUA', 'BENDAHARA']);

        $dues = Due::with('period')->latest()->get();
        
        // Fetch user's payments
        $myPayments = Payment::where('user_id', $user->id)->get()->keyBy('due_id');

        $formattedDues = $dues->map(function ($due) use ($myPayments) {
            $payment = $myPayments->get($due->id);
            return [
                'id' => $due->id,
                'title' => $due->title,
                'amount' => $due->amount,
                'type' => $due->type,
                'status' => $payment ? $payment->status : 'BELUM BAYAR',
                'payment_id' => $payment ? $payment->id : null,
                'rejection_reason' => $payment ? $payment->rejection_reason : null,
                'created_at' => $due->created_at,
            ];
        });

        return view('finance.index', [
            'dues' => $formattedDues,
            'canManageFinance' => $isFinanceManager,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:BULANAN,KEGIATAN,KHUSUS',
        ]);

        Due::create([
            'title' => $request->title,
            'amount' => $request->amount,
            'type' => $request->type,
        ]);

        return redirect()->back()->with('success', 'Tagihan Kas berhasil dibuat.');
    }
}
