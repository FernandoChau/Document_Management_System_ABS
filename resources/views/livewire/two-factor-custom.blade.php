<div class="p-4 bg-gray-100 rounded">
    @if (session()->has('message'))
        <div class="mb-4 text-green-600">{{ session('message') }}</div>
    @endif

    @if ($enabled)
        <h2 class="font-bold text-lg mb-2">Autenticação de Dois Fatores Ativada</h2>
        <div class="mb-4">
            {!! $qrCode !!}
        </div>
        <div class="mb-4">
            <h3 class="font-semibold">Códigos de Recuperação:</h3>
            <ul class="list-disc pl-6">
                @foreach ($recoveryCodes as $code)
                    <li>{{ $code }}</li>
                @endforeach
            </ul>
        </div>
        <button wire:click="disable2FA" class="bg-red-500 text-white px-4 py-2 rounded">
            Desativar 2FA
        </button>
    @else
        <h2 class="font-bold text-lg mb-2">Ativar Autenticação de Dois Fatores</h2>
        <button wire:click="enable2FA" class="bg-blue-500 text-white px-4 py-2 rounded">
            Ativar 2FA
        </button>
    @endif
</div>
