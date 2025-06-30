<?php
class AI_Content_Generator {

    protected $api_key;
    protected $default_prompt;

    public function __construct() {
        $settings = get_option('ai_content_generator_settings');
        $this->api_key = $settings['gemini_api_key'] ?? '';
        $this->default_prompt = $settings['default_prompt'] ?? '';
    }

    public function generate_content($topic) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'Gemini API key is not set');
        }

        $prompt = str_replace('[TOPIC]', $topic, $this->default_prompt);
       // $prompt = $this->default_prompt;
        
        // Make the actual API call to Gemini
        $response = $this->call_gemini_api($prompt);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return $this->format_gemini_response($response, $topic);
    }

    protected function call_gemini_api($prompt) {
    $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    $url = add_query_arg('key', $this->api_key, $api_url);
    
    $request_body = [
        'contents' => [
            'parts' => [
                ['text' => $prompt]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'topP' => 0.9,
            'topK' => 40,
            'maxOutputTokens' => 2048
        ],
        'safetySettings' => [
            [
                'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                'threshold' => 'BLOCK_ONLY_HIGH'
            ]
        ]
    ];
    
    $args = [
        'body' => json_encode($request_body),
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'timeout' => 30,
    ];
    
    $response = wp_remote_post($url, $args);
    
    // Handle HTTP errors
    if (is_wp_error($response)) {
        return new WP_Error('http_error', $response->get_error_message());
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    // Clean the raw response
    $cleaned_body = trim($body);
    $cleaned_body = preg_replace('/^```(?:json)?\s*/', '', $cleaned_body);
    $cleaned_body = preg_replace('/\s*```$/', '', $cleaned_body);
    
    // Decode JSON response
    $data = json_decode($cleaned_body, true);
    
    // Handle JSON decode errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_error', 'Invalid JSON response from API: ' . json_last_error_msg());
    }
    
    // Handle API errors
    if ($response_code >= 400) {
        $error_message = $data['error']['message'] ?? 'Unknown API error';
        return new WP_Error('api_error', "API Error ($response_code): $error_message");
    }
    
    // Validate response structure
    if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        error_log('Gemini API Unexpected Response: ' . print_r($data, true));
        return new WP_Error('invalid_response', 'Unexpected API response format');
    }
    
    // Check for safety filters
    if (isset($data['candidates'][0]['safetyRatings'])) {
        foreach ($data['candidates'][0]['safetyRatings'] as $rating) {
            if ($rating['probability'] === 'HIGH') {
                return new WP_Error('safety_filter', 'Content blocked by safety filters');
            }
        }
    }
    
    // Clean the generated text content
    $generated_text = $data['candidates'][0]['content']['parts'][0]['text'];
    $data['candidates'][0]['content']['parts'][0]['text'] = $this->clean_generated_text($generated_text);
    
    return $data;
}

protected function clean_generated_text($text) {
    // Remove unwanted markdown code blocks
    $text = preg_replace('/^```(?:html)?\s*/', '', $text);
    $text = preg_replace('/\s*```$/', '', $text);
    
    // Remove empty paragraphs and broken HTML
    $text = preg_replace('/<p>\s*<\/p>/i', '', $text);
    $text = preg_replace('/<div[^>]*>\s*<\/div>/i', '', $text);
    $text = preg_replace('/<\/div>\s*(?=<[^\/])/i', '', $text);
    
    // Balance HTML tags if function exists
    if (function_exists('balanceTags')) {
        $text = balanceTags($text, true);
    }
    
    // Convert markdown-style headers to HTML if needed
    $text = preg_replace('/^#\s+(.+)$/m', '<h1>$1</h1>', $text);
    $text = preg_replace('/^##\s+(.+)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^###\s+(.+)$/m', '<h3>$1</h3>', $text);
    
    // Ensure proper paragraph formatting
    $text = wpautop($text);
    
    return trim($text);
}

    protected function format_gemini_response($api_response, $topic) {
        $content = $api_response['candidates'][0]['content']['parts'][0]['text'];
        
        // Basic formatting (you can enhance this)
        $formatted_content = wpautop($content);
        
        // Generate SEO elements
        $meta_prompt = "Create a concise SEO meta title (under 60 chars) and description (under 160 chars) for a post about: $topic. Don't use ** for highlight.";
        $seo_response = $this->call_gemini_api($meta_prompt);
        
        $meta_title = "Generated Post: $topic";
        $meta_description = "A comprehensive article about $topic";
        
        if (!is_wp_error($seo_response)) {
            $seo_text = $seo_response['candidates'][0]['content']['parts'][0]['text'];
            if (preg_match('/Title:\s*(.+?)\s*Description:\s*(.+)/s', $seo_text, $matches)) {
                $meta_title = trim($matches[1]);
                $meta_description = trim($matches[2]);
            }
        }
        
        return [
            'title' => $this->extract_title($content) ?: "Generated Post About $topic",
            'content' => $formatted_content,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'excerpt'=>$meta_description,
        ];
    }

    protected function extract_title($content) {
        // Try to find the first heading
        if (preg_match('/<h[1-2][^>]*>(.+?)<\/h[1-2]>/i', $content, $matches)) {
            return strip_tags($matches[1]);
        }
        
        // Or the first sentence
        $first_paragraph = strip_tags($content);
        $first_sentence = preg_split('/([.!?]+)/', $first_paragraph, 2, PREG_SPLIT_DELIM_CAPTURE);
        if (!empty($first_sentence[0])) {
            return $first_sentence[0] . ($first_sentence[1] ?? '');
        }
        
        return null;
    }

    public function create_post($topic, $post_type = 'post') {
        $generated = $this->generate_content($topic);
        
        if (is_wp_error($generated)) {
            return $generated;
        }

        $post_data = array(
            'post_title' => $generated['title'],
            'post_content' => $generated['content'],
            'post_status' => 'publish', // Set to draft for review or 'publish' for auto-publish
            'post_type' => $post_type
        );

        $post_id = wp_insert_post($post_data);

        if ($post_id && !is_wp_error($post_id)) {
            // Set SEO meta if Yoast is active
            if (class_exists('WPSEO_Meta')) {
                update_post_meta($post_id, '_yoast_wpseo_title', $generated['meta_title']);
                update_post_meta($post_id, '_yoast_wpseo_metadesc', $generated['meta_description']);
            }
            
            return $post_id;
        }

        return false;
    }
}