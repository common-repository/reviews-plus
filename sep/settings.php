<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages separate price settings
 *
 * Here price settings are defined and managed.
 *
 * @version        1.0.0
 * @package        price-field/sep
 * @author        Norbert Dreszer
 */
add_action( 'admin_menu', 'register_ic_reviews_settings_menu' );

/**
 * Adds price field submenu to WordPress Settings menu
 */
function register_ic_reviews_settings_menu() {
	add_options_page( __( 'Reviews', 'reviews-plus' ), __( 'Reviews', 'reviews-plus' ), 'manage_options', 'ic_reviews', 'ic_reviews_settings' );
}

add_action( 'admin_init', 'register_ic_reviews_sep_settings', 20 );

/**
 * Registers price field settings
 */
function register_ic_reviews_sep_settings() {
	register_setting( 'ic_reviews', 'ic_reviews_settings' );
}

/**
 * Sets default price field settings
 *
 * @return type
 */
function default_ic_reviews_settings() {
	$show_def = array( '' );
	if ( function_exists( 'product_post_type_array' ) ) {
		$show_def = product_post_type_array();
	}

	return array(
		'enabled'                => array( 'al_product' ),
		'show'                   => $show_def,
		'disable_rating'         => 0,
		'reply_enabled'          => 0,
		'block_multiple_reviews' => 0,
		'revs_color'             => '#000',
		'icon'                   => 'f155'
	);
}

/**
 * Returns price field settings
 *
 * @return type
 */
function get_ic_reviews_sep_settings() {
	$settings = wp_parse_args( get_option( 'ic_reviews_settings' ), default_ic_reviews_settings() );

	return $settings;
}

/**
 * Shows price field settings fields
 *
 */
