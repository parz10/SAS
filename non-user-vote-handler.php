<?php

namespace Helpie\Features\Components\Voting;

if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('\Helpie\Features\Components\Voting\Non_User_Vote_Handler')) {

    class Non_User_Vote_Handler
    {

        public function get_non_user_vote_array($user_meta_key)
        {
            if (isset($_COOKIE[$user_meta_key]) && !empty($_COOKIE[$user_meta_key])) {
                $user_vote_array = $_COOKIE[$user_meta_key];
                $user_vote_array = stripslashes($user_vote_array);
                $user_vote_array = json_decode($user_vote_array, true);

                return $user_vote_array;
            } else {
                return false;
            }
        }

        public function get_non_user_previous_vote($post_id, $user_meta_key)
        {
            $previous_vote = '';
            $user_vote_array = $this->get_non_user_vote_array($user_meta_key);

            if (isset($user_vote_array) && is_array($user_vote_array)) {
                foreach ($user_vote_array as $key => $value) {
                    if ((int) $key == (int) $post_id) {
                        $previous_vote = $value;
                    }
                }
            }

            return $previous_vote;
        }

        public function update_previous_vote_non_user($post_id, $user_meta_key)
        {
            if ($this->has_user_voted_already($post_id, $user_meta_key)) {
                $previous_vote = $this->get_non_user_previous_vote($post_id, $user_meta_key);
                $previous_post_meta_key = 'helpie_vote_' . $previous_vote . '_count';
                $previous_count = abs(intval(get_post_meta($post_id, $previous_post_meta_key, true)));
                $new_count = (int) $previous_count - 1;

                update_post_meta($post_id, $previous_post_meta_key, $new_count);
                $this->user_previous_vote = $previous_vote;
            }
        }

        public function has_user_voted_already($post_id, $user_meta_key)
        {
            $has_voted = false;

            $user_vote_array = $this->get_non_user_vote_array($user_meta_key);

            if (isset($user_vote_array) && is_array($user_vote_array)) {
                foreach ($user_vote_array as $key => $value) {
                    if ($key == $post_id) {
                        $has_voted = true;
                    }
                }
            }

            return $has_voted;
        }

        public function update_non_user_vote($user_vote_array, $post_id, $vote, $user_meta_key)
        {
            $new_array = $user_vote_array;

            $new_array[$post_id] = $vote;
            $cookie_name = $user_meta_key;
            $cookie_value = json_encode($new_array);
            setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");

        }

    } // END CLASS

}