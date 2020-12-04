<?php
add_action( 'init', 'githubauthvideo_register_post_type' );
function githubauthvideo_register_post_type() {
	$args = [
		'label'  => esc_html__( 'Github Sponsor Videos', 'text-domain' ),
		'labels' => [
			'menu_name'          => esc_html__( 'Github Sponsor Videos', 'githubauthvideo' ),
			'name_admin_bar'     => esc_html__( 'Github Sponsor Video', 'githubauthvideo' ),
			'add_new'            => esc_html__( 'Add Github Sponsor Video', 'githubauthvideo' ),
			'add_new_item'       => esc_html__( 'Add new Github Sponsor Video', 'githubauthvideo' ),
			'new_item'           => esc_html__( 'New Github Sponsor Video', 'githubauthvideo' ),
			'edit_item'          => esc_html__( 'Edit Github Sponsor Video', 'githubauthvideo' ),
			'view_item'          => esc_html__( 'View Github Sponsor Video', 'githubauthvideo' ),
			'update_item'        => esc_html__( 'View Github Sponsor Video', 'githubauthvideo' ),
			'all_items'          => esc_html__( 'All Github Sponsor Videos', 'githubauthvideo' ),
			'search_items'       => esc_html__( 'Search Github Sponsor Videos', 'githubauthvideo' ),
			'parent_item_colon'  => esc_html__( 'Parent Github Sponsor Video', 'githubauthvideo' ),
			'not_found'          => esc_html__( 'No Github Sponsor Videos found', 'githubauthvideo' ),
			'not_found_in_trash' => esc_html__( 'No Github Sponsor Videos found in Trash', 'githubauthvideo' ),
			'name'               => esc_html__( 'Github Sponsor Videos', 'githubauthvideo' ),
			'singular_name'      => esc_html__( 'Github Sponsor Video', 'githubauthvideo' ),
		],
		'public'              => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'show_ui'             => false,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => true,
		'show_in_rest'        => false,
		'capability_type'     => 'post',
		'hierarchical'        => false,
		'has_archive'         => true,
		'query_var'           => false,
		'can_export'          => true,
		'rewrite_no_front'    => false,
		'show_in_menu'        => 'upload.php',
		'menu_icon'           => 'dashicons-video-alt2',
		'supports' => [
			'title',
			'editor',
			'thumbnail',
		],
		
		'rewrite' => true
	];

	register_post_type( 'github-sponsor-video', $args );
}