<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/admin/partials
 * @author     Multidots <nishit.langaliya@multidots.com>
 * @link       #
 * @since      1.0.0
 */

 //Checking the current tab
if ( isset($_GET[ 'tab' ]) ) {
    $active_tab = sanitize_text_field($_GET[ 'tab' ]);
} else {
    $active_tab = "ifsa_member_verification_general_settings";
}

?>
<div class="ifsa-admin-settings-wrapper">
<h1>Member Verification Plugin</h1>

<h2 class="nav-tab-wrapper ifsa_nav-tab-wrapper">
    <a href="?page=ifsa-settings&tab=ifsa_member_verification_general_settings" class="nav-tab ifsa_nav-tab <?php echo $active_tab == 'ifsa_member_verification_general_settings' ? 'nav-tab-active' : ''; ?>">General</a>
    <a href="?page=ifsa-settings&tab=ifsa_member_verification_email_settings" class="nav-tab ifsa_nav-tab <?php echo $active_tab == 'ifsa_member_verification_email_settings' ? 'nav-tab-active' : ''; ?>">Email</a>
    <a href="?page=ifsa-settings&tab=ifsa_member_verification_log_settings" class="nav-tab ifsa_nav-tab <?php echo $active_tab == 'ifsa_member_verification_log_settings' ? 'nav-tab-active' : ''; ?>">Logs</a>
</h2>



    <?php 

    //Loading sections according to the requirement
    
    if ( $active_tab == "ifsa_member_verification_general_settings" ) { ?>
        <form method="POST" action="options.php">
            <?php
            settings_fields('ifsa_member_verification_general_settings');
            do_settings_sections('ifsa_member_verification_general_settings');
            submit_button(); ?>
        </form> <?php
    } elseif ( $active_tab == "ifsa_member_verification_email_settings" ) { ?>
        <form method="POST" action="options.php">
            <?php

            settings_fields('ifsa_member_verification_email_settings');
            do_settings_sections('ifsa_member_verification_email_settings');
            submit_button(); 
            ?>
        </form> <?php
    } else {  
        
        ?>
        <form method="POST" action="<?php echo esc_url(add_query_arg())?>">
        		
            <?php
            settings_fields('ifsa_member_verification_log_settings');
            do_settings_sections('ifsa_member_verification_log_settings');
            ?>
        </form> <?php
    }
    
    ?>
</div>

