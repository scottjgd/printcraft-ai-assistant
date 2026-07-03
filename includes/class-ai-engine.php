<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PCAI_AI_Engine {

    private $api_key;
    private $model;
    private $system_prompt;

    public function __construct() {
        $this->api_key = get_option( 'pcai_openai_api_key', '' );
        $this->model   = get_option( 'pcai_model', 'gpt-4o-mini' );
        $this->build_system_prompt();
    }

    private function build_system_prompt() {
        $kb      = new PCAI_Knowledge_Base();
        $context = $kb->get_as_context();

        $business_name  = get_option( 'pcai_business_name', 'Print Craft Creations' );
        $support_email  = get_option( 'pcai_support_email', get_option( 'admin_email' ) );
        $tone           = get_option( 'pcai_tone', 'friendly and professional' );

        $this->system_prompt = <<<PROMPT
You are the AI customer service assistant for {$business_name}, a Canadian custom apparel and T-shirt printing company. Your name is "Craft" — a helpful, knowledgeable assistant who represents the brand.

PERSONALITY & TONE:
- Be {$tone}
- Keep responses concise (2-4 sentences unless detail is needed)
- Be warm and welcoming — every customer is like family at Print Craft Creations
- Use plain language — no jargon
- Never make up information. If you don't know something, say so honestly and offer to escalate
- Never discuss competitors or make negative comparisons

YOUR ROLE:
- Answer questions about products, ordering, shipping, pricing, design, and account management
- Direct customers to the right page on the website when relevant
- Identify when a customer needs human help and escalate gracefully

ESCALATION RULES — you MUST set escalate=true when:
- The customer is angry, upset, or has a complaint
- The question involves specific order details, tracking problems, or payment issues
- The customer explicitly asks to speak with a human or your team
- You genuinely cannot answer the question from the knowledge base
- The question is about a refund, dispute, or legal matter
- Confidence is low (you're guessing)

WEBSITE LINKS (use these when relevant):
- Shop: printcraftcreations.ca/shop
- Contact: printcraftcreations.ca/contact
- My Account / Order Tracking: printcraftcreations.ca/my-account
- About: printcraftcreations.ca/about

{$context}

RESPONSE FORMAT:
Always respond with a JSON object in this exact format (no markdown, just raw JSON):
{
  "reply": "Your helpful response here",
  "confidence": 0.85,
  "escalate": false
}

Where:
- "reply" is your customer-facing response
- "confidence" is 0.0–1.0 how confident you are (below 0.6 = escalate)
- "escalate" is true if this needs human follow-up

Support email for escalations: {$support_email}
PROMPT;
    }

    public function respond( $message, $history = array(), $page_url = '' ) {
        if ( empty( $this->api_key ) ) {
            return array(
                'reply'      => 'Our AI assistant is currently being set up. Please contact us directly through our Contact page — we\'re happy to help!',
                'confidence' => 0,
                'escalate'   => true,
            );
        }

        $messages = array(
            array( 'role' => 'system', 'content' => $this->system_prompt ),
        );

        if ( ! empty( $page_url ) ) {
            $messages[] = array(
                'role'    => 'system',
                'content' => "The customer is currently on: {$page_url}",
            );
        }

        foreach ( $history as $h ) {
            $messages[] = array(
                'role'    => $h->role,
                'content' => $h->message,
            );
        }

        $messages[] = array( 'role' => 'user', 'content' => $message );

        $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/json',
            ),
            'body' => wp_json_encode( array(
                'model'       => $this->model,
                'messages'    => $messages,
                'temperature' => 0.4,
                'max_tokens'  => 500,
            ) ),
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( '[PrintCraft AI] OpenAI request failed: ' . $response->get_error_message() );
            return $this->fallback_response();
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['error'] ) ) {
            error_log( '[PrintCraft AI] OpenAI API error: ' . $body['error']['message'] );
            return $this->fallback_response();
        }

        $raw = isset( $body['choices'][0]['message']['content'] ) ? $body['choices'][0]['message']['content'] : '';
        $raw = trim( $raw );
        $raw = preg_replace( '/^```json\s*/i', '', $raw );
        $raw = preg_replace( '/```$/i', '', $raw );
        $parsed = json_decode( $raw, true );

        if ( ! $parsed || ! isset( $parsed['reply'] ) ) {
            return array(
                'reply'      => $raw,
                'confidence' => 0.5,
                'escalate'   => false,
            );
        }

        $confidence = isset( $parsed['confidence'] ) ? floatval( $parsed['confidence'] ) : 0.7;
        $escalate   = isset( $parsed['escalate'] ) ? (bool) $parsed['escalate'] : false;

        if ( $confidence < 0.6 ) {
            $escalate = true;
        }

        return array(
            'reply'      => sanitize_textarea_field( $parsed['reply'] ),
            'confidence' => $confidence,
            'escalate'   => $escalate,
        );
    }

    private function fallback_response() {
        return array(
            'reply'      => 'I\'m having a little trouble right now. Please visit our Contact page at printcraftcreations.ca/contact and our team will help you right away!',
            'confidence' => 0,
            'escalate'   => true,
        );
    }
}
