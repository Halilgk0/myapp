<?php

namespace App\Support;

/**
 * Müşteri panelinin metinlerini biletin milliyetine göre çevirir.
 * Desteklenmeyen milliyetler İngilizce'ye düşer.
 */
class CustomerTranslator
{
    /**
     * Milliyet kodu → dil kodu eşlemesi.
     * (Ticket.customer_nationality değerleri — User::getNationalityOptions ile aynı.)
     */
    private const NATIONALITY_TO_LANG = [
        'TR' => 'tr',
        'DE' => 'de', 'AT' => 'de', 'CH' => 'de',
        'RU' => 'ru', 'BY' => 'ru', 'UA' => 'ru',
        'EN' => 'en',
        'FR' => 'fr', 'BE' => 'fr', 'LU' => 'fr', 'MC' => 'fr',
        'IT' => 'it', 'SM' => 'it', 'VA' => 'it',
        'ES' => 'es', 'AD' => 'es',
        'NL' => 'nl',
    ];

    public static function langFor(?string $nationality): string
    {
        $code = strtoupper((string) $nationality);
        return self::NATIONALITY_TO_LANG[$code] ?? 'en';
    }

    /**
     * Tüm UI metinlerini içeren array döner. Eksik anahtar İngilizce'den tamamlanır.
     */
    public static function strings(?string $nationality): array
    {
        $lang = self::langFor($nationality);
        $all = self::translations();
        $base = $all['en'];
        $lang = $all[$lang] ?? $base;
        return array_merge($base, $lang);
    }

    /**
     * Saniyeyi yerelleştirilmiş ETA metnine çevirir. Örn: "5 dk", "1 sa 20 dk", "30 sn".
     */
    public static function formatEta(int $seconds, ?string $nationality): string
    {
        $t = self::strings($nationality);
        if ($seconds < 60) {
            return $seconds . ' ' . $t['unit_sec'];
        }
        $minutes = (int) round($seconds / 60);
        if ($minutes < 60) {
            return $minutes . ' ' . $t['unit_min'];
        }
        $hours = (int) floor($minutes / 60);
        $rem = $minutes % 60;
        $out = $hours . ' ' . $t['unit_hour'];
        if ($rem > 0) {
            $out .= ' ' . $rem . ' ' . $t['unit_min'];
        }
        return $out;
    }

