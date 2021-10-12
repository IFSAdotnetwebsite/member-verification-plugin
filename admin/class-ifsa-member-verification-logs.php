<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/public
 * @author     Multidots <nishit.langaliya@multidots.com>
 * @license    GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link       #
 * @since      1.0.0
 */

/**
 * WP_List_Table is not loaded automatically so we need to load it in our application
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Create a new table class that will extend the WP_List_Table
 *
 * @package Ifsa_Member_Verification
 * @author  Multidots <plugin@multidots.com>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    #
 */
class Ifsa_List_Table extends WP_List_Table {
	
	/**
	 * Initializing the ajax array
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'singular_form',
				'plural'   => 'plural_form',
				'ajax'     => false,
			)
		);
		
	}
	
	/**
	 * Prepare the items for the table to process
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		
		$this->process_bulk_action();
		
		$data = $this->table_data();
		usort( $data, array( &$this, 'sort_data' ) );
		
		$perPage     = 20;
		$currentPage = $this->get_pagenum();
		$totalItems  = count( $data );
		
		$this->set_pagination_args(
			array(
				'total_items' => $totalItems,
				'per_page'    => $perPage,
			)
		);
		
		$data = array_slice( $data, ( ( $currentPage - 1 ) * $perPage ), $perPage );
		
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;
	}
	
	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return Array
	 */
	public function get_columns() {
		$columns = array(
			'cb'                => '<input type="checkbox" />',
			'id'                => 'ID',
			'log_action'        => 'Actions',
			'remark'            => 'Remarks',
			'logged_in_user_id' => 'LC Admin',
			'member_id'         => 'LC member',
			'action_date'       => 'Date Time',
			'user_ip'           => 'IP',
		);
		
		return $columns;
	}
	
	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {
		
		$title = '<strong>' . $item['name'] . '</strong>';
		
		if ( isset( $_REQUEST['page'] ) && ! empty( $_REQUEST['page'] ) ) {
			$page = sanitize_text_field( $_REQUEST['page'] );
		}
		
		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $page ), 'delete', absint( $item['ID'] ), $delete_nonce ),
		];
		
		return $title . $this->row_actions( $actions );
	}
	
	
	/**
	 * Define which columns are hidden
	 *
	 * @return Array
	 */
	public function get_hidden_columns() {
		return array( 'id' );
	}
	
	/**
	 * Bulk actions for the Member verification admin logs
	 *
	 * @return Array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete',
		);
		
		return $actions;
	}
	
	/**
	 * Checkbox for the Member verification admin logs table
	 *
	 * @return Array
	 */
	public function process_bulk_action() {
		
		// security check during bulk action!
		if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {
			
			$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
			$action = 'bulk-' . $this->_args['plural'];
			
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_die( 'Security check failed!' );
			}
			
		}
		
		$action = $this->current_action();
		switch ( $action ) {
			case 'delete':
				global $wpdb;
			//	$table_name = $wpdb->prefix . 'ifsa_log';
				
				if ( 'delete' === $action ) {
					$ids = isset( $_REQUEST['id'] ) ? ( $_REQUEST['id'] ) : array(); // WPCS: sanitization ok.
					if ( is_array( $ids ) ) {
						$ids = implode( ',', $ids );
					}
					
					if ( ! empty( $ids ) ) {
						$wpdb->query( "DELETE FROM {$wpdb->prefix}ifsa_log WHERE id IN($ids)" ); // WPCS: unprepared SQL ok.
					}
				}
				break;
			default:
				return;
				break;
		}
		
		return;
	}
	
	/**
	 * Define the Checkbox column
	 *
	 * @return Array
	 */
	public function column_cb( $item ) {
		//exit(item['id'])
		return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />', $item['id']
		);
	}
	
	/**
	 * Define the sortable columns
	 *
	 * @return Array
	 */
	public function get_sortable_columns() {
		return array( 'id' => array( 'id', true ) );
	}
	
	/**
	 * Get the table data from the wp_ifsa_logs table
	 *
	 * @return Array
	 */
	private function table_data() {
		global $wpdb;
		
		//die;
		$data     = array();
	//	$ifsa_log = $wpdb->prefix . 'ifsa_log';

	$search = ( isset( $_REQUEST['s'] ) ) ? trim($_REQUEST['s']) : 'false';

	$s1 =  get_option('temp_ifsa_search',true);
	if ($search == 'false' && $s1 != 'false' && $_REQUEST['paged'] > 1){
		$search = $s1;
		
	}
	//$do_search = ( $search ) ? $wpdb->prepare(" AND post_content LIKE '%%%s%%' ", $search ) : ''; 
	$dtsearch = date('Y-m-d', strtotime($search));

	
	global $wpdb;
	$users = $wpdb->get_results( "SELECT * FROM $wpdb->users WHERE user_nicename Like '%$search%' OR user_login Like '%$search%'" );
	if ($search !='false') {
		
		update_option('temp_ifsa_search',$search);
		

	if( $users ) {
	//	echo print_r($users,true );
			$usersid = $users[0]->ID;
			$results  = $wpdb->get_results($wpdb->prepare("SELECT * FROM  {$wpdb->prefix}ifsa_log WHERE member_id = $usersid OR logged_in_user_id = $usersid or log_action Like '%$search%' OR remark Like '%$search%' OR action_date Like '$dtsearch%' OR user_ip Like '%$search%' " )); 
	}else  {
		$results  = $wpdb->get_results($wpdb->prepare("SELECT * FROM  {$wpdb->prefix}ifsa_log WHERE  log_action Like '%$search%' OR remark Like '%$search%' OR action_date Like '$dtsearch%' OR user_ip Like '%$search%' " ));
	}

	//echo print_r($wpdb,true);
	///die;
		
		
	}else {
		delete_option('temp_ifsa_search');
	$results  = $wpdb->get_results($wpdb->prepare("SELECT * FROM  {$wpdb->prefix}ifsa_log" ));
	}
		if ( $results || ! is_wp_error( $results ) ) {
			
			foreach ( $results as $ifsa_log_data ) {
				$lcadminname  = get_user_by( 'id', $ifsa_log_data->logged_in_user_id );
				$lcmembername = get_user_by( 'id', $ifsa_log_data->member_id );
				$data[]       = array(
					'id'                => $ifsa_log_data->id,
					'log_action'        => $ifsa_log_data->log_action,
					'remark'            => $ifsa_log_data->remark,
					'logged_in_user_id' => $lcadminname->display_name,
					'member_id'         => $lcmembername->display_name,
					'action_date'       =>  date( 'F j, Y', strtotime( $ifsa_log_data->action_date )) ,
					'user_ip'           => $ifsa_log_data->user_ip,
				);
			}
			
		}
		
		return $data;
	}
	
	/**
	 * Define what data to show on each column of the table
	 *
	 * @param Array  $item        Data
	 * @param String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
			case 'log_action':
			case 'remark':
			case 'logged_in_user_id':
			case 'member_id':
			case 'action_date':
			case 'user_ip':
				return $item[ $column_name ];
			
			default:
				return print_r( $item, true );
		}
	}
	
	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @return Mixed
	 */
	private function sort_data( $a, $b ) {
		// Set defaults
		$orderby = 'id';
		$order   = 'desc';
		
		// If orderby is set, use this as the sort column
		if ( isset( $_GET['order'] ) && ! empty( $_GET['orderby'] ) ) {
			$orderby = sanitize_text_field( $_GET['orderby'] );
		}
		
		// If order is set use this as the order
		if ( isset( $_GET['order'] ) && ! empty( $_GET['order'] ) ) {
			$order = sanitize_text_field( $_GET['order'] );
		}
		
		
		$result = strcmp( $a[ $orderby ], $b[ $orderby ] );
		
		if ( $order === 'asc' ) {
			return $result;
		}
		
		return - $result;
	}

	public function search_box( $text, $input_id ) {
        if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
            return;
        }
 
		$input_id = $input_id . '-search-input';
		
	
	
 
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
        }
        if ( ! empty( $_REQUEST['order'] ) ) {
            echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
        }
       
        ?>
<div class="cls-searchbox">
<div class="cls-seperator_div2">
					<?php $downloadurl = site_url( '/wp-admin/admin.php?page=ifsa-settings&tab=ifsa_member_verification_log_settings&action=export_csv_file' );
					$downloadurl       = ( $downloadurl ); ?>
					<p><a class="cls-seperator_download_user-btn button-primary" href="<?php echo esc_url( $downloadurl ); ?>">Download
							Logs</a></p>
				</div>

<p class="search-box">
    <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
    <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
        <?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
</p>

</div>
        <?php
    }
}
