<?php

class RCT_Widget_Personalisation extends WP_Widget {

	function __construct() {
		parent::__construct(
		// Base ID of your widget
		'rct_personalisation',
		// Widget name will appear in UI
  __( 'URP - Personalisation', 'content_trail' ),
		// Widget description
	  array( 'description' => __( 'Content Personalisation widget', 'content_trail' ), )
		);
	}

	// Creating widget front-end
	public function widget( $args, $instance ) {
		wp_enqueue_script( 'rct-custom' );
		wp_enqueue_script('jquery-effects-core');
		wp_enqueue_script('jquery-effects-slide');

		echo $args[ 'before_widget' ];
		echo '<div class="RecoSense_personalisation_container" ></div>';
		echo $args[ 'after_widget' ];
	}

}
