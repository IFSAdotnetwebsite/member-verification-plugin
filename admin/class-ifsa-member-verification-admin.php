<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/admin
 * @author     Multidots <nishit.langaliya@multidots.com>
 * @license    GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link       #
 * @since      1.0.0
 */

require_once plugin_dir_path( __FILE__ ) . 'class-ifsa-member-verification-logs.php';

/**
 * Main class to handle all the methods of plugin
 *
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/admin
 * @author     Multidots <nishit.langaliya@multidots.com>
 * @license    GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link       #
 */
class Ifsa_Member_Verification_Admin {
	
	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;
	
	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $version The current version of this plugin.
	 */
	private $version;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}
	
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ifsa-member-verification-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css' );
	}
	
	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ifsa-member-verification-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'jquery-ui-datepicker' );
	}
	
	
	
	/* Omit closing PHP tag to avoid "Headers already sent" issues. */
	
	/**
	 * Function to register custom user profile fields for IFSA member
	 */
	public function ifsa_custom_user_profile_fields( $user ) {
		//$terms = get_terms( 'regions' );
		$terms = get_terms(
			[
				'taxonomy'   => 'committee',
				'hide_empty' => false,
			]
		);
		
		?>

		<table class="form-table">
			<tr>
				<th><label for="ifsa_committee">Assign Committee</label></th>
				<td>
					<?php
					$dropdown_value   = get_the_author_meta( 'ifsa_committee', $user->ID );
					$ifsa_user_status = get_the_author_meta( 'user_active_status', $user->ID );
					?>
					<select name="ifsa_committee" id="ifsa_committee">
						<option value="">--Select Committee--</option>
						<?php foreach ( $terms as $term ) { ?>
							<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php if ( $term->term_id == $dropdown_value ) {
								echo 'selected';
							} ?>><?php echo esc_html_e( $term->name, 'Ifsa_Member_Verification' ); ?>
							</option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="ifsa_activation">Approve Or Reject</label></th>
				<td>
					<?php
					$ifsa_user_status = get_the_author_meta( 'user_active_status', $user->ID );
					?>
					<select name="ifsa_activation" id="ifsa_activation">
						<option value="">--Select--</option>
						<option value="no" <?php if ( $ifsa_user_status === 'no' ) {
							echo 'selected';
						} ?>>Reject
						</option>
						<option value="true"  <?php echo 'selected';  if ( $ifsa_user_status === 'true' ) {
							echo 'selected';
						} ?>>Yes
						</option>
					</select>
				</td>
			</tr>
		</table>
		<?php
	}
	
	/**
	 * Save data the IFSA custom user profile fields
	 */
	public function ifsa_save_custom_user_profile_fields( $user_id ) {
		// again do this only if you can
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		$reason = '';
		$flagmember = 0;
		if ( isset( $_POST['ifsa_committee'] ) && ! empty( $_POST['ifsa_committee'] ) ) {
			
			$committee = sanitize_text_field( $_POST['ifsa_committee'] );
			// save my custom field
			update_user_meta( $user_id, 'ifsa_committee', $committee );
			$user = get_user_by( 'id', $user_id );
			// Add role
			$user->add_role( 'lc_admin' );
			update_user_meta( $user_id, 'user_active_status', "true" );
			if(function_exists('pmpro_changeMembershipLevel')) {
				$memberLevel = pmpro_changeMembershipLevel(2, $user_id);
				if ($memberLevel == true) {
					update_user_meta( $user_id, 'membership_assigned', 2 );
				}
				
			}
			$flagmember = 1;
		}
		
		if ( isset( $_POST['ifsa_activation'] ) && ! empty( $_POST['ifsa_activation'] ) ) {
			global $wpdb;
			//global $bp;
			
			$ifsa_activation = sanitize_text_field( $_POST['ifsa_activation'] );
			
			$adminid       = get_current_user_id();
			$lastupdated   = bp_core_current_time();
		//	$lcmembertable = $wpdb->prefix . 'ifsa_lc_member';
			$memberid      = $user_id;
			
			if ( $ifsa_activation == 'no' ) {
				$remark = "Admin has rejected";
				$action = "Super admin rejected";
				$status = 0;
				$reason = 'Admin has rejected';
				$user   = new WP_User( $memberid );
				// Remove all user roles after registration
				foreach ( $user->roles as $role ) {
					//$user->remove_role( $role );
				}

				if(function_exists('pmpro_changeMembershipLevel')) {
					$memberLevel = pmpro_changeMembershipLevel(1, $memberid);
					if ($memberLevel == true) {
						update_user_meta( $memberid, 'membership_assigned', 0 );
					}
					
				}
			} else {
				$remark = "Admin has approved";
				$action = "Super admin approved";
				
				$status = 1;
				if ( $flagmember == 0 ) {
					$user = get_user_by( 'id', $user_id );
					// Add role
					$user->add_role( 'lc_member' );
					update_user_meta( $user_id, 'user_active_status', "true" );
					
					if(function_exists('pmpro_changeMembershipLevel')) {
						$memberLevel = pmpro_changeMembershipLevel(1, $memberid);
						if ($memberLevel == true) {
							update_user_meta( $memberid, 'membership_assigned', 1 );
						}
						
					}
					
				}
			
			}
			
			
			$result = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}ifsa_lc_member SET member_status = %d, reason = %s, action_date = %s WHERE user_id = %d", $status, $reason,$lastupdated, $memberid ) ); // WPCS: unprepared SQL ok.
			if ( empty( $result ) || is_wp_error( $result ) ) {
			}
			
			$args  = array(
				'field'   => 'Name',
				'user_id' => $memberid,
			);
			$args1 = array(
				'field'   => 'Surname',
				'user_id' => $memberid,
			);
			
			$fname = bp_get_profile_field_data( $args );
			$lname = bp_get_profile_field_data( $args1 );
			
			$ifsa_log = $wpdb->prefix . 'ifsa_log';
			
			if ( isset( $_SERVER['REMOTE_ADDR'] ) && ! empty( sanitize_text_field($_SERVER['REMOTE_ADDR']) ) ) {
				$userip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
			}
			
			
			if ( $flag !== 1 ) {
				$query  = "INSERT INTO {$ifsa_log} ( log_action,remark, logged_in_user_id ,member_id, action_date, user_ip) VALUES ( %s,%s, %d, %d, %s, %s )"; // WPCS: unprepared SQL ok.
				$sqllog = $wpdb->prepare( $query, "$action", "$remark", $adminid, $memberid, $lastupdated, $userip );
				
				$resultlog = $wpdb->query( $sqllog );
				
				
				if ( empty( $resultlog ) || is_wp_error( $resultlog ) ) {
				
				}
				$flag = 1;
			}
			
			update_user_meta( $user_id, 'user_active_status', $ifsa_activation );
		}
	}
	
	/**
	 * Funcition to load  Plugin
	 */
	public function ifsa_load_plugin() {
		
		/* do stuff once right after activation */
		if ( ! function_exists( 'bp_is_active' ) ) {
			//if (is_admin() && get_option('Activated_Plugin') == 'ifsa-member-verification') {
			delete_option( 'Activated_Plugin' );
			
			add_action( 'admin_notices', array( $this, 'self_deactivate_notice' ) );
			
			// Check for proper function.
			if ( ! function_exists( 'deactivate_plugins' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			
			// Deactivate.
			deactivate_plugins( IFSA_MEMBER_VERIFICATION_PATH . 'ifsa-member-verification.php' );
			
			
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
			//    }
		}
	}
	
	/**
	 * Display an error message when parent plugin is missing
	 */
	public function self_deactivate_notice() {
		?>
		<div class="notice notice-error">
			Please install and activate Buddypress plugin before activating this plugin.
		</div>
		<?php
	}
	
	/**
	 * Registers the menu page on admin side
	 */
	public function ifsa_member_verification_admin_menu() {
		//Adding custom menu page Member Verification Plugin
		add_menu_page(
			__( 'Member Verification Plugin', 'Ifsa_Member_Verification' ),
			__( 'Member Verification Plugin', 'Ifsa_Member_Verification' ),
			'manage_options',
			'ifsa-verification',
			array( $this, 'ifsa_member_verification_contents' ),
			'dashicons-buddicons-buddypress-logo',
			65
		);
		
		//Adding sub menu page to the parent Member Verification page
		add_submenu_page( 'ifsa-verification', 'Add New Region', 'Add New Region', 'manage_options', 'post-new.php?post_type=regions' );
		add_submenu_page( 'ifsa-verification', 'Add New Committee', 'Add New Committee', 'manage_options', 'edit-tags.php?taxonomy=committee&post_type=regions' );
		
		add_submenu_page(
			'ifsa-verification', 'General Settings', 'General Settings', 'manage_options', 'ifsa-settings',
			array( $this, 'ifsa_member_verification_contents' )
		);
	}
	
	/**
	 * Call back Function for the Menu page
	 */
	public function ifsa_member_verification_contents() {
		//Admin page markup
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/ifsa-member-verification-admin-display.php';
	}
	
	/**
	 * Registering genetal setting section on the admin page
	 */
	public function ifsa_member_verification_general_settings() {
		//Adding general setting section __('General', 'Ifsa_Member_Verification')
		add_settings_section(
			'ifsa_member_verification_general_settings',
			'',
			array( $this, 'ifsa_member_verification_general_setting_callback' ),
			'ifsa_member_verification_general_settings'
		);
		register_setting( 'ifsa_member_verification_general_settings', 'ifsa_general_setting_date_field', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_general_settings', 'ifsa_step_1_description', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_general_settings', 'ifsa_step_2_description', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_general_settings', 'ifsa_step_3_description', array( $this, 'sanitize_textarea_field' ) );

		register_setting( 'ifsa_member_verification_general_settings', 'after_30', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_general_settings', 'after_21', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_general_settings', 'after_15', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_general_settings', 'before_30', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_general_settings', 'next_yr_valid', array( $this, 'sanitize_textarea_field' ) );
	}
	
	/**
	 * Function to insert the markup for the general setting section
	 */
	public function ifsa_member_verification_general_setting_callback() {
		//Markup for general page
		include_once plugin_dir_path( __FILE__ ) . 'partials/ifsa_member_verification_general_settings.php';
	}
	
	
	/**
	 * Registering the email settings page on the admin side
	 */
	public function ifsa_member_verification_email_settings() {
		//Adding email setting section __('Email', 'Ifsa_Member_Verification')
		add_settings_section(
			'ifsa_member_verification_email_settings',
			'',
			array( $this, 'ifsa_member_verification_email_setting_callback' ),
			'ifsa_member_verification_email_settings'
		);
		
		//Registering the settings field and its value in the option table
		register_setting( 'ifsa_member_verification_email_settings', 'welcome_email_after_verify_member', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'reminder_on_same_date_when_renewed', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'member_bulk_invite_for_join_the_community', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'fifteen_days_after_expire_date', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'reject_by_lc_admin_email_to_member', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'twentytwo_days_after_expire_date', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'thirty_days_before_renewal_date', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'thirty_days_after_expire_date', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'remove_member_content', array( $this, 'sanitize_textarea_field' ) );
	
		
		register_setting( 'ifsa_member_verification_email_settings', 'welcome_email_after_verify_member_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'reminder_on_same_date_when_renewed_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'member_bulk_invite_for_join_the_community_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'fifteen_days_after_expire_date_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'reject_by_lc_admin_email_to_member_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'twentytwo_days_after_expire_date_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'thirty_days_before_renewal_date_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'thirty_days_after_expire_date_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'remove_member_content_subject', array( $this, 'sanitize_textarea_field' ) );
	
	
	}
	
	/**
	 * Function to insert the markup for the general setting section
	 */
	public function ifsa_member_verification_email_setting_callback() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/ifsa_member_verification_email_settings.php';
	}
	
	/**
	 * Markup for the log settings page on the admin general settings page
	 */
	public function ifsa_member_verification_log_settings() {
		//Adding tab setting section__('Tab-3', 'Ifsa_Member_Verification')
		add_settings_section(
			'ifsa_member_verification_log_settings',
			'',
			array( $this, 'ifsa_member_verification_log_setting_callback' ),
			'ifsa_member_verification_log_settings'
		);
		//Registering the settings field and its value in the option table
		register_setting( 'ifsa_member_verification_log_settings', 'ifsa_member_verification_log_settings', array( $this, 'sanitize_textarea_field' ) );
	}
	
	/**
	 * Function to insert the markup for the general setting section
	 */
	public function ifsa_member_verification_log_setting_callback() {
		//WP List Table for the admin log page
		$ifsa_list_table = new Ifsa_List_Table();
		
	
		?>
				<form id="nds-user-list-form" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

				<?php
		$ifsa_list_table->prepare_items();
		$ifsa_list_table->search_box('Search', 'search');
		$ifsa_list_table->display();
		?>
		</form>
	<?php }
	
	/**
	 * Funciotn to register Region custom  post type
	 */
	public function ifsa_register_regions() {
		$labels = array(
			'menu_name'          => esc_html__( 'Regions', 'ifsa-member-verification' ),
			'name_admin_bar'     => esc_html__( 'Region', 'ifsa-member-verification' ),
			'add_new'            => esc_html__( 'Add Region', 'ifsa-member-verification' ),
			'add_new_item'       => esc_html__( 'Add new Region', 'ifsa-member-verification' ),
			'new_item'           => esc_html__( 'New Region', 'ifsa-member-verification' ),
			'edit_item'          => esc_html__( 'Edit Region', 'ifsa-member-verification' ),
			'view_item'          => esc_html__( 'View Region', 'ifsa-member-verification' ),
			'update_item'        => esc_html__( 'View Region', 'ifsa-member-verification' ),
			'all_items'          => esc_html__( 'All Regions', 'ifsa-member-verification' ),
			'search_items'       => esc_html__( 'Search Regions', 'ifsa-member-verification' ),
			'parent_item_colon'  => esc_html__( 'Parent Region', 'ifsa-member-verification' ),
			'not_found'          => esc_html__( 'No Regions found', 'ifsa-member-verification' ),
			'not_found_in_trash' => esc_html__( 'No Regions found in Trash', 'ifsa-member-verification' ),
			'name'               => esc_html__( 'Regions', 'ifsa-member-verification' ),
			'singular_name'      => esc_html__( 'Region', 'ifsa-member-verification' ),
		);
		$args   = array(
			'label'               => __( 'Region', 'text_domain' ),
			'description'         => __( 'Regions', 'text_domain' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => 'ifsa-verification',
			'menu_position'       => 5,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);
		register_post_type( 'Regions', $args );
	}
	
	
	/**
	 * Funciton to Register Custom Taxonomy
	 */
	public function ifsa_region_taxonomy() {
		
		$labels = array(
			'name'                       => _x( 'Committees', 'Committee General Name', 'ifsa-member-verification' ),
			'singular_name'              => _x( 'Committee', 'Committee Singular Name', 'ifsa-member-verification' ),
			'menu_name'                  => __( 'Committee', 'ifsa-member-verification' ),
			'all_items'                  => __( 'All Items', 'ifsa-member-verification' ),
			'parent_item'                => __( 'Parent Item', 'ifsa-member-verification' ),
			'parent_item_colon'          => __( 'Parent Item:', 'ifsa-member-verification' ),
			'new_item_name'              => __( 'New Item Name', 'ifsa-member-verification' ),
			'add_new_item'               => __( 'Add New Item', 'ifsa-member-verification' ),
			'edit_item'                  => __( 'Edit Item', 'ifsa-member-verification' ),
			'update_item'                => __( 'Update Item', 'ifsa-member-verification' ),
			'view_item'                  => __( 'View Item', 'ifsa-member-verification' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'ifsa-member-verification' ),
			'add_or_remove_items'        => __( 'Add or remove items', 'ifsa-member-verification' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'ifsa-member-verification' ),
			'popular_items'              => __( 'Popular Items', 'ifsa-member-verification' ),
			'search_items'               => __( 'Search Items', 'ifsa-member-verification' ),
			'not_found'                  => __( 'Not Found', 'ifsa-member-verification' ),
			'no_terms'                   => __( 'No items', 'ifsa-member-verification' ),
			'items_list'                 => __( 'Items list', 'ifsa-member-verification' ),
			'items_list_navigation'      => __( 'Items list navigation', 'ifsa-member-verification' ),
		);
		$args   = array(
			'labels'            => $labels,
			
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
			'hierarchical' => false,
'parent_item'  => null,
'parent_item_colon' => null,
		);
		register_taxonomy( 'committee', array( 'regions' ), $args );
	}
	
	/**
	 * Delete user from member list
	 */
	public function ifsa_custom_remove_user($user_id) {
		global $wpdb;
		$removefromdb = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}ifsa_lc_member WHERE user_id = %d", $user_id));
		
	}

	public function ifsa_hide_dashboard() {
		if ( ! current_user_can( 'manage_options' ) && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			 wp_redirect(home_url()); exit;
		 }
	   }

	
	   /**
	 * Funciton is use to download csv file
	 */
	public function export_csv_callback() {
		
		global $wpdb;
		global $bp;
		
		$lastupdated = bp_core_current_time();
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'export_csv_file' && is_admin() ) {
			
			// Query
			$statement = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ifsa_log " ));
			
			// file creation
			$wp_filename = "filename_" . gmdate( "d-m-y" ) . ".csv";
			
			// Clean object
			ob_end_clean();
			
			// Open file
			$wp_file = fopen( $wp_filename, "w" );
			$fields  = array( 'id', 'Actions', 'Remarks', 'LC Admin', 'LC member', 'Date Time','IP' );
			
			fputcsv( $wp_file, $fields );
			
			// loop for insert data into CSV file
			
				foreach ( $statement as $ifsa_log_data ) {
					$lcadminname  = get_user_by( 'id', $ifsa_log_data->logged_in_user_id );
					$lcmembername = get_user_by( 'id', $ifsa_log_data->member_id );
					$wp_array = array(
						'id'                => $ifsa_log_data->id,
						'log_action'        => $ifsa_log_data->log_action,
						'remark'            => strip_tags($ifsa_log_data->remark),
						'logged_in_user_id' => $lcadminname->display_name,
						'member_id'         => $lcmembername->display_name,
						'action_date'       =>  date( 'F j, Y', strtotime( $ifsa_log_data->action_date )) ,
						'user_ip'           => $ifsa_log_data->user_ip,
					);
				
				
				fputcsv( $wp_file, $wp_array );
			}
			
			
			// Close file
			fclose( $wp_file );
			
			// download csv file
			header( "Content-Description: File Transfer" );
			header( "Content-Disposition: attachment; filename=" . $wp_filename );
			header( "Content-Type: application/csv;" );
			readfile( $wp_filename );
			exit;
		} else {
		
		}
	}

	
	   
}
