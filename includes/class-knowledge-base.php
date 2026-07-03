<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PCAI_Knowledge_Base {

    public function seed_initial_knowledge() {
        global $wpdb;
        $table = $wpdb->prefix . 'pcai_knowledge';

        $existing = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE source = 'seed'" );
        if ( $existing > 0 ) {
            return;
        }

        $knowledge = array(
            array(
                'category' => 'about',
                'question' => 'What is Print Craft Creations?',
                'answer'   => 'Print Craft Creations is a Canadian custom apparel and T-shirt printing company. We specialize in expert printing on custom clothing — creating pieces that people love to wear. We treat every customer like family and take pride in the quality and craftsmanship of every order.',
            ),
            array(
                'category' => 'about',
                'question' => 'Where are you located? What country do you operate in?',
                'answer'   => 'Print Craft Creations is based in Canada and serves customers across the country. You can reach us through our website at printcraftcreations.ca or use the Contact page to get in touch directly.',
            ),
            array(
                'category' => 'products',
                'question' => 'What products do you sell? What can I get printed?',
                'answer'   => 'We specialize in custom printed apparel — primarily custom T-shirts and clothing. You can browse our full product catalog in the Shop section of our website at printcraftcreations.ca/shop. We offer a variety of styles, sizes, and printing options to suit your needs.',
            ),
            array(
                'category' => 'ordering',
                'question' => 'How do I place an order?',
                'answer'   => 'Ordering is easy! Simply visit our Shop at printcraftcreations.ca/shop, browse our products, select your options (size, color, quantity), add your custom design, and proceed through checkout. If you need help with a bulk or custom order, please use our Contact page and we\'ll be happy to assist you.',
            ),
            array(
                'category' => 'ordering',
                'question' => 'Can I order in bulk? Do you do bulk orders?',
                'answer'   => 'Yes! We welcome bulk orders for businesses, teams, events, and organizations. For large or custom bulk orders, please reach out to us through the Contact page on our website and we\'ll provide you with pricing and details tailored to your needs.',
            ),
            array(
                'category' => 'design',
                'question' => 'What file formats do you accept for custom designs?',
                'answer'   => 'For the best print quality, we recommend submitting your artwork as a high-resolution file. Common accepted formats typically include PNG, JPG, SVG, AI, EPS, and PDF. For specific design requirements or if you\'re unsure, please contact us through our Contact page and our team will guide you.',
            ),
            array(
                'category' => 'design',
                'question' => 'Can you help me create a design? Do you offer design services?',
                'answer'   => 'Please reach out to us through our Contact page and our team will let you know what design assistance options are available. We want to make sure your final product looks exactly the way you envision it!',
            ),
            array(
                'category' => 'printing',
                'question' => 'What printing methods do you use?',
                'answer'   => 'We use professional printing techniques to ensure vibrant, long-lasting results on your custom apparel. For detailed information about our specific printing methods, please contact us through our Contact page or browse our product descriptions in the Shop.',
            ),
            array(
                'category' => 'shipping',
                'question' => 'Do you ship across Canada? What are your shipping options?',
                'answer'   => 'Yes, we ship across Canada! Shipping options and rates are calculated at checkout based on your location and order size. For specific shipping timelines or if you have a deadline, please contact us so we can ensure your order arrives on time.',
            ),
            array(
                'category' => 'shipping',
                'question' => 'How long does shipping take? What is the delivery time?',
                'answer'   => 'Delivery times depend on your location within Canada and the complexity of your order (production time + shipping time). Please check our website or contact us for current estimated turnaround times. If you have a specific deadline, let us know and we\'ll do our best to accommodate you!',
            ),
            array(
                'category' => 'turnaround',
                'question' => 'How long does it take to produce my order? What is the turnaround time?',
                'answer'   => 'Turnaround time depends on the type and size of your order. For current production timelines, please contact us through our Contact page or check your order confirmation for details. If you have a deadline, let us know when ordering and we\'ll do our best to meet it.',
            ),
            array(
                'category' => 'pricing',
                'question' => 'How much does it cost? What are your prices?',
                'answer'   => 'Pricing depends on the product, quantity, number of colors, and print complexity. The best way to get accurate pricing is to visit our Shop at printcraftcreations.ca/shop or contact us for a custom quote on larger orders. Bulk orders typically qualify for volume discounts!',
            ),
            array(
                'category' => 'account',
                'question' => 'How do I track my order?',
                'answer'   => 'You can track your order by logging into your account at printcraftcreations.ca under "My Account." Once your order ships, you\'ll receive a tracking number by email. If you have any issues tracking your order, please contact us and we\'ll help right away.',
            ),
            array(
                'category' => 'account',
                'question' => 'How do I create an account or log in?',
                'answer'   => 'You can create an account or log in by clicking "My Account" in the navigation menu at printcraftcreations.ca. Having an account lets you track orders, view order history, and manage your details.',
            ),
            array(
                'category' => 'returns',
                'question' => 'What is your return policy? Can I return or exchange my order?',
                'answer'   => 'Because our products are custom-made to your specifications, we handle returns on a case-by-case basis. If there is a defect or error on our part, we will absolutely make it right! Please contact us through our Contact page as soon as possible with your order number and a description of the issue.',
            ),
            array(
                'category' => 'returns',
                'question' => 'My order arrived damaged or with a mistake. What do I do?',
                'answer'   => 'We\'re so sorry to hear that! Please contact us immediately through our Contact page with your order number, a description of the issue, and photos if possible. We will prioritize getting this resolved for you right away — your satisfaction is our top priority.',
            ),
            array(
                'category' => 'contact',
                'question' => 'How can I contact you? How do I get in touch?',
                'answer'   => 'The best way to reach us is through the Contact page on our website at printcraftcreations.ca/contact. You can also reach out through our website\'s contact form and our team will get back to you as soon as possible. We\'re here to help!',
            ),
            array(
                'category' => 'contact',
                'question' => 'What are your business hours?',
                'answer'   => 'For our current business hours and response times, please check our Contact page at printcraftcreations.ca/contact. We do our best to respond to all inquiries promptly!',
            ),
            array(
                'category' => 'payment',
                'question' => 'What payment methods do you accept?',
                'answer'   => 'We accept all major payment methods through our secure online checkout. For specific payment options available, please proceed to checkout on our website or contact us if you have questions about payment for a large order.',
            ),
            array(
                'category' => 'minimum',
                'question' => 'Is there a minimum order quantity?',
                'answer'   => 'Minimum order quantities may vary depending on the product and printing method. Please check individual product listings in our Shop or contact us for specific minimums on your desired product. We try to accommodate orders of all sizes!',
            ),
        );

        foreach ( $knowledge as $item ) {
            $wpdb->insert(
                $table,
                array(
                    'question' => $item['question'],
                    'answer'   => $item['answer'],
                    'category' => $item['category'],
                    'source'   => 'seed',
                    'approved' => 1,
                ),
                array( '%s', '%s', '%s', '%s', '%d' )
            );
        }
    }

    public function get_all( $approved_only = true ) {
        global $wpdb;
        $table = $wpdb->prefix . 'pcai_knowledge';
        $where = $approved_only ? 'WHERE approved = 1' : '';
        return $wpdb->get_results( "SELECT * FROM $table $where ORDER BY category, id" );
    }

    public function get_as_context() {
        $items = $this->get_all( true );
        if ( empty( $items ) ) return '';

        $context = "KNOWLEDGE BASE — use this to answer customer questions:\n\n";
        foreach ( $items as $item ) {
            $context .= "Q: {$item->question}\nA: {$item->answer}\n\n";
        }
        return $context;
    }

    public function add_entry( $question, $answer, $category = 'general', $source = 'admin' ) {
        global $wpdb;
        return $wpdb->insert(
            $wpdb->prefix . 'pcai_knowledge',
            array(
                'question' => sanitize_text_field( $question ),
                'answer'   => sanitize_textarea_field( $answer ),
                'category' => sanitize_text_field( $category ),
                'source'   => $source,
                'approved' => ( $source === 'admin' ) ? 1 : 0,
            ),
            array( '%s', '%s', '%s', '%s', '%d' )
        );
    }

    public function update_entry( $id, $data ) {
        global $wpdb;
        return $wpdb->update(
            $wpdb->prefix . 'pcai_knowledge',
            $data,
            array( 'id' => intval( $id ) )
        );
    }

    public function delete_entry( $id ) {
        global $wpdb;
        return $wpdb->delete(
            $wpdb->prefix . 'pcai_knowledge',
            array( 'id' => intval( $id ) )
        );
    }

    public function increment_use( $id ) {
        global $wpdb;
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}pcai_knowledge SET use_count = use_count + 1 WHERE id = %d",
            $id
        ) );
    }
}
