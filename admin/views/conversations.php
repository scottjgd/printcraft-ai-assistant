<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap pcai-wrap">
    <h1 class="pcai-page-title">
        <span class="dashicons dashicons-format-chat"></span>
        Conversations
    </h1>

    <?php if ( empty( $sessions ) ): ?>
        <div class="pcai-empty">
            <span class="dashicons dashicons-format-chat" style="font-size:48px;color:#cbd5e1;"></span>
            <p>No conversations yet. Once customers start chatting, they'll appear here.</p>
        </div>
    <?php else: ?>
    <div class="pcai-table-wrap">
    <table class="wp-list-table widefat striped pcai-table">
        <thead>
            <tr>
                <th style="min-width:140px">Session</th>
                <th style="min-width:70px">Messages</th>
                <th style="min-width:110px" class="pcai-col-hide-mobile">Avg Confidence</th>
                <th style="min-width:90px">Escalated?</th>
                <th style="min-width:110px" class="pcai-col-hide-mobile">Started</th>
                <th style="min-width:110px">Last Activity</th>
                <th style="min-width:70px">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $sessions as $s ): ?>
            <tr>
                <td><code style="font-size:11px"><?php echo esc_html( substr( $s->session_id, 0, 16 ) ); ?>…</code></td>
                <td><?php echo esc_html( $s->message_count ); ?></td>
                <td class="pcai-col-hide-mobile">
                    <?php if ( $s->avg_confidence !== null ): ?>
                        <span class="pcai-confidence" data-pct="<?php echo round( $s->avg_confidence * 100 ); ?>">
                            <?php echo round( $s->avg_confidence * 100 ); ?>%
                        </span>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td>
                    <?php if ( $s->has_escalation ): ?>
                        <span class="pcai-badge-escalated">Yes</span>
                    <?php else: ?>
                        <span class="pcai-badge-ok">No</span>
                    <?php endif; ?>
                </td>
                <td class="pcai-col-hide-mobile"><?php echo esc_html( date_i18n( 'M j, g:i a', strtotime( $s->started_at ) ) ); ?></td>
                <td><?php echo esc_html( date_i18n( 'M j, g:i a', strtotime( $s->last_activity ) ) ); ?></td>
                <td>
                    <a href="<?php echo admin_url( 'admin.php?page=pcai-conversations&session=' . urlencode( $s->session_id ) ); ?>" class="button button-small">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
