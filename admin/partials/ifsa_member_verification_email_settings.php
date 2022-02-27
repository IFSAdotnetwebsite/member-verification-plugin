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

 //File containg Variables for the WP_editor parameters
require_once plugin_dir_path(__FILE__).'ifsa_member_verication_email_tab_variables.php';
$after_30 =  !empty ( get_option( 'after_30' ) ) ? get_option( 'after_30' ) : '30';
$after_21 =  !empty ( get_option( 'after_21' ) ) ? get_option( 'after_21' ) : '21';
$after_15 =  !empty ( get_option( 'after_15' ) ) ? get_option( 'after_15' ) : '15';
$before_30  =  !empty ( get_option( 'before_30' ) ) ? get_option( 'before_30' ) : '30';

$editor_settings = array('media_buttons' => false,'quicktags'=>true, 'textarea_rows' => 7);

?>
<section id="dynamic_variable">
    <h3>Dynamic varibles used in email templates.</h3>
<ol>
<li><strong>{lc_member}</strong> - Shows LC Member First Name and Last Name. </li>
<li><strong>{lc_admin}</strong> -  Shows LC Admin Display Name.  </li>
<li><strong>{renew_date}</strong> -  Shows expiration date of membership.  </li>
<li><strong>{lc_link}</strong> -  Shows registration invite link. </li>
<li><strong>{15_days_after_date_expire}</strong> -  Shows date after 15 days of expiration date.  </li>
<li><strong>{reject_reason}</strong> -  Shows the reason of rejection. </li>
<li><strong>{22_days_after_date_expire}</strong> - Shows date after 22 days of expiration date. </li>
<li><strong>{30_days_before_date}</strong> - Shows date before 30 days of expiration date.</li>
<li><strong>{30_days_after_date}</strong> - Shows date after 30 days of expiration date.</li>
</ol>

</section>
<div class="ifsc_member_admin_email_container">
    <?php
    foreach (IFSA_EMAILS as $email_name => $email_defaults){
        $content = get_option($email_name, "");
        $subject_key = $email_name."_subject";
        $subject = get_option($subject_key) ;
        $subject = $subject ? $subject: $email_defaults['subject'];
        ?>
        <div class="ifsc_member_admin_email-division">
            <h2 class="ifsc_member_admin_email-division_heading">
                <?php echo $email_defaults['description']?>
            </h2>
            <div class="cls-email-subject">
                <span>Email Subject:</span>
                <?php echo "<input type =text value=\"$subject\" name =$subject_key id=$subject_key class=regular-text>" ?>
            </div>
            <div class="division-editor">
                <?php wp_editor($content, $email_name."_content", $editor_settings)?>
            </div>
        </div>
    <?php } ?>
</div>






