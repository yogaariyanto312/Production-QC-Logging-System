<?php

use App\Models\Note;
use App\Models\SchedulePhoto;
use App\Services\BotNotificationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hapus semua foto jadwal setiap Senin pukul 00:01
// Jalankan cron: * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
Artisan::command('jadwal:clear', function () {
    $photos = SchedulePhoto::all();
    $count  = $photos->count();

    foreach ($photos as $photo) {
        Storage::disk('public')->delete($photo->file_path);
        // Hapus folder tanggal jika sudah kosong
        Storage::disk('public')->deleteDirectory(dirname($photo->file_path));
    }

    SchedulePhoto::truncate();

    $this->info("Selesai: {$count} foto jadwal dihapus dari server.");
})->purpose('Hapus semua foto jadwal dari server (dijadwalkan tiap Senin)');

Schedule::command('jadwal:clear')->weekly()->mondays()->at('00:01');

// Hapus catatan yang sudah ditandai selesai lebih dari 8 jam
Artisan::command('catatan:clear-done', function () {
    $count = Note::where('is_done', true)
        ->whereNotNull('done_at')
        ->where('done_at', '<=', now()->subHours(8))
        ->delete();

    $this->info("Selesai: {$count} catatan yang sudah selesai dihapus.");
})->purpose('Hapus catatan yang sudah ditandai selesai lebih dari 8 jam');

Schedule::command('catatan:clear-done')->hourly();

// Kirim laporan harian setiap jam 10 malam
Artisan::command('laporan:harian', function () {
    $setting = \App\Models\BotSetting::instance();
    if (!$setting->report_enabled) {
        $this->info('Laporan harian dinonaktifkan — dilewati.');
        return;
    }
    $result = BotNotificationService::sendDailyReport();
    if ($result['ok']) {
        $this->info($result['message']);
    } else {
        $this->warn($result['message']);
    }
})->purpose('Kirim laporan harian produksi ke Telegram/Discord setiap jam 10 malam');

Schedule::command('laporan:harian')->dailyAt('22:00')->timezone('Asia/Jakarta');
