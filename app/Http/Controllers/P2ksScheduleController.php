<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\P2ksSchedule;
use App\Models\User;
use Carbon\Carbon;

class P2ksScheduleController extends Controller
{
    public function index()
    {
        Carbon::setLocale('id');
        $schedules = P2ksSchedule::with(['user.member.division'])->orderBy('scheduled_date', 'desc')->get();
        $canEdit = auth()->user() && auth()->user()->hasAnyRole(['ADMIN', 'KETUA', 'WAKIL_KETUA', 'SEKRETARIS', 'PEMBINA']);
        
        $twoMonthsAgo = Carbon::now()->subMonths(2);
        // Yang tidak boleh dipilih lagi: yang sudah selesai atau sedang menunggu/hadir dalam 2 bulan terakhir. Batal tidak dihitung.
        $recentScheduledUserIds = P2ksSchedule::where('scheduled_date', '>=', $twoMonthsAgo)
            ->where('status', '!=', 'batal')
            ->pluck('user_id');
            
        // Ambil maksimal 20 user agar wheel tidak penuh
        $eligibleUsers = User::whereNotIn('id', $recentScheduledUserIds)->inRandomOrder()->limit(20)->get();

        // Kelompokkan per bulan, lalu kelompokkan per hari
        $groupedSchedules = $schedules->groupBy(function ($item) {
            return Carbon::parse($item->scheduled_date)->locale('id')->isoFormat('MMMM Y');
        })->map(function ($monthSchedules) {
            return $monthSchedules->groupBy(function ($item) {
                return Carbon::parse($item->scheduled_date)->locale('id')->isoFormat('dddd, D MMMM Y');
            });
        });

        // Ringkasan Statistik
        $stats = [
            'total' => $schedules->count(),
            'menunggu' => $schedules->where('status', 'menunggu')->count(),
            'hadir' => $schedules->where('status', 'hadir')->count(),
            'tidak_hadir' => $schedules->where('status', 'tidak_hadir')->count(),
        ];
        
        return view('p2ks.index', compact('schedules', 'groupedSchedules', 'canEdit', 'eligibleUsers', 'stats'));
    }

    public function spin(Request $request)
    {
        if (!auth()->user() || !auth()->user()->hasAnyRole(['ADMIN', 'KETUA', 'WAKIL_KETUA', 'SEKRETARIS', 'PEMBINA'])) {
            if ($request->ajax()) return response()->json(['error' => 'Unauthorized action.'], 403);
            return back()->with('error', 'Unauthorized action.');
        }

        // Cek limit harian: maksimal 2
        $todayCount = P2ksSchedule::whereDate('scheduled_date', Carbon::today())->count();
        if ($todayCount >= 2) {
            if ($request->ajax()) return response()->json(['error' => 'Maksimal penjadwalan 2 anak per hari sudah tercapai.'], 400);
            return back()->with('error', 'Maksimal 2 anak per hari.');
        }

        $twoMonthsAgo = Carbon::now()->subMonths(2);
        $recentScheduledUserIds = P2ksSchedule::where('scheduled_date', '>=', $twoMonthsAgo)
            ->where('status', '!=', 'batal')
            ->pluck('user_id');
        
        $wheelUserIds = $request->input('user_ids', []);
        
        $eligibleUsersQuery = User::whereNotIn('id', $recentScheduledUserIds);
        if (!empty($wheelUserIds)) {
            $eligibleUsersQuery->whereIn('id', $wheelUserIds);
        }
        $eligibleUsers = $eligibleUsersQuery->get();

        if ($eligibleUsers->isEmpty()) {
            if ($request->ajax()) return response()->json(['error' => 'Semua anggota di roda ini sudah terpilih dalam 2 bulan terakhir.'], 400);
            return back()->with('error', 'Tidak ada anggota valid.');
        }

        $selectedUser = $eligibleUsers->random();

        P2ksSchedule::create([
            'user_id' => $selectedUser->id,
            'scheduled_date' => Carbon::now(),
            'status' => 'menunggu'
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'user' => $selectedUser,
                'message' => 'Anggota terpilih: ' . $selectedUser->name
            ]);
        }

        return back()->with('success', 'Anggota terpilih: ' . $selectedUser->name);
    }

    public function updateStatus(Request $request, P2ksSchedule $schedule)
    {
        if (!auth()->user() || !auth()->user()->hasAnyRole(['ADMIN', 'KETUA', 'WAKIL_KETUA', 'SEKRETARIS', 'PEMBINA'])) {
            return back()->with('error', 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'status' => 'required|in:menunggu,hadir,tidak_hadir,batal'
        ]);
        
        $schedule->update(['status' => $validated['status']]);
        
        return back()->with('success', 'Status piket berhasil diperbarui.');
    }
}
