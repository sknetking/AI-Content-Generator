<?php
class AI_Content_Generator_Yoast_Integration {

    public function __construct() {
        if ($this->yoast_seo_active()) {
            add_filter('ai_content_generator_meta_fields', array($this, 'add_yoast_fields'));
        }
    }

    protected function yoast_seo_active() {
        return defined('WPSEO_VERSION');
    }

    public function add_yoast_fields($meta_fields) {
        $meta_fields['yoast_title'] = array(
            'label' => 'Yoast SEO Title',
            'type' => 'text',
            'default' => ''
        );

        $meta_fields['yoast_description'] = array(
            'label' => 'Yoast SEO Description',
            'type' => 'textarea',
            'default' => ''
        );

        return $meta_fields;
    }
}