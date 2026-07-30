<?php
/**
 * Rebrand announcement notice.
 *
 * Announces the Quick View for WooCommerce to Reno Quick View rename on the
 * plugin's own screen. Dismissing it hides the notice for the whole site,
 * matching how the offer banner behaves, so the dismissed state is kept in a
 * site option.
 *
 * @link http://shapedplugin.com
 * @since 2.3.0
 * @package Woo_Quick_View.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  // if direct access.

if ( ! class_exists( 'Woo_Quick_View_Rebrand_Notice' ) ) {
	/**
	 * The class for the rebrand announcement notice.
	 */
	class Woo_Quick_View_Rebrand_Notice {

		/**
		 * The single instance of the class.
		 *
		 * @var self
		 * @since 2.3.0
		 */
		private static $instance = null;

		/**
		 * Site option key holding the dismissed state.
		 *
		 * @var string
		 */
		const DISMISSED_OPTION_KEY = 'sp_wqv_rebrand_notice_dismissed';

		/**
		 * Blog post explaining the rebrand.
		 *
		 * @var string
		 */
		const ANNOUNCEMENT_URL = 'https://shapedplugin.com/woo-quick-view-is-now-reno-quick-view/';

		/**
		 * Class constructor.
		 */
		private function __construct() {
			add_action( 'admin_notices', array( $this, 'render_rebrand_notice' ) );
			add_action( 'wp_ajax_sp_wqv_dismiss_rebrand_notice', array( $this, 'dismiss_rebrand_notice' ) );
		}

		/**
		 * Retrieves the singleton instance of the class.
		 *
		 * @return self The singleton instance of the class.
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Whether the current screen belongs to this plugin.
		 *
		 * The plugin registers a single top level page and renders its help tab
		 * on the same page, so one screen id covers every plugin screen.
		 *
		 * @return bool
		 */
		private function is_plugin_screen() {
			if ( ! function_exists( 'get_current_screen' ) ) {
				return false;
			}

			$current_screen = get_current_screen();

			if ( ! is_object( $current_screen ) ) {
				return false;
			}

			return 'toplevel_page_wqv_settings' === $current_screen->id;
		}

		/**
		 * Determines whether the notice should be shown to the current user.
		 *
		 * Dismissing writes a site-wide option, so the same permission that
		 * guards the plugin UI guards the notice. The pro plugin shuts this one
		 * down entirely when both are active, so no cross-plugin check is
		 * needed to keep the rename from being announced twice.
		 *
		 * @return bool
		 */
		public function should_render() {
			if ( ! $this->is_plugin_screen() || ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			return ! get_option( self::DISMISSED_OPTION_KEY );
		}

		/**
		 * Renders the rebrand notice on the page.
		 *
		 * The styles live in notices.min.css, which includes/functions.php
		 * already enqueues on every admin screen, so nothing is enqueued here.
		 *
		 * @return void
		 */
		public function render_rebrand_notice() {
			if ( ! $this->should_render() ) {
				return;
			}

			$nonce = wp_create_nonce( 'sp_wqv_rebrand_notice_dismiss' );
			?>
			<div id="wqv-rebrand-notice" class="wqv-rebrand-notice">
				<div class="wqv-rebrand-notice-content">
					<img class="wqv-rebrand-notice-icon" src="<?php echo esc_url( SP_WQV_URL . 'admin/views/img/rebrand-notice-icon.gif' ); ?>" alt="" width="18" height="18">
					<p class="wqv-rebrand-notice-text">
						<?php
						printf(
							/* translators: 1: old plugin name, 2: new plugin name. */
							esc_html__( '%1$s is now %2$s — Everything else remains the same. Why we rebranded', 'woo-quickview' ),
							'<strong>' . esc_html__( 'Quick View for WooCommerce', 'woo-quickview' ) . '</strong>',
							'<strong>' . esc_html__( 'Reno Quick View', 'woo-quickview' ) . '</strong>'
						);
						?>
					</p>
					<a class="wqv-rebrand-notice-link" href="<?php echo esc_url( self::ANNOUNCEMENT_URL ); ?>" target="_blank" rel="noopener noreferrer">
						<span class="screen-reader-text"><?php esc_html_e( 'Read more about the rebranding', 'woo-quickview' ); ?></span>
						<svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
							<path d="M5.25 2.625H2.625A1.167 1.167 0 0 0 1.458 3.79v7.583a1.167 1.167 0 0 0 1.167 1.167h7.583a1.167 1.167 0 0 0 1.167-1.167V8.75" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M8.75 1.458h3.792V5.25M6.125 7.875l6.417-6.417" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</a>
				</div>

				<button type="button" class="wqv-rebrand-notice-dismiss" data-nonce="<?php echo esc_attr( $nonce ); ?>">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice', 'woo-quickview' ); ?></span>
					<svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
						<path d="M10.5 3.5 3.5 10.5M3.5 3.5l7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
					</svg>
				</button>
			</div>

			<script type="text/javascript">
				(function($){
					$(document).on('click', '#wqv-rebrand-notice .wqv-rebrand-notice-dismiss', function(e){
						e.preventDefault();
						$.post(ajaxurl, {
							action: 'sp_wqv_dismiss_rebrand_notice',
							nonce: $(this).data('nonce')
						});
						$('#wqv-rebrand-notice').fadeOut(300);
					});
				})(jQuery);
			</script>
			<?php
		}

		/**
		 * Handles the AJAX request to dismiss the rebrand notice.
		 *
		 * @return void
		 */
		public function dismiss_rebrand_notice() {
			check_ajax_referer( 'sp_wqv_rebrand_notice_dismiss', 'nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'error' => esc_html__( 'Authorization failed!', 'woo-quickview' ) ), 401 );
			}

			// Not autoloaded: only ever read on this plugin's admin screen.
			update_option( self::DISMISSED_OPTION_KEY, 1, false );

			wp_send_json_success();
		}
	}

	// Initialize the rebrand notice.
	Woo_Quick_View_Rebrand_Notice::instance();
}
