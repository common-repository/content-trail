<?php
// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die;
}

if ( !class_exists( 'Content_Trail_Admin' ) ) {
	class Content_Trail_Admin {
		/**
		 * Holds the values to be used in the fields callbacks
		 */
		protected static $instance	 = null;
		private $settings_page_slug	 = 'content_trail_settings';
		private $analytics_page_slug = 'content_trail_analytics';
		/**
		 * Start up
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'content_trail_plugin_setup_menu' ) );
			add_action( 'plugin_action_links_' . RCT_PLUGIN_BASENAME, array( $this, 'plugin_action_link' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'wp_ajax_rct_save_key', array( $this, 'save_key_handler' ) );
		}
		function enqueue_scripts( $hook ) {
			if ( $hook == 'toplevel_page_' . $this->settings_page_slug ) {
				$handle = RCT_PREFIX . '_custom_script';
				wp_register_script( $handle, plugin_dir_url( trailingslashit( RCT_PLUGIN_BASENAME ) ) . 'admin/js/content-trail-admin.js', array( 'jquery' ), RCT_VERSION, true );

				wp_enqueue_style( 'rct_admin_style', plugin_dir_url( trailingslashit( RCT_PLUGIN_BASENAME ) ) . 'admin/css/content-trail-admin.css', array(), RCT_VERSION );
				global $post;
				$args = array(
					'numberposts' => -1
				);
				$myposts						 = get_posts( $args );
				$req_post						 = array();
				$req_post[ 'id_not_to_delete' ]	 = array();
				$req_post[ 'guid' ]				 = $_SERVER[ 'SERVER_NAME' ];
				foreach ( $myposts as $posts ) {
					array_push( $req_post[ 'id_not_to_delete' ], $posts->ID );
				}
				$reco_key = $this->get_key();
				if ( !empty( $reco_key ) ) {
					$req_post[ 'recosense_key' ] = $reco_key;
					?>
					<style>
						.act_email{
							display:none;	
						}
						#verify_reco_key{
							display:none;
						}
					</style>
					<?php
				}
				//No RecoSensekey
				//$resp				 = wp_remote_get( trailingslashit( RCT_API_URL ) . 'getinfo?guid=' . $req_post[ 'guid' ] . '&recosense_key=' . $req_post[ 'recosense_key' ] . '' );
				$resp				 = wp_remote_get( trailingslashit( RCT_API_URL ) . 'getinfo?guid=' . $req_post[ 'guid' ]  );
				$body				 = json_decode( $resp[ 'body' ] );
				$last_modified_date	 = $body->last_modified_date;
				$my_query			 = new WP_Query( array(
					'date_query'	 => array(
						array(
							'column' => 'post_modified_gmt',
							'after'	 => $last_modified_date,
						)
					),
					'posts_per_page' => 1000
				) );

				$post_count	 = count( $my_query->posts );
				$i			 = 0;
				if ( $post_count > 0 ) {
					foreach ( $my_query->posts as $post ) {
						$req_post[ 'items' ][ $i ][ 'item_id' ]				 = $post->ID;
						$req_post[ 'items' ][ $i ][ 'title' ]				 = $post->post_title;
						$req_post[ 'items' ][ $i ][ 'description' ]			 = $post->post_content;
						$req_post[ 'items' ][ $i ][ 'url' ]					 = $post->guid;
						$req_post[ 'items' ][ $i ][ 'post_date' ]			 = $post->post_date;
						$req_post[ 'items' ][ $i ][ 'post_modified_date' ]	 = $post->post_modified;
						$req_post[ 'items' ][ $i ][ 'image' ]				 = $this->getImage( 1, $post->post_content,$post->ID );
						$i++;
					}
					wp_localize_script( $handle, 'scriptParams', $req_post );
				}
				$data = array(
					'api_url'	 => trailingslashit( RCT_API_URL ),
					'guid'		 => urlencode( RCT_GUID ),
					'key'		 => $this->get_key(),
					'ajax_url'	 => admin_url( 'admin-ajax.php' ),
				);
				wp_localize_script( $handle, 'rct_data', $data );
				wp_enqueue_script( $handle );
			}

			if ( $hook === 'content-trail_page_' . $this->analytics_page_slug ) {
				wp_register_script( 'rct_admin_analytics_highcharts', 'https://code.highcharts.com/highcharts.js', array(), false, true );
				wp_register_script( 'rct_admin_analytics_highcharts1', 'https://code.highcharts.com/modules/exporting.js', array(), false, true );
				wp_register_script( 'rct_admin_analytics', plugin_dir_url( trailingslashit( RCT_PLUGIN_BASENAME ) ) . 'admin/js/content-trail-analytics.js', array( 'jquery' ), RCT_VERSION, true );
				wp_enqueue_script( 'rct_admin_analytics_highcharts' );
				wp_enqueue_script( 'rct_admin_analytics_highcharts1' );
				wp_enqueue_script( 'rct_admin_analytics' );
			}
		}

		function plugin_action_link( $links ) {
			$path	 = 'admin.php?page=' . $this->settings_page_slug;
			$url	 = admin_url( $path );
			$links	 = array_merge( array(
				'<a class="content_trail_settings" href="' . $url . '">' . __( 'Settings' ) . '</a>'
			), $links );
			return $links;
		}

		function content_trail_plugin_setup_menu() {
			$page_title		 = __( 'User Related Posts', 'content_trail' );
			$menu_title		 = __( 'User Related Posts', 'content_trail' );
			$sub_menu_title	 = __( 'Analytics', 'content_trail' );
			add_menu_page( $page_title, $menu_title, 'manage_options', $this->settings_page_slug, array( $this, 'page_init' ) );
			add_submenu_page( $this->settings_page_slug, $page_title, $sub_menu_title, 'manage_options', $this->analytics_page_slug, array( $this, 'analytics_page_init' ) );
		}

		/**
		 * @return Content_Trail_Admin Returns the current instance of the Content_Trail_Admin class
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Register and add settings
		 */
		public function analytics_page_init() {
			?>
			<div class="col-md-12" id="engage_analysis"></div>
			<?php
		}

		public function page_init() {
			?><h3><?php _e( 'RecoSense Content Recommendation', 'content_trail' ); ?></h3>
			<div class="wrap settings-container">
				<table class="table table-bordered">
					
				</table>
			</div>
			<div class="configure container rct_configure"  >
				<table class="update_rec_container table-strip " >
					<tr>
						<td colspan="4">
							<h3 class="contentad_instructions_h2"><?php _e( 'Configure Your Widget', 'content_trail' ); ?> </h3>
						</td>
					</tr>
					<tr>
						<td>
							<h4>Widget Output Type</h4>
						</td>
						<td colspan="4">
							<ul class="wid_type">
								<li class="label label-default">Images and Texts</li>
								<li class="label label-default">Texts only</li>
							</ul>
						</td>
					</tr>
					<tr>
						<td>
							<h4>Widget Layout (<i>columns x rows</i>)</h4>
						</td>
						<td colspan="4">
							<ul class="wid_layout">
								<li class="label label-default" clmns = "colmns ">4x1</li>
								<li class="label label-default" clmns = "colmns ">3x1</li>
								<li class="label label-default" clmns = "colmns ">2x1</li>
								<li class="label label-default" clmns = "colmns">1x1</li>
							</ul>
						</td>
					</tr>
					<tr>
						<td colspan="4">
							<h4>Choose Widget Look</h4>
						</td>
					</tr>
					<tr>
						<td>
							<p>Select Widget Layout 1x1</p>
							<input type="radio" class="border" value="border" name="look" />
						</td>
						<td>
							<p>Select Widget Layout 1x1</p>
							<input type="radio" class="border" value="border-bottom" name="look" />
						</td>
						<td>
							<p>Select Widget Layout 1x1</p>
							<input type="radio" class="border" value="full-image" name="look" />
						</td>
						<td>
							<p>Select Widget Layout 2x1</p>
							<input type="radio" class="border" value="two-column" name="look" />
						</td>
					</tr>
					<tr>
						<td>
							<img class="img-responsive" src="<?php echo plugin_dir_url( trailingslashit( RCT_PLUGIN_BASENAME ) ) . 'admin/img/border.png' ?>" />
						</td>
						<td>
							<img class="img-responsive" src="<?php echo plugin_dir_url( trailingslashit( RCT_PLUGIN_BASENAME ) ) . 'admin/img/border-bottom.png' ?>" />
						</td>
						<td>
							<img class="img-responsive" src="<?php echo plugin_dir_url( trailingslashit( RCT_PLUGIN_BASENAME ) ) . 'admin/img/full-image.png' ?>" />
						</td>
						<td>
							<img class="img-responsive" src="<?php echo plugin_dir_url( trailingslashit( RCT_PLUGIN_BASENAME ) ) . 'admin/img/two-column.png' ?>" />
						</td>
					</tr>

					<tr>
						<td colspan="5">
							<input name="Submit" type="button"  class="button-primary update_config" value="Update Configuration" />
						</td>
					</tr>
				</table>
			</div>
			<?php
		}
		public function getImage( $num, $content,$post_ID ) {
			if (has_post_thumbnail($post_ID)) {
				$image_id = get_post_thumbnail_id($post_ID);
				$image_url = wp_get_attachment_image_src($image_id, $size);
				$image_url = $image_url[0];
			}else{
				$image_url = '';
				ob_start();
				ob_end_clean();
				$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
				$image_url = $matches [1] [0];

				/*if(empty($first_img)){ //Defines a default image
					$image_url = "/images/default.jpg";
				}*/
			}
			return $image_url;
		}
		public function save_key( $key ) {
			update_option( 'rct_key', $key );
		}
		public function get_key() {
			$key = get_option( 'rct_key', '' );
			return $key;
		}
		public function save_key_handler() {
			$sanitized_key = $this->sanitize_key( $_POST[ 'key' ] );
			update_option( 'rct_key', $sanitized_key );
			echo '1';
			wp_die();
		}
		public function sanitize_key( $key ) {
			$sanitized_key = sanitize_key( $key );
			return $sanitized_key;
		}
	}
	Content_Trail_Admin::get_instance();
}
