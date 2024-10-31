<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 *
 *  @version       1.0.0
 *  @author        impleCode
 *
 */

class ic_reviews_limit {
	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		$reviews_settings = get_ic_reviews_sep_settings();
		if ( empty( $reviews_settings['block_multiple_reviews'] ) ) {
			return;
		}
		add_filter( 'pre_comment_approved', array( $this, 'check_review' ), 10, 2 );
		add_filter( 'duplicate_comment_id', array( $this, 'check_duplicate' ), 10, 2 );
		add_filter( 'ic_review_default_text', array( $this, 'review_text' ), 10, 2 );
		add_filter( 'ic_review_default_title', array( $this, 'review_title' ), 10, 2 );
		add_filter( 'ic_review_form_id', array( $this, 'review_id' ), 10, 2 );
		add_action( 'ic_reviews_form_before', array( $this, 'update_message' ) );
	}

	function update_message() {
		if ( ! empty( $_GET['review_updated'] ) ) {
			?>
            <div id="ic-review-update-message" class="ic-review-update-message">
				<?php
				if ( $_GET['review_updated'] === 'error' ) {
					implecode_warning( __( 'You are not allowed to update reviews.', 'reviews-plus' ) );
				} else {
					implecode_success( __( 'Your review has been updated.', 'reviews-plus' ) );
				}
				?>
            </div>
			<?php
		}
	}

	function check_duplicate( $dup_id, $comment_data ) {
		if ( empty( $comment_data['comment_type'] ) || ! ic_string_contains( $comment_data['comment_type'], 'ic_rev' ) ) {
			return $dup_id;
		}
		if ( ! empty( $comment_data['comment_parent'] ) ) {
			return $dup_id;
		}

		if ( isset( $_POST['ic_review_rating'] ) ) {
			$saved_rating = ic_get_ic_rev_rating( $dup_id );
			if ( intval( $saved_rating ) !== intval( $_POST['ic_review_rating'] ) ) {

				return 0;
			}
		}
		if ( isset( $_POST['ic_review_title'] ) ) {
			$saved_title = ic_get_ic_rev_title( $dup_id );
			if ( $saved_title !== $_POST['ic_review_title'] ) {

				return 0;
			}
		}

		return $dup_id;
	}

	function review_text( $default, $post_id = null ) {
		$review_id = $this->review_id( 0, $post_id );
		if ( ! empty( $review_id ) ) {
			return get_comment_text( $review_id );
		}

		return $default;
	}

	function review_title( $default, $post_id = null ) {
		$review_id = $this->review_id( 0, $post_id );
		if ( ! empty( $review_id ) ) {
			return ic_get_ic_rev_title( $review_id );
		}

		return $default;
	}

	function review_id( $default, $post_id = null ) {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}
		if ( ic_ic_reviews_enabled( $post_id ) ) {
			$user = wp_get_current_user();
			if ( $user && intval( $user->ID ) ) {
				$review_id = $this->previous_review( $user->ID, $post_id );
				if ( ! empty( $review_id ) ) {
					return $review_id;
				}
			}
		}

		return $default;
	}

	function check_review( $approved, $comment_data ) {
		if ( empty( $comment_data['comment_type'] ) || ! ic_string_contains( $comment_data['comment_type'], 'ic_rev' ) ) {
			return $approved;
		}
		if ( ! empty( $comment_data['comment_parent'] ) ) {
			return $approved;
		}
		if ( ! empty( $comment_data['comment_post_ID'] ) && ! $this->can_new_review( $comment_data['comment_post_ID'] ) ) {
			$review_id = $this->update_review( $comment_data );
			if ( $review_id ) {
				wp_redirect( add_query_arg( 'review_updated', $review_id, get_permalink( $comment_data['comment_post_ID'] ) ) . '#ic-review-update-message' );
			} else {
				wp_redirect( add_query_arg( 'review_updated', 'error', get_permalink( $comment_data['comment_post_ID'] ) ) . '#ic-review-update-message' );
			}
			exit;
		}

		return $approved;
	}

	function update_review( $comment_data ) {
		if ( empty( $comment_data['comment_type'] ) || ! ic_string_contains( $comment_data['comment_type'], 'ic_rev' ) ) {
			return;
		}
		if ( empty( $comment_data['comment_post_ID'] ) || ! is_user_logged_in() ) {
			return;
		}
		$user = wp_get_current_user();
		if ( $user && intval( $user->ID ) ) {
			$review_id                  = $this->previous_review( $user->ID, $comment_data['comment_post_ID'] );
			$comment_data['comment_ID'] = $review_id;
			wp_update_comment( $comment_data );

			return $review_id;
		}
	}

	function previous_review( $user_id, $post_id ) {
		$comment_type = $this->comment_type( $post_id );
		$args         = array(
			'post_id'      => $post_id,
			'user_id'      => $user_id,
			'comment_type' => $comment_type,
			'fields'       => 'ids'
		);
		$reviews      = get_comments( $args );

		return end( $reviews );
	}

	function can_new_review( $post_id ) {
		$open = true;
		if ( intval( $post_id ) && get_post( $post_id ) ) {
			$args = array( 'post_id' => $post_id, 'count' => true, 'comment_type' => $this->comment_type( $post_id ) );
			$user = wp_get_current_user();
			if ( $user && intval( $user->ID ) ) {
				$args['user_id'] = $user->ID;
				$open            = get_comments( $args ) ? false : true;
			} else {
				$commenter = wp_get_current_commenter();
				if ( $commenter && is_array( $commenter ) && isset( $commenter['comment_author_email'] ) ) {
					$args['author_email'] = $commenter['comment_author_email'];
					$open                 = get_comments( $args ) ? false : true;
				}
			}
		}

		return $open;
	}

	function comment_type( $post_id ) {
		$post_type    = get_post_type( $post_id );
		$comment_type = 'ic_rev';
		if ( $post_type !== 'al_product' ) {
			$comment_type .= '_' . $post_type;
		}

		return $comment_type;
	}
}

new ic_reviews_limit;