<?php
/**
 * Post type archive functionality.
 *
 * Allows for registering an "archive" CPT to modify titles, descriptions and
 * post type rewrite slugs on a traditional edit post screen.
 *
 * @package AudioTheme\Archives
 * @since 2.0.0
 */

/**
 * Archive custom post type class.
 *
 * @package AudioTheme\Archives
 * @since 2.0.0
 */
class Audiotheme_Module_Archives extends Audiotheme_Module {
	/**
	 * Archive post type name.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	const POST_TYPE = 'audiotheme_archive';

	/**
	 * Cached archive settings.
	 *
	 * @since 2.0.0
	 * @type array Post type name is the key and the value is an array of archive settings.
	 */
	protected $archives = array();

	/**
	 * Post type for the current request.
	 *
	 * @since 2.0.0
	 * @type string
	 */
	protected $current_archive_post_type = '';

	/**
	 *
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( array(
			'module_id'          => 'archives',
			'module_name'        => __( 'Archives', 'audiotheme' ),
			'module_description' => __( '', 'audiotheme' ),
			'is_core_module'     => true,
		), $args );

		parent::__construct( $args );
	}

	/**
	 * Load archive functionality.
	 *
	 * @since 2.0.0
	 */
	public function load() {
		$this->register_hooks();
	}

	/**
	 *
	 */
	public function register_hooks() {
		// Register early so archives can be registered when other post types are registered.
		add_action( 'init',                        array( $this, 'register_post_type' ), 5 );
		add_filter( 'post_updated_messages',       array( $this, 'updated_messages' ) );
		add_action( 'pre_get_posts',               array( $this, 'pre_get_posts' ) );
		add_filter( 'post_type_link',              array( $this, 'post_type_link' ), 10, 3 );
		add_filter( 'post_type_archive_link',      array( $this, 'post_type_archive_link' ), 10, 2 );
		add_filter( 'post_type_archive_title',     array( $this, 'post_type_archive_title' ) );
		add_action( 'admin_bar_menu',              array( $this, 'admin_bar_edit_menu' ), 80 );
		add_action( 'post_updated',                array( $this, 'on_archive_update' ), 10, 3 );
		add_action( 'delete_post',                 array( $this, 'delete_post_type_archive' ) );
		add_filter( 'get_audiotheme_archive_meta', array( $this, 'sanitize_columns_setting' ), 10, 5 );

		if ( is_admin() ) {
			// High priority makes archive links appear last in submenus.
			add_action( 'admin_menu',                           array( $this, 'admin_menu' ), 100 );
			add_action( 'parent_file',                          array( $this, 'parent_file' ) );
			add_action( 'add_meta_boxes_' . self::POST_TYPE,    array( $this, 'add_meta_boxes' ) );
			add_action( 'audiotheme_archive_settings_meta_box', array( $this, 'settings_meta_box_fields' ), 15, 3 );
			add_action( 'save_post',                            array( $this, 'save_archive_settings' ), 10, 2 );
		}
	}

