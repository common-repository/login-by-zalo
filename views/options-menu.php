<?php
/*
*
* View Name: Login by Zalo options menu view
*
*/

if (!defined('ABSPATH')) exit; ?>

	<div class="col-md-12 ufa-nav">
		<ul class="nav">
		<?php foreach ($this->lz_settings_page_menu() as $k => $menu_item) { ?>
			<li class="nav-item <?php echo isset( $_GET['page'] ) && (sanitize_text_field( $_GET['page'] ) == $k) ? 'active' : ''; ?>" style="margin-right: 1.5em;">
				<a href="<?php echo admin_url('admin.php?page='.$k); ?>"><?php echo $menu_item; ?></a>
			</li>
		<?php } ?>
		
			<li class="nav-item nav-item-support">
				<a href="mailto:support@bluecoral.vn" data-toggle="tooltip" data-placement="top" title="<?php echo esc_html( __('Need Support?', 'login-by-zalo') ); ?>">
					<i class="dashicons dashicons-sos"></i> 
					<span class="label"><?php echo esc_html( __('Need Support?', 'login-by-zalo') ); ?></span>
				</a>
			</li>
			<li class="nav-item nav-item-beer">
				<a href="https://go.bluecoral.vn/buymeabeer" target="_blank" data-toggle="tooltip" data-placement="top" title="<?php echo esc_html( __('Buy me a beer!', 'login-by-zalo') ); ?>">
					<i class="dashicons dashicons-beer"></i>
					<span class="label"><?php echo esc_html( __('Buy me a beer!', 'login-by-zalo') ); ?></span>
				</a>
			</li>
		</ul>
	</div>