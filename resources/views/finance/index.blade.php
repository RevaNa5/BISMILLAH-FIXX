@extends('layouts.admin')

@section('title', 'Kas & Keuangan')
@section('header', 'Kas & Tagihan')

@section('content')
<div x-data="{ 
    showModal: false, 
    payModal: false, 
    selectedDue: null,
    payData: {
        due_id: '',
        payment_method: 'TRANSFER',
        proof: null
    },
    openPayModal(due) {
        this.selectedDue = due;
        this.payData.due_id = due.id;
        this.payModal = true;
    }
}" class="space-y-8">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-[#121B2D]/80 backdrop-blur-xl rounded-3xl p-6 md:p-8 border border-cyan-500/20 shadow-[0_10px_40px_rgba(0,0,0,0.5)] relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-64 h-64 bg-cyan-600/10 rounded-full blur-[80px] pointer-events-none group-hover:scale-110 transition-transform duration-700"></div>
        <div class="relative z-10">
            <h3 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight drop-shadow-[0_0_8px_rgba(255,255,255,0.2)]">
                Daftar Tagihan <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-emerald-400">Kas & Iuran</span>
            </h3>
            <p class="text-slate-400 text-sm mt-1">Kelola dan bayar tagihan iuran organisasi PIK-R SKAKMATT secara transparan dan teratur.</p>
        </div>
        @if($canManageFinance)
            <div class="relative z-10">
                <button 
                    @click="showModal = true"
                    class="px-5 py-3 rounded-2xl bg-gradient-to-r from-cyan-500 to-emerald-500 text-white font-bold text-sm shadow-[0_0_15px_rgba(6,182,212,0.4)] hover:shadow-[0_0_25px_rgba(6,182,212,0.6)] hover:scale-[1.02] transition-all duration-300 flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Tagihan Baru
                </button>
            </div>
        @endif
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-2xl font-medium shadow-[0_0_20px_rgba(16,185,129,0.15)] flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-500/10 border border-red-500/30 text-red-400 rounded-2xl font-medium shadow-[0_0_20px_rgba(239,68,68,0.15)] flex items-center gap-3">
            <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @php
        $totalDues = count($dues);
        $unpaidDues = $dues->where('status', 'BELUM BAYAR')->count();
        $pendingDues = $dues->where('status', 'MENUNGGU_VERIFIKASI')->count();
        $paidDues = $dues->where('status', 'LUNAS')->count();
    @endphp

    <!-- Ringkasan Statistik Kas -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-slate-800/80 backdrop-blur-md border border-slate-700/80 rounded-2xl p-5 text-center shadow-lg transition-all duration-300 hover:border-slate-500">
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-2">Total Tagihan</p>
            <p class="text-3xl font-black text-white">{{ $totalDues }}</p>
        </div>
        <div class="bg-slate-900/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-5 text-center shadow-lg transition-all duration-300 hover:border-slate-500">
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-2">Belum Bayar</p>
            <p class="text-3xl font-black text-slate-300">{{ $unpaidDues }}</p>
        </div>
        <div class="bg-amber-900/20 backdrop-blur-md border border-amber-500/30 rounded-2xl p-5 text-center shadow-lg transition-all duration-300 hover:border-amber-400">
            <p class="text-[10px] text-amber-400 font-bold uppercase tracking-wider mb-2">Menunggu Verifikasi</p>
            <p class="text-3xl font-black text-amber-400">{{ $pendingDues }}</p>
        </div>
        <div class="bg-emerald-900/20 backdrop-blur-md border border-emerald-500/30 rounded-2xl p-5 text-center shadow-lg transition-all duration-300 hover:border-emerald-400">
            <p class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider mb-2">Lunas</p>
            <p class="text-3xl font-black text-emerald-400">{{ $paidDues }}</p>
        </div>
    </div>

    <!-- Grid Tagihan Kas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($dues as $due)
            <div class="bg-[#121B2D]/85 backdrop-blur-xl border border-cyan-500/30 hover:border-cyan-400/60 rounded-3xl p-6 md:p-7 shadow-[0_10px_30px_rgba(0,0,0,0.5)] hover:shadow-[0_15px_35px_rgba(6,182,212,0.15)] transition-all duration-500 relative overflow-hidden group flex flex-col justify-between hover:translate-y-[-2px]">
                
                <!-- Ambient Glow Effects -->
                <div class="absolute -top-12 -right-12 w-36 h-36 bg-cyan-500/10 rounded-full blur-2xl pointer-events-none group-hover:scale-125 transition-transform duration-700"></div>
                <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10">
                    <!-- Badges Row -->
                    <div class="flex justify-between items-center mb-5">
                        <div class="px-3 py-1 bg-cyan-950/60 border border-cyan-500/40 text-cyan-300 text-xs font-extrabold tracking-wider uppercase rounded-full shadow-[0_0_10px_rgba(6,182,212,0.2)] flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            {{ $due['type'] }}
                        </div>

                        @if($due['status'] === 'LUNAS')
                            <span class="px-3 py-1 bg-emerald-950/60 border border-emerald-500/50 text-emerald-400 font-bold text-xs rounded-full flex items-center gap-1.5 shadow-[0_0_12px_rgba(16,185,129,0.3)]">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                LUNAS
                            </span>
                        @elseif($due['status'] === 'MENUNGGU_VERIFIKASI')
                            <span class="px-3 py-1 bg-amber-950/60 border border-amber-500/50 text-amber-400 font-bold text-xs rounded-full flex items-center gap-1.5 shadow-[0_0_12px_rgba(245,158,11,0.3)]">
                                <svg class="w-3.5 h-3.5 text-amber-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                MENUNGGU VERIFIKASI
                            </span>
                        @elseif($due['status'] === 'DITOLAK')
                            <span class="px-3 py-1 bg-red-950/60 border border-red-500/50 text-red-400 font-bold text-xs rounded-full flex items-center gap-1.5 shadow-[0_0_12px_rgba(239,68,68,0.3)]">
                                <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                DITOLAK
                            </span>
                        @else
                            <span class="px-3 py-1 bg-slate-800/90 border border-slate-600/70 text-slate-300 font-bold text-xs rounded-full flex items-center gap-1.5 shadow-[0_0_8px_rgba(148,163,184,0.15)]">
                                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                BELUM BAYAR
                            </span>
                        @endif
                    </div>

                    <!-- Title & Nominal -->
                    <h4 class="text-xl font-bold text-white group-hover:text-cyan-200 transition-colors mb-2 tracking-tight">
                        {{ $due['title'] }}
                    </h4>

                    <div class="mb-6">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Nominal Iuran</span>
                        <p class="text-3xl md:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-300 drop-shadow-[0_0_15px_rgba(16,185,129,0.35)]">
                            Rp {{ number_format($due['amount'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div class="relative z-10 pt-2 border-t border-slate-700/50">
                    @if($due['status'] === 'DITOLAK')
                        <div class="bg-red-950/40 border border-red-500/40 text-red-300 p-3.5 rounded-2xl text-xs mb-4 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <span class="font-bold text-red-400">Alasan Penolakan:</span>
                                <p class="mt-0.5 text-slate-300">{{ $due['rejection_reason'] ?? 'Bukti pembayaran tidak terbaca atau tidak valid.' }}</p>
                            </div>
                        </div>
                    @endif

                    @if($due['status'] === 'BELUM BAYAR' || $due['status'] === 'DITOLAK')
                        <button
                            @click="openPayModal({{ json_encode($due) }})"
                            class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white py-3.5 rounded-2xl font-bold text-sm shadow-[0_0_20px_rgba(16,185,129,0.4)] hover:shadow-[0_0_30px_rgba(16,185,129,0.7)] transition-all duration-300 flex items-center justify-center gap-2 group/btn hover:scale-[1.01] active:scale-[0.99]"
                        >
                            <svg class="w-4 h-4 text-white group-hover/btn:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            Bayar Sekarang
                        </button>
                    @elseif($due['status'] === 'LUNAS')
                        <div class="w-full bg-emerald-950/30 border border-emerald-500/30 text-emerald-400 py-3 rounded-2xl font-bold text-sm text-center flex items-center justify-center gap-2 shadow-[0_0_15px_rgba(16,185,129,0.15)]">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Sudah Lunas
                        </div>
                    @else
                        <div class="w-full bg-amber-950/30 border border-amber-500/30 text-amber-400 py-3 rounded-2xl font-bold text-sm text-center flex items-center justify-center gap-2 shadow-[0_0_15px_rgba(245,158,11,0.15)]">
                            <svg class="w-4 h-4 text-amber-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Menunggu Verifikasi Pengurus
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
        
        @if(count($dues) === 0)
            <div class="col-span-full bg-slate-900/40 border border-dashed border-slate-700 rounded-3xl p-12 text-center">
                <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                <p class="text-slate-400 font-medium">Belum ada tagihan kas yang diterbitkan saat ini.</p>
            </div>
        @endif
    </div>

    <!-- Modal Create Due -->
    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="showModal = false"></div>
        <div x-show="showModal" x-transition class="bg-[#0F172A] border border-slate-700/80 rounded-3xl w-full max-w-md p-7 relative z-10 shadow-[0_0_50px_rgba(0,0,0,0.8)] text-white">
            <div class="flex items-center justify-between pb-4 border-b border-slate-700/50 mb-5">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                    Buat Tagihan Baru
                </h3>
                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('kas.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nama Tagihan</label>
                    <input type="text" name="title" placeholder="Contoh: Kas Bulan September" class="w-full bg-slate-900/80 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" required />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nominal (Rp)</label>
                    <input type="number" name="amount" min="0" placeholder="5000" class="w-full bg-slate-900/80 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all" required />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Jenis Kas</label>
                    <select name="type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                        <option value="BULANAN">Bulanan</option>
                        <option value="KEGIATAN">Kegiatan</option>
                        <option value="KHUSUS">Khusus</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-700/50 mt-6">
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 text-slate-300 font-semibold hover:bg-slate-800 rounded-xl transition-colors text-sm">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-500 to-emerald-500 text-white font-bold rounded-xl shadow-[0_0_15px_rgba(6,182,212,0.4)] hover:shadow-[0_0_25px_rgba(6,182,212,0.6)] transition-all text-sm">Simpan Tagihan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Pay -->
    <div x-show="payModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="payModal" x-transition.opacity class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="payModal = false"></div>
        <div x-show="payModal" x-transition class="bg-[#0F172A] border border-slate-700/80 rounded-3xl w-full max-w-md p-7 relative z-10 shadow-[0_0_50px_rgba(0,0,0,0.8)] text-white">
            <div class="flex items-center justify-between pb-4 border-b border-slate-700/50 mb-5">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                    Bayar Tagihan
                </h3>
                <button type="button" @click="payModal = false" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="bg-slate-900/80 border border-slate-700/70 rounded-2xl p-4 mb-5 text-sm">
                <span class="text-slate-400 text-xs block mb-1">Konfirmasi Pembayaran:</span>
                <p class="font-bold text-white text-base" x-text="selectedDue?.title"></p>
                <p class="text-emerald-400 font-extrabold text-xl mt-1" x-text="selectedDue ? 'Rp ' + new Intl.NumberFormat('id-ID').format(selectedDue.amount) : ''"></p>
            </div>

            <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="due_id" x-model="payData.due_id" />
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Metode Pembayaran</label>
                    <select name="payment_method" x-model="payData.payment_method" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                        <option value="TRANSFER">Transfer Bank</option>
                        <option value="QRIS">QRIS</option>
                        <option value="CASH">Tunai (Titip Pengurus / Bendahara)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Upload Bukti Pembayaran (JPG/PNG)</label>
                    <input type="file" name="proof" accept="image/jpeg,image/png,image/jpg" class="w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-500/20 file:text-cyan-300 hover:file:bg-cyan-500/30 file:cursor-pointer transition-colors border border-slate-700 rounded-xl bg-slate-900/60 p-2" required />
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-700/50 mt-6">
                    <button type="button" @click="payModal = false" class="px-5 py-2.5 text-slate-300 font-semibold hover:bg-slate-800 rounded-xl transition-colors text-sm">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold rounded-xl shadow-[0_0_15px_rgba(16,185,129,0.4)] hover:shadow-[0_0_25px_rgba(16,185,129,0.6)] transition-all text-sm">Kirim Bukti Pembayaran</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
