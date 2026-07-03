<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PCAI_Conversation {

    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'pcai_conversations';
    }

    public function get_history( $session_id, $limit = 10 ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT role, message FROM {$this->table} WHERE session_id = %s ORDER BY created_at DESC LIMIT %d",
            $session_id,
            $limit
        ) );
    }

    public function save_message( $session_id, $role, $message, $meta = array() ) {
        global $wpdb;
        $data = array(
            'session_id' => sanitize_text_field( $session_id ),
            'role'       => $role,
            'message'    => $message,
        );
        $format = array( '%s', '%s', '%s' );

        if ( isset( $meta['confidence'] ) ) {
            $data['confidence'] = floatval( $meta['confidence'] );
            $format[]           = '%f';
        }
        if ( isset( $meta['escalated'] ) ) {
            $data['escalated'] = $meta['escalated'] ? 1 : 0;
            $format[]          = '%d';
        }
        if ( isset( $meta['page_url'] ) ) {
            $data['page_url'] = esc_url_raw( $meta['page_url'] );
            $format[]         = '%s';
        }

        $wpdb->insert( $this->table, $data, $format );
        return $wpdb->insert_id;
    }

    public function save_feedback( $session_id, $message_id, $helpful, $question = '', $answer = '' ) {
        global $wpdb;
        $wpdb->update(
            $this->table,
            array( 'helpful' => $helpful ? 1 : 0 ),
            array( 'id' => intval( $message_id ) ),
            array( '%d' ),
            array( '%d' )
        );

        if ( ! $helpful && ! empty( $question ) && ! empty( $answer ) ) {
            $kb = new PCAI_Knowledge_Base();
            $kb->add_entry( $question, $answer, 'learned', 'learned' );
        }
    }

    public function get_sessions( $limit = 50, $offset = 0, $escalated_only = false ) {
        global $wpdb;
        $where = $escalated_only ? 'WHERE escalated = 1' : '';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT session_id,
                    MIN(created_at) AS started_at,
                    MAX(created_at) AS last_activity,
                    COUNT(*) AS message_count,
                    MAX(escalated) AS has_escalation,
                    AVG(confidence) AS avg_confidence
             FROM {$this->table}
             $where
             GROUP BY session_id
             ORDER BY last_activity DESC
             LIMIT %d OFFSET %d",
            $limit,
            $offset
        ) );
    }

    public function get_session_messages( $session_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE session_id = %s ORDER BY created_at ASC",
            $session_id
        ) );
    }

    public function get_stats() {
        global $wpdb;
        $total_sessions    = $wpdb->get_var( "SELECT COUNT(DISTINCT session_id) FROM {$this->table}" );
        $total_messages    = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table} WHERE role = 'user'" );
        $escalated_count   = $wpdb->get_var( "SELECT COUNT(DISTINCT session_id) FROM {$this->table} WHERE escalated = 1" );
        $helpful_count     = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table} WHERE helpful = 1" );
        $unhelpful_count   = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table} WHERE helpful = 0" );
        $avg_confidence    = $wpdb->get_var( "SELECT AVG(confidence) FROM {$this->table} WHERE role = 'assistant' AND confidence IS NOT NULL" );

        return array(
            'total_sessions'  => intval( $total_sessions ),
            'total_messages'  => intval( $total_messages ),
            'escalated_count' => intval( $escalated_count ),
            'helpful_count'   => intval( $helpful_count ),
            'unhelpful_count' => intval( $unhelpful_count ),
            'avg_confidence'  => round( floatval( $avg_confidence ) * 100 ),
        );
    }
}
