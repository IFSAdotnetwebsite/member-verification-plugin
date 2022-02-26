<?php

class IFSALCMember
{

    /**
     * @var Closure|mixed|null
     */
    private $show_messages;

    // This is a flag to avoid logging action more than once.
    private $first_run = true;

    public $lc_member_table;

    public function __construct($show_messages = false){

        $this->show_messages = $show_messages;
        global $wpdb;
        $this->lc_member_table = $wpdb->prefix . 'ifsa_lc_member';
    }

    /**
     * Utility function to show a message in the admin page
     * @param string $message The message
     * @param string $notice_type accepts 'notice-error', 'notice-warning', 'notice-success'
     */
    function message(string $message, string $notice_type = 'notice-error')
    {
        // This is the second time the plugin code is run so is assumed that no new logging is needed
        if (!$this->first_run || !$this->show_messages) {
            return;
        }

        $notice = "<div class='notice is-dismissible {$notice_type}'>
                    {$message}
            </div>";

//      Technically the transient could expire before is read, but still the plugin work even if the message system breaks
//      by using the transient (instead of options api) an unnecessary write to the database is avoided.
//      The 200 sec is an arbitrary number.
        $notices = get_transient('ifsa_member_verification_admin_notices');
        $notices = $notices ? $notices : array();
        $notices[] = $notice;
        set_transient('ifsa_member_verification_admin_notices', $notices, 200);
    }
    /**
     * Return the LC name give the ID or empty string if it doesn't exist
     * @param $lc_id
     * @return string The LC Name
     */
    function get_lc_name($lc_id): string
    {
        if ($lc_id == 'none') {
            return "";
        }
        $term_lc = get_term_by("ID", $lc_id, 'committee');
        if (!$term_lc) {
            if ($lc_id != 0) { // LC Id can be 0 (eg. when creating a user) otherwise is a error
                error_log("Unknown LC Id");
                $this->message("Error in LC Id. get in touch with website admin");
            }

            return "";
        }
        return $term_lc->name;
    }

    /**
     * Returns the id of the admin for the given LC. In case of no or multiple admins shows an error and return WP_Error
     * @param $lc_id
     * @return int|WP_Error
     */
    function get_lc_admin($lc_id)
    {

        $query = new WP_User_Query(
            array(
                "meta_key" => "ifsa_committee",
                "meta_value" => $lc_id,
                "fields" => "ID",
                "role" => "lc_admin"
            )
        );

        if (empty($query->get_results())) {
            $lc_name = $this->get_lc_name($lc_id);
            $this->message("IFSA Member verification: Error! No LC admin for {$lc_name}");
            return new WP_Error("No LC Admin");
        }
        if (count($query->get_results()) > 1) {
            $lc_name = $this->get_lc_name($lc_id);
            $this->message("IFSA Member verification: Error! More than 1 LC Admin fo {$lc_name}");
            return new WP_Error("More than 1 LC Admin");
        } else {
            return $query->get_results()[0];
        }
    }

    /**
     * Return the region id associated with a given LC
     * @param $lc_id
     * @return int|WP_Error
     */
    function get_region($lc_id)
    {
        $args = array(
            'post_type' => 'regions',
            'tax_query' => array(
                array(
                    'taxonomy' => 'committee',
                    'field' => 'term_id',
                    'terms' => $lc_id
                )
            )
        );
        $query = new WP_Query($args);
        // Safety check that the number of regions is correct
        $n_regions = count($query->posts);
        if ($n_regions != 1) {
            $this->message("Error! An LC can be only in one region. {$lc_id} si in {$n_regions}");
            return new WP_Error();
        }
        return $query->posts[0]->ID;
    }



}