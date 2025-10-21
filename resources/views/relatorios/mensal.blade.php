<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Mensal - {{ $inicioMes->format('F Y') }}</title>
    <style>
        .date{font-style: italic;font-size: 10pt;}
        .name
        {
            
        }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left;}
        th { background: #f2f2f2; }
        h1, h4 { margin: 0; padding: 0; }
        .meta { margin-top: 8px; font-size: 11px; color: #555; }
    </style>
</head>
<body>
    <h1>Relatório Mensal</h1>
    <h4>Período: {{ $inicioMes->format('d/m/Y') }} — {{ $fimMes->format('d/m/Y') }}</h4>

    <div class="meta">Gerado em: {{ now()->format('d/m/Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Criado em</th>
                <th>Referência</th>
                <th>Nome</th>
                <th>Criado Por</th>
                <!-- Adiciona colunas conforme o teu modelo -->
            </tr>
        </thead>
        <tbody>
            @php $i = 1;@endphp
            @forelse($pedidos as $pedido)
                <tr>
                    <td class="id">{{ $i++ }}</td>
                    <td class="date">{{ $pedido->created_at->format('d/m/Y') }}</td>
                    <td>{{ $pedido->file_ref}}</td>
                    <td class="name">{{ $pedido->name }}</td>
                    <td> {{ $pedido->creator->name }} </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Nenhum registro no período.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
