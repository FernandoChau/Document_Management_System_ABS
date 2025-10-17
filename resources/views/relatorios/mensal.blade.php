<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Mensal - {{ $inicioMes->format('F Y') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
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
                <th>ID</th>
                <th>Cliente</th>
                <th>Valor</th>
                <th>Data</th>
                <!-- Adiciona colunas conforme o teu modelo -->
            </tr>
        </thead>
        <tbody>
            @forelse($pedidos as $fild)
                <tr>
                    <td>{{ $pedido->id }}</td>
                    <td>{{ $pedido->name ?? ($pedido->cliente->name ?? '—') }}</td>
                    <td> Ola </td>
                    <!-- <td>{{ number_format($pedido->valor ?? 0, 2, ',', '.') }}</td> -->
                    <td>{{ $pedido->created_at->format('d/m/Y') }}</td>
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
