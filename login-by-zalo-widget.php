<?php
/*
*
* Login by Zalo Widget
*
*/

if (!defined('ABSPATH')) exit; 

if (!class_exists('Login_Zalo_Widget')) {
	class Login_Zalo_Widget extends WP_Widget {
		/**
		* Class Construct
		*/
		public function __construct() {	
			parent::__construct(
				'login_zalo',
				'Login by Zalo',
				array(
					'description' => __('Widget to show Zalo button.', 'login-by-zalo'),
				)
			);
		}
		 
		function form($instance = array()) {
			$title = (!empty($instance['title'])) ? esc_attr( sanitize_text_field( $instance['title'] ) ) : '';
			
			echo '<p>Title <input type="text" class="widefat" name="'.$this->get_field_name('title').'" value="'.$title.'"/></p>';
		}
		
		function update($new_instance = array(), $old_instance = array()) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			
			return $instance;
		}		
		 
		function widget($args, $instance) {
			extract($args);
			$title = apply_filters('widget_title', $instance['title']);
	 
			echo $before_widget;
			echo $before_title.$title.$after_title;
			echo do_shortcode('[login_zalo class="login-zalo-widget-wrap"]');
			echo $after_widget;
	 
		}
	}
}