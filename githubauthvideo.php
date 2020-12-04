<?php
/**
 * Plugin Name:     Github Authenticated Video
 * Description:     Video that is behind github oauth prompt. Checks for sponsorship
 * Version:         0.1.0
 * Author:          Justin Litten
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     githubauthvideo
 *
 * @package         phonicscore
 */

/**
 * Registers all block assets so that they can be enqueued through the block editor
 * in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */

include 'plugin_bootstrapping/settings.php';
include 'plugin_bootstrapping/post_type.php';

function phonicscore_githubauthvideo_block_init() {
	$dir = dirname( __FILE__ );

	$script_asset_path = "$dir/build/index.asset.php";
	if ( ! file_exists( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "phonicscore/githubauthvideo" block first.'
		);
	}
	$index_js     = 'build/index.js';
	$script_asset = require( $script_asset_path );
	wp_register_script(
		'phonicscore-githubauthvideo-block-editor',
		plugins_url( $index_js, __FILE__ ),
		$script_asset['dependencies'],
		$script_asset['version']
	);
	wp_set_script_translations( 'phonicscore-githubauthvideo-block-editor', 'githubauthvideo' );

	$editor_css = 'build/index.css';
	wp_register_style(
		'phonicscore-githubauthvideo-block-editor',
		plugins_url( $editor_css, __FILE__ ),
		array(),
		filemtime( "$dir/$editor_css" )
	);

	$style_css = 'build/style-index.css';
	wp_register_style(
		'phonicscore-githubauthvideo-block',
		plugins_url( $style_css, __FILE__ ),
		array(),
		filemtime( "$dir/$style_css" )
	);

	register_block_type( 'phonicscore/githubauthvideo', array(
		'editor_script' => 'phonicscore-githubauthvideo-block-editor',
		'editor_style'  => 'phonicscore-githubauthvideo-block-editor',
		'style'         => 'phonicscore-githubauthvideo-block',
	) );
}
add_action( 'init', 'phonicscore_githubauthvideo_block_init' );
/*
add_action( 'parse_request', function( $wp ){
	var_dump($wp);
	exit;
    if ( preg_match( '#^github_auth/?#', $wp->request, $matches ) ) {
        //$leaf = $matches[1];

        // Load your file - make sure the path is correct.
        include_once plugin_dir_path( __FILE__ ) . 'githubauthvideo-plugin/authentication/auth.php';

        exit; // and exit
    }
} );
*/

function register_rewrite_rule_init() {
	//flush_rewrite_rules();
	$plugin_url = plugins_url( 'githubauthvideo-plugin/authentication/auth.php', __FILE__ );
	add_rewrite_rule('^github_auth/?', $plugin_url, 'top');
	flush_rewrite_rules(false);
}

add_action( 'init',  'register_rewrite_rule_init' );

?>