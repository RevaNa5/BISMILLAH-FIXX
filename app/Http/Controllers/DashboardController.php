<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\Division;
use App\Models\Due;
use App\Models\Event;
use App\Models\Member;
use App\Models\P2ksSchedule;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('name')->toArray();
        $primaryRole = count($roles) > 0 ? $roles[0] : 'USER';

        $isPengurus = count(array_intersect(['ADMIN', 'KETUA', 'WAKIL_KETUA', 'PEMBINA', 'PELATIH', 'SEKRETARIS', 'BENDAHARA'], $roles)) > 0;
        $isFinanceManager = count(array_intersect(['ADMIN', 'KETUA', 'WAKIL_KETUA', 'BENDAHARA', 'PEMBINA'], $roles)) > 0;

        $stats = [];

        // 1. Stats Organisasi untuk Pimpinan & Pengurus
        if ($isPengurus) {
            $stats['total_members'] = Member::count();
            $stats['total_divisions'] = Division::count();
            $stats['active_events'] = Event::where('status', 'OPEN')->count();
        }

        // 2. Stats Keuangan Kas & Tagihan untuk Pengurus Keuangan (Admin, Ketua, Bendahara, Pembina)
        if ($isFinanceManager) {
            $income = CashTransaction::where('type', 'INCOME')->sum('amount');
            $expense = CashTransaction::where('type', 'EXPENSE')->sum('amount');
            $stats['cash_balance'] = $income - $expense;
            $stats['total_verified_funds'] = $income;
            $stats['total_dues_count'] = Due::count();
            $stats['verified_payments_count'] = Payment::where('status', 'LUNAS')->count();
            $stats['pending_payments_count'] = Payment::where('status', 'MENUNGGU_VERIFIKASI')->count();

            // Total target tagihan & nominal tagihan
            $allDues = Due::all();
            $totalMembersCount = Member::where('status', 'AKTIF')->count();
            $totalTargetBills = 0;
            $totalTargetNominal = 0;

            foreach ($allDues as $due) {
                if ($due->target_type === 'DIVISI' && $due->target_division_id) {
                    $targetCount = Member::where('division_id', $due->target_division_id)->where('status', 'AKTIF')->count();
                } elseif ($due->target_type === 'INTI') {
                    $targetCount = User::role(['KETUA', 'WAKIL_KETUA', 'SEKRETARIS', 'BENDAHARA'])->count();
                } else {
                    $targetCount = $totalMembersCount;
                }
                $totalTargetBills += $targetCount;
                $totalTargetNominal += ($targetCount * $due->amount);
            }

            $stats['total_target_bills'] = $totalTargetBills;
            $stats['total_target_nominal'] = $totalTargetNominal;
            $stats['unpaid_bills_count'] = max(0, $totalTargetBills - $stats['verified_payments_count'] - $stats['pending_payments_count']);
            $stats['unpaid_dues_nominal'] = max(0, $totalTargetNominal - $income);
            $stats['recent_cash_transactions'] = CashTransaction::with(['creator', 'payment.user'])
                ->latest('transaction_date')
                ->latest('id')
                ->take(5)
                ->get();
        }

        // 3. Stats & Riwayat Personal Anggota (Untuk semua anggota termasuk pengurus saat melihat tagihan pribadinya)
        $userMember = $user->member;
        $userDivisionId = $userMember?->division_id;
        $isUserInti = $user->hasRole(['KETUA', 'WAKIL_KETUA', 'SEKRETARIS', 'BENDAHARA']);

        $applicableDues = Due::all()->filter(function ($due) use ($userDivisionId, $isUserInti) {
            if ($due->target_type === 'DIVISI') {
                return $userDivisionId && $due->target_division_id == $userDivisionId;
            }
            if ($due->target_type === 'INTI') {
                return $isUserInti;
            }

            return true;
        });

        $userPayments = $user->payments()->with('due')->get()->keyBy('due_id');

        $myTotalDuesCount = $applicableDues->count();
        $myPaidCount = 0;
        $myPendingCount = 0;
        $myUnpaidNominal = 0;

        $myActiveDuesList = [];
        foreach ($applicableDues as $due) {
            $payment = $userPayments->get($due->id);
            $status = $payment ? $payment->status : 'BELUM BAYAR';

            if ($status === 'LUNAS') {
                $myPaidCount++;
            } elseif ($status === 'MENUNGGU_VERIFIKASI') {
                $myPendingCount++;
            } else {
                $myUnpaidNominal += $due->amount;
            }

            $myActiveDuesList[] = [
                'due' => $due,
                'status' => $status,
                'payment' => $payment,
            ];
        }

        $stats['my_total_dues'] = $myTotalDuesCount;
        $stats['my_paid_dues'] = $myPaidCount;
        $stats['my_pending_dues'] = $myPendingCount;
        $stats['my_unpaid_dues'] = max(0, $myTotalDuesCount - $myPaidCount - $myPendingCount);
        $stats['my_unpaid_nominal'] = $myUnpaidNominal;
        $stats['my_recent_payments'] = $user->payments()->with('due')->latest()->take(5)->get();

        // Stats Event & Kehadiran Personal
        $stats['my_events'] = 0;
        if (Schema::hasTable('event_registrations')) {
            $stats['my_events'] = \DB::table('event_registrations')->where('user_id', $user->id)->count();
        }

        $stats['attendance_rate'] = '100%';
        if (Schema::hasTable('attendances')) {
            $totalAtt = \DB::table('attendances')->where('user_id', $user->id)->count();
            if ($totalAtt > 0) {
                $hadir = \DB::table('attendances')->where('user_id', $user->id)->whereIn('status', ['hadir', 'HADIR'])->count();
                $stats['attendance_rate'] = round(($hadir / $totalAtt) * 100).'%';
            }
        }

        // Jadwal P2KS Aktif yang Belum Dikonfirmasi Selesai
        $todayP2ks = P2ksSchedule::where('user_id', $user->id)
            ->whereDate('scheduled_date', '<=', Carbon::today())
            ->where('status', 'menunggu')
            ->orderBy('scheduled_date', 'desc')
            ->first();

        // Common data for everyone
        $upcomingEvents = Event::where('status', 'OPEN')->orderBy('date', 'asc')->take(5)->get();

        return view('dashboard', [
            'roles' => $roles,
            'primaryRole' => $primaryRole,
            'isPengurus' => $isPengurus,
            'isFinanceManager' => $isFinanceManager,
            'stats' => $stats,
            'upcomingEvents' => $upcomingEvents,
            'todayP2ks' => $todayP2ks,
            'myActiveDuesList' => $myActiveDuesList,
        ]);
    }
}
