@extends('layouts.admin')

@section('title', 'Dashboard - PIK-R SKAKMATT')

@php
    $roles = Auth::user()->roles->pluck('name')->toArray();
    $primaryRole = count($roles) > 0 ? $roles[0] : 'USER';
@endphp

@section('header', 'Dashboard ' . $primaryRole)

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <!-- Welcome Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between bg-[#121B2D]/80 backdrop-blur-xl rounded-3xl p-8 shadow-[0_10px_40px_rgba(0,0,0,0.5)] border border-cyan-500/20 relative overflow-hidden group hover:shadow-[0_15px_50px_rgba(6,182,212,0.1)] transition-all duration-500">
        <div class="absolute top-0 right-0 w-64 h-64 bg-cyan-600/20 rounded-full blur-[80px] -mr-20 -mt-20 pointer-events-none group-hover:scale-110 transition-transform duration-700"></div>
        <div class="relative z-10">
            <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-2 tracking-tight drop-shadow-[0_0_8px_rgba(255,255,255,0.2)]">
                Halo, <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 to-yellow-500 drop-shadow-[0_0_10px_rgba(252,211,77,0.4)]">{{ Auth::user()->name }}</span>! 👋
            </h1>
            <p class="text-cyan-100/70 text-lg">Selamat datang kembali di dashboard PIK-R SKAKMATT.</p>
        </div>
        
        <div class="mt-6 md:mt-0 relative z-10 flex gap-3">
            <a href="{{ route('feature.task_board') }}" class="px-5 py-2.5 bg-[#070b19] border border-cyan-500/30 text-cyan-400 font-bold rounded-xl hover:bg-cyan-500/10 hover:border-cyan-400 hover:shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-all duration-300 shadow-sm flex items-center gap-2 group">
                <svg class="w-5 h-5 text-cyan-600/50 group-hover:text-cyan-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                Task Board
            </a>
        </div>
    </div>

    @php
        $now = \Carbon\Carbon::now();
        $hour = $now->hour;
        // Tampil dari jam 07:00 pagi
        $isAnnouncementTime = $hour >= 7;
        // Tampil form absen khusus jam 16:00 - 17:00 (Saya paksa TRUE agar Anda bisa lihat bentuk absennya malam ini)
        $isAttendanceTime = true;
    @endphp

    @php
        $now = \Carbon\Carbon::now();
        $hour = $now->hour;
        // Tampil dari jam 07:00 pagi
        $isAnnouncementTime = $hour >= 7;
        // Tampil form absen khusus jam 16:00 - 17:00 (Saya paksa muncul sekarang agar bisa dites)
        $isAttendanceTime = true;
    @endphp

    @if($isAnnouncementTime)
    <!-- Forum Announcement / Attendance Banner -->
    <div x-data="{ showForm: false, submitted: false }" class="relative bg-gradient-to-r from-blue-900/40 to-indigo-900/40 border border-blue-500/50 rounded-3xl p-6 shadow-[0_10px_40px_rgba(59,130,246,0.15)] overflow-hidden group transition-all duration-300">
        <div class="absolute inset-0 bg-blue-500/10 blur-[50px] rounded-full pointer-events-none group-hover:bg-blue-500/20 transition-colors"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="flex items-start gap-5 w-full">
                <div class="w-14 h-14 rounded-2xl bg-blue-500/20 border border-blue-500/30 flex items-center justify-center text-blue-400 shrink-0 shadow-[0_0_15px_rgba(59,130,246,0.3)]">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                </div>
                <div class="w-full">
                    
                    <!-- Kondisi 1: Hanya Pengumuman (Sebelum Jam Absen) -->
                    @if(!$isAttendanceTime)
                        <h3 class="text-xl md:text-2xl font-bold text-white tracking-tight flex items-center gap-2 mb-2">
                            Pengumuman Forum Rutin
                        </h3>
                        <p class="text-blue-100/70 text-sm md:text-base leading-relaxed">
                            Jangan lupa, hari ini akan ada <strong>Forum Rutin</strong> pada pukul <strong>16.00 - 17.00 WIB</strong>. Persiapkan diri Anda! Tombol presensi akan otomatis muncul di sini pada jam tersebut.
                        </p>
                    @else
                        <!-- Kondisi 2: Saat Jam Absen -->
                        
                        <!-- Tampilan Awal: Notifikasi & Tombol Buka -->
                        <div x-show="!showForm && !submitted" x-transition.opacity>
                            <h3 class="text-xl md:text-2xl font-bold text-white tracking-tight flex items-center gap-2 mb-2">
                                Pengumuman Forum Rutin
                                <span class="px-2 py-1 bg-red-500/20 text-red-400 text-xs rounded-full border border-red-500/30 animate-pulse">LIVE ABSENSI</span>
                            </h3>
                            <p class="text-blue-100/70 text-sm mb-4">
                                Waktu presensi forum telah dibuka! Segera isi kehadiran Anda sebelum jam 17.00.
                            </p>
                            <button @click="showForm = true" class="px-5 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-400 hover:to-indigo-400 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                Isi Presensi Sekarang
                            </button>
                        </div>
                        
                        <!-- Tampilan Form Absensi (Terbuka saat diklik) -->
                        <div x-show="showForm && !submitted" style="display: none;" x-transition.opacity>
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-white">Form Presensi Forum</h3>
                                    <p class="text-blue-100/70 text-xs mt-1">Silakan isi data kehadiran dengan jujur.</p>
                                </div>
                                <button @click="showForm = false" class="text-gray-400 hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                            
                            <form @submit.prevent="submitted = true; showForm = false" class="bg-[#0B1221]/50 border border-blue-500/20 rounded-xl p-5 w-full relative">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                                    <label class="cursor-pointer relative block">
                                        <input type="radio" name="status" value="hadir" class="peer sr-only" checked>
                                        <div class="h-full p-4 rounded-xl border border-gray-700 bg-gray-800/50 text-center text-gray-400 peer-checked:bg-emerald-500/20 peer-checked:border-emerald-500 peer-checked:text-emerald-400 peer-checked:ring-2 peer-checked:ring-emerald-500/30 peer-checked:scale-[1.02] transition-all duration-300 hover:bg-gray-700/80 shadow-sm">
                                            <div class="text-2xl mb-1">✅</div>
                                            <div class="font-bold">Hadir</div>
                                        </div>
                                        <!-- Active Checkmark Indicator -->
                                        <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity text-emerald-400">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer relative block">
                                        <input type="radio" name="status" value="sakit" class="peer sr-only">
                                        <div class="h-full p-4 rounded-xl border border-gray-700 bg-gray-800/50 text-center text-gray-400 peer-checked:bg-yellow-500/20 peer-checked:border-yellow-500 peer-checked:text-yellow-400 peer-checked:ring-2 peer-checked:ring-yellow-500/30 peer-checked:scale-[1.02] transition-all duration-300 hover:bg-gray-700/80 shadow-sm">
                                            <div class="text-2xl mb-1">🤒</div>
                                            <div class="font-bold">Sakit</div>
                                        </div>
                                        <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity text-yellow-400">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer relative block">
                                        <input type="radio" name="status" value="izin" class="peer sr-only">
                                        <div class="h-full p-4 rounded-xl border border-gray-700 bg-gray-800/50 text-center text-gray-400 peer-checked:bg-blue-500/20 peer-checked:border-blue-500 peer-checked:text-blue-400 peer-checked:ring-2 peer-checked:ring-blue-500/30 peer-checked:scale-[1.02] transition-all duration-300 hover:bg-gray-700/80 shadow-sm">
                                            <div class="text-2xl mb-1">📝</div>
                                            <div class="font-bold">Izin</div>
                                        </div>
                                        <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity text-blue-400">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        </div>
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-400 mb-2">Bukti Kehadiran (Selfie)</label>
                                        <input type="file" name="foto_selfie" accept="image/*" capture="camera" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-500/20 file:text-blue-400 hover:file:bg-blue-500/30 bg-gray-900 border border-gray-700 rounded-xl p-1 cursor-pointer">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-400 mb-2">Surat Keterangan (Bila Sakit/Izin)</label>
                                        <input type="file" name="surat_izin" accept="image/*,.pdf" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-yellow-500/20 file:text-yellow-400 hover:file:bg-yellow-500/30 bg-gray-900 border border-gray-700 rounded-xl p-1 cursor-pointer">
                                    </div>
                                </div>
                                
                                <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-400 hover:to-indigo-400 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all">
                                    Kirim Presensi
                                </button>
                            </form>
                        </div>
                        
                        <!-- Tampilan Sukses (Setelah Kirim) -->
                        <div x-show="submitted" style="display: none;" x-transition.opacity class="flex items-center gap-4 p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10">
                            <div class="w-12 h-12 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-emerald-400 text-lg">Terima Kasih!</h3>
                                <p class="text-emerald-100/70 text-sm">Data presensi forum Anda berhasil dicatat.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($todayP2ks)
    <!-- P2KS Notification Banner -->
    <div class="relative bg-gradient-to-r from-teal-900/40 to-emerald-900/40 border border-teal-500/50 rounded-3xl p-6 shadow-[0_10px_40px_rgba(20,184,166,0.15)] overflow-hidden group hover:shadow-[0_15px_50px_rgba(20,184,166,0.25)] transition-all duration-500">
        <div class="absolute inset-0 bg-teal-500/10 blur-[50px] rounded-full pointer-events-none group-hover:bg-teal-500/20 transition-colors"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start md:items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-teal-500/20 border border-teal-500/30 flex items-center justify-center text-teal-400 shrink-0 shadow-[0_0_15px_rgba(20,184,166,0.3)]">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </div>
                <div>
                    <h3 class="text-xl md:text-2xl font-bold text-white tracking-tight drop-shadow-[0_0_5px_rgba(255,255,255,0.2)] flex items-center gap-2">
                        Panggilan Tugas P2KS! <span class="relative flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-teal-500"></span></span>
                    </h3>
                    <p class="text-teal-100/70 text-sm md:text-base mt-1.5 leading-relaxed">
                        Anda telah dijadwalkan secara acak untuk melaksanakan piket P2KS hari ini (<span class="font-bold text-teal-300">{{ \Carbon\Carbon::parse($todayP2ks->scheduled_date)->translatedFormat('l, d F Y') }}</span>). Mohon segera laksanakan tugas membersihkan sekretariat.
                    </p>
                </div>
            </div>
            <a href="{{ route('p2ks.index') }}" class="px-6 py-3 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-white font-bold rounded-xl shadow-[0_0_20px_rgba(20,184,166,0.4)] hover:shadow-[0_0_30px_rgba(20,184,166,0.6)] hover:-translate-y-0.5 transition-all duration-300 shrink-0 flex items-center gap-2">
                Lihat Jadwal
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>
    </div>
    @endif

    <!-- Main Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @if(array_intersect(['ADMIN', 'KETUA', 'PEMBINA', 'SEKRETARIS'], $roles))
            <x-stat-card title="Total Anggota" value="{{ $stats['total_members'] ?? 0 }}" color="cyan">
                <x-slot name="icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </x-slot>
            </x-stat-card>
            <x-stat-card title="Divisi Aktif" value="{{ $stats['total_divisions'] ?? 0 }}" color="violet">
                <x-slot name="icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </x-slot>
            </x-stat-card>
            <x-stat-card title="Event Aktif" value="{{ $stats['active_events'] ?? 0 }}" color="lime">
                <x-slot name="icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </x-slot>
            </x-stat-card>
        @else
            <!-- Personal stats untuk Anggota Biasa -->
            <x-stat-card title="Event Saya" value="{{ $stats['my_events'] ?? 0 }}" color="cyan">
                <x-slot name="icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                </x-slot>
            </x-stat-card>
            <x-stat-card title="Total Kegiatan" value="{{ \App\Models\Event::where('status', 'CLOSED')->count() ?? 0 }}" color="violet">
                <x-slot name="icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </x-slot>
            </x-stat-card>
            <x-stat-card title="Kehadiran" value="95%" color="lime">
                <x-slot name="icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                </x-slot>
            </x-stat-card>
        @endif
        
        @if(array_intersect(['BENDAHARA', 'KETUA', 'ADMIN'], $roles))
            <x-glowing-stat-card title="Saldo Kas Aktif" value="Rp {{ number_format($stats['cash_balance'] ?? 0, 0, ',', '.') }}" subtitle="Total pemasukan terkini">
                <x-slot name="icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </x-slot>
            </x-glowing-stat-card>
        @else
            <x-glowing-stat-card title="Tagihan Belum Lunas" value="{{ $stats['my_unpaid_dues'] ?? 0 }} Tagihan" subtitle="Status: {{ ($stats['my_unpaid_dues'] ?? 0) > 0 ? 'Perlu Dibayar' : 'Aman' }}" gradient="from-blue-600 to-indigo-500">
                <x-slot name="icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </x-slot>
            </x-glowing-stat-card>
        @endif
    </div>

    <!-- Lower Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Events List -->
        <div class="bg-[#121B2D]/80 backdrop-blur-xl rounded-3xl p-8 shadow-[0_10px_40px_rgba(0,0,0,0.5)] border border-cyan-500/20 relative overflow-hidden group hover:shadow-[0_15px_50px_rgba(6,182,212,0.1)] transition-all duration-500">
            <div class="flex justify-between items-center mb-6 relative z-10">
                <h3 class="text-xl font-bold text-white tracking-tight drop-shadow-[0_0_5px_rgba(255,255,255,0.2)]">Upcoming Events</h3>
                <a href="{{ route('feature.pending', 'Manajemen Event') }}" class="text-sm font-semibold text-cyan-400 hover:text-cyan-300 transition-colors hover:drop-shadow-[0_0_8px_rgba(6,182,212,0.8)]">Lihat Semua</a>
            </div>
            
            <div class="space-y-0 relative before:absolute before:inset-y-0 before:left-[35px] before:w-0.5 before:bg-cyan-500/20">
                @forelse($upcomingEvents as $event)
                <div class="group/event relative flex items-start py-4 pl-0 pr-4 rounded-2xl hover:bg-cyan-500/5 transition-all duration-300 cursor-pointer">
                    <!-- Timeline Node -->
                    <div class="absolute left-[31px] top-[32px] w-2.5 h-2.5 rounded-full bg-cyan-400 border-2 border-[#121B2D] shadow-[0_0_10px_rgba(6,182,212,0.8)] z-10 group-hover/event:scale-150 group-hover/event:bg-white group-hover/event:shadow-[0_0_15px_rgba(255,255,255,1)] transition-all duration-300"></div>
                    
                    <div class="flex-shrink-0 w-[70px] flex flex-col items-center justify-center text-cyan-400 mr-6">
                        <span class="text-xs font-bold uppercase tracking-widest text-cyan-500/70">{{ \Carbon\Carbon::parse($event->date)->format('M') }}</span>
                        <span class="text-2xl font-extrabold leading-none drop-shadow-[0_0_8px_rgba(6,182,212,0.4)]">{{ \Carbon\Carbon::parse($event->date)->format('d') }}</span>
                    </div>
                    
                    <div class="flex-1 bg-[#070b19]/50 border border-cyan-500/10 rounded-2xl p-4 group-hover/event:border-cyan-500/40 group-hover/event:shadow-[0_0_20px_rgba(6,182,212,0.15)] transition-all duration-300">
                        <h4 class="text-base font-bold text-gray-200 group-hover/event:text-cyan-300 transition-colors mb-2">{{ $event->title }}</h4>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center text-sm text-gray-400 gap-2 sm:gap-4">
                            <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> {{ $event->time ? \Carbon\Carbon::parse($event->time)->format('H:i') : '-' }}</span>
                            <span class="flex items-center gap-1.5 truncate max-w-[120px]"><svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> {{ $event->location ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="flex items-center justify-center py-10 text-slate-500 text-sm">
                    Tidak ada event mendatang.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-[#121B2D]/80 backdrop-blur-xl rounded-3xl p-8 shadow-[0_10px_40px_rgba(0,0,0,0.5)] border border-cyan-500/20 relative overflow-hidden group hover:shadow-[0_15px_50px_rgba(6,182,212,0.1)] transition-all duration-500">
            <h3 class="text-xl font-bold text-white tracking-tight mb-6 drop-shadow-[0_0_5px_rgba(255,255,255,0.2)]">Aksi Cepat</h3>
            <div class="grid grid-cols-2 gap-4">
                <x-action-card href="{{ route('feature.event.index') }}" title="Kegiatan" description="Lihat kalender">
                    <x-slot name="icon">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </x-slot>
                </x-action-card>
                
                @if(in_array('BENDAHARA', $roles) || in_array('KETUA', $roles))
                    <x-action-card href="{{ route('kas.index') }}" title="Data Kas" description="Kelola tagihan">
                        <x-slot name="icon">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </x-slot>
                    </x-action-card>
                @else
                    <x-action-card href="{{ route('kas.index') }}" title="Tagihan" description="Bayar iuran">
                        <x-slot name="icon">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </x-slot>
                    </x-action-card>
                @endif

                <x-action-card href="{{ route('anggota.index') }}" title="Anggota" description="Direktori">
                    <x-slot name="icon">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </x-slot>
                </x-action-card>

                <x-action-card href="{{ url('/informasi') }}" title="Informasi" description="Pengumuman">
                    <x-slot name="icon">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                    </x-slot>
                </x-action-card>
            </div>
        </div>
        
        <!-- Notifications -->
        <div class="bg-[#121B2D]/80 backdrop-blur-xl rounded-3xl p-8 shadow-[0_10px_40px_rgba(0,0,0,0.5)] border border-violet-500/20 relative overflow-hidden group hover:shadow-[0_15px_50px_rgba(139,92,246,0.1)] transition-all duration-500">
            <h3 class="text-xl font-bold text-white tracking-tight mb-6 drop-shadow-[0_0_5px_rgba(255,255,255,0.2)] flex items-center gap-2">
                <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                Notifikasi Personal
            </h3>
            
            <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-violet-500/20 scrollbar-track-transparent">
                @php
                    $notifications = auth()->user()->notifications()->take(5)->get();
                @endphp
                
                @forelse($notifications as $notif)
                <div class="p-3 bg-violet-500/5 hover:bg-violet-500/10 border border-violet-500/10 rounded-xl transition-colors {{ $notif->read_at ? 'opacity-70' : 'border-violet-500/30' }}">
                    <div class="flex gap-3">
                        <div class="mt-0.5">
                            @if(isset($notif->data['type']) && $notif->data['type'] == 'task')
                                <div class="w-2 h-2 rounded-full bg-cyan-400 mt-1.5 shadow-[0_0_5px_rgba(6,182,212,0.8)]"></div>
                            @elseif(isset($notif->data['type']) && $notif->data['type'] == 'committee')
                                <div class="w-2 h-2 rounded-full bg-amber-400 mt-1.5 shadow-[0_0_5px_rgba(251,191,36,0.8)]"></div>
                            @else
                                <div class="w-2 h-2 rounded-full bg-violet-400 mt-1.5 shadow-[0_0_5px_rgba(139,92,246,0.8)]"></div>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-200 leading-snug">{{ $notif->data['message'] ?? 'Ada pemberitahuan baru' }}</p>
                            @if(isset($notif->data['event']))
                                <p class="text-xs text-violet-300/80 font-medium mt-0.5">{{ $notif->data['event'] }}</p>
                            @endif
                            <p class="text-[10px] text-slate-500 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 border border-dashed border-slate-700/50 rounded-xl text-slate-500 text-sm">
                    Belum ada notifikasi baru.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
