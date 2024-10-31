<?php

/**
 * Plugin Name: Reviews Plus - Google compatible WordPress Reviews for any content
 * Description: Add rich reviews to posts, pages or any custom post type. Reviews summary compatible with SERP.
 * Version: 1.4.1
 * Author: impleCode
 * Author URI: https://implecode.com
 * Text Domain: reviews-plus
 * Domain Path: /lang/
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'AL_REVIEWS_BASE_URL', plugins_url( '/', __FILE__ ) );
define( 'AL_REVIEWS_BASE_PATH', dirname( __FILE__ ) );
define( 'AL_REVIEWS_MAIN_FILE', __FILE__ );

add_action( 'after_setup_theme', 'start_ic_revs', 16 );

function start_ic_revs() {
	load_plugin_textdomain( 'reviews-plus', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	require_once( AL_REVIEWS_BASE_PATH . '/functions/index.php' );
	require_once( AL_REVIEWS_BASE_PATH . '/includes/index.php' );
	require_once( AL_REVIEWS_BASE_PATH . '/sep/index.php' );
	require_once( AL_REVIEWS_BASE_PATH . '/ext/index.php' );
}

add_action( 'init', 'ic_revs_register_styles', 20 );

/**
 * Registers reviews stylesheet
 *
 */
function ic_revs_register_styles() {
	wp_register_style( 'al_ic_revs_styles', AL_REVIEWS_BASE_URL . 'css/reviews-plus.min.css' . ic_filemtime( AL_REVIEWS_BASE_PATH . '/css/reviews-plus.min.css' ) );
	wp_register_script( 'al_ic_revs_scripts', AL_REVIEWS_BASE_URL . 'js/reviews-plus.min.js' . ic_filemtime( AL_REVIEWS_BASE_PATH . '/js/reviews-plus.min.js' ), array( 'jquery' ) );
}

add_action( 'current_screen', 'ic_revs_admin_register_styles', 20 );

/**
 * Registers reviews admin stylesheet
 *
 */
function ic_revs_admin_register_styles() {
	wp_register_style( 'al_ic_revs_admin_styles', AL_REVIEWS_BASE_URL . 'css/reviews-plus-admin.min.css' . ic_filemtime( AL_REVIEWS_BASE_PATH . 'css/reviews-plus-admin.min.css' ), array(
		'wp-color-picker',
		'dashicons'
	) );
	wp_register_script( 'al_ic_revs_scripts', AL_REVIEWS_BASE_URL . 'js/reviews-plus.min.js' . ic_filemtime( AL_REVIEWS_BASE_PATH . '/js/reviews-plus.min.js' ), array( 'jquery' ) );
	$deps = array(
		'jquery',
		'admin-comments',
	);
	if ( is_ic_revs_admin_screen() || is_ic_review_edit_screen() ) {
		$in_footer = true;
	} else {
		$deps[]    = 'wp-color-picker';
		$in_footer = false;
	}
	$screen = get_current_screen();
	if ( ! empty( $screen->is_block_editor ) ) {
		$deps[] = 'wp-edit-post';
		wp_register_script( 'al_ic_revs_admin_editor', AL_REVIEWS_BASE_URL . 'js/reviews-plus-editor.min.js' . ic_filemtime( plugin_dir_path( __FILE__ ) . '/js/reviews-plus-editor.min.js' ), array(
			'wp-data',
			'wp-edit-post'
		) );
	}
	wp_register_script( 'al_ic_revs_admin_scripts', AL_REVIEWS_BASE_URL . 'js/reviews-plus-admin.min.js' . ic_filemtime( plugin_dir_path( __FILE__ ) . '/js/reviews-plus-admin.min.js' ), $deps, false, $in_footer );

}

add_action( 'wp_enqueue_scripts', 'ic_revs_enqueue_styles', 20 );

/**
 * Enqueues catalog stylesheet
 *
 */
function ic_revs_enqueue_styles() {
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style( 'al_ic_revs_styles' );
	wp_enqueue_script( 'al_ic_revs_scripts' );
	wp_localize_script( 'al_ic_revs_scripts', 'ic_revs', array(
		'no_rating'    => implecode_warning( __( 'The rating cannot be empty.', 'reviews-plus' ), 0 ),
		'no_empty'     => implecode_warning( __( 'A valid value is required.', 'reviews-plus' ), 0 ),
		'check_errors' => implecode_warning( __( 'Please fill all the required data.', 'reviews-plus' ), 0 )
	) );
}

add_action( 'admin_enqueue_scripts', 'ic_revs_admin_enqueue_styles', 20 );

/**
 * Enqueues catalog admin stylesheet
 *
 */
function ic_revs_admin_enqueue_styles() {
	wp_enqueue_style( 'al_ic_revs_admin_styles' );
	wp_localize_script( 'al_ic_revs_admin_scripts', 'reviews_object', array(
		'showcomm' => __( 'Show more reviews' ),
		'endcomm'  => __( 'No more reviews found.' ),
		'nonce'    => wp_create_nonce( 'ic-ajax-nonce' )
	) );
	wp_enqueue_script( 'al_ic_revs_admin_scripts' );
	wp_enqueue_script( 'al_ic_revs_scripts' );
	global $current_screen;
	$post_types = get_ic_review_active_post_types();
	if ( function_exists( 'product_post_type_array' ) ) {
		$post_types = array_merge( product_post_type_array(), $post_types );
	}
	if ( is_array( $post_types ) && ! empty( $current_screen->post_type ) && ! in_array( $current_screen->post_type, $post_types ) ) {
		return;
	}
	wp_enqueue_script( 'al_ic_revs_admin_editor' );
}

add_action( 'admin_enqueue_scripts', 'ic_enqueue_review_scripts' );

function ic_enqueue_review_scripts() {

	if ( is_ic_revs_admin_screen() ) {
		wp_enqueue_script( 'admin-comments' );
		enqueue_comment_hotkeys_js();
	}
}
