<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PCAI_Widget {

    public function init() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_widget' ) );
    }

    public function enqueue_assets() {
        if ( get_option( 'pcai_enabled', '1' ) !== '1' ) return;

        wp_enqueue_style(
            'pcai-widget',
            PCAI_PLUGIN_URL . 'public/css/chat-widget.css',
            array(),
            PCAI_VERSION
        );

        wp_enqueue_script(
            'pcai-widget',
            PCAI_PLUGIN_URL . 'public/js/chat-widget.js',
            array( 'jquery' ),
            PCAI_VERSION,
            true
        );

        wp_localize_script( 'pcai-widget', 'PCAI', array(
            'ajax_url'   => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'pcai_nonce' ),
            'greeting'   => get_option( 'pcai_greeting', 'Hi there! 👋 I\'m Craft, your Print Craft Creations assistant. How can I help you today?' ),
            'bot_name'   => get_option( 'pcai_bot_name', 'Craft' ),
            'theme_color'=> get_option( 'pcai_theme_color', '#2563eb' ),
            'page_url'   => get_permalink() ?: home_url( $_SERVER['REQUEST_URI'] ),
        ) );
    }

    public function render_widget() {
        if ( get_option( 'pcai_enabled', '1' ) !== '1' ) return;
        $bot_name    = esc_attr( get_option( 'pcai_bot_name', 'Craft' ) );
        $theme_color = esc_attr( get_option( 'pcai_theme_color', '#2563eb' ) );
        ?>
        <div id="pcai-widget" style="--pcai-primary: <?php echo $theme_color; ?>">
            <button id="pcai-toggle" aria-label="Open chat assistant" title="Chat with us">
                <svg id="pcai-icon-chat" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <svg id="pcai-icon-close" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                <span id="pcai-badge" style="display:none"></span>
            </button>

            <div id="pcai-panel" role="dialog" aria-label="<?php echo $bot_name; ?> Chat Assistant">
                <div id="pcai-header">
                    <div id="pcai-avatar">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
                    </div>
                    <div id="pcai-header-info">
                        <strong><?php echo $bot_name; ?></strong>
                        <span>Print Craft Creations Assistant</span>
                    </div>
                    <button id="pcai-minimize" aria-label="Minimize chat">&times;</button>
                </div>

                <div id="pcai-messages" role="log" aria-live="polite"></div>

                <div id="pcai-contact-form" style="display:none">
                    <p id="pcai-contact-intro">So we can follow up with you directly, please leave your contact info:</p>
                    <input type="text"  id="pcai-contact-name"  placeholder="Your name" autocomplete="name">
                    <input type="email" id="pcai-contact-email" placeholder="Email address" autocomplete="email">
                    <input type="tel"   id="pcai-contact-phone" placeholder="Phone number (optional)" autocomplete="tel">
                    <button id="pcai-contact-submit">Send My Info</button>
                    <p id="pcai-contact-thanks" style="display:none">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Thanks! We'll be in touch soon.
                    </p>
                </div>

                <div id="pcai-input-area">
                    <textarea id="pcai-input" placeholder="Type your message..." rows="1" aria-label="Chat message input"></textarea>
                    <button id="pcai-send" aria-label="Send message">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
}
