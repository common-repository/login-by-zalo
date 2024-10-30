<?php
/*
*
* View Name: Login by Zalo options view
*
*/

if (!defined('ABSPATH')) exit;

global $lz_options; 
extract($lz_options); ?>

	<div class="wrap ufa-wrap">
	<?php do_action('lz_option_screen_header'); ?>
	
		<div class="ufa-container">
		<?php $this->lz_render_settings_view('options-header'); ?>
			
		<?php $this->lz_render_settings_view('options-menu'); ?>
		
			<div class="col-md-12">
				<form method="POST" autocompleted="off" class="ufa-form">
					<div class="form-header">
						<div class="row">
							<div class="col-md-12 col-border-bottom row-margin-bottom">
								<h2><?php echo esc_html( __('Zalo Settings', 'login-by-zalo') ); ?></h2>
							</div>
						</div>
					</div>
					
				<?php do_action('lz_option_form_header'); ?>
					
					<div class="form-content">						
						<div class="form-group">
							<div class="row">
								<div class="col-md-3 col-header"><?php echo esc_html( __('App ID', 'login-by-zalo') ); ?></div>
								<div class="col-md-9 col-content col-border-bottom">
									<input name="app_id" type="text" id="app_id" placeholder="<?php echo esc_html( __("Zalo App ID", 'bluecoral') ); ?>" class="regular-text code form-control" style="max-width: 25rem;" value="<?php echo esc_attr($app_id); ?>" />
								</div>
							</div>
						</div>	
						
						<div class="form-group">
							<div class="row">
								<div class="col-md-3 col-header"><?php echo esc_html( __('App Secret', 'login-by-zalo') ); ?></div>
								<div class="col-md-9 col-content col-border-bottom">
									<input name="secret" type="text" id="secret" placeholder="<?php echo esc_html( __('Zalo App Secret', 'bluecoral') ); ?>" class="regular-text code form-control" style="max-width: 25rem;" value="<?php echo esc_attr($secret); ?>" />
									<p class="description">
										<?php echo __( sprintf( 'Enter your Zalo Application credentials. Learn how to access your <a href="%s" target="_blank">Website integration information</a>.', 'https://developers.zalo.me/docs/api/social-api/tham-khao/cau-hinh-app-callback-url-post-4672' ), 'login-by-zalo' ); ?>
									</p>
								</div>
							</div>
						</div>	
						
						<div class="form-group">
							<div class="row">
								<div class="col-md-3 col-header"><?php echo esc_html( __('Redirect URL', 'login-by-zalo') ); ?></div>
								<div class="col-md-9 col-content col-border-bottom">
									<code><?php echo $redirect_url; ?></code>
									<p class="description"><?php echo __('Please set this <strong>redirect URL</strong> to your application settings.', 'login-by-zalo'); ?></p>
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<div class="row">
								<div class="col-md-3 col-header"><?php echo esc_html( __('Signin Button', 'login-by-zalo') ); ?></div>
								<div class="col-md-9 col-content col-border-bottom">
									<p>
										<label for="display_admin">
											<input name="display_admin" type="hidden" value="0" />
											<input id="display_admin" name="display_admin" type="checkbox" class="display_admin" value="1" <?php echo ($display_admin === 1) ? 'checked' : ''; ?> />
											<?php echo esc_html( __('Show Signin Button in Admin login form.', 'login-by-zalo') ); ?>
										</label>	
										<div class="display_admin-position <?php echo ($display_admin === 0) ? 'hidden' : ''; ?>">									
											<select name="display_admin_pos" class="form-control">
												<option value="0" <?php selected((int) @$display_admin_pos, 0); ?>><?php echo esc_html( __('Bottom position', 'login-by-zalo') ); ?></option>
												<option value="1" <?php selected((int) @$display_admin_pos, 1); ?>><?php echo esc_html( __('Middle position', 'login-by-zalo') ); ?></option>
												<option value="2" <?php selected((int) @$display_admin_pos, 2); ?>><?php echo esc_html( __('Top position', 'login-by-zalo') ); ?></option>
											</select>
										</div>
									</p>
									
									<p>
										<label for="display_front">
											<input name="display_front" type="hidden" value="0" />
											<input id="display_front" name="display_front" type="checkbox" class="display_front" value="1" <?php echo ($display_front === 1) ? 'checked' : ''; ?> />
											<?php echo esc_html( __('Show Signin Button in Site login form.', 'login-by-zalo') ); ?>
										</label>
										<div class="display_front-position <?php echo ($display_front === 0) ? 'hidden' : ''; ?>">										
											<select name="display_front_pos" class="form-control">
												<option value="0" <?php selected((int) @$display_front_pos, 0); ?>><?php echo esc_html( __('Bottom position', 'login-by-zalo') ); ?></option>
												<option value="1" <?php selected((int) @$display_front_pos, 1); ?>><?php echo esc_html( __('Middle position', 'login-by-zalo') ); ?></option>
												<option value="2" <?php selected((int) @$display_front_pos, 2); ?>><?php echo esc_html( __('Top position', 'login-by-zalo') ); ?></option>
											</select>
										</div>
									</p>
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<div class="row">
								<div class="col-md-3 col-header"><?php echo esc_html( __('Usage', 'login-by-zalo') ); ?></div>
								<div class="col-md-9 col-content col-border-bottom">									
									<p>
										<label for="login_show">
											<input name="login_show" type="hidden" value="0" />
											<input id="login_show" name="login_show" type="checkbox" class="login_show" value="1" <?php echo ($login_show === 1) ? 'checked' : ''; ?> />
											<?php echo esc_html( __('Show Signin Button after Login.', 'login-by-zalo') ); ?>
										</label>
									</p>
									<code>[login_zalo class="your-class"]</code>
									<p style="margin-top: 10px;margin-bottom: 0;"><?php echo esc_html( __('Use shortcode to display login button.', 'login-by-zalo') ); ?></p>
									<ul style="list-style: disc;">
										<li><?php echo __('<strong>class</strong>: define your custom class.', 'login-by-zalo'); ?></li>
									</ul>
								</div>
							</div>
						</div>
						
					<?php do_action('lz_option_form_items'); ?>	
					</div>
					
				<?php do_action('lz_option_form_end'); ?>
					
					<div class="form-footer">
						<button type="submit" name="submit_lz" id="submit" class="btn btn-primary" value="Save Changes"><?php echo esc_html( __('Save Changes') ); ?></button>
					</div>
					
				<?php do_action('lz_option_form_footer'); ?>
										
				<?php wp_nonce_field( 'lz_login_settings_action', 'lz_login_settings_name' ); ?>
				
				</form>
			</div>
		</div>
	
	<?php do_action('lz_option_screen_footer'); ?>
	</div>