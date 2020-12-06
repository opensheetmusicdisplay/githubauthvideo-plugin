<?php
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}
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
		'render_callback' => 'phonicscore_githubauthvideo_block_render_callback'
	) );
}

//Determines what's rendered in WP.
function phonicscore_githubauthvideo_block_render_callback($block_attributes, $content) {
	echo '<div>' . var_dump($block_attributes) . '</div>';
	return $content; 
	//Check for cookies
	//If cookies present, render video
	$token = $_COOKIE['githubauthvideo' . $_SERVER['HTTP_HOST'] . '_token'];
	if (!isset($token)){
		//Blurred video with auth link
	} else {
		$tokenType = 'bearer';
		$tokenTypeCookie = $_COOKIE['githubauthvideo' . $_SERVER['HTTP_HOST'] . '_token_type'];
		if(isset($tokenTypeCookie)){
			$tokenType = $tokenTypeCookie;
		}
		$videoUrl = 'github_auth_video?access_token=' . $token . '&token_type=' . $tokenType;
		echo $videoUrl;
		exit;
	}
	//If cookies not present, render blurred video with auth link
	//Auth link navigates to github_auth with a 'return_path' query param (the current path)
}

add_action( 'init', 'phonicscore_githubauthvideo_block_init' );

add_action( 'parse_request', function( $wp ){
	$uri = $_SERVER['REQUEST_URI'];
    if ( preg_match( '/github_auth/', $uri ) ) {
        // If we match, means we have a github oauth callback
        include_once plugin_dir_path( __FILE__ ) . 'authentication/auth.php';
        exit; // and exit
    } else if ( preg_match( '/github_auth_video/', $uri ) ) {
        // If we match, means we have a github oauth callback
        include_once plugin_dir_path( __FILE__ ) . 'authentication/serve-video.php';
        exit; // and exit
    }
} );

?>