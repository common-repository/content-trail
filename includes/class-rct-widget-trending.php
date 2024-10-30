<?php

class RCT_Widget_Trending extends WP_Widget {

	function __construct() {
		parent::__construct(
		// Base ID of your widget
		'rct_trending',
		// Widget name will appear in UI
  __( 'URP - Trending', 'content_trail' ),
		// Widget description
	  array( 'description' => __( 'Content Trending widget', 'content_trail' ), )
		);
	}

	// Creating widget front-end
	public function widget( $args, $instance ) {
		wp_enqueue_script( 'rct-custom' );
		wp_enqueue_script('jquery-effects-core');
		wp_enqueue_script('jquery-effects-slide');
		echo $args[ 'before_widget' ];
		echo '<div class="RecoSense_trending_container" ></div>';
		echo $args[ 'after_widget' ];
	}

}
