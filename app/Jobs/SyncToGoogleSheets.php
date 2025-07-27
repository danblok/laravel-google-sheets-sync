<?php

namespace App\Jobs;

use App\Services\GoogleSheetsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncToGoogleSheets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        try {
            $service = new GoogleSheetsService;
            $result = $service->syncToSheet();

            if ($result) {
                Log::info('Google Sheets sync job completed successfully');
            } else {
                Log::warning('Google Sheets sync job failed');
            }
        } catch (\Exception $e) {
            Log::error('Google Sheets sync job error: '.$e->getMessage());
            throw $e;
        }
    }
}
