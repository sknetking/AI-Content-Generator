<?php
// AJAX handler for manual generation
function ai_generate_manual_content() {
    check_ajax_referer('ai_content_generator_nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $topic = sanitize_text_field($_POST['topic']);
    
    if (empty($topic)) {
        wp_send_json_error('Topic is required');
    }
    
    $generator = new AI_Content_Generator();
    $generated = $generator->generate_content($topic);
    
    if (is_wp_error($generated)) {
        wp_send_json_error($generated->get_error_message());
    }
    
    wp_send_json_success([
        'title' => $generated['title'],
        'content' => $generated['content'],
        'meta_title' => $generated['meta_title'],
        'meta_description' => $generated['meta_description']
    ]);
}
add_action('wp_ajax_ai_generate_manual_content', 'ai_generate_manual_content');

// AJAX handler for inserting draft
function ai_insert_manual_content() {
    check_ajax_referer('ai_content_generator_nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $settings = get_option('ai_content_generator_settings');
    $post_type = $settings['post_type'] ?? 'post';
    
    $post_data = [
        'post_title' => sanitize_text_field($_POST['title']),
        'post_content' => wp_kses_post($_POST['content']),
        'post_status' => 'draft',
        'post_type' => $post_type
    ];
    
    $post_id = wp_insert_post($post_data);
    
    if ($post_id && !is_wp_error($post_id)) {
        // Set SEO meta if Yoast is active
        if (class_exists('WPSEO_Meta')) {
            update_post_meta($post_id, '_yoast_wpseo_title', sanitize_text_field($_POST['meta_title']));
            update_post_meta($post_id, '_yoast_wpseo_metadesc', sanitize_text_field($_POST['meta_description']));
        }
        
        wp_send_json_success([
            'post_id' => $post_id,
            'edit_link' => get_edit_post_link($post_id, 'url')
        ]);
    }
    
    wp_send_json_error('Failed to create post');
}
add_action('wp_ajax_ai_insert_manual_content', 'ai_insert_manual_content');