<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\File;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

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
        $this->info('Iniciando geração de relatório mensal...');
        
        $inicioMes = Carbon::now()->startOfMonth();
        $fimMes = Carbon::now()->endOfMonth();

        // Exemplo: pegar todos os pedidos do mês
        $pedidos = File::whereBetween('created_at', [$inicioMes, $fimMes])->orderBy('created_at')->get();

        // Aqui podes gerar o relatório em PDF
        $pdf = PDF::loadView('relatorios.mensal', compact('pedidos', 'inicioMes', 'fimMes'));

        // Salvar no storage
        $path = 'app/relatorios/';
        $nome = 'relatorio_' . now()->format('Y_m') . '.pdf';
        $pdf->save(storage_path($path . $nome));

        // mover o relatorio para o wasabi
        Storage::disk('wasabi')->putFileAs('reports', storage_path($path . $nome), $nome);

        $this->info("Relatório mensal gerado: {$nome}");

        $this->info("Enviando o Relatório por email.");

         try {
            $adminEmail = config('mail.admin_address', env('MAIL_USERNAME')); // define em .env ADMIN_EMAIL=...
            if ($adminEmail) {
                // Use Mail::raw to send a plain text body (compatible with Symfony Mime API)
                $filePath = storage_path( $path . $nome);

                if (!file_exists($filePath)) {
                    $this->error("Arquivo de relatório não encontrado em: {$filePath}");
                } else {
                    Mail::raw('Segue em anexo o relatório mensal.', function ($message) use ($adminEmail, $filePath, $nome) {
                        $message->to($adminEmail)
                            ->subject('Relatório Mensal - ' . now()->format('F Y'))
                            ->attach($filePath, [
                                'as' => $nome,
                                'mime' => 'application/pdf',
                            ]);
                    });

                    $this->info("Relatório enviado por e-mail para {$adminEmail}");
                }
            } else {
                $this->info("ADMIN_EMAIL não definido. Saltando envio por e-mail.");
            }
        } catch (\Exception $e) {
            $this->error('Erro ao enviar e-mail: ' . $e->getMessage());
        }
    }
}
