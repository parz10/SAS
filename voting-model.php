<?php

namespace Helpie\Features\Components\Voting;

use \Helpie\Includes\Translations as Translations;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Helpie\Features\Components\Voting\Voting_Model')) {
    class Voting_Model
    {

        public function __construct()
        {
            $this->settings = new \Helpie\Includes\Settings\Getters\Settings();
        }

        public function get_viewProps($args = array())
        {

            // First Layer: Fill with defaults
            $viewProps = $this->get_default_args();

            if (isset($args) && is_array($args)) {
                $viewProps = array_merge($viewProps, $args);
            }

            return $viewProps;
        }

        public function get_default_args()
        {
            $args = $this->get_manual_default_args();

            // Second Layer: Helpie Settings Values
            $view_settings = $this->get_settings();

            $args = array_merge($args, $view_settings);

            return $args;
        }

        public function get_settings()
        {
            $settings = array(
                'label' => $this->settings->single_page->get_voting_label(),
                'voting_template' => $this->settings->single_page->get_voting_template(),

            );

            return $settings;
        }

        public function get_manual_default_args()
        {
            $args = array();

            // Get Default Values from GET - FIELDS
            $fields = $this->get_fields();
            foreach ($fields as $key => $field) {
                $args[$key] = $field['default'];
            }

            return $args;
        }

        public function get_fields()
        {
            $fields = array(
                'label' => $this->get_label_field(),
                'voting_template' => $this->get_voting_template_field(),
            );

            return $fields;
        }

        // FIELDS
        protected function get_label_field()
        {
            return array(
                'name' => 'label',
                'label' => Translations::get('label'),
                'default' => 'How did you like this article?',
                'type' => 'text',
            );
        }

        protected function get_voting_template_field()
        {
            return array(
                'name' => 'voting_template',
                'label' => Translations::get('voting_system'),
                'default' => 'emotion',
                'options' => array(
                    'classic' => Translations::get('classic'),
                    'emotion' => Translations::get('emotion'),
                ),
                'type' => 'select',
            );
        }

        public function get_style_config()
        {
            $style_config = array(

                'element' => array(
                    'name' => 'helpie_element',
                    'selector' => '.pauple-helpie-module.article-voting',
                    'label' => __('Voting Container', 'elementor'),
                    'styleProps' => array('text-align', 'background', 'border', 'border_radius', 'padding', 'margin'),
                    'children' => array(
                        'label' => array(
                            'name' => 'helpie_element_label',
                            'selector' => '.pauple-helpie-module.article-voting .label',
                            'label' => Translations::get('label'),
                            'styleProps' => array('color', 'typography', 'text-align'),
                        ),
                        'icon' => array(
                            'name' => 'helpie_element_icon',
                            'selector' => '.pauple-helpie-module.article-voting .voting-icon i.icon',
                            'label' => Translations::get('voting_icon'),
                            'styleProps' => array('color', 'text-align'),
                        ),
                        'count' => array(
                            'name' => 'helpie_element_count',
                            'selector' => '.pauple-helpie-module.article-voting count',
                            'label' => Translations::get('single_item_count'),
                            'styleProps' => array('color', 'typography', 'text-align'),
                        ),

                    ),
                ),
            );

            return $style_config;
        }
    } // END CLASS
}
