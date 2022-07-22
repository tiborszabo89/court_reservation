<?php
/**
* Plugin Name: Vue Court Reservation By Evista
* Plugin URI: https://profiles.wordpress.org/csomorelwood/
* Description: Court Reservation Calendar
* Version: 99.9
* Author: Csömör
* Author URI: https://profiles.wordpress.org/csomorelwood/
**/

/*
** Register scripts, styles, init Vue
*/
function func_load_vuescripts() {
  wp_register_script('wpvue_vuejs1', plugin_dir_url( __FILE__ ).'dist/js/chunk-vendors.js', true);

	wp_register_script('wpvue_vuejs', plugin_dir_url( __FILE__ ) . 'dist/js/app.js', true);
  wp_enqueue_script('wpvue_vuejs');
  
  wp_localize_script( 'wpvue_vuejs', 'wpApiSettings', array(
    'resturl' => esc_url_raw( rest_url() ),
    'nonce' => wp_create_nonce( 'wp_rest' ),
  ) );
  
  if(!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php"); 
  }

  wp_localize_script( 'wpvue_vuejs', 'userData', array(
    'id' => get_current_user_id(),
    'name' => wp_get_current_user()->display_name,
    'full_name' => wp_get_current_user()->first_name . " " . wp_get_current_user()->last_name,
    'email' => wp_get_current_user()->user_email,
    'phone' => esc_attr(get_the_author_meta('phone', get_current_user_id())),
    'berlet' => esc_attr(get_the_author_meta('berlet', get_current_user_id())),
    'is_admin' => current_user_can('administrator') ? 1 : 0
  ) );

  $szvegek_options = get_option( 'szvegek_option_name' );
  $sikeres_foglals_popup_0 = $szvegek_options['sikeres_foglals_popup_0'];
  $foglals_trlse_popup_1 = $szvegek_options['foglals_trlse_popup_1'];
  $last_minute_tooltip_2 = $szvegek_options['last_minute_tooltip_2'];
  $stteds_tooltip_3 = $szvegek_options['stteds_tooltip_3'];

  wp_localize_script( 'wpvue_vuejs', 'courtResStrings', array(
    'prevdate' => __('Előző nap', 'vuecourtreservation'),
    'nextdate' => __('Következő nap', 'vuecourtreservation'),
    'today' => __('Nap', 'vuecourtreservation'),
    'currhour' => __('Jelenlegi idő', 'vuecourtreservation'),
    'reservable' => __('Foglalható', 'vuecourtreservation'),
    'reserved' => __('Foglalt', 'vuecourtreservation'),
    'selected' => __('Kiválasztva', 'vuecourtreservation'),
    'reserve' => __('Lefoglalom', 'vuecourtreservation'),
    'myreservations' => __('Foglalásaim', 'vuecourtreservation'),
    'really' => __('Biztos lefoglalja a kiválasztott időpontokat?', 'vuecourtreservation'),
    'username' => __('Neve', 'vuecourtreservation'),
    'email' => __('Email címe', 'vuecourtreservation'),
    'tel' => __('Telefonszáma', 'vuecourtreservation'),
    'msg' => __('Megjegyzés', 'vuecourtreservation'),
    'today' => __('Ma', 'vuecourtreservation'),
    'oclock' => __('órakor', 'vuecourtreservation'),
    'user_select' => __('Másik felhasználóhoz rendelem (admin mód)', 'vuecourtreservation'),
    'cancel' => __('Mégse', 'vuecourtreservation'),
    'darktime' => $stteds_tooltip_3 ? $stteds_tooltip_3 : __('Sötétedés után lámpahasználati díj kerül felszámolásra', 'vuecourtreservation'),
    'repeat' => __('Ismétlődő foglalás', 'vuecourtreservation'),
    'repnum' => __('Ismétlődések száma (hetekben)', 'vuecourtreservation'),
    'succesful_res' => $sikeres_foglals_popup_0 ? $sikeres_foglals_popup_0 : __('Sikeres foglalás! A foglalás lemondása legkésőbb 24 órával a lefoglalt időpont előtt lehetséges.', 'vuecourtreservation'),
    'res_deleted' => $foglals_trlse_popup_1 ? $foglals_trlse_popup_1 : __('Foglalása sikeresen törölve.', 'vuecourtreservation'),
    'last_minute' => $last_minute_tooltip_2 ? $last_minute_tooltip_2 : __('Last Minute foglalás. A foglalás nem lemondható.', 'vuecourtreservation'),
    'cancel_res' => __('Lemondom', 'vuecourtreservation'),
    'close' => __('Bezár', 'vuecourtreservation'),
    'money' => __('Fizetős foglalás', 'vuecourtreservation'),
    'ticket' => __('Bérletes foglalás', 'vuecourtreservation'),
    'editRes' => __('Foglalás szerkesztése', 'vuecourtreservation'),
    'elfogyottABerlet' => __('Figyelem: A foglalással az Ön bérlete kimerül, a további foglalásai költséget vonnak maguk után.', 'vuecourtreservation'),
    'vanBerlet' => __('Bérletéből fennmaradó alkalmak száma: ', 'vuecourtreservation'),
    'nincsBerlet' => __('Figyelem: A foglalás túllépi a bérletben foglalt alkalmakat, így %s alkalom fizetési kötelezettséggel jár.', 'vuecourtreservation'),
  ) );
  wp_enqueue_script('wpvue_vuejs');
}
add_action('init', 'func_load_vuescripts');

