<?php

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die;
}

if ( !class_exists( 'Content_Trail' ) ) {

	class Content_Trail {

		protected static $instance = null;

		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'init_localization' ) );
			add_action( 'widgets_init', array( $this, 'widgets_init' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'post_updated', array( $this, 'sync_updated_post' ) );

			add_action( 'wp_ajax_rct_url_to_post_id', array( $this, 'url_to_post_id_ajax_handler' ) );
			add_action( 'wp_ajax_nopriv_rct_url_to_post_id', array( $this, 'url_to_post_id_ajax_handler' ) );
			add_action( 'wp_ajax_rct_api_call', array( $this, 'api_ajax_handler' ) );
			add_action( 'wp_ajax_nopriv_rct_api_call', array( $this, 'api_ajax_handler' ) );

			//Include widget classes
			require_once(trailingslashit( RCT_PATH ) . 'includes/class-rct-widget-recommendation.php');
			require_once(trailingslashit( RCT_PATH ) . 'includes/class-rct-widget-personalisation.php');
			require_once(trailingslashit( RCT_PATH ) . 'includes/class-rct-widget-trending.php');
		}

		public function widgets_init() {
			register_widget( 'RCT_Widget_Recommendation' );
			register_widget( 'RCT_Widget_Personalisation' );
			register_widget( 'RCT_Widget_Trending' );
		}

		public function enqueue_scripts() {
			wp_register_script( 'recoprint', 'https://wpplugin.recosenselabs.com/sdk/js/recoprint.js', array(), false, true );
			wp_register_script( 'rct-custom', plugin_dir_url( trailingslashit( RCT_PLUGIN_BASENAME ) ) . 'public/js/rct-custom.js', array( 'jquery', 'recoprint' ), RCT_VERSION, true );
			wp_register_script( 'rct-personalisation', plugin_dir_url( trailingslashit( RCT_PLUGIN_BASENAME ) ) . 'public/js/rct-widget-personalisation.js', array( 'rct-custom' ), RCT_VERSION, true );
			wp_register_script( 'rct-recommendation', plugin_dir_url( trailingslashit( RCT_PLUGIN_BASENAME ) ) . 'public/js/rct-widget-recommendation.js', array( 'rct-custom' ), RCT_VERSION, true );
			wp_register_script( 'rct-trending', plugin_dir_url( trailingslashit( RCT_PLUGIN_BASENAME ) ) . 'public/js/rct-widget-trending.js', array( 'rct-custom' ), RCT_VERSION, true );
			
			$data = array(
				'api_url'	 => trailingslashit( RCT_API_URL ),
				'guid'		 => urlencode( RCT_GUID ),
				'ajax_url'	 => admin_url( 'admin-ajax.php' ),
				'item_id'	 => get_the_ID(),
			);
			wp_localize_script( 'recoprint', 'rct_data', $data );
		}

		/**
		 * @return Content_Trail Returns the current instance of the class
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Initialize localization
		 */
		public function init_localization() {
			load_plugin_textdomain( 'content_trail' );
		}

		public function url_to_post_id_ajax_handler() {
			echo $sam = url_to_postid( $_POST[ "url" ] );
			wp_die();
		}

		/**
		 * Sync the post content to the API server when post is updated
		 * @param int $post_ID Post ID of the updated post
		 * @param WP_Post $post_after WP_Post object with updated content
		 * @param WP_Post $post_befre WP_Post object with previous content
		 */
		public function sync_updated_post( $post_ID) {
			$args = array(
			  'p'         => $post_ID,
			  'post_type' => 'any'
			);
			
			global $post;
			$my_posts = new WP_Query($args);
			$argss	 = array(
				'numberposts' => -1
			);
			$req_post = array();
			$myposts						 = get_posts( $argss );
			$req_post[ 'id_not_to_delete' ]	 = array();
			$req_post[ 'guid' ]				 = $_SERVER[ 'SERVER_NAME' ];
			$req_post[ 'recosense_key' ]	 = $this->get_key();
			foreach ( $myposts as $posts ) {
				array_push( $req_post[ 'id_not_to_delete' ], $posts->ID );
			}
			$post_count	 = count( $my_posts->posts );
			if ( $post_count > 0 ) {
				$req_post[ 'items' ] = array();
				$i					 = 0;
				foreach ( $my_posts->posts as $post ) {
					$req_post[ 'items' ][ $i ][ 'item_id' ]				 = $post->ID;
					$req_post[ 'items' ][ $i ][ 'title' ]				 = $post->post_title;
					$req_post[ 'items' ][ $i ][ 'description' ]			 = $post->post_content;
					$req_post[ 'items' ][ $i ][ 'url' ]					 = $post->guid;
					$req_post[ 'items' ][ $i ][ 'post_date' ]			 = $post->post_date;
					$req_post[ 'items' ][ $i ][ 'post_modified_date' ]	 = $post->post_modified;
					$req_post[ 'items' ][ $i ][ 'image' ]				 = $this->getImage( 1, $post->post_content,$post->ID );
					$i++;
				}

				$req_post	 = json_encode( $req_post );
				$response	 = wp_remote_post( RCT_API_URL . "/polling", array(
					'method'	 => 'POST',
					'headers'	 => array( "Content-type" => "application/json" ),
					'body'		 => $req_post
				)
				);

				if ( is_wp_error( $response ) ) {
					$error_message = $response->get_error_message();
				}
			}
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
		public function get_key() {
			$key = get_option( 'rct_key', '' );
			return $key;
		}
		/**
		 * Ajax handler for the API calls
		 */
		public function api_ajax_handler() {
			$existing_endpoints	 = array(
				'recommendation',
				'personalisation',
				'trending',
			);
			$raw_endpoint		 = $_POST[ 'endpoint' ];
			if ( in_array( $raw_endpoint, $existing_endpoints ) ) {
				$endpoint = $raw_endpoint;
			}
			if ( isset( $_POST[ 'item_id' ] ) ) {
				$args = array(
					'item_id'	 => intval( $_POST[ 'item_id' ] ),
					'guid'		 => RCT_GUID,
				);
			}
			else if ( isset( $_POST[ 'device_id' ] ) ) {
				$args[ 'device_id' ] = sanitize_key( $_POST[ 'device_id' ] );
				$args[ 'guid' ] = RCT_GUID;
			}
			else{
				$args[ 'guid' ] = RCT_GUID;
			}
			$query_url	 = add_query_arg( $args, trailingslashit( RCT_API_URL ) . $endpoint );
			$cache_key	 = 'rct_api_' . md5( $query_url );
			//Try to fetch from cache if available
			if ( false === ( $return		 = get_transient( $cache_key ) ) ) {
				$request = wp_remote_get( $query_url );
				if ( !is_wp_error( $request ) ) {
					if ( $request[ 'response' ][ 'code' ] === 200 ) {
						$return = $request[ 'body' ];
						set_transient( $cache_key, $return, 5 * MINUTE_IN_SECONDS );
					}
				}
			}
			echo $return;
			wp_die();
		}

	}

	Content_Trail::get_instance();
}
