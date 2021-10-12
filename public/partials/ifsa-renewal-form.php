<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/public/partials
 * @author     Multidots <nishit.langaliya@multidots.com>
 * @link       #
 * @since      1.0.0
 */
$roles = array();
$user  = wp_get_current_user(); // getting & setting the current user
$roles = ( array ) $user->roles; // obtaining the role

 if (!is_user_logged_in() ||  $roles[0] != 'lc_member') { 
$login = wp_login_url();
     ?>
<p> Please <a href="<?php echo $login;?>">login</a> as LC member to renew your membership. </a>
 <?php }  else { ?>

<div class="ifsa_renewal_form-container">
    <form method="POST" action="<?php echo esc_url(site_url('/register'))?>" autocomplete="off">
        <div id="ifsa_renewal_form-1">
            <h3 class="ifsa_renewal_form-heading">
            <?php echo esc_html_e('Student Step Title Goes Here','ifsa'); ?>

            </h3>
            <p>
            <?php echo esc_html_e('Student status','ifsa'); ?>
                 
</p>
            <div class="ifsa_renewal_form-options">
                <label for="ifsa_renewal_form-yes">Yes</label>
                <input id="ifsa_renewal_form-yes" name="ifsa_renewal_toggle" value="Yes" type="radio">
                <div
                id="<?php echo esc_attr( 'ifsa-loading-renew-1', 'Ifsa_Member_Verification' ); ?>"
									style="display:none;">
								<img src="<?php echo esc_url( site_url() . '/wp-admin/images/loading.gif' ); ?>"
								     title="loading"/>
							</div>
                <label for="ifsa_renewal_form-no">No</label>
                <input id="ifsa_renewal_form-no" name="ifsa_renewal_toggle" value="no" type="radio">
            </div>
            <div id="ifsa_renewal_form-sucess" class="ifsa_renewal_form-sucess" style="display:none;">
                    <h3>Your request submitted successfully</h3>
                    <a href="<?php echo esc_url(site_url( '/' ))?>">
                        Back to home
                    </a>
                </div>
        </div>
        <div id="ifsa_renewal_form-2">
            <h3 class="ifsa_renewal_form-heading">
            <?php echo esc_html_e('Graduation day','ifsa'); ?>

            </h3> 
            <p class="ifsa_renewal_form-subheading">
            <?php echo esc_html_e(' When did you graduate?','ifsa'); ?>

                
</p>
            <div class="ifsa_renewal_form-options">      
                <input  id="ifsa_renewal_form-datepicker" type="text" placeholder="mm-dd-yy">
                <div id="ifsa_renewal_form-success" class="ifsa_renewal_form-success">
                    <button type="button" id="ifsa_renewal_form-success_btn">Renew</button>
                </div>
                <div
									id="<?php echo esc_attr( 'ifsa-loading-renew-2', 'Ifsa_Member_Verification' ); ?>"
									style="display:none;">
								<img src="<?php echo esc_url( site_url() . '/wp-admin/images/loading.gif' ); ?>"
								     title="loading"/>
							</div>
                <div id="ifsa_renewal_form-error" class="ifsa_renewal_form-error">
                    <h3>Sorry you can’be an IFSA member</h3>
                    <a href="<?php echo esc_url(site_url( '/' )) ?>">
                        Back to home
                    </a>
                </div>
                <div id="ifsa_renewal_form-sucess-2" class="ifsa_renewal_form-sucess-2" style="display:none;">
                    <h3>Your request submitted successfully</h3>
                    <a href="<?php echo esc_url(site_url( '/' ))?>">
                        Back to home
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
 <?php  } ?>