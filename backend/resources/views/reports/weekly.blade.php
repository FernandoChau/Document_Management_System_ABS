<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 10px; }
        h1 { margin: 0 0 5px; font-size: 20px; }
        .summary { margin-bottom: 18px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 7px 6px; text-align: left; }
        th { background: #eef2ff; font-weight: bold; }
        tr:nth-child(even) { background: #f9fafb; }
    </style>
</head>
<body>
    <h1>Relatório Semanal de Documentos</h1>
    <div class="summary">
        Semana {{ $week }} de {{ $year }} | Período: {{ $dateRange }} |
        {{ $documents->count() }} {{ $documents->count() === 1 ? 'documento' : 'documentos' }} |
        Volume: {{ $totalSize }}
    </div>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Documento</th>
                <th>Pasta</th>
                <th>Submetido por</th>
                <th>Data</th>
                <th>Hora</th>
                <th>Tamanho</th>
                <th>Formato</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($documents as $document)
                <tr>
                    <td>{{ $document->reference_code }}</td>
                    <td>{{ $document->name }}</td>
                    <td>{{ $document->folder?->name ?? 'Sem Pasta' }}</td>
                    <td>{{ $document->uploader?->name ?? 'Desconhecido' }}</td>
                    <td>{{ $document->created_at?->format('d/m/Y') }}</td>
                    <td>{{ $document->created_at?->format('H:i:s') }}</td>
                    <td>{{ $document->size_formatted }}</td>
                    <td>{{ $document->mime_type ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Nenhum documento encontrado nesta semana.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
