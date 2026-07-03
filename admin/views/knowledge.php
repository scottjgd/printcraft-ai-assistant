<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap pcai-wrap">
    <h1 class="pcai-page-title">
        <span class="dashicons dashicons-book-alt"></span>
        Knowledge Base
        <a href="<?php echo admin_url('admin.php?page=pcai-knowledge&add=1'); ?>" class="page-title-action">Add New Entry</a>
    </h1>

    <?php if ( isset($_GET['saved']) ): ?>
        <div class="notice notice-success is-dismissible"><p>Entry saved successfully.</p></div>
    <?php endif; ?>
    <?php if ( isset($_GET['deleted']) ): ?>
        <div class="notice notice-success is-dismissible"><p>Entry deleted.</p></div>
    <?php endif; ?>

    <?php
    $show_form = isset($_GET['add']) || $edit_id > 0;
    $edit_entry = null;
    if ( $edit_id > 0 ) {
        foreach ( $entries as $e ) {
            if ( (int)$e->id === $edit_id ) { $edit_entry = $e; break; }
        }
    }
    $prefill_q = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
    ?>

    <?php if ( $show_form ): ?>
    <div class="pcai-panel pcai-form-panel">
        <h2><?php echo $edit_entry ? 'Edit Entry' : 'Add New Entry'; ?></h2>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('pcai_kb_save'); ?>
            <input type="hidden" name="action" value="pcai_save_kb">
            <?php if ( $edit_entry ): ?>
                <input type="hidden" name="kb_id" value="<?php echo esc_attr($edit_entry->id); ?>">
            <?php endif; ?>
            <table class="form-table">
                <tr>
                    <th><label for="question">Question / Trigger</label></th>
                    <td>
                        <input type="text" id="question" name="question" class="regular-text"
                               value="<?php echo esc_attr( $edit_entry ? $edit_entry->question : $prefill_q ); ?>" required>
                        <p class="description">The question or topic this entry answers.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="answer">Answer</label></th>
                    <td>
                        <textarea id="answer" name="answer" rows="5" class="large-text" required><?php echo esc_textarea( $edit_entry ? $edit_entry->answer : '' ); ?></textarea>
                        <p class="description">The response the AI will use when this topic comes up.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="category">Category</label></th>
                    <td>
                        <select id="category" name="category">
                            <?php
                            $cats = array('general','about','products','ordering','design','printing','shipping','turnaround','pricing','account','returns','contact','payment','minimum');
                            $sel  = $edit_entry ? $edit_entry->category : 'general';
                            foreach ($cats as $cat):
                            ?>
                                <option value="<?php echo esc_attr($cat); ?>" <?php selected($sel, $cat); ?>><?php echo esc_html(ucfirst($cat)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Approved / Active</th>
                    <td>
                        <label>
                            <input type="checkbox" name="approved" value="1" <?php checked( $edit_entry ? $edit_entry->approved : 1 ); ?>>
                            Active (AI will use this entry)
                        </label>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary">Save Entry</button>
                <a href="<?php echo admin_url('admin.php?page=pcai-knowledge'); ?>" class="button">Cancel</a>
            </p>
        </form>
    </div>
    <?php endif; ?>

    <table class="wp-list-table widefat fixed striped pcai-table">
        <thead>
            <tr>
                <th style="width:30%">Question</th>
                <th>Answer</th>
                <th style="width:90px">Category</th>
                <th style="width:80px">Source</th>
                <th style="width:60px">Uses</th>
                <th style="width:80px">Status</th>
                <th style="width:100px">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty($entries) ): ?>
            <tr><td colspan="7" class="pcai-empty-row">No entries yet.</td></tr>
            <?php endif; ?>
            <?php foreach ( $entries as $entry ): ?>
            <tr>
                <td><?php echo esc_html( $entry->question ); ?></td>
                <td class="pcai-answer-preview"><?php echo esc_html( wp_trim_words( $entry->answer, 20 ) ); ?></td>
                <td><span class="pcai-cat-badge"><?php echo esc_html( $entry->category ); ?></span></td>
                <td><span class="pcai-source-<?php echo esc_attr($entry->source); ?>"><?php echo esc_html( ucfirst($entry->source) ); ?></span></td>
                <td><?php echo esc_html( $entry->use_count ); ?></td>
                <td>
                    <?php if ($entry->approved): ?>
                        <span class="pcai-badge-ok">Active</span>
                    <?php else: ?>
                        <span class="pcai-badge-escalated">Pending</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?php echo admin_url('admin.php?page=pcai-knowledge&edit=' . $entry->id); ?>" class="button button-small">Edit</a>
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;" onsubmit="return confirm('Delete this entry?')">
                        <?php wp_nonce_field('pcai_kb_delete'); ?>
                        <input type="hidden" name="action" value="pcai_delete_kb">
                        <input type="hidden" name="kb_id" value="<?php echo esc_attr($entry->id); ?>">
                        <button type="submit" class="button button-small" style="color:#dc2626;">Del</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="pcai-hint" style="margin-top:12px">
        <strong>Tip:</strong> Entries with source "Learned" came from AI attempts to answer questions. Review and approve them to improve AI accuracy over time.
    </p>
</div>
