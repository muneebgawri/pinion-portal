<?php
namespace Jet_Engine\CPT\Custom_Tables;

/**
 * Meta storage management class
 */
class Meta_Storage {

	private $db;
	private $object_type;
	private $object_slug;
	private $fields;

	public function __construct( $db = null, $object_type = 'post', $object_slug = '', $fields = [] ) {

		$this->db          = $db;
		$this->object_type = $object_type;
		$this->object_slug = $object_slug;
		$this->fields      = $fields;

		$this->hook_crud_handlers();

	}

	/**
	 * Hook CRUD action to appropriate WP functions
	 * 
	 * @return [type] [description]
	 */
	public function hook_crud_handlers() {

		$object_type = $this->object_type;

		// Create
		add_filter(
			'add_' . $object_type . '_metadata', 
			function( $result = null, $object_id = 0, $meta_key = '', $meta_value = '', $unique = false ) use ( $object_type ) {

				if ( ! $this->is_custom_field_from_storage( $object_type, $object_id, $meta_key ) ) {
					return $result;
				}

				return $this->add_data( $object_id, $meta_key, $meta_value );

			},
			0, 5
		);

		// Read
		add_filter(
			'get_' . $object_type . '_metadata', 
			function( $result = null, $object_id = 0, $meta_key = '', $single = true ) use ( $object_type ) {

				if ( ! $this->is_custom_field_from_storage( $object_type, $object_id, $meta_key ) ) {
					return $result;
				}

				return $this->get_data( $object_id, $meta_key, $single );

			},
			0, 4 
		);

		// Update
		add_filter(
			'update_' . $object_type . '_metadata',
			function( $result = null, $object_id = 0, $meta_key = '', $meta_value = '', $prev = false ) use ( $object_type ) {

				if ( ! $this->is_custom_field_from_storage( $object_type, $object_id, $meta_key ) ) {
					return $result;
				}

				return $this->add_data( $object_id, $meta_key, $meta_value );

			},
			0, 5
		);

		// Delete
		add_filter(
			'delete_' . $object_type . '_metadata',
			function( $result = null, $object_id = 0, $meta_key = '' ) use ( $object_type ) {

				if ( ! $this->is_custom_field_from_storage( $object_type, $object_id, $meta_key ) ) {
					return $result;
				}

				return $this->delete_data( $object_id, $meta_key );

			},
			0, 3
		);

		$this->on_object_delete();

	}

	/**
	 * Hook clearing metadata on object delete
	 * 
	 * @return [type] [description]
	 */
	public function on_object_delete() {

		$object_type = $this->object_type;

		switch ( $this->object_type ) {
			case 'post':

				add_action( 'delete_post', function( $post_id, $post ) use ( $object_type ) {

					if ( ! $this->is_object_of_type( $object_type, $post_id ) ) {
						return;
					}

					$this->clear_data( $post_id );

				}, 0, 2 );

				break;
		}

	}

