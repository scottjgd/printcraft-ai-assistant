<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap pcai-wrap">
    <h1 class="pcai-page-title">
        <span class="dashicons dashicons-format-chat"></span>
        PrintCraft AI — Dashboard
    </h1>

    <div class="pcai-stats-grid">
        <div class="pcai-stat-card">
            <div class="pcai-stat-icon" style="background:#dbeafe">
                <span class="dashicons dashicons-groups" style="color:#2563eb"></span>
            </div>
            <div class="pcai-stat-body">
                <div class="pcai-stat-number"><?php echo esc_html( $stats['total_sessions'] ); ?></div>
                <div class="pcai-stat-label">Total Conversations</div>
            </div>
        </div>
        <div class="pcai-stat-card">
            <div class="pcai-stat-icon" style="background:#dcfce7">
                <span class="dashicons dashicons-format-chat" style="color:#16a34a"></span>
            </div>
            <div class="pcai-stat-body">
                <div class="pcai-stat-number"><?php echo esc_html( $stats['total_messages'] ); ?></div>
                <div class="pcai-stat-label">Messages Handled</div>
            </div>
        </div>
        <div class="pcai-stat-card">
            <div class="pcai-stat-icon" style="background:#fef3c7">
                <span class="dashicons dashicons-phone" style="color:#d97706"></span>
            </div>
            <div class="pcai-stat-body">
                <div class="pcai-stat-number"><?php echo esc_html( $escalation->get_open_count() ); ?></div>
                <div class="pcai-stat-label">Open Escalations</div>
            </div>
        </div>
        <div class="pcai-stat-card">
            <div class="pcai-stat-icon" style="background:#f3e8ff">
                <span class="dashicons dashicons-chart-bar" style="color:#9333ea"></span>
            </div>
            <div class="pcai-stat-body">
                <div class="pcai-stat-number"><?php echo esc_html( $stats['avg_confidence'] ); ?>%</div>
                <div class="pcai-stat-label">Avg. AI Confidence</div>
            </div>
        </div>
    </div>

    <div class="pcai-panels">
        <div class="pcai-panel">
            <h2>Quick Actions</h2>
            <ul class="pcai-quick-links">
                <li><a href="<?php echo admin_url('admin.php?page=pcai-escalations'); ?>" class="pcai-quick-link escalation">
                    <span class="dashicons dashicons-phone"></span>
                    View Open Escalations
                    <?php if ( $escalation->get_open_count() > 0 ): ?>
                        <span class="pcai-ql-badge"><?php echo esc_html( $escalation->get_open_count() ); ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="<?php echo admin_url('admin.php?page=pcai-conversations'); ?>" class="pcai-quick-link">
                    <span class="dashicons dashicons-format-chat"></span>
                    Browse Conversations
                </a></li>
                <li><a href="<?php echo admin_url('admin.php?page=pcai-knowledge&add=1'); ?>" class="pcai-quick-link">
                    <span class="dashicons dashicons-plus-alt"></span>
                    Add Knowledge Base Entry
                </a></li>
                <li><a href="<?php echo admin_url('admin.php?page=pcai-settings'); ?>" class="pcai-quick-link">
                    <span class="dashicons dashicons-admin-settings"></span>
                    Plugin Settings
                </a></li>
            </ul>
        </div>

        <div class="pcai-panel">
            <h2>Feedback Summary</h2>
            <?php $total_fb = $stats['helpful_count'] + $stats['unhelpful_count']; ?>
            <?php if ( $total_fb > 0 ): ?>
                <?php $pct = round( ( $stats['helpful_count'] / $total_fb ) * 100 ); ?>
                <div class="pcai-feedback-summary">
                    <div class="pcai-fb-bar-wrap">
                        <div class="pcai-fb-bar" style="width:<?php echo esc_attr( $pct ); ?>%"></div>
                    </div>
                    <p><strong><?php echo esc_html( $pct ); ?>%</strong> of rated responses were marked helpful
                        (<?php echo esc_html( $stats['helpful_count'] ); ?> 👍 / <?php echo esc_html( $stats['unhelpful_count'] ); ?> 👎)</p>
                    <p class="pcai-hint">Add unhelpful responses to your <a href="<?php echo admin_url('admin.php?page=pcai-knowledge'); ?>">Knowledge Base</a> to train the AI.</p>
                </div>
            <?php else: ?>
                <p class="pcai-hint">No customer feedback yet. Feedback appears after customers rate AI responses.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ( ! get_option('pcai_openai_api_key') ): ?>
    <div class="pcai-setup-banner">
        <span class="dashicons dashicons-warning" style="color:#d97706;font-size:22px;"></span>
        <div>
            <strong>Setup Required:</strong> Your AI assistant needs an OpenAI API key to start working.
            <a href="<?php echo admin_url('admin.php?page=pcai-settings'); ?>" class="button button-primary" style="margin-left:10px;">Configure Now</a>
        </div>
    </div>
    <?php endif; ?>
</div>
