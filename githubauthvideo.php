<?php
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}
/**
 * Plugin Name:     Github Authenticated Video
 * Description:     Video that is behind github oauth prompt. Checks for sponsorship
 * Version:         1.0.5
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

 //Improvements to make: Only check for auth once if multiple videos on page

 //TODO: these should be config options probably
const GITHUB_GRAPH_API_URL = 'https://api.github.com/graphql';
const GITHUB_OAUTH_BEGIN_URL = 'https://github.com/login/oauth/authorize';
const GITHUB_OAUTH_TOKEN_URL = 'https://github.com/login/oauth/access_token';

include 'api/VideoStream.php';
include 'authentication/GithubAuthCookies.php';
include 'api/GithubAPIService.php';
include 'rendering/PlayerHtmlRendering.php';

//If we get more media utility functions like this, break out into it's own file.
//For now, sufficient to contain it here
function get_video_mime_type($location){
	$mimes = new \Mimey\MimeTypes;
	$mimeType = 'video/*';
	//try path info
	$pathInfo = pathinfo($location, PATHINFO_EXTENSION);
	if(isset($pathInfo) && $pathInfo != ''){
		$mimeType = $mimes->getMimeType($pathInfo);
	}
	return $mimeType;
}

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
	if(is_admin()){
		return '';
	}
	$videoId = -1;
	if(isset($block_attributes['videoId'])){
		$videoId = $block_attributes['videoId'];
	}
	$orgId = get_post_meta( $videoId, 'githubauthvideo_github-organization-slug', true );
	$returnPath = $_SERVER['REQUEST_URI'];
	$renderer = new PlayerHtmlRenderer($videoId, $orgId, $returnPath);

	$main_settings_options = get_option( 'main_settings_option_name' );
	$SERVER_SIDE_RENDERING = FALSE;
	if($main_settings_options && array_key_exists("server_side_rendering_6", $main_settings_options)){
		$SERVER_SIDE_RENDERING = $main_settings_options['server_side_rendering_6'];
	}

	if($SERVER_SIDE_RENDERING){
		if($videoId == -1){
			return '<div>No video was selected.</div>';
		}
		$GithubApi = new GithubAPIService(GITHUB_GRAPH_API_URL);
		if($GithubApi->is_token_valid()){
			if($GithubApi->is_viewer_sponsor_of_org($orgId)){
				//Token seems to be valid, render actual video embed
				return $renderer->get_video_html();
			} else {
				//User auth'd correctly, but is not sponsor of specified organization
				return $renderer->get_sponsor_html();
			}
		} else {
			//User is not auth'd properly
			return $renderer->get_auth_html();
		}
		
	} else {
		//If we aren't doing server-side rendering, render the placeholder for JS to take over 
		return $renderer->get_video_placeholder_html();	
	}
}

function phonicscore_githubauthvideo_block_enqueue_js( ) {
	$main_settings_options = get_option( 'main_settings_option_name' );

	$SERVER_SIDE_RENDERING = FALSE;
	if($main_settings_options && array_key_exists("server_side_rendering_6", $main_settings_options)){
		$SERVER_SIDE_RENDERING = $main_settings_options['server_side_rendering_6'];
	}

	//Only enqueue player script if we don't have server-side rendering enabled
	if(!$SERVER_SIDE_RENDERING){
		//Can't do conditional enqueuing since the block could be embedded on any post
		wp_enqueue_script(
			'githubauthvideo-script',
			esc_url( plugins_url( 'build/player/player.min.js', __FILE__ ) ),
			array( ),
			'1.0.5',
			true
		);

		$Cookies = GithubAuthCookies::getCookiesInstance();
		$IGNORE_SPONSORSHIP = FALSE;
		if($main_settings_options && array_key_exists("ignore_sponsorship_4", $main_settings_options)){
			$IGNORE_SPONSORSHIP = $main_settings_options['ignore_sponsorship_4'];
		}
	
		//TODO: Test if we need to pull in the HTML like this and compile w/ handlebars, or if the web service will be sufficient
		wp_localize_script(
			'githubauthvideo-script',
			'githubauthvideo_player_js_data',
			array(
				'auth_html' => plugins_url( 'html/auth.html', __FILE__ ),
				'sponsor_html' => plugins_url( 'html/sponsor.html', __FILE__ ),
				'video_html' => plugins_url( 'html/video.html', __FILE__ ),
				'token_key' => $Cookies->get_token_key(),
				'token_type_key' => $Cookies->get_token_type_key(),
				'github_api_url' => GITHUB_GRAPH_API_URL,
				'video_html_url' => '/githubauthvideo_video_html',
				'ignore_sponsorship' => $IGNORE_SPONSORSHIP
			)
		);
	}

	$main_settings_options = get_option( 'main_settings_option_name' ); // Array of All Options
	if($main_settings_options){
		$track_with_google_analytics_3 = $main_settings_options['track_with_google_analytics_3']; //Google Analytics setting
		if($track_with_google_analytics_3 == TRUE){	
			wp_enqueue_script(
				'githubauthvideo-analytics-script',
				esc_url( plugins_url( 'build/player/analytics.min.js', __FILE__ ) ),
				array(),
				'1.0.1',
				true
			);

			wp_localize_script(
				'githubauthvideo-analytics-script',
				'githubauthvideo_analytics_js_data',
				array(
					'server_side_rendering' => $SERVER_SIDE_RENDERING
				)
			);
		}
	}
}

function enqueue_editor_assets(){
	wp_localize_script(
		'phonicscore-githubauthvideo-block-editor',
		'js_data',
		array(
			'player_image' => plugins_url( 'images/editor-player.png', __FILE__ )
		)
	);
}

function githubauthvideo_setup_rewrite_rules(){
	add_rewrite_rule( 'githubauthvideo_video_html[/]?$', 'index.php?githubauthvideo_video_html=1', 'top' );
	add_rewrite_rule( 'githubauthvideo_video/([0-9]+)[/]?$', 'index.php?githubauthvideo_video=$matches[1]', 'top' );
	add_rewrite_rule( 'githubauthvideo_auth/([1-2])[/]?(.*)$', 'index.php?githubauthvideo_auth=$matches[1]', 'top' );
	add_filter( 'query_vars', function( $query_vars ) {
		array_push($query_vars, 'githubauthvideo_video', 'githubauthvideo_auth', 'githubauthvideo_video_html', 'code', 'state', 'return_path');
		return $query_vars;
	} );

	add_action( 'template_include', function( $template ) {
		if ( get_query_var( 'githubauthvideo_video' ) != false && get_query_var( 'githubauthvideo_video' ) != '' ) {
			return plugin_dir_path( __FILE__ ) . 'authentication/serve-video.php';
		} else if ( get_query_var( 'githubauthvideo_auth' ) != false && get_query_var( 'githubauthvideo_auth' ) != '' ) {
			return plugin_dir_path( __FILE__ ) . 'authentication/auth.php';
		}  else if ( get_query_var( 'githubauthvideo_video_html' ) != false && get_query_var( 'githubauthvideo_video_html' ) != '' ) {
			return plugin_dir_path( __FILE__ ) . 'api/serve-player-html.php';
		}
		return $template;
	} );
}

function githubauthvideo_activate() { 
    // Register rewrite rules
    githubauthvideo_setup_rewrite_rules(); 
    // reset permalinks
    flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'githubauthvideo_activate' );

function githubauthvideo_deactivate(){
	unregister_post_type('github-sponsor-video');

	flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'githubauthvideo_deactive' );

function githubauthvideo_uninstall(){
	githubauthvideo_deactivate();
	delete_option();
}

register_uninstall_hook(__FILE__, 'githubauthvideo_uninstall');

include 'admin-pages/settings.php';
include 'admin-pages/post_type.php';
add_action( 'init', 'phonicscore_githubauthvideo_block_init' );
add_action( 'init',  'githubauthvideo_setup_rewrite_rules' );
add_action( 'wp_enqueue_scripts', 'phonicscore_githubauthvideo_block_enqueue_js' );
add_action('admin_enqueue_scripts', 'enqueue_editor_assets');
?>