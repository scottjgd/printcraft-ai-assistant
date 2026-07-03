<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PCAI_Admin {

    public function init() {
        add_action( 'admin_menu', array( $this, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_post_pcai_save_kb', array( $this, 'handle_kb_save' ) );
        add_action( 'admin_post_pcai_delete_kb', array( $this, 'handle_kb_delete' ) );
        add_action( 'admin_post_pcai_update_escalation', array( $this, 'handle_escalation_update' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    public function register_menus() {
        $escalation = new PCAI_Escalation();
        $open_count = $escalation->get_open_count();
        $badge = $open_count > 0 ? ' <span class="update-plugins"><span class="plugin-count">' . $open_count . '</span></span>' : '';

        add_menu_page(
            'PrintCraft AI',
            'PrintCraft AI' . $badge,
            'manage_options',
            'pcai-dashboard',
            array( $this, 'page_dashboard' ),
            'dashicons-format-chat',
            58
        );

        add_submenu_page( 'pcai-dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'pcai-dashboard', array( $this, 'page_dashboard' ) );
        add_submenu_page( 'pcai-dashboard', 'Conversations', 'Conversations', 'manage_options', 'pcai-conversations', array( $this, 'page_conversations' ) );
        add_submenu_page( 'pcai-dashboard', 'Knowledge Base', 'Knowledge Base', 'manage_options', 'pcai-knowledge', array( $this, 'page_knowledge' ) );
        add_submenu_page( 'pcai-dashboard', 'Escalations' . $badge, 'Escalations' . $badge, 'manage_options', 'pcai-escalations', array( $this, 'page_escalations' ) );
        add_submenu_page( 'pcai-dashboard', 'Settings', 'Settings', 'manage_options', 'pcai-settings', array( $this, 'page_settings' ) );
    }

    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'pcai' ) === false ) return;
        wp_enqueue_style( 'pcai-admin', PCAI_PLUGIN_URL . 'admin/css/admin.css', array(), PCAI_VERSION );
        wp_enqueue_script( 'pcai-admin', PCAI_PLUGIN_URL . 'admin/js/admin.js', array( 'jquery' ), PCAI_VERSION, true );
    }

    public function register_settings() {
        $fields = array(
            'pcai_openai_api_key', 'pcai_model', 'pcai_enabled', 'pcai_bot_name',
            'pcai_greeting', 'pcai_theme_color', 'pcai_support_email',
            'pcai_escalation_cc', 'pcai_tone', 'pcai_business_name',
            'pcai_github_user', 'pcai_github_repo', 'pcai_github_token',
        );
        foreach ( $fields as $field ) {
            register_setting( 'pcai_settings', $field );
        }

        if (
            isset( $_GET['pcai_clear_cache'] ) &&
            check_admin_referer( 'pcai_clear_cache' )
        ) {
            PCAI_GitHub_Updater::clear_cache();
            wp_safe_redirect( admin_url( 'admin.php?page=pcai-settings&cache_cleared=1' ) );
            exit;
        }
    }

    public function admin_notices() {
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'pcai' ) === false ) return;

        if ( ! get_option( 'pcai_openai_api_key' ) ) {
            echo '<div class="notice notice-warning is-dismissible"><p>';
            echo '<strong>PrintCraft AI:</strong> Please <a href="' . admin_url( 'admin.php?page=pcai-settings' ) . '">add your OpenAI API key</a> to activate the AI assistant.';
            echo '</p></div>';
        }
        if ( isset( $_GET['cache_cleared'] ) ) {
            echo '<div class="notice notice-success is-dismissible"><p><strong>PrintCraft AI:</strong> Update cache cleared — WordPress will check GitHub for the latest release on next load.</p></div>';
        }
    }

    public function page_dashboard() {
        $conversation = new PCAI_Conversation();
        $stats = $conversation->get_stats();
        $escalation = new PCAI_Escalation();
        include PCAI_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    public function page_conversations() {
        $session_id = isset( $_GET['session'] ) ? sanitize_text_field( wp_unslash( $_GET['session'] ) ) : '';
        $conversation = new PCAI_Conversation();
        if ( $session_id ) {
            $messages = $conversation->get_session_messages( $session_id );
            include PCAI_PLUGIN_DIR . 'admin/views/conversation-detail.php';
        } else {
            $sessions = $conversation->get_sessions( 100 );
            include PCAI_PLUGIN_DIR . 'admin/views/conversations.php';
        }
    }

    public function page_knowledge() {
        $kb = new PCAI_Knowledge_Base();
        $entries = $kb->get_all( false );
        $edit_id = isset( $_GET['edit'] ) ? intval( $_GET['edit'] ) : 0;
        include PCAI_PLUGIN_DIR . 'admin/views/knowledge.php';
    }

    public function page_escalations() {
        $escalation = new PCAI_Escalation();
        $session_id = isset( $_GET['session'] ) ? sanitize_text_field( wp_unslash( $_GET['session'] ) ) : '';
        $status_filter = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : null;
        $escalations = $escalation->get_all( $status_filter );
        include PCAI_PLUGIN_DIR . 'admin/views/escalations.php';
    }

    public function page_settings() {
        include PCAI_PLUGIN_DIR . 'admin/views/settings.php';
    }

    public function handle_kb_save() {
        check_admin_referer( 'pcai_kb_save' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );

        $kb = new PCAI_Knowledge_Base();
        $id = isset( $_POST['kb_id'] ) ? intval( $_POST['kb_id'] ) : 0;
        $question = isset( $_POST['question'] ) ? sanitize_text_field( wp_unslash( $_POST['question'] ) ) : '';
        $answer   = isset( $_POST['answer'] ) ? sanitize_textarea_field( wp_unslash( $_POST['answer'] ) ) : '';
        $category = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : 'general';
        $approved = isset( $_POST['approved'] ) ? 1 : 0;

        if ( $id ) {
            $kb->update_entry( $id, array(
                'question' => $question,
                'answer'   => $answer,
                'category' => $category,
                'approved' => $approved,
            ) );
        } else {
            $kb->add_entry( $question, $answer, $category, 'admin' );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=pcai-knowledge&saved=1' ) );
        exit;
    }

    public function handle_kb_delete() {
        check_admin_referer( 'pcai_kb_delete' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );

        $kb = new PCAI_Knowledge_Base();
        $id = isset( $_POST['kb_id'] ) ? intval( $_POST['kb_id'] ) : 0;
        if ( $id ) $kb->delete_entry( $id );

        wp_safe_redirect( admin_url( 'admin.php?page=pcai-knowledge&deleted=1' ) );
        exit;
    }

    public function handle_escalation_update() {
        check_admin_referer( 'pcai_escalation_update' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );

        $escalation = new PCAI_Escalation();
        $id      = isset( $_POST['escalation_id'] ) ? intval( $_POST['escalation_id'] ) : 0;
        $status  = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'open';
        $notes   = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';
        $by      = wp_get_current_user()->display_name;

        if ( $id ) $escalation->update_status( $id, $status, $notes, $by );

        wp_safe_redirect( admin_url( 'admin.php?page=pcai-escalations&updated=1' ) );
        exit;
    }
}
