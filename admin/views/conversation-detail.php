<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap pcai-wrap">
    <h1 class="pcai-page-title">
        <a href="<?php echo admin_url('admin.php?page=pcai-conversations'); ?>" style="text-decoration:none;color:inherit;">
            <span class="dashicons dashicons-arrow-left-alt2"></span>
        </a>
        Conversation Detail
    </h1>
    <p class="pcai-hint">Session: <code><?php echo esc_html( $session_id ); ?></code></p>

    <div class="pcai-chat-preview">
        <?php if ( empty( $messages ) ): ?>
            <p class="pcai-hint">No messages found for this session.</p>
        <?php else: ?>
            <?php foreach ( $messages as $msg ): ?>
            <div class="pcai-chat-msg pcai-chat-<?php echo esc_attr( $msg->role ); ?>">
                <div class="pcai-chat-meta">
                    <strong><?php echo $msg->role === 'user' ? 'Customer' : 'AI (Craft)'; ?></strong>
                    <span><?php echo esc_html( date_i18n( 'M j, g:i:s a', strtotime( $msg->created_at ) ) ); ?></span>
                    <?php if ( $msg->role === 'assistant' && $msg->confidence !== null ): ?>
                        <span class="pcai-confidence"><?php echo round( $msg->confidence * 100 ); ?>% confidence</span>
                    <?php endif; ?>
                    <?php if ( $msg->escalated ): ?>
                        <span class="pcai-badge-escalated">Escalated</span>
                    <?php endif; ?>
                    <?php if ( $msg->helpful === '1' ): ?>
                        <span style="color:#16a34a">👍 Helpful</span>
                    <?php elseif ( $msg->helpful === '0' ): ?>
                        <span style="color:#dc2626">👎 Not helpful</span>
                    <?php endif; ?>
                </div>
                <div class="pcai-chat-bubble"><?php echo nl2br( esc_html( $msg->message ) ); ?></div>
                <?php if ( $msg->role === 'assistant' && $msg->helpful === '0' ): ?>
                <div style="margin-top:8px">
                    <a href="<?php echo admin_url('admin.php?page=pcai-knowledge&add=1&q=' . urlencode($msg->message)); ?>" class="button button-small">Add improved answer to Knowledge Base</a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
