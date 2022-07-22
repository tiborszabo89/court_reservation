<?php
/**
 * Generated by the WordPress Option Page generator
 * at http://jeremyhixon.com/wp-tools/option-page/
 */

class EmailBelltsok {
	private $email_belltsok_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'email_belltsok_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'email_belltsok_page_init' ) );
	}

	public function email_belltsok_add_plugin_page() {
		add_submenu_page(
      'vue-court-reservation',
			'Email beállítások', // page_title
			'Email beállítások', // menu_title
			'manage_options', // capability
			'email-belltsok', // menu_slug
			array( $this, 'email_belltsok_create_admin_page' ) // function
		);
	}

	public function email_belltsok_create_admin_page() {
		$this->email_belltsok_options = get_option( 'email_belltsok_option_name' ); ?>

		<div class="wrap">
			<h2>Email beállítások</h2>
			<p></p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'email_belltsok_option_group' );
					do_settings_sections( 'email-belltsok-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function email_belltsok_page_init() {
		register_setting(
			'email_belltsok_option_group', // option_group
			'email_belltsok_option_name', // option_name
			array( $this, 'email_belltsok_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'email_belltsok_setting_section', // id
			'Settings', // title
			array( $this, 'email_belltsok_section_info' ), // callback
			'email-belltsok-admin' // page
		);

		add_settings_field(
			'cmzettek_0', // id
			'Címzettek', // title
			array( $this, 'cmzettek_0_callback' ), // callback
			'email-belltsok-admin', // page
			'email_belltsok_setting_section' // section
		);

		add_settings_field(
			'msolatot_kapnak_1', // id
			'Másolatot kapnak', // title
			array( $this, 'msolatot_kapnak_1_callback' ), // callback
			'email-belltsok-admin', // page
			'email_belltsok_setting_section' // section
		);

		add_settings_field(
			'admin_j_foglals_zenet_2', // id
			'Admin: Új foglalás üzenet', // title
			array( $this, 'admin_j_foglals_zenet_2_callback' ), // callback
			'email-belltsok-admin', // page
			'email_belltsok_setting_section' // section
		);

		add_settings_field(
			'admin_foglals_szerkesztve_zenet_3', // id
			'Admin: Foglalás szerkesztve üzenet', // title
			array( $this, 'admin_foglals_szerkesztve_zenet_3_callback' ), // callback
			'email-belltsok-admin', // page
			'email_belltsok_setting_section' // section
		);

		add_settings_field(
			'admin_foglals_trlve_zenet_4', // id
			'Admin: Foglalás törölve üzenet', // title
			array( $this, 'admin_foglals_trlve_zenet_4_callback' ), // callback
			'email-belltsok-admin', // page
			'email_belltsok_setting_section' // section
		);

		add_settings_field(
			'user_j_foglals_zenet_5', // id
			'User: Új foglalás üzenet', // title
			array( $this, 'user_j_foglals_zenet_5_callback' ), // callback
			'email-belltsok-admin', // page
			'email_belltsok_setting_section' // section
		);

		add_settings_field(
			'user_foglals_szerkesztve_zenet_6', // id
			'User: Foglalás szerkesztve üzenet', // title
			array( $this, 'user_foglals_szerkesztve_zenet_6_callback' ), // callback
			'email-belltsok-admin', // page
			'email_belltsok_setting_section' // section
		);

		add_settings_field(
			'user_foglals_trlve_zenet_7', // id
			'User: Foglalás törölve üzenet', // title
			array( $this, 'user_foglals_trlve_zenet_7_callback' ), // callback
			'email-belltsok-admin', // page
			'email_belltsok_setting_section' // section
		);
	}

	public function email_belltsok_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['cmzettek_0'] ) ) {
			$sanitary_values['cmzettek_0'] = sanitize_text_field( $input['cmzettek_0'] );
		}

		if ( isset( $input['msolatot_kapnak_1'] ) ) {
			$sanitary_values['msolatot_kapnak_1'] = sanitize_text_field( $input['msolatot_kapnak_1'] );
		}

		if ( isset( $input['admin_j_foglals_zenet_2'] ) ) {
			$sanitary_values['admin_j_foglals_zenet_2'] = esc_textarea( $input['admin_j_foglals_zenet_2'] );
		}

		if ( isset( $input['admin_foglals_szerkesztve_zenet_3'] ) ) {
			$sanitary_values['admin_foglals_szerkesztve_zenet_3'] = esc_textarea( $input['admin_foglals_szerkesztve_zenet_3'] );
		}

		if ( isset( $input['admin_foglals_trlve_zenet_4'] ) ) {
			$sanitary_values['admin_foglals_trlve_zenet_4'] = esc_textarea( $input['admin_foglals_trlve_zenet_4'] );
		}

		if ( isset( $input['user_j_foglals_zenet_5'] ) ) {
			$sanitary_values['user_j_foglals_zenet_5'] = esc_textarea( $input['user_j_foglals_zenet_5'] );
		}

		if ( isset( $input['user_foglals_szerkesztve_zenet_6'] ) ) {
			$sanitary_values['user_foglals_szerkesztve_zenet_6'] = esc_textarea( $input['user_foglals_szerkesztve_zenet_6'] );
		}

		if ( isset( $input['user_foglals_trlve_zenet_7'] ) ) {
			$sanitary_values['user_foglals_trlve_zenet_7'] = esc_textarea( $input['user_foglals_trlve_zenet_7'] );
		}

		return $sanitary_values;
	}

	public function email_belltsok_section_info() {
		
	}

	public function cmzettek_0_callback() {
		printf(
			'<input class="regular-text" type="text" name="email_belltsok_option_name[cmzettek_0]" id="cmzettek_0" value="%s" style="width: %s;">',
			isset( $this->email_belltsok_options['cmzettek_0'] ) ? esc_attr( $this->email_belltsok_options['cmzettek_0']) : '',
      '100%'
		);
	}

	public function msolatot_kapnak_1_callback() {
		printf(
			'<input class="regular-text" type="text" name="email_belltsok_option_name[msolatot_kapnak_1]" id="msolatot_kapnak_1" value="%s" style="width: %s;">',
			isset( $this->email_belltsok_options['msolatot_kapnak_1'] ) ? esc_attr( $this->email_belltsok_options['msolatot_kapnak_1']) : '',
      '100%'
		);
	}

	public function admin_j_foglals_zenet_2_callback() {
		printf(
			'<textarea class="large-text" rows="5" name="email_belltsok_option_name[admin_j_foglals_zenet_2]" id="admin_j_foglals_zenet_2">%s</textarea>',
			isset( $this->email_belltsok_options['admin_j_foglals_zenet_2'] ) ? esc_attr( $this->email_belltsok_options['admin_j_foglals_zenet_2']) : ''
		);
	}

	public function admin_foglals_szerkesztve_zenet_3_callback() {
		printf(
			'<textarea class="large-text" rows="5" name="email_belltsok_option_name[admin_foglals_szerkesztve_zenet_3]" id="admin_foglals_szerkesztve_zenet_3">%s</textarea>',
			isset( $this->email_belltsok_options['admin_foglals_szerkesztve_zenet_3'] ) ? esc_attr( $this->email_belltsok_options['admin_foglals_szerkesztve_zenet_3']) : ''
		);
	}

	public function admin_foglals_trlve_zenet_4_callback() {
		printf(
			'<textarea class="large-text" rows="5" name="email_belltsok_option_name[admin_foglals_trlve_zenet_4]" id="admin_foglals_trlve_zenet_4">%s</textarea>',
			isset( $this->email_belltsok_options['admin_foglals_trlve_zenet_4'] ) ? esc_attr( $this->email_belltsok_options['admin_foglals_trlve_zenet_4']) : ''
		);
	}

	public function user_j_foglals_zenet_5_callback() {
		printf(
			'<textarea class="large-text" rows="5" name="email_belltsok_option_name[user_j_foglals_zenet_5]" id="user_j_foglals_zenet_5">%s</textarea>',
			isset( $this->email_belltsok_options['user_j_foglals_zenet_5'] ) ? esc_attr( $this->email_belltsok_options['user_j_foglals_zenet_5']) : ''
		);
	}

	public function user_foglals_szerkesztve_zenet_6_callback() {
		printf(
			'<textarea class="large-text" rows="5" name="email_belltsok_option_name[user_foglals_szerkesztve_zenet_6]" id="user_foglals_szerkesztve_zenet_6">%s</textarea>',
			isset( $this->email_belltsok_options['user_foglals_szerkesztve_zenet_6'] ) ? esc_attr( $this->email_belltsok_options['user_foglals_szerkesztve_zenet_6']) : ''
		);
	}

	public function user_foglals_trlve_zenet_7_callback() {
		printf(
			'<textarea class="large-text" rows="5" name="email_belltsok_option_name[user_foglals_trlve_zenet_7]" id="user_foglals_trlve_zenet_7">%s</textarea>',
			isset( $this->email_belltsok_options['user_foglals_trlve_zenet_7'] ) ? esc_attr( $this->email_belltsok_options['user_foglals_trlve_zenet_7']) : ''
		);
	}

}
if ( is_admin() )
	$email_belltsok = new EmailBelltsok();

/* 
 * Retrieve this value with:
 * $email_belltsok_options = get_option( 'email_belltsok_option_name' ); // Array of All Options
 * $cmzettek_0 = $email_belltsok_options['cmzettek_0']; // Címzettek
 * $msolatot_kapnak_1 = $email_belltsok_options['msolatot_kapnak_1']; // Másolatot kapnak
 * $admin_j_foglals_zenet_2 = $email_belltsok_options['admin_j_foglals_zenet_2']; // Admin: Új foglalás üzenet
 * $admin_foglals_szerkesztve_zenet_3 = $email_belltsok_options['admin_foglals_szerkesztve_zenet_3']; // Admin: Foglalás szerkesztve üzenet
 * $admin_foglals_trlve_zenet_4 = $email_belltsok_options['admin_foglals_trlve_zenet_4']; // Admin: Foglalás törölve üzenet
 * $user_j_foglals_zenet_5 = $email_belltsok_options['user_j_foglals_zenet_5']; // User: Új foglalás üzenet
 * $user_foglals_szerkesztve_zenet_6 = $email_belltsok_options['user_foglals_szerkesztve_zenet_6']; // User: Foglalás szerkesztve üzenet
 * $user_foglals_trlve_zenet_7 = $email_belltsok_options['user_foglals_trlve_zenet_7']; // User: Foglalás törölve üzenet
 */
