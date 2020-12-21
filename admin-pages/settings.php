<?php
/**
 * Generated by the WordPress Option Page generator
 * at http://jeremyhixon.com/wp-tools/option-page/
 */

class GithubVideoAuthMainSettings {
	private $main_settings_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'main_settings_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'main_settings_page_init' ) );
	}

	public function main_settings_add_plugin_page() {
		add_options_page(
            'Github Sponsor Video',
            'Github Sponsor Video Plugin',
            'manage_options',
            'githubauthvideo',
			array( $this, 'main_settings_create_admin_page' ) // function
		);
	}

	public function main_settings_create_admin_page() {
		$this->main_settings_options = get_option( 'main_settings_option_name' ); ?>

		<div class="wrap">
			<h2>Plugin Settings</h2>
			<p></p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'main_settings_option_group' );
					do_settings_sections( 'main-settings-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function main_settings_page_init() {
		register_setting(
			'main_settings_option_group', // option_group
			'main_settings_option_name', // option_name
			array( $this, 'main_settings_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'main_settings_setting_section', // id
			'Settings', // title
			array( $this, 'main_settings_section_info' ), // callback
			'main-settings-admin' // page
		);

		add_settings_field(
			'github_app_client_id_0', // id
			'Github App Client ID', // title
			array( $this, 'github_app_client_id_0_callback' ), // callback
			'main-settings-admin', // page
			'main_settings_setting_section' // section
		);

		add_settings_field(
			'github_app_client_secret_1', // id
			'Github App Client Secret', // title
			array( $this, 'github_app_client_secret_1_callback' ), // callback
			'main-settings-admin', // page
			'main_settings_setting_section' // section
		);

		add_settings_field(
			'jwt_private_key_2', // id
			'Private Key For Session Generation', // title
			array( $this, 'jwt_private_key_2_callback' ), // callback
			'main-settings-admin', // page
			'main_settings_setting_section' // section
		);

		add_settings_field(
			'track_with_google_analytics_3', // id
			'Track With Google Analytics', // title
			array( $this, 'track_with_google_analytics_3_callback' ), // callback
			'main-settings-admin', // page
			'main_settings_setting_section' // section
		);

		add_settings_field(
			'ignore_sponsorship_4', // id
			'Ignore Sponsorship Status', // title
			array( $this, 'ignore_sponsorship_4_callback' ), // callback
			'main-settings-admin', // page
			'main_settings_setting_section' // section
		);

		add_settings_field(
			'do_not_enforce_https_5', // id
			'Do Not Require HTTPS', // title
			array( $this, 'do_not_enforce_https_5_callback' ), // callback
			'main-settings-admin', // page
			'main_settings_setting_section' // section
		);

		add_settings_field(
			'server_side_rendering_6', // id
			'Use Server-Side Rendering for Player', // title
			array( $this, 'server_side_rendering_6_callback' ), // callback
			'main-settings-admin', // page
			'main_settings_setting_section' // section
		);
	}

	public function main_settings_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['github_app_client_id_0'] ) ) {
			$sanitary_values['github_app_client_id_0'] = sanitize_text_field( $input['github_app_client_id_0'] );
		}

		if ( isset( $input['github_app_client_secret_1'] ) ) {
			$sanitary_values['github_app_client_secret_1'] = sanitize_text_field( $input['github_app_client_secret_1'] );
		}

		if ( isset( $input['jwt_private_key_2'] ) ) {
			$sanitary_values['jwt_private_key_2'] = sanitize_text_field( $input['jwt_private_key_2'] );
		}

		if ( isset( $input['track_with_google_analytics_3'] ) ) {
			$sanitary_values['track_with_google_analytics_3'] = $input['track_with_google_analytics_3'];
		}

		if ( isset( $input['ignore_sponsorship_4'] ) ) {
			$sanitary_values['ignore_sponsorship_4'] = $input['ignore_sponsorship_4'];
		}

		if ( isset( $input['do_not_enforce_https_5'] ) ) {
			$sanitary_values['do_not_enforce_https_5'] = $input['do_not_enforce_https_5'];
		}

		if ( isset( $input['server_side_rendering_6'] ) ) {
			$sanitary_values['server_side_rendering_6'] = $input['server_side_rendering_6'];
		}
		return $sanitary_values;
	}

	public function main_settings_section_info() {
		
	}

	public function github_app_client_id_0_callback() {
		printf(
			'<input class="regular-text" type="text" name="main_settings_option_name[github_app_client_id_0]" id="github_app_client_id_0" value="%s">',
			isset( $this->main_settings_options['github_app_client_id_0'] ) ? esc_attr( $this->main_settings_options['github_app_client_id_0']) : ''
		);
	}

	public function github_app_client_secret_1_callback() {
		printf(
			'<input class="regular-text" type="text" name="main_settings_option_name[github_app_client_secret_1]" id="github_app_client_secret_1" value="%s">',
			isset( $this->main_settings_options['github_app_client_secret_1'] ) ? esc_attr( $this->main_settings_options['github_app_client_secret_1']) : ''
		);
	}

	public function jwt_private_key_2_callback() {
		printf(
			'<input class="regular-text" type="text" name="main_settings_option_name[jwt_private_key_2]" id="jwt_private_key_2" value="%s">',
			isset( $this->main_settings_options['jwt_private_key_2'] ) ? esc_attr( $this->main_settings_options['jwt_private_key_2']) : ''
		);
	}

	public function track_with_google_analytics_3_callback() {
		printf(
			'<input type="checkbox" name="main_settings_option_name[track_with_google_analytics_3]" id="track_with_google_analytics_3" value="track_with_google_analytics_3" %s> <label for="track_with_google_analytics_3">If GA is added to the front-end, the player can automatically track playback events to it.</label>',
			( isset( $this->main_settings_options['track_with_google_analytics_3'] ) && $this->main_settings_options['track_with_google_analytics_3'] === 'track_with_google_analytics_3' ) ? 'checked' : ''
		);
	}

	public function ignore_sponsorship_4_callback() {
		printf(
			'<input type="checkbox" name="main_settings_option_name[ignore_sponsorship_4]" id="ignore_sponsorship_4" value="ignore_sponsorship_4" %s> <label for="ignore_sponsorship_4">Check to ignore whether the user is a sponsor of the specified organization. Just Github authentication will grant them access.</label>',
			( isset( $this->main_settings_options['ignore_sponsorship_4'] ) && $this->main_settings_options['ignore_sponsorship_4'] === 'ignore_sponsorship_4' ) ? 'checked' : ''
		);
	}

	public function do_not_enforce_https_5_callback() {
		printf(
			'<input type="checkbox" name="main_settings_option_name[do_not_enforce_https_5]" id="do_not_enforce_https_5" value="do_not_enforce_https_5" %s> <label for="do_not_enforce_https_5">Check to ignore whether the server has HTTPS enabled. THIS IS NOT RECOMMENDED FOR SECURITY REASONS.</label>',
			( isset( $this->main_settings_options['do_not_enforce_https_5'] ) && $this->main_settings_options['do_not_enforce_https_5'] === 'do_not_enforce_https_5' ) ? 'checked' : ''
		);
	}

	public function server_side_rendering_6_callback() {
		printf(
			'<input type="checkbox" name="main_settings_option_name[server_side_rendering_6]" id="server_side_rendering_6" value="server_side_rendering_6" %s> <label for="server_side_rendering_6">Check to render the player via the server, or use client-side rendering. If your hosting provider enforced server caching, client-side rendering may be necessary.</label>',
			( isset( $this->main_settings_options['server_side_rendering_6'] ) && $this->main_settings_options['server_side_rendering_6'] === 'server_side_rendering_6' ) ? 'checked' : ''
		);
	}

}
if ( is_admin() )
	$main_settings = new GithubVideoAuthMainSettings();

/* 
 * Retrieve this value with:
 * $main_settings_options = get_option( 'main_settings_option_name' ); // Array of All Options
 * $github_app_client_id_0 = $main_settings_options['github_app_client_id_0']; // Github App Client ID
 * $github_app_client_secret_1 = $main_settings_options['github_app_client_secret_1']; // Github App Client Secret
 * $jwt_private_key_2 = $main_settings_options['jwt_private_key_2']; // Private Key For Session Generation
 * $track_with_google_analytics_3 = $main_settings_options['track_with_google_analytics_3']; // Whether we want to enable google analytics or not (needs included already)
 * $ignore_sponsorship_4 = $main_settings_options['ignore_sponsorship_4']; // Whether to track if the user is sponsoring the organization or not for video access
 * $do_not_enforce_https_5 = $main_settings_options['do_not_enforce_https_5']; // Check to ignore enforcement of HTTPS on the server
 * $server_side_rendering_6 = $main_settings_options['server_side_rendering_6']; // Whether the server does the rendering or the client
 */

 ?>