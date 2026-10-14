<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Member;
use App\Models\Division;
use App\Models\Event;
use App\Models\CashTransaction;
use App\Models\Due;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('name')->toArray();
        $primaryRole = count($roles) > 0 ? $roles[0] : 'USER';

        $stats = [];

        // Stats untuk Pimpinan & Pengurus
        if (array_intersect(['ADMIN', 'KETUA', 'PEMBINA', 'SEKRETARIS'], $roles)) {
            $stats['total_members'] = Member::count();
            $stats['total_divisions'] = Division::count();
            $stats['active_events'] = Event::where('status', 'OPEN')->count();
        }

        // Stats Keuangan untuk Bendahara & Ketua
        if (array_intersect(['ADMIN', 'BENDAHARA', 'KETUA'], $roles)) {
            // Kita coba cek apakah tabel CashTransaction ada
            if (\Illuminate\Support\Facades\Schema::hasTable('cash_transactions')) {
                $income = CashTransaction::where('type', 'INCOME')->sum('amount');
                $expense = CashTransaction::where('type', 'EXPENSE')->sum('amount');
                $stats['cash_balance'] = $income - $expense;
            } else {
                $stats['cash_balance'] = 450000; // Mockup fallback
            }
        }

        // Stats Personal untuk Anggota Biasa
        $stats['my_events'] = 0; 
        if (\Illuminate\Support\Facades\Schema::hasTable('event_registrations')) {
            $stats['my_events'] = \DB::table('event_registrations')->where('user_id', $user->id)->count();
        }

        $stats['my_unpaid_dues'] = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('dues') && \Illuminate\Support\Facades\Schema::hasTable('payments')) {
            $totalDues = Due::count();
            $paidDues = $user->payments()->where('status', 'LUNAS')->count();
            $stats['my_unpaid_dues'] = max(0, $totalDues - $paidDues);
        }

        // Jadwal P2KS Aktif yang Belum Dikonfirmasi Selesai
        $todayP2ks = \App\Models\P2ksSchedule::where('user_id', $user->id)
            ->whereDate('scheduled_date', '<=', \Carbon\Carbon::today())
            ->where('status', 'menunggu')
            ->orderBy('scheduled_date', 'desc')
            ->first();

        // Common data for everyone
        $upcomingEvents = Event::where('status', 'OPEN')->orderBy('date', 'asc')->take(5)->get();

        return view('dashboard', [
            'roles' => $roles,
            'primaryRole' => $primaryRole,
            'stats' => $stats,
            'upcomingEvents' => $upcomingEvents,
            'todayP2ks' => $todayP2ks
        ]);
    }
}
