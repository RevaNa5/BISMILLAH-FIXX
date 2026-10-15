<?php

namespace Database\Seeders;

use App\Models\CashTransaction;
use App\Models\Due;
use App\Models\OrganizationPeriod;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    /**
     * Run the database seeds for Kas and Keuangan.
     */
    public function run(): void
    {
        $period = OrganizationPeriod::where('is_active', true)->first()
            ?? OrganizationPeriod::first();

        // 1. Temukan Bendahara Organisasi
        $bendahara1 = User::role('BENDAHARA')->where('email', 'like', '%alyaa%')->first()
            ?? User::role('BENDAHARA')->first()
            ?? User::role('ADMIN')->first();

        $bendahara2 = User::role('BENDAHARA')->where('email', 'like', '%fadilla%')->first()
            ?? $bendahara1;

        // 2. Setup Tagihan Kas (Dues)
        $dueBulanBerjalan = Due::firstOrCreate(
            ['title' => 'Kas Bulan Berjalan', 'organization_period_id' => $period?->id],
            [
                'amount' => 5000,
                'type' => 'BULANAN',
                'deadline' => Carbon::now()->endOfMonth()->toDateString(),
                'description' => 'Iuran kas bulanan wajib untuk seluruh anggota aktif PIK-R SKAKMATT.',
            ]
        );

        $dueAwalPeriode = Due::firstOrCreate(
            ['title' => 'Iuran Wajib Awal Periode 2025/2026', 'organization_period_id' => $period?->id],
            [
                'amount' => 10000,
                'type' => 'KHUSUS',
                'deadline' => '2025-08-31',
                'description' => 'Iuran awal periode untuk kartu anggota dan seragam organisasi.',
            ]
        );

        // 3. Ambil data anggota yang sudah ada di database
        $candidateUsers = User::whereHas('roles', function ($q) {
            $q->whereNotIn('name', ['ADMIN', 'PEMBINA', 'PELATIH']);
        })->get();

        if ($candidateUsers->isEmpty()) {
            $candidateUsers = User::take(60)->get();
        }

        $paymentMethods = ['CASH', 'TRANSFER', 'QRIS'];

        // 4. Input Data Pembayaran untuk 'Kas Bulan Berjalan'
        $paidCount = 0;
        foreach ($candidateUsers as $idx => $user) {
            if ($paidCount >= 50) {
                break;
            }

            $verifier = ($idx % 2 === 0) ? $bendahara1 : $bendahara2;
            $method = $paymentMethods[$idx % 3];
            $daysAgo = ($idx % 20) + 1;
            $payDate = Carbon::now()->subDays($daysAgo);

            // Sebagian kecil buat status 'MENUNGGU_VERIFIKASI' agar bendahara bisa verifikasi
            if ($idx >= 45 && $idx < 50) {
                Payment::updateOrCreate(
                    ['due_id' => $dueBulanBerjalan->id, 'user_id' => $user->id],
                    [
                        'amount_paid' => $dueBulanBerjalan->amount,
                        'payment_method' => $method,
                        'proof_file' => 'proofs/sample_transfer.jpg',
                        'status' => 'MENUNGGU_VERIFIKASI',
                        'rejection_reason' => null,
                        'verified_by_user_id' => null,
                        'verified_at' => null,
                        'created_at' => $payDate,
                        'updated_at' => $payDate,
                    ]
                );
            } else {
                // Status LUNAS
                $payment = Payment::updateOrCreate(
                    ['due_id' => $dueBulanBerjalan->id, 'user_id' => $user->id],
                    [
                        'amount_paid' => $dueBulanBerjalan->amount,
                        'payment_method' => $method,
                        'proof_file' => 'proofs/sample_proof.jpg',
                        'status' => 'LUNAS',
                        'rejection_reason' => null,
                        'verified_by_user_id' => $verifier->id,
                        'verified_at' => $payDate,
                        'created_at' => $payDate,
                        'updated_at' => $payDate,
                    ]
                );

                // Buat pencatatan transaksi kas masuk (CashTransaction)
                CashTransaction::updateOrCreate(
                    ['payment_id' => $payment->id],
                    [
                        'type' => 'INCOME',
                        'amount' => $payment->amount_paid,
                        'description' => 'Pembayaran '.$dueBulanBerjalan->title.' - '.$user->name,
                        'transaction_date' => $payDate->toDateString(),
                        'created_by' => $verifier->id,
                        'created_at' => $payDate,
                        'updated_at' => $payDate,
                    ]
                );
            }

            $paidCount++;
        }

        // 5. Input Data Pembayaran untuk 'Iuran Wajib Awal Periode'
        $earlyCount = 0;
        foreach ($candidateUsers as $idx => $user) {
            if ($earlyCount >= 35) {
                break;
            }

            $verifier = ($idx % 2 === 0) ? $bendahara1 : $bendahara2;
            $method = $paymentMethods[($idx + 1) % 3];
            $payDate = Carbon::parse('2025-08-10')->addDays($idx % 15);

            $payment = Payment::updateOrCreate(
                ['due_id' => $dueAwalPeriode->id, 'user_id' => $user->id],
                [
                    'amount_paid' => $dueAwalPeriode->amount,
                    'payment_method' => $method,
                    'proof_file' => 'proofs/sample_receipt.jpg',
                    'status' => 'LUNAS',
                    'rejection_reason' => null,
                    'verified_by_user_id' => $verifier->id,
                    'verified_at' => $payDate,
                    'created_at' => $payDate,
                    'updated_at' => $payDate,
                ]
            );

            CashTransaction::updateOrCreate(
                ['payment_id' => $payment->id],
                [
                    'type' => 'INCOME',
                    'amount' => $payment->amount_paid,
                    'description' => 'Pembayaran '.$dueAwalPeriode->title.' - '.$user->name,
                    'transaction_date' => $payDate->toDateString(),
                    'created_by' => $verifier->id,
                    'created_at' => $payDate,
                    'updated_at' => $payDate,
                ]
            );

            $earlyCount++;
        }

        // 6. Catatan Kas Resmi Organisasi (Pemasukan Non-Iuran & Pengeluaran Operasional)
        $officialTransactions = [
            [
                'type' => 'INCOME',
                'amount' => 350000.00,
                'description' => 'Saldo Awal Kas PIK-R SKAKMATT Periode 2025/2026',
                'transaction_date' => '2025-07-15',
                'created_by' => $bendahara1->id,
            ],
            [
                'type' => 'INCOME',
                'amount' => 175000.00,
                'description' => 'Keuntungan Hasil Wirausaha PKWU (Skakmatt Dodolan & Merchandise)',
                'transaction_date' => '2026-08-20',
                'created_by' => $bendahara2->id,
            ],
            [
                'type' => 'EXPENSE',
                'amount' => 45000.00,
                'description' => 'Pengadaan Buku Kas Besar, Kwitansi & Kebutuhan ATK Kebendaharaan',
                'transaction_date' => '2025-07-25',
                'created_by' => $bendahara1->id,
            ],
            [
                'type' => 'EXPENSE',
                'amount' => 75000.00,
                'description' => 'Konsumsi Rapat Pleno Koordinasi Pengurus & Pembina',
                'transaction_date' => '2025-08-15',
                'created_by' => $bendahara2->id,
            ],
            [
                'type' => 'EXPENSE',
                'amount' => 55000.00,
                'description' => 'Pengadaan Kotak P3K & Kelengkapan Ruang Konseling Sebaya',
                'transaction_date' => '2026-09-12',
                'created_by' => $bendahara1->id,
            ],
        ];

        foreach ($officialTransactions as $ot) {
            CashTransaction::updateOrCreate(
                ['description' => $ot['description'], 'transaction_date' => $ot['transaction_date']],
                [
                    'type' => $ot['type'],
                    'amount' => $ot['amount'],
                    'created_by' => $ot['created_by'],
                    'payment_id' => null,
                ]
            );
        }
    }
}
