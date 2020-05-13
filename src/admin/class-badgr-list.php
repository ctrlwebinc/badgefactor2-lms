<?php
/**
 * Badge Factor 2
 * Copyright (C) 2019 ctrlweb
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @package Badge_Factor_2
 */

namespace BadgeFactor2\Admin;

use BadgeFactor2\BadgrClient;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Undocumented class
 */
class Badgr_List extends \WP_List_Table {

	/**
	 * Model class to use.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Singular name for this Badgr object.
	 *
	 * @var string
	 */
	protected $singular;

	/**
	 * Plural name for this Badgr object.
	 *
	 * @var string
	 */
	protected $plural;

	/**
	 * Slug to use for this Badgr object.
	 *
	 * @var string
	 */
	protected $slug;


	/**
	 * Class constructor.
	 *
	 * @param string $model Model class.
	 * @param string $singular Singular name.
	 * @param string $plural Plural name.
	 * @param string $slug Slug to use.
	 */
	public function __construct( $model, $singular, $plural, $slug ) {

		$this->model    = $model;
		$this->singular = $singular;
		$this->plural   = $plural;
		$this->slug     = $slug;

		parent::__construct(
			array(
				'singular' => $singular,
				'plural'   => $plural,
				'ajax'     => false,
			)
		);

	}

	/**
	 * Retrieve all records from Badgr provider.
	 *
	 * @param int $per_page Number of records per page.
	 * @param int $page_number Page number.
	 *
	 * @return mixed
	 */
	public function all( $per_page = 10, $page_number = 1 ) {
		return $this->model::all( $per_page, $page_number );
	}

	/**
	 * Delete a record through Badgr provider.
	 *
	 * @param int $id customer ID.
	 */
	public function delete( $id ) {
		// TODO.
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public function record_count() {
		$objects = $this->model::all();
		if ( $objects ) {
			return count( $objects );
		}
		return 0;
	}


	/**
	 * Text displayed when no record is available.
	 */
	public function no_items() {
		// Translators: This will be displayed if no object is returned by Badgr.
		printf( __( 'No %s avaliable.', 'badgefactor2' ), $this->singular );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		$return = '';
		foreach ( $this->model::get_columns() as $column_slug => $column_title ) {
			if ( $column_name === $column_slug ) {
				switch ( $column_slug ) {
					case 'image':
						$return .= '<img style="width:50%" src="' . $item->$column_name . '">';
						break;
					case 'issuer':
						$return .= $item->$column_name;
						break;
					case 'entityId':
						$return .= '<a href="admin.php?page=' . $this->slug . '&action=edit&entity_id=' . $item->$column_name . '">' . $item->$column_name . '</a>';
						break;
					case 'issuerOpenBadgeId':
						$return .= '<a href="admin.php?page=' . $this->slug . '&action=edit&entity_id=' . $item->$column_name . '">' . $item->$column_name . '</a>';
						break;
					case 'createdAt':
						$date = strtotime( $item->$column_name );
						$return .= '<span style="font-size: 0.85em">' . gmdate( 'Y-m-d&\nb\s\p;H:i:s', $date ) . '</span>';
						break;
					default:
						$return .= $item->$column_name;
						break;
				}
				return $return;
			}
		}
		// Show the whole array for troubleshooting purposes.
		return print_r( $item, true );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />',
			$item->entityId
		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'bf2_delete_' . $this->slug );

		$title = $item->name;

		$actions = array(
			'delete' => '', //sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce ),
		);

		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array_merge(
			array(
				'cb' => '<input type="checkbox" />',
			),
			$this->model::get_columns()
		);

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {

		return $this->model::get_sortable_columns();
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => 'Delete',
		);

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( $this->slug . '_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = $this->record_count();

		$this->set_pagination_args(
			array(
				'total_items' => $total_items, //WE have to calculate the total number of items
				'per_page'    => $per_page, //WE have to determine how many items to show on a page
			)
		);

		$this->items = $this->all( $per_page, $current_page );
	}


	public function display() {
		if ( isset( $_GET['action'] ) ) {
			switch ( $_GET['action'] ) {
				case 'new':
					if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
						$entity_id = $this->model::create( $_POST, $_FILES );
						// TODO redirect to edit with save message.
					}
					include BF2_ABSPATH . 'templates/admin/tpl.edit-' . $this->slug . '.php';
					break;
				case 'edit':
					if ( isset( $_GET['entity_id'] ) ) {
						// Entity ID is set.

						$entity = $this->model::get( $_GET['entity_id'] );
						if ( false === $entity ) {
							wp_die( __( 'You attempted to edit an item that doesn\'t exist. Perhaps it was deleted?', 'badgefactor2' ) );
						}

						if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
							$entity_id = $this->model::update( $_GET['entity_id'], $_POST );
						}
						include BF2_ABSPATH . 'templates/admin/tpl.edit-' . $this->slug . '.php';
					} else {
						//Entity ID is not set.
						wp_die( __( 'You are missing an entity ID.', 'badgefactor2' ) );
					}

					break;
			}
		} else {
			parent::display();
		}

	}

	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'sp_delete_' . $this->slug ) ) {
				die( 'Go get a life script kiddies' );
			} else {
				$this->delete( absint( $_GET[ $this->slug ] ) );

				// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
				// add_query_arg() return the current url.
				wp_redirect( esc_url_raw( add_query_arg() ) );
				exit;
			}
		}

		// If the delete bulk action is triggered.
		if ( ( isset( $_POST['action'] ) && 'bulk-delete' === $_POST['action'] )
			|| ( isset( $_POST['action2'] ) && 'bulk-delete' === $_POST['action2'] )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				$this->delete( $id );

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
			// add_query_arg() return the current url.
			wp_redirect( esc_url_raw( add_query_arg() ) );
			exit;
		}
	}


	/**
	 * Undocumented function
	 *
	 * @param string $which
	 * @return void
	 */
	public function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			if ( BadgrClient::is_active() ) {
				echo '<div class="alignleft actions"><a class="button action button-primary" href="admin.php?page=' . $this->slug . '&action=new">' . __( 'Add New', 'badgefactor2' ) . '</a></div>';
			} else {
				echo __( 'Badgr connection inactive!', 'badgefactor2' );
			}
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return bool
	 */
	public function validate() {
		// Defaults to false. Must be implemented in child class.
		return false;
	}

}
