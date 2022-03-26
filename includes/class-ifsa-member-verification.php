<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/includes
 * @author     Multidots <nishit.langaliya@multidots.com>
 */
class Ifsa_Member_Verification {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ifsa_Member_Verification_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'IFSA_MEMBER_VERIFICATION_VERSION' ) ) {
			$this->version = IFSA_MEMBER_VERIFICATION_VERSION;
		} else {
			$this->version = '1.1.0';
		}
		$this->plugin_name = 'ifsa-member-verification';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
        $this->define_utility_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ifsa_Member_Verification_Loader. Orchestrates the hooks of the plugin.
	 * - Ifsa_Member_Verification_i18n. Defines internationalization functionality.
	 * - Ifsa_Member_Verification_Admin. Defines all hooks for the admin area.
	 * - Ifsa_Member_Verification_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once IFSA_MEMBER_VERIFICATION_PATH. 'includes/class-ifsa-member-verification-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once IFSA_MEMBER_VERIFICATION_PATH . 'includes/class-ifsa-member-verification-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once IFSA_MEMBER_VERIFICATION_PATH . 'admin/class-ifsa-member-verification-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once IFSA_MEMBER_VERIFICATION_PATH . 'public/class-ifsa-member-verification-public.php';
        require_once IFSA_MEMBER_VERIFICATION_PATH . 'public/class-IFSALCAdmin.php';
        require_once IFSA_MEMBER_VERIFICATION_PATH . 'public/class-IFSAPublicForms.php';

        /**
         * Constant file
         */
        require_once IFSA_MEMBER_VERIFICATION_PATH . 'includes/ifsa_member_verification_constant.php';

        $this->loader = new Ifsa_Member_Verification_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ifsa_Member_Verification_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ifsa_Member_Verification_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Ifsa_Member_Verification_Admin( $this->get_plugin_name(), $this->get_version() );

        // Admin notices
	
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action('admin_init', $plugin_admin, 'ifsa_load_plugin');
		$this->loader->add_action( 'init', $plugin_admin, 'ifsa_register_regions' );
		$this->loader->add_action( 'init',$plugin_admin, 'ifsa_region_taxonomy', 0 );
		// Used to create LC admin user profile
		$this->loader->add_action( 'show_user_profile', $plugin_admin,'ifsa_custom_user_profile_fields' );
		$this->loader->add_action( 'edit_user_profile', $plugin_admin,'ifsa_custom_user_profile_fields' );
		$this->loader->add_action( "user_new_form", $plugin_admin,"ifsa_custom_user_profile_fields" );
		$this->loader->add_action('user_register', $plugin_admin,'ifsa_save_custom_user_profile_fields');
		$this->loader->add_action('profile_update',$plugin_admin, 'ifsa_save_custom_user_profile_fields');

		//use for the admin menu page
		$this->loader->add_action('admin_menu', $plugin_admin, 'ifsa_member_verification_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'ifsa_member_verification_general_settings');
		$this->loader->add_action('admin_init', $plugin_admin, 'ifsa_member_verification_email_settings');
		$this->loader->add_action('admin_init', $plugin_admin, 'ifsa_member_verification_log_settings');
		$this->loader->add_action( 'delete_user',  $plugin_admin,'ifsa_custom_remove_user', 10 );

		
		$this->loader->add_action('admin_init',  $plugin_admin,'ifsa_hide_dashboard');
		$this->loader->add_action( 'admin_init', $plugin_admin, 'export_csv_callback' );

	
	

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {


        $public_forms = new IFSAPublicForms($this->version);

        $this->loader->add_action( 'wp_enqueue_scripts', $public_forms, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $public_forms, 'enqueue_scripts' );

        $this->loader->add_action( 'wp_ajax_ifsa_list_region', $public_forms,'list_regions' );
        // Renew requests makes sense only if you are logged in
//        $this->loader->add_action( 'wp_ajax_ifsa_renew_request', $public_forms, 'ifsa_ifsa_renew_request' );
        // Registration only if you are not logged in
        $this->loader->add_action('wp_ajax_nopriv_register_user_front_end',  $public_forms,'ifsa_register_user_front_end_callback', 0);


        $lc_admin = new IFSALCAdmin($this->version);

        $this->loader->add_action( 'wp_enqueue_scripts', $lc_admin, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $lc_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'bp_setup_nav', $lc_admin, 'ifsa_profile_tab_member_list' );
		$this->loader->add_action( 'wp_ajax_ifsa_approve_member', $lc_admin,'ifsa_approve_member_callback' );
		$this->loader->add_action( 'wp_ajax_ifsa_reject_member', $lc_admin,'ifsa_reject_member_callback' );
		$this->loader->add_action( 'wp_ajax_ifsa_remove_member', $lc_admin,'ifsa_remove_member_callback' );

		$this->loader->add_action( 'wp_ajax_file_upload', $lc_admin, 'file_upload_callback' );
		$this->loader->add_action( 'init', $lc_admin, 'download_csv_callback' );

        $this->loader->add_action( 'bp_after_member_header',  $lc_admin,'ifsa_lcadmin_banner' );

//		$this->loader->add_action( 'bp_after_profile_loop_content', $public_forms, 'ifsa_memebership_expire_callback' );
		// Keep this?
//        $this->loader->add_action( 'bp_after_has_profile_parse_args',  $public_forms,'ifsa_bpfr_hide_profile_field_group' );

//        $this->loader->add_action( 'ifsa_cron_job', $public_forms, 'ifsa_cron_job_callback' );
//
//
//		$this->loader->add_action( 'weekl_member_list_callback', $public_forms, 'weekl_member_list_callback' );
//		$this->loader->add_action( 'ifsa_membership_start', $public_forms, 'ifsa_membership_start' );
//
//		$this->loader->add_filter( 'bp_nouveau_feedback_messages', $public_forms,'ifsa_change_nouveau_string', 20, 1 );
//
//		$this->loader->add_filter( 'login_redirect', $public_forms, 'ifsa_redirect_to_profile', 100, 3 );
//
//		//$this->loader->add_action('after_setup_theme',$public_forms, 'ifsa_remove_admin_bar');
//	//	$this->loader->add_action('bp_before_registration_submit_buttons',$public_forms, 'ifsa_add_to_registration', 36);
//
//		$this->loader->add_action( 'bp_after_member_header',  $public_forms,'ifsa_lcadmin_banner' );
		
	}

    function define_utility_hooks(){
        $this->loader->add_action( 'admin_notices',  ifsa_utility(),'show_admin_notices');
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ifsa_Member_Verification_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