	/**
	 * Check if given custom field is stored in the custom DB table
	 * 
	 * @param  [type]  $object_type [description]
	 * @param  [type]  $object_id   [description]
	 * @param  [type]  $meta_key    [description]
	 * @return boolean              [description]
	 */
	public function is_custom_field_from_storage( $object_type, $object_id, $meta_key ) {
		
		if ( ! in_array( $meta_key, $this->fields ) ) {
			return false;
		}

		if ( ! $this->is_object_of_type( $object_type, $object_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if given object of current type
	 * 
	 * @param  string  $object_type [description]
	 * @param  integer $object_id   [description]
	 * @return boolean              [description]
	 */
	public function is_object_of_type( $object_type = 'post', $object_id = 0 ) {

		if ( ! $object_id ) {
			return false;
		}

		$result = false;

		switch ( $object_type ) {
			case 'post':
				$object = get_post( $object_id );

				if ( $object && ! is_wp_error( $object ) ) {
					$result = ( $object->post_type === $this->object_slug ) ? true : false;
				}

				break;

		}

		return $result;
	}

	/**
	 * Delete all data related to $object_id
	 * 
	 * @param  [type] $object_id [description]
	 * @return [type]            [description]
	 */
	public function clear_data( $object_id ) {
		$this->db->delete( [ 'object_ID' => $object_id ] );
		wp_cache_delete( $this->get_cache_key( $object_id ), 'jet_engine_custom_tables' );
	}

	/**
	 * Delete single meta field value from custom table
	 * 
	 * @param  integer $object_id [description]
	 * @param  string  $meta_key  [description]
	 * @return [type]             [description]
	 */
	public function delete_data( $object_id = 0, $meta_key = '' ) {

		$this->add_data( $object_id, $meta_key, '', false );

		wp_cache_delete( $this->get_cache_key( $object_id ), 'jet_engine_custom_tables' );

		do_action( 'deleted_' . $this->object_type . '_meta', [], $object_id, $meta_key, false );

		return true;
	}

	/**
	 * Get object of current type by its ID
	 * 
	 * @param  integer $object_id [description]
	 * @return [type]             [description]
	 */
	public function get_object( $object_id = 0 ) {

		$object = false;

		switch ( $this->object_type ) {
			case 'post':
				$object = get_post( $object_id );
				break;

		}

		return $object;

	}

	/**
	 * Returns data from custom table
	 * 
	 * @param  [type] $object_id [description]
	 * @param  [type] $meta_key  [description]
	 * @return [type]            [description]
	 */
	public function get_data( $object_id = 0, $meta_key = '', $single = true ) {

		$cache_key = $this->get_cache_key( $object_id );
		$group     = 'jet_engine_custom_tables';
		$obj_row   = wp_cache_get( $cache_key, $group );

		$object = jet_engine()->listings->data->get_current_object();

		if ( $object && isset( $object->object_ID ) 
			&& absint( $object->object_ID ) === absint( $object_id )
			&& isset( $object->$meta_key )
		) {
			// We always need to return an array here, because result of "get_{$meta_type}_metadata" filter processed as an array in the WP core
			return [ maybe_unserialize( $object->$meta_key ) ];
		}

		if ( false === $obj_row ) {
			$obj_row = $this->db->get_item( $object_id, 'object_ID' );
			wp_cache_set( $cache_key, $obj_row, $group );
		}
		
		if ( ! $obj_row ) {
			return false;
		}

		$result = isset( $obj_row[ $meta_key ] ) ? maybe_unserialize( $obj_row[ $meta_key ] ) : false;

		// We always need to return an array here, because result of "get_{$meta_type}_metadata" filter processed as an array in the WP core
		return [ $result ];

	}

	/**
	 * Retruns cache key
	 * 
	 * @param  [type] $object_id [description]
	 * @return [type]            [description]
	 */
	public function get_cache_key( $object_id = 0 ) {
		return $this->db->table() . '_' . $object_id;
	}

	/**
	 * Maybe process adding metadata
	 * 
	 * @param integer $object_id  [description]
	 * @param string  $meta_key   [description]
	 * @param string  $meta_value [description]
	 */
	public function add_data( $object_id = 0, $meta_key = '', $meta_value = '', $insert = true ) {

		if ( ! $this->db->is_table_exists() ) {
			$this->db->create_table();
		}

		$obj_row_exists = $this->db->get_item( $object_id, 'object_ID' );

		if ( $obj_row_exists ) {
			
			$this->db->update( [ $meta_key => $meta_value ], [ 'object_ID' => $object_id ] );
			wp_cache_delete( $this->get_cache_key( $object_id ), 'jet_engine_custom_tables' );

			// Trigger default WP hooks to ensure 3rd party code compatibility
			do_action( 'updated_' . $this->object_type . '_meta', 0, $object_id, $meta_key, $meta_value );

			if ( 'post' === $this->object_type ) {
				do_action( 'updated_postmeta', 0, $object_id, $meta_key, $meta_value );
			}

		} elseif ( $insert ) {
			
			$this->db->insert( [ $meta_key => $meta_value, 'object_ID' => $object_id ] );

			// Trigger default WP hooks to ensure 3rd party code compatibility
			do_action( 'added_' . $this->object_type . '_meta', 0, $object_id, $meta_key, $meta_value );

		}

		return true;

	}

}
