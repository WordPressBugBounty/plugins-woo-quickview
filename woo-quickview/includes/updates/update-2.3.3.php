<?php
/**
 * Update version.
 *
 * Migrates the old Quick View button settings ("Hide label" checkbox,
 * "None" icon option and "Button CSS Class" style) to the new
 * "Button Style" option.
 *
 * @package Woo Quick View
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  // if direct access.

update_option( 'woo_quick_view_version', '2.3.3' );
update_option( 'woo_quick_view_db_version', '2.3.3' );

// Run in a closure so no variables leak into the global scope.
( function () {
	$settings = get_option( '_sp_wqvpro_options' );

	if ( ! is_array( $settings ) ) {
		return;
	}

	$old_style = isset( $settings['wqvpro_qv_button_style'] ) ? $settings['wqvpro_qv_button_style'] : '';

	if ( ! in_array( $old_style, array( 'label', 'icon', 'label_with_icon' ), true ) ) {
		$icon_option = isset( $settings['wqvpro_quick_view_button_icon_option'] ) ? $settings['wqvpro_quick_view_button_icon_option'] : 'no';
		$icon_only   = ! empty( $settings['wqvpro_quick_view_button_icon_only'] );

		// 'custom_image' is a Pro icon; Pro shares this option, so keep it as an icon.
		$has_icon = in_array( $icon_option, array( 'font_icon', 'custom_image' ), true );

		if ( ! $has_icon ) {
			$settings['wqvpro_qv_button_style'] = 'label';
		} elseif ( $icon_only ) {
			$settings['wqvpro_qv_button_style'] = 'icon';
		} else {
			$settings['wqvpro_qv_button_style'] = 'label_with_icon';
		}

		// "None" was removed from the icon options; the label style hides the icon.
		if ( ! $has_icon ) {
			$settings['wqvpro_quick_view_button_icon_option'] = 'font_icon';
		}

		// The CSS class was only applied with the old "Button CSS Class" style; it now has its own
		// "Button CSS Class" checkbox (Advanced tab, same key as Pro). Keep the saved class name as is.
		if ( 'button_css_class' === $old_style ) {
			$settings['check_button_css_class'] = '1';
		}
	}

	/**
	 * Update settings.
	 */
	update_option( '_sp_wqvpro_options', $settings );
} )();
