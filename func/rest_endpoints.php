<?php
/*
** REST AllUsers (because WP is a dumbass and cannot even get all users from the /users endpoint because of the 100 per_page limit.... jesus)
*/
add_action('rest_api_init', function () {
	register_rest_route(
		'wpse/v1',
		'/all_users/',
		[
			'methods'       => 'GET',
			'callback'      => __NAMESPACE__ . '\get_all_users'
		]
	);
});

function get_all_users($request){
	return get_users(array( 'fields' => array( 'id', 'display_name' ) ));
}


/*
** REST Court Reservation
*/
add_action('rest_api_init', function () {
	register_rest_route(
		'wpse/v1',
		'/rest_court_reservation/',
		[
			'methods'       => 'GET',
			'callback'      => __NAMESPACE__ . '\get_rest_court_reservation'
		]
	);
});

function get_rest_court_reservation($request){
  global $wpdb;
  $results = $wpdb->get_results( 
    $wpdb->prepare("SELECT * FROM {$wpdb->prefix}reservable_courts") 
  );
  if($results): foreach($results as $courtres):
    $reservedCourts = $wpdb->get_results( 
      $wpdb->prepare("SELECT {$wpdb->prefix}vue_court_reservations.id, {$wpdb->prefix}users.display_name, time, snippet, res_name, res_tel, res_email, res_berlet, res_last_minute FROM {$wpdb->prefix}vue_court_reservations, {$wpdb->prefix}users WHERE court_id = {$courtres->id} AND {$wpdb->prefix}vue_court_reservations.user_id = {$wpdb->prefix}users.id AND time LIKE '{$request->get_params()["date"]}%'") 
    );
    $items[]=[
      'id' => $courtres->id,
      'type' => $courtres->type,
      'name' => $courtres->name,
      'text' => $courtres->text,
      'starttime' => $courtres->starttime,
      'endtime' => $courtres->endtime,
      'darktime' => preg_replace('/\:[0-9]*/','',date_sunset(time(),SUNFUNCS_RET_STRING,47,19,90,1)),
      'reserved' => $reservedCourts
    ];
  endforeach; endif;

	return $items;
}

/*
** REST Court Reservation
*/
add_action('rest_api_init', function () {
	register_rest_route(
		'wpse/v1',
		'/add_rest_court_reservation/',
		[
			'methods'       => 'POST',
			'callback'      => __NAMESPACE__ . '\post_rest_court_reservation'
		]
	);
});

