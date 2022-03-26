<?php

class IFSAPublicForms
{
    private $version;


    /**
     * Initialize the class and set its properties.
     */
    public function __construct($version)
    {
        $this->version = $version;

        add_shortcode('ifsa-multistep-form', array($this, 'ifsa_multistep_form'));
        add_shortcode('ifsa-renewal-form', array($this, 'ifsa_renewal_form'));

    }
    public function enqueue_styles()
    {
        wp_enqueue_style('member-verification-forms', plugin_dir_url(__FILE__) . 'css/ifsa-member-verification-public.css', array(), $this->version);
        wp_enqueue_style('renewal_form_style', plugin_dir_url(__FILE__) . 'css/ifsa-renewal-form.css', array(), $this->version);
        wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css', array(), $this->version);

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script("ifsa-registration-form", plugin_dir_url(__FILE__) . 'js/ifsa-registration-form.js', array('jquery'), $this->version, false);
        wp_enqueue_script('ifsa-renewal-form', plugin_dir_url(__FILE__) . 'js/ifsa-renewal-form.js', array('jquery'), $this->version, false);
        wp_enqueue_script('jquery-ui-datepicker');
    }

    /**
     * Call back function for the [ifsa-multistep-form] shortcode markup
     *
     */
    public function ifsa_multistep_form()
    {

        ob_start();

        if (!(is_user_logged_in())) {
            include_once plugin_dir_path(__FILE__) . 'partials/ifsa-registration-form.php';
        } else {
            echo "<script>location.href = window.location.origin</script>";
        }

        return ob_get_clean();
    }

    /**
     * Call back function for the [ifsa-renewal-form] shortcode markup
     */
    public function ifsa_renewal_form()
    {
        ob_start();

        include_once plugin_dir_path(__FILE__) . 'partials/ifsa-renewal-form.php';

        return ob_get_clean();
    }

    /**
     * Ajax callback to get LC list for a region
     *
     * Note is not checking nonce as there is no action done on the server
     */
    public function list_region_callback()
    {
        $region = ifsa_utility()->get_post_var('region');

        $committee_list = wp_get_object_terms($region, 'committee');

        // Return null if we found no results
        if (!$committee_list) {
            return;
        }

        // HTML for our select printing post titles as loop
        $output = "";
        foreach ($committee_list as $index) {
            $output .= '<option value="' . $index->term_id . '">' . $index->name . '</option>';
        }

        echo $output;
        wp_die();
    }

    /**
     * Register a user from the frontend
     * This is an ajax callback
     * @return void
     */
    public function ifsa_register_user_front_end_callback()
    {
        // Explain why the nonce is not checked?
        // is there a good reason?
        //check_ajax_referer("registration-form-ajax");

        // Maps the ajax fields into Wordpress user fields and sanitize them
        $user_data = array(
            'user_login' => ifsa_utility()->get_post_var('user_name'),
            'user_email' => sanitize_email($_POST['user_email']),
            'user_pass' => ifsa_utility()->get_post_var('user_password'),
            'first_name' => ifsa_utility()->get_post_var('user_first_name'),
            'last_name' => ifsa_utility()->get_post_var('user_last_name')
        );
        $user_data['display_name'] = $user_data['first_name']. " " . $user_data['last_name'];

        // Add user in database
        $user_id = wp_insert_user($user_data);

        // Check that the user creation was successful
        if (is_wp_error($user_id)) {
            if (isset($user_id->errors['empty_user_login'])) {
                $notice_key = 'User Name and Email are mandatory';
                echo $notice_key;
            } elseif (isset($user_id->errors['existing_user_login'])) {
                echo 'User name already exist.';
            } else {
                echo 'Error Occurred during sign up. Message: ' . $user_id->get_error_message() . ". If problem persists contact site admin";
            }
            wp_die();
        }

        /** Basic user creation completed. Deal with IFSA specific part */

        $lc_id = ifsa_utility()->get_post_var('lc');


        /**  Check Local Committee */
        $lc_admin = ifsa_lc()->get_lc_admin($lc_id);
        if(is_wp_error($lc_admin)){
            echo "Invalid LC. try again or contact website admin. Error: ".$lc_admin->get_error_code();
            wp_die();
        }

        // Add LC id to user meta. This is used in the admin interface
        update_user_meta($user_id, 'ifsa_committee', $lc_id);

        /** Buddypress profile */

        $region_id = ifsa_utility()->get_post_var('ifsa_region');
        // Map Xprofile_name => POST_name
        $user_add_info = array(
            'Gender' => 'gender',
            'Nationality' => 'nationality',
            'University Name' => 'universityname',
            'University Country' => 'country',
            'University Level' => 'universityLevel',
            'Course Topic' => 'courseTopic',
            'Graduation Day' => 'graduateday',
        );
        // uses get_post_var to sanitize the input
        foreach ($user_add_info as $key => $ajax_value){
            $user_add_info[$key] = ifsa_utility()->get_post_var($ajax_value);
        }
        // Add addition fields to profile

        $user_add_info['Local Committee Name'] = ifsa_lc()->get_lc_name($lc_id);
        $user_add_info['IFSA Region'] = ifsa_lc()->get_region_name($region_id);

        foreach ($user_add_info as $field_name => $field_value){
            $return = xprofile_set_field_data($field_name, $user_id, $field_value);
            if(!$return){
                error_log("Error in creating xprofile fields. Field name: $field_name, User id: $user_id, Field value: $field_value");
            }
        }
        // Update custom table for member verification
        // differentiate email invites
        // Consider update this to use Wp_Query
        $source = ifsa_utility()->get_post_var('utm_source') == 'invite' ? "Invite" : "Website"; // default source is website

        global $wpdb;
        $last_updated = bp_core_current_time();

        $query = "INSERT INTO {ifsa_lc()->lc_member_table} ( user_id,lc_adminid, committee_id ,region_id, action_date, member_status,source) VALUES ( %d,%d, %s, %s, %s, %d,%s )";
        $sql = $wpdb->prepare($query, $user_id, $lc_admin, $lc_id, $region_id, $last_updated, 0, $source);

        $result = $wpdb->query($sql);

        if (empty($result) || is_wp_error($result)) {
            echo "Error in updating user into database. Please contact website admin.";
            wp_die();
        }


        /** Email */

        // To member
        $res1 = ifsa_lc()->send_ifsa_email('register_email_user', array(
            '{user_email}' => $user_data['user_email'],
            '{user_name}' => $user_data['first_name'],
            '{user_fullname}' => $user_data['display_name'],
            '{lc_name}' => $user_add_info['Local Committee Name']
        ));
        // To LC admin
        $res2 = ifsa_lc()->send_ifsa_email('register_email_lc_admin', array(
            '{lc_admin_email}' => get_userdata($lc_admin)->user_email,
            '{user_name}' => $user_data['first_name'],
            '{user_fullname}' => $user_data['display_name'],
            '{lc_name}' => $user_add_info['Local Committee Name']
        ));

        if(is_wp_error($res1) || is_wp_error($res2)){
            echo "Error in sending email. Contact website administrator. {$res1->get_error_code()} {$res2->get_error_code()}";
            wp_die();
        }

        /** Log */

        ifsa_lc()->log("user registered",
            "Source: $source, LC: {$user_add_info['Local Committee Name']}", $user_id);

        echo 'success';

        wp_die();
    }

}