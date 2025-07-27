<?php

namespace App\Console\Commands;

use App\Services\GoogleSheetsService;
use Illuminate\Console\Command;

class FetchGoogleSheetData extends Command
{
    protected $signature = 'sheets:fetch {--count= : Укажите кол-во строк для показа}';

    protected $description = 'Получает данные из Google таблицы и отображает их';

    public function handle()
    {
        try {
            $service = new GoogleSheetsService;
            $data = $service->getSheetData();

            if (empty($data)) {
                $this->error('В Google таблице не было найдено данных');

                return 1;
            }

            $rows = array_slice($data, 1);

            $count = $this->option('count');
            if ($count && is_numeric($count)) {
                $rows = array_slice($rows, 0, (int) $count);
            }

            if (empty($rows)) {
                $this->info('Нет строк для отображения');

                return 0;
            }

            return $this::showCleanProgress($rows);

        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());

            return 1;
        }
    }

    private function showCleanProgress($rows)
    {
        $this->info('🔄 Получаем данные из Google таблицы...');
        $this->line('📊 Всего строк для обработки: '.count($rows));
        $this->newLine();

        $bar = $this->output->createProgressBar(count($rows));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%%');
        $bar->start();

        $results = [];
        foreach ($rows as $index => $row) {
            $id = $row[0] ?? 'Н/Д';
            $comment = $row[6] ?? '-';

            $results[] = [
                'id' => $id,
                'comment' => $comment,
            ];

            $bar->advance();
            // Имитация обработки, иначе полоска слишком быстро заполняется
            // Даже при полсотни тысяч данных, всё равно заполняется моментально
            usleep(100);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('✅ Выгрузка данных успешно завершена!');
        $this->newLine();

        foreach ($results as $result) {
            $this->line("ID: {$result['id']} | Комментарий: {$result['comment']}");
        }

        return 0;
    }
}
