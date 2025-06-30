<?php
class AI_Content_Generator_Settings {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function add_admin_menu() {
          add_menu_page(
            'AI Content Generator',
            'AI Content Generator',
            'manage_options',
            'ai-content-generator',
            array($this, 'settings_page'),
            'dashicons-edit-page',
            25
        );

        // Optional: Add identical submenu to avoid duplicate link
        add_submenu_page(
            'ai-content-generator',
            'AI Content Generator',
            'Generator',
            'manage_options',
            'ai-content-generator',
            array($this, 'settings_page')
        );
    }

    public function settings_init() {
        register_setting('ai_content_generator', 'ai_content_generator_settings');

        add_settings_section(
            'ai_content_generator_section',
            'API & General Settings',
            array($this, 'settings_section_callback'),
            'ai_content_generator'
        );

        add_settings_field(
            'gemini_api_key',
            'Gemini API Key',
            array($this, 'api_key_render'),
            'ai_content_generator',
            'ai_content_generator_section'
        );

        add_settings_field(
            'default_prompt',
            'Default Prompt',
            array($this, 'default_prompt_render'),
            'ai_content_generator',
            'ai_content_generator_section'
        );

        add_settings_field(
            'post_frequency',
            'Post Frequency',
            array($this, 'post_frequency_render'),
            'ai_content_generator',
            'ai_content_generator_section'
        );

        add_settings_field(
            'post_type',
            'Post Type',
            array($this, 'post_type_render'),
            'ai_content_generator',
            'ai_content_generator_section'
        );
    }

    public function api_key_render() {
        $options = get_option('ai_content_generator_settings');
        ?>
        <input type="password" name="ai_content_generator_settings[gemini_api_key]" value="<?php echo esc_attr($options['gemini_api_key'] ?? ''); ?>" class="regular-text">
        <p class="description">Enter your Google Gemini API key</p>
        <?php
    }

    public function default_prompt_render() {
        $options = get_option('ai_content_generator_settings');
        $default_prompt = "Write a comprehensive, SEO-optimized blog post about [TOPIC].
        Include:
        - Engaging introduction
        - 3-5 sections with H2 headings
        - Bullet points where appropriate
        - Conclusion with key takeaways
        Format as proper HTML with paragraphs and headings.
        Use a friendly, professional tone suitable for a general audience.";
        ?>
        <textarea name="ai_content_generator_settings[default_prompt]" rows="5" cols="50" class="large-text"><?php echo esc_textarea($options['default_prompt'] ?? $default_prompt); ?></textarea>
        <p class="description">Default prompt for content generation. Use [TOPIC]= You post title, like - [How ai is working?].</p>
        <?php
    }

    public function post_frequency_render() {
        $options = get_option('ai_content_generator_settings');
        $frequency = $options['post_frequency'] ?? 'weekly';
        ?>
        <select name="ai_content_generator_settings[post_frequency]">
            <option value="daily" <?php selected($frequency, 'daily'); ?>>Daily</option>
            <option value="weekly" <?php selected($frequency, 'weekly'); ?>>Weekly</option>
            <option value="monthly" <?php selected($frequency, 'monthly'); ?>>Monthly</option>
        </select>
        <?php
    }

    public function post_type_render() {
        $options = get_option('ai_content_generator_settings');
        $post_type = $options['post_type'] ?? 'post';
        $post_types = get_post_types(array('public' => true), 'objects');
        ?>
        <select name="ai_content_generator_settings[post_type]">
            <?php foreach ($post_types as $type): ?>
                <option value="<?php echo esc_attr($type->name); ?>" <?php selected($post_type, $type->name); ?>>
                    <?php echo esc_html($type->labels->singular_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public function settings_section_callback() {
        echo '<p>Configure your AI content generation settings below.</p>';
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>AI Content Generator Settings</h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('ai_content_generator');
                do_settings_sections('ai_content_generator');
                submit_button();
                ?>
                   <div class="postbox" style="margin-top: 20px; padding: 20px;">
                        <h2>Manual Content Generation</h2>
                        <p>Test the AI content generation with a custom topic:</p>
                    
                        <div>
                            <input type='text' id="manual_topic" placeholder="Enter topic..." class="">
                            <button id="manual_generate" class="button button-primary">Generate Content</button>
                            <span id="generation_result" style="margin-left:10px;"></span>
                        </div>
                        
                        <div id="generated_content_preview" style="margin-top:15px; display:none;">
                            <h3>Preview:</h3>
                            <div id="content_preview" style="border:1px solid #ddd; padding:15px; background:#fff;"></div>
                            <button id="insert_as_draft" class="button" style="margin-top:10px;">Insert as Draft</button>
                        </div>
                  </div>
            </form>
        </div>
        <?php
    }

    public function enqueue_admin_assets($hook) {
       
        wp_enqueue_style('ai-content-generator-admin',AI_CONTENT_GENERATOR_URL . 'assets/css/admin.css');

        wp_enqueue_script(
            'ai-content-generator-admin',
            AI_CONTENT_GENERATOR_URL . 'assets/js/admin.js',
            array('jquery'),
            AI_CONTENT_GENERATOR_VERSION,
            true
        );
         wp_localize_script('ai-content-generator-admin', 'aiContentGenerator', [
        'nonce' => wp_create_nonce('ai_content_generator_nonce')
         ]);
    }
}