function ic_reviews_settings() {
	$post_types = get_post_types( array( 'publicly_queryable' => true ), 'objects' );
	unset( $post_types['attachment'] );
	echo '<h2>' . __( 'Settings', 'reviews-plus' ) . ' - impleCode Product Reviews</h2>';
	if ( ! defined( 'AL_BASE_PATH' ) ) {
		implecode_info( sprintf( __( '%s is recommended if you need a product catalog functionality. It will let you separate products or services from other website content.', 'reviews-plus' ), '<a href="' . admin_url( 'plugin-install.php?s=ecommerce+product+catalog+by+implecode&tab=search&type=term' ) . '">eCommerce Product Catalog</a>' ) );
	} else {
		implecode_info( __( 'Product Reviews are automatically enabled for product catalog. Use this screen only if you have to enable reviews for other content.', 'reviews-plus' ) );
	}
	echo '<h3>' . __( 'General Reviews Settings', 'reviews-plus' ) . '</h3>';
	echo '<form method="post" action="options.php">';
	settings_fields( 'ic_reviews' );
	$reviews_settings = get_ic_reviews_sep_settings();
	echo '<h4>' . __( 'Enable Reviews for', 'reviews-plus' ) . ':</h4>';
	$checked = in_array( 'page', $reviews_settings['enabled'] ) ? 'checked' : '';
	echo '<input ' . $checked . ' type="checkbox" name="ic_reviews_settings[enabled][]" value="page"> ' . __( 'Pages', 'reviews-plus' ) . '<br>';
	foreach ( $post_types as $type_key => $type_obj ) {
		if ( strpos( $type_key, 'al_product' ) !== 0 ) {
			$checked = in_array( $type_key, $reviews_settings['enabled'] ) ? 'checked' : '';
			echo '<input ' . $checked . ' type="checkbox" name="ic_reviews_settings[enabled][]" value="' . $type_key . '"> ' . $type_obj->labels->name . '<br>';
		}
	}
	echo '<h4>' . __( 'Show Reviews on', 'reviews-plus' ) . ':</h4>';
	$checked = in_array( 'page', $reviews_settings['show'] ) ? 'checked' : '';
	echo '<input ' . $checked . ' class="show-reviews" type="checkbox" name="ic_reviews_settings[show][]" value="page"> ' . __( 'Pages', 'reviews-plus' ) . '<br>';
	$reviews_settings['show-on']['page'] = ! empty( $reviews_settings['show-on']['page'] ) ? $reviews_settings['show-on']['page'] : 'selected';
	ic_revs_show_on_settings_html( 'page', $reviews_settings['show-on']['page'] );
	foreach ( $post_types as $type_key => $type_obj ) {
		if ( strpos( $type_key, 'al_product' ) !== 0 ) {
			$checked = in_array( $type_key, $reviews_settings['show'] ) ? 'checked' : '';
			echo '<input ' . $checked . ' class="show-reviews" type="checkbox" name="ic_reviews_settings[show][]" value="' . $type_key . '"> ' . $type_obj->labels->name . '<br>';
			$reviews_settings['show-on'][ $type_key ] = ! empty( $reviews_settings['show-on'][ $type_key ] ) ? $reviews_settings['show-on'][ $type_key ] : 'selected';
			ic_revs_show_on_settings_html( $type_key, $reviews_settings['show-on'][ $type_key ] );
		}
	}
	echo implecode_info( __( 'You can also display reviews with', 'reviews-plus' ) . ': <ol><li>' . sprintf( __( '%s shortcode placed in content.', 'reviews-plus' ), '<code>' . esc_html( '[reviews]' ) . '</code>' ) . '</li><li>' . sprintf( __( '%s code placed in template file.', 'reviews-plus' ), '<code>' . esc_html( '<?php ic_reviews() ?>' ) . '</code>' ) . '</li><li>' . sprintf( __( '%s shortcode placed in content.', 'reviews-plus' ), '<code>' . esc_html( '[average_rating]' ) . '</code>' ) . '</li><li>' . sprintf( __( '%s code placed in template file.', 'reviews-plus' ), '<code>' . esc_html( '<?php echo ic_get_reviews_average_html() ?>' ) . '</code>' ) . '</li></ol>' );
	echo '<h3>' . __( 'Additional Reviews Settings', 'reviews-plus' ) . '</h3>';
	echo '<table>';
	implecode_settings_checkbox( __( 'Disable Rating', 'reviews-plus' ), 'ic_reviews_settings[disable_rating]', $reviews_settings['disable_rating'] );
	implecode_settings_checkbox( __( 'Enable review reply for anyone', 'reviews-plus' ), 'ic_reviews_settings[reply_enabled]', $reviews_settings['reply_enabled'] );
	implecode_settings_checkbox( __( 'Prevent users from posting multiple reviews for the same item', 'reviews-plus' ), 'ic_reviews_settings[block_multiple_reviews]', $reviews_settings['block_multiple_reviews'], 1, __( 'Logged in users will be able to update their reviews.', 'reviews-plus' ) );
	implecode_settings_text_color( __( 'Rating color', 'reviews-plus' ), 'ic_reviews_settings[revs_color]', $reviews_settings['revs_color'] );
	ic_revs_types_settings( $reviews_settings['icon'] );
	echo '</table>';
	echo '<p class="submit"><input type="submit" class="button-primary" value="' . __( 'Save changes', 'reviews-plus' ) . '"/></p>';
	echo '</form>';
	echo '<div class="plugin-logo"><a href="https://implecode.com/#cam=reviews-settings&key=logo-link"><img class="en" src="' . AL_REVIEWS_BASE_URL . '/img/implecode.png' . '" width="282px" alt="impleCode" /></a></div>';
}

function ic_revs_show_on_settings_html( $type_key, $selected = 'selected' ) {
	echo '<div class="enabled-where" style="margin: 5px 10px; display:none;">';
	echo '<input type="radio" name="ic_reviews_settings[show-on][' . $type_key . ']" value="selected" ' . checked( $selected, 'selected', 0 ) . '> ' . __( 'Selected', 'reviews-plus' ) . '<br>';
	echo '<input type="radio" name="ic_reviews_settings[show-on][' . $type_key . ']" value="all" ' . checked( $selected, 'all', 0 ) . '> ' . __( 'All', 'reviews-plus' );
	echo '</div>';
}

