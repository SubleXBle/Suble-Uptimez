<?php
/**
 * Suble Uptimez - Deinstallationsskript
 * Löscht Plugin-spezifische Daten bei der Deinstallation
 */

// Verhindert direktes Laden der Datei
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Sicherstellen, dass nur beim Deinstallieren über das WordPress-Admin-Menü ausgeführt wird
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Inkludiere die Datei für die API-Schlüssel-Verwaltung
require_once plugin_dir_path( __FILE__ ) . 'api-key-handling.php';

// Lösche die gespeicherten Optionen
delete_option( 'suble_uptimez_api_key' );

// Optional: Weitere Bereinigungen, falls nötig, z. B. benutzerdefinierte Tabellen (nicht erforderlich hier)
