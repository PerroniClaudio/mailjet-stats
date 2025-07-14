<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messaggi del {{ date('d/m/Y', strtotime($selectedDate)) }} - Dashboard Mailjet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Messaggi del
                        {{ date('d/m/Y', strtotime($selectedDate)) }}</h1>
                    <p class="text-gray-600 mt-2">Elenco dettagliato dei messaggi inviati in questo giorno</p>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('mailjet.dashboard') }}"
                        class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        ← Torna alla Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Selettore Data -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Seleziona Data</h3>
            <form method="GET" action="{{ route('mailjet.daily-messages') }}" class="flex flex-col gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data</label>
                    <input type="date" name="date" value="{{ $selectedDate }}"
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Visualizza
                    </button>
                </div>
            </form>
        </div>

        @if (isset($error))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <strong>Errore:</strong> {{ $error }}
            </div>
        @endif

        @if ($messages && count($messages) > 0)
            <!-- Nota informativa -->
            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm">
                        <strong>Informazione:</strong> Le email dei destinatari vengono recuperate tramite API Mailjet
                        per ogni messaggio.
                        Il caricamento potrebbe richiedere alcuni secondi per completarsi.
                    </span>
                </div>
            </div>
        @endif

        @if ($dailyStats && count($dailyStats) > 0)
            <!-- Statistiche del Giorno -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistiche del Giorno</h3>
                @php $stats = $dailyStats[0]; @endphp
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">
                            {{ number_format($stats['MessageSentCount'] ?? 0) }}</div>
                        <div class="text-sm text-gray-500">Inviate</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">
                            {{ number_format($stats['MessageOpenedCount'] ?? 0) }}</div>
                        <div class="text-sm text-gray-500">Aperte</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">
                            {{ number_format($stats['MessageClickedCount'] ?? 0) }}</div>
                        <div class="text-sm text-gray-500">Cliccate</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600">
                            {{ number_format($stats['MessageBlockedCount'] ?? 0) }}</div>
                        <div class="text-sm text-gray-500">Bloccate</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600">
                            {{ number_format(($stats['MessageHardBouncedCount'] ?? 0) + ($stats['MessageSoftBouncedCount'] ?? 0)) }}
                        </div>
                        <div class="text-sm text-gray-500">Bounce</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600">
                            {{ number_format($stats['MessageSpamCount'] ?? 0) }}</div>
                        <div class="text-sm text-gray-500">Spam</div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Elenco Messaggi -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Elenco Messaggi</h3>
                        <p class="text-sm text-gray-600">Totale messaggi trovati: {{ number_format($totalMessages) }}
                        </p>
                    </div>
                    <div class="flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Ordinati per data (più recenti prima)
                    </div>
                </div>
            </div>

            @if ($messages && count($messages) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mittente</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email Destinatario</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Subject</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Stato</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dimensione</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data/Ora</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($messages as $message)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $message['ID'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex flex-col">
                                            @if (
                                                ($message['SenderEmail'] ?? 'N/A') !== 'N/A' &&
                                                    ($message['SenderEmail'] ?? 'Mittente non disponibile') !== 'Mittente non disponibile')
                                                <span class="font-medium">{{ $message['SenderEmail'] }}</span>
                                                @if ($message['SenderName'] ?? false)
                                                    <span
                                                        class="text-gray-500 text-xs">{{ $message['SenderName'] }}</span>
                                                @endif
                                                @if ($message['SenderStatus'] ?? false)
                                                    <span
                                                        class="text-xs {{ $message['SenderStatus'] === 'Active' ? 'text-green-600' : 'text-orange-600' }}">
                                                        {{ $message['SenderStatus'] }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-500 italic">Mittente non disponibile</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex flex-col">
                                            @if (
                                                ($message['ContactEmail'] ?? 'N/A') !== 'N/A' &&
                                                    ($message['ContactEmail'] ?? 'Email non disponibile') !== 'Email non disponibile')
                                                <span class="font-medium">{{ $message['ContactEmail'] }}</span>
                                                @if ($message['ContactName'] ?? false)
                                                    <span
                                                        class="text-gray-500 text-xs">{{ $message['ContactName'] }}</span>
                                                @endif
                                            @else
                                                <span class="text-gray-500 italic">Email non disponibile</span>
                                            @endif
                                            <span class="text-gray-400 text-xs">ID:
                                                {{ $message['ContactID'] ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                        <div class="truncate" title="{{ $message['Subject'] ?? 'N/A' }}">
                                            {{ $message['Subject'] ?: '(Nessun oggetto)' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $status = $message['Status'] ?? 'unknown';
                                            $statusClass = match ($status) {
                                                'sent' => 'bg-green-100 text-green-800',
                                                'opened' => 'bg-blue-100 text-blue-800',
                                                'clicked' => 'bg-purple-100 text-purple-800',
                                                'bounced' => 'bg-red-100 text-red-800',
                                                'spam' => 'bg-red-100 text-red-800',
                                                'blocked' => 'bg-red-100 text-red-800',
                                                'queued' => 'bg-yellow-100 text-yellow-800',
                                                'deferred' => 'bg-yellow-100 text-yellow-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                        @if ($message['StatePermanent'] ?? false)
                                            <div class="text-xs text-gray-500 mt-1">Permanente</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex flex-col">
                                            <span>{{ number_format($message['MessageSize'] ?? 0) }} bytes</span>
                                            @if (($message['AttachmentCount'] ?? 0) > 0)
                                                <span class="text-xs text-gray-500">{{ $message['AttachmentCount'] }}
                                                    allegati</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex flex-col">
                                            <span
                                                class="font-medium">{{ $message['ArrivedAt'] ? date('d/m/Y H:i:s', strtotime($message['ArrivedAt'])) : 'N/A' }}</span>
                                            @if ($message['ArrivedAt'])
                                                <span
                                                    class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($message['ArrivedAt'])->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6 text-center text-gray-500">
                    <p class="text-lg">Nessun messaggio trovato per il {{ date('d/m/Y', strtotime($selectedDate)) }}
                    </p>
                    <p class="text-sm mt-2">Prova a selezionare una data diversa</p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-gray-500 text-sm">
            <p>Dashboard Mailjet - Messaggi del {{ date('d/m/Y', strtotime($selectedDate)) }}</p>
        </div>
    </div>
</body>

</html>
