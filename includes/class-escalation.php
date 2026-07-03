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

    private function send_notification( $session_id, $trigger_message, $ai_reply ) {
        $to      = get_option( 'pcai_support_email', get_option( 'admin_email' ) );
        $cc      = get_option( 'pcai_escalation_cc', '' );
        $subject = '[Print Craft Creations] Customer Needs Help — AI Chat Escalation';

        $admin_url = admin_url( 'admin.php?page=pcai-escalations&session=' . urlencode( $session_id ) );

        $body  = "Hello Print Craft Creations Team,\n\n";
        $body .= "A customer has a question that your AI assistant could not confidently answer. They may need follow-up.\n\n";
        $body .= "--- CUSTOMER MESSAGE ---\n";
        $body .= $trigger_message . "\n\n";
        $body .= "--- AI RESPONSE GIVEN ---\n";
        $body .= $ai_reply . "\n\n";
        $body .= "--- SESSION ID ---\n";
        $body .= $session_id . "\n\n";
        $body .= "View full conversation in WordPress admin:\n";
        $body .= $admin_url . "\n\n";
        $body .= "Please follow up with this customer as soon as possible.\n\n";
        $body .= "— PrintCraft AI Assistant";

        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        if ( ! empty( $cc ) ) {
            $headers[] = 'Cc: ' . sanitize_email( $cc );
        }

        wp_mail( $to, $subject, $body, $headers );
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
