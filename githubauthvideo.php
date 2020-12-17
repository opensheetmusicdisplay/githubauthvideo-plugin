<?php
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}
/**
 * Plugin Name:     Github Authenticated Video
 * Description:     Video that is behind github oauth prompt. Checks for sponsorship
 * Version:         1.0.0
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
const VIDEO_JS_URL = 'https://vjs.zencdn.net/7.10.2/video.js';
const VIDEO_CSS_URL = 'https://vjs.zencdn.net/7.10.2/video-js.css';

include 'api/VideoStream.php';
include 'admin-pages/settings.php';
include 'admin-pages/post_type.php';
include 'authentication/GithubAuthCookies.php';
include 'api/GithubAPIService.php';

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

function get_splash_image($videoId = NULL){
	$splashUrl = esc_url( plugins_url( 'images/blur.png', __FILE__ ) );
	if(isset($videoId)) {
		$metaSplash =  get_post_meta( $videoId, 'githubauthvideo_splash-screen', true );
		if(isset($metaSplash) && !empty($metaSplash)){
			$splashUrl = $metaSplash;
		}
	}

	return $splashUrl;
}

function render_github_auth($videoId = NULL){
	$returnPath = $_SERVER['REQUEST_URI'];
	$authUrl = esc_url( '/github_auth?return_path=' . urlencode($returnPath) );
	$splashUrl = get_splash_image($videoId);
	$ghIconUrl = esc_url( plugins_url( 'images/github-icon.png', __FILE__ ) );
	return <<<EOT
	<div class="githubvideoauth-video-auth-splash-container">
		<img src="$splashUrl" class="githubvideoauth-video-auth-splash-image" />
		<div class="githubvideoauth-video-auth-splash-cover">
			<a href="$authUrl">
				<button>
					<img class="github-icon" src="$ghIconUrl"> <span class="github-button-text">Authenticate with Github</span>
				</button>
			</a>
		</div>
	</div>
	EOT;
}

function render_sponsor($videoId = NULL, $orgId = ''){
	$splashUrl = get_splash_image($videoId);
	$ghIconUrl = esc_url( plugins_url( 'images/github-icon.png', __FILE__ ) );
	//TODO: Have reasonable default
	$sponsorUrl = esc_url('https://github.com/sponsors/' . $orgId);
	return <<<EOT
	<div class="githubvideoauth-video-auth-splash-container">
		<img src="$splashUrl" class="githubvideoauth-video-auth-splash-image" />
		<div class="githubvideoauth-video-auth-splash-cover">
			<div class="githubvideoauth-sponsor-message-block">
				<text>Only Github sponsors have access to this video.</text>
			</div>
			<br>
			<a href="$sponsorUrl" target="_blank">
				<button>
					<img class="github-icon" src="$ghIconUrl"> <span class="github-button-text">Become a sponsor now!</span>
				</button>
			</a>
		</div>
	</div>
	EOT;
}

function render_video($videoId){
	$videoUrl = '/github_auth_video?video_id=' . $videoId;
	$location = get_post_meta( $videoId, 'githubauthvideo_video-location-uri', true );
	$textContent = get_post_meta( $videoId, 'githubauthvideo_video-description', true );
	$title = '';
	$post = get_post($videoId);
	if($post){
		$title = $post->post_title;
	}
	$mimeType = get_video_mime_type($location);
	return <<<EOT
		<h5 class="githubvideoauth-video-title-container">$title</h5>
		<div class="githubvideoauth-video-container">
			<video class="githubvideoauth-video"
			 title="$title"
			 alt="$videoId"
			 controls
			 preload="auto"
			 data-setup='{}'
			>
				<source src="$videoUrl" type="$mimeType"></source>
			</video>
		</div>
		<div class="githubvideoauth-video-text-content-container">
			$textContent
		</div>
	EOT;
}

//Determines what's rendered in WP.
function phonicscore_githubauthvideo_block_render_callback($block_attributes, $content) {
	if(is_admin()){
		return '';
	}
	if(!isset($block_attributes['videoId'])){
		return '<div>No video was selected.</div>';
	}

	$videoId = $block_attributes['videoId'];

	if($videoId == -1){
		return '<div>No video was selected.</div>';
	}
	$GithubApi = new GithubAPIService(GITHUB_GRAPH_API_URL);
	$orgId = get_post_meta( $videoId, 'githubauthvideo_github-organization-slug', true );
	
	if($GithubApi->is_token_valid()){
		if($GithubApi->is_viewer_sponsor_of_org($orgId)){
			//Token seems to be valid, render actual video embed
			return render_video($videoId);
		} else {
			//User auth'd correctly, but is not sponsor of specified organization
			return render_sponsor($videoId, $orgId);
		}
	} else {
		//User is not auth'd properly
		return render_github_auth($videoId);
	}
	
}

add_action( 'init', 'phonicscore_githubauthvideo_block_init' );

add_action( 'parse_request', function( $wp ){
	$uri = $_SERVER['REQUEST_URI'];
	if ( preg_match( '/github_auth_video/', $uri ) ) {
        include_once plugin_dir_path( __FILE__ ) . 'authentication/serve-video.php';
        exit; // and exit
	} else if ( preg_match( '/github_auth/', $uri ) ) {
        // If we match, means we have a github oauth callback
        include_once plugin_dir_path( __FILE__ ) . 'authentication/auth.php';
        exit; // and exit
    }
} );

add_action( 'wp_enqueue_scripts', 'phonicscore_githubauthvideo_block_enqueue_js' );
function phonicscore_githubauthvideo_block_enqueue_js( ) {
	//Can't do conditional enqueuing since the block could be embedded on any post
	/*
	wp_enqueue_style( 'video-style', VIDEO_CSS_URL, array( ), '7.10.2' );
    wp_enqueue_script(
        'video-script',
        VIDEO_JS_URL,
        array( ),
        '7.10.2',
        true
	);
	*/
	$main_settings_options = get_option( 'main_settings_option_name' ); // Array of All Options
	if($main_settings_options){
		$track_with_google_analytics_3 = $main_settings_options['track_with_google_analytics_3']; //Google Analytics setting
		if($track_with_google_analytics_3 == TRUE){	
			wp_enqueue_script(
				'video-analytics-script',
				esc_url( plugins_url( 'frontend_scripts/analytics.js', __FILE__ ) ),
				array( ),
				'0.1.0',
				true
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
add_action('admin_enqueue_scripts', 'enqueue_editor_assets');
?>