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
require_once plugin_dir_path(__FILE__).'ifsc_member_verication_email_tab_variables.php';
$after_30 =  !empty ( get_option( 'after_30' ) ) ? get_option( 'after_30' ) : '30';
$after_21 =  !empty ( get_option( 'after_21' ) ) ? get_option( 'after_21' ) : '21';
$after_15 =  !empty ( get_option( 'after_15' ) ) ? get_option( 'after_15' ) : '15';
$before_30  =  !empty ( get_option( 'before_30' ) ) ? get_option( 'before_30' ) : '30';


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

</code>
</section>
<div class="ifsc_member_admin_email_container">

    <div class="ifsc_member_admin_email-division">
        <h2 class="ifsc_member_admin_email-division_heading">
            Welcome Email after verify member
        </h2>
        <div class="cls-email-subject">
            <span>Email Subject:</span>
            <input type ="text" value="<?php echo $content_ifsa_mail_id_subject;?>" name = "welcome_email_after_verify_member_subject" id="welcome_email_after_verify_member_subject" class="regular-text"/>
        </div>
        <div class="division-editor">
            <?php wp_editor($content_ifsa_mail_id, $welcome_email_after_verify_member, $setting)?>
        </div>
    </div>
    <div class="ifsc_member_admin_email-division">
        <h2 class="ifsc_member_admin_email-division_heading">
            Reminder on same date when renewed [if not renewed]
        </h2>
        <div class="cls-email-subject">
            <span>Email Subject:</span>
            <input type ="text" value="<?php echo $content_ifsa_reminder_id_subject;?>" name = "reminder_on_same_date_when_renewed_subject" id="reminder_on_same_date_when_renewed_subject" class="regular-text"/>
        </div>
        <div class="division-editor">
            <?php wp_editor($content_ifsa_reminder_id, $reminder_on_same_date_when_renewed, $setting)?>
        </div>
    </div>
    <div class="ifsc_member_admin_email-division">
        <h2 class="ifsc_member_admin_email-division_heading">
            Member Bulk invite for join the community
        </h2>
        <div class="cls-email-subject">
            <span>Email Subject:</span>
            <input type ="text" value="<?php echo $content_ifsa_member_join_id_subject;?>" name = "member_bulk_invite_for_join_the_community_subject" id="member_bulk_invite_for_join_the_community_subject" class="regular-text"/>
        </div>
        <div class="division-editor">
            <?php wp_editor($content_ifsa_member_join_id, $member_bulk_invite_for_join_the_community, $setting)?>
        </div>
    </div>
    <div class="ifsc_member_admin_email-division">
        <h2 class="ifsc_member_admin_email-division_heading">
            <?php echo $after_15;?> Days after Expire date [if not renewed]
        </h2>
        <div class="cls-email-subject">
            <span>Email Subject:</span>
            <input type ="text" value="<?php echo $content_ifsa_expire_date_id_subject;?>" name = "fifteen_days_after_expire_date_subject" id="fifteen_days_after_expire_date_subject" class="regular-text"/>
        </div>
        <div class="division-editor">
            <?php wp_editor($content_ifsa_expire_date_id, $fifteen_days_after_expire_date, $setting)?>
        </div>
    </div>
    <div class="ifsc_member_admin_email-division">
        <h2 class="ifsc_member_admin_email-division_heading">
            Reject by LC Admin email to member
        </h2>
        <div class="cls-email-subject">
            <span>Email Subject:</span>
            <input type ="text" value="<?php echo $content_ifsa_lc_admin_id_subject;?>" name = "reject_by_lc_admin_email_to_member_subject" id="reject_by_lc_admin_email_to_member_subject" class="regular-text"/>
        </div>
        <div class="division-editor">
            <?php wp_editor($content_ifsa_lc_admin_id, $reject_by_lc_admin_email_to_member, $setting)?>
        </div>
    </div>
    <div class="ifsc_member_admin_email-division">
        <h2 class="ifsc_member_admin_email-division_heading">
        <?php echo $after_21;?> Days after Expire date [if not renewed]
        </h2>
        <div class="cls-email-subject">
            <span>Email Subject:</span>
            <input type ="text" value="<?php echo $content_ifsa_twentytwo_expire_date_id_subject;?>" name = "twentytwo_days_after_expire_date_subject" id="twentytwo_days_after_expire_date_subject" class="regular-text"/>
        </div>
        <div class="division-editor">
            <?php wp_editor($content_ifsa_twentytwo_expire_date_id, $twentytwo_days_after_expire_date, $setting)?>
        </div>
    </div>
    <div class="ifsc_member_admin_email-division">
        <h2 class="ifsc_member_admin_email-division_heading">
        <?php echo $before_30;?> Days before renewal date - Reminder email
        </h2>
        <div class="cls-email-subject">
            <span>Email Subject:</span>
            <input type ="text" value="<?php echo $content_ifsa_thirty_days_before_renewal_date_id_subject;?>" name = "thirty_days_before_renewal_date_subject" id="thirty_days_before_renewal_date_subject" class="regular-text"/>
        </div>
        <div class="division-editor">
            <?php wp_editor($content_ifsa_thirty_days_before_renewal_date_id, $thirty_days_before_renewal_date, $setting)?>
        </div>
    </div>
    <div class="ifsc_member_admin_email-division">
        <h2 class="ifsc_member_admin_email-division_heading">
        <?php echo $after_30;?> days after expire date [if account is disabled]
        </h2>
        <div class="cls-email-subject">
            <span>Email Subject:</span>
            <input type ="text" value="<?php echo $content_ifsa_thirty_days_after_expire_date_id_subject;?>" name = "thirty_days_after_expire_date_subject" id="thirty_days_after_expire_date_subject" class="regular-text"/>
        </div>
        <div class="division-editor">
            <?php wp_editor($content_ifsa_thirty_days_after_expire_date_id, $thirty_days_after_expire_date, $setting)?>
        </div>
    </div>

    <div class="ifsc_member_admin_email-division">
        <h2 class="ifsc_member_admin_email-division_heading">
        LC Admin remove LC member 
        </h2>
        <div class="cls-email-subject">
            <span>Email Subject:</span>
            <input type ="text" value="<?php echo $remove_member_content_id_subject;?>" name = "remove_member_content_subject" id="remove_member_content_subject" class="regular-text"/>
        </div>
        <div class="division-editor">
            <?php wp_editor($remove_member_content_id, $remove_member_content, $setting)?>
        </div>
    </div>
    
</div>






