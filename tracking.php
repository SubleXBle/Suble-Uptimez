// Datei: tracking.php
// -------------------------------------------------------------
// Das Tracking-Modul einbinden
// include_once plugin_dir_path(__FILE__) . 'tracking.php';
// --------------------------------------------------------------

function suble_pluginz_track_installation() {
    suble_pluginz_send_tracking_event("Uptimez Installiert");
}
register_activation_hook(__FILE__, 'suble_pluginz_track_installation');

function suble_pluginz_track_uninstall() {
    suble_pluginz_send_tracking_event("Uptimez Deinstalliert");
}
register_uninstall_hook(__FILE__, 'suble_pluginz_track_uninstall');

function suble_pluginz_track_update($upgrader_object, $options) {
    if ($options['action'] == 'update' && $options['type'] == 'plugin') {
        $plugin_basename = plugin_basename(__FILE__);

        foreach ($options['plugins'] as $plugin) {
            if ($plugin === $plugin_basename) {
                suble_pluginz_send_tracking_event("Uptimez Aktualisiert");
            }
        }
    }
}
add_action('upgrader_process_complete', 'suble_pluginz_track_update', 10, 2);

function suble_pluginz_send_tracking_event($action) {
    if (get_option('suble_pluginz_tracking') !== 'ja') {
        return;
    }

    $site_url = get_site_url();
    $matomo_url = "https://dein-matomo-server.com/matomo.php?idsite=10&rec=1";
    $matomo_url .= "&action_name=" . urlencode($action);
    $matomo_url .= "&url=" . urlencode($site_url);
   // $matomo_url .= "&e_c=Plugin&token_auth=DEIN_TOKEN";

    wp_remote_get($matomo_url);
}
