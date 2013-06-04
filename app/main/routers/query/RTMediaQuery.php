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
	private $action_query = false;

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
		//print_r($raw_query);

		if ( is_array( $raw_query ) && count( $raw_query ) ) {

			$bulk = false;
			$action = false;
			$attribute = false;
			$modifier_type = false;
			$modifier_value = false;
			$format = '';

			if(in_array('json',$raw_query)){
				$this->format = 'json';
			}

			if ( is_numeric( $raw_query[ 0 ] ) ) {
				$modifier_type = 'id';
			} else {
				$modifier_type = 'media_type';
			}

			$modifier_value = $raw_query[ 0 ];

			if ( isset( $raw_query[ 1 ] ) ) {

				$this->set_actions();

				if ( in_array( $raw_query[ 1 ], $this->actions ) ) {

					$action = $raw_query[ 1 ];

					if ( $modifier_type === 'media_type' ) {
						$bulk = true;
					}
				}
			}

			if ( isset( $raw_query[ 2 ] ) ) {
				$attribute= $raw_query[2];
			}

			$this->action_query = (object)array(
				$modifier_type	=> $modifier_value,
				'action'		=> $action,
				'attribute'		=> $attribute,
				'bulk'			=> true
			);

		}
	}

	function set_actions() {
		$this->actions = apply_filters( 'rt_media_query_actions', $this->actions );
	}

	function &query( $query ) {
		$this->query = $this->query_vars = wp_parse_args( $query );
		return $this->get_media();
	}


	function populate_media() {
		$this->model = new RTMediaModel();

		unset( $this->query->meta_query );
		unset( $this->query->tax_query );

		$pre_media = $this->model->get_media( $this->query );



		if ( ! $pre_media )
			return false;

		foreach ( $pre_media as $pre_medium ) {
			$this->media[ $pre_medium->media_id ] = $pre_medium;
		}

		if ( is_multisite() ) {
			foreach ( $this->media as $mk => $mv ) {
				$blogs[ $mv->blog_id ][ $mk ] = $mv;
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

	function populate_post_data( $media ) {
		if ( ! empty( $media ) && is_array( $media ) ) {
			$media_post_query_args = array(
				'post_type' => 'any',
				'post_status' => 'any',
				'post__in' => array_keys( $media ),
				'ignore_sticky_posts' => 1
			);


			if ( isset( $this->query_vars->meta_query ) ) {
				$media_post_query_args[ 'meta_query' ] = $this->query_vars->meta_query;
			}
			if ( isset( $this->query_vars->tax_query ) ) {
				$media_post_query_args[ 'tax_query' ] = $this->query_vars->tax_query;
			}

			$media_post_query = new WP_Query( $media_post_query_args );

			$media_post_data = $media_post_query->posts;
			foreach ( $media_post_data as $post ) {

				$this->media[ $post->ID ] = (object) (array_merge( (array) $this->media[ $post->ID ], (array) $post ));

				$this->media[ $post->ID ]->id = intval( $this->media[ $post->ID ]->id );

				unset( $this->media[ $post->ID ]->ID );
			}

			$this->media_count = count( $this->media );
		}
	}

	function have_media() {
		if ( $this->current_media + 1 < $this->media_count ) {
			return true;
		} elseif ( $this->current_media + 1 == $this->media_count && $this->media_count > 0 ) {
			do_action_ref_array( 'rt_media_loop_end', array( &$this ) );
			// Do some cleaning up after the loop
			$this->rewind_media();
		}

		$this->in_the_media_loop = false;
		return false;
	}

	function rt_media() {
		global $rt_media;
		$this->in_the_media_loop = true;

		if ( $this->current_media == -1 ) // loop has just started
			do_action_ref_array( 'rt_media_loop_start', array( &$this ) );

		$rt_media = $this->next_media();
	}

	function next_media() {
		$this->current_media ++;
		//print_r($this->media);

		$this->rt_media = $this->media[ $this->current_media ];
		return $this->rt_media;
	}

	function rewind_media() {
		$this->current_media = -1;
		if ( $this->media_count > 0 ) {
			$this->media = $this->media[ 0 ];
		}
	}

	function &get_media() {

		$this->populate_media();

		return $this->media;
	}

}

?>
