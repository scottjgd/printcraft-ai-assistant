=== PrintCraft AI Assistant ===
Contributors: printcraftcreations
Tags: customer service, chatbot, ai, openai, woocommerce
Requires at least: 5.8
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later

AI-powered customer service chatbot for Print Craft Creations. Pre-trained on your business. Learns from every conversation. Escalates to your team when needed.

== Description ==

PrintCraft AI Assistant adds a smart chat widget to your website, powered by OpenAI GPT. It comes pre-loaded with answers about your products, ordering, shipping, pricing, and more — and gets smarter as customers interact with it.

**Key Features:**

* Floating chat widget — appears on every page of your site
* Pre-trained with 20+ Print Craft Creations-specific Q&A pairs
* Powered by OpenAI GPT-4o Mini (or your choice of model)
* Learns from interactions — mark responses as helpful/unhelpful to improve
* Human escalation — emails your team when AI can't answer confidently
* Admin dashboard — view all conversations, manage knowledge base, handle escalations
* Knowledge Base editor — add, edit, and approve Q&A entries anytime
* Fully customizable — bot name, greeting, color, tone, support email

== Installation ==

1. Upload the `printcraft-ai-assistant` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **PrintCraft AI > Settings** in your WordPress admin
4. Enter your OpenAI API key (get one at https://platform.openai.com/api-keys)
5. Set your support email for escalation notifications
6. The chat widget will appear automatically on your site!

== Getting Your OpenAI API Key ==

1. Go to https://platform.openai.com/api-keys
2. Sign in or create a free account
3. Click "Create new secret key"
4. Copy and paste it into PrintCraft AI > Settings > OpenAI API Key
5. Add some credit to your account at https://platform.openai.com/billing
   (GPT-4o Mini costs roughly $0.00015 per message — very affordable)

== How the Learning System Works ==

* Every AI response shows 👍 / 👎 buttons to website visitors
* Thumbs-up responses are counted as successful
* Thumbs-down responses are flagged for admin review
* Admins can add improved answers to the Knowledge Base
* The AI uses your Knowledge Base on every response — more entries = smarter AI
* "Learned" entries (from flagged responses) appear in your Knowledge Base pending approval

== Escalation System ==

When the AI isn't confident in its answer (or the customer asks for a human), it:
1. Shows the customer a message saying your team has been notified
2. Sends an email to your support email address with the conversation
3. Creates an escalation ticket in your admin panel
4. Your team marks it as In Progress or Resolved with internal notes

== Frequently Asked Questions ==

= Will it work with WooCommerce? =
Yes! The widget works on all pages of your WordPress site, including WooCommerce product and checkout pages.

= How much does it cost to run? =
The plugin itself is free. You pay OpenAI for API usage. GPT-4o Mini (the default model) costs approximately $0.00015 per 1,000 tokens — a typical chat message costs less than $0.001. For a small business with moderate traffic, expect under $5/month.

= Can I customize the responses? =
Yes! Use the Knowledge Base editor in your admin panel to add, edit, or remove any Q&A pair. The AI uses these as its primary source of truth.

= What happens if the AI gives a wrong answer? =
Customers can click 👎 to flag unhelpful responses. You'll see these in your Knowledge Base as "Learned" entries. Review and approve improved answers to train the AI going forward.

== Changelog ==

= 1.0.0 =
* Initial release
* 20+ pre-loaded Q&A entries for Print Craft Creations
* Knowledge Base editor
* Conversation history viewer
* Escalation management dashboard
* OpenAI GPT integration with confidence scoring
* Customer feedback system
