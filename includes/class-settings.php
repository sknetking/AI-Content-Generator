<?php
// Add admin menu
add_action('admin_menu', 'gemini_chatbot_admin_menu');

function gemini_chatbot_admin_menu() {
    add_options_page(
        'Gemini Chatbot Settings',
        'Gemini Chatbot',
        'manage_options',
        'gemini-chatbot-settings',
        'gemini_chatbot_settings_page'
    );
}

// Register settings
add_action('admin_init', 'gemini_chatbot_register_settings');

function gemini_chatbot_register_settings() {
    register_setting('gemini_chatbot_settings_group', 'gemini_chatbot_settings', 'gemini_chatbot_sanitize_settings');
    
    add_settings_section(
        'gemini_chatbot_main_section',
        'Main Settings',
        'gemini_chatbot_main_section_cb',
        'gemini-chatbot-settings'
    );
    
    // API Key
    add_settings_field(
        'api_key',
        'Gemini API Key',
        'gemini_chatbot_api_key_cb',
        'gemini-chatbot-settings',
        'gemini_chatbot_main_section'
    );
    
    // Chatbot Name
    add_settings_field(
        'chatbot_name',
        'Chatbot Name',
        'gemini_chatbot_name_cb',
        'gemini-chatbot-settings',
        'gemini_chatbot_main_section'
    );
    
    // Chatbot Icon
    add_settings_field(
        'chatbot_icon',
        'Chatbot Icon',
        'gemini_chatbot_icon_cb',
        'gemini-chatbot-settings',
        'gemini_chatbot_main_section'
    );
    
    // Default Message
    add_settings_field(
        'default_message',
        'Default Welcome Message',
        'gemini_chatbot_default_message_cb',
        'gemini-chatbot-settings',
        'gemini_chatbot_main_section'
    );
    
    // Default Prompt
    add_settings_field(
        'default_prompt',
        'Default AI Prompt',
        'gemini_chatbot_default_prompt_cb',
        'gemini-chatbot-settings',
        'gemini_chatbot_main_section'
    );

    // Position
    add_settings_field(
        'position',
        'Chatbot Position',
        'gemini_chatbot_position_cb',
        'gemini-chatbot-settings',
        'gemini_chatbot_main_section'
    );
    
    // Content Types Selection
    add_settings_field(
        'included_content_types',
        'Include Content Types',
        'gemini_chatbot_content_types_cb',
        'gemini-chatbot-settings',
        'gemini_chatbot_main_section'
    );
    
    // Specific Content Selection
    add_settings_field(
        'included_specific_content',
        'Include Specific Content',
        'gemini_chatbot_specific_content_cb',
        'gemini-chatbot-settings',
        'gemini_chatbot_main_section'
    );
    
    // WooCommerce
    add_settings_field(
        'enable_woocommerce',
        'Enable WooCommerce Support',
        'gemini_chatbot_enable_woocommerce_cb',
        'gemini-chatbot-settings',
        'gemini_chatbot_main_section'
    );
   
    // Display Options
    add_settings_field(
        'auto_display',
        'Display Options',
        'gemini_chatbot_shortcode_cb',
        'gemini-chatbot-settings',
        'gemini_chatbot_main_section'
    );
}

