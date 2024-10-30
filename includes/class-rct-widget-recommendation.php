<?php

class RCT_Widget_Recommendation extends WP_Widget {

	function __construct() {
		parent::__construct(
		// Base ID of your widget
		'rct_recommendation',
		// Widget name will appear in UI
  __( 'URP - Recommendation', 'content_trail' ),
		// Widget description
	  array( 'description' => __( 'Content Recommendation widget', 'content_trail' ), )
		);
	}

	// Creating widget front-end
	public function widget( $args, $instance ) {
		wp_enqueue_script( 'rct-custom' );
		wp_enqueue_script('jquery-effects-core');
		wp_enqueue_script('jquery-effects-slide');
		echo $args[ 'before_widget' ];
		echo '<div class="RecoSense_container" ></div>';
		echo $args[ 'after_widget' ];
	}

}
