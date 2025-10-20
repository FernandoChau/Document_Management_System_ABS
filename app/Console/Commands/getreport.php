<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use PDF;
use App\Models\File;
use Illuminate\Support\Facades\Storage;

class getreport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:getreport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera relatórios mensais automaticamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $fimMes = Carbon::now()->endOfMonth();

        // Exemplo: pegar todos os pedidos do mês
        $pedidos = File::whereBetween('created_at', [$inicioMes, $fimMes])->get();

        // Aqui podes gerar o relatório em PDF
        $pdf = PDF::loadView('relatorios.mensal', compact('pedidos', 'inicioMes', 'fimMes'));

        // Salvar no storage
        $path = 'app/relatorios/';
        $nome = 'relatorio_' . now()->format('Y_m') . '.pdf';
        $pdf->save(storage_path($path . $nome));

        // mover o relatorio para o wasabi
        Storage::disk('wasabi')->putFileAs('reports', storage_path($path . $nome), $nome);

        $this->info("Relatório mensal gerado: {$nome}");
    }
}