function gemini_chatbot_sanitize_settings($input) {
    $sanitized = array();
    
    if (isset($input['api_key'])) {
        $sanitized['api_key'] = sanitize_text_field($input['api_key']);
    }
    
    if (isset($input['chatbot_name'])) {
        $sanitized['chatbot_name'] = sanitize_text_field($input['chatbot_name']);
    }
    
    if (isset($input['chatbot_icon'])) {
        $sanitized['chatbot_icon'] = sanitize_text_field($input['chatbot_icon']);
    }
    
    if (isset($input['default_message'])) {
        $sanitized['default_message'] = sanitize_textarea_field($input['default_message']);
    }
    
    if (isset($input['default_prompt'])) {
        $sanitized['default_prompt'] = sanitize_textarea_field($input['default_prompt']);
    }

    if (isset($input['position'])) {
        $sanitized['position'] = sanitize_text_field($input['position']);
    }
    
    // Content types sanitization
    if (isset($input['included_content_types'])) {
        $sanitized['included_content_types'] = array_map('sanitize_text_field', $input['included_content_types']);
    }
    
    // Specific content sanitization
    if (isset($input['included_specific_content'])) {
        $sanitized['included_specific_content'] = array_map('intval', $input['included_specific_content']);
    }
    
    if (isset($input['enable_woocommerce'])) {
        $sanitized['enable_woocommerce'] = (bool)$input['enable_woocommerce'];
    } 
    
    if (isset($input['auto_display'])) {
        $sanitized['auto_display'] = (bool)$input['auto_display'];
    }

    return $sanitized;
}

// Callback functions
function gemini_chatbot_main_section_cb() {
    echo '<p>Configure your Gemini AI Chatbot settings below.</p>';
}

function gemini_chatbot_api_key_cb() {
    $options = get_option('gemini_chatbot_settings');
    echo '<input type="password" id="api_key" name="gemini_chatbot_settings[api_key]" value="' . esc_attr($options['api_key']) . '" class="regular-text" />';
    echo '<p class="description">Enter your Google Gemini API key. <a href="https://ai.google.dev/" target="_blank">Get API key</a></p>';
}

function gemini_chatbot_name_cb() {
    $options = get_option('gemini_chatbot_settings');
    echo '<input type="text" id="chatbot_name" name="gemini_chatbot_settings[chatbot_name]" value="' . esc_attr($options['chatbot_name']) . '" class="regular-text" />';
}

function gemini_chatbot_icon_cb() {
    $options = get_option('gemini_chatbot_settings');
    echo '<input type="text" id="chatbot_icon" name="gemini_chatbot_settings[chatbot_icon]" value="' . esc_attr($options['chatbot_icon']) . '" class="regular-text" />';
    echo '<p class="description">Enter a Font Awesome class (e.g.,"fa-brands fa-rocketchat") or SVG code or any image url.</p>';
    echo '<p>See <a href="https://fontawesome.com/icons" target="_blank">Font Awesome icons</a></p>';
}

function gemini_chatbot_default_message_cb() {
    $options = get_option('gemini_chatbot_settings');
    echo '<textarea id="default_message" name="gemini_chatbot_settings[default_message]" class="large-text">' . esc_textarea($options['default_message']) . '</textarea>';
}

function gemini_chatbot_default_prompt_cb() {
$options = get_option('gemini_chatbot_settings');
$default_prompt = !empty($options['default_prompt']) ? $options['default_prompt'] : 
"You are {$options['chatbot_name']}, a polite, friendly, and knowledgeable AI assistant for this WordPress website.  
Always respond as if you're speaking directly to a user — not an admin. Be warm, helpful, and respectful.  
Base your answers strictly on the provided context. If the context is missing or incomplete, reply gracefully and let the user know you cannot assist at the moment.  
Kindly respond to greetings like 'hi' or 'hello' to make users feel welcome.  
You may use HTML for formatting responses:
- Wrap each response paragraph in a <p> tag.  
- You may add inline CSS styles inside <p> if needed.  
- For links to pages or posts, use <a href=\"".site_url('/')."post_or_page_slug\">link text</a>.
-Always use a tag with link when you give referece from this site.
";    
    echo '<textarea id="default_prompt" name="gemini_chatbot_settings[default_prompt]" rows="4" class="large-text">' . esc_textarea($default_prompt) . '</textarea>';
    echo '<p class="description">This prompt will be prefixed to all user queries to guide the AI\'s responses.</p>';
}