function ic_revs_types_settings( $selected = '' ) {
	$available = ic_revs_types_available();
	$counter   = 0;
	echo '<tr>';
	echo '<td style="vertical-align:top;">' . __( 'Rating icon', 'reviews-plus' ) . ':</td>';
	echo '<td>';
	foreach ( $available as $icon ) {
		if ( is_array( $icon ) ) {
			$icon = $icon[0];
		}
		echo '<label><input ' . checked( $icon, $selected, 0 ) . ' type="radio" name="ic_reviews_settings[icon]" value="' . $icon . '">';
		echo '<span class="rating-icon-settings ' . $icon . '"></span></label>';
		$counter ++;
		if ( $counter === 4 ) {
			echo '<div style="height: 20px"></div>';
			$counter = 0;
		} else {
			echo '<span style="display:inline-block;width:40px;"></span>';
		}
	}
	echo '<style>';
	echo '
	.rating-icon-settings {
	    display: inline-block;
        width: 27px;
        height: 27px;
        font-size: 27px;
	}
	.rating-icon-settings:before {
	    font-family: "dashicons";
        line-height: 27px;
        vertical-align: middle;
    }';
	foreach ( $available as $icon ) {
		if ( is_array( $icon ) ) {
			$icon = $icon[0];
		}
		echo '.' . $icon . ':before {';
		echo 'content:"\\' . $icon . '"';
		echo '}';
	}
	echo '</style>';
	echo '</td>';
	echo '</tr>';
}

function ic_revs_types_available() {
	return array(
		'f155' => array( 'f155', 'f154' ),
		'f521' => array( 'f521', 'f520' ),
		'f159' => 'f159',
		'f12a' => 'f12a',
		'f107' => 'f107',
		'f306' => 'f306',
		'f308' => 'f308',
		'f309' => 'f309',
		'f487' => 'f487',
		'f488' => 'f488',
		'f452' => 'f452',
		'f451' => 'f451',
		'f16d' => 'f16d',
		'f339' => 'f339',
		'f511' => 'f511',
		'f527' => 'f527',
		'f328' => 'f328',
		'f15f' => 'f15f',
		'f16c' => 'f16c',
		'f16f' => 'f16f',
		'f17f' => 'f17f',
		'f198' => 'f198',
		'f197' => 'f197'
	);
}

add_action( 'admin_notices', 'ic_reviews_admin_notices' );

/**
 * Shows Reviews Plus Notices
 *
 */
function ic_reviews_admin_notices() {
	if ( ( ic_reviews_admin_page_conditional() || ( isset( $_GET['page'] ) && $_GET['page'] == 'ic_reviews' ) ) && false === get_site_transient( 'ic_revs_hide_plugin_translation_info' ) ) {
		ic_revs_plugin_translation_notice();
	}
}

function ic_revs_plugin_translation_notice() {
	?>
    <div class="update-nag ic-revs-translate"><?php echo sprintf( __( "<strong>Psst, it's less than 1 minute</strong> to add some translations to %s collaborative <a target='_blank' href='%s'>translation project</a>", 'ecommerce-product-catalog' ), 'Reviews Plus', 'https://translate.wordpress.org/projects/wp-plugins/reviews-plus', 'Reviews Plus' ) ?>
    <span class="dashicons dashicons-no"></span></div><?php
}

add_action( 'wp_ajax_hide_ic_revs_translate_notice', 'ajax_hide_revs_translation_notice' );

/**
 * Handles ajax translation notice hide
 *
 */
function ajax_hide_revs_translation_notice() {
	if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'ic-ajax-nonce' ) ) {
		ic_revs_plugin_translation_notice_hide();
	}
	wp_die();
}

function ic_revs_plugin_translation_notice_hide() {
	set_site_transient( 'ic_revs_hide_plugin_translation_info', 1 );
}

add_filter( 'plugin_action_links_' . plugin_basename( AL_REVIEWS_MAIN_FILE ), 'ic_reviews_links' );

/**
 * Shows settings link on plugin list
 *
 * @param array $links
 *
 * @return type
 */
function ic_reviews_links( $links ) {
	$links[] = '<a href="' . get_admin_url( null, 'options-general.php?page=ic_reviews' ) . '">Settings</a>';

	//$links[] = '<a href="https://implecode.com/wordpress/plugins/premium-support/#cam=catalog-settings-link&key=support-link" target="_blank">Premium Support</a>';
	return array_reverse( $links );
}
