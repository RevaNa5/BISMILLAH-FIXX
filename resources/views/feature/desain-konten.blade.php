@extends('layouts.admin')

@section('title', 'Desain Konten - SKAKMATT')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">
                Desain Konten & Poster
            </h2>
            <p class="text-gray-400 text-sm mt-1">Koleksi pamflet, poster, dan desain publikasi PIK-R SKAKMATT.</p>
        </div>
        <button class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-400 hover:to-pink-400 text-white rounded-xl font-semibold shadow-lg shadow-purple-500/30 transition-all flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Buat Desain Baru
        </button>
    </div>

    <!-- Filter/Tabs -->
    <div class="flex gap-2 overflow-x-auto pb-2 [scrollbar-width:none]">
        <button class="px-4 py-2 rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/30 font-semibold text-sm whitespace-nowrap">Semua Desain</button>
        <button class="px-4 py-2 rounded-full bg-white/5 text-gray-400 hover:text-purple-300 hover:bg-white/10 font-semibold text-sm whitespace-nowrap transition-all">Peringatan Hari Besar</button>
        <button class="px-4 py-2 rounded-full bg-white/5 text-gray-400 hover:text-purple-300 hover:bg-white/10 font-semibold text-sm whitespace-nowrap transition-all">Event</button>
        <button class="px-4 py-2 rounded-full bg-white/5 text-gray-400 hover:text-purple-300 hover:bg-white/10 font-semibold text-sm whitespace-nowrap transition-all">Edukasi KRR</button>
    </div>

    <!-- Design Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        
        <!-- Item 1: Hari Lahir Pancasila -->
        <div class="group relative">
            <div class="bg-gray-800 rounded-2xl p-2 relative overflow-hidden ring-1 ring-white/10 group-hover:ring-purple-500/50 transition-all duration-300 shadow-xl shadow-black/40">
                <div class="aspect-[4/5] relative rounded-xl overflow-hidden bg-[#0a0a0a]">
                    <img src="https://images.unsplash.com/photo-1596414086775-3e321509fa22?auto=format&fit=crop&q=80&w=600" alt="Hari Pancasila" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 opacity-80 mix-blend-overlay">
                    <div class="absolute inset-0 bg-gradient-to-tr from-red-600/40 to-transparent"></div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-6 border-4 border-white/20 m-4 rounded-lg">
                        <div class="w-12 h-12 mb-3 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path></svg>
                        </div>
                        <h3 class="font-extrabold text-white text-xl uppercase tracking-widest drop-shadow-md">Hari Lahir<br>Pancasila</h3>
                        <p class="text-xs text-white/80 mt-2 font-medium tracking-wider">1 Juni 2026</p>
                    </div>
                </div>
            </div>
            <div class="mt-4 px-1">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-gray-200">Pamflet Hari Pancasila</h4>
                    <span class="text-xs font-semibold text-green-400 bg-green-400/10 px-2 py-1 rounded">Published</span>
                </div>
                <div class="flex gap-2 mt-3">
                    <button class="flex-1 py-1.5 text-xs font-semibold text-white bg-white/5 hover:bg-white/10 rounded-lg transition-colors border border-white/10">Unduh (PNG)</button>
                    <button class="flex-1 py-1.5 text-xs font-semibold text-white bg-purple-500/20 hover:bg-purple-500/30 text-purple-300 rounded-lg transition-colors border border-purple-500/30">Edit di Canva</button>
                </div>
            </div>
        </div>

        <!-- Item 2: Edukasi KRR -->
        <div class="group relative">
            <div class="bg-gray-800 rounded-2xl p-2 relative overflow-hidden ring-1 ring-white/10 group-hover:ring-pink-500/50 transition-all duration-300 shadow-xl shadow-black/40">
                <div class="aspect-[4/5] relative rounded-xl overflow-hidden bg-[#0a0a0a]">
                    <img src="https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&q=80&w=600" alt="Edukasi KRR" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 opacity-80 mix-blend-overlay">
                    <div class="absolute inset-0 bg-gradient-to-br from-pink-600/40 to-blue-600/40"></div>
                    <div class="absolute inset-0 flex flex-col items-start justify-end p-6">
                        <span class="px-2 py-1 bg-white/20 backdrop-blur-md text-white text-[10px] font-bold rounded mb-2 tracking-widest uppercase">Edukasi</span>
                        <h3 class="font-extrabold text-white text-2xl leading-tight drop-shadow-md">Katakan TIDAK<br>pada TRIAD KRR</h3>
                        <div class="w-12 h-1 bg-pink-400 mt-3 rounded-full"></div>
                    </div>
                </div>
            </div>
            <div class="mt-4 px-1">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-gray-200">Infografis Triad KRR</h4>
                    <span class="text-xs font-semibold text-yellow-400 bg-yellow-400/10 px-2 py-1 rounded">Draft</span>
                </div>
                <div class="flex gap-2 mt-3">
                    <button class="flex-1 py-1.5 text-xs font-semibold text-white bg-white/5 hover:bg-white/10 rounded-lg transition-colors border border-white/10">Preview</button>
                    <button class="flex-1 py-1.5 text-xs font-semibold text-white bg-pink-500/20 hover:bg-pink-500/30 text-pink-300 rounded-lg transition-colors border border-pink-500/30">Edit di Canva</button>
                </div>
            </div>
        </div>

        <!-- Item 3: Open Recruitment -->
        <div class="group relative">
            <div class="bg-gray-800 rounded-2xl p-2 relative overflow-hidden ring-1 ring-white/10 group-hover:ring-cyan-500/50 transition-all duration-300 shadow-xl shadow-black/40">
                <div class="aspect-[4/5] relative rounded-xl overflow-hidden bg-gradient-to-b from-[#1a2b4c] to-[#0B1221]">
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center">
                        <div class="w-16 h-16 bg-cyan-500/20 rounded-2xl rotate-45 flex items-center justify-center mb-6 ring-2 ring-cyan-400/50">
                            <svg class="w-8 h-8 text-cyan-400 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        </div>
                        <h3 class="font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 to-blue-400 text-3xl uppercase tracking-tight">Oprec<br>Pengurus</h3>
                        <p class="text-cyan-200/60 mt-2 font-medium text-sm">Angkatan 2026/2027</p>
                    </div>
                </div>
            </div>
            <div class="mt-4 px-1">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-gray-200">Poster Oprec Pengurus</h4>
                    <span class="text-xs font-semibold text-green-400 bg-green-400/10 px-2 py-1 rounded">Published</span>
                </div>
                <div class="flex gap-2 mt-3">
                    <button class="flex-1 py-1.5 text-xs font-semibold text-white bg-white/5 hover:bg-white/10 rounded-lg transition-colors border border-white/10">Unduh (PNG)</button>
                    <button class="flex-1 py-1.5 text-xs font-semibold text-white bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-300 rounded-lg transition-colors border border-cyan-500/30">Edit di Canva</button>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