function register_the_styles_for_magical_reservation() {
  wp_register_style('reservation-styles', plugins_url('dist/css/app.css',__FILE__ ));
  wp_enqueue_style('reservation-styles');
}
add_action('init', 'register_the_styles_for_magical_reservation');

/*
** Fork REST users endpoint
*/
add_filter('rest_user_query', 'remove_has_published_posts_from_api_user_query', 10, 2);
function remove_has_published_posts_from_api_user_query($prepared_args, $request){
  unset($prepared_args['has_published_posts']);

  return $prepared_args;
}

add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );
function my_show_extra_profile_fields( $user ) {
  echo "<table class=\"form-table\">
      <tr>
        <th><label for=\"phone\">Telefonszám</label></th>
        <td>
        <input type=\"text\" name=\"phone\" id=\"phone\" value=\"" . esc_attr( get_the_author_meta( 'phone', $user->ID ) ) . "\" class=\"regular-text\" /><br />
            <span class=\"description\">Itt adhatja meg telefonszámát</span>
        </td>
      </tr>
      <tr>
        <th><label for=\"berlet\">Bérlet</label></th>
        <td>
        <input type=\"number\" name=\"berlet\" id=\"berlet\" value=\"" . esc_attr( get_the_author_meta( 'berlet', $user->ID ) ) . "\" class=\"regular-text\" /><br />
            <span class=\"description\">Itt módosíthatja a bérlet időtartamát. Minden egyes foglalás után automatikusan levonja a rendszer az alkalmakat.</span>
        </td>
      </tr>
    </table>";
}

add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );
function my_save_extra_profile_fields( $user_id ) {
  if ( !current_user_can( 'edit_user', $user_id ) )
    return false;
  update_usermeta( $user_id, 'phone', $_POST['phone'] );
  update_usermeta( $user_id, 'berlet', $_POST['berlet'] );
}

/*
** Add shortcode []
*/
add_action( 'login_form_middle', 'add_lost_password_link' );
function add_lost_password_link() {
	return '<a href="/wp-login.php?action=lostpassword">' . __('Elfelejtett jelszó?', 'vuecourtreservation') . '</a>';
}
function func_wp_vue(){
  wp_enqueue_script('wpvue_vuejs');
  wp_enqueue_script('wpvue_vuejs1');
  if(is_user_logged_in()){
    $str = "<div id='app'></div>";
  }
  else{
    $args = array(
      'echo' => false,
      'label_username' => __('Felhasználónév vagy email cím', 'vuecourtreservation'),
      'label_password' => __('Jelszó', 'vuecourtreservation'),
      'label_remember' => __('Emlékezz rám', 'vuecourtreservation'),
      'label_log_in' => __('Bejelentkezés', 'vuecourtreservation'),
    );
    $str = '<style>.reservationlogin *{color: #ce6228 !important;}</style><div class="reservationlogin"><h3>A pályafoglaló megtekintéséhez kérjük jelentkezzen be!</h3>' . wp_login_form($args) . '</div><div class="reservationregister"><h4>Még nincs fiókja?</h4><a href="' . get_permalink(get_page_by_path('regisztracio')) . '">Regisztrálok</a>';
  }
  return $str;
}
add_shortcode( 'wpvue', 'func_wp_vue' );


/*
** Variables
*/
$courts_table_name = '';
$reservation_table_name = '';


/* 
** Create DB Tables
*/
function court_reservation_plugin_activation_setup(){
  global $wpdb;
  $courts_table_name = $wpdb->prefix . 'reservable_courts';
  $reservation_table_name =  $wpdb->prefix . 'vue_court_reservations';
  $users_table_name =  $wpdb->prefix . 'users';
  
  // Create Courts table
  $courtssql = "CREATE TABLE $courts_table_name (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    type tinytext NOT NULL,
    name tinytext NOT NULL,
    text text NOT NULL,
    starttime int(2) NOT NULL,
    endtime int(2) NOT NULL,
    dark int(2) NOT NULL,
    PRIMARY KEY (id)
  );";

  try {
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $courtssql );
  } catch (Exception $e) {
    echo '<strong>Something bad happened through the database creation.<strong><br>';
    echo 'Caught exception: ',  $e->getMessage(), "\n";
  }

  // Create Reservations table
  $reservationsql = "CREATE TABLE $reservation_table_name (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    time DATETIME NOT NULL,
    court_id BIGINT(20) UNSIGNED NOT NULL,
    user_id BIGINT(20) UNSIGNED,
    res_berlet TINYINT(1) NULL,
    res_name text NULL,
    res_email text NULL,
    res_tel text NULL,
    res_last_minute TINYINT(1) NULL,
    snippet text NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (court_id) REFERENCES $courts_table_name(id),
    FOREIGN KEY (user_id) REFERENCES $users_table_name(id)
  );";

  try {
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $reservationsql );
  } catch (Exception $e) {
    echo '<strong>Something bad happened through the database creation.<strong><br>';
    echo 'Caught exception: ',  $e->getMessage(), "\n";
  }
}
register_activation_hook( __FILE__, 'court_reservation_plugin_activation_setup' );

/*
** Delete reservations on user delete
*/
add_action( 'delete_user', 'wpdocs_delete_user' );
function wpdocs_delete_user( $user_id ) {
  global $wpdb;
  $reservation_table_name =  $wpdb->prefix . 'vue_court_reservations';
  $deletedReservations = $wpdb->delete($reservation_table_name, ['user_id' => $user_id]);
}

/*
** Includes
*/
include 'func/options.php';
include 'func/text_options.php';
include 'func/email_options.php';
include 'func/send_emails.php';
include 'func/rest_endpoints.php';

?>