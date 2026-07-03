<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap pcai-wrap">
    <h1 class="pcai-page-title">
        <span class="dashicons dashicons-admin-settings"></span>
        PrintCraft AI — Settings
    </h1>

    <form method="post" action="options.php">
        <?php settings_fields('pcai_settings'); ?>

        <div class="pcai-panel">
            <h2>AI Configuration</h2>
            <table class="form-table">
                <tr>
                    <th><label for="pcai_openai_api_key">OpenAI API Key <span style="color:#dc2626">*</span></label></th>
                    <td>
                        <input type="password" id="pcai_openai_api_key" name="pcai_openai_api_key"
                               value="<?php echo esc_attr( get_option('pcai_openai_api_key') ); ?>"
                               class="regular-text" autocomplete="new-password">
                        <p class="description">
                            Get your key from <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com/api-keys</a>.
                            This is required for the AI assistant to work.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><label for="pcai_model">AI Model</label></th>
                    <td>
                        <select id="pcai_model" name="pcai_model">
                            <option value="gpt-4o-mini" <?php selected(get_option('pcai_model','gpt-4o-mini'), 'gpt-4o-mini'); ?>>GPT-4o Mini (Recommended — fast & affordable)</option>
                            <option value="gpt-4o" <?php selected(get_option('pcai_model','gpt-4o-mini'), 'gpt-4o'); ?>>GPT-4o (More capable, higher cost)</option>
                            <option value="gpt-3.5-turbo" <?php selected(get_option('pcai_model','gpt-4o-mini'), 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo (Budget option)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="pcai_tone">AI Tone / Personality</label></th>
                    <td>
                        <input type="text" id="pcai_tone" name="pcai_tone"
                               value="<?php echo esc_attr( get_option('pcai_tone','friendly and professional') ); ?>"
                               class="regular-text"
                               placeholder="e.g. friendly and professional">
                        <p class="description">Describe how the AI should communicate. Examples: "warm and helpful", "professional and concise".</p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="pcai-panel">
            <h2>Chat Widget</h2>
            <table class="form-table">
                <tr>
                    <th>Widget Enabled</th>
                    <td>
                        <label>
                            <input type="checkbox" name="pcai_enabled" value="1" <?php checked(get_option('pcai_enabled','1'), '1'); ?>>
                            Show chat widget on the website
                        </label>
                    </td>
                </tr>
                <tr>
                    <th><label for="pcai_bot_name">Bot Name</label></th>
                    <td>
                        <input type="text" id="pcai_bot_name" name="pcai_bot_name"
                               value="<?php echo esc_attr( get_option('pcai_bot_name','Craft') ); ?>"
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="pcai_greeting">Opening Greeting</label></th>
                    <td>
                        <textarea id="pcai_greeting" name="pcai_greeting" rows="3" class="large-text"><?php echo esc_textarea( get_option('pcai_greeting', "Hi there! 👋 I'm Craft, your Print Craft Creations assistant. How can I help you today?") ); ?></textarea>
                        <p class="description">The first message customers see when they open the chat.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="pcai_theme_color">Widget Color</label></th>
                    <td>
                        <input type="color" id="pcai_theme_color" name="pcai_theme_color"
                               value="<?php echo esc_attr( get_option('pcai_theme_color','#2563eb') ); ?>">
                        <p class="description">Choose a color that matches your brand.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="pcai_business_name">Business Name</label></th>
                    <td>
                        <input type="text" id="pcai_business_name" name="pcai_business_name"
                               value="<?php echo esc_attr( get_option('pcai_business_name','Print Craft Creations') ); ?>"
                               class="regular-text">
                    </td>
                </tr>
            </table>
        </div>

        <div class="pcai-panel">
            <h2>GitHub Auto-Updates</h2>
            <p style="color:#64748b;margin-top:0">Once configured, WordPress will automatically check your GitHub repo for new releases and show "Update Available" in your Plugins list — just like any other plugin.</p>
            <table class="form-table">
                <tr>
                    <th><label for="pcai_github_user">GitHub Username / Org</label></th>
                    <td>
                        <input type="text" id="pcai_github_user" name="pcai_github_user"
                               value="<?php echo esc_attr( get_option('pcai_github_user', '') ); ?>"
                               class="regular-text" placeholder="e.g. printcraftcreations">
                        <p class="description">Your GitHub username or organization name.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="pcai_github_repo">GitHub Repository Name</label></th>
                    <td>
                        <input type="text" id="pcai_github_repo" name="pcai_github_repo"
                               value="<?php echo esc_attr( get_option('pcai_github_repo', '') ); ?>"
                               class="regular-text" placeholder="e.g. printcraft-ai-assistant">
                        <p class="description">The exact name of your GitHub repository.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="pcai_github_token">GitHub Personal Access Token <span style="font-weight:400;color:#64748b">(optional)</span></label></th>
                    <td>
                        <input type="password" id="pcai_github_token" name="pcai_github_token"
                               value="<?php echo esc_attr( get_option('pcai_github_token', '') ); ?>"
                               class="regular-text" autocomplete="new-password">
                        <p class="description">Only needed if your repository is <strong>private</strong>. Leave blank for public repos.
                        Create one at <a href="https://github.com/settings/tokens/new?scopes=repo&description=PrintCraft+WP+Updater" target="_blank">github.com/settings/tokens</a> with <code>repo</code> scope.</p>
                    </td>
                </tr>
                <tr>
                    <th>Update Status</th>
                    <td>
                        <?php
                        $gh_user = get_option('pcai_github_user','');
                        $gh_repo = get_option('pcai_github_repo','');
                        if ( $gh_user && $gh_repo ):
                        ?>
                            <span style="color:#16a34a">✓ Connected to <a href="https://github.com/<?php echo esc_attr($gh_user.'/'. $gh_repo); ?>" target="_blank">github.com/<?php echo esc_html($gh_user.'/'.$gh_repo); ?></a></span>
                            &nbsp;
                            <a href="<?php echo wp_nonce_url( admin_url('admin.php?page=pcai-settings&pcai_clear_cache=1'), 'pcai_clear_cache' ); ?>" class="button button-small">Force Check for Updates</a>
                        <?php else: ?>
                            <span style="color:#94a3b8">Not configured — enter your GitHub details above to enable auto-updates.</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <div class="pcai-github-howto">
                <strong>How to set this up (one time):</strong>
                <ol>
                    <li>Create a repository on <a href="https://github.com/new" target="_blank">github.com/new</a> — name it <code>printcraft-ai-assistant</code></li>
                    <li>Upload the plugin folder contents to that repo (or use Git to push)</li>
                    <li>To release a new version: bump <code>PCAI_VERSION</code> in the main PHP file, create a GitHub Release tagged <code>v1.1.0</code>, and attach <code>printcraft-ai-assistant.zip</code> as an asset</li>
                    <li>WordPress will detect the new release within 12 hours (or click "Force Check" above)</li>
                </ol>
            </div>
        </div>

        <div class="pcai-panel">
            <h2>Escalation & Support</h2>
            <table class="form-table">
                <tr>
                    <th><label for="pcai_support_email">Support Email</label></th>
                    <td>
                        <input type="email" id="pcai_support_email" name="pcai_support_email"
                               value="<?php echo esc_attr( get_option('pcai_support_email', get_option('admin_email')) ); ?>"
                               class="regular-text">
                        <p class="description">Email address that receives escalation notifications when the AI can't answer a question.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="pcai_escalation_cc">CC Email (optional)</label></th>
                    <td>
                        <input type="email" id="pcai_escalation_cc" name="pcai_escalation_cc"
                               value="<?php echo esc_attr( get_option('pcai_escalation_cc','') ); ?>"
                               class="regular-text"
                               placeholder="team@example.com">
                        <p class="description">Optional second email to CC on escalation notifications.</p>
                    </td>
                </tr>
            </table>
        </div>

        <p class="submit">
            <?php submit_button('Save Settings', 'primary', 'submit', false); ?>
        </p>
    </form>
</div>