function post_rest_court_reservation($request){
  global $wpdb;
  $reservation_table_name =  $wpdb->prefix . 'vue_court_reservations';
  $courts_table_name =  $wpdb->prefix . 'reservable_courts';
  $sqlInsertToReservations = '';
  $uid = 1;

  if (!is_wp_error($request) ) {
    $errors = [];
    $success = [];
    $for_email = [];
    /*
    var_dump($request->get_params());
    die();
    */
    if($request->get_params()):
      foreach($request->get_params() as $param):
        $i=0;
        if($param['form_rep'] == "" || $param['form_rep'] < 1):
          $param['form_rep'] = 1;
        endif;
        if($param['form_rep']): while($i < $param['form_rep']):
          $time = new DateTime($param['time']);
          if($i > 0):
            $time->modify("+" . $i . " week");
          endif;

          $uid = $param['selecteduser'] ? $param['selecteduser'] : $param['userid'];

          $is_reserved_already = $wpdb->get_results( 
            $wpdb->prepare("SELECT id FROM {$reservation_table_name} WHERE time = \"{$time->format("Y:m:d H:i:s")}\" AND court_id = {$param['courtid']}")
          );

          $courtname = $wpdb->get_results( 
            $wpdb->prepare("SELECT name FROM {$courts_table_name} WHERE id = {$param['courtid']}")
          );

          // IF Already Reserved
          if($is_reserved_already){
            $errors[] = $time->format("Y:m:d H:i:s");
          }
          else{
            $berlet = 0;
            if(get_the_author_meta('berlet', $uid) > 0):
              update_usermeta( $uid, 'berlet', get_the_author_meta('berlet', $uid)-1 );
              $berlet = 1;
            endif;
            
            $timestamp = strtotime($param['time']);
            $lastmin = ($timestamp > (time() + 86400)) ? 0 : 1;
            
            $data = array('time' => $time->format("Y:m:d H:i:s"), 'court_id' => $param['courtid'], 'user_id' => $uid, 'snippet' => $param['form_msg'], 'res_name' => $param['form_name'], 'res_email' => $param['form_email'], 'res_tel' => $param['form_tel'], 'res_berlet' => $berlet, 'res_last_minute' => $lastmin);
            $format = array('%s','%d', '%d');
            $wpdb->insert($reservation_table_name, $data, $format);
            $success[] = $time->format("Y:m:d H:i:s");
            $for_email[] = $time->format("Y:m:d H:i:s") . ' - ' . $courtname[0]->name;
          }
          $i++;
        endwhile; endif;
      endforeach;
    endif;

    if($errors){
      return new WP_REST_Response(
        array(
          'status' => 400,
          'response' => 'Sajnáljuk, nem tudtuk az összes alkalmat lefoglalni. <br><br> Sikeres foglalások:<br> ' . implode(',<br>', $success) . ' <br><br> Sikertelen foglalások:<br>' . implode(',<br>', $errors),
          'num' => sizeof($errors),
        )
      );
    }
    
    $umail = '';
    $uname = '';
    $uphone = '';
    if($param['selecteduser']):
      $udata = get_userdata($uid);
      $umail = $udata->user_email;
      $uname = $udata->display_name;
      $uphone = $udata->phone;
    else:
      $umail = $request->get_params()[0]['form_email'];
      $uname = $request->get_params()[0]['form_name'];
      $uphone = $request->get_params()[0]['form_tel'];
    endif;

    $mailhandler = new ReservationMailHandler();
    $mailhandler->sendMail('admin-new', $umail, $uname, $uphone, $for_email, $request->get_params()[0]['form_msg']);
    $mailhandler->sendMail('user-new', $umail, $uname, $uphone, $for_email, $request->get_params()[0]['form_msg']);

    return new WP_REST_Response(
      array(
        'status' => 200,
        'response' => 'Inzertációk megtörténtek'
      )
    );
  } else {
    return new WP_Error(666, 'Error in hell');
  }
}

/*
** REST EDIT Court Reservation
*/
add_action('rest_api_init', function () {
	register_rest_route(
		'wpse/v1',
		'/edit_rest_court_reservation/',
		[
			'methods'       => 'POST',
			'callback'      => __NAMESPACE__ . '\edit_post_rest_court_reservation'
		]
	);
});

function edit_post_rest_court_reservation($request){
  global $wpdb;
  $reservation_table_name =  $wpdb->prefix . 'vue_court_reservations';
  $courts_table_name =  $wpdb->prefix . 'reservable_courts';
  $oldres;

  if (!is_wp_error($request) ) {
    $success = 0;
    $uid = 1;
    $oldres = $wpdb->get_results( 
      $wpdb->prepare("SELECT user_id, res_berlet, time, name FROM {$reservation_table_name}, {$courts_table_name} WHERE {$reservation_table_name}.id = {$request->get_params()['id']} AND court_id = {$courts_table_name}.id")
    );
    /*
    var_dump($request->get_params());
    die();
    */
    if($request->get_params()):
      $param = $request->get_params();
      $uid = $param['selecteduser'] ? $param['selecteduser'] : $param['userid'];
      $resID = $param['id'];
      
      $data = array('user_id' => $uid, 'snippet' => $param['form_msg'], 'res_name' => $param['form_name'], 'res_email' => $param['form_email'], 'res_tel' => $param['form_tel']);
      $where = array('id' => $resID);
      $updateID = $wpdb->update($reservation_table_name, $data, $where);
      if($updateID){
        $success = 1;
      }
    endif;

    if(!$success){
      return new WP_REST_Response(
        array(
          'status' => 400,
          'response' => 'Az adatok frissítése nem sikerült.',
        )
      );
    }

    $timestamp = strtotime($oldres[0]->time);
    $ouid = $oldres[0]->user_id;
    if(($timestamp > time() + 86400) && $oldres[0]->res_berlet == 1) {
      if(get_the_author_meta('berlet', $ouid) >= 0):
        update_usermeta( $ouid, 'berlet', get_the_author_meta('berlet', $ouid)+1 );
      endif;
    }

    if(get_the_author_meta('berlet', $uid) >= 0):
      update_usermeta( $uid, 'berlet', get_the_author_meta('berlet', $uid)-1 );
    endif;
    
    $umail = $request->get_params()['form_email'];
    $uname = $request->get_params()['form_name'];
    $uphone = $request->get_params()['form_tel'];
    $umsg = $request->get_params()['form_msg'];
    $rtime[] = $oldres[0]->time . ' - ' . $oldres[0]->name;
    
    $mailhandler = new ReservationMailHandler();
    $mailhandler->sendMail('admin-edit', $umail, $uname, $uphone, $rtime, $umsg);
    $mailhandler->sendMail('user-edit', $umail, $uname, $uphone, $rtime, $umsg);

    return new WP_REST_Response(
      array(
        'status' => 200,
        'response' => 'Az adatokat sikeresen frissítettük.'
      )
    );
  } else {
    return new WP_Error(666, 'Error in hell');
  }
}