    private static function translations(): array
    {
        return [
            'en' => [
                'html_lang'          => 'en',
                'title_my_ticket'    => 'My Ticket',
                'btn_logout'         => 'Logout',
                'eta_label'          => 'Estimated Arrival',
                'eta_connecting'     => 'Connecting...',
                'eta_loading'        => 'Loading driver location',
                'eta_not_started'    => 'Has not departed yet',
                'eta_driver_waiting' => 'is waiting',
                'eta_no_driver'      => 'Will appear once a driver is assigned',
                'eta_km_away'        => 'km away',
                'eta_live'           => 'Live tracking',
                'eta_last_known'     => 'Last known location',
                'eta_calculating'    => 'Calculating...',
                'eta_location'       => 'Location',
                'section_driver'     => 'Driver Information',
                'section_ticket'     => 'Ticket Information',
                'section_customer'   => 'Customer Information',
                'row_tour'           => 'Tour',
                'row_tour_date'      => 'Tour Date',
                'row_pickup_time'    => 'Pickup Time',
                'row_pickup_loc'     => 'Pickup Location',
                'row_room'           => 'Room Number',
                'row_passengers'     => 'Passengers',
                'row_rest'           => 'Outstanding Payment',
                'row_name'           => 'Full Name',
                'row_phone'          => 'Phone',
                'row_email'          => 'Email',
                'no_driver'          => 'Driver not yet assigned. Will be assigned soon.',
                'call_driver_title'  => 'Call driver',
                'popup_pickup'       => 'Your Pickup Point',
                'popup_driver'       => 'Driver',
                'unit_sec'           => 'sec',
                'unit_min'           => 'min',
                'unit_hour'          => 'h',
                'map_unavailable'    => 'Map is currently unavailable',
                'passenger_word'     => 'people',
                'no_passengers'      => 'No passengers',
            ],

            'tr' => [
                'html_lang'          => 'tr',
                'title_my_ticket'    => 'Biletim',
                'btn_logout'         => 'Çıkış',
                'eta_label'          => 'Tahmini Varış',
                'eta_connecting'     => 'Bağlanıyor...',
                'eta_loading'        => 'Şoför konumu alınıyor',
                'eta_not_started'    => 'Henüz yola çıkmadı',
                'eta_driver_waiting' => 'bekliyor',
                'eta_no_driver'      => 'Şoför atandığında burada görünecek',
                'eta_km_away'        => 'km uzaklıkta',
                'eta_live'           => 'Canlı takip',
                'eta_last_known'     => 'Son bilinen konum',
                'eta_calculating'    => 'Hesaplanıyor...',
                'eta_location'       => 'Konum',
                'section_driver'     => 'Şoför Bilgileri',
                'section_ticket'     => 'Bilet Bilgileri',
                'section_customer'   => 'Müşteri Bilgileri',
                'row_tour'           => 'Tur',
                'row_tour_date'      => 'Tur Tarihi',
                'row_pickup_time'    => 'Alış Saati',
                'row_pickup_loc'     => 'Alış Yeri',
                'row_room'           => 'Oda No',
                'row_passengers'     => 'Yolcu Sayısı',
                'row_rest'           => 'Kalan Ödeme (Rest)',
                'row_name'           => 'Ad Soyad',
                'row_phone'          => 'Telefon',
                'row_email'          => 'E-posta',
                'no_driver'          => 'Şoför henüz atanmadı. Yakında atanacaktır.',
                'call_driver_title'  => 'Şoförü ara',
                'popup_pickup'       => 'Alış Yeriniz',
                'popup_driver'       => 'Şoför',
                'unit_sec'           => 'sn',
                'unit_min'           => 'dk',
                'unit_hour'          => 'sa',
                'map_unavailable'    => 'Harita şu anda kullanılamıyor',
                'passenger_word'     => 'Kişi',
                'no_passengers'      => 'Yolcu yok',
            ],

            'de' => [
                'html_lang'          => 'de',
                'title_my_ticket'    => 'Mein Ticket',
                'btn_logout'         => 'Abmelden',
                'eta_label'          => 'Voraussichtliche Ankunft',
                'eta_connecting'     => 'Verbindung...',
                'eta_loading'        => 'Fahrerstandort wird geladen',
                'eta_not_started'    => 'Noch nicht losgefahren',
                'eta_driver_waiting' => 'wartet',
                'eta_no_driver'      => 'Erscheint, sobald ein Fahrer zugewiesen ist',
                'eta_km_away'        => 'km entfernt',
                'eta_live'           => 'Live-Verfolgung',
                'eta_last_known'     => 'Letzter bekannter Standort',
                'eta_calculating'    => 'Berechnung läuft...',
                'eta_location'       => 'Standort',
                'section_driver'     => 'Fahrerinformationen',
                'section_ticket'     => 'Ticketinformationen',
                'section_customer'   => 'Kundeninformationen',
                'row_tour'           => 'Tour',
                'row_tour_date'      => 'Tourdatum',
                'row_pickup_time'    => 'Abholzeit',
                'row_pickup_loc'     => 'Abholort',
                'row_room'           => 'Zimmernummer',
                'row_passengers'     => 'Passagiere',
                'row_rest'           => 'Ausstehende Zahlung',
                'row_name'           => 'Name',
                'row_phone'          => 'Telefon',
                'row_email'          => 'E-Mail',
                'no_driver'          => 'Fahrer noch nicht zugewiesen. Wird bald zugewiesen.',
                'call_driver_title'  => 'Fahrer anrufen',
                'popup_pickup'       => 'Ihr Abholort',
                'popup_driver'       => 'Fahrer',
                'unit_sec'           => 'Sek',
                'unit_min'           => 'Min',
                'unit_hour'          => 'Std',
                'map_unavailable'    => 'Karte derzeit nicht verfügbar',
                'passenger_word'     => 'Personen',
                'no_passengers'      => 'Keine Passagiere',
            ],

            'ru' => [
                'html_lang'          => 'ru',
                'title_my_ticket'    => 'Мой билет',
                'btn_logout'         => 'Выйти',
                'eta_label'          => 'Ожидаемое прибытие',
                'eta_connecting'     => 'Подключение...',
                'eta_loading'        => 'Загрузка местоположения водителя',
                'eta_not_started'    => 'Ещё не выехал',
                'eta_driver_waiting' => 'ожидает',
                'eta_no_driver'      => 'Появится после назначения водителя',
                'eta_km_away'        => 'км',
                'eta_live'           => 'Отслеживание в реальном времени',
                'eta_last_known'     => 'Последнее известное местоположение',
                'eta_calculating'    => 'Вычисление...',
                'eta_location'       => 'Местоположение',
                'section_driver'     => 'Информация о водителе',
                'section_ticket'     => 'Информация о билете',
                'section_customer'   => 'Информация о клиенте',
                'row_tour'           => 'Тур',
                'row_tour_date'      => 'Дата тура',
                'row_pickup_time'    => 'Время посадки',
                'row_pickup_loc'     => 'Место посадки',
                'row_room'           => 'Номер комнаты',
                'row_passengers'     => 'Пассажиры',
                'row_rest'           => 'Остаток к оплате',
                'row_name'           => 'Имя и фамилия',
                'row_phone'          => 'Телефон',
                'row_email'          => 'Эл. почта',
                'no_driver'          => 'Водитель ещё не назначен. Скоро будет назначен.',
                'call_driver_title'  => 'Позвонить водителю',
                'popup_pickup'       => 'Ваше место посадки',
                'popup_driver'       => 'Водитель',
                'unit_sec'           => 'с',
                'unit_min'           => 'мин',
                'unit_hour'          => 'ч',
                'map_unavailable'    => 'Карта временно недоступна',
                'passenger_word'     => 'чел.',
                'no_passengers'      => 'Нет пассажиров',
            ],

            'fr' => [
                'html_lang'          => 'fr',
                'title_my_ticket'    => 'Mon billet',
                'btn_logout'         => 'Déconnexion',
                'eta_label'          => 'Arrivée estimée',
                'eta_connecting'     => 'Connexion...',
                'eta_loading'        => 'Chargement de la position du chauffeur',
                'eta_not_started'    => 'Pas encore en route',
                'eta_driver_waiting' => 'attend',
                'eta_no_driver'      => 'Apparaîtra dès qu\'un chauffeur sera attribué',
                'eta_km_away'        => 'km',
                'eta_live'           => 'Suivi en direct',
                'eta_last_known'     => 'Dernière position connue',
                'eta_calculating'    => 'Calcul en cours...',
                'eta_location'       => 'Position',
                'section_driver'     => 'Informations du chauffeur',
                'section_ticket'     => 'Informations du billet',
                'section_customer'   => 'Informations du client',
                'row_tour'           => 'Tour',
                'row_tour_date'      => 'Date du tour',
                'row_pickup_time'    => 'Heure de prise en charge',
                'row_pickup_loc'     => 'Lieu de prise en charge',
                'row_room'           => 'Numéro de chambre',
                'row_passengers'     => 'Passagers',
                'row_rest'           => 'Solde à payer',
                'row_name'           => 'Nom complet',
                'row_phone'          => 'Téléphone',
                'row_email'          => 'E-mail',
                'no_driver'          => 'Chauffeur pas encore attribué. Bientôt assigné.',
                'call_driver_title'  => 'Appeler le chauffeur',
                'popup_pickup'       => 'Votre point de prise en charge',
                'popup_driver'       => 'Chauffeur',
                'unit_sec'           => 's',
                'unit_min'           => 'min',
                'unit_hour'          => 'h',
                'map_unavailable'    => 'Carte actuellement indisponible',
                'passenger_word'     => 'personnes',
                'no_passengers'      => 'Aucun passager',
            ],

            'it' => [
                'html_lang'          => 'it',
                'title_my_ticket'    => 'Il mio biglietto',
                'btn_logout'         => 'Esci',
                'eta_label'          => 'Arrivo stimato',
                'eta_connecting'     => 'Connessione...',
                'eta_loading'        => 'Caricamento posizione autista',
                'eta_not_started'    => 'Non ancora partito',
                'eta_driver_waiting' => 'è in attesa',
                'eta_no_driver'      => 'Apparirà quando un autista sarà assegnato',
                'eta_km_away'        => 'km',
                'eta_live'           => 'Tracciamento dal vivo',
                'eta_last_known'     => 'Ultima posizione nota',
                'eta_calculating'    => 'Calcolo in corso...',
                'eta_location'       => 'Posizione',
                'section_driver'     => 'Informazioni autista',
                'section_ticket'     => 'Informazioni biglietto',
                'section_customer'   => 'Informazioni cliente',
                'row_tour'           => 'Tour',
                'row_tour_date'      => 'Data del tour',
                'row_pickup_time'    => 'Ora di ritiro',
                'row_pickup_loc'     => 'Luogo di ritiro',
                'row_room'           => 'Numero camera',
                'row_passengers'     => 'Passeggeri',
                'row_rest'           => 'Saldo da pagare',
                'row_name'           => 'Nome completo',
                'row_phone'          => 'Telefono',
                'row_email'          => 'Email',
                'no_driver'          => 'Autista non ancora assegnato. Sarà assegnato a breve.',
                'call_driver_title'  => 'Chiama l\'autista',
                'popup_pickup'       => 'Il tuo punto di ritiro',
                'popup_driver'       => 'Autista',
                'unit_sec'           => 's',
                'unit_min'           => 'min',
                'unit_hour'          => 'h',
                'map_unavailable'    => 'Mappa attualmente non disponibile',
                'passenger_word'     => 'persone',
                'no_passengers'      => 'Nessun passeggero',
            ],

            'es' => [
                'html_lang'          => 'es',
                'title_my_ticket'    => 'Mi billete',
                'btn_logout'         => 'Salir',
                'eta_label'          => 'Llegada estimada',
                'eta_connecting'     => 'Conectando...',
                'eta_loading'        => 'Cargando ubicación del conductor',
                'eta_not_started'    => 'Aún no ha salido',
                'eta_driver_waiting' => 'está esperando',
                'eta_no_driver'      => 'Aparecerá cuando se asigne un conductor',
                'eta_km_away'        => 'km',
                'eta_live'           => 'Seguimiento en vivo',
                'eta_last_known'     => 'Última ubicación conocida',
                'eta_calculating'    => 'Calculando...',
                'eta_location'       => 'Ubicación',
                'section_driver'     => 'Información del conductor',
                'section_ticket'     => 'Información del billete',
                'section_customer'   => 'Información del cliente',
                'row_tour'           => 'Tour',
                'row_tour_date'      => 'Fecha del tour',
                'row_pickup_time'    => 'Hora de recogida',
                'row_pickup_loc'     => 'Lugar de recogida',
                'row_room'           => 'Número de habitación',
                'row_passengers'     => 'Pasajeros',
                'row_rest'           => 'Pago pendiente',
                'row_name'           => 'Nombre completo',
                'row_phone'          => 'Teléfono',
                'row_email'          => 'Correo electrónico',
                'no_driver'          => 'Conductor aún no asignado. Será asignado pronto.',
                'call_driver_title'  => 'Llamar al conductor',
                'popup_pickup'       => 'Su punto de recogida',
                'popup_driver'       => 'Conductor',
                'unit_sec'           => 's',
                'unit_min'           => 'min',
                'unit_hour'          => 'h',
                'map_unavailable'    => 'Mapa actualmente no disponible',
                'passenger_word'     => 'personas',
                'no_passengers'      => 'Sin pasajeros',
            ],

            'nl' => [
                'html_lang'          => 'nl',
                'title_my_ticket'    => 'Mijn ticket',
                'btn_logout'         => 'Uitloggen',
                'eta_label'          => 'Verwachte aankomst',
                'eta_connecting'     => 'Verbinden...',
                'eta_loading'        => 'Locatie chauffeur laden',
                'eta_not_started'    => 'Nog niet vertrokken',
                'eta_driver_waiting' => 'wacht',
                'eta_no_driver'      => 'Verschijnt zodra een chauffeur is toegewezen',
                'eta_km_away'        => 'km verderop',
                'eta_live'           => 'Live volgen',
                'eta_last_known'     => 'Laatst bekende locatie',
                'eta_calculating'    => 'Berekenen...',
                'eta_location'       => 'Locatie',
                'section_driver'     => 'Chauffeurinformatie',
                'section_ticket'     => 'Ticketinformatie',
                'section_customer'   => 'Klantinformatie',
                'row_tour'           => 'Tour',
                'row_tour_date'      => 'Tourdatum',
                'row_pickup_time'    => 'Ophaaltijd',
                'row_pickup_loc'     => 'Ophaallocatie',
                'row_room'           => 'Kamernummer',
                'row_passengers'     => 'Passagiers',
                'row_rest'           => 'Openstaand bedrag',
                'row_name'           => 'Volledige naam',
                'row_phone'          => 'Telefoon',
                'row_email'          => 'E-mail',
                'no_driver'          => 'Chauffeur nog niet toegewezen. Wordt binnenkort toegewezen.',
                'call_driver_title'  => 'Bel chauffeur',
                'popup_pickup'       => 'Uw ophaallocatie',
                'popup_driver'       => 'Chauffeur',
                'unit_sec'           => 's',
                'unit_min'           => 'min',
                'unit_hour'          => 'u',
                'map_unavailable'    => 'Kaart momenteel niet beschikbaar',
                'passenger_word'     => 'personen',
                'no_passengers'      => 'Geen passagiers',
            ],
        ];
    }
}
