<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap pcai-wrap">
    <h1 class="pcai-page-title">
        <span class="dashicons dashicons-phone"></span>
        Customer Escalations
    </h1>

    <?php if ( isset($_GET['updated']) ): ?>
        <div class="notice notice-success is-dismissible"><p>Escalation updated.</p></div>
    <?php endif; ?>

    <div class="pcai-filter-bar">
        <a href="<?php echo admin_url('admin.php?page=pcai-escalations'); ?>" class="button <?php echo !isset($_GET['status']) ? 'button-primary' : ''; ?>">All</a>
        <a href="<?php echo admin_url('admin.php?page=pcai-escalations&status=open'); ?>" class="button <?php echo (isset($_GET['status']) && $_GET['status']==='open') ? 'button-primary' : ''; ?>">Open</a>
        <a href="<?php echo admin_url('admin.php?page=pcai-escalations&status=in_progress'); ?>" class="button <?php echo (isset($_GET['status']) && $_GET['status']==='in_progress') ? 'button-primary' : ''; ?>">In Progress</a>
        <a href="<?php echo admin_url('admin.php?page=pcai-escalations&status=resolved'); ?>" class="button <?php echo (isset($_GET['status']) && $_GET['status']==='resolved') ? 'button-primary' : ''; ?>">Resolved</a>
    </div>

    <?php if ( empty($escalations) ): ?>
        <div class="pcai-empty">
            <span class="dashicons dashicons-yes-alt" style="font-size:48px;color:#16a34a;"></span>
            <p>No escalations to show. Your AI is handling everything!</p>
        </div>
    <?php else: ?>
    <?php foreach ( $escalations as $e ): ?>
    <div class="pcai-escalation-card <?php echo esc_attr($e->status); ?>">
        <div class="pcai-esc-header">
            <div>
                <span class="pcai-esc-status pcai-esc-status-<?php echo esc_attr($e->status); ?>">
                    <?php echo esc_html( ucwords( str_replace('_',' ', $e->status) ) ); ?>
                </span>
                <span class="pcai-hint" style="margin-left:10px"><?php echo esc_html( date_i18n('M j, Y g:i a', strtotime($e->created_at)) ); ?></span>
            </div>
            <a href="<?php echo admin_url('admin.php?page=pcai-conversations&session=' . urlencode($e->session_id)); ?>" class="button button-small">View Full Chat</a>
        </div>
        <div class="pcai-esc-body">
            <div class="pcai-esc-col">
                <strong>Customer asked:</strong>
                <p><?php echo esc_html( $e->trigger_message ); ?></p>
            </div>
            <div class="pcai-esc-col">
                <strong>AI responded:</strong>
                <p class="pcai-hint"><?php echo esc_html( $e->ai_reply ); ?></p>
            </div>
        </div>
        <?php if ( $e->notes ): ?>
        <div class="pcai-esc-notes">
            <strong>Notes:</strong> <?php echo esc_html($e->notes); ?>
            <?php if ( $e->resolved_by ): ?> — by <?php echo esc_html($e->resolved_by); ?><?php endif; ?>
        </div>
        <?php endif; ?>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="pcai-esc-form">
            <?php wp_nonce_field('pcai_escalation_update'); ?>
            <input type="hidden" name="action" value="pcai_update_escalation">
            <input type="hidden" name="escalation_id" value="<?php echo esc_attr($e->id); ?>">
            <div class="pcai-esc-form-row">
                <div class="pcai-esc-form-status">
                    <label class="pcai-esc-form-label">Status</label>
                    <select name="status" style="width:100%">
                        <option value="open" <?php selected($e->status,'open'); ?>>Open</option>
                        <option value="in_progress" <?php selected($e->status,'in_progress'); ?>>In Progress</option>
                        <option value="resolved" <?php selected($e->status,'resolved'); ?>>Resolved</option>
                    </select>
                </div>
                <div class="pcai-esc-form-notes">
                    <label class="pcai-esc-form-label">Internal Notes</label>
                    <input type="text" name="notes" value="<?php echo esc_attr($e->notes ?? ''); ?>" style="width:100%;box-sizing:border-box;" placeholder="e.g. Replied via email">
                </div>
                <div class="pcai-esc-form-btn">
                    <button type="submit" class="button button-primary" style="width:100%">Update</button>
                </div>
            </div>
        </form>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
