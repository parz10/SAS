<?php

namespace Helpie\Features\Components\Voting;

if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('\Helpie\Features\Components\Voting\Article_Vote_Handler')) {

    class Article_Vote_Handler
    {
        private $vote;
        private $post_id;
        private $user_id;
        private $user_meta_key;
        private $post_meta_key;
        private $user_previous_vote;

        public $user_vote_handler;
        public $non_user_vote_handler;

        public function __construct($post_id, $user_id)
        {

            $this->non_user_vote_handler = new \Helpie\Features\Components\Voting\Non_User_Vote_Handler();
            $this->user_vote_handler = new \Helpie\Features\Components\Voting\User_Vote_Handler();
            $this->post_id = $post_id;
            $this->user_id = $user_id;
            $this->user_meta_key = 'helpie_article_votes';
            if (is_user_logged_in()) {
                $this->user_vote_array = $this->user_vote_handler->get_user_vote_array($this->user_id, $this->user_meta_key);
            } else {
                $this->user_vote_array = $this->non_user_vote_handler->get_non_user_vote_array($this->user_meta_key);
            }
        }

        public function cast_vote($vote_value)
        {
            $this->vote = $vote_value;
            $this->post_meta_key = $this->get_post_meta_key();
        }

        public function get_post_meta_key()
        {
            return 'helpie_vote_' . $this->vote . '_count';
        }

        public function update_user_vote()
        {
            $new_array = $this->user_vote_array;

            if (!isset($new_array) || !is_array($new_array)) {
                $new_array = array();
            }

            $new_array[$this->post_id] = $this->vote;
            update_user_meta($this->user_id, $this->user_meta_key, $new_array);
        }

        public function get_user_vote()
        {
            $vote = '';
            $user_vote_array = $this->user_vote_handler->get_user_vote_array($this->user_id, $this->user_meta_key);

            if ($this->user_vote_handler->has_user_voted_already($this->post_id, $this->user_id, $this->user_meta_key)) {
                foreach ($user_vote_array as $key => $value) {
                    if ($key == $this->post_id) {
                        $vote = $value;
                    }
                }
            }

            return $vote;
        }

        public function update_new_vote($post_id, $post_meta_key)
        {
            /* New vote */
            $previous_count = 0;
            if (null != get_post_meta($post_id, $post_meta_key, true)) {
                $previous_count = abs(intval(get_post_meta($post_id, $post_meta_key, true)));
            }

            $new_count = (int) $previous_count + 1;
            update_post_meta($post_id, $post_meta_key, $new_count);
        }

        public function update_vote()
        {

            // echo "helpie_voting_access: " . $helpie_voting_access;
            if (is_user_logged_in()) {
                if ($this->user_vote_handler->has_user_voted_already($this->post_id, $this->user_id, $this->user_meta_key)) {
                    $this->user_vote_handler->update_previous_vote($this->post_id, $this->user_id, $this->user_meta_key);
                    $this->update_new_vote($this->post_id, $this->post_meta_key);
                } else {
                    $this->update_new_vote($this->post_id, $this->post_meta_key);
                }

                $this->update_user_vote();
            } else {
                if ($this->non_user_vote_handler->has_user_voted_already($this->post_id, $this->user_meta_key)) {
                    $this->non_user_vote_handler->update_previous_vote_non_user($this->post_id, $this->user_meta_key);
                    $this->update_new_vote($this->post_id, $this->post_meta_key);
                } else {
                    $this->update_new_vote($this->post_id, $this->post_meta_key);
                }

                $this->non_user_vote_handler->update_non_user_vote($this->user_vote_array, $this->post_id, $this->vote, $this->user_meta_key);
            }
        }
    } // END CLASS

}