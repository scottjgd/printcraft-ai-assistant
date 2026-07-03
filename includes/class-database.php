<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PCAI_Database {

    public static function install() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql_conversations = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pcai_conversations (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id VARCHAR(64) NOT NULL,
            role ENUM('user','assistant') NOT NULL,
            message LONGTEXT NOT NULL,
            confidence FLOAT DEFAULT NULL,
            escalated TINYINT(1) DEFAULT 0,
            page_url VARCHAR(500) DEFAULT NULL,
            helpful TINYINT(1) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        $sql_knowledge = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pcai_knowledge (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            question TEXT NOT NULL,
            answer LONGTEXT NOT NULL,
            category VARCHAR(100) DEFAULT 'general',
            source ENUM('seed','learned','admin') DEFAULT 'admin',
            approved TINYINT(1) DEFAULT 1,
            use_count INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category (category),
            KEY approved (approved)
        ) $charset_collate;";

        $sql_escalations = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pcai_escalations (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id VARCHAR(64) NOT NULL,
            trigger_message LONGTEXT NOT NULL,
            ai_reply LONGTEXT NOT NULL,
            status ENUM('open','in_progress','resolved') DEFAULT 'open',
            resolved_by VARCHAR(100) DEFAULT NULL,
            notes TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        dbDelta( $sql_conversations );
        dbDelta( $sql_knowledge );
        dbDelta( $sql_escalations );

        add_option( 'pcai_db_version', PCAI_VERSION );

        $kb = new PCAI_Knowledge_Base();
        $kb->seed_initial_knowledge();
    }

    public static function deactivate() {
    }
}