	/**
	 * Register the archive custom post type.
	 *
	 * @since 2.0.0
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Archives', 'post format general name', 'audiotheme' ),
			'singular_name'      => _x( 'Archive', 'post format singular name', 'audiotheme' ),
			'add_new'            => _x( 'Add New', 'audiotheme_archive', 'audiotheme' ),
			'add_new_item'       => __( 'Add New Archive', 'audiotheme' ),
			'edit_item'          => __( 'Edit Archive', 'audiotheme' ),
			'new_item'           => __( 'New Archive', 'audiotheme' ),
			'view_item'          => __( 'View Archive', 'audiotheme' ),
			'search_items'       => __( 'Search Archives', 'audiotheme' ),
			'not_found'          => __( 'No archives found.', 'audiotheme' ),
			'not_found_in_trash' => __( 'No archives found in Trash.', 'audiotheme' ),
			'all_items'          => __( 'All Archives', 'audiotheme' ),
			'menu_name'          => __( 'Archives', 'audiotheme' ),
			'name_admin_bar'     => _x( 'Archive', 'add new on admin bar', 'audiotheme' ),
		);

		$args = array(
			'can_export'                 => false,
			'capability_type'            => array( 'post', 'posts' ),
			'capabilities'               => array(
				'delete_post'            => 'delete_audiotheme_archive',
				// Custom capabilities prevent unnecessary fields from showing in post_submit_meta_box().
				'create_posts'           => 'create_audiotheme_archives',
				'delete_posts'           => 'delete_audiotheme_archives',
				'delete_private_posts'   => 'delete_audiotheme_archives',
				'delete_published_posts' => 'delete_audiotheme_archives',
				'delete_others_posts'    => 'delete_audiotheme_archives',
				'publish_posts'          => 'publish_audiotheme_archives',
			),
			'exclude_from_search'        => true,
			'has_archive'                => false,
			'hierarchical'               => false,
			'labels'                     => $labels,
			'map_meta_cap'               => true,
			'public'                     => true,
			'publicly_queryable'         => false,
			// Allows the CPT slug to be edited. Extra rewrite rules wont' be generated.
			'rewrite'                    => 'audiotheme_archive',
			'query_var'                  => false,
			'show_ui'                    => false,
			'show_in_admin_bar'          => true,
			'show_in_menu'               => false,
			'show_in_nav_menus'          => true,
			'supports'                   => array( 'title', 'editor' ),
		);

		register_post_type( self::POST_TYPE, apply_filters( 'audiotheme_archive_register_args', $args ) );
	}

	/**
	 * Create an archive post for a post type.
	 *
	 * This should be called after the post type has been registered.
	 *
	 * @since 2.0.0
	 *
	 * @todo Do we need to make sure this doesn't run in the network admin?
	 *
	 * @param string $post_type Post type name.
	 * @param array $args {
	 *     An array of arguments. Optional.
	 *
	 *     @type string $admin_menu_parent Admin menu parent slug.
	 * }
	 * @return int Archive post ID.
	 */
	public function add_post_type_archive( $post_type, $args = array() ) {
		$archives = get_audiotheme_archive_ids();
		$post_id  = isset( $archives[ $post_type ] ) ? $archives[ $post_type ] : '';

		if ( empty( $post_id ) ) {
			$post_id = $this->maybe_insert_archive_post( $post_type );
			$archives[ $post_type ] = $post_id;
			update_option( 'audiotheme_archives', $archives );

			// Update the post type rewrite base.
			$this->update_post_type_rewrite_base( $post_type, $post_id );
			flush_rewrite_rules();
		}

		// Cache the archive args.
		$this->archives[ $post_type ] = array_merge( array( 'post_id' => $post_id ), $args );

		return $post_id;
	}

	/**
	 * Delete the cached reference to an archive post.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type Post type name.
	 */
	public function delete_post_type_archive( $post_type ) {
		$archives = $this->get_archive_ids();

		// Look up the post type by post ID.
		if ( is_int( $post_type ) ) {
			$post_type = array_search( $post_type, $archives );
		}

		if ( ! empty( $archives[ $post_type ] ) ) {
			unset( $archives[ $post_type ] );
			update_option( 'audiotheme_archives', $archives );
		}
	}

	/**
	 * Retrieve the archive post ID for a post type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type_name Optional. Post type name. Defaults to the current post type.
	 * @return int
	 */
	public function get_archive_id( $post_type = null ) {
		$post_type = $post_type ? $post_type : get_post_type();
		$archives  = $this->get_archive_ids();

		if ( empty( $post_type ) ) {
			$post_type = get_query_var( 'post_type' );
		}

		return empty( $archives[ $post_type ] ) ? null : $archives[ $post_type ];
	}

	/**
	 * Retrieve archive post IDs.
	 *
	 * @since 2.0.0
	 *
	 * @return array Associative array with post types as keys and post IDs as the values.
	 */
	public function get_archive_ids() {
		return get_option( 'audiotheme_archives', array() );
	}

	/**
	 * Retrieve archive meta.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys.
	 * @param bool $single Optional. Whether to return a single value.
	 * @param mixed $default Optional. A default value to return if the requested meta doesn't exist.
	 * @param string $post_type Optional. The post type archive to retrieve meta data for. Defaults to the current post type.
	 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
	 */
	public function get_archive_meta( $key = '', $single = false, $default = null, $post_type = null ) {
		$post_type = empty( $post_type ) ? get_post_type() : $post_type;
		if ( ! $post_type && ! $this->is_post_type_archive() ) {
			return null;
		}

		$archive_id = $this->get_archive_id( $post_type );
		if ( ! $archive_id ) {
			return null;
		}

		$value = get_post_meta( $archive_id, $key, $single );
		if ( empty( $value ) && ! empty( $default ) ) {
			$value = $default;
		}

		return apply_filters( 'get_audiotheme_archive_meta', $value, $key, $single, $default, $post_type );
	}

