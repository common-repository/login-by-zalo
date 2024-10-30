<?php
/*
*
* Plugin Name: Login by Zalo
* Plugin URI: https://bluecoral.vn/plugin/login-by-zalo
* Description: Integrate Zalo Login at your website easiest and simplest possible way.
* Author: Blue Coral
* Author URI: https://bluecoral.vn
* Contributors: bluecoral, nguyenrom
* Version: 2.1
* Text Domain: login-by-zalo
*
*/

if (!defined('ABSPATH')) exit; 

if ( !defined( "LOGIN_BY_ZALO_FILE" ) ) {
	define( "LOGIN_BY_ZALO_FILE", __FILE__ );
}

if (!class_exists('Login_Zalo')) {
    class Login_Zalo {
        
        private static $_instance;
        private $domain ;
        private $endpoint ; 
        private $option_key ;
        private $meta_key_id ;
        private $meta_key_data ;
        private $meta_key_avatar ;
        private $options ;
        

        public static function get_instance() {

            if ( null == self::$_instance ) {
                self::$_instance = new Login_Zalo();
            }
    
            return self::$_instance;
    
        }
        /**
        * Class Construct
        */
        public function __construct() { 
            $this->domain = 'login-by-zalo';
            $this->endpoint = 'login-zalo'; 
            $this->option_key = 'lz_options';
            $this->meta_key_id = '_zalo_id';
            $this->meta_key_data = '_zalo_data';
            $this->meta_key_avatar = '_zalo_avatar';
            $this->options = $this->lz_get_options();
            
            require_once(trailingslashit(plugin_dir_path( __FILE__ )).'libraries/vendor/autoload.php');
            
            add_action('admin_init', array($this, 'lz_admin_init'));
            add_filter('user_contactmethods', array($this, 'add_contact_methods'));
            // functions
            $this->lz_settings_page();
            $this->lz_endpoint();
            
            if (!$this->lz_premium()) {
                $this->lz_fields();
                $this->lz_loginform();
                $this->lz_shortcode();
                $this->lz_widget();         
            }
            $this->define_page_hooks();
            $this->load_dependencies();
            
        }
        
        public static function getInstance(): Login_Zalo {
            if (!empty(static::$_instance)) {
                return static::$_instance;
            }
            return new static();
        }
        
        function lz_premium() {
            return class_exists('Login_Social_Premium');
        }
        
        
        /**
        * Functions
        */      
        function lz_get_options() {
            return get_option($this->option_key, $this->lz_get_default_options());
        }   
        
        function lz_get_default_options() {
            return array(
                'app_id' => '',
                'secret' => '',
                'display_admin' => 0,
                'display_admin_pos' => 0,
                'display_front' => 0,
                'display_front_pos' => 0,
                'login_show' => 0,
                'show' => 1,
            );
        }
        
        function lz_update_options($values) {
            $values = array_merge($this->options, $values);
            return update_option($this->option_key, $values);
        }

        function lz_settings_page() {
            add_action('admin_menu', array($this, 'lz_register_settings_pages'), 10);
        }
        
        function lz_admin_init() {
            add_filter('plugin_action_links_'.plugin_basename(__FILE__), array($this, 'lz_render_plugin_action_links'), 10, 1);
            
            $this->lz_submit_settings_data();
        }
        
        function lz_render_plugin_action_links($links = array()) {
            array_unshift($links, '<a href="'.esc_url( admin_url('admin.php?page='.$this->domain)).'">'.__('Settings').'</a>');
            
            return $links;
        }
        
        function lz_get_redirect_after_login() {
            return apply_filters('lz_get_redirect_after_login', get_site_url());
        }
        
        function lz_empty_settings() {
            return (empty($this->options['app_id']) || empty($this->options['secret']));
        }
        
        function v4() {
            return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

                // 32 bits for "time_low"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),

                // 16 bits for "time_mid"
                mt_rand(0, 0xffff),

				// 48 bits for "node"
				mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
			);
		}
		
		
		/**
		* Endpoint
		*/	
		function lz_endpoint() {
			add_filter('init', array($this, 'lz_endpoint_rewrite_rules'), 10);
			add_filter('template_include', array($this, 'lz_endpoint_template_include'), 10);
		}
		
		function lz_endpoint_rewrite_rules() {
			add_rewrite_rule($this->endpoint.'/?$', 'index.php?login-action=zalo', 'top');	
			add_rewrite_tag('%login-action%', '([^&]+)');	
			flush_rewrite_rules();			
		}
		
		function lz_endpoint_template_include($template) {
			$login = get_query_var('login-action');
			
			if ($login == 'zalo') return $this->lz_login_zalo();
			
			return $template;
		}
		
		public function lz_endpoint_get_url() {
			return apply_filters('lz_endpoint_get_url', get_site_url().'/'.$this->endpoint, $this->options);
		}
		
		function lz_login_zalo() {
			$config = array(
				'app_id' => $this->options['app_id'],
				'app_secret' => $this->options['secret'],
				// 'callback_url' => $this->lz_endpoint_get_url(),
			);
			
			$zalo = new Zalo\Zalo($config);
			$helper = $zalo->getRedirectLoginHelper();
			
			if (!isset($_GET['code'])) return $this->lz_login_zalo_redirect($helper);
			
			$code = sanitize_text_field( $_GET['code'] );
			$access_token = $helper->getZaloToken($this->lz_endpoint_get_url());
			
			if (!$access_token) return $this->lz_login_zalo_redirect($helper);
			
			$fields = array(
				'fields' => 'id, name, picture.type(large)',
			);
			$response = $zalo->get(Zalo\ZaloEndPoint::API_GRAPH_ME, $access_token, $fields);
			$result = $response->getDecodedBody();
			
			return $this->lz_login_zalo_retrieve($result);
		}
		
		function lz_login_zalo_redirect($helper) {
			$codeChallenge = null;
			$state = "my_state";
			// redirect to login screen
			wp_redirect($helper->getLoginUrl($this->lz_endpoint_get_url(), $codeChallenge, $state));
			exit;
		}
		
		function lz_login_zalo_retrieve($data = array()) {
			$user_id = get_current_user_id();
			$data_id = sanitize_text_field( $data['id'] );

			if ($user_id > 0) {
				$check = $this->lz_login_zalo_check_id( $data_id );
				
				if (empty($check)) {
					// meta						
					update_user_meta($user_id, $this->meta_key_data, $data);
					update_user_meta($user_id, $this->meta_key_id, $data_id );
					
					if (isset($data['picture']['data']['url']))
						$picture_url = sanitize_text_field( $data['picture']['data']['url'] );
						update_user_meta($user_id, $this->meta_key_avatar, $picture_url);
					
					return wp_redirect($this->lz_get_redirect_after_login());
				} else if ($check == $user_id) {					
					if (isset($data['picture']['data']['url']))
						$picture_url = sanitize_text_field( $data['picture']['data']['url'] );
						update_user_meta($user_id, $this->meta_key_avatar, $picture_url );
					
					return wp_redirect($this->lz_get_redirect_after_login());
				} else {
					wp_logout();
					wp_clear_auth_cookie();
					$user = wp_set_current_user($check);
					
					$user_id = get_current_user_id();
					
					if (isset($data['picture']['data']['url']))
						$picture_url = $data['picture']['data']['url'];
						update_user_meta($user_id, $this->meta_key_avatar, $picture_url);
					
					wp_set_auth_cookie($user_id);
					do_action('wp_login', $user->user_login, $user);
					
					return wp_redirect($this->lz_get_redirect_after_login());
				}
			} else {

				$user_id = $this->lz_login_zalo_check_id( $data_id );
				
				if (empty($user_id)) {
					$user_id = wp_insert_user(
						array(
							'user_pass' => $this->v4(),
							'user_login' => $this->lz_login_zalo_user_name( $data_id ),
							'user_nicename' => $data['name'],
							'first_name' => $data['name'],
							'last_name' => '',
							'display_name' => $data['name'],
						)					
					);
					
					if ($user_id > 0) {												
						wp_clear_auth_cookie();
						$user = wp_set_current_user($user_id);
						
						// meta						
						update_user_meta($user_id, $this->meta_key_data, $data);
						update_user_meta($user_id, $this->meta_key_id, $data_id );
						
						if (isset($data['picture']['data']['url']))
							$picture_url = $data['picture']['data']['url'];
							update_user_meta($user_id, $this->meta_key_avatar, $picture_url);
						
						wp_set_auth_cookie($user_id);
						do_action('wp_login', $user->user_login, $user);

						do_action('login_zalo_register', $user_id, $data);
					}
					
					return wp_redirect($this->lz_get_redirect_after_login());
				} else {
					wp_clear_auth_cookie();
					$user = wp_set_current_user($user_id);
					
					if (isset($data['picture']['data']['url']))
						$picture_url = $data['picture']['data']['url'];
						update_user_meta($user_id, $this->meta_key_avatar, $picture_url);
					
					wp_set_auth_cookie($user_id);
					do_action('wp_login', $user->user_login, $user);
					
					return wp_redirect($this->lz_get_redirect_update_after_login());
				}
			}
		}
		
		function lz_login_zalo_check_id($zalo_id = '') {
			global $wpdb;
			
			return $wpdb->get_var(
				$wpdb->prepare('SELECT user_id FROM '.$wpdb->usermeta.' WHERE meta_key = %s AND meta_value = %s', esc_sql($this->meta_key_id), esc_sql($zalo_id))
			);
		}
		
		function lz_login_zalo_user_name($zalo_id = '') {
			return 'zalo-'.$zalo_id;
		}
		
		
		/**
		* Custom fields
		*/	
		function lz_fields() {
			add_filter('get_avatar_url', array($this, 'lz_get_avatar_url'), 10, 3);
		}
		
		function lz_get_avatar_url($url, $id_or_email, $args) {
			if( is_object($id_or_email) && isset($id_or_email->comment_ID))
			{
				$id_or_email = $id_or_email->comment_ID ;
			}
			$user = get_user_by('id', absint($id_or_email));
			$zalo_avatar = get_user_meta($user->ID, $this->meta_key_avatar, true);
			
			return (!empty($zalo_avatar)) ? $zalo_avatar : $url;
		}
		
		
		/**
		* Widget
		*/
		function lz_widget() {
			add_action('widgets_init', array($this, 'lz_widgets_init'));
		}
		
		function lz_widgets_init() {
			require_once(trailingslashit(plugin_dir_path( __FILE__ )).'login-by-zalo-widget.php');
			
			register_widget('Login_Zalo_Widget');
		}
		
		
		/**
		* Loginform
		*/
		function lz_loginform() {
			if ($this->lz_empty_settings()) return;
			
			if ($this->options['display_admin'] == 1) {
				switch ($this->options['display_admin_pos']) {
					case 0: // Bottom position
						add_action('login_footer', array($this, 'lz_loginform_render_admin_bottom'));
						break;
						
					case 1: // Middle position
						add_action('login_form', array($this, 'lz_loginform_render_admin'));
						break;
						
					case 2: // Top position
						add_action('login_footer', array($this, 'lz_loginform_render_admin_top'));
						break;
				}
			}
			
			if ($this->options['display_front'] == 1) {
				switch ($this->options['display_front_pos']) {
					case 0: // Bottom position
						add_filter('login_form_bottom', array($this, 'lz_loginform_render'), 10, 1);
						add_action('woocommerce_login_form_start', array($this, 'lz_loginform_render'), 10);
						break;
						
					case 1: // Middle position
						add_filter('login_form_middle', array($this, 'lz_loginform_render'), 10, 1);
						add_action('woocommerce_login_form', array($this, 'lz_loginform_render'), 10);
						break;
						
					case 2: // Top position
						add_filter('login_form_top', array($this, 'lz_loginform_render'), 10, 1);
						add_action('woocommerce_login_form_end', array($this, 'lz_loginform_render'), 10);
						break;
				}
			}
		}
		
		function lz_loginform_render($args = array()) {
			ob_start();
			
			do_action('lz_login_form_button_front_before', $this->options, $args);
			
			echo do_shortcode('[login_zalo]');
			
			do_action('lz_login_form_button_front_after', $this->options, $args);
			
			return ob_get_clean();
		}
		
		function lz_loginform_render_admin() {
			if ($this->lz_premium()) {
				return;
			}
			do_action('lz_login_form_button_admin_before', $this->options);
			
			echo do_shortcode('[login_zalo class="wp-login"]');
			
			do_action('lz_login_form_button_admin_after', $this->options);
		}
		
		function lz_loginform_render_admin_bottom() {
			if ($this->lz_premium()) {
				return;
			}
			wp_enqueue_script('button-bottom-lz', plugins_url('assets/js/wp-login-bottom.js', LOGIN_BY_ZALO_FILE), '', true);			
			
			do_action('lz_login_form_button_admin_before', $this->options);
			
			echo do_shortcode('[login_zalo class="wp-login login-zalo-wrap-bottom"]');
			
			do_action('lz_login_form_button_admin_after', $this->options);
		}
		
		function lz_loginform_render_admin_top() {
			if ($this->lz_premium()) {
				return;
			}
			wp_enqueue_script('button-bottom-lz', plugins_url('assets/js/wp-login-top.js', LOGIN_BY_ZALO_FILE), '', true);			
			
			do_action('lz_login_form_button_admin_before', $this->options);
			
			echo do_shortcode('[login_zalo class="wp-login login-zalo-wrap-bottom"]');
			
			do_action('lz_login_form_button_admin_after', $this->options);
		}
		
		
		/**
		* Shortcode
		*/		
		function lz_shortcode() {
			add_shortcode('login_zalo', array($this, 'lz_button'));
		}
		
		function lz_button($args = array(), $content = '') {			
			wp_enqueue_style('button-lz', plugins_url('assets/css/button.css', LOGIN_BY_ZALO_FILE), array(), null, 'all');
			
			if ($this->lz_button_hide()) return'';
			
			$args = apply_filters('lz_button_args', $args);
			$button_label = apply_filters('lz_button_label', __('Log in with Zalo', 'login-by-zalo'));
			$endpoint = $this->lz_endpoint_get_url();
			$class = 'login-zalo-wrap';
			if (isset($args['class'])) $class .= ' '. sanitize_text_field( $args['class'] );
			
			$button ='<div class="'.esc_attr($class).'">';
			$button .= '<a class="zalo-button" href="'.esc_url($endpoint).'">';
			$button .= '<img class="zalo-button-icon" src="'.plugins_url('assets/images/zalo-logo.png', LOGIN_BY_ZALO_FILE).'" />';
			$button .= '<label class="zalo-button-label">'.$button_label.'</label>';
			$button .= '</a>';
			$button .= '</div>';
			
			return apply_filters('lz_button_html', $button, $args = array(), $content = '', $endpoint);
		}
		
		function lz_button_hide() {
			$user_id = get_current_user_id();
			
			return ($user_id > 0 && $this->options['login_show'] == 0);
		}
        
        /**
        * Settings screen
        */  
        function lz_register_settings_pages() {
            if ($this->lz_premium()) return;            

            add_menu_page(__('Social login & register settings', 'login-by-zalo'), __('Social Login', 'login-by-zalo'), 'manage_options', $this->domain, array($this, 'lz_render_settings_page'), 'dashicons-share');
        }
        
        function lz_settings_page_menu() {
            return apply_filters(
                'lz_settings_page_menu',
                array(
                    $this->domain => __('Zalo Settings', 'login-by-zalo'),
                )
            );
        }

        
        function lz_render_settings_page() {
            if ($this->lz_premium()) return;
            
            $this->lz_render_settings_page_header();
            $this->lz_render_settings_view('options');      
            $this->lz_render_settings_page_footer();
        }


        function lz_render_settings_view($file_name = '', $once = true) {
            $file = apply_filters(
                'lz_settings_view_file',
                trailingslashit(plugin_dir_path(__FILE__)).'views/'.$file_name.'.php',
                $file_name
            );
            
            if (file_exists($file)) {
                if ($once) require_once($file);
                else require($file);
            }
        }
        
        function lz_render_settings_page_header() {
            global $lz_options;
            
            // css
            wp_enqueue_style('bootstrap', plugins_url($this->domain.'/assets/css/bootstrap.min.css'), array(), null, 'all');
            wp_enqueue_style('admin-lz', plugins_url($this->domain.'/assets/css/admin.css'), array(), null, 'all');         
            
            // js
            wp_enqueue_script('bootstrap', plugins_url($this->domain.'/assets/js/bootstrap.bundle.min.js'), array('jquery'), true);
            wp_enqueue_script('admin-lz', plugins_url($this->domain.'/assets/js/admin.js'), array('jquery'), true);
            
            $lz_options = $this->options;
            $lz_options['redirect_url'] = $this->lz_endpoint_get_url();
        }
        
        function lz_render_settings_page_footer() {         
        }
        
        function lz_submit_settings_data() {    
            global $lz_options;
            
            if (!is_admin()) return;
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['submit_lz'])) {
                    $post_data = array();
                    
                    if (isset($_POST['app_id'])) $post_data['app_id'] = sanitize_text_field($_POST['app_id']);
                    if (isset($_POST['secret'])) $post_data['secret'] = sanitize_text_field($_POST['secret']);  
                    if (isset($_POST['display_admin'])) $post_data['display_admin'] = (int) $_POST['display_admin'];
                    if (isset($_POST['display_admin_pos'])) $post_data['display_admin_pos'] = (int) $_POST['display_admin_pos'];
                    if (isset($_POST['display_front'])) $post_data['display_front'] = (int) $_POST['display_front'];
                    if (isset($_POST['display_front_pos'])) $post_data['display_front_pos'] = (int) $_POST['display_front_pos'];
                    if (isset($_POST['login_show'])) $post_data['login_show'] = (int) $_POST['login_show'];
                    if ($post_data['display_admin'] === 0) $post_data['display_admin_pos'] = 0;
                    if ($post_data['display_front'] === 0) $post_data['display_front_pos'] = 0;
                    
                    $post_data = apply_filters('lz_post_data', $post_data, $this->options);
                    $this->lz_update_options($post_data);
                    $lz_options = $this->options;
                    
                    // reload page after saving
                    wp_redirect(admin_url('admin.php?page='.$this->domain));
                    exit;
                }
            }
        }
        
        function lz_render_info_page() {
            if ($this->lz_premium()) return;
            
            $this->lz_render_settings_page_header();
            $this->lz_render_settings_view('options');      
            $this->lz_render_settings_page_footer();
        }
    
        function lz_get_redirect_update_after_login() {
            $user_id = get_current_user_id();
            $phone_number = get_user_meta( $user_id, 'billing_phone', true );
  
            if( !isset($phone_number) || empty($phone_number) ){
                $this->create_page();
                return apply_filters('lz_get_redirect_after_login', get_site_url().'/'.'update-profile');
            }
            return apply_filters('lz_get_redirect_after_login', get_site_url());
        }

        function create_page(){
            $pageGuid = get_site_url() . "/update-profile";
            $my_post  = array( 'post_title'     => 'Update Profile',
                            'post_type'      => 'page',
                            'post_name'      => 'update-profile',
                            'post_content'   => "",
                            'post_status'    => 'publish',
                            'comment_status' => 'closed',
                            'ping_status'    => 'closed',
                            'post_author'    => 1,
                            'menu_order'     => 0,                             
                            'page_template'  => 'profile-template.php', // Assign page template
                            'guid'           => $pageGuid );

            $the_query = new WP_Query( array('pagename'     => 'update-profile') );
            $is_post=$the_query->have_posts();
            if( !$is_post){
                wp_insert_post( $my_post );
            }
            
        }
        
        function load_dependencies(){
            $plugin = new Page_Template();
        }
        function define_page_hooks(){
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'login-by-zalo/page-template.php';
        }
    
        function add_contact_methods($profile_fields) {
            $profile_fields['billing_phone'] = 'Phone number';
            return $profile_fields;
         }     
    }
    
    Login_Zalo::getInstance();
}

