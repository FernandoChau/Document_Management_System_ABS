<?php

namespace App\Console\Commands;

use App\Services\FolderZipService;
use Illuminate\Console\Command;

class CleanOldZips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-old-zips {--hours=1 : Deletar ZIPs mais antigos que N horas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpar arquivos ZIP temporários antigos';

    /**
     * Execute the console command.
     */
    public function handle(FolderZipService $folderZipService)
    {
        $hours = (int) $this->option('hours');
        $count = $folderZipService->cleanOldZips($hours);

        $this->info("Removidos {$count} arquivos ZIP antigos (mais de {$hours} hora(s))");
    }
}
