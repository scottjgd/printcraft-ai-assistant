/* PrintCraft AI Chat Widget */
(function($) {
    'use strict';

    var PCAI_Chat = {
        sessionId: '',
        isOpen: false,
        messageCount: 0,
        hasEscalated: false,
        lastBotMessageId: null,
        lastUserMessage: '',

        init: function() {
            this.sessionId = this.getOrCreateSession();
            this.bindEvents();
            this.showGreeting();
            this.applyTheme();

            setTimeout(function() {
                PCAI_Chat.showBadge();
            }, 8000);
        },

        applyTheme: function() {
            var color = PCAI.theme_color || '#2563eb';
            document.documentElement.style.setProperty('--pcai-primary', color);
        },

        getOrCreateSession: function() {
            var key = 'pcai_session';
            var existing = sessionStorage.getItem(key);
            if (existing) return existing;
            var id = 'pcai_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem(key, id);
            return id;
        },

        bindEvents: function() {
            $('#pcai-toggle').on('click', function() { PCAI_Chat.toggle(); });
            $('#pcai-minimize').on('click', function() { PCAI_Chat.close(); });
            $('#pcai-send').on('click', function() { PCAI_Chat.sendMessage(); });
            $('#pcai-input').on('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    PCAI_Chat.sendMessage();
                }
            });
            $('#pcai-input').on('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 80) + 'px';
            });
        },

        toggle: function() {
            if (this.isOpen) { this.close(); } else { this.open(); }
        },

        open: function() {
            this.isOpen = true;
            $('#pcai-panel').addClass('open');
            $('#pcai-icon-chat').hide();
            $('#pcai-icon-close').show();
            $('#pcai-badge').hide().text('');
            $('#pcai-input').focus();
            this.scrollToBottom();
        },

        close: function() {
            this.isOpen = false;
            $('#pcai-panel').removeClass('open');
            $('#pcai-icon-chat').show();
            $('#pcai-icon-close').hide();
        },

        showBadge: function() {
            if (!this.isOpen && this.messageCount === 0) {
                $('#pcai-badge').text('1').show();
            }
        },

        showGreeting: function() {
            this.addBotMessage(PCAI.greeting || 'Hi! How can I help you today?');
        },

        sendMessage: function() {
            var text = $('#pcai-input').val().trim();
            if (!text) return;

            this.lastUserMessage = text;
            $('#pcai-input').val('').css('height', '');
            $('#pcai-send').prop('disabled', true);
            this.messageCount++;

            this.addUserMessage(text);
            this.showTyping();

            $.ajax({
                url: PCAI.ajax_url,
                type: 'POST',
                data: {
                    action: 'pcai_chat',
                    nonce: PCAI.nonce,
                    message: text,
                    session_id: this.sessionId,
                    page_url: PCAI.page_url || window.location.href,
                },
                success: function(res) {
                    PCAI_Chat.hideTyping();
                    $('#pcai-send').prop('disabled', false);
                    if (res.success) {
                        var showFb = !res.data.api_error;
                        var msgId = PCAI_Chat.addBotMessage(res.data.reply, showFb);
                        PCAI_Chat.lastBotMessageId = msgId;
                        if (res.data.escalate && !res.data.api_error && !PCAI_Chat.hasEscalated) {
                            PCAI_Chat.hasEscalated = true;
                            setTimeout(function() {
                                PCAI_Chat.showContactForm();
                            }, 900);
                        }
                    } else {
                        PCAI_Chat.addBotMessage("I'm having trouble right now. Please visit our <a href='https://printcraftcreations.ca/contact' target='_blank'>Contact page</a> and we'll help you right away!");
                    }
                },
                error: function() {
                    PCAI_Chat.hideTyping();
                    $('#pcai-send').prop('disabled', false);
                    PCAI_Chat.addBotMessage("Something went wrong on my end. Please try again or <a href='https://printcraftcreations.ca/contact' target='_blank'>contact us directly</a>.");
                },
            });
        },

        addUserMessage: function(text) {
            var time = this.formatTime(new Date());
            var html = '<div class="pcai-msg user">' +
                '<div class="pcai-bubble">' + this.escapeHtml(text) + '</div>' +
                '<span class="pcai-time">' + time + '</span>' +
                '</div>';
            $('#pcai-messages').append(html);
            this.scrollToBottom();
        },

        addBotMessage: function(text, showFeedback) {
            var id = 'pcai-msg-' + Date.now();
            var time = this.formatTime(new Date());
            var feedback = '';
            if (showFeedback) {
                feedback = '<div class="pcai-feedback" id="fb-' + id + '">' +
                    '<button class="pcai-fb-yes" data-id="' + id + '" title="This was helpful">👍 Helpful</button>' +
                    '<button class="pcai-fb-no" data-id="' + id + '" title="This wasn\'t helpful">👎 Not helpful</button>' +
                    '</div>';
            }
            var html = '<div class="pcai-msg bot" id="' + id + '">' +
                '<div class="pcai-bubble">' + this.linkify(text) + '</div>' +
                '<span class="pcai-time">' + time + '</span>' +
                feedback +
                '</div>';
            $('#pcai-messages').append(html);
            this.scrollToBottom();
            this.bindFeedback(id);
            return id;
        },

        bindFeedback: function(id) {
            var self = this;
            $('#fb-' + id + ' .pcai-fb-yes').on('click', function() {
                $(this).addClass('active').siblings().prop('disabled', true);
                self.sendFeedback(id, true, self.lastUserMessage, '');
                $('#fb-' + id).find('button').prop('disabled', true);
            });
            $('#fb-' + id + ' .pcai-fb-no').on('click', function() {
                $(this).addClass('active').siblings().prop('disabled', true);
                self.sendFeedback(id, false, self.lastUserMessage, '');
                $('#fb-' + id).find('button').prop('disabled', true);
            });
        },

        sendFeedback: function(msgId, helpful, question, answer) {
            $.post(PCAI.ajax_url, {
                action: 'pcai_feedback',
                nonce: PCAI.nonce,
                session_id: this.sessionId,
                message_id: msgId,
                helpful: helpful ? '1' : '0',
                question: question,
                answer: answer,
            });
        },

        showContactForm: function() {
            var $form = $('#pcai-contact-form');
            $form.slideDown(250);
            PCAI_Chat.scrollToBottom();

            $('#pcai-contact-submit').off('click').on('click', function() {
                var name  = $('#pcai-contact-name').val().trim();
                var email = $('#pcai-contact-email').val().trim();
                var phone = $('#pcai-contact-phone').val().trim();

                if (!email && !phone) {
                    $('#pcai-contact-email').focus();
                    return;
                }

                var $btn = $(this);
                $btn.prop('disabled', true).text('Sending…');

                $.ajax({
                    url: PCAI.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'pcai_save_contact',
                        nonce: PCAI.nonce,
                        session_id: PCAI_Chat.sessionId,
                        name: name,
                        email: email,
                        phone: phone,
                    },
                    complete: function() {
                        $form.find('input, button').hide();
                        $('#pcai-contact-intro').hide();
                        $('#pcai-contact-thanks').show();
                        PCAI_Chat.scrollToBottom();
                    },
                });
            });
        },

        showTyping: function() {
            var html = '<div class="pcai-msg bot" id="pcai-typing">' +
                '<div class="pcai-typing"><span></span><span></span><span></span></div>' +
                '</div>';
            $('#pcai-messages').append(html);
            this.scrollToBottom();
        },

        hideTyping: function() {
            $('#pcai-typing').remove();
        },

        scrollToBottom: function() {
            var el = document.getElementById('pcai-messages');
            if (el) el.scrollTop = el.scrollHeight;
        },

        formatTime: function(date) {
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        escapeHtml: function(text) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        },

        linkify: function(text) {
            text = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            text = text.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>');
            text = text.replace(/\n/g, '<br>');
            return text;
        },
    };

    $(document).ready(function() {
        PCAI_Chat.init();
    });

})(jQuery);
