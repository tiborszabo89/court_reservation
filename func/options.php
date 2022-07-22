<?php

class VueCourtReservation {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'vue_court_reservation_add_plugin_page' ) );
	}

	public function vue_court_reservation_add_plugin_page() {
		add_menu_page(
			'Teniszpályafoglaló', // page_title
			'Teniszpályafoglaló', // menu_title
			'manage_options', // capability
			'vue-court-reservation', // menu_slug
			array( $this, 'vue_court_reservation_create_admin_page' ), // function
			'dashicons-calendar', // icon_url
			2 // position
		);
	}

  /*
  ** INSERT POST-ed data into reservable_courts table
  */
  public function check_incoming_inputs() {
    if(isset($_POST['court_name'])){
      global $wpdb;
      $courts_table_name = $wpdb->prefix . 'reservable_courts';
      $data = array('type' => $_POST['court_type'], 'name' => $_POST['court_name'], 'text' => $_POST['court_info'], 'starttime' => $_POST['court_from'], 'endtime' => $_POST['court_to'], 'dark' => $_POST['court_dark']);
      $format = array('%s','%s', '%s', '%d', '%d', '%d');
      $wpdb->insert($courts_table_name, $data, $format);
      $my_id = $wpdb->insert_id;
      if($my_id){
        echo '<p><strong style="color: green;">Pálya sikeresen létrehozva :)</strong></p>';
      }
      //var_dump($_POST['court_name'], $_POST['court_type'], $_POST['court_info'], $_POST['court_from'], $_POST['court_to']);
    }
  }
  
  /*
  ** List Courts
  */
  public function list_all_courts() {
    global $wpdb;
    $results = $wpdb->get_results( 
      $wpdb->prepare("SELECT * FROM {$wpdb->prefix}reservable_courts") 
    );
    //var_dump($results);?>
    <style>
      .courts-wrap{
        display: flex;
        width: 100%;
        padding-right: 30px;
        box-sizing: border-box;
        flex-wrap: wrap;
      }
      .court{
        width: 30%;
        background-color: white;
        border: 1px solid;
        border-radius: 5px;
        padding: 10px 25px;
        box-sizing: border-box;
        margin: 1%;
      }
      .court .name{
        background-color: #b0d2ef;
        margin: 0;
        padding: 10px;
        text-align: center;
      }
    </style>
    <h2><?= __('Pályák', 'vuecourtreservation') ?></h2>
    <div class="courts-wrap">
      <?php if($results): foreach($results as $courtres): ?>
        <div class="court">
          <form action="" method="post">
            <input type="hidden" name="update_court_id" value="<?= $courtres->id ?>">
            <p class="name"><strong><input name="update_court_name" type="text" value="<?= $courtres->name ?>"></strong></p>
            <p class="type"><strong><?= __('Típus: ', 'vuecourtreservation') ?></strong><input name="update_court_type" type="text" value="<?= $courtres->type ?>" required></p>
            <p class="info"><strong><?= __('Info: ', 'vuecourtreservation') ?></strong><input name="update_court_info" type="text" value="<?= $courtres->text ?>"></p>
            <p class="time"><strong><?= __('Nyitás: ', 'vuecourtreservation') ?></strong><input name="update_court_from" type="number" min="0" max="24" value="<?= $courtres->starttime ?>" required></p>
            <p class="time"><strong><?= __('Zárás: ', 'vuecourtreservation') ?></strong><input name="update_court_to" type="number" min="0" max="24" value="<?= $courtres->endtime ?>" required></p>
            <p class="time"><strong><?= __('Sötétedés: ', 'vuecourtreservation') ?></strong><input name="update_court_dark" type="number" min="0" max="24" value="<?= $courtres->dark ?>" required></p>
            <input type="submit" value="Módosítás">
          </form>
          <form action="" method="post">
            <input type="hidden" name="delete_court_id" value="<?= $courtres->id ?>">
            <details style="margin-top:30px;">
              <summary>
                <?= __('Törlés', 'vuecourtreservation') ?>
              </summary>
              <p><?= __('Biztosan ki szeretné törölni a pályát?', 'vuecourtreservation') ?></p>
              <input type="submit" value="Törlés">
            </details>
          </form>
        </div>
      <?php endforeach; endif; ?>
    </div>
  <?php }

  /*  
  ** Update Court
  */
  public function updateCourt($cid){
    if(isset($_POST['update_court_name'])){
      global $wpdb;
      $courts_table_name = $wpdb->prefix . 'reservable_courts';
      $data = array('type' => $_POST['update_court_type'], 'name' => $_POST['update_court_name'], 'text' => $_POST['update_court_info'], 'starttime' => $_POST['update_court_from'], 'endtime' => $_POST['update_court_to'], 'dark' => $_POST['update_court_dark']);
      $format = array('%s','%s', '%s', '%d', '%d', '%d');
      $where = [ 'id' => $cid ];
      $where_format = [ '%d' ];
      $updated = $wpdb->update( $courts_table_name, $data, $where, $format, $where_format );
      if($updated){
        echo '<p><strong style="color: green;">Pálya sikeresen módosítva :)</strong></p>';
      }
    }
  }

  /*  
  ** Delete Court
  */
  public function deleteCourt($cid){
    global $wpdb;
    $courts_table_name = $wpdb->prefix . 'reservable_courts';
    $deleted = $wpdb->delete($courts_table_name, ['id' => $cid], ['%d']);
    if($deleted){
      echo '<p><strong style="color: red;">Pálya sikeresen törölve :)</strong></p>';
    }
  }

	public function vue_court_reservation_create_admin_page() {
    /*
    ** Run functions in correct order
    ** $_POST create data -> $_POST update data -> $_POST delete data -> List Forms
    */
    $this->check_incoming_inputs();
    if(isset($_POST['update_court_id'])){
      $this->updateCourt($_POST['update_court_id']);
    }
    if(isset($_POST['delete_court_id'])){
      $this->deleteCourt($_POST['delete_court_id']);
    }
    $this->list_all_courts();
    ?>

    <h2><?php echo __('Új pálya hozzáadása', 'vuecourtreservation') ?></h2>
    <?php settings_errors(); ?>
    <div class="court">
      <form action="" method="post">
        <p class="name"><strong><input name="court_name" type="text" required></strong></p>
        <p class="type"><strong><?= __('Típus: ', 'vuecourtreservation') ?></strong><input name="court_type" type="text" required></p>
        <p class="info"><strong><?= __('Info: ', 'vuecourtreservation') ?></strong><input name="court_info" type="text"></p>
        <p class="time"><strong><?= __('Nyitás: ', 'vuecourtreservation') ?></strong><input name="court_from" type="number" min="0" max="24" required></p>
        <p class="time"><strong><?= __('Zárás: ', 'vuecourtreservation') ?></strong><input name="court_to" type="number" min="0" max="24" required></p>
        <p class="time"><strong><?= __('Sötétedés: ', 'vuecourtreservation') ?></strong><input name="court_dark" type="number" min="0" max="24" required></p>
        <input type="submit" value="Új pálya hozzáadása">
      </form>
    </div>
    
    <h2><?= __('Popup szövegek', 'vuecourtreservation'); ?></h2>
    <form action="" method="post">
      <p class="type"><strong><?= __('Sikeres foglalás: ', 'vuecourtreservation') ?></strong><input name="update_court_type" type="text" value="<?= $courtres->type ?>" required></p>
      <input type="submit" value="Módosítás">
    </form>
<?php }
}

if ( is_admin() )
	$vue_court_reservation = new VueCourtReservation();