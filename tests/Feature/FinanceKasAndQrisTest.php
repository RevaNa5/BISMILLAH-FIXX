<?php

namespace Tests\Feature;

use App\Models\CashTransaction;
use App\Models\Division;
use App\Models\Due;
use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinanceKasAndQrisTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $bendahara;

    protected User $ketua;

    protected User $memberUser;

    protected Division $division;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard roles
        Role::firstOrCreate(['name' => 'ADMIN']);
        Role::firstOrCreate(['name' => 'KETUA']);
        Role::firstOrCreate(['name' => 'BENDAHARA']);
        Role::firstOrCreate(['name' => 'USER']);

        $this->division = Division::firstOrCreate(
            ['name' => 'Divisi Kegiatan'],
            ['description' => 'Divisi Kegiatan']
        );

        $this->admin = User::factory()->create(['name' => 'Admin Test', 'email' => 'admin_test@pikr.com']);
        $this->admin->assignRole('ADMIN');

        $this->bendahara = User::factory()->create(['name' => 'Bendahara Test', 'email' => 'bendahara_test@pikr.com']);
        $this->bendahara->assignRole('BENDAHARA');

        $this->ketua = User::factory()->create(['name' => 'Ketua Test', 'email' => 'ketua_test@pikr.com']);
        $this->ketua->assignRole('KETUA');

        $this->memberUser = User::factory()->create(['name' => 'Anggota Test', 'email' => 'anggota_test@pikr.com', 'kelas' => 'XI RPL 1']);
        $this->memberUser->assignRole('USER');

        Member::create([
            'user_id' => $this->memberUser->id,
            'name' => 'Anggota Test',
            'kelas' => 'XI RPL 1',
            'status' => 'AKTIF',
            'division_id' => $this->division->id,
        ]);
    }

    public function test_qris_image_file_exists_on_disk(): void
    {
        $qrisPath = public_path('images/qris_pikr.jpeg');
        $this->assertFileExists($qrisPath, 'File qris_pikr.jpeg harus tersedia di folder public/images.');
        $this->assertGreaterThan(0, filesize($qrisPath));
    }

    public function test_finance_manager_can_access_management_and_regular_user_cannot(): void
    {
        // 1. Bendahara access
        $responseBendahara = $this->actingAs($this->bendahara)->get(route('kas.index'));
        $responseBendahara->assertStatus(200);
        $responseBendahara->assertSee('Pusat Pengelolaan');
        $responseBendahara->assertSee('Tabel Pembayaran Anggota');
        $responseBendahara->assertSee('Export Rekap Kas (CSV)');
        $responseBendahara->assertSee('Buat Tagihan Baru');

        // 2. Regular User access to kas.index sees personal section
        $responseMember = $this->actingAs($this->memberUser)->get(route('kas.index'));
        $responseMember->assertStatus(200);
        $responseMember->assertSee('Tagihan Kas Milik Saya');
        $responseMember->assertDontSee('Tabel Pembayaran Anggota');
        $responseMember->assertDontSee('Export Rekap Kas (CSV)');

        // 3. Regular User attempting management routes gets 403 Forbidden
        $responseStore = $this->actingAs($this->memberUser)->post(route('kas.store'), [
            'title' => 'Tagihan Ilegal',
            'amount' => 5000,
            'type' => 'BULANAN',
        ]);
        $responseStore->assertStatus(403);

        $responseExport = $this->actingAs($this->memberUser)->get(route('kas.export'));
        $responseExport->assertStatus(403);
    }

    public function test_pengurus_can_create_update_and_safe_delete_due(): void
    {
        // 1. Create Due
        $response = $this->actingAs($this->bendahara)->post(route('kas.store'), [
            'title' => 'Iuran Kas Bulan Oktober',
            'amount' => 10000,
            'type' => 'BULANAN',
            'target_type' => 'DIVISI',
            'target_division_id' => $this->division->id,
            'deadline' => '2026-10-31',
            'description' => 'Iuran wajib anggota divisi kegiatan',
        ]);

        $response->assertRedirect(route('kas.index'));
        $this->assertDatabaseHas('dues', [
            'title' => 'Iuran Kas Bulan Oktober',
            'amount' => 10000,
            'target_type' => 'DIVISI',
            'target_division_id' => $this->division->id,
        ]);

        $due = Due::where('title', 'Iuran Kas Bulan Oktober')->first();

        // 2. Update Due
        $responseUpdate = $this->actingAs($this->bendahara)->put(route('kas.update', $due->id), [
            'title' => 'Iuran Kas Bulan Oktober (Revisi)',
            'amount' => 15000,
            'type' => 'BULANAN',
            'target_type' => 'SEMUA',
            'deadline' => '2026-11-05',
            'description' => 'Revisi untuk semua anggota',
        ]);

        $responseUpdate->assertRedirect(route('kas.index'));
        $this->assertDatabaseHas('dues', [
            'id' => $due->id,
            'title' => 'Iuran Kas Bulan Oktober (Revisi)',
            'amount' => 15000,
            'target_type' => 'SEMUA',
        ]);

        // 3. Delete Due without payments succeeds
        $responseDelete = $this->actingAs($this->bendahara)->delete(route('kas.destroy', $due->id));
        $responseDelete->assertRedirect(route('kas.index'));
        $this->assertDatabaseMissing('dues', ['id' => $due->id]);
    }

    public function test_member_payment_submission_status_is_pending_and_not_counted_in_cash(): void
    {
        Storage::fake('public');

        $due = Due::create([
            'title' => 'Kas Wajib Member',
            'amount' => 5000,
            'type' => 'BULANAN',
            'target_type' => 'SEMUA',
        ]);

        $fakeProof = UploadedFile::fake()->create('bukti_transfer.jpg', 150, 'image/jpeg');

        $response = $this->actingAs($this->memberUser)->post(route('payments.store'), [
            'due_id' => $due->id,
            'payment_method' => 'QRIS',
            'proof' => $fakeProof,
        ]);

        $response->assertRedirect(route('kas.index'));

        // Payment status must be MENUNGGU_VERIFIKASI
        $this->assertDatabaseHas('payments', [
            'due_id' => $due->id,
            'user_id' => $this->memberUser->id,
            'amount_paid' => 5000,
            'payment_method' => 'QRIS',
            'status' => 'MENUNGGU_VERIFIKASI',
        ]);

        // Cash transaction must NOT be created for this payment yet
        $payment = Payment::where('due_id', $due->id)->where('user_id', $this->memberUser->id)->first();
        $this->assertNotNull($payment);
        $this->assertDatabaseMissing('cash_transactions', [
            'payment_id' => $payment->id,
        ]);
    }

    public function test_pengurus_can_approve_payment_and_creates_cash_transaction(): void
    {
        Storage::fake('public');

        $due = Due::create([
            'title' => 'Kas Event PIK-R',
            'amount' => 20000,
            'type' => 'KEGIATAN',
            'target_type' => 'SEMUA',
        ]);

        $payment = Payment::create([
            'due_id' => $due->id,
            'user_id' => $this->memberUser->id,
            'amount_paid' => 20000,
            'payment_method' => 'QRIS',
            'proof_file' => 'proofs/dummy.jpg',
            'status' => 'MENUNGGU_VERIFIKASI',
        ]);

        $initialIncome = (float) CashTransaction::where('type', 'INCOME')->sum('amount');

        // Bendahara verifies and approves payment
        $response = $this->actingAs($this->bendahara)->post(route('payments.verify', $payment->id), [
            'action' => 'setujui',
        ]);

        $response->assertRedirect();

        // Payment must be LUNAS
        $payment->refresh();
        $this->assertEquals('LUNAS', $payment->status);
        $this->assertEquals($this->bendahara->id, $payment->verified_by_user_id);
        $this->assertNotNull($payment->verified_at);

        // CashTransaction must be created
        $this->assertDatabaseHas('cash_transactions', [
            'payment_id' => $payment->id,
            'type' => 'INCOME',
            'amount' => 20000,
        ]);

        $newIncome = (float) CashTransaction::where('type', 'INCOME')->sum('amount');
        $this->assertEquals($initialIncome + 20000, $newIncome);
    }

    public function test_pengurus_can_reject_payment_and_removes_any_cash_transaction(): void
    {
        $due = Due::create([
            'title' => 'Kas Khusus',
            'amount' => 15000,
            'type' => 'KHUSUS',
            'target_type' => 'SEMUA',
        ]);

        $payment = Payment::create([
            'due_id' => $due->id,
            'user_id' => $this->memberUser->id,
            'amount_paid' => 15000,
            'payment_method' => 'QRIS',
            'proof_file' => 'proofs/dummy.jpg',
            'status' => 'MENUNGGU_VERIFIKASI',
        ]);

        $initialTxCount = CashTransaction::count();

        // Bendahara rejects payment
        $response = $this->actingAs($this->bendahara)->post(route('payments.verify', $payment->id), [
            'action' => 'tolak',
            'rejection_reason' => 'Gambar buram dan mutasi belum masuk.',
        ]);

        $response->assertRedirect();

        $payment->refresh();
        $this->assertEquals('DITOLAK', $payment->status);
        $this->assertEquals('Gambar buram dan mutasi belum masuk.', $payment->rejection_reason);

        // Verify no cash transaction for this payment and count unchanged
        $this->assertDatabaseMissing('cash_transactions', [
            'payment_id' => $payment->id,
        ]);
        $this->assertEquals($initialTxCount, CashTransaction::count());
    }

    public function test_due_with_verified_payment_cannot_be_deleted(): void
    {
        $due = Due::create([
            'title' => 'Kas Terkunci',
            'amount' => 10000,
            'type' => 'BULANAN',
            'target_type' => 'SEMUA',
        ]);

        Payment::create([
            'due_id' => $due->id,
            'user_id' => $this->memberUser->id,
            'amount_paid' => 10000,
            'status' => 'LUNAS',
            'payment_method' => 'CASH',
        ]);

        $response = $this->actingAs($this->bendahara)->delete(route('kas.destroy', $due->id));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Due must still exist
        $this->assertDatabaseHas('dues', ['id' => $due->id]);
    }

    public function test_export_rekap_kas_csv_downloads_valid_csv(): void
    {
        $due = Due::create([
            'title' => 'Iuran Ekspor',
            'amount' => 12000,
            'type' => 'BULANAN',
        ]);

        Payment::create([
            'due_id' => $due->id,
            'user_id' => $this->memberUser->id,
            'amount_paid' => 12000,
            'status' => 'LUNAS',
            'payment_method' => 'QRIS',
        ]);

        $response = $this->actingAs($this->bendahara)->get(route('kas.export'));
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('Iuran Ekspor', $content);
        $this->assertStringContainsString('12000', $content);
    }

    public function test_dashboard_synchronization_for_pengurus_and_member(): void
    {
        $due = Due::create([
            'title' => 'Kas Sinkronisasi',
            'amount' => 25000,
            'type' => 'BULANAN',
            'target_type' => 'SEMUA',
        ]);

        // 1. Pengurus Dashboard check
        $resPengurus = $this->actingAs($this->bendahara)->get(route('dashboard'));
        $resPengurus->assertStatus(200);
        $resPengurus->assertSee('Ringkasan Kas & Tagihan PIK-R SKAKMATT', false);
        $resPengurus->assertSee('Dana Terverifikasi');
        $resPengurus->assertSee('Target Anggota');

        // 2. Member Dashboard check
        $resMember = $this->actingAs($this->memberUser)->get(route('dashboard'));
        $resMember->assertStatus(200);
        $resMember->assertSee('Status Tagihan Kas Saya');
        $resMember->assertSee('Kas Sinkronisasi');
        $resMember->assertSee('Bayar Sekarang');
    }

    public function test_payment_modal_displays_bank_accounts_and_ewallet_number(): void
    {
        $response = $this->actingAs($this->memberUser)->get(route('kas.index'));
        $response->assertStatus(200);
        $response->assertSee('0882003684111');
        $response->assertSee('KAS PIK-R SKAKMATT');
        $response->assertSee('046-2003-6841');
        $response->assertSee('0123-01-098765-50-1');
        $response->assertSee('3-012-34567-8');
        $response->assertSee('Bayar Sekarang');
    }

    public function test_member_can_submit_payment_with_ewallet_and_transfer_methods(): void
    {
        Storage::fake('public');

        $due = Due::create([
            'title' => 'Iuran Multi Metode',
            'amount' => 10000,
            'type' => 'BULANAN',
            'target_type' => 'SEMUA',
        ]);

        $fakeProof = UploadedFile::fake()->create('bukti_ewallet.jpg', 150, 'image/jpeg');

        // Submit via EWALLET
        $response = $this->actingAs($this->memberUser)->post(route('payments.store'), [
            'due_id' => $due->id,
            'payment_method' => 'EWALLET',
            'proof' => $fakeProof,
        ]);

        $response->assertRedirect(route('kas.index'));

        $this->assertDatabaseHas('payments', [
            'due_id' => $due->id,
            'user_id' => $this->memberUser->id,
            'amount_paid' => 10000,
            'payment_method' => 'TRANSFER',
            'status' => 'MENUNGGU_VERIFIKASI',
        ]);
    }
}
