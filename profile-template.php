<?php
/*
*
* View Name: Login by Zalo options view
*
*/

if (!defined('ABSPATH')) exit;

$error_msg = "";
if(isset($_POST["user_submit_lz"])) {
    if ( ! isset( $_POST['nonce_field'] ) || ! wp_verify_nonce( $_POST['nonce_field'], 'update_user_info_action' ) ) {
        $error_msg = "Nonce is not valid!";
    }
    else {
        $user_id = get_current_user_id();
        if (isset($_POST['email']) && !empty($_POST['email'])){
            $email = sanitize_text_field( $_POST["email"] );
            wp_update_user( array ('ID' => $user_id, 'user_email' => $email ) );
        }

        if (isset($_POST['phone']) && !empty($_POST['phone'])){
            $phone = sanitize_text_field( $_POST["phone"] );
            update_user_meta( $user_id, 'billing_phone', $phone );
            // reload page after saving
            wp_redirect(apply_filters('lz_get_redirect_after_login', get_site_url()));
            exit;
        }
    }
    
}
$user = wp_get_current_user();
$phone_number = "";
if(isset( get_user_meta($user->ID)['billing_phone']['0']) && !empty( get_user_meta($user->ID)['billing_phone']['0'])){
    $phone_number =  get_user_meta($user->ID)['billing_phone']['0'];
}
$username = $user->display_name;
$email = $user->user_email
?>
    <head>
        <link rel="stylesheet" type="text/css" href=<?php echo (plugin_dir_url( __FILE__ ).'assets/css/bootstrap.min.css'); ?> /> 
        <link rel="stylesheet" type="text/css" href=<?php echo (plugin_dir_url( __FILE__ ).'assets/css/bootstrap.min.css.map'); ?> /> 
    </head>

    <body>
        <div class="wrapper">	
            <div class="container">
                <div class="row justify-content-around">
                    <form id='update_form_submit' method="POST" class="col-md-5 bg-light p-3 my-3">
                        <?php if ( !empty( $error_msg ) ) : ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error_msg; ?>
                            </div>
                        <?php endif; ?>
                        <h2 class="text-center text-uppercase h-3 py-3"> Update User Info</h2>                       					
                        <div class="form-group">                  
                            <label for="name">Display name</label>
                            <input name="name" type="text" id="name" placeholder="Display name" class="form-control"  value="<?php echo esc_attr($username); ?>" disabled  />
                        </div>	
                                 
                        <div class="form-group">                  
                            <label for="phone">Phone *</label>
                            <input name="phone" type="phone" id="phone" placeholder="Phone number" class="form-control"  value="<?php echo esc_attr($phone_number); ?>" pattern="[0-9]{10}|[0-9]{11}|[0-9]{12}" required  />
                        </div>	

                        <div class="form-group">                  
                            <label for="email">Email</label>
                            <input name="email" type="email" id="email" placeholder="name@example.com" class="form-control"  value="<?php echo esc_attr($email); ?>"  />
                        </div>	
             
                                            
                        <div class="form-footer">
                            <button type="submit" name="user_submit_lz" id="submit" class="btn btn-primary btn-block" value="Save Changes"><?php _e('Save Changes'); ?></button>
                        </div>

                        <?php wp_nonce_field( 'update_user_info_action', 'nonce_field' ); ?>
                    </form>
                </div>
            </div>	
        </div>
    </body>
    <script>
        var phone_input = document.getElementById("phone");

        phone_input.addEventListener('input', () => {
        phone_input.setCustomValidity('');
        phone_input.checkValidity();
        });

        phone_input.addEventListener('invalid', () => {
        if(phone_input.value === '') {
            phone_input.setCustomValidity('Enter phone number');
        } else {
            phone_input.setCustomValidity('Your phone number is not valid');
        }
        });
    </script>
    <style>

    </style>
    
<?

