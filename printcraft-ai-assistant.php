<?php
/**
 * Plugin Name: PrintCraft AI Assistant
 * Plugin URI: https://printcraftcreations.ca
 * Description: AI-powered customer service chatbot for Print Craft Creations. Learns from interactions and escalates to human support when needed.
 * Version: 1.0.8
 * Author: Print Craft Creations
 * License: GPL-2.0+
 * Text Domain: printcraft-ai
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PCAI_VERSION', '1.0.8' );
define( 'PCAI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PCAI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PCAI_PLUGIN_FILE', __FILE__ );

require_once PCAI_PLUGIN_DIR . 'includes/class-database.php';
require_once PCAI_PLUGIN_DIR . 'includes/class-knowledge-base.php';
require_once PCAI_PLUGIN_DIR . 'includes/class-ai-engine.php';
require_once PCAI_PLUGIN_DIR . 'includes/class-conversation.php';
require_once PCAI_PLUGIN_DIR . 'includes/class-escalation.php';
require_once PCAI_PLUGIN_DIR . 'includes/class-github-updater.php';
require_once PCAI_PLUGIN_DIR . 'public/class-widget.php';
require_once PCAI_PLUGIN_DIR . 'admin/class-admin.php';

register_activation_hook( __FILE__, array( 'PCAI_Database', 'install' ) );
add_action( 'plugins_loaded', array( 'PCAI_Database', 'maybe_upgrade' ) );
add_action( 'wp_ajax_nopriv_pcai_save_contact', array( 'PCAI_Escalation', 'handle_save_contact_ajax' ) );
add_action( 'wp_ajax_pcai_save_contact', array( 'PCAI_Escalation', 'handle_save_contact_ajax' ) );
register_deactivation_hook( __FILE__, array( 'PCAI_Database', 'deactivate' ) );

function pcai_init() {
    $updater = new PCAI_GitHub_Updater();
    $updater->init();

    $widget = new PCAI_Widget();
    $widget->init();

    if ( is_admin() ) {
        $admin = new PCAI_Admin();
        $admin->init();
    }
}
add_action( 'plugins_loaded', 'pcai_init' );

function pcai_ajax_handler() {
    check_ajax_referer( 'pcai_nonce', 'nonce' );

    $message   = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';
    $session   = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
    $page_url  = isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : '';

    if ( empty( $message ) ) {
        wp_send_json_error( array( 'message' => 'Empty message.' ) );
    }

    $conversation = new PCAI_Conversation();
    $ai_engine    = new PCAI_AI_Engine();
    $escalation   = new PCAI_Escalation();

    $history = $conversation->get_history( $session );

    $result = $ai_engine->respond( $message, $history, $page_url );

    $conversation->save_message( $session, 'user', $message );
    $conversation->save_message( $session, 'assistant', $result['reply'], array(
        'confidence'    => $result['confidence'],
        'escalated'     => $result['escalate'],
        'page_url'      => $page_url,
    ) );

    if ( $result['escalate'] ) {
        $escalation->trigger( $session, $message, $result['reply'] );
    }

    wp_send_json_success( array(
        'reply'     => $result['reply'],
        'escalate'  => $result['escalate'],
        'api_error' => ! empty( $result['api_error'] ),
        'support_email' => get_option( 'pcai_support_email', get_option( 'admin_email' ) ),
    ) );
}
add_action( 'wp_ajax_pcai_chat', 'pcai_ajax_handler' );
add_action( 'wp_ajax_nopriv_pcai_chat', 'pcai_ajax_handler' );

function pcai_feedback_handler() {
    check_ajax_referer( 'pcai_nonce', 'nonce' );

    $session    = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
    $message_id = isset( $_POST['message_id'] ) ? intval( $_POST['message_id'] ) : 0;
    $helpful    = isset( $_POST['helpful'] ) ? (bool) $_POST['helpful'] : false;
    $question   = isset( $_POST['question'] ) ? sanitize_text_field( wp_unslash( $_POST['question'] ) ) : '';
    $answer     = isset( $_POST['answer'] ) ? sanitize_textarea_field( wp_unslash( $_POST['answer'] ) ) : '';

    $conversation = new PCAI_Conversation();
    $conversation->save_feedback( $session, $message_id, $helpful, $question, $answer );

    wp_send_json_success();
}
add_action( 'wp_ajax_pcai_feedback', 'pcai_feedback_handler' );
add_action( 'wp_ajax_nopriv_pcai_feedback', 'pcai_feedback_handler' );
