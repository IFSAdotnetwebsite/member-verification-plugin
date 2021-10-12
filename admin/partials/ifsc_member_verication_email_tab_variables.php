<?php

/**
 * This file is use to declare variables for the 
 * Email regisration page of Member Verification 
 * Plugin
 *
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/admin/partials
 * @author     Multidots <nishit.langaliya@multidots.com>
 * @link       #
 * @since      1.0.0
 */

$ifsa_field_names = array(   
                        'welcome_email_after_verify_member',
                        'reminder_on_same_date_when_renewed',
                        'member_bulk_invite_for_join_the_community',
                        'fifteen_days_after_expire_date',
                        'reject_by_lc_admin_email_to_member',
                        'twentytwo_days_after_expire_date',
                        'thirty_days_before_renewal_date',
                        'thirty_days_after_expire_date',
                        'remove_member_content',
                        
                    );

$content_ifsa_mail_id = get_option('welcome_email_after_verify_member');
$content_ifsa_reminder_id = get_option('reminder_on_same_date_when_renewed');
$content_ifsa_member_join_id = get_option('member_bulk_invite_for_join_the_community');
$content_ifsa_expire_date_id  = get_option('fifteen_days_after_expire_date');
$content_ifsa_lc_admin_id = get_option('reject_by_lc_admin_email_to_member');
$content_ifsa_twentytwo_expire_date_id = get_option('twentytwo_days_after_expire_date');
$content_ifsa_thirty_days_before_renewal_date_id = get_option('thirty_days_before_renewal_date');
$content_ifsa_thirty_days_after_expire_date_id = get_option('thirty_days_after_expire_date');
$remove_member_content_id = get_option('remove_member_content');
$setting = array('media_buttons' => false,'quicktags'=>false, 'textarea_rows' => 7);

foreach ($ifsa_field_names as $key => $name) {
    ${$name} = $ifsa_field_names[$key];
    $content= get_option($ifsa_field_names[$key]);
};


$content_ifsa_mail_id_subject = !empty( get_option('welcome_email_after_verify_member_subject')) ? get_option('welcome_email_after_verify_member_subject') : 'Successfully verified to IFSA';
$content_ifsa_reminder_id_subject = !empty( get_option('reminder_on_same_date_when_renewed_subject')) ? get_option('reminder_on_same_date_when_renewed_subject') : 'IFSA account expiration';
$content_ifsa_member_join_id_subject = !empty( get_option('member_bulk_invite_for_join_the_community_subject')) ? get_option('member_bulk_invite_for_join_the_community_subject') : '{lc_admin} has invited you to join IFSA' ;
$content_ifsa_expire_date_id_subject  = !empty( get_option('fifteen_days_after_expire_date_subject'))? get_option('fifteen_days_after_expire_date_subject') :'Reminder IFSA account expiration';
$content_ifsa_lc_admin_id_subject = !empty( get_option('reject_by_lc_admin_email_to_member_subject')) ?  get_option('reject_by_lc_admin_email_to_member_subject') :'Reject by LC Admin email to member';
$content_ifsa_twentytwo_expire_date_id_subject = !empty( get_option('twentytwo_days_after_expire_date_subject') ) ? get_option('twentytwo_days_after_expire_date_subject') :'Reminder IFSA account expiration one week';
$content_ifsa_thirty_days_before_renewal_date_id_subject = !empty( get_option('thirty_days_before_renewal_date_subject') ) ? get_option('thirty_days_before_renewal_date_subject') :'Start of IFSA renewal period';
$content_ifsa_thirty_days_after_expire_date_id_subject = !empty( get_option('thirty_days_after_expire_date_subject') ) ? get_option('thirty_days_after_expire_date_subject') :'IFSA account expiration';
$remove_member_content_id_subject = !empty( get_option('remove_member_content_subject') ) ?  get_option('remove_member_content_subject') :'Removal from IFSA membership';

