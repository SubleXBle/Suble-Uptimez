<?php
/**
 * API Key Handling für Suble Uptimez
 * Diese Datei kümmert sich um das Speichern und Abrufen des Uptime-Robot API-Schlüssels.
 */

// Verhindert direktes Laden der Datei
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Funktion zum Speichern des API-Schlüssels.
 */
function suble_uptimez_save_api_key( $api_key ) {
    // API-Schlüssel in der Datenbank speichern
    update_option( 'suble_uptimez_api_key', sanitize_text_field( $api_key ) );
}

/**
 * Funktion zum Abrufen des gespeicherten API-Schlüssels.
 */
function suble_uptimez_get_api_key() {
    // API-Schlüssel aus der Datenbank holen
    return get_option( 'suble_uptimez_api_key', '' );
}
