<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Folder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Nomes dos meses em português
     */
    protected array $monthNames = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro'
    ];

    /**
     * Retorna os relatórios com periodicidade semanal agrupados por Ano > Mês > Semana.
     * Serve como índice semanal dos documentos que entraram na aplicação.
     */
    public function weeklyIndex(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'ano' => 'nullable|integer|min:2000|max:2100',
            'mes' => 'nullable|integer|min:1|max:12',
            'busca' => 'nullable|string|max:150',
            'pasta_id' => 'nullable|uuid|exists:folders,id',
        ]);

        $query = Document::query()
            ->with(['folder:id,name,reference_code', 'uploader:id,name,email,avatar,avatar_color,role'])
            ->whereNull('deleted_at');

        // Controlo de Acesso e Permissões Seguras
        if (!$user->isAdmin()) {
            $userGroupIds = $user->groups()->pluck('groups.id')->toArray();

            $query->where(function ($q) use ($user, $userGroupIds) {
                // Utilizador subiu o documento
                $q->where('user_id', $user->id)
                    // Permissão direta no documento
                    ->orWhereHas('permissions', function ($pq) use ($user, $userGroupIds) {
                        $pq->where('can_view', true)
                            ->where(function ($inner) use ($user, $userGroupIds) {
                                $inner->where('user_id', $user->id);
                                if (!empty($userGroupIds)) {
                                    $inner->orWhereIn('group_id', $userGroupIds);
                                }
                            });
                    })
                    // Permissão herdada da pasta
                    ->orWhereHas('folder.permissions', function ($fpq) use ($user, $userGroupIds) {
                        $fpq->where('can_view', true)
                            ->where(function ($inner) use ($user, $userGroupIds) {
                                $inner->where('user_id', $user->id);
                                if (!empty($userGroupIds)) {
                                    $inner->orWhereIn('group_id', $userGroupIds);
                                }
                            });
                    });
            });
        }

        // Filtro por pasta
        if ($request->filled('pasta_id')) {
            $query->where('folder_id', $request->pasta_id);
        }

        // Filtro por Ano
        if ($request->filled('ano')) {
            $query->whereYear('created_at', $request->ano);
        }

        // Filtro por Mês
        if ($request->filled('mes')) {
            $query->whereMonth('created_at', $request->mes);
        }

        // Filtro de pesquisa textual
        if ($request->filled('busca')) {
            $searchTerm = '%' . strtolower($request->busca) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(reference_code) LIKE ?', [$searchTerm])
                    ->orWhereHas('folder', function ($fq) use ($searchTerm) {
                        $fq->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                            ->orWhereRaw('LOWER(reference_code) LIKE ?', [$searchTerm]);
                    })
                    ->orWhereHas('uploader', function ($uq) use ($searchTerm) {
                        $uq->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                            ->orWhereRaw('LOWER(email) LIKE ?', [$searchTerm]);
                    });
            });
        }

        $documents = $query->orderBy('created_at', 'desc')->get();

        // Estruturação hierárquica: Ano -> Mês -> Semana -> Documentos
        $yearsData = [];
        $totalSizeBytes = 0;
        $totalWeeksSet = [];
        $availableYearsSet = [];

        foreach ($documents as $doc) {
            $createdAt = Carbon::parse($doc->created_at);
            $year = $createdAt->year;
            $month = $createdAt->month;
            $weekNumber = $createdAt->isoWeek;
            $startOfWeek = $createdAt->copy()->startOfWeek()->format('d/m/Y');
            $endOfWeek = $createdAt->copy()->endOfWeek()->format('d/m/Y');
            $weekKey = "W{$weekNumber}_{$year}_{$month}";

            $availableYearsSet[$year] = true;
            $totalWeeksSet["{$year}_{$weekNumber}"] = true;
            $totalSizeBytes += (int) $doc->size;

            // Formatação do código do documento
            $folderRef = $doc->folder?->reference_code ?: ($doc->folder?->name ? strtoupper(substr($doc->folder->name, 0, 2)) : 'DC');
            $docCode = !empty($doc->reference_code) ? $doc->reference_code : ($folderRef . '.' . $doc->name);

            // Objeto do Documento formatado para o frontend
            $docItem = [
                'id' => $doc->id,
                'name' => $doc->name,
                'code' => $docCode,
                'mime_type' => $doc->mime_type,
                'size' => (int) $doc->size,
                'size_formatted' => $this->formatBytes((int) $doc->size),
                'folder' => [
                    'id' => $doc->folder?->id,
                    'name' => $doc->folder?->name ?? 'Sem Pasta',
                    'reference_code' => $doc->folder?->reference_code ?? $folderRef,
                ],
                'uploader' => [
                    'id' => $doc->uploader?->id,
                    'name' => $doc->uploader?->name ?? 'Sistema',
                    'email' => $doc->uploader?->email ?? '',
                    'avatar' => $doc->uploader?->avatar,
                    'avatar_color' => $doc->uploader?->avatar_color ?? '#4fb852',
                ],
                'created_at' => $doc->created_at->toIso8601String(),
                'created_at_formatted' => $createdAt->format('d/m/Y H:i'),
                'created_at_date' => $createdAt->format('d/m/Y'),
                'created_at_time' => $createdAt->format('H:i'),
            ];

            // Inicializar Ano
            if (!isset($yearsData[$year])) {
                $yearsData[$year] = [
                    'year' => $year,
                    'count' => 0,
                    'size_bytes' => 0,
                    'size_formatted' => '0 B',
                    'months' => [],
                ];
            }
            $yearsData[$year]['count']++;
            $yearsData[$year]['size_bytes'] += (int) $doc->size;

            // Inicializar Mês
            if (!isset($yearsData[$year]['months'][$month])) {
                $yearsData[$year]['months'][$month] = [
                    'month' => $month,
                    'name' => $this->monthNames[$month] ?? "Mês {$month}",
                    'count' => 0,
                    'size_bytes' => 0,
                    'size_formatted' => '0 B',
                    'weeks' => [],
                ];
            }
            $yearsData[$year]['months'][$month]['count']++;
            $yearsData[$year]['months'][$month]['size_bytes'] += (int) $doc->size;

            // Inicializar Semana
            if (!isset($yearsData[$year]['months'][$month]['weeks'][$weekKey])) {
                $yearsData[$year]['months'][$month]['weeks'][$weekKey] = [
                    'week_key' => $weekKey,
                    'week_number' => $weekNumber,
                    'start_date' => $startOfWeek,
                    'end_date' => $endOfWeek,
                    'label' => "Semana {$weekNumber} ({$startOfWeek} a {$endOfWeek})",
                    'short_label' => "Semana {$weekNumber}",
                    'date_range' => "{$startOfWeek} - {$endOfWeek}",
                    'count' => 0,
                    'size_bytes' => 0,
                    'size_formatted' => '0 B',
                    'documents' => [],
                ];
            }
            $yearsData[$year]['months'][$month]['weeks'][$weekKey]['count']++;
            $yearsData[$year]['months'][$month]['weeks'][$weekKey]['size_bytes'] += (int) $doc->size;
            $yearsData[$year]['months'][$month]['weeks'][$weekKey]['documents'][] = $docItem;
        }

        // Converter arrays associativos em listas ordenadas
        $formattedYears = [];
        krsort($yearsData); // Anos mais recentes primeiro

        foreach ($yearsData as $year => $yearNode) {
            $yearNode['size_formatted'] = $this->formatBytes($yearNode['size_bytes']);
            $monthsList = [];
            krsort($yearNode['months']); // Meses mais recentes primeiro

            foreach ($yearNode['months'] as $monthNum => $monthNode) {
                $monthNode['size_formatted'] = $this->formatBytes($monthNode['size_bytes']);
                $weeksList = [];
                // Semanas mais recentes primeiro dentro do mês
                usort($monthNode['weeks'], function ($a, $b) {
                    return $b['week_number'] <=> $a['week_number'];
                });

                foreach ($monthNode['weeks'] as $weekNode) {
                    $weekNode['size_formatted'] = $this->formatBytes($weekNode['size_bytes']);
                    $weeksList[] = $weekNode;
                }

                $monthNode['weeks'] = $weeksList;
                $monthsList[] = $monthNode;
            }

            $yearNode['months'] = $monthsList;
            $formattedYears[] = $yearNode;
        }

        // Anos disponíveis para o seletor rápido
        $availableYears = array_keys($availableYearsSet);
        rsort($availableYears);

        return response()->json([
            'summary' => [
                'total_documents' => $documents->count(),
                'total_size_bytes' => $totalSizeBytes,
                'total_size_formatted' => $this->formatBytes($totalSizeBytes),
                'total_weeks' => count($totalWeeksSet),
                'available_years' => $availableYears,
            ],
            'years' => $formattedYears,
        ]);
    }

    /**
     * Exporta o índice semanal de documentos em formato CSV para conferência e auditoria.
     */
    public function exportWeek(Request $request): StreamedResponse
    {
        $request->validate([
            'ano' => 'required|integer',
            'semana' => 'required|integer|min:1|max:53',
        ]);

        $ano = (int) $request->ano;
        $semana = (int) $request->semana;

        $user = $request->user();

        $query = Document::query()
            ->with(['folder', 'uploader'])
            ->whereNull('deleted_at')
            ->whereYear('created_at', $ano);

        if (!$user->isAdmin()) {
            $userGroupIds = $user->groups()->pluck('groups.id')->toArray();
            $query->where(function ($q) use ($user, $userGroupIds) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('permissions', function ($pq) use ($user, $userGroupIds) {
                        $pq->where('can_view', true)
                            ->where(function ($inner) use ($user, $userGroupIds) {
                                $inner->where('user_id', $user->id);
                                if (!empty($userGroupIds)) {
                                    $inner->orWhereIn('group_id', $userGroupIds);
                                }
                            });
                    });
            });
        }

        $allDocs = $query->orderBy('created_at', 'asc')->get();

        // Filtrar documentos que pertencem à semana solicitada
        $weekDocs = $allDocs->filter(function ($doc) use ($semana) {
            return Carbon::parse($doc->created_at)->isoWeek === $semana;
        });

        $filename = "indice_semanal_semana_{$semana}_{$ano}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($weekDocs) {
            $handle = fopen('php://output', 'w');
            // Adicionar BOM UTF-8 para garantir abertura correta no Excel
            fputs($handle, "\xEF\xBB\xBF");

            // Cabeçalhos das colunas
            fputcsv($handle, [
                'Código',
                'Nome do Documento',
                'Pasta',
                'Submetido Por',
                'Data de Submissão',
                'Hora de Submissão',
                'Tamanho',
                'Formato'
            ], ';');

            foreach ($weekDocs as $doc) {
                $createdAt = Carbon::parse($doc->created_at);
                $folderRef = $doc->folder?->reference_code ?: ($doc->folder?->name ? strtoupper(substr($doc->folder->name, 0, 2)) : 'DC');
                $docCode = !empty($doc->reference_code) ? $doc->reference_code : ($folderRef . '.' . $doc->name);

                fputcsv($handle, [
                    $docCode,
                    $doc->name,
                    $doc->folder?->name ?? 'Sem Pasta',
                    $doc->uploader?->name ?? 'Desconhecido',
                    $createdAt->format('d/m/Y'),
                    $createdAt->format('H:i:s'),
                    $this->formatBytes((int) $doc->size),
                    $doc->mime_type ?? 'N/A'
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Formatação legível de tamanho de ficheiro em Bytes, KB, MB, GB
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        $value = $bytes / pow(1024, $power);

        return round($value, $precision) . ' ' . $units[$power];
    }
}
