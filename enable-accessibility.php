<?php
/*
 * Plugin Name: Enable Accessibility
 * Description: Enable Accessibility is a beautiful Advanced Toolbar that gives you great tools for fixing a common accessibility problems in WordPress themes..
 * Version:     1.0.0
 * Author:      uPress
 * Author URI: https://www.upress.co.il
 * Plugin URI: https://www.enable.co.il
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * Text Domain: enable-accessibility
 * Domain Path: /languages/
 */
/*
 * Original Plugin by Octa Code
 *
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'EnableAccessibilityPlugin' ) ) :

	/**
	 * Register the plugin.
	 *
	 * Display the administration panel, insert JavaScript etc.
	 */
	class EnableAccessibilityPlugin {

		protected $loader;
		protected $version;
		protected $plugin_slug;
		protected $kses_allowed_html;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->plugin_slug = 'enable-accessibility';
			load_plugin_textdomain( $this->plugin_slug, false, basename( dirname( __FILE__ ) ) . '/languages/' );
			$this->version = '1.0.0';

			$this->kses_allowed_html = array(
				'a'      => array(
					'href'   => true,
					'target' => true,
					'title'  => true,
				),
				'strong' => array(),
				'em'     => array(),
				'b'      => array(),
				'i'      => array(),
				'br'     => array(),
			);

			$this->define_constants();
			$this->setup_actions();
			$this->load_dependencies();

			add_action( 'admin_menu', array( $this, 'accessibility_create_menu' ) );
		}

		/**
		 * Hook WC Brands into WordPress
		 */
		private function setup_actions() {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_adminscripts' ) );
		}

		// Styles Handling
		public function enqueue_scripts() {
			$license = get_option( 'enable-accessibility' );
			wp_enqueue_script(
				'enable-accessibility',
				'//cdn.enable.co.il/licenses/' . (!empty($license) ? $license . '/' : '') . 'init.js',
				array( 'jquery' ),
				ENABLE_ACCESSIBILITY_VERSION,
				true
			);
		}

		public function enqueue_adminscripts() {
			wp_enqueue_style(
				'admin-style',
				ENABLE_ACCESSIBILITY_ASSETS_URL . '/css/admin-style.css',
				array(),
				ENABLE_ACCESSIBILITY_VERSION
			);
		}

		/**
		 * Define Accessibilty constants
		 */
		private function define_constants() {
			define( 'ENABLE_ACCESSIBILITY_VERSION', $this->version );
			define( 'ENABLE_ACCESSIBILITY_BASE_URL', trailingslashit( plugins_url( 'enable-accessibility' ) ) );
			define( 'ENABLE_ACCESSIBILITY_ASSETS_URL', trailingslashit( ENABLE_ACCESSIBILITY_BASE_URL . 'assets' ) );
			define( 'ENABLE_ACCESSIBILITY_PATH', plugin_dir_path( __FILE__ ) );
			define( 'ENABLE_ACCESSIBILITY_PLUGINURL', 'https://my.enable.co.il/modules/addons/goenable/api.php' );
		}

		/**
		 * WP Admin menu links.
		 */
		public function accessibility_create_menu() {
			//create new top-level menu
			add_menu_page(
				esc_html__( 'Accessibility Settings', 'enable-accessibility' ),
				esc_html__( 'Enable Accessibility', 'enable-accessibility' ),
				'manage_options', 'enable-accessibility-admin',
				array(
					$this,
					'accessibility_settings_page'
				),
				'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIzNDQuNjYxcHgiIGhlaWdodD0iMzQ0LjU4NnB4IiB2aWV3Qm94PSIwIDAgMzQ0LjY2MSAzNDQuNTg2IiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCAzNDQuNjYxIDM0NC41ODYiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxwYXRoIGZpbGw9IiNGRkZGRkYiIGQ9Ik0xNzIuMzY1LDBDNzcuMTkzLDAsMCw3Ny4xMTEsMCwxNzIuMjkyYzAsOTUuMTg0LDc3LjE5MywxNzIuMjk0LDE3Mi4zNjUsMTcyLjI5NGM5NS4xODUsMCwxNzIuMjk2LTc3LjExLDE3Mi4yOTYtMTcyLjI5NEMzNDQuNjYxLDc3LjExMSwyNjcuNTUsMCwxNzIuMzY1LDB6IE0yODEuMDcxLDEzNC43OTJMMTc5LjExNywyMzYuMjY1Yy0wLjY4NiwxLjA0LTEuNDcsMi4wMjYtMi4zNjksMi45MzNsLTE1LjgyOCwxNS43NDRjLTcuNjMyLDcuNjMyLTIwLjYxMiw3LjYzMi0yOC4yNTcsMGwtNjkuMDA3LTY5LjY0M2MtNy42MTEtNy42MzItNy42MTEtMjAuNjI4LDAtMjguMjU0bDE1Ljc0MS0xNS43NjZjNy42MzgtNy42MTksMjAuNjQ2LTcuNjE5LDI4LjI2NSwwbDM5LjA2NywzOS4zOWw5MC4zNC04OS44OTNjNy41NS03LjYzMSwyMC42MjgtNy42MzEsMjguMjM4LDBsMTUuNzYyLDE1Ljc0MUMyODguNzEyLDExNC4xNjMsMjg4LjcxMiwxMjcuMTUyLDI4MS4wNzEsMTM0Ljc5MnoiLz48L3N2Zz4='
			);
			add_submenu_page(
				'enable-accessibility-admin',
				esc_html__( 'Attachments alt', 'enable-accessibility' ),
				esc_html__( 'Attachments alt', 'enable-accessibility' ),
				'manage_options', 'enable-accessibility-media',
				array(
					$this,
					'accesibility_admin_media_page'
				),
				'dashicons-admin-media'
			);
		}

		/**
		 * General Settings.
		 */
		public function accessibility_settings_page() {
			if ( $_SERVER["REQUEST_METHOD"] == "POST" && isset( $_POST['action'] ) && $_POST['action'] == "save_accessibility_settings" ) {
				$this->_admin_update_accessibility_settings();
			}

			include( "includes/accessibility-settings.php" );
		}

		/**
		 * Attachments media handler.
		 */
		public function accesibility_admin_media_page() {
			//      update_post_meta($pid, '_wp_attachment_image_alt', $palt);
			if ( $_SERVER["REQUEST_METHOD"] == "POST" && isset( $_POST['action'] ) && $_POST['action'] == "save_accessibility_attachments_settings" ) {
				$this->_admin_update_attachments();
			}
			include( "includes/accessibility-attachments-alt.php" );
		}

		private function load_dependencies() {
			require_once ENABLE_ACCESSIBILITY_PATH . 'includes/class-accesibility-loader.php';

			$this->loader = new EnableAcceibility_Loader();
		}

		public function run() {
			$this->loader->run();
		}

		public function get_version() {
			return $this->version;
		}

		public function _admin_update_attachments() {
			$alt_data   = $_POST['attachments_alt'];
			$title_data = $_POST['attachments_title'];
			foreach ( $alt_data as $pid => $p_alt ) {
				update_post_meta( (int)$pid, '_wp_attachment_image_alt', sanitize_text_field( $p_alt ) );
			}
			foreach ( $title_data as $pid => $p_title ) {
				$post = array(
					'ID'         => (int)$pid,
					'post_title' => sanitize_text_field( $p_title ),
				);
				wp_update_post( $post );
			}

			// Validate the license.
			$message = esc_html__( "Saved Changes Successfully", 'enable-accessibility' );
			echo "<div class=\"notice notice-success\"><p>{$message}</p></div>";
		}


		private function _get_license_data( $lkey ) {
			if ( $lkey == "" ) {
				return new stdClass();
			}

			$jsonRsp = file_get_contents( ENABLE_ACCESSIBILITY_PLUGINURL . "?litk=" . urlencode( $lkey ) . "&r=" . urlencode( site_url() ) );

			$licenseData = json_decode( $jsonRsp );

			return $licenseData;
		}

		/**
		 * Post form action for the update prices for brand.
		 */
		function _admin_update_accessibility_settings() {
			$lkey = sanitize_text_field( $_POST["enable_license"] );
			update_option( 'enable-accessibility', $lkey );
			if ( $lkey == '' ) {
				$status = 0;
			} else {
				$licenseData = $this->_get_license_data( $lkey );
				if ( $licenseData->status == 'Success' ) {
					$status = 1;
				} else {
					$status = - 1;
				}
			}

			update_option( 'enable-accessibility-status', $status );
			// Validate the license.
			$message = esc_html__( "Saved Settings Successfully", 'enable-accessibility' );
			echo "<div class=\"notice notice-success\"><p>{$message}</p></div>";
		}

		function _get_license_message( $license_data ) {
			if ( ! $license_data ) {
				return sprintf(
					wp_kses(
						__( 'Get your license <a href="%s" target="_blank">here</a>.', 'enable-accessibility' ),
						$this->kses_allowed_html
					),
					'https://www.enable.co.il'
				);
			}

			if ( 'error' == $license_data->result ) {
				if ( 'NO_DOMAIN' == $license_data->error ) {
					return '<span style="color: red;">' . sprintf(
							wp_kses(
								__( 'License does not permit usage on this domain. Add your domain <a href="%s" target="_blank">here</a>.', 'enable-accessibility' ),
								$this->kses_allowed_html
							),
							'https://my.enable.co.il/index.php?m=goenable'
						) . '</span>';
				}

				return '<span style="color: red;">' . esc_html__( 'License did not validate.', 'enable-accessibility' ) . '</span>';
			}

			if ( isset( $license_data->data->nextduedate ) ) {
				if('0000-00-00' === $license_data->data->nextduedate) {
					return sprintf(
						wp_kses(
							__( 'Success! This license is valid.', 'enable-accessibility' ),
							$this->kses_allowed_html
						)
					);
				}

				$daysLeft = ( strtotime( $license_data->data->nextduedate ) - time() ) / ( 3600 * 24 );
				if ( $daysLeft > 0 ) {
					return sprintf(
						wp_kses(
							__( 'Success! <strong>%d</strong> days for license renewal.', 'enable-accessibility' ),
							$this->kses_allowed_html
						),
						round( $daysLeft )
					);
				} else {
					return sprintf(
						wp_kses(
							__( 'Oh Oh! <strong>Your license has expired %d days ago.</strong><br/>Please go ahead and renew.', 'enable-accessibility' ),
							$this->kses_allowed_html
						),
						round( $daysLeft * ( - 1 ) )
					);
				}
			}

			return '';
		}

		public function get_attachments( $all = false ) {
			global $wpdb;

			$query_all_attachments = "SELECT `wp`.`ID`, `wp`.`post_author`, `wp`.`post_date`, `wp`.`post_title`, `wp`.`post_name`,
        `wp`.`guid`, `wp`.`post_parent`, `wpp`.`post_title` `parent_title` FROM $wpdb->posts `wp` "
			                         . " LEFT JOIN $wpdb->posts `wpp` ON `wp`.`post_parent` = `wpp`.`ID`"
			                         . " WHERE `wp`.`post_type` = 'attachment' ORDER BY `wp`.`ID` DESC";

			$posts = $wpdb->get_results( $query_all_attachments );

			return $posts;
		}

	}

endif;

function run_accessibility_plugin() {
	$plugin = new EnableAccessibilityPlugin();
	$plugin->run();
}

run_accessibility_plugin();
