<?php
class AI_Content_Generator_Cron {

    public static function activate() {
        if (!wp_next_scheduled('ai_content_generator_daily_event')) {
            wp_schedule_event(time(), 'daily', 'ai_content_generator_daily_event');
        }
    }

    public static function deactivate() {
        wp_clear_scheduled_hook('ai_content_generator_daily_event');
    }

    public function __construct() {
        add_action('ai_content_generator_daily_event', array($this, 'generate_scheduled_content'));
        add_filter('cron_schedules', array($this, 'add_custom_schedules'));
    }

    public function add_custom_schedules($schedules) {
        $settings = get_option('ai_content_generator_settings');
        $frequency = $settings['post_frequency'] ?? 'weekly';

        if (!isset($schedules['ai_weekly'])) {
            $schedules['ai_weekly'] = array(
                'interval' => 604800, // 1 week in seconds
                'display' => __('Once Weekly')
            );
        }

        if (!isset($schedules['ai_monthly'])) {
            $schedules['ai_monthly'] = array(
                'interval' => 2635200, // 1 month in seconds
                'display' => __('Once Monthly')
            );
        }

        return $schedules;
    }

    public function generate_scheduled_content() {
        $settings = get_option('ai_content_generator_settings');
        $frequency = $settings['post_frequency'] ?? 'weekly';
        $post_type = $settings['post_type'] ?? 'post';

        // In a real implementation, you would fetch trending topics
        $topic = $this->get_trending_topic();
        
        $generator = new AI_Content_Generator();
        $result = $generator->create_post($topic, $post_type);

        if (is_wp_error($result)) {
            error_log('AI Content Generator Error: ' . $result->get_error_message());
        }
    }

    protected function get_trending_topic() {
        // Placeholder - in a real implementation, you would fetch from Google Trends or another API
        $topics = array(
            'Artificial Intelligence in WordPress',
            'Latest SEO Trends',
            'Content Marketing Strategies',
            'Google Algorithm Updates',
            'AI Writing Tools Comparison'
        );
        
        return $topics[array_rand($topics)];
    }
}