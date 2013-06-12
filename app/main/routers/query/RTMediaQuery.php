<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaQuery
 *
 * @author saurabh
 */
class RTMediaQuery {


	/**
	 *
	 * @var array The query arguments for the current instance
	 */
	public $query = '';

	/**
	 *
	 * @var object The current action object (edit/delete/custom)
	 */
	public $action_query = false;

	/**
	 *
	 * @var object The currently relevant interaction object
	 */

	private $interaction;

	/**
	 *
	 * @var array The actions recognised for the object
	 */

	private $actions = array(
		'edit',
		'delete'
	);



	public $media = '';

	public $media_count = 0;
	public $current_media = -1;
	public $in_the_media_loop = false;

	public $format = false;

	/**
	 * Initialise the query
	 *
	 * @global object $rt_media_interaction The global interaction object
	 * @param array $args The query arguments
	 */

	function __construct( $args = false ) {

		// set up the interaction object relevant to just the query
		// we only need information related to the media route
		global $rt_media_interaction;

		//print_r($rt_media_interaction);
		$this->interaction = $rt_media_interaction->routes->media;

		// set up the action query from the URL
		$this->set_action_query();

		// if no args were supplied, initialise the $args
		if ( empty( $args ) ) {

			$this->init();

		// otherwise just populate the query
		} else {

			$this->query($args);

		}

	}

	/**
	 * Initialise the default args for the query
	 */

	function init(){



	}

	function set_action_query() {

		$raw_query = $this->interaction->query_vars;

		if ( is_array( $raw_query ) && count( $raw_query ) && !empty($raw_query[0]) ) {

			$bulk = false;
			$action = false;
			$attribute = false;
			$modifier_type = false;
			$modifier_value = false;
			$format = '';

			/**
			 * json request
			 */
			if(in_array('json',$raw_query)){
				$this->format = 'json';
			}

			/**
			 * accessed the url directly by media id
			 */
			if ( is_numeric( $raw_query[ 0 ] ) ) {
				$modifier_type = 'id';
			} else {
			/**
			 * accessed the url directly by media type
			 */
				$modifier_type = 'media_type';
			}

			$modifier_value = $raw_query[ 0 ];

			
			if ( isset( $raw_query[ 1 ] ) ) {

				/**
				 * action manipulator hook
				 */
				$this->set_actions();
				
				if ( in_array( $raw_query[ 1 ], $this->actions ) ) {

					$action = $raw_query[ 1 ];

					if ( $modifier_type === 'media_type' ) {
						$bulk = true;
					}
				}
			}

			/**
			 * additional attributes
			 */
			if ( isset( $raw_query[ 2 ] ) ) {
				$attribute= $raw_query[2];
			}

			/**
			 * set action query object
			 */
			$this->action_query = (object)array(
				$modifier_type	=> $modifier_value,
				'action'		=> $action,
				'attribute'		=> $attribute,
				'bulk'			=> true
			);

		} else if( isset($raw_query) )
			/*
			 * invalid slug
			 */
			include get_404_template();

		/**
		 * setting parameters in action query object for pagination
		 */
		global $rt_media;
		$options = $rt_media->get_option();
		$this->action_query = (object) array(
			'offset' => ( isset($_GET['offset']) && !empty($_GET['offset']) ) ? intval($_GET['offset']) : 0,
			'paged' => ( isset($_GET['rt_media_paged']) && !empty($_GET['rt_media_paged']) ) ? intval($_GET['rt_media_paged']) : 1,
			'per_page_media' => $options['per_page_media']
		);

	}

	/**
	 * additional actions to be added via action hook
	 */
	function set_actions() {
		$this->actions = apply_filters( 'rt_media_query_actions', $this->actions );
	}

	/**
	 * get media query for the request
	 * @param type $query
	 * @return type
	 */
	function &query( $query ) {
		$this->query = $this->query_vars = wp_parse_args( $query );
		return $this->get_media();
	}


	/**
	 * populate the media object for the page/album
	 * 
	 * @return boolean
	 */
	function populate_media() {
		$this->model = new RTMediaModel();

		unset( $this->query->meta_query );
		unset( $this->query->tax_query );
		
		/**
		 * Handle order of the result set
		 */
		$order_by = 'media_id';
		$order = 'desc';
		if( isset($this->query['order']) ) {
			$order = $this->query['order'];
			unset($this->query['order']);
		}

		if( isset($this->query['order_by']) ) {
			$order_by = $this->query['order_by'];
			unset($this->query['order_by']);
			if( $order_by == 'ratings' )
				$order_by = 'ratings_average ' . $order .', ratings_count';
		}
		$order_by .= ' ' . $order;

		/**
		 * fetch media entries from rtMedia context
		 */
		$pre_media = $this->model->get_media( $this->query, $this->action_query->offset, $this->action_query->per_page_media, $order_by );

		/**
		 * count total medias in alnum rrespective of pagination
		 */
		$media_for_total_count = $this->model->get_media( $this->query, false, false );
		$this->media_count = count($media_for_total_count);
		
		if ( ! $pre_media )
			return false;

/*		removed because of indexing ---- 0,1,2 was required rather than post_ids
		foreach ( $pre_media as $pre_medium ) {
			$this->media[ $pre_medium->media_id ] = $pre_medium;
		}*/

		$this->media = $pre_media;

		/**
		 * multiside manipulation
		 */
		if ( is_multisite() ) {
			foreach ( $this->media as $media ) {
				$blogs[ $media->blog_id ][] = $media;
			}


			foreach ( $blogs as $blog_id => &$media ) {
				switch_to_blog( $blog_id );
				$this->populate_post_data( $media );
				wp_reset_query();
			}
			restore_current_blog();
		} else {
			$this->populate_post_data( $this->media );
		}
	}

	/**
	 * helper method to fetch media id of each media from the map
	 * @param type $media
	 * @return type
	 */
	function get_media_id($media) {
		return $media->media_id;
	}

	/**
	 * helper method to find the array entry for the given media id
	 * @param type $id
	 * @return null
	 */
	function get_media_by_media_id($id) {

		foreach ($this->media as $key=>$media) {
			if($media->media_id == $id)
				return $key;
		}
		return null;
	}

	/**
	 * populate the post data for the fetched media from rtMedia context
	 * @param type $media
	 */
	function populate_post_data( $media ) {
		if ( ! empty( $media ) && is_array( $media ) ) {
			
			/**
			 * setting up query vars for WP_Query
			 */
			$media_post_query_args = array(
				'orderby' => 'ID',
				'order' => 'DESC',
				'post_type' => 'any',
				'post_status' => 'any',
				'post__in' => array_map(array($this, 'get_media_id'), $media ),
				'ignore_sticky_posts' => 1
			);

			/**
			 * setting up meta query vars
			 */
			if ( isset( $this->query_vars->meta_query ) ) {
				$media_post_query_args[ 'meta_query' ] = $this->query_vars->meta_query;
			}
			/**
			 * setting up taxonomy query vars
			 */
			if ( isset( $this->query_vars->tax_query ) ) {
				$media_post_query_args[ 'tax_query' ] = $this->query_vars->tax_query;
			}
			
			/**
			 * fetch relative data from WP_POST
			 */
			$media_post_query = new WP_Query( $media_post_query_args );

			/**
			 * Merge the data with media object of the album
			 */
			$media_post_data = $media_post_query->posts;
			foreach ( $media_post_data as $post ) {

				$key = $this->get_media_by_media_id($post->ID);
				$this->media[ $key ] = (object) (array_merge( (array) $this->media[ $key ], (array) $post ));
				
				$this->media[ $key ]->id = intval( $this->media[ $key ]->id );
				
				unset( $this->media[ $key ]->ID );
			}
		}
	}

	/**
	 * Checks at any point of time any media is left to be processed in the db pool
	 * @return boolean
	 */
	function have_media() {
		
		$total = $this->media_count;
		$curr = $this->current_media;
		$per_page = $this->action_query->per_page_media;
		$offset = $this->action_query->offset;
		
		if ( $curr + 1 < $per_page && $total > $offset + $curr + 1 ) {
			return true;
		} elseif ( $curr + 1 == $per_page && $per_page > 0 ) {
			do_action_ref_array( 'rt_media_loop_end', array( &$this ) );
			// Do some cleaning up after the loop
			$this->rewind_media();
		}

		$this->in_the_media_loop = false;
		return false;
	}

	/**
	 * moves ahead in the loop of media within the album
	 * @global type $rt_media_media
	 */
	function rt_album() {
		global $rt_media_media;
		$this->in_the_media_loop = true;

		if ( $this->current_media == -1 ) // loop has just started
			do_action_ref_array( 'rt_media_loop_start', array( &$this ) );

		$rt_media_media = $this->next_media();
	}
	
	/**
	 * returns the current media object in the album
	 * @global type $rt_media_media
	 * @return \type
	 */
	function rt_media() {
		global $rt_media_media;
		
		return $rt_media_media;
	}

	/**
	 * helper method for rt_album to move ahead in the db pool
	 * @return type
	 */
	function next_media() {
		$this->current_media ++;

		$this->rt_media = $this->media[ $this->current_media ];
		return $this->rt_media;
	}

	/**
	 * Rewinds the db pool of media album and resets it to begining
	 */
	function rewind_media() {
		$this->current_media = -1;
		if ( $this->action_query->per_page_media > 0 ) {
			$this->media = $this->media[ 0 ];
		}
	}

	/**
	 * 
	 * @return type
	 */
	function &get_media() {

		$this->populate_media();

		return $this->media;
	}

}

?>
