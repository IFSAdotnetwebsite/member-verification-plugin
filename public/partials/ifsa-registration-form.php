<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @package    ifsa_Multistep_Form
 * @subpackage ifsa_Multistep_Form/public/partials
 * @author     Multidots <nishit.langaliya@multidots.com>
 * @link       plugin@multdots.com
 * @since      1.0.0
 *
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="ifsa_multistep_form-main-continer">
    <form action="" name="ifsa_form" id="ifsa_form" class="standard-form signup-form clearfix" method="post"
          enctype="multipart/form-data">
        <div id="ifsa_form_1" class="ifsa_multistep_form-body">
            <h3 class="ifsa_multistep_form-heading">
                Welcome to the IFSA member registration form
            </h3>
            <p>
                This is a multistep registration form that will guide you to create an IFSA account.
                <a href="https://ifsa.net/how-to-register">Learn more </a>  about the member verification system
                or <a href="https://ifsa.net/how-to-register/feedback/"> contact us for questions or feedbacks </a>
            </p>
            <h4>
                Are you a student?
            </h4>
            <p>
                Answer yes if you are currently enrolled in a university, no otherwise
            </p>
            <div class="ifsa_multistep_form-options">
                <label for="form_1_toggle-no">No</label>
                <input id="form_1_toggle-no" name="toggle" value="No" type="radio">
                <label for="form_1_toggle-yes">Yes</label>
                <input id="form_1_toggle-yes" name="toggle" value="Yes" type="radio">
            </div>
            <p class="ifsa_multistep_form_counter">
                1/4
            </p>

        </div>
        <div id="ifsa_form_2" class="ifsa_multistep_form-body2">
            <h3 class="ifsa_multistep_form-heading">
                <?php echo IFSA_FORM_2_TITLE; ?>
            </h3>
            <p class="ifsa_multistep_form-subheading">
                Add the information about your university
                <?php echo get_option('ifsa_step_2_description'); ?>
            </p>
            <div class="ifsa_multistep_form-options">
                <label for="ifsa_universityname" class="required">University Name</label>
                <input id="ifsa_universityname" name="ifsa_universityname" type="text">
                <span id="ifsa_universityname-error"></span>
                <label for="ifsa_country" class="required"> University Country</label>
                <select name="ifsa_country" id="ifsa_country">
                    <option value="">Select University Country</option>
                    <?php foreach (COUNTRY_LIST as $key =>  $value) { ?>
                        <option value="<?php echo esc_attr($value, 'Ifsa_Member_Verification') ?>"><?php echo esc_html_e($value, 'Ifsa_Member_Verification') ?></option>
                    <?php } ?>
                </select>
                <span id="ifsa_country-error"></span>
                <label for="ifsa_universitylevel" class="required">University Level</label>
                <select name="ifsa_universitylevel" id="ifsa_universitylevel">
                    <option value="">Select University Level</option>
                    <?php foreach (UNI_LEVELS as $key => $value) { ?>
                        <option value="$value">$value</option>
                    <?php } ?>
                </select>
                <span id="ifsa_universitylevel-error"></span>
                <label for="ifsa_coursetopic">Course Topic</label>
                <input id="ifsa_coursetopic" name="ifsa_coursetopic" type="text">
                <span id="ifsa_coursetopic-error"></span>
                <div class="cls-back">
                    <button id="ifsa_form_2-btn-back" type="button">Back</button>
                    <button id="ifsa_form_2-btn" type="button">Continue</button>
                </div>
            </div>
            <p class="ifsa_multistep_form_counter">
                2/4
            </p>
        </div>
        <div id="ifsa_form_3" class="ifsa_multistep_form-body">
            <h3 class="ifsa_multistep_form-heading">
                Are you an IFSA member?
            </h3>
            <p>
                Are you a member of an IFSA LC (Local Committee)
            </p>
            <p class="ifsa_multistep_form-subheading">
                <?php echo get_option('ifsa_step_3_description'); ?>
            </p>
            <div class="ifsa_multistep_form-options">
                <input id="toggle-no" name="toggle_3" value="No" type="radio">
                <label for="toggle-no">No</label>
                <input id="toggle-yes" name="toggle_3" value="Yes" type="radio">
                <label for="toggle-yes">Yes</label>
            </div>
            <p class="ifsa_multistep_form_counter">
                3/4
            </p>
        </div>
        <div id="ifsa_form_6" class="ifsa_multistep_form-body">
            <h3 class="ifsa_multistep_form-heading">
                Student status
            </h3>
            <p>
                Have you ever been a student?
            </p>
            <div class="ifsa_multistep_form-options">
                <input id="toggle-yes-6" name="toggle_6" value="Yes" type="radio">
                <label for="toggle-yes-6">Yes</label>
                <input id="toggle-no-6" name="toggle_6" value="No" type="radio">
                <label for="toggle-no-6">No</label>
            </div>
            <p class="ifsa_multistep_form_counter">
                2/5
            </p>
        </div>
        <div id="ifsa_form_7" class="ifsa_multistep_form-body">
            <div id="ifsa_form_7-error" class="ifsa_form_7-error">
                <h3>Sorry you can't be an IFSA member</h3>
                <h3>Please contact IFSA SAN</h3>
            </div>
        </div>
        <div id="ifsa_form_4" class="ifsa_multistep_form-body4">
            <h3 class="ifsa_multistep_form-heading">
                When did you graduate?
            </h3>
            <p>
                This information is needed because you can be an IFSA member only for 1 year after graduation.
            </p>
            <div class="ifsa_multistep_form-options">
                <input id="datepicker" type="text" placeholder="mm-dd-yy">
                <div id="ifsa_form_4-success" class="ifsa_form_4-success">
                    <button id="ifsa_form_4-success_btn-back" class="ifsa_form_4-success-back" type="button"
                            style="visibility: hidden;">Back
                    </button>
                    <button type="button" id="ifsa_form_4-success_btn">Next</button>
                </div>
            </div>
            <div id="ifsa_form_4-error" class="ifsa_form_4-error">
                <h3>Sorry you can't be an IFSA member</h3>
                You cannot be an IFSA anymore, but you are invited to join the <a href="htts://ifsa-san.net"> IFSA Supporter and Alumni Network (IFSA SAN)</a>
            </div>
        </div>
        <div id="ifsa_form_5" class="ifsa_multistep_form-body">
            <div id="ifsa_form_5-error" class="ifsa_form_5-error">
                <h3> Sorry you can't register as an IFSA member</h3>
                If you are not an IFSA member you cannot register here.
                Get in touch with the <a href="https://ifsa.net/team#regional-representatives"> Regional Representatives </a>
                of your region to discover how to become an IFSA member.
            </div>
        </div>

        <div id="ifsa_final_step" class="ifsa_multistep_form-body" style="visibility:hidden;">

            <h3 class="ifsa_multistep_form-heading fusion-responsive-typography-calculated" data-fontsize="26"
                data-lineheight="46.8px" style="--fontSize:26; line-height: 1.8;">
                Sign up </h3>
            <div class="layout-wrap">
                <p class="register-message" style="display:none"></p>
                <div class="rendered-form">
                    <fieldset>
                        <legend>Personal Information</legend>
                        <div class="formbuilder-text form-group field-txt-name">
                            <label for="txt-name" class="formbuilder-text-label required">First Name</label>
                            <input type="text" class="form-control" name="txt-name" access="false" id="txt-name"
                                   required="required" aria-required="true">
                            <span id="ifsa_fname-error"></span>
                        </div>
                        <div class="formbuilder-text form-group field-txt-surname">
                            <label for="txt-surname" class="formbuilder-text-label required">Last Name</label>
                            <input type="text" class="form-control" name="txt-surname" access="false" id="txt-surname"
                                   required="required" aria-required="true">
                            <span id="ifsa_lname-error"></span>
                        <div class="formbuilder-text form-group field-txt-username">
                            <label for="txt-username" class="formbuilder-text-label required">Username (nickname)</label>
                            <input type="text" class="form-control" name="txt-username" access="false" id="txt-username"
                                   required="required" aria-required="true">
                            <span id="ifsa_username-error"></span>
                        </div>
                        <div class="formbuilder-text form-group field-txt-email">
                            <label for="txt-email" class="formbuilder-text-label required">Email</label>
                            <input type="email" class="form-control" name="txt-email" access="false" id="txt-email"
                                   required="required" aria-required="true">
                            <span id="ifsa_email-error"></span>
                        </div>
                        <div class="formbuilder-text form-group field-txt-password">
                            <label for="txt-password" class="formbuilder-text-label required">Password</label>
                            <input type="password" class="form-control" name="txt-password" access="false" id="txt-password"
                                   required="required" aria-required="true">
                            <span id="ifsa_password-error"></span>
                        </div>
                        </div>
                        <div class="formbuilder-select form-group field-ddl-gender">
                            <label for="ddl-gender" class="formbuilder-select-label">Gender</label>
                            <select class="form-control" name="ddl-gender" id="ddl-gender">
                                <option value = "" selected="true" id="ddl-gender-0">Select Gender</option>
                                <?php foreach (GENDERS as $key => $value) { ?>
                                    <option value="$value">$value</option>
                                <?php } ?>
                            </select>
                            <span id="ifsa_lc-error"></span>
                        </div>
                        <div class="formbuilder-select form-group field-ddl-nationality">
                            <label for="ddl-nationality" class="formbuilder-select-label">Where are you from?</label>
                            <select class="form-control" name="ddl-nationality" id="ddl-nationality">
                                <option selected="true" id="ddl-nationality-0">Select Country</option>
                                <?php foreach (COUNTRY_LIST as $key => $value) { ?>
                                    <option value="<?php echo esc_attr($value, 'Ifsa_Member_Verification') ?>"><?php echo esc_html_e($value, 'Ifsa_Member_Verification') ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>LC Information</legend>
                        <div class="formbuilder-select form-group field-ddl-region">
                            <label for="ddl-region" class="formbuilder-select-label required">
                                IFSA Region
                            </label>
                            <select class="form-control" name="ifsa_region" id="ifsa_region">
                                <option selected="true" value="" id="ddl-region-0">Select Region</option>
                                <?php
                                global $wpdb;
                                $custom_post_type = 'regions'; // define your custom post type slug here
                                // A sql query to return all post titles
                                $results = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = %s and post_status = 'publish'", $custom_post_type), ARRAY_A);
                                // Return null if we found no results
                                if (!$results) {
                                    return;
                                }
                                foreach ($results as $index => $post) {
                                    echo $htmlregion = '<option value="' . esc_attr($post['ID'], 'Ifsa_Member_Verification') . '">' . $post['post_title'] . '</option>';
                                }
                                ?>

                            </select>
                            <span id="ifsa_region-error"></span>
                        </div>
                        <div class="formbuilder-select form-group field-ddl-lc">
                            <label for="ddl-lc" class="formbuilder-select-label required">IFSA Committee</label>
                            <select class="form-control" name="lc" id="lc">
                                <option selected="true" value="" id="ddl-lc-0">Select Committee</option>

                            </select>
                        </div>
                    </fieldset>
                    <div class="formbuilder-radio-group form-group field-ddl-terms">
                        <label for="ddl-terms" class="formbuilder-radio-group-label required">Terms and Conditions</label>
                        <div class="radio-group">
                            <div class="formbuilder-radio-inline">
                                <p class="description" tabindex="0">Check IFSA <a href="../terms">terms &amp;
                                        conditions</a>
                                    and <a href="https://ifsa.net/privacy-policy">Privacy Policy</a>
                                </p>
                                <input name="ddl-terms" access="false" id="ddl-terms-0" required="required"
                                       aria-required="true" value="yes" type="checkbox">
                                <label for="ddl-terms-0">I agree to the terms and conditions and
                                    privacy policy</label>
                                <span id="ifsa_terms-error"></span>
                            </div>

                        </div>
                    </div>
                </div>
            </div><!-- //.layout-wrap -->
            <div class="submit">
                <button id="ifsa_form_last-btn-back" type="button">Back</button>

                <input type="submit" class="button" id="register-button" value="Register">
                <div id="ifsa-loading-register" style="display:none;">
                    <img src="<?php echo esc_url(site_url() . '/wp-admin/images/loading.gif'); ?>"
                         title="loading"/>
                </div>
            </div>
            <p class="ifsa_multistep_form_counter">
                4/4
            </p>

        </div>

    </form>
    <p class="register-success" style="display:none">
    <h3 class="ifsa_multistep_form-heading-h3 ifsa_multistep_form-heading fusion-responsive-typography-calculated"
        data-fontsize="26" data-lineheight="46.8px" style="--fontSize:26; line-height: 1.8;display:none;">
        Thank you for registering to the IFSA website! You received a confirmation email and now need to wait for your LC to confirm your account</h3>
    <aside class="bp-feedback bp-messages info">
        <span class="bp-icon" aria-hidden="true"></span>
        <p class="register-success-p" style="text-align: center;"></p>
    </aside>
    </p>
</div>