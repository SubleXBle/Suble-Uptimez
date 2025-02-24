<?php
/**
 * Plugin Name: Suble Uptimez
 * Plugin URI: https://suble.org
 * Description: Zeigt die Uptime-Status von Monitoren mithilfe des Uptime-Robots API.
 * Version: 1.0
 * Author: SubleXBle
 * Author URI: https://suble.org
 */

// Verhindert direktes Laden der Datei
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// Include Tracking
include_once plugin_dir_path(__FILE__) . 'tracking.php';
// Include für die API-Schlüssel-Verwaltung
require_once plugin_dir_path( __FILE__ ) . 'api-key-handling.php';

// Plugin-Hauptfunktion
function suble_uptimez_menu() {
    // Füge den Admin-Menüpunkt hinzu
    add_menu_page(
        'Suble Uptimez',                // Seiten-Titel
        'Suble Uptimez',                // Menü-Titel
        'manage_options',               // Berechtigung
        'suble-uptimez',                // Slug
        'suble_uptimez_admin_page',     // Callback-Funktion
        'dashicons-chart-line',         // Icon
        6                               // Position
    );
}
add_action( 'admin_menu', 'suble_uptimez_menu' );

// Admin-Seite anzeigen
function suble_uptimez_admin_page() {
    ?>
    <div class="wrap">
        <h1>Suble Uptimez Einstellungen</h1>

        <form method="post" action="options.php">
            <?php
            settings_fields( 'suble_uptimez_options_group' );
            do_settings_sections( 'suble-uptimez' );
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Uptime-Robot API Key</th>
                    <td><input type="text" name="suble_uptimez_api_key" value="<?php echo esc_attr( suble_uptimez_get_api_key() ); ?>" /></td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>

        <h2>Monitor-Status</h2>
        <?php suble_uptimez_display_monitors(); ?>
    </div>
    <?php
}

// Initialisierung der Plugin-Einstellungen
function suble_uptimez_register_settings() {
    register_setting( 'suble_uptimez_options_group', 'suble_uptimez_api_key' );
}
add_action( 'admin_init', 'suble_uptimez_register_settings' );

// API-Schlüssel speichern
if ( isset( $_POST['suble_uptimez_api_key'] ) ) {
    suble_uptimez_save_api_key( $_POST['suble_uptimez_api_key'] );
}

// Monitore und deren Status anzeigen
function suble_uptimez_display_monitors() {
    $api_key = suble_uptimez_get_api_key();

    if ( empty( $api_key ) ) {
        echo '<p>Bitte einen API-Schlüssel eingeben.</p>';
        return;
    }

    // API-Abfrage für Uptime Robot Monitore
    $url = 'https://api.uptimerobot.com/v2/getMonitors';
    $response = wp_remote_post( $url, array(
        'body' => json_encode(array(
            'api_key' => $api_key,
            'format'  => 'json'
        )),
        'headers' => array(
            'Content-Type' => 'application/json'
        ),
    ));

    if ( is_wp_error( $response ) ) {
        echo '<p>Fehler beim Abrufen der Monitore.</p>';
        return;
    }

    $data = json_decode( wp_remote_retrieve_body( $response ) );

    if ( isset( $data->monitors ) && is_array( $data->monitors ) ) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Monitor Name</th><th>Status</th></tr></thead>';
        echo '<tbody>';
        foreach ( $data->monitors as $monitor ) {
            echo '<tr>';
            echo '<td>' . esc_html( $monitor->friendly_name ) . '</td>';
            echo '<td>' . esc_html( $monitor->status ) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>Keine Monitore gefunden.</p>';
    }
}

// Shortcode für die Monitore erstellen
function suble_uptimez_monitors_shortcode() {
    // API-Schlüssel holen
    $api_key = suble_uptimez_get_api_key();
    if ( empty( $api_key ) ) {
        return '<p>Kein API-Schlüssel gespeichert.</p>';
    }

    // Uptime Robot API URL
    $url = 'https://api.uptimerobot.com/v2/getMonitors';

    // Anfrage-Parameter (API-Key und Format)
    $data = array(
        'api_key' => $api_key,
        'format'  => 'json'
    );

    // cURL-Anfrage an die API
    $response = wp_remote_post( $url, array(
        'method'    => 'POST',
        'body'      => json_encode( $data ),
        'timeout'   => 15,
        'headers'   => array( 'Content-Type' => 'application/json' ),
    ) );

    // Überprüfen, ob die Anfrage erfolgreich war
    if ( is_wp_error( $response ) ) {
        return '<p>Fehler beim Abrufen der Monitore.</p>';
    }

    // Antwort dekodieren
    $body = wp_remote_retrieve_body( $response );
    $monitors_data = json_decode( $body );

    // Überprüfen, ob Monitore in der Antwort enthalten sind
    if ( empty( $monitors_data->monitors ) ) {
        return '<p>Keine Monitore gefunden.</p>';
    }

    // Monitore anzeigen
    $output = '<table>';
    $output .= '<thead><tr><th>Monitor Name</th><th>Status</th></tr></thead>';
    $output .= '<tbody>';

    foreach ( $monitors_data->monitors as $monitor ) {
        $status = ( $monitor->status == 2 ) ? 'Online' : 'Offline';
        $output .= '<tr>';
        $output .= '<td>' . esc_html( $monitor->friendly_name ) . '</td>';
        $output .= '<td>' . esc_html( $status ) . '</td>';
        $output .= '</tr>';
    }

    $output .= '</tbody>';
    $output .= '</table>';

    return $output;
}
add_shortcode( 'suble_uptimez_monitors', 'suble_uptimez_monitors_shortcode' );