function gemini_chatbot_position_cb() {
    $options = get_option('gemini_chatbot_settings');
    $positions = array(
        'bottom-right' => 'Bottom Right',
        'bottom-left' => 'Bottom Left',
        'top-right' => 'Top Right',
        'top-left' => 'Top Left'
    );
    
    echo '<select id="position" name="gemini_chatbot_settings[position]">';
    foreach ($positions as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($options['position'], $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}

function gemini_chatbot_content_types_cb() {
    $options = get_option('gemini_chatbot_settings');
    $selected_types = isset($options['included_content_types']) ? $options['included_content_types'] : array();
    
    // Get all public post types except WooCommerce and attachments
    $post_types = get_post_types(array(
        'public' => true,
        '_builtin' => false
    ), 'objects');
    
    // Remove unwanted post types
    unset($post_types['attachment']);
    if (class_exists('WooCommerce')) {
        unset($post_types['product']);
    }
    
    // Include built-in posts and pages
    $post_types['post'] = get_post_type_object('post');
    $post_types['page'] = get_post_type_object('page');
    
    echo '<select name="gemini_chatbot_settings[included_content_types][]" multiple="multiple" style="width:100%;min-height:150px;">';
	echo "<option>Select Post Type</option>";
    foreach ($post_types as $type) {
        $selected = in_array($type->name, $selected_types) ? 'selected="selected"' : '';
        echo '<option value="' . esc_attr($type->name) . '" ' . $selected . '>' . esc_html($type->label) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Hold CTRL/CMD to select multiple content types</p>';
}

function gemini_chatbot_specific_content_cb() {
    $options = get_option('gemini_chatbot_settings');
    $selected_content = isset($options['included_specific_content']) ? $options['included_specific_content'] : array();
    
    $args = array(
        'post_type' => 'any',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC'
    );
    
    $all_content = get_posts($args);
    
    echo '<select name="gemini_chatbot_settings[included_specific_content][]" multiple="multiple" style="width:100%;min-height:200px;">';
	echo "<option>Select Post/Page</option>";
    foreach ($all_content as $item) {
        $post_type_obj = get_post_type_object($item->post_type);
        $selected = in_array($item->ID, $selected_content) ? 'selected="selected"' : '';
        echo '<option value="' . esc_attr($item->ID) . '" ' . $selected . '>';
        echo esc_html($item->post_title) . ' (' . $post_type_obj->labels->singular_name . ')';
        echo '</option>';
    }
    echo '</select>';
    echo '<p class="description">Hold CTRL/CMD to select specific pages/posts to include</p>';
}

function gemini_chatbot_enable_woocommerce_cb() {
    $options = get_option('gemini_chatbot_settings');
    $enabled = isset($options['enable_woocommerce']) ? $options['enable_woocommerce'] : false;
    echo '<input type="checkbox" id="enable_woocommerce" name="gemini_chatbot_settings[enable_woocommerce]" value="1" ' . checked(1, $enabled, false) . ' />';
    echo '<label for="enable_woocommerce">Enable WooCommerce product support</label>';
}

function gemini_chatbot_shortcode_cb() {
    $options = get_option('gemini_chatbot_settings');
    $auto_display = isset($options['auto_display']) ? $options['auto_display'] : true;
    
    echo '<code>[gemini_chatbot]</code>';
    echo '<p class="description">Use this shortcode to display the chatbot in specific locations.</p>';
    
    echo '<br><br>';
    echo '<input type="checkbox" id="auto_display" name="gemini_chatbot_settings[auto_display]" value="1" ' . checked(1, $auto_display, false) . ' />';
    echo '<label for="auto_display">Automatically display chatbot in footer</label>';
}

// Settings page content
function gemini_chatbot_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <form action="options.php" method="post">
            <?php
            settings_fields('gemini_chatbot_settings_group');
            do_settings_sections('gemini-chatbot-settings');
            submit_button('Save Settings');
            ?>
        </form>
        
        <div class="gemini-chatbot-preview">
            <h2>Preview</h2>
            <div id="gemini-chatbot-preview-container"></div>
        </div>
    </div>
    <?php
}
