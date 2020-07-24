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
 *
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 */

namespace BadgeFactor2\Admin;

use BadgeFactor2\BadgrClient;
use BadgeFactor2\BadgrProvider;
use BadgeFactor2\Models\BadgeClass;
use BadgeFactor2\Models\Issuer;
use BadgeFactor2\Singleton;
use GuzzleHttp\Exception\ClientException;
use stdClass;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Badgr List class.
 */
class Badgr_List extends \WP_List_Table {

	use Singleton;

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
	 * Filter.
	 *
	 * @var filter
	 */
	protected $filter;


	/**
	 * Class constructor.
	 *
	 * @param string $model Model class.
	 * @param string $singular Singular name.
	 * @param string $plural Plural name.
	 * @param string $slug Slug to use.
	 * @param array  $filter Filter to use.
	 */
	public function __construct( $model, $singular, $plural, $slug, $filter = array() ) {

		$this->model    = $model;
		$this->singular = $singular;
		$this->plural   = $plural;
		$this->slug     = $slug;
		$this->filter   = $filter;

		add_action( 'admin_notices', array( $this, 'notice_created' ) );

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
	 * @param int   $per_page Number of records per page.
	 * @param int   $page_number Page number.
	 * @param array $filter Filter to use.
	 *
	 * @return mixed
	 */
	public function all( $per_page = 10, $page_number = 1, $filter = array() ) {
		$list = $this->model::all( $per_page, $page_number );
		if ( $list ) {
			$list_class = get_class( $this );
			foreach ( $list as $i => $item ) {
				$list[ $i ]->listClass = $list_class;
			}
		}
		return $list;
	}

	/**
	 * Delete a record through Badgr provider.
	 *
	 * @param int $id customer ID.
	 */
	public function delete( $id ) {
		return $this->model::delete( $id );
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public function record_count() {
		$objects = $this->model::all( -1 );
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
		printf( __( 'No %s avaliable.', BF2_DATA['TextDomain'] ), $this->singular );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param object $item Item.
	 * @param string $column_name Column name.
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		$return = '';
		foreach ( $this->model::get_columns() as $column_slug => $column_title ) {
			if ( $column_name === $column_slug ) {
				switch ( $column_slug ) {
					case 'issuer':
						$issuer  = Issuer::get( $item->$column_name );
						$return .= '<a href="admin.php?page=issuers&action=edit&entity_id=' . $item->$column_name . '">' . $issuer->name . '</a>';
						break;
					case 'createdAt':
						$date    = strtotime( $item->$column_name );
						$return .= '<span style="font-size: 0.85em">' . gmdate( 'Y-m-d&\nb\s\p;H:i:s', $date ) . '</span>';
						break;
					case 'badgeclass':
						$badge   = BadgeClass::get( $item->$column_name );
						$return .= '<a href="admin.php?page=badges&action=edit&entity_id=' . $item->$column_name . '">' . $badge->name . '</a>';
						break;
					case 'recipient':
						$recipient = $item->recipient;
						$return   .= '<a href="users.php?action=-1&s=' . $recipient->plaintextIdentity . '">' . $recipient->plaintextIdentity . '</a>';
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
	 * @param object $item Item.
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="entity_id[]" value="%s" />',
			$item->entityId
		);
	}


	/**
	 * Method for name column
	 *
	 * @param object $item an array of DB data.
	 *
	 * @return string
	 */
	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'bf2_delete_' . $this->slug );

		$title = '<a href="admin.php?page=' . $this->slug . '&action=edit&entity_id=' . $item->entityId . '">' . $item->name . '</a>';

		$actions = array(
			'delete' => sprintf( '<a onclick="if(!confirm( \'%s\' ) ) { event.preventDefault() }" href="?page=%s&action=%s&entity_id=%s&_wpnonce=%s">%s</a>', __( 'Are you sure you want to delete this item?', BF2_DATA['TextDomain'] ), $this->slug, 'delete', $item->entityId, $delete_nonce, __( 'Delete', BF2_DATA['TextDomain'] ) ),
		);

		return $title . $this->row_actions( $actions );
	}


	/**
	 * Undocumented function.
	 *
	 * @param [type] $item Item.
	 * @return string
	 */
	function column_image( $item ) {
		if ( 'Assertion' === $item->entityType ) {
			$revoke_nonce = wp_create_nonce( 'bf2_revoke_' . $this->slug );
			$badge        = BadgeClass::get( $item->badgeclass );
			if ( $item->revoked ) {
				$title   = '<a href="admin.php?page=assertions&action=edit&entity_id=' . $item->entityId . '">' . $badge->name . '</a><br/>' . __( 'REVOKED!', BF2_DATA['TextDomain'] );
				$actions = array();
			} else {
				$title   = '<a href="admin.php?page=assertions&action=edit&entity_id=' . $item->entityId . '">' . $badge->name . '</a>';
				$actions = array(
					'revoke' => sprintf( '<a href="?page=%s&action=%s&entity_id=%s">%s</a>', $this->slug, 'revoke', $item->entityId, __( 'Revoke', BF2_DATA['TextDomain'] ) ),
				);
			}

			return $title . $this->row_actions( $actions );
		} else {
			return '<img style="width:50%" src="' . $item->image . '">';
		}
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
			'delete' => __( 'Delete', BF2_DATA['TextDomain'] ),
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

		$per_page     = $_GET['posts_per_page'] ?? 10;
		$current_page = $_GET['paged'] ?? 1;
		$total_items  = $this->record_count();

		$this->set_pagination_args(
			array(
				'total_items' => $total_items, // We have to calculate the total number of items.
				'per_page'    => $per_page, // We have to determine how many items to show on a page.
			)
		);

		$this->items = $this->all( $per_page, $current_page );
	}


	/**
	 * Display.
	 *
	 * @return void
	 */
	public function display() {
		global $wp;
		if ( isset( $_GET['action'] ) ) {
			$this->manage_actions();
		} else {
			echo '<form id="bf2-admin-filter" method="get">
    		<input type="hidden" name="page" value="' . $_REQUEST['page'] . '" />';
			parent::display();
			echo '</form>';
		}
	}


	/**
	 * Manage actions.
	 *
	 * @return void
	 */
	private function manage_actions() {
		switch ( $_GET['action'] ) {
			case 'new':
				if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
					$entity_id = $this->model::create( $_POST, $_FILES );
					if ( $entity_id ) {
						$redirect_url = str_replace( '&action=new', '&notice=created', $_SERVER['REQUEST_URI'] );
						$this->redirect( $redirect_url );
					} else {
						$entity = new stdClass;
						foreach ( $_POST as $key => $value ) {
							$entity->{$key} = $value;
						}
					}
				}
				include BF2_ABSPATH . 'templates/admin/tpl.edit-' . $this->slug . '.php';
				break;
			case 'edit':
				if ( ! isset( $_GET['entity_id'] ) ) {
					// Entity ID is not set.
					wp_die( __( 'You are missing an entity ID.', BF2_DATA['TextDomain'] ) );
				} else {
					// Entity ID is set.
					try {
						$entity = $this->model::get( $_GET['entity_id'] );
						if ( false === $entity ) {
							wp_die( __( 'You attempted to edit an item that doesn\'t exist. Perhaps it was deleted?', BF2_DATA['TextDomain'] ) );
						}

						if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
							$entity_id      = $this->model::update( $_GET['entity_id'], $_POST );
							$_GET['notice'] = 'updated';
						}

						include BF2_ABSPATH . 'templates/admin/tpl.edit-' . $this->slug . '.php';

					} catch ( ClientException $e ) {
						if ( 404 === $e->getResponse()->getStatusCode() ) {
							echo '<div class="notice notice-error is-dismissible"><p>' . __( 'Item does not exist.', BF2_DATA['TextDomain'] ) . '</p></div>';
						} elseif ( 400 === $e->getResponse()->getStatusCode() ) {
							echo '<div class="notice notice-error is-dismissible"><p>' . __( 'Item cannot be edited.', BF2_DATA['TextDomain'] ) . '</p></div>';
						} else {
							echo $e->getMessage();
						}
					}
				}
				break;
			case 'revoke':
				if ( ! isset( $_GET['entity_id'] ) ) {
					// Entity ID is not set.
					wp_die( __( 'You are missing an entity ID.', BF2_DATA['TextDomain'] ) );
				} else {
					// Entity ID is set.
					$entity = $this->model::get( $_GET['entity_id'] );
					if ( false === $entity ) {
						wp_die( __( 'You attempted to edit an item that doesn\'t exist. Perhaps it was deleted?', BF2_DATA['TextDomain'] ) );
					}

					if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
						// Revoke.

						if ( $_GET['entity_id'] !== $_POST['assertion'] ) {
							wp_die( __( 'You attempted to edit an item that doesn\'t exist. Perhaps it was deleted?', BF2_DATA['TextDomain'] ) );
						} else {
							BadgrProvider::revoke_assertion( $_POST['assertion'], $_POST['reason'] );
							$_GET['notice'] = 'revoked';
						}
					}
					include BF2_ABSPATH . 'templates/admin/tpl.revoke-' . $this->slug . '.php';
				}
				break;
		}
	}


	/**
	 * Notice created.
	 *
	 * @return void
	 */
	public function notice_created() {
		global $pagenow;
		if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && isset( $_GET['notice'] ) && 'created' === $_GET['notice'] ) :
			?>
		<div class="updated settings-error notice is-dismissible"> 
			<p><strong><?php echo __( 'Object created.', BF2_DATA['TextDomain'] ); ?></strong></p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php echo __( 'Dismiss this message.', BF2_DATA['TextDomain'] ); ?></span>
			</button>
		</div>
			<?php
		endif;
	}


	/**
	 * Process bulk actions.
	 *
	 * @return void
	 */
	public function process_bulk_action() {

		// Detect action triggered.
		if ( 'delete' === $this->current_action() ) {
			if ( ! is_array( $_GET['entity_id'] ) ) {
				// Single delete.
				// In our file that handles the request, verify the nonce.
				$nonce = esc_attr( $_REQUEST['_wpnonce'] );

				if ( ! wp_verify_nonce( $nonce, 'bf2_delete_' . $this->slug ) ) {
					die( 'Go get a life script kiddies' );
				} else {
					try {
						$this->delete( $_GET['entity_id'] );
						echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Item deleted.', BF2_DATA['TextDomain'] ) . '</p></div>';
					} catch ( ClientException $e ) {
						if ( 404 === $e->getResponse()->getStatusCode() ) {
							echo '<div class="notice notice-error is-dismissible"><p>' . __( 'Item does not exist.', BF2_DATA['TextDomain'] ) . '</p></div>';
						} elseif ( 400 === $e->getResponse()->getStatusCode() ) {
							echo '<div class="notice notice-error is-dismissible"><p>' . __( 'Item cannot be deleted.', BF2_DATA['TextDomain'] ) . '</p></div>';
						}
					}
				}
			} else {
				// Bulk delete.
				// In our file that handles the request, verify the nonce.
				$nonce = esc_attr( $_REQUEST['_wpnonce'] );
				if ( ! wp_verify_nonce( $nonce, 'bulk-' . $this->_args['plural'] ) ) {
					die( 'Go get a life script kiddies' );
				} else {
					foreach ( $_GET['entity_id'] as $id ) {
						$this->delete( $id );
					}
					echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Items deleted.', BF2_DATA['TextDomain'] ) . '</p></div>';
				}
			}
		}
	}


	/**
	 * Extra table navigation
	 *
	 * @param string $which Which.
	 * @return void
	 */
	public function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			if ( BadgrClient::is_active() ) {
				echo '<div class="alignleft actions">';
				if ( 'assertions' === $_GET['page'] ) {
					if ( isset( $_GET['filter_type'] ) && isset( $_GET['filter_value'] ) ) {
						$filter_type  = stripslashes( $_GET['filter_type'] );
						$filter_value = stripslashes( $_GET['filter_value'] );
						echo '<a class="button action button-primary" href="admin.php?page=' . $this->slug . '&filter_type=' . $filter_type . '&filter_value=' . $filter_value . '&action=new">' . __( 'Add New', BF2_DATA['TextDomain'] ) . '</a>';
					}
				} else {
					echo '<a class="button action button-primary" href="admin.php?page=' . $this->slug . '&action=new">' . __( 'Add New', BF2_DATA['TextDomain'] ) . '</a>';
				}
				if ( ! empty( $this->filter ) ) {
					echo '<input type="hidden" name="filter_for" value="' . get_class( $this ) . '">';
					echo '<select name="filter_type">';
					echo '<option value="">' . __( 'List type', BF2_DATA['TextDomain'] ) . '</option>';
					$disabled = 'disabled';
					if ( isset( $_GET['filter_type'] ) ) {
						$selected_filter = $_GET['filter_type'];
					}
					foreach ( $this->filter as $filter ) {
						$filter       = $filter::get_instance();
						$filter_class = ( new \ReflectionClass( get_class( $filter ) ) )->getShortName();
						$selected     = '';
						if ( $filter_class === $selected_filter ) {
								$selected = ' selected';
								$disabled = '';
						}
						echo "<option value='{$filter_class}'{$selected}>{$filter->singular}</option>";
					}
					echo '</select>';
					echo "<select name='filter_value' {$disabled}>";
					echo '<option value="">' . __( 'List for', BF2_DATA['TextDomain'] ) . '</option>';
					if ( ! $disabled ) {
						$selected_filter = 'BadgeFactor2\Admin\Lists\\' . $selected_filter;
						$filter          = new $selected_filter();
						$selected_filter = null;
						if ( isset( $_GET['filter_value'] ) ) {
							$selected_filter = stripslashes( $_GET['filter_value'] );
						}
						foreach ( $filter->all() as $filter ) {
							$selected = '';
							if ( $filter->entityId === $selected_filter ) {
								$selected = 'selected';
							}
							echo "<option value='{$filter->entityId}' {$selected}>{$filter->name}</option>";
						}
					}
					echo '</select>';
				}
				echo '</div>';
			} else {
				echo __( 'Badgr connection inactive!', BF2_DATA['TextDomain'] );
			}
		}
	}

	/**
	 * Validate
	 *
	 * @return bool
	 */
	public function validate() {
		// Defaults to false. Must be implemented in child class.
		return false;
	}

	/**
	 * Get model.
	 *
	 * @return string Model class.
	 */
	public function get_model() {
		return $this->model;
	}


	/**
	 * Redirects page with javascript.
	 *
	 * @param string $url URL to which to redirect.
	 * @return void
	 */
	private function redirect( $url ) {
		$string  = '<script type="text/javascript">';
		$string .= 'window.location = "' . $url . '"';
		$string .= '</script>';

		echo $string;
		exit;
	}

}