/*
** REST Delete Reservation
*/
add_action('rest_api_init', function () {
	register_rest_route(
		'wpse/v1',
		'/delete_rest_court_reservation/',
		[
			'methods'       => 'POST',
			'callback'      => __NAMESPACE__ . '\delete_rest_court_reservation'
		]
	);
});

function delete_rest_court_reservation($request){
  global $wpdb;
  $reservation_table_name =  $wpdb->prefix . 'vue_court_reservations';
  $courts_table_name =  $wpdb->prefix . 'reservable_courts';
  $oldres;
  //var_dump($request->get_params());
  //die();

  if (!is_wp_error($request) ) {
    $oldres = $wpdb->get_results( 
      $wpdb->prepare("SELECT user_id, time, res_name, res_email, res_tel, res_berlet, name FROM {$reservation_table_name}, {$courts_table_name} WHERE {$reservation_table_name}.id = {$request->get_params()['id']} AND court_id = {$courts_table_name}.id")
    );
    
    if($request->get_params()):
      $data = array('id' => $request->get_params()['id']);
      $wpdb->delete($reservation_table_name, $data);
    endif;

    $timestamp = strtotime($oldres[0]->time);
    $uid = $oldres[0]->user_id;
    if(($timestamp > time() + 86400) && $oldres[0]->res_berlet == 1) {
      if(get_the_author_meta('berlet', $uid) >= 0):
        update_usermeta( $uid, 'berlet', get_the_author_meta('berlet', $uid)+1 );
      endif;
    }


    $umail = $oldres[0]->res_email;
    $uname = $oldres[0]->res_name;
    $uphone = $oldres[0]->res_tel;
    $rtime[] = $oldres[0]->time . ' - ' . $oldres[0]->name;
    
    $mailhandler = new ReservationMailHandler();
    $mailhandler->sendMail('admin-delete', $umail, $uname, $uphone, $rtime);
    $mailhandler->sendMail('user-delete', $umail, $uname, $uphone, $rtime);

    return new WP_REST_Response(
      array(
        'status' => 200,
        'response' => 'Successful deletion'
      )
    );
  } else {
    return new WP_Error(666, 'Error in hell');
  }
}

/*
** REST Get Reservation Data
*/
add_action('rest_api_init', function () {
	register_rest_route(
		'wpse/v1',
		'/get_reservation_data/',
		[
			'methods'       => 'GET',
			'callback'      => __NAMESPACE__ . '\get_reservation_data'
		]
	);
});

function get_reservation_data($request){
  global $wpdb;
  $reservation_table_name =  $wpdb->prefix . 'vue_court_reservations';

  if (!is_wp_error($request) ) {
    if($request->get_params()):
      $results = $wpdb->get_results( 
        $wpdb->prepare("SELECT * FROM {$reservation_table_name} WHERE id = {$request->get_params()['id']}") 
      );
      return $results;
    endif;
    return 0;
  } else {
    return new WP_Error(666, 'Error in hell');
  }
}


?>