<?php
class ReservationMailHandler {
  private $ezamugymirevalo;

  public function __construct(){
    $this->$ezamugymirevalo = '';
  }

  public function sendMail($type, $usermail = '', $username = '', $userphone = '', $reservations = '', $sidenote = ''){
    $email_belltsok_options = get_option( 'email_belltsok_option_name' );
    $to = '';
    $cc = '';
    $body = '';
    $subject = '';

    switch ($type):
      case 'admin-new':
        $to = $email_belltsok_options['cmzettek_0'];
        $cc = $email_belltsok_options['msolatot_kapnak_1'];
        $body = $email_belltsok_options['admin_j_foglals_zenet_2'];
        $subject = 'Liget Teniszpálya - Új foglalás';
        break;
      case 'admin-delete':
        $to = $email_belltsok_options['cmzettek_0'];
        $cc = $email_belltsok_options['msolatot_kapnak_1'];
        $body = $email_belltsok_options['admin_foglals_trlve_zenet_4'];
        $subject = 'Liget Teniszpálya - Törölt foglalás';
        break;
      case 'admin-edit':
        $to = $email_belltsok_options['cmzettek_0'];
        $cc = $email_belltsok_options['msolatot_kapnak_1'];
        $body = $email_belltsok_options['admin_foglals_szerkesztve_zenet_3'];
        $subject = 'Liget Teniszpálya - Foglalás szerkesztve';
        break;
      case 'user-new':
        $to = $usermail;
        $body = $email_belltsok_options['user_j_foglals_zenet_5'];
        $subject = 'Liget Teniszpálya - Új foglalás';
        break;
      case 'user-delete':
        $to = $usermail;
        $body = $email_belltsok_options['user_foglals_trlve_zenet_7'];
        $subject = 'Liget Teniszpálya - Törölt foglalás';
        break;
      case 'user-edit':
        $to = $usermail;
        $body = $email_belltsok_options['user_foglals_szerkesztve_zenet_6'];
        $subject = 'Liget Teniszpálya - Foglalás szerkesztve';
        break;
      default:
        $to = $email_belltsok_options['cmzettek_0'];
        $cc = $email_belltsok_options['msolatot_kapnak_1'];
        $body = $email_belltsok_options['admin_j_foglals_zenet_2'];
        $subject = 'Liget Teniszpálya - Új foglalás';
    endswitch;

    $body = preg_replace('/\n/', '</br>' , $body);
    $body = str_replace('{foglalas_alkalmak}', implode(',</br>', $reservations) , $body);
    $body = str_replace('{foglalas_nev}', $username , $body);
    $body = str_replace('{foglalas_email}', $usermail , $body);
    $body = str_replace('{foglalas_telefon}', $userphone , $body);
    $body = str_replace('{foglalas_megjegyzes}', $sidenote , $body);
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    wp_mail( $to, $subject, $body, $headers );
  }
}