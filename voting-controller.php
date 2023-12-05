<?php

namespace Helpie\Features\Components\Voting;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Helpie\Features\Components\Voting\Voting_Controller')) {
    class Voting_Controller
    {

        private $post_id;
        private $user_id;
        private $user_meta_key;

        public function __construct()
        {
            $this->post_id = get_the_ID();
            $this->user_id = get_current_user_id();
            $this->user_meta_key = 'helpie_article_votes';
            $this->settings = new \Helpie\Includes\Settings\Getters\Settings();
            $this->model = new \Helpie\Features\Components\Voting\Voting_Model();
        }

        public function get_view($args = array())
        {
            $viewProps = $this->model->get_viewProps($args);

            // error_log('$viewProps : ' . print_r($viewProps, true));
            return $this->get_html($viewProps);
        }

        // public function render()
        // {
        //     echo $this->get_html();
        // }

        public function get_html($viewProps)
        {
            $html = '';

            $voting_template = $viewProps['voting_template'];
            $label = $viewProps['label'];
            $helpie_voting_access = $this->settings->single_page->allow_visitors_to_vote();

            // Return if current_user cannot vote
            if ($helpie_voting_access == false && !is_user_logged_in()) {
                return '';
            }

            // Get correct template
            if ($voting_template != 'none') {
                $html .= $this->get_vote_system($voting_template, $label);
            }

            return $html;
        }

        private function get_vote_system($template, $label)
        {

            // error_log('$template : ' . $template);
            $post_id = $this->post_id;
            $ph_previous_vote = $this->get_previous_vote();

            $html_content = "<div class='pauple-helpie-module article-voting'>";
            $html_content .= "<span class='label'>" . $label . "</span>";
            $html_content .= "<span data-post-id='" . get_the_ID() . "' data-user-id='" . get_current_user_id() . "' class='icon-tray'>";

            $emotions = $this->get_vote_options($template);
            foreach ($emotions as $key => $value) {
                $ph_active_cls = '';
                if ($ph_previous_vote == $value) {
                    $ph_active_cls = 'selected';
                }

                $count = $this->get_option_count($post_id, $value);

                $html_content .= "<span  data-vote='" . $value . "' class=' voting-icon " . $value . ' ' . $ph_active_cls . "'>";
                $html_content .= "<i data-content='" . $value . "' class='" . $value . " outline icon' aria-hidden='true'></i>";
                $html_content .= "<count>" . $count . "</count>";
                $html_content .= "</span>";
            }

            $html_content .= "</span></div><div style='clear:both;'></div>";
            return $html_content;
        }

        private function get_previous_vote()
        {
            $ph_vote_handler = new \Helpie\Features\Components\Voting\Article_Vote_Handler($this->post_id, $this->user_id);
            $ph_previous_vote = '';

            if (is_user_logged_in()) {

                if ($ph_vote_handler->user_vote_handler->get_user_previous_vote($this->post_id, $this->user_id, $this->user_meta_key) != null) {
                    $ph_previous_vote = $ph_vote_handler->user_vote_handler->get_user_previous_vote($this->post_id, $this->user_id, $this->user_meta_key);
                }
            } else {
                if ($ph_vote_handler->non_user_vote_handler->get_non_user_previous_vote($this->post_id, $this->user_meta_key) != null) {
                    $ph_previous_vote = $ph_vote_handler->non_user_vote_handler->get_non_user_previous_vote($this->post_id, $this->user_meta_key);
                }
            }

            return $ph_previous_vote;
        }

        private function get_vote_options($template)
        {

            $classic = array(
                0 => 'thumbs up outline',
                1 => 'thumbs down outline',
            );

            $emotions = array(
                0 => 'frown',
                1 => 'meh',
                2 => 'smile',
                3 => 'heart',
            );

            if ($template == 'classic') {
                return $classic;
            }

            return $emotions;
        }

        private function get_option_count($post_id, $value)
        {
            $post_meta_key = 'helpie_vote_' . $value . '_count';
            $count = 0;
            if (get_post_meta($post_id, $post_meta_key, true) != null) {
                $count = get_post_meta($post_id, $post_meta_key, true);
            }

            return $count;
        }
    } // END CLASS

}
