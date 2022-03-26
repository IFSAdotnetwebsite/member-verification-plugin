<?php

class IFSAUtility
{
    /**
     * Store the active instance of the class
     * @var IFSAUtility
     */
    private static $instance;

    /**
     * @var bool
     */
    private $first_run = true;

    /**
     *
     * @return IFSAUtility
     */
    public static function get_instance(): IFSAUtility
    {

        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;

    }

    /**
     * Return a sanitized post variable or default
     * @param string $var
     * @param string $default
     * @return string
     */
    public function get_post_var(string $var, string $default=""): string
    {
        if(isset($_POST[$var]) && !empty($_POST[$var])) {
            return sanitize_text_field($_POST[$var]);
        } else {
            return $default;
        }
    }

    /**
     * Show a message in the admin page
     * @param string $message The message
     * @param string $notice_type accepts 'notice-error', 'notice-warning', 'notice-success'
     */
    function admin_message(string $message, string $notice_type = 'notice-error')
    {
        // This is the second time the plugin code is run so is assumed that no new logging is needed
        // Run only in the admin area
        if (!$this->first_run || !is_admin()) {
            return;
        }

        $notice = "<div class='notice is-dismissible {$notice_type}'>
                    {$message}
            </div>";

        // Technically the transient could expire before is read, but still the plugin work even if the message system breaks
        // by using the transient (instead of options api) an unnecessary write to the database is avoided.
        // The 200 sec is an arbitrary number.
        $notices = get_transient('ifsa_member_verification_admin_notices');
        $notices = $notices ? $notices : array();
        $notices[] = $notice;
        set_transient('ifsa_member_verification_admin_notices', $notices, 200);
    }

    /**
     * Function called by the 'admin_notices' hook to actually show the notices
     */
    public function show_admin_notices()
    {
        $notices = get_transient('ifsa_member_verification_admin_notices');
        $notices = $notices ? $notices : array();
        foreach ($notices as $notice) {
            echo $notice;
        }
        delete_transient('ifsa_member_verification_admin_notices');
    }

    /**
     * Empty div with nonce
     * @param string $action nonce action
     * @return string
     */
    function insert_nonce(string $action): string
    {
        /* Create Nonce */
        $nonce = esc_attr(wp_create_nonce( $action ));
        /* Output empty div. */
        return "<div id='$action' data-nonce='$nonce' ></div>";
    }
}

function ifsa_utility(): IFSAUtility
{
    return IFSAUtility::get_instance();
}