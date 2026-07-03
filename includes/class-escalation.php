<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PCAI_Escalation {

    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'pcai_escalations';
    }

    public function trigger( $session_id, $trigger_message, $ai_reply ) {
        global $wpdb;

        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$this->table} WHERE session_id = %s AND status = 'open'",
            $session_id
        ) );

        if ( $existing ) {
            return;
        }

        $wpdb->insert(
            $this->table,
            array(
                'session_id'      => sanitize_text_field( $session_id ),
                'trigger_message' => $trigger_message,
                'ai_reply'        => $ai_reply,
                'status'          => 'open',
            ),
            array( '%s', '%s', '%s', '%s' )
        );

        $this->send_notification( $session_id, $trigger_message, $ai_reply );
    }

    public function update_contact_info( $session_id, $name, $email, $phone ) {
        global $wpdb;
        $wpdb->update(
            $this->table,
            array(
                'customer_name'  => sanitize_text_field( $name ),
                'customer_email' => sanitize_email( $email ),
                'customer_phone' => sanitize_text_field( $phone ),
            ),
            array( 'session_id' => sanitize_text_field( $session_id ) ),
            array( '%s', '%s', '%s' ),
            array( '%s' )
        );

        $this->send_contact_notification( $session_id, $name, $email, $phone );
    }

    public static function handle_save_contact_ajax() {
        check_ajax_referer( 'pcai_nonce', 'nonce' );

        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
        $name       = isset( $_POST['name'] )       ? sanitize_text_field( wp_unslash( $_POST['name'] ) )       : '';
        $email      = isset( $_POST['email'] )      ? sanitize_email( wp_unslash( $_POST['email'] ) )           : '';
        $phone      = isset( $_POST['phone'] )      ? sanitize_text_field( wp_unslash( $_POST['phone'] ) )      : '';

        if ( empty( $session_id ) || ( empty( $email ) && empty( $phone ) ) ) {
            wp_send_json_error( 'Please provide at least an email or phone number.' );
        }

        $esc = new self();
        $esc->update_contact_info( $session_id, $name, $email, $phone );
        wp_send_json_success();
    }

    private function send_notification( $session_id, $trigger_message, $ai_reply ) {
        $to      = get_option( 'pcai_support_email', get_option( 'admin_email' ) );
        $cc      = get_option( 'pcai_escalation_cc', '' );
        $subject = '[Print Craft Creations] Customer Needs Help — AI Chat Escalation';

        $admin_url = admin_url( 'admin.php?page=pcai-escalations' );

        $body  = "Hello Print Craft Creations Team,\n\n";
        $body .= "A customer has a question your AI assistant escalated for human follow-up.\n\n";
        $body .= "--- CUSTOMER MESSAGE ---\n";
        $body .= $trigger_message . "\n\n";
        $body .= "--- AI RESPONSE GIVEN ---\n";
        $body .= $ai_reply . "\n\n";
        $body .= "NOTE: The customer may provide their contact info in the chat. Check the escalation in your admin panel:\n";
        $body .= $admin_url . "\n\n";
        $body .= "— PrintCraft AI Assistant";

        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        if ( ! empty( $cc ) ) {
            $headers[] = 'Cc: ' . sanitize_email( $cc );
        }

        wp_mail( $to, $subject, $body, $headers );
    }

    private function send_contact_notification( $session_id, $name, $email, $phone ) {
        $to      = get_option( 'pcai_support_email', get_option( 'admin_email' ) );
        $subject = '[Print Craft Creations] Customer Left Contact Info — Please Follow Up';

        $admin_url = admin_url( 'admin.php?page=pcai-escalations' );

        $body  = "Hello Print Craft Creations Team,\n\n";
        $body .= "A customer who needed help has left their contact information. Please follow up!\n\n";
        if ( $name )  $body .= "Name:  {$name}\n";
        if ( $email ) $body .= "Email: {$email}\n";
        if ( $phone ) $body .= "Phone: {$phone}\n";
        $body .= "\nView the escalation:\n" . $admin_url . "\n\n";
        $body .= "— PrintCraft AI Assistant";

        wp_mail( $to, $subject, $body );
    }

    public function get_all( $status = null, $limit = 50, $offset = 0 ) {
        global $wpdb;
        $where = $status ? $wpdb->prepare( 'WHERE status = %s', $status ) : '';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$this->table} $where ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        ) );
    }

    public function update_status( $id, $status, $notes = '', $resolved_by = '' ) {
        global $wpdb;
        return $wpdb->update(
            $this->table,
            array(
                'status'      => sanitize_text_field( $status ),
                'notes'       => sanitize_textarea_field( $notes ),
                'resolved_by' => sanitize_text_field( $resolved_by ),
            ),
            array( 'id' => intval( $id ) )
        );
    }

    public function get_open_count() {
        global $wpdb;
        return intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table} WHERE status = 'open'" ) );
    }
}
