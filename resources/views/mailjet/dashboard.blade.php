<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mailjet - Statistiche</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard Mailjet</h1>
                    <p class="text-gray-600 mt-2">Visualizza le statistiche del tuo account Mailjet</p>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('mailjet.daily-messages') }}"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        📧 Messaggi di Oggi
                    </a>
                    <a href="{{ route('mailjet.daily-messages') }}?date={{ date('Y-m-d', strtotime('yesterday')) }}"
                        class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        📅 Messaggi di Ieri
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtri per Data -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtri per Data</h3>
            <form method="GET" action="{{ route('mailjet.dashboard') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Selezione Periodo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Periodo</label>
                        <select name="period" id="period"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="lifetime"
                                {{ ($filters['period'] ?? 'lifetime') == 'lifetime' ? 'selected' : '' }}>Sempre</option>
                            <option value="1month" {{ ($filters['period'] ?? '') == '1month' ? 'selected' : '' }}>Ultimo
                                mese</option>
                            <option value="3months" {{ ($filters['period'] ?? '') == '3months' ? 'selected' : '' }}>
                                Ultimi 3 mesi</option>
                            <option value="6months" {{ ($filters['period'] ?? '') == '6months' ? 'selected' : '' }}>
                                Ultimi 6 mesi</option>
                            <option value="1year" {{ ($filters['period'] ?? '') == '1year' ? 'selected' : '' }}>Ultimo
                                anno</option>
                            <option value="custom" {{ ($filters['period'] ?? '') == 'custom' ? 'selected' : '' }}>
                                Personalizzato</option>
                        </select>
                    </div>

                    <!-- Data Inizio -->
                    <div id="date-inputs" class="grid grid-cols-1 md:grid-cols-2 gap-4 col-span-2"
                        style="display: {{ ($filters['period'] ?? 'lifetime') == 'custom' ? 'grid' : 'none' }};">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data Inizio</label>
                            <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data Fine</label>
                            <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Pulsanti -->
                    <div class="flex items-end space-x-2">
                        <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Applica Filtri
                        </button>
                        <a href="{{ route('mailjet.dashboard') }}"
                            class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Reset
                        </a>
                    </div>
                </div>

                <!-- Indicatore periodo corrente -->
                <div class="text-sm text-gray-600">
                    <strong>Periodo corrente:</strong>
                    @if (($filters['period'] ?? 'lifetime') == 'lifetime')
                        Tutte le statistiche disponibili
                    @elseif(($filters['period'] ?? '') == '1month')
                        Ultimo mese
                    @elseif(($filters['period'] ?? '') == '3months')
                        Ultimi 3 mesi
                    @elseif(($filters['period'] ?? '') == '6months')
                        Ultimi 6 mesi
                    @elseif(($filters['period'] ?? '') == '1year')
                        Ultimo anno
                    @elseif(($filters['period'] ?? '') == 'custom' && $filters['from_date'] && $filters['to_date'])
                        Dal {{ date('d/m/Y', strtotime($filters['from_date'])) }} al
                        {{ date('d/m/Y', strtotime($filters['to_date'])) }}
                    @else
                        Periodo personalizzato
                    @endif
                </div>
            </form>
        </div>

        <script>
            document.getElementById('period').addEventListener('change', function() {
                const dateInputs = document.getElementById('date-inputs');
                if (this.value === 'custom') {
                    dateInputs.style.display = 'grid';
                } else {
                    dateInputs.style.display = 'none';
                }
            });
        </script>

        @if (isset($error))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <strong>Errore:</strong> {{ $error }}
            </div>
        @endif

        @if ($stats)
            <!-- Statistiche Generali -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Email Inviate</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_sent']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Email Aperte</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['opened']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Click</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['clicked']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Bounced</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['bounced']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiche Aggiuntive -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Dettagli Account</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nome Account:</span>
                            <span class="font-medium">{{ $stats['name'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Stato:</span>
                            <span
                                class="px-2 py-1 rounded-full text-xs font-medium {{ $stats['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $stats['is_active'] ? 'Attivo' : 'Inattivo' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Data Creazione:</span>
                            <span
                                class="font-medium">{{ $stats['created_at'] ? date('d/m/Y H:i', strtotime($stats['created_at'])) : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Totale Messaggi:</span>
                            <span
                                class="font-medium text-blue-600">{{ number_format($stats['total_messages'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistiche Dettagliate</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Email Bloccate:</span>
                            <span class="font-medium text-red-600">{{ number_format($stats['blocked']) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Spam:</span>
                            <span class="font-medium text-red-600">{{ number_format($stats['spam']) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">In Coda:</span>
                            <span
                                class="font-medium text-yellow-600">{{ number_format($stats['message_queued'] ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Rimandate:</span>
                            <span
                                class="font-medium text-yellow-600">{{ number_format($stats['message_deferred'] ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Disiscrizioni:</span>
                            <span
                                class="font-medium text-orange-600">{{ number_format($stats['message_unsubscribed'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Metriche di Performance</h3>
                    <div class="space-y-4">
                        @php
                            $openRate = $stats['total_sent'] > 0 ? ($stats['opened'] / $stats['total_sent']) * 100 : 0;
                            $clickRate =
                                $stats['total_sent'] > 0 ? ($stats['clicked'] / $stats['total_sent']) * 100 : 0;
                            $bounceRate =
                                $stats['total_sent'] > 0 ? ($stats['bounced'] / $stats['total_sent']) * 100 : 0;
                        @endphp

                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Tasso di Apertura</span>
                                <span
                                    class="text-sm font-medium text-gray-700">{{ number_format($openRate, 2) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ min($openRate, 100) }}%">
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Tasso di Click</span>
                                <span
                                    class="text-sm font-medium text-gray-700">{{ number_format($clickRate, 2) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-purple-600 h-2 rounded-full"
                                    style="width: {{ min($clickRate, 100) }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Tasso di Bounce</span>
                                <span
                                    class="text-sm font-medium text-gray-700">{{ number_format($bounceRate, 2) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-red-600 h-2 rounded-full" style="width: {{ min($bounceRate, 100) }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiche Eventi vs Messaggi -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Confronto Eventi vs Messaggi</h3>
                    <p class="text-sm text-gray-600">Confronto tra eventi unici e messaggi totali</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">
                                {{ number_format($stats['event_opened'] ?? 0) }}</div>
                            <div class="text-sm text-gray-500">Eventi Apertura</div>
                            <div class="text-xs text-gray-400">vs {{ number_format($stats['opened']) }} messaggi
                                aperti</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">
                                {{ number_format($stats['event_clicked'] ?? 0) }}</div>
                            <div class="text-sm text-gray-500">Eventi Click</div>
                            <div class="text-xs text-gray-400">vs {{ number_format($stats['clicked']) }} messaggi
                                cliccati</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600">
                                {{ number_format($stats['event_spam'] ?? 0) }}</div>
                            <div class="text-sm text-gray-500">Eventi Spam</div>
                            <div class="text-xs text-gray-400">vs {{ number_format($stats['spam']) }} messaggi spam
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($campaigns && count($campaigns) > 0)
            <!-- Campagne Recenti -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Campagne Recenti</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Subject</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Stato</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data Creazione</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($campaigns as $campaign)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $campaign['ID'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $campaign['Subject'] ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $campaign['Status'] === 'sent' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $campaign['Status'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ date('d/m/Y H:i', strtotime($campaign['CreatedAt'])) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($sendStats && count($sendStats) > 0)
            <!-- Statistiche di Invio per Periodo -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Statistiche di Invio per Periodo</h3>
                    <p class="text-sm text-gray-600">Dettaglio delle statistiche suddivise per
                        {{ ($filters['resolution'] ?? 'Day') == 'Day' ? 'giorni' : (($filters['resolution'] ?? 'Day') == 'Month' ? 'mesi' : 'periodo') }}
                    </p>
                </div>
                <div class="p-6">
                    @if (count($sendStats) > 1)
                        <!-- Visualizzazione tabella per più periodi -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Data</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Inviate</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aperte</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cliccate</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Bloccate</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Bounce</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Azioni</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($sendStats as $stat)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $stat['Timeslice'] ? date('d/m/Y', strtotime($stat['Timeslice'])) : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($stat['MessageSentCount'] ?? 0) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($stat['MessageOpenedCount'] ?? 0) }}
                                                <span
                                                    class="text-xs text-gray-500">({{ number_format($stat['EventOpenedCount'] ?? 0) }}
                                                    eventi)</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($stat['MessageClickedCount'] ?? 0) }}
                                                <span
                                                    class="text-xs text-gray-500">({{ number_format($stat['EventClickedCount'] ?? 0) }}
                                                    eventi)</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                                {{ number_format($stat['MessageBlockedCount'] ?? 0) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                                {{ number_format(($stat['MessageHardBouncedCount'] ?? 0) + ($stat['MessageSoftBouncedCount'] ?? 0)) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if ($stat['Timeslice'])
                                                    <a href="{{ route('mailjet.daily-messages', ['date' => date('Y-m-d', strtotime($stat['Timeslice']))]) }}"
                                                        class="text-blue-600 hover:text-blue-900 font-medium">
                                                        Visualizza messaggi
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- Visualizzazione card per singolo periodo -->
                        @php $stat = $sendStats[0]; @endphp
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">
                                    {{ number_format($stat['MessageSentCount'] ?? 0) }}</div>
                                <div class="text-sm text-gray-500">Email Inviate</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">
                                    {{ number_format($stat['MessageOpenedCount'] ?? 0) }}</div>
                                <div class="text-sm text-gray-500">Messaggi Aperti</div>
                                <div class="text-xs text-gray-400">{{ number_format($stat['EventOpenedCount'] ?? 0) }}
                                    eventi</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">
                                    {{ number_format($stat['MessageClickedCount'] ?? 0) }}</div>
                                <div class="text-sm text-gray-500">Messaggi Cliccati</div>
                                <div class="text-xs text-gray-400">
                                    {{ number_format($stat['EventClickedCount'] ?? 0) }} eventi</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600">
                                    {{ number_format($stat['MessageBlockedCount'] ?? 0) }}</div>
                                <div class="text-sm text-gray-500">Bloccate</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600">
                                    {{ number_format(($stat['MessageHardBouncedCount'] ?? 0) + ($stat['MessageSoftBouncedCount'] ?? 0)) }}
                                </div>
                                <div class="text-sm text-gray-500">Bounce Totali</div>
                                <div class="text-xs text-gray-400">
                                    {{ number_format($stat['MessageHardBouncedCount'] ?? 0) }} hard,
                                    {{ number_format($stat['MessageSoftBouncedCount'] ?? 0) }} soft</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600">
                                    {{ number_format($stat['MessageSpamCount'] ?? 0) }}</div>
                                <div class="text-sm text-gray-500">Spam</div>
                                <div class="text-xs text-gray-400">{{ number_format($stat['EventSpamCount'] ?? 0) }}
                                    eventi</div>
                            </div>
                        </div>

                        <!-- Statistiche aggiuntive per singolo periodo -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-md font-semibold text-gray-900 mb-4">Statistiche Aggiuntive</h4>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="text-center">
                                    <div class="text-lg font-bold text-yellow-600">
                                        {{ number_format($stat['MessageQueuedCount'] ?? 0) }}</div>
                                    <div class="text-sm text-gray-500">In Coda</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold text-yellow-600">
                                        {{ number_format($stat['MessageDeferredCount'] ?? 0) }}</div>
                                    <div class="text-sm text-gray-500">Rimandate</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold text-orange-600">
                                        {{ number_format($stat['MessageUnsubscribedCount'] ?? 0) }}</div>
                                    <div class="text-sm text-gray-500">Disiscrizioni</div>
                                    <div class="text-xs text-gray-400">
                                        {{ number_format($stat['EventUnsubscribedCount'] ?? 0) }} eventi</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold text-gray-600">
                                        {{ number_format($stat['Total'] ?? 0) }}</div>
                                    <div class="text-sm text-gray-500">Totale</div>
                                </div>
                            </div>
                        </div>

                        @if ($stat['Timeslice'])
                            <div class="mt-4 text-center text-sm text-gray-600">
                                <strong>Periodo:</strong> {{ date('d/m/Y', strtotime($stat['Timeslice'])) }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="mt-8 text-center text-gray-500 text-sm">
            <p>Dashboard Mailjet - Ultimo aggiornamento: {{ date('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>

</html>
