<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Setting;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ClearValuesRequest;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    private $client;

    private $service;

    private $spreadsheetId;

    public function __construct()
    {
        $this->initializeClient();
        $this->extractSpreadsheetId();
    }

    private function initializeClient()
    {
        $this->client = new Client;
        $this->client->setApplicationName('Laravel Google Sheets Sync');
        $this->client->setScopes([Sheets::SPREADSHEETS]);
        $this->client->setAccessType('offline');

        $credentialsPath = storage_path(env('GOOGLE_APPLICATION_CREDENTIALS'));

        if (! $credentialsPath || ! file_exists($credentialsPath)) {
            throw new \Exception('Google credentials file not found. Path: '.$credentialsPath.':'.$credentialsPath);
        }

        $this->client->setAuthConfig($credentialsPath);
        $this->service = new Sheets($this->client);
    }

    private function extractSpreadsheetId()
    {
        $url = Setting::get('google_sheet_url');
        if ($url) {
            preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
            $this->spreadsheetId = $matches[1] ?? null;
        }
    }

    public function syncToSheet()
    {
        if (! $this->spreadsheetId) {
            Log::warning('No Google Sheet URL configured');

            return false;
        }

        try {
            $currentData = $this->getCurrentSheetData();
            $comments = $this->extractComments($currentData);

            $items = Item::allowed()->get();

            $values = [
                ['ID', 'Name', 'Description', 'Status', 'Created At', 'Updated At', 'Comments'],
            ];

            foreach ($items as $item) {
                $comment = $comments[$item->id] ?? '';
                $values[] = [
                    $item->id,
                    $item->name,
                    $item->description ?? '',
                    $item->status,
                    $item->created_at->toDateTimeString(),
                    $item->updated_at->toDateTimeString(),
                    $comment,
                ];
            }

            $this->clearSheet();
            $this->writeToSheet($values);

            Log::info('Successfully synced '.count($items).' items to Google Sheets');

            return true;

        } catch (\Exception $e) {
            Log::error('Google Sheets sync error: '.$e->getMessage());

            return false;
        }
    }

    private function getCurrentSheetData()
    {
        try {
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, 'A:G');

            return $response->getValues() ?? [];
        } catch (\Exception $e) {
            Log::warning('Could not fetch current sheet data: '.$e->getMessage());

            return [];
        }
    }

    private function extractComments($data)
    {
        $comments = [];
        if (empty($data) || count($data) <= 1) {
            return $comments;
        }

        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];
            if (isset($row[0]) && isset($row[6])) { // ID и Comments
                $comments[$row[0]] = $row[6];
            }
        }

        return $comments;
    }

    private function clearSheet()
    {
        $this->service->spreadsheets_values->clear(
            $this->spreadsheetId,
            'A:G',
            new ClearValuesRequest
        );
    }

    private function writeToSheet($values)
    {
        $body = new ValueRange(['values' => $values]);
        $params = ['valueInputOption' => 'RAW'];

        $this->service->spreadsheets_values->update(
            $this->spreadsheetId,
            'A1',
            $body,
            $params
        );
    }

    public function getSheetData()
    {
        if (! $this->spreadsheetId) {
            return [];
        }

        try {
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, 'A:G');

            return $response->getValues() ?? [];
        } catch (\Exception $e) {
            Log::error('Error fetching sheet data: '.$e->getMessage());

            return [];
        }
    }

    public function testConnection()
    {
        try {
            if (! $this->spreadsheetId) {
                return ['success' => false, 'message' => 'ID таблицы не настроено'];
            }

            $this->service->spreadsheets->get($this->spreadsheetId);

            return ['success' => true, 'message' => 'Успешное подключение'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
