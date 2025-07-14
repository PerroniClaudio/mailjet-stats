<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MailjetController extends Controller {
    private $apiKey;
    private $apiSecret;
    private $baseUrl = 'https://api.mailjet.com/v3/REST';

    public function __construct() {
        $this->apiKey = env('MAILJET_USERNAME');
        $this->apiSecret = env('MAILJET_PASSWORD');
    }
    public function dashboard(Request $request) {
        try {
            // Ottieni i parametri di filtro
            $filters = $this->getDateFilters($request);

            // Recupera le statistiche generali
            $stats = $this->getGeneralStats($filters);

            // Recupera le statistiche delle campagne
            $campaigns = $this->getCampaignStats($filters);

            // Recupera le statistiche dei messaggi
            $messages = $this->getMessageStats($filters);

            // Recupera le statistiche di invio
            $sendStats = $this->getSendStats($filters);

            return view('mailjet.dashboard', compact('stats', 'campaigns', 'messages', 'sendStats', 'filters'));
        } catch (\Exception $e) {
            return view('mailjet.dashboard', [
                'error' => 'Errore nel recupero delle statistiche: ' . $e->getMessage(),
                'stats' => null,
                'campaigns' => null,
                'messages' => null,
                'sendStats' => null,
                'filters' => $this->getDateFilters($request)
            ]);
        }
    }

    private function getDateFilters(Request $request) {
        $period = $request->get('period', 'lifetime');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $filters = [
            'period' => $period,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'from_timestamp' => null,
            'to_timestamp' => null,
            'resolution' => 'Lifetime'
        ];

        if ($period === 'custom' && $fromDate && $toDate) {
            $filters['from_timestamp'] = strtotime($fromDate);
            $filters['to_timestamp'] = strtotime($toDate . ' 23:59:59');
            $filters['resolution'] = 'Day';
        } elseif ($period !== 'lifetime') {
            $months = match ($period) {
                '1month' => 1,
                '3months' => 3,
                '6months' => 6,
                '1year' => 12,
                default => null
            };

            if ($months) {
                $filters['from_timestamp'] = strtotime("-{$months} months");
                $filters['to_timestamp'] = time();
                $filters['resolution'] = $months > 3 ? 'Month' : 'Day';
            }
        }

        return $filters;
    }

    private function makeRequest($endpoint, $params = []) {
        $url = $this->baseUrl . '/' . $endpoint;

        $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
            ->get($url, $params);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('API request failed: ' . $response->body());
    }

    private function getGeneralStats($filters = []) {
        // Parametri per la richiesta
        $params = [
            'CounterSource' => 'APIKey',
            'CounterTiming' => 'Message',
            'CounterResolution' => $filters['resolution'] ?? 'Lifetime'
        ];

        // Aggiungi filtri di data se presenti
        if (isset($filters['from_timestamp']) && $filters['from_timestamp']) {
            $params['FromTS'] = $filters['from_timestamp'];
        }
        if (isset($filters['to_timestamp']) && $filters['to_timestamp']) {
            $params['ToTS'] = $filters['to_timestamp'];
        }

        // Recupera le statistiche dai contatori
        $statCounters = $this->makeRequest('statcounters', $params);

        // Recupera informazioni sulla API key
        $apiKeyInfo = $this->makeRequest('apikey');

        $stats = [
            'total_sent' => 0,
            'bounced' => 0,
            'spam' => 0,
            'blocked' => 0,
            'clicked' => 0,
            'opened' => 0,
            'is_active' => true,
            'name' => 'Mailjet API',
            'created_at' => null
        ];

        // Elabora i dati dei contatori utilizzando i campi corretti
        if (isset($statCounters['Data']) && is_array($statCounters['Data'])) {
            foreach ($statCounters['Data'] as $counter) {
                $stats['total_sent'] += $counter['MessageSentCount'] ?? 0;
                $stats['bounced'] += ($counter['MessageHardBouncedCount'] ?? 0) + ($counter['MessageSoftBouncedCount'] ?? 0);
                $stats['spam'] += $counter['MessageSpamCount'] ?? 0;
                $stats['blocked'] += $counter['MessageBlockedCount'] ?? 0;
                $stats['clicked'] += $counter['MessageClickedCount'] ?? 0;
                $stats['opened'] += $counter['MessageOpenedCount'] ?? 0;

                // Statistiche aggiuntive per eventi
                $stats['event_clicked'] = ($stats['event_clicked'] ?? 0) + ($counter['EventClickedCount'] ?? 0);
                $stats['event_opened'] = ($stats['event_opened'] ?? 0) + ($counter['EventOpenedCount'] ?? 0);
                $stats['event_spam'] = ($stats['event_spam'] ?? 0) + ($counter['EventSpamCount'] ?? 0);
                $stats['event_unsubscribed'] = ($stats['event_unsubscribed'] ?? 0) + ($counter['EventUnsubscribedCount'] ?? 0);
                $stats['message_unsubscribed'] = ($stats['message_unsubscribed'] ?? 0) + ($counter['MessageUnsubscribedCount'] ?? 0);
                $stats['message_queued'] = ($stats['message_queued'] ?? 0) + ($counter['MessageQueuedCount'] ?? 0);
                $stats['message_deferred'] = ($stats['message_deferred'] ?? 0) + ($counter['MessageDeferredCount'] ?? 0);
                $stats['total_messages'] = ($stats['total_messages'] ?? 0) + ($counter['Total'] ?? 0);
            }
        }

        // Elabora le informazioni della API key
        if (isset($apiKeyInfo['Data']) && is_array($apiKeyInfo['Data']) && count($apiKeyInfo['Data']) > 0) {
            $keyData = $apiKeyInfo['Data'][0];
            $stats['name'] = $keyData['Name'] ?? 'Mailjet API';
            $stats['is_active'] = $keyData['IsActive'] ?? true;
            $stats['created_at'] = $keyData['CreatedAt'] ?? null;
        }

        return $stats;
    }

    private function getCampaignStats($filters = []) {
        try {
            $params = ['Limit' => 10];

            // Aggiungi filtri di data se presenti
            if (isset($filters['from_timestamp']) && $filters['from_timestamp']) {
                $params['FromTS'] = $filters['from_timestamp'];
            }
            if (isset($filters['to_timestamp']) && $filters['to_timestamp']) {
                $params['ToTS'] = $filters['to_timestamp'];
            }

            $campaigns = $this->makeRequest('campaign', $params);

            return $campaigns['Data'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getMessageStats($filters = []) {
        try {
            $params = [
                'Limit' => 50,
                'ShowSubject' => true  // Mostra l'oggetto del messaggio
            ];

            // Aggiungi filtri di data se presenti
            if (isset($filters['from_timestamp']) && $filters['from_timestamp']) {
                $params['FromTS'] = $filters['from_timestamp'];
            } else {
                $params['FromTS'] = strtotime('-30 days');
            }

            if (isset($filters['to_timestamp']) && $filters['to_timestamp']) {
                $params['ToTS'] = $filters['to_timestamp'];
            }

            $messages = $this->makeRequest('message', $params);

            return $messages['Data'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }
    private function getSendStats($filters = []) {
        try {
            $params = [
                'CounterSource' => 'APIKey',
                'CounterTiming' => 'Message',
                'CounterResolution' => $filters['resolution'] ?? 'Day'
            ];

            // Aggiungi filtri di data se presenti
            if (isset($filters['from_timestamp']) && $filters['from_timestamp']) {
                $params['FromTS'] = $filters['from_timestamp'];
            }
            if (isset($filters['to_timestamp']) && $filters['to_timestamp']) {
                $params['ToTS'] = $filters['to_timestamp'];
            }

            $sendStats = $this->makeRequest('statcounters', $params);

            $data = $sendStats['Data'] ?? [];

            // Ordina per data discendente (più recente prima)
            usort($data, function ($a, $b) {
                $dateA = $a['Timeslice'] ?? '';
                $dateB = $b['Timeslice'] ?? '';
                return strcmp($dateB, $dateA); // Ordine discendente
            });

            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function apiInfo() {
        try {
            $apiInfo = $this->makeRequest('apikey');
            return response()->json($apiInfo);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private static $contactCache = [];

    private function getContactInfo($contactId) {
        // Usa la cache per evitare chiamate duplicate
        if (isset(self::$contactCache[$contactId])) {
            return self::$contactCache[$contactId];
        }

        try {
            $contact = $this->makeRequest("contact/{$contactId}");
            $contactData = $contact['Data'][0] ?? null;

            // Salva in cache
            self::$contactCache[$contactId] = $contactData;

            return $contactData;
        } catch (\Exception $e) {
            // Salva anche i fallimenti in cache per evitare retry
            self::$contactCache[$contactId] = null;
            return null;
        }
    }

    private function enrichMessagesWithContactInfo($messages) {
        if (empty($messages)) {
            return $messages;
        }

        // Raccogli tutti i ContactID unici
        $contactIds = array_unique(array_filter(array_column($messages, 'ContactID')));

        // Recupera le informazioni di tutti i contatti in batch
        $contactsInfo = [];
        foreach (array_chunk($contactIds, 50) as $chunk) { // Processa in gruppi di 50
            foreach ($chunk as $contactId) {
                try {
                    $contact = $this->makeRequest("contact/{$contactId}");
                    if (isset($contact['Data'][0])) {
                        $contactsInfo[$contactId] = $contact['Data'][0];
                    }
                } catch (\Exception $e) {
                    // Contatto non trovato o errore API
                    continue;
                }
            }
        }

        // Arricchisci i messaggi con le informazioni dei contatti
        $enrichedMessages = [];
        foreach ($messages as $message) {
            $contactId = $message['ContactID'] ?? null;
            $contactInfo = $contactsInfo[$contactId] ?? null;

            // Aggiungi le informazioni del contatto al messaggio
            $message['ContactEmail'] = $contactInfo['Email'] ?? 'N/A';
            $message['ContactName'] = $contactInfo['Name'] ?? null;
            $message['ContactIsOptIn'] = $contactInfo['IsOptInPending'] ?? false;
            $message['ContactCreatedAt'] = $contactInfo['CreatedAt'] ?? null;

            $enrichedMessages[] = $message;
        }

        return $enrichedMessages;
    }

    private function getMessagesWithEvents($startOfDay, $endOfDay) {
        try {
            // Recupera gli eventi del giorno che contengono le email
            $events = $this->makeRequest('messagesentstatistics', [
                'FromTS' => $startOfDay,
                'ToTS' => $endOfDay,
                'Limit' => 1000,
                'ShowSubject' => true  // Mostra l'oggetto del messaggio
            ]);

            return $events['Data'] ?? [];
        } catch (\Exception $e) {
            // Fallback al metodo standard se non funziona
            return [];
        }
    }

    private function tryAlternativeEmailRetrieval($messages) {
        // Metodo di backup: prova a ottenere le email da eventi o statistiche
        try {
            // Proviamo con messagehistory che potrebbe avere più dettagli
            $startOfDay = strtotime(date('Y-m-d 00:00:00'));
            $endOfDay = strtotime(date('Y-m-d 23:59:59'));

            $history = $this->makeRequest('messagehistory', [
                'FromTS' => $startOfDay,
                'ToTS' => $endOfDay,
                'Limit' => 1000,
                'ShowSubject' => true  // Mostra l'oggetto del messaggio
            ]);

            return $history['Data'] ?? $messages;
        } catch (\Exception $e) {
            return $messages;
        }
    }

    private function addSenderInfo($messages) {
        foreach ($messages as &$message) {
            $senderId = $message['SenderID'] ?? null;

            if ($senderId) {
                try {
                    // Recupera le informazioni del sender
                    $sender = $this->makeRequest("sender/{$senderId}");
                    if (isset($sender['Data'][0])) {
                        $senderData = $sender['Data'][0];
                        $message['SenderEmail'] = $senderData['Email'] ?? 'N/A';
                        $message['SenderName'] = $senderData['Name'] ?? null;
                        $message['SenderDNS'] = $senderData['DNS'] ?? null;
                        $message['SenderStatus'] = $senderData['Status'] ?? null;
                    } else {
                        $message['SenderEmail'] = 'N/A';
                        $message['SenderName'] = null;
                    }
                } catch (\Exception $e) {
                    // Fallback: prova a ottenere info dal campo From se disponibile
                    $message['SenderEmail'] = $message['From'] ?? 'N/A';
                    $message['SenderName'] = $message['FromName'] ?? null;
                }
            } else {
                // Prova a usare i campi From diretti se disponibili
                $message['SenderEmail'] = $message['From'] ?? 'N/A';
                $message['SenderName'] = $message['FromName'] ?? null;
            }
        }

        return $messages;
    }

    public function dailyMessages(Request $request, $date = null) {
        try {
            // Se non è specificata una data, usa oggi
            $selectedDate = $date ?? $request->get('date', date('Y-m-d'));

            // Valida il formato della data
            if (!strtotime($selectedDate)) {
                throw new \Exception('Formato data non valido');
            }

            // Calcola i timestamp per inizio e fine giornata
            $startOfDay = strtotime($selectedDate . ' 00:00:00');
            $endOfDay = strtotime($selectedDate . ' 23:59:59');

            // Recupera tutti i messaggi del giorno
            $messages = $this->makeRequest('message', [
                'FromTS' => $startOfDay,
                'ToTS' => $endOfDay,
                'Limit' => 1000,  // Aumenta il limite per avere più messaggi
                'ShowSubject' => true  // Mostra l'oggetto del messaggio
            ]);

            // Arricchisci i messaggi con le informazioni email dei contatti e mittente
            $messagesData = $messages['Data'] ?? [];

            // Ordina i messaggi per data/ora decrescente (più recenti prima)
            usort($messagesData, function ($a, $b) {
                $dateA = $a['ArrivedAt'] ?? '';
                $dateB = $b['ArrivedAt'] ?? '';
                return strcmp($dateB, $dateA); // Ordine decrescente
            });

            try {
                $enrichedMessages = $this->enrichMessagesWithContactInfo($messagesData);
                $enrichedMessages = $this->addSenderInfo($enrichedMessages);
            } catch (\Exception $e) {
                // Se fallisce il recupero delle email, usa i dati base
                $enrichedMessages = $messagesData;
                // Aggiungi un campo per indicare che le email non sono disponibili
                foreach ($enrichedMessages as &$message) {
                    $message['ContactEmail'] = 'Email non disponibile';
                    $message['ContactName'] = null;
                    $message['SenderEmail'] = 'Mittente non disponibile';
                    $message['SenderName'] = null;
                }
            }

            // Recupera anche le statistiche del giorno
            $dailyStats = $this->makeRequest('statcounters', [
                'CounterSource' => 'APIKey',
                'CounterTiming' => 'Message',
                'CounterResolution' => 'Day',
                'FromTS' => $startOfDay,
                'ToTS' => $endOfDay
            ]);

            return view('mailjet.daily-messages', [
                'messages' => $enrichedMessages,
                'dailyStats' => $dailyStats['Data'] ?? [],
                'selectedDate' => $selectedDate,
                'totalMessages' => $messages['Total'] ?? 0
            ]);
        } catch (\Exception $e) {
            return view('mailjet.daily-messages', [
                'error' => 'Errore nel recupero dei messaggi: ' . $e->getMessage(),
                'messages' => [],
                'dailyStats' => [],
                'selectedDate' => $selectedDate ?? date('Y-m-d'),
                'totalMessages' => 0
            ]);
        }
    }
}