	/**
	 * Retrieve the title for a post type archive.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type Optional. Post type name. Defaults to the current post type.
	 * @param string $title Optional. Fallback title.
	 * @return string
	 */
	public function get_archive_title( $post_type = '', $title = '' ) {
		if ( empty( $post_type ) ) {
			$post_type = get_query_var( 'post_type' );
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}
		}

		if ( $post_id = $this->get_archive_id( $post_type) ) {
			$title = get_post( $post_id )->post_title;
		}

		return $title;
	}

	/**
	 * Retrieve archive settings fields and data.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type Post type name.
	 * @return array
	 */
	public function get_settings_fields( $post_type ) {
		/**
		 * Enable and filter post type archive settings.
		 *
		 * @since 2.0.0
		 *
		 * @param array $settings {
		 *     Settings to enable for the archive.
		 *
		 *     @type array $columns {
		 *         Archive column settings.
		 *
		 *         @type int $default The default number of columns to show. Defaults to 4 if enabled.
		 *         @type array $choices An array of possible values.
		 *     }
		 *     @type bool $posts_per_archive_page Whether to enable the setting
		 *                                        for modifying the number of
		 *                                        posts to show on the post
		 *                                        type's archive.
		 * }
		 */
		return apply_filters( 'audiotheme_archive_settings_fields', array(), $post_type );
	}

	/**
	 * Set the post type for the current archive request.
	 *
	 * This can be used to set the post type for archive requests that aren't
	 * post type archives. For example, to have a term archive use the same
	 * settings as a post type archive, set the post type with this method in
	 * 'pre_get_posts'.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type Post type name.
	 */
	public function set_current_archive_post_type( $post_type ) {
		$this->current_archive_post_type = $post_type;
	}

	/**
	 * Determine if a post ID is for an archive post.
	 *
	 * @since 2.0.0
	 *
	 * @param int $archive_id Post ID.
	 * @return string|bool Post type name if true, otherwise false.
	 */
	public function is_archive_id( $archive_id ) {
		$archives = $this->get_archive_ids();
		return array_search( $archive_id, $archives );
	}

	/**
	 * Whether the current query has a corresponding archive post.
	 *
	 * @since 2.0.0
	 *
	 * @param array|string $post_types Optional. A post type name or array of
	 *                                 post type names. Defaults to all archives
	 *                                 registered via Audiotheme_Archives::add_post_type_archive().
	 * @return bool
	 */
	public function is_post_type_archive( $post_types = array() ) {
		if ( empty( $post_types ) ) {
			$post_types = array_keys( $this->get_archive_ids() );
		}

		return is_post_type_archive( $post_types );
	}

	/*
	 * Hook callbacks.
	 */

	/**
	 * Filter archive queries.
	 *
	 * Sets the number of posts per archive page based on saved archive meta.
	 *
	 * @since 2.0.0
	 * @todo Refactor to make it easier to retrieve settings and to define defaults in a single location.
	 * @todo Implement a "rows" setting for calculating "posts_per_archive_page".
	 *
	 * @param object $query The main WP_Query object. Passed by reference.
	 */
	public function pre_get_posts( $wp_query ) {
		$post_type = apply_filters( 'audiotheme_archive_query_post_type', $this->current_archive_post_type );

		if ( empty( $post_type ) && $this->is_post_type_archive() ) {
			$post_type = $this->get_post_type();
		}

		if ( is_admin() || ! $wp_query->is_main_query() || empty( $post_type ) ) {
			return;
		}

		// Determine if the 'posts_per_archive_page' setting is active for the current post type.
		$fields = $this->get_settings_fields( $post_type );

		$columns = 1;
		if ( ! empty( $fields['columns'] ) && $fields['columns'] ) {
			$default = empty( $fields['columns']['default'] ) ? 4 : absint( $fields['columns']['default'] );
			$columns = $this->get_archive_meta( 'columns', true, $default, $post_type );
		}

		if ( ! empty( $fields['posts_per_archive_page'] ) && $fields['posts_per_archive_page'] ) {
			// Get the number of posts to display for this post type.
			$posts_per_archive_page = $this->get_archive_meta( 'posts_per_archive_page', true, get_option( 'posts_per_page' ), $post_type );

			if ( ! empty( $posts_per_archive_page ) ) {
				$wp_query->set( 'posts_per_archive_page', intval( $posts_per_archive_page ) );
			}
		}

		if ( empty( $posts_per_archive_page ) && $columns > 1 ) {
			// Default to three even rows.
			$wp_query->set( 'posts_per_archive_page', intval( $columns * 3 ) );
		}

		do_action_ref_array( 'audiotheme_archive_query', array( $wp_query, $post_type ) );
	}

	/**
	 * Filter archive CPT permalinks to match the corresponding post type's
	 * archive link.
	 *
	 * @since 2.0.0
	 *
	 * @param string $permalink Default permalink.
	 * @param WP_Post $post Post object.
	 * @param bool $leavename Optional, defaults to false. Whether to keep post name.
	 * @return string Permalink.
	 */
	public function post_type_link( $permalink, $post, $leavename ) {
		global $wp_rewrite;

		if ( self::POST_TYPE == $post->post_type ) {
			$post_type        = $this->is_archive_id( $post->ID );
			$post_type_object = get_post_type_object( $post_type );

			if ( get_option( 'permalink_structure' ) ) {
				$front = '/';
				if ( $wp_rewrite->using_index_permalinks() ) {
					$front .= $wp_rewrite->index . '/';
				}

				if ( isset( $post_type_object->rewrite ) && $post_type_object->rewrite['with_front'] ) {
					$front = $wp_rewrite->front;
				}

				if ( $leavename ) {
					$permalink = home_url( $front . '%postname%/' );
				} else {
					$permalink = home_url( $front . $post->post_name . '/' );
				}
			} else {
				$permalink = add_query_arg( 'post_type', $post_type, home_url( '/' ) );
			}
		}

		return $permalink;
	}

	/**
	 * Filter post type archive permalinks.
	 *
	 * @since 2.0.0
	 *
	 * @param string $link Post type archive link.
	 * @param string $post_type Post type name.
	 * @return string
	 */
	public function post_type_archive_link( $link, $post_type ) {
		$archive_post_id = $this->get_archive_id( $post_type );

		if ( ! empty( $archive_post_id ) ) {
			$link = get_permalink( $archive_post_id );
		}

		return $link;
	}

	/**
	 * Filter the default post_type_archive_title() template tag and replace
	 * with custom archive title.
	 *
	 * @since 2.0.0
	 *
	 * @param string $label Post type archive title.
	 * @return string
	 */
	public function post_type_archive_title( $title ) {
		$post_type_object = get_queried_object();
		return $this->get_archive_title( $post_type_object->name, $title );
	}

	/**
	 * Provide an edit link for archives in the admin bar.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar object instance.
	 */
	public function admin_bar_edit_menu( $wp_admin_bar ) {
		if ( ! is_admin() && $this->is_post_type_archive() ) {
			$archive_post_id  = $this->get_archive_id();
			$post_type_object = get_post_type_object( get_post_type( $archive_post_id ) );

			if ( empty( $post_type_object ) ) {
				return;
			}

			$wp_admin_bar->add_menu( array(
				'id'    => 'edit',
				'title' => $post_type_object->labels->edit_item,
				'href'  => get_edit_post_link( $archive_post_id ),
			) );
		}
	}

	/**
	 * Flush the rewrite rules when an archive post slug is changed.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id Post ID
	 * @param WP_Post $post_after Updated post object.
	 * @param WP_Post $post_before Post object before udpate.
	 */
	public function on_archive_update( $post_id, $post_after, $post_before ) {
		$post_type = $this->is_archive_id( $post_id );

		if ( $post_type && $post_after->post_name != $post_before->post_name ) {
			$this->update_post_type_rewrite_base( $post_type, $post_id );
			flush_rewrite_rules();
		}
	}

	/**
	 * Sanitize archive columns setting.
	 *
	 * The allowed columns value may be different between themes, so make sure
	 * it exists in the settings defined by the theme, otherwise, return the
	 * theme default.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value Existing meta value.
	 * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys.
	 * @param bool $single Optional. Whether to return a single value.
	 * @param mixed $default Optional. A default value to return if the requested meta doesn't exist.
	 * @param string $post_type Optional. The post type archive to retrieve meta data for. Defaults to the current post type.
	 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
	 */
	public function sanitize_columns_setting( $value, $key, $single, $default, $post_type ) {
		if ( 'columns' !== $key || $value === $default ) {
			return $value;
		}

		$fields = $this->get_settings_fields( $post_type );
		if ( ! empty( $fields['columns']['choices'] ) && ! in_array( $value, $fields['columns']['choices'] ) ) {
			$value = $default;
		}

		return $value;
	}

	/**
	 * Add submenu items for archives under the post type menu item.
	 *
	 * Ensures the user has the capability to edit pages in general as well as
	 * the individual page before displaying the submenu item.
	 *
	 * @since 2.0.0
	 */
	public function admin_menu() {
		$archives = $this->get_archive_ids();

		if ( empty( $archives ) ) {
			return;
		}

		// Verify the user can edit audiotheme_archive posts.
		$archive_type_object = get_post_type_object( self::POST_TYPE );
		if ( ! current_user_can( $archive_type_object->cap->edit_posts ) ) {
			return;
		}

		foreach ( $archives as $post_type => $archive_id ) {
			// Verify the user can edit the particular audiotheme_archive post in question.
			if ( ! current_user_can( $archive_type_object->cap->edit_post, $archive_id ) ) {
				continue;
			}

			$parent_slug = 'edit.php?post_type=' . $post_type;
			if ( isset( $this->archives[ $post_type ]['admin_menu_parent'] ) ) {
				$parent_slug = $this->archives[ $post_type ]['admin_menu_parent'];
			}

			// Add the submenu item.
			add_submenu_page(
				$parent_slug,
				$archive_type_object->labels->singular_name,
				$archive_type_object->labels->singular_name,
				$archive_type_object->cap->edit_posts,
				add_query_arg( array( 'post' => $archive_id, 'action' => 'edit' ), 'post.php' ),
				null
			);
		}
	}

	/**
	 * Replace the submit meta box to remove unnecessary fields.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function add_meta_boxes( $post ) {
		$post_type = $this->is_archive_id( $post->ID );

		remove_meta_box( 'submitdiv', self::POST_TYPE, 'side' );

		add_meta_box(
			'submitdiv',
			__( 'Update', 'audiotheme' ),
			'audiotheme_post_submit_meta_box',
			self::POST_TYPE,
			'side',
			'high',
			array(
				'force_delete'      => false,
				'show_publish_date' => false,
				'show_statuses'     => false,
				'show_visibility'   => false,
			)
		);

		// Activate the default archive settings meta box.
		$show = apply_filters( 'add_audiotheme_archive_settings_meta_box', false, $post_type );
		$show_for_post_type = apply_filters( 'add_audiotheme_archive_settings_meta_box_' . $post_type, false );

		// Show if any settings fields have been registered for the post type.
		$fields = $this->get_settings_fields( $post_type );

		if ( $show || $show_for_post_type || ! empty( $fields ) ) {
			add_meta_box(
				'audiothemesettingsdiv',
				__( 'Archive Settings', 'audiotheme' ),
				array( $this, 'display_settings_meta_box' ),
				self::POST_TYPE,
				'side',
				'default',
				array(
					'fields' => $fields,
				)
			);
		}
	}

	/**
	 * Highlight the corresponding top level and submenu items when editing an
	 * archive post.
	 *
	 * @since 2.0.0
	 *
	 * @param string $parent_file A parent file identifier.
	 * @return string
	 */
	public function parent_file( $parent_file ) {
		global $post, $submenu_file;

		if ( $post && self::POST_TYPE == get_current_screen()->id && ( $post_type = $this->is_archive_id( $post->ID ) ) ) {
			$parent_file  = 'edit.php?post_type=' . $post_type;
			$submenu_file = add_query_arg( array( 'post' => $post->ID, 'action' => 'edit' ), 'post.php' );

			if ( isset( $this->archives[ $post_type ]['admin_menu_parent'] ) ) {
				$parent_file = $this->archives[ $post_type ]['admin_menu_parent'];
			}
		}

		return $parent_file;
	}

	/**
	 * Display archive settings meta box.
	 *
	 * The meta box needs to be activated first, then fields can be displayed
	 * using one of the actions.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Archive post.
	 */
	public function display_settings_meta_box( $post, $args = array() ) {
		$post_type = $this->is_archive_id( $post->ID );
		wp_nonce_field( 'save-archive-meta_' . $post->ID, 'audiotheme_archive_nonce' );
		do_action( 'audiotheme_archive_settings_meta_box', $post, $post_type, $args['args']['fields'] );
		do_action( 'audiotheme_archive_settings_meta_box_' . $post_type, $post, $args['args']['fields'] );
	}

	/**
	 * Add fields to the archive settings meta box.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Archive post.
	 */
	public function settings_meta_box_fields( $post, $post_type, $fields = array() ) {
		if ( empty( $fields ) ) {
			return;
		}

		if ( ! empty( $fields['posts_per_archive_page'] ) && $fields['posts_per_archive_page'] ) {
			$value = $this->get_archive_meta( 'posts_per_archive_page', true, '', $post_type );
			?>
			<p>
				<label for="audiotheme-posts-per-archive-page"><?php _e( 'Posts per page:', 'audiotheme' ); ?></label>
				<input type="text" name="posts_per_archive_page" id="audiotheme-posts-per-archive-page" value="<?php echo esc_attr( $value ); ?>" class="small-text">
			</p>
			<?php
		}

		if ( ! empty( $fields['columns'] ) && $fields['columns'] ) {
			$default = empty( $fields['columns']['default'] ) ? 4 : absint( $fields['columns']['default'] );
			$value   = $this->get_archive_meta( 'columns', true, $default, $post_type );
			$choices = range( 3, 5 );

			if ( ! empty( $fields['columns']['choices'] ) && is_array( $fields['columns']['choices'] ) ) {
				$choices = $fields['columns']['choices'];
			}
			?>
			<p>
				<label for="audiotheme-columns"><?php _e( 'Columns:', 'audiotheme' ); ?></label>
				<select name="columns" id="audiotheme-columns">
					<?php
					foreach ( $choices as $number ) {
						$number = absint( $number );

						printf( '<option value="%1$d"%2$s>%1$d</option>',
							$number,
							selected( $number, $value, false )
						);
					}
					?>
				</select>
			</p>
			<?php
		}
	}

	/**
	 * Save archive meta data.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_archive_settings( $post_id, $post ) {
		$is_autosave    = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
		$is_revision    = wp_is_post_revision( $post_id );
		$is_valid_nonce = isset( $_POST['audiotheme_archive_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_archive_nonce'], 'save-archive-meta_' . $post_id );

		// Bail if the data shouldn't be saved or intention can't be verified.
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		$post_type = $this->is_archive_id( $post->ID );
		do_action( 'save_audiotheme_archive_settings', $post_id, $post, $post_type );

		$fields = $this->get_settings_fields( $post_type );

		// Sanitize and save the posts per archive page setting.
		if ( ! empty( $fields['posts_per_archive_page'] ) && $fields['posts_per_archive_page'] ) {
			$posts_per_archive_page = is_numeric( $_POST['posts_per_archive_page'] ) ? intval( $_POST['posts_per_archive_page'] ) : '';
			update_post_meta( $post_id, 'posts_per_archive_page', $posts_per_archive_page );
		}

		// Sanitize and save the columns setting.
		if ( ! empty( $fields['columns'] ) && $fields['columns'] ) {
			$choices = range( 3, 5 );
			if ( ! empty( $fields['columns']['choices'] ) && is_array( $fields['columns']['choices'] ) ) {
				$choices = array_map( 'absint', $fields['columns']['choices'] );
			}

			$value = absint( $_POST['columns'] );
			if ( ! in_array( $value, $choices ) ) {
				$choices_min = min( $choices );
				$choices_max = max( $choices );
				$value       = min( max( $value, $choices_min ), $choices_max );
			}

			update_post_meta( $post_id, 'columns', $value );
		}
	}

	/**
	 * Archive update messages.
	 *
	 * @since 2.0.0
	 *
	 * @see /wp-admin/edit-form-advanced.php
	 *
	 * @param array $messages The array of post update messages.
	 * @return array An array with new CPT update messages.
	 */
	public function updated_messages( $messages ) {
		$post             = get_post();
		$post_type        = self::POST_TYPE;
		$post_type_object = get_post_type_object( $post_type );

		if ( self::POST_TYPE != $post_type ) {
			return;
		}

		$messages[ self::POST_TYPE ] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Archive updated.', 'audiotheme' ),
			2  => __( 'Custom field updated.', 'audiotheme' ),
			3  => __( 'Custom field deleted.', 'audiotheme' ),
			4  => __( 'Archive updated.', 'audiotheme' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Archive restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Archive published.', 'audiotheme' ),
			7  => __( 'Archive saved.', 'audiotheme' ),
			8  => __( 'Archive submitted.', 'audiotheme' ),
			9  => sprintf( __( 'Archive scheduled for: <strong>%1$s</strong>.', 'audiotheme' ),
				  /* translators: Publish box date format, see http://php.net/date */
				  date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ) ),
			10 => __( 'Archive draft updated.', 'audiotheme' ),
		);

		if ( $post_type_object->publicly_queryable ) {
			$permalink         = get_permalink( $post->ID );
			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );

			$view_link    = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View archive', 'audiotheme' ) );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview archive', 'audiotheme' ) );

			$messages[ $post_type ][1]  .= $view_link;
			$messages[ $post_type ][6]  .= $view_link;
			$messages[ $post_type ][9]  .= $view_link;
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}

	/*
	 * Protected methods.
	 */

	/**
	 * Retrieve the post type for the current query.
	 *
	 * @todo Only call this after is_post_type_archive()?
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_post_type() {
		$post_type = get_query_var( 'post_type' );

		if ( is_array( $post_type ) ) {
			$post_type = reset( $post_type );
		}

		return $post_type;
	}

	/**
	 * Retrieve a post type's archive slug.
	 *
	 * Checks the 'has_archive' and 'with_front' args in order to build the slug.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type Post type name.
	 * @return string Archive slug.
	 */
	protected function get_post_type_archive_slug( $post_type ) {
		global $wp_rewrite;

		$post_type_object = get_post_type_object( $post_type );

		$slug = ( false !== $post_type_object->rewrite ) ? $post_type_object->rewrite['slug'] : $post_type_object->name;

		if ( $post_type_object->has_archive ) {
			$slug = ( true === $post_type_object->has_archive ) ? $post_type_object->rewrite['slug'] : $post_type_object->has_archive;

			if ( $post_type_object->rewrite['with_front'] ) {
				$slug = substr( $wp_rewrite->front, 1 ) . $slug;
			} else {
				$slug = $wp_rewrite->root . $slug;
			}
		}

		return $slug;
	}

	/**
	 * Create an archive post for a post type if one doesn't exist.
	 *
	 * The post type's plural label is used for the post title and the defined
	 * rewrite slug is used for the postname.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type_name Post type slug.
	 * @return int Post ID.
	 */
	protected function maybe_insert_archive_post( $post_type ) {
		$archive_id = $this->get_archive_id( $post_type );
		if ( $archive_id ) {
			return $archive_id;
		}

		// Search for an inactive archive before creating a new post.
		$inactive_posts = get_posts( array(
			'post_type'  => self::POST_TYPE,
			'meta_key'   => 'post_type',
			'meta_value' => $post_type,
			'fields'     => 'ids',
		) );

		if ( ! empty( $inactive_posts ) ) {
			return $inactive_posts[0];
		}

		// Search the inactive option before creating a new page.
		// The 'audiotheme_archives_inactive' option should be removed when
		// upgrading to 2.0.0. This is here for legacy purposes.
		$inactive = get_option( 'audiotheme_archives_inactive' );
		if ( $inactive && isset( $inactive[ $post_type ] ) && get_post( $inactive[ $post_type ] ) ) {
			return $inactive[ $post_type ];
		}

		// Otherwise, create a new archive post.
		$post_type_object = get_post_type_object( $post_type );

		$post = array(
			'post_title'  => $post_type_object->labels->name,
			'post_name'   => $this->get_post_type_archive_slug( $post_type ),
			'post_type'   => self::POST_TYPE,
			'post_status' => 'publish',
		);

		return wp_insert_post( $post );
	}

	/**
	 * Update a post type's rewrite base option.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type Post type slug.
	 * @param int $archive_id Archive post ID>
	 */
	protected function update_post_type_rewrite_base( $post_type, $archive_id ) {
		$archive = get_post( $archive_id );
		update_option( $post_type . '_rewrite_base', $archive->post_name );
	}
}
