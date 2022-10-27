<?php
/**
* Plugin Name: Resales Property
* Plugin URI: https://www.zestgeek.com/
* Description: This plugin is used for sync the property and display the property.
* Version: 0.2
* Author: Wp Devloper
* Author URI: https://www.zestgeek.com/
**/

define('WP_RESALES_MARBELLA_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('WP_RESALES_MARBELLA_PLUGIN_PATH', plugin_dir_path( __FILE__ ));

add_filter( 'theme_page_templates', 'pt_add_page_template_to_dropdown' );

function pt_add_page_template_to_dropdown( $templates )
{
   $templates[WP_RESALES_MARBELLA_PLUGIN_PATH . 'templates/property-tpl.php'] = __( 'Single Property', 'single-property' );

   return $templates;


}

add_filter( 'template_include', 'pt_change_page_template', 99 );

function pt_change_page_template($template){
    if (is_page()) {
        $meta = get_post_meta(get_the_ID());
        $plugion_tamplate = WP_RESALES_MARBELLA_PLUGIN_PATH . 'templates/property-tpl.php';
        if (!empty($meta['_wp_page_template'][0]) && $meta['_wp_page_template'][0] == $plugion_tamplate) {
            $template = $meta['_wp_page_template'][0];
        }
    }
    return $template;
}

register_deactivation_hook( __FILE__, 'wp_resales_marbella_table_drop');

function wp_resales_marbella_table_drop() {
  global $wpdb;
  $property_table = $wpdb->prefix.'property';
  if($wpdb->get_var("SHOW TABLES LIKE '$property_table'") == $property_table) {
    $sql = "DROP TABLE IF EXISTS $property_table";
    $wpdb->query($sql);
  }
}

register_activation_hook( __FILE__, 'wp_resales_marbella_table' );

function wp_resales_marbella_table() { 
  global $wpdb;
  $property_table = $wpdb->prefix.'property';

  if($wpdb->get_var("SHOW TABLES LIKE '$property_table'") != $property_table) {
    $sql = "CREATE TABLE if not exists $property_table (
      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      reference varchar(225) NOT NULL,
      slug varchar(225) NOT NULL UNIQUE,
      lang varchar(255) DEFAULT NULL,
      listing int DEFAULT NULL,
      agency_ref varchar(225) DEFAULT NULL,
      country varchar(225) DEFAULT NULL,
      province varchar(225) DEFAULT NULL,
      area varchar(225) DEFAULT NULL,
      location varchar(225) DEFAULT NULL,
      sub_location varchar(225) DEFAULT NULL,
      property_type JSON DEFAULT NULL,
      status JSON DEFAULT NULL,
      bedrooms int(11) DEFAULT NULL,
      bathrooms int(11) DEFAULT NULL,
      currency varchar(225) DEFAULT NULL,
      price decimal(11,4) DEFAULT NULL,
      original_price decimal(11,4) DEFAULT NULL,
      rental_period varchar(225) DEFAULT NULL,
      rental_price1 decimal(11,4) DEFAULT NULL,
      rental_price2 decimal(11,4) DEFAULT NULL,
      dimensions varchar(225) DEFAULT NULL,
      built int(11) DEFAULT NULL,
      terrace int(11) DEFAULT NULL,
      garden_plot int(11) DEFAULT NULL,
      co2_rated int(11) DEFAULT NULL,
      energy_rated int(11) DEFAULT NULL,
      own_property int(11) DEFAULT NULL,
      pool int(11) DEFAULT NULL,
      parking int(11) DEFAULT NULL,
      garden int(11) DEFAULT NULL,
      description longtext DEFAULT NULL,
      property_features JSON DEFAULT NULL,
      pictures JSON DEFAULT NULL,
      created_date varchar(225) DEFAULT NULL,
      updated_date varchar(225) DEFAULT NULL,
      PRIMARY KEY  (id)
    );"; 
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
  }

  $listing_table = $wpdb->prefix.'listing';

  if($wpdb->get_var("SHOW TABLES LIKE '$listing_table'") != $listing_table) {
    $sql = "CREATE TABLE if not exists $listing_table (
      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      title varchar(225) NOT NULL,
      created_date varchar(225) DEFAULT NULL,
      updated_date varchar(225) DEFAULT NULL,
      PRIMARY KEY  (id)
    );"; 
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
      $listinglists = ['sale','short term rental','long term rental','featured'];
      foreach($listinglists as $listinglist){
      $result = $wpdb->insert(
                $listing_table,
                array('title'=>$listinglist,
                'created_date'=>date('Y-m-d'),
                'updated_date'=>''),
            );
    }
  }
}


add_action( 'admin_menu', 'wp_add_api_setting_menu' );
function wp_add_api_setting_menu() {
  add_menu_page('API Setting', 'API Setting', 'manage_options', 'api', 'wp_add_api_page_html');
}
function wp_add_api_page_html() {
  global $wpdb;
  if(isset($_POST['save_api'])){
    $api_key = $_POST['api_key'];
     update_option('api_key',$api_key);
  }
  ?>
  <form method="post">
    <div>
      <label><?=__('API Key');?></label>
      <input type="text" name="api_key" value="<?php echo get_option('api_key'); ?>">
      <button type="submit" name="save_api"><?=_e('Save','wp-resale');?></button>
    </div>
  </form>
  <?php
}


add_action("wp_ajax_get_search_result", "get_search_result");
add_action("wp_ajax_nopriv_get_search_result", "get_search_result");
add_action("wp_ajax_get_search_id", "get_search_id");
add_action("wp_ajax_nopriv_get_search_id", "get_search_id");
add_action("wp_ajax_get_search_count", "get_search_count");
add_action("wp_ajax_nopriv_get_search_count", "get_search_count");
function get_search_id(){
  global $wpdb;
  $location = load_plugin_textdomain( 'wp-resale', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
  $my_current_lang = $_POST['lang'];
  $url_current_lang = $_POST['url_lang'];
  $property_table = $wpdb->prefix.'property';
  $reference = $_POST['reference'];
  $sql = "SELECT id,slug FROM $property_table where `reference`='$reference'";
  $id = $wpdb->get_results( $sql );
  if(empty($id)){
    $result_data['type'] = 'error';
  }else{
  ob_start();
  ?>
  <a href="<?= site_url().($url_current_lang?'/'.$url_current_lang:'');?>/single-property?slug=<?=$id[0]->slug;?>"><button id="button-view" type="button"><?=_e('View','wp-resale')?></button></a>
  <?php
  $output = ob_get_contents();
  $result_data['data'] = $output;
  $result_data['type']='success';
  }
  ob_end_clean();
  echo json_encode($result_data);
  wp_die();
}
function get_search_count(){
  global $wpdb;
  $my_current_lang = $_POST['lang'];
  $url_current_lang = $_POST['url_lang'];
  $property_table = $wpdb->prefix.'property';
  $listing_table = $wpdb->prefix.'listing';
  $listing = $_POST['listing']?$_POST['listing']:null;
  $location = $_POST['location']?"'".implode("', '",$_POST['location'])."'":null;
  $property_type = $_POST['property_type']?"'".implode("', '",$_POST['property_type'])."'":null;
  $bedroom = $_POST['bedroom']?:null;
  $min_price = $_POST['min_price']?:null;
  $max_price = $_POST['max_price']?:null;
  $advance_searches = $_POST['advance_search']?:null;
  $sql = "SELECT count(id) as total FROM $property_table where 1";
  $sql .= " AND lang = '$my_current_lang'";
  $sql .= $listing?" AND listing = $listing":"";
  $sql .= $location?" AND location in (".$location.")":"";
  $sql .= $property_type?" AND JSON_EXTRACT(property_type, '$.NameType') in (".$property_type.")":"";
  $sql .= $bedroom?" AND bedrooms = '$bedroom'":"";
  $sql .= (($listing==2||$listing==3)? (!$min_price?"":" AND rental_price1 > '$min_price'") : ($min_price?" AND price > '$min_price'":""));
  $sql .= (($listing==2||$listing==3)? (!$max_price?"":" AND rental_price2 < '$max_price'") : ($max_price?" AND price < '$max_price'":""));
  foreach($advance_searches as $advance_search){
    $sql .= $advance_search?" AND property_features Like '%".$advance_search."%'":"";
  }
  // echo $sql;die;
  $zero = 0;
  $total = $wpdb->get_results( $sql );
  $data = array();
  $data['total'] =  $total[0]->total?'('.$total[0]->total.')':'('.$zero.')';
  $data['type'] = 'success';
  echo json_encode($data);
  wp_die();
}
function get_search_result(){
  $location = load_plugin_textdomain( 'wp-resale', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
  global $wpdb;
  $property_table = $wpdb->prefix.'property';
  ob_start();
  ?>
  
    <?php 
    $my_current_lang = $_POST['lang'];
    $url_current_lang = $_POST['url_lang'];
    $page_size = $_POST['page_size']?:null;
    $listing = $_POST['listing']?$_POST['listing']:null;
    $location = !empty($_POST['location'])?"'".implode("', '",$_POST['location'])."'":null;
    // echo $location;die;
    $property_type = $_POST['property_type']?"'".implode("', '",$_POST['property_type'])."'":null;
    $bedroom = $_POST['bedroom']?:null;
    $min_price = $_POST['min_price']?:null;
    $max_price = $_POST['max_price']?:null;
    $sort = $_POST['sort']?:null;
    $advance_searches = $_POST['advance_search']?:null;
    $sql = "SELECT * FROM $property_table where 1";
    $sql .= " AND lang = '$my_current_lang'";
    $sql .= $listing?" AND listing = $listing":"";
    $sql .= $location?" AND location in (".$location.")":"";
    $sql .= $property_type?" AND JSON_EXTRACT(property_type, '$.NameType') in (".$property_type.")":"";
    $sql .= $bedroom?" AND bedrooms = '$bedroom'":"";
    $sql .= (($listing==2||$listing==3)? (!$min_price?"":" AND rental_price1 > '$min_price'") : ($min_price?" AND price > '$min_price'":""));
    $sql .= (($listing==2||$listing==3)? (!$max_price?"":" AND rental_price2 < '$max_price'") : ($max_price?" AND price < '$max_price'":""));
    foreach($advance_searches as $advance_search){
      $sql .= $advance_search?" AND property_features Like '%".$advance_search."%'":"";
    }
    $sql .= $sort?" ORDER BY ".$sort:"";
    if(isset($_POST['pagination'])){
      $sql .= " limit ".(($_POST['pagination']-1)*$page_size).", ".$page_size;
    }else{
      $sql .= " limit ".$page_size;
    }
    // echo $sql;
    $datas = $wpdb->get_results( $sql );
    
    if(empty($datas)){
      ?><p><?=_e('No data found','wp-resale');?></p><?php
    }else{
      foreach($datas as $data){
        $pic = json_decode($data->pictures);
        $property_type = json_decode($data->property_type);
          ?>
            <div class="realestate_img_container ">
               <div class="realestate_box">
                  <div class="realestate_single_image">
                     <a href="<?= site_url().($url_current_lang?'/'.$url_current_lang:'');?>/single-property?slug=<?=$data->slug;?>" 
                        class="single_image-wrapper ">
                     <img src="<?=$pic->Picture[0]->PictureURL?$pic->Picture[0]->PictureURL: WP_RESALES_MARBELLA_PLUGIN_URL.'/images/realestate.jpg';?>"
                        class="simgle_image"
                        alt="<?=_e('Image','wp-resale');?>" loading="lazy" title=""
                        ></a>
                  </div>
                  <div class="realestate-code">
                      <p>
                        <?=$data->reference?:''?>
                     </p>
                  </div>
                  <div class="realestate-price">
                     <h5 ><b><?php if($data->listing==1||$data->listing==4){ echo _e('For Sale','wp-resale'); }elseif($data->listing==2){ echo _e('Holiday Rentals','wp-resale'); }elseif($data->listing==3){ echo _e('Long Term Rental','wp-resale'); } ?>
                     <br>€<?php if($data->price!=0) { echo round($data->price); }elseif($data->original_price!=0) { echo round($data->original_price); }else{ echo round($data->rental_price1).' - €'.round($data->rental_price2); } ?>
                     </b>
                     <span class="week"> <?php if(!empty($data->rental_period)) { echo _e('per').' '.$data->rental_period; } ?></span></h5>
                  </div>
                  <div class="realestate-info">
                     <div class="realestate-content">
                       <i class="fa fa-home font-icons-estate" aria-hidden="true"></i>
                        <span>
                        <?=$data->built?:''?>
                        </span>
                        <span style="font-size: 75%;"><?=__('m2')?>
                        </span>
                     </div>
                     <div class="realestate-content">
                       <i class="fa fa-bed font-icons-estate" aria-hidden="true"></i>
                        <span>
                        <?=$data->bedrooms?:0?>
                        </span>
                     </div>
                     <div class="realestate-content">
                  <i class="fa fa-bath font-icons-estate" aria-hidden="true"></i>
                        <span>
                        <?=$data->bathrooms?:0?>
                        </span>
                     </div>
                  </div>
               </div>
               <div class="simgle_image_name">
                  <h4 style="text-align: center;"><a href="<?= site_url().($url_current_lang?'/'.$url_current_lang:'');?>/single-property?slug=<?=$data->slug;?>" class="single_image_heading"><?=__($property_type->NameType)?:''?> <?=_e('in','wp-resale')?> <?=__($data->location)?:''?></a></h4>
               </div>
            </div>
            <?php 
          }
        }
  $output = ob_get_contents();
  $result_data['grid'] = $output;
  ob_end_clean();
  ob_start();
    if(empty($datas)){
      ?><p><?=_e('No data found','wp-resale')?></p><?php
    }else{
      foreach($datas as $data){
        $pic = json_decode($data->pictures);
        $property_type = json_decode($data->property_type);
          ?>
            <div class="realestate_description-list">
               <div class="realestate_img_container-list ">
                  <div class="realestate_box">
                     <div class="realestate_single_image">
                        <a href="<?= site_url().($url_current_lang?'/'.$url_current_lang:'');?>/single-property?slug=<?=$data->slug;?>"
                           class="single_image-wrapper ">
                        <img src="<?=$pic->Picture[0]->PictureURL?$pic->Picture[0]->PictureURL: WP_RESALES_MARBELLA_PLUGIN_URL.'/images/realestate.jpg';?>"
                           class="simgle_image_list"
                           alt="<?=__('Image')?>" loading="lazy" title=""
                           ></a>
                     </div>
                     <div class="realestate-code-list">
                        <p>
                           <?=$data->reference?:''?>
                        </p>
                     </div>
                     <div class="realestate-price">
                        <h5 ><b><?php if($data->listing==1||$data->listing==4){ echo _e('For Sale','wp-resale'); }elseif($data->listing==2){ echo _e('Holiday Rentals','wp-resale'); }elseif($data->listing==3){ echo _e('Long Term Rentals','wp-resale'); } ?>
                     <br>€<?php if($data->price!=0) { echo round($data->price); }elseif($data->original_price!=0) { echo round($data->original_price); }else{ echo round($data->rental_price1).' - €'.round($data->rental_price2); } ?>
                     </b><span class="week"> <?php if(!empty($data->rental_period)) { echo _e('per ','wp-resale').' '.$data->rental_period; } ?></span></h5>
                     </div>
                  </div>
               </div>
               <div class="realestate-descrip">
                  <h4 style="margin:0px;"><?=$property_type->NameType?:''?></h4>
                  <p><?=_e('Location','wp-resale');?>: <b><?=$data->location?:''?></b></p>
                  <div class="realestate_des">
                     <div class="realestate-content-list">
                             <i class="fa fa-bed font-icons-estate list-icon-pad" aria-hidden="true"></i>
                        <span class="bed-text">
                        <?=_e('Beds','wp-resale');?>:<b><?=$data->bedrooms?:0?></b> 
                        </span>
                     </div>
                     <div class="realestate-content-list">
                              <i class="fa fa-bath font-icons-estate  list-icon-pad" aria-hidden="true"></i>
                        <span>
                        <span class="bed-text">
                        <?=_e('Bath','wp-resale');?>:<b><?=$data->bathrooms?:0?></b> 
                        </span>
                     </div>
                     <div class="realestate-content-list">
                   <i class="fa fa-home font-icons-estate  list-icon-pad" aria-hidden="true"></i>
                        <span class="bed-text">
                        <?=_e('Build','wp-resale');?>:
                        <b><?=$data->built?:''?>m<sup>2</sup></b>
                        </span>
                     </div>
                  </div>
                  <div class="realestate-para">
                     <p class="realestate-location"><?=__($data->description)?:''?>
                     </p>
                  </div>
                  <div class="read-more">
                    <a href="<?= site_url().($url_current_lang?'/'.$url_current_lang:'');?>/single-property?slug=<?=__($data->slug);?>"
                           class="single_image-wrapper ">
                     <button type="button" class="readmore-btn">
                     <?=_e('Read More','wp-resale');?>
                     <i class="fa fa-angle-right angle-right" aria-hidden="true"></i>
                     </button>
                   </a>
                  </div>
               </div>
            </div>
            <?php 
          }
        }
          ?>
       </div>
  <?php
  $output = ob_get_contents();
  $result_data['list'] = $output;
  ob_end_clean();
  ob_start();
      $listing = $_POST['listing']?$_POST['listing']:null;
      $location = $_POST['location']?"'".implode("', '",$_POST['location'])."'":null;
      $property_type = $_POST['property_type']?"'".implode("', '",$_POST['property_type'])."'":null;
      $bedroom = $_POST['bedroom']?:null;
      $min_price = $_POST['min_price']?:null;
      $max_price = $_POST['max_price']?:null;
      $advance_searches = $_POST['advance_search']?:null;
      $sql = "SELECT count(id) as total FROM $property_table where 1";
      $sql .= " AND lang = '$my_current_lang'";
      $sql .= $listing?" AND listing = $listing":"";
      $sql .= $location?" AND location in (".$location.")":"";
      $sql .= $property_type?" AND JSON_EXTRACT(property_type, '$.NameType') in (".$property_type.")":"";
      $sql .= $bedroom?" AND bedrooms = '$bedroom'":"";
      $sql .= (($listing==2||$listing==3)? (!$min_price?"":" AND rental_price1 > '$min_price'") : ($min_price?" AND price > '$min_price'":""));
      $sql .= (($listing==2||$listing==3)? (!$max_price?"":" AND rental_price2 < '$max_price'") : ($max_price?" AND price < '$max_price'":""));
      foreach($advance_searches as $advance_search){
        $sql .= $advance_search?" AND property_features Like '%".$advance_search."%'":"";
      }
  $total = $wpdb->get_results( $sql );
  $total = $total[0]->total;
  $data_per_page = 12;
  $total_page = ceil($total/$data_per_page);
  ?>
  <div class="pagination-drop" style="width: 100%;">
      <i class="fa fa-angle-double-left angle-double-left" style="opacity: 0; position: relative; z-index: -1;"></i>
      <i class="fa fa-angle-left angle-left" aria-hidden="true" style="opacity: 0; position: relative; z-index: -1;"></i>
      <select name="pagination" class="pagination-1 page">
        <?php 
        for ($i=1; $i <= $total_page; $i++) {
        ?>
          <option value="<?=$i;?>"><?=_e('Page','wp-resale')?> <?=$i;?></option>
      <?php 
        } 
      ?>
        </select>
      <?php 
      if($total_page!=1){
      ?>
      <i class="fa fa-angle-right angle-right" aria-hidden="true" ></i>
      <i class="fa fa-angle-double-right angle-double-right" ></i>
      <?php 
      }else{
        ?>
        <i class="fa fa-angle-right angle-right" aria-hidden="true" style="opacity: 0; position: relative; z-index: -1;"></i>
        <i class="fa fa-angle-double-right angle-double-right" style="opacity: 0; position: relative; z-index: -1;"></i>
      <?php
      }
      ?>
  </div>
  <?php
  $output1 = ob_get_contents();
  $result_data['pagination'] = $output1;
  ob_end_clean();
  $result_data['data'] = $output;
  $result_data['total_data'] = $total;
  $result_data['data_per_page'] = $data_per_page;
  $result_data['total_page'] = $output;
  $result_data['not']='done';
  $result_data['type']='success';
  echo json_encode($result_data);
  wp_die();
}

add_action( 'wp_enqueue_scripts', 'my_script_enqueuer' );
function my_script_enqueuer() {
   wp_register_script( "my_script", WP_RESALES_MARBELLA_PLUGIN_URL.'js/my.js', array('jquery') );
   wp_localize_script( 'my_script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        

   wp_enqueue_script( 'jquery' );
   wp_enqueue_script( 'my_script' );

}
if ( ! wp_next_scheduled( 'wp_resales_marbella_insert_property' ) ) { 
  wp_schedule_event( time(), 'daily', 'wp_resales_marbella_insert_property' ); 
}
add_action( 'wp_resales_marbella_insert_property', 'wp_resales_marbella_insert_property_fun' );

function wp_resales_marbella_insert_property_fun() {
  global $wpdb;
  // $i=1;
  for ($k=1; $k <=2 ; $k++) {
    for ($j=1; $j <= 3; $j++) {
      $var = 1;
      for ($i=1; $i <= $var; $i++) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://webapi.resales-online.com/V6/SearchProperties?p_agency_filterid='.$j.'&p1=1021801&p2='.get_option("api_key").'&P_sandbox=true&P_Lang='.$k.'&P_PageSize=80&P_PageNo='.$i,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        $res = json_decode($response,true);
        // echo '<pre>';print_r($res);die;
        $total_records = $res['QueryInfo']['PropertyCount'];
        $perpage = $res['QueryInfo']['PropertiesPerPage'];
        $var = ceil($total_records/$perpage);
        curl_close($curl);
        $table_property = $wpdb->prefix.'property';
        foreach($res['Property'] as $single){
          $slug = str_replace(" ","-",strtolower($single['PropertyType']['NameType'].' in '.$single['Location']))."-".time()."-".uniqid();
          $lang = $k==1?'en':'es';
          $res_data = array(
                'reference' => __($single['Reference']),
                'slug' => __($slug),
                'lang' => $lang,
                'listing' => $j,
                'agency_ref' => __($single['AgencyRef']),
                'country' => __($single['Country']),
                'province' => __($single['Province']),
                'area' => __($single['Area']),
                'location' => __($single['Location']),
                'sub_location' => __($single['SubLocation']),
                'property_type' => __(json_encode($single['PropertyType'])),
                'status' => __(json_encode($single['Status'])),
                'bedrooms' => __($single['Bedrooms']),
                'bathrooms' => __($single['Bathrooms']),
                'currency' => __($single['Currency']),
                'price' => $single['Price']?:0,
                'original_price' => $single['OriginalPrice']?:0,
                'rental_period' => $single['RentalPeriod']?:'',
                'rental_price1' => $single['RentalPrice1']?:0,
                'rental_price2' => $single['RentalPrice2']?:0,
                'dimensions' => __($single['Dimensions']),
                'built' => __($single['Built']),
                'terrace' => __($single['Terrace']),
                'garden_plot' => $single['GardenPlot'],
                'co2_rated' => intval($single['CO2Rated']),
                'energy_rated' => intval($single['EnergyRated']),
                'own_property' => intval($single['OwnProperty']),
                'pool' => intval($single['Pool']),
                'parking' => intval($single['Parking']),
                'garden' => intval($single['Garden']),
                'description' => $single['Description'],
                'property_features' => __(json_encode($single['PropertyFeatures'])),
                'pictures' => __(json_encode($single['Pictures']))
                  );
          $refrence=$single['Reference'];
          $data = $wpdb->get_results( "SELECT * FROM $table_property WHERE reference ='$refrence' AND lang = '$lang'");
          if(empty($data)){
            $res_data['created_date']=date('Y-m-d');
            $result = $wpdb->insert(
                  $table_property,
                  $res_data
              );
          }
          else{
            $res_data['updated_date']=date('Y-m-d');
            // print_r($res_data);die;
            $result = $wpdb->update(
                  $table_property,
                  $res_data,
                  array('reference' =>$refrence,'lang' => $lang,)
              );
          }
        }
      }
    }
  }
}

add_shortcode('resales_results','all_resale_data_get');
function all_resale_data_get($atts){
  $my_current_lang = !empty(apply_filters( 'wpml_current_language', NULL ))?apply_filters( 'wpml_current_language', NULL ):'en';
  $url_current_lang = !empty(apply_filters( 'wpml_current_language', NULL ))?apply_filters( 'wpml_current_language', NULL ):'';
  $location = load_plugin_textdomain( 'wp-resale', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
  if(isset($_GET['search'])){
    $list=$_GET['listing'];
    $loc=$_GET['location'];
    if(!is_array($loc)){
      $loc=explode(",", $loc);
    }    $prop_type=$_GET['property_type'];
    if(!is_array($prop_type)){
      $prop_type=explode(",", $prop_type);
    }
    $bed=$_GET['bedroom'];
    $min=$_GET['min_price'];
    $max=$_GET['max_price'];
  }
  $newdev = isset($atts['newdev'])?$atts['newdev']:'only';
  $price_min = isset($atts['price_min'])?$atts['price_min']:1000;
  $price_max = isset($atts['price_max'])?$atts['price_max']:1000000;
  $price_step = isset($atts['price_step'])?$atts['price_step']:10000;
  $prop_box = isset($atts['prop_box'])?$atts['prop_box']:0;
  $loc_box = isset($atts['loc_box'])?$atts['loc_box']:0;
  $wiipagesize = isset($atts['wiipagesize'])?$atts['wiipagesize']:12;

  global $wpdb;
  ob_start();
  $property_table = $wpdb->prefix.'property';
  $listing_table = $wpdb->prefix.'listing';
  function advance_search(){
    $my_current_lang = !empty(apply_filters( 'wpml_current_language', NULL ))?apply_filters( 'wpml_current_language', NULL ):'en';
    $url_current_lang = !empty(apply_filters( 'wpml_current_language', NULL ))?apply_filters( 'wpml_current_language', NULL ):'';
    $location = load_plugin_textdomain( 'wp-resale', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
    global $wpdb;
    $property_table = $wpdb->prefix.'property';
    $result = '';
    $sql = "SELECT property_features FROM $property_table WHERE lang = '$my_current_lang'";
    $property_features = $wpdb->get_results( $sql );
    $dropdown = [];
    $parking = [];
    foreach($property_features as $property_feature){
      $property_feature = json_decode($property_feature->property_features,true);
      foreach($property_feature['Category'] as $datas){
        $dropdown[$datas['Type']]=$datas['Value'];
      }
    }
    foreach($dropdown as $key=>$values){
      $result .= '<div class="advanced-search-drop dropdown-sin-1">
        <select name="'.$key.'" class="advanced-listing" placeholder="'.$key.'">
        <option value="">'.$key.'</option>';
          foreach($values as $value){
            $result .= '<option value="'.$value.'">'.strtolower($value).'</option>';
          }
        $result .= '</select>
      </div>';
    }
    return $result;
  }
  ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
<!-- jQuery Modal -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
<style>
      @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');
   </style>
<link rel="stylesheet" href="<?= WP_RESALES_MARBELLA_PLUGIN_URL.'css/style.css'?>" />
<link href="https://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="<?= WP_RESALES_MARBELLA_PLUGIN_URL.'css/jquery.dropdown.min.css';?>">
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?= WP_RESALES_MARBELLA_PLUGIN_URL.'css/jquery.dropdown.css';?>">
<script src="<?= WP_RESALES_MARBELLA_PLUGIN_URL.'js/jquery.dropdown.js';?>"></script>
<script>
  jQuery(document).ready(function () {
    jQuery('.list-view-button').click(function () {
      if (jQuery(this).hasClass('grid-view-button')) {
        jQuery('.grid-view-filter').css('display', 'flex')
        jQuery('.list-view-filter').css('display', 'none')
      }
      else{
        jQuery('.list-view-filter').css('display', 'block')
        jQuery('.grid-view-filter').css('display', 'none')
      };
    });
  });
</script>
  <section class="realestate">
      <div class="section-search">
        <form>
          <div class="refresh-section">
              <a href="<?= site_url().($url_current_lang?'/'.$url_current_lang:'');?>/list-property"><button type="button">
               <i class='fa fa-repeat'></i>
              </button></a>
              <a href="#ex1" rel="modal:open">
                 <i class="fa fa-share-alt share-icon"></i>
              </a>
          </div>
              <div class="real-estate-search">
                  <div class="estate-drop">
                <div class="dropdown-sin-1 listing-1">
                  <select name="listing" id="listing-1" placeholder="<?=_e('Listing','wp-resale');?>">
                    <option <?php if (isset($list)){ if(1==$list){ echo 'selected'; } } ?> value="1"><?=_e('Sales','wp-resale');?></option>
                    <option <?php if (isset($list)){ if(2==$list){ echo 'selected'; } } ?> value="2"><?=_e('Short Term Rental','wp-resale');?></option>
                    <option <?php if (isset($list)){ if(3==$list){ echo 'selected'; } } ?> value="3"><?=_e('Long Term Rental','wp-resale');?></option>
                    <option <?php if (isset($list)){ if(4==$list){ echo 'selected'; } } ?> value="4"><?=_e('Featured','wp-resale');?></option>    
                  </select>
                </div>
                    </div>
                    <?php 
                    if(!empty($loc_box)){
                    ?>
                    <div class="estate-drop">
                  <div class="dropdown-mul-1 location-1">
                    <select name="location[]" id="location-1" multiple placeholder="<?=_e('Location','wp-resale');?>"> 
                      <?php 
                  $sql = "SELECT DISTINCT(location) FROM $property_table WHERE lang = '$my_current_lang'";
                  $locations = $wpdb->get_results( $sql );
                  foreach($locations as $location){
                  ?>
                    <option <?php if (isset($loc) && !empty($loc)){ if(in_array($location->location,$loc)){ echo 'selected'; } } ?> value="<?=$location->location?>"><?=strtolower($location->location)?></option>
                  <?php 
                  }
                  ?>
                    </select>
                  </div>
                    </div>
                    <?php 
                  }
                  if(!empty($prop_box)){
                    ?>
                    <div class="estate-drop">
                  <div class="dropdown-mul-1 property_type-1">
                    <select name="property_type[]" id="property_type-1" multiple placeholder="<?=_e('Property Type','wp-resale');?>"> 
                      <?php 
                  $sql = "SELECT property_type FROM $property_table WHERE lang = '$my_current_lang'";
                  $property_types = $wpdb->get_results( $sql );
                  $unique_property = [];
                  foreach($property_types as $property_type){
                    $property_type = json_decode($property_type->property_type);
                    if(in_array($property_type->NameType, $unique_property)){
                      continue;
                    }else{
                      $unique_property[] = $property_type->NameType;
                    }
                  ?>
                  <option <?php if (isset($prop_type)){ if(in_array($property_type->NameType,$prop_type)){ echo 'selected'; } } ?> value="<?=$property_type->NameType?>"><?=strtolower($property_type->NameType)?></option>
                  <?php 
                  }
                  ?>
                    </select>
                  </div>
                    </div>
                    <?php 
                  }
                    ?>
                    <div class="estate-drop">
                  <div class="dropdown-sin-1 bedroom-1">
                    <select name="bedroom" id="bedroom-1" placeholder="<?=_e('Bedroom','wp-resale');?>"> 
                      <option value=""><?=_e('Bedroom','wp-resale');?></option>
                      <?php 
                  $sql = "SELECT DISTINCT(bedrooms) FROM $property_table WHERE lang = '$my_current_lang'";
                  $bedrooms = $wpdb->get_results( $sql );
                  foreach($bedrooms as $bedroom){
                  ?>
                    <option <?php if (isset($bed)){ if($bedroom->bedrooms==$bed){ echo 'selected'; } } ?> value="<?=$bedroom->bedrooms?>"><?=strtolower($bedroom->bedrooms)?> <?=_e('Bedroom','wp-resale');?></option>
                  <?php 
                  }
                  ?>
                    </select>
                  </div>
                    </div>
                    <div class="estate-drop last-drop ">
                      <div class="dropdown-sin-1 last-dropdown min_price-1">
                    <select class="filter-1" id="min_price-1" name="min_price" class="listing drp5" placeholder="<?=_e('Min Price','wp-resale');?>">
                      <option value=""><?=_e('Min Price','wp-resale');?></option>
                      <?php 
                      $price = $price_min;
                      while($price<=$price_max){
                      ?>
                      <option <?php if (isset($min)){ if($price==$min){ echo 'selected'; } } ?> value="<?=$price;?>"><?=$price;?></option>
                      <?php 
                      $price+=$price_step;
                      } 
                    ?>
                    </select>
                  </div>
                  <div class="dropdown-sin-1 last-dropdown max_price-1">
                    <select class="filter-1" id="max_price-1" name="max_price" class="listing drp6" placeholder="<?=_e('Max Price','wp-resale')?>">
                      <option value=""><?=_e('Max Price','wp-resale')?></option>
                      <?php 
                      $price = $price_min;
                      while($price<=$price_max){
                      ?>
                      <option <?php if (isset($max)){ if($price==$max){ echo 'selected'; } } ?> value="<?=$price;?>"><?=$price;?></option>
                      <?php 
                      $price+=$price_step;
                      } 
                      ?>
                    </select>
                  </div>
                </div>
              </div>
              <p id="min_max_error" style="text-align: center; color: red; font-size: 14px;"></p>



          <!--  advanced search -->

          <div class="advanced-search-section">

            <div class="advanced-border" style="display: none;">

              <?=advance_search();?>

            </div>
            <div class="search-box">
              <div class="resales-box">
                <a href="#ex2" rel="modal:open"><button type="button" class="resales-btn" > <?=_e('Resales Ref#','wp-resale')?></button></a>
              </div>
              <div class="advanced-search">
                <button type="button" id="advance-search-1" class="search"><?=_e('Advanced search','wp-resale')?></button>
              </div>
                <?php 
                  $sql = "SELECT count(id) as total FROM $property_table";
                  $total = $wpdb->get_results( $sql );
                ?>
                <div class="search-bar ">
                  
                      <button class="search-btn" id="search" type="submit" name="search" >
                        <div>
                        <i class="fas fa-search" aria-hidden="true"></i>
                      </div>
                        <span><?=_e('Search','wp-resale');?></span>
                        <img id="count_content_load" src="<?= WP_RESALES_MARBELLA_PLUGIN_URL.'images/Loading_icon.gif';?>">
                        <span class="search-count"></span></button>
                </div>
            </div>
          </div>
        </form>
        <!-- buttons -->
        <div class="view-list pagination-top">
          <!-- sort -->
          <div class="sort">
            <div class="dropdown" tabindex="1">
              <i class="db2" tabindex="1"></i>
              <a class="dropbtn">    
          <i class='fas fa-sort-amount-up sort-icons'></i>
                <span class="sort-txt"><?=_e('Sort by','wp-resale');?></span>
              </a>
                <div class="dropdown-content">
                  <a class="sort_data active" data-Id="price asc"><?=_e('Price Ascending','wp-resale');?></a>
                  <a class="sort_data" data-Id="price desc"><?=_e('Price Descending','wp-resale');?></a>
                  <a class="sort_data" data-Id="location asc"><?=_e('Location','wp-resale');?></a>
                  <a class="sort_data" data-Id="updated_date asc"><?=_e('Latest Updated','wp-resale');?></a>
                  <a class="sort_data" data-Id="updated_date desc"><?=_e('Oldest Updated','wp-resale');?></a>
                  <a class="sort_data" data-Id="created_date asc"><?=_e('Latest List','wp-resale');?></a>
                  <a class="sort_data" data-Id="created_date desc"><?=_e('Oldest List','wp-resale');?></a>
                </div>
            </div>
          </div>
          <!--  pagination -->
          <div class="pagination" style="border: none;padding: 0;margin: 0;">
            
          </div>
          <div class="buttons-list">
            <button type="button" class="grid-view-button list-view-button"><i class="fa fa-th list-grid-icon" aria-hidden="true"></i></button>
            <button type="button" class="list-view-button list-view-button"> <i class="fa fa-list list-grid-icon" aria-hidden="true"></i></button>
          </div>
        </div>
      </div>
            <!-- img section  -->
            <img class="total_content_load" src="<?= WP_RESALES_MARBELLA_PLUGIN_URL.'images/Loading_icon.gif';?>">
            <div class="realestate-img-section">
                <div class="realestate_row  list grid-view-filter">
                </div>
                <div class="realestate_row  list list-view-filter">
                </div>
            </div>
          <div class="view-list pagination-bottom">
            <!--  pagination -->
            <div class="pagination" style="border: none;padding: 0;margin: 0; width: 100%;">
              
            </div>
          </div>
      </section>
      <div id="ex1" class="modal" style="display:none">
        <div >
          <h2><?=_e('Share Property Search','wp-resale');?></h2>
        </div>
        <?php 
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        ?>
        <div class="search-body">
          <div class="modal-content">
            <img  class="notes"src="<?= WP_RESALES_MARBELLA_PLUGIN_URL.'images/copy.png';?>"><p></p><a href="https://en.wikipedia.org/wiki/Clipboard_(computing)" target="_blank"><img  class="question" src="<?= WP_RESALES_MARBELLA_PLUGIN_URL.'images/quetions.png';?>"></a>
          </div>
          <div class="modal-input">
            <input id="page_permalink" type="text" name="" value="<?=__($actual_link);?>" readonly>
          </div>
        </div>
      </div>
      <div id="ex2" class="modal" style="display:none">
        <div class="modal-header">
          <?=_e('Reference number','wp-resale')?>
        </div>
        <div class="modal-content-body">
          <input id="refrence-input" type="text" name="reference" required>
          <span id="refrence-input-span"></span>
        </div>
        <div class="modal-footer reference-footer">
          <div class="refrence_content_load_sec" style="display: none;">
            <img class="refrence_content_load" src="<?= WP_RESALES_MARBELLA_PLUGIN_URL.'images/Loading_icon.gif';?>" style="display: none;">
          </div>
          <div class="modal-button-view">
            
          </div>
          <div class="modal-button-search">
            <button id="refrence-search" type="button"><?=_e('Search','wp-resale')?></button>
          </div>
        </div>
      </div>
      <script>
      $('.dropdown-mul-1').dropdown({
        limitCount: 40,
        multipleMode: 'label',
        choice: function () {
          console.log("hhh");
        }
      });
      $('.dropdown-sin-1').dropdown({
        readOnly: true,
        input: '<input type="text" maxLength="20" placeholder="<?=_e('Select',"wp-resale")?>">'
      });

      jQuery(document).ready( function() {
        $('.location-1').find('input').attr('placeholder', '<?=_e("Location","wp-resale")?>');
        $('.property_type-1').find('input').attr('placeholder', '<?=_e("Property Type","wp-resale")?>');
        $('.dropdown-sin-1').find('select option').removeAttr('selected');
        $('.total_content_load').show();
        $('.realestate-img-section').hide();
        var listing = jQuery(".listing-1").find(".dropdown-chose").attr('data-value');
        var location = jQuery("#location-1").val();
        if(location!=null){
        location = location.filter(function (el) {
            return el != null && el != "";
          });
          if(location.length==0){
            location = null;
          }
        }
        var property_type = jQuery("#property_type-1").val();
        var bedroom = jQuery(".bedroom-1").find(".dropdown-chose").attr('data-value');
        var min_price = jQuery(".min_price-1").find(".dropdown-chose").attr('data-value');
        var max_price = jQuery(".max_price-1").find(".dropdown-chose").attr('data-value');
        var search = jQuery("#search").val();
        var sort = jQuery(".sort_data.active").attr('data-id');
        var page_size = <?=$wiipagesize;?>;
        var lang = '<?=$my_current_lang?>';
        var url_lang = '<?=$url_current_lang?>';
        ajax_search_count('get_search_count');
        jQuery.ajax({
          type : "post",
          dataType : "json",
          url :  myAjax.ajaxurl,
          data : {action: "get_search_result",sort:sort,listing:listing,location:location,property_type:property_type,bedroom:bedroom,min_price:min_price,max_price:max_price,search:search,page_size:page_size,lang:lang,url_lang:url_lang},
          success: function(response) {
            $('.total_content_load').hide();
            $('.realestate-img-section').show();
            if(response.type == "success") {
              jQuery(".grid-view-filter").html(response.grid);
              jQuery(".list-view-filter").html(response.list);
              jQuery(".pagination").html(response.pagination);
            }
            else {
              alert("<?=_e('No data found')?>");
            }
          }
        })
        jQuery("#refrence-search").on("click",function(e) {
          var reference = jQuery("#refrence-input").val();
          if (reference == '') {
            $('#refrence-input-span').html('<?=_e('You entered blank, please try again','wp-resale')?>');
            $(".modal-button-view").find('a').remove();
            $('.modal-button-view').html('');
            return false;
          }else{
            $('.refrence_content_load').show();
            $('.refrence_content_load_sec').show();
            $('.modal-button-view').html('');
            $('#refrence-input-span').html('');
          }
          jQuery.ajax({
             type : "post",
             dataType : "json",
             url :  myAjax.ajaxurl,
             data : {action: "get_search_id",reference:reference,lang:lang,url_lang:url_lang},
             success: function(response) {
                if(response.type == "success") {
                  $('.refrence_content_load').hide();
                  $('.refrence_content_load_sec').hide();
                   jQuery(".modal-button-view").html(response.data);
                }
                else {
                  $('.refrence_content_load').hide();
                  $('.refrence_content_load_sec').hide();
                   jQuery(".modal-button-view").html("<?=_e('No data found, please try again','wp-resale')?>");
                }
             }
          });
        });
        jQuery(".notes").on("click",function(e) {
          document.getSelection().removeAllRanges();
          var link = $("#page_permalink").select();
          document.execCommand("copy");
          setTimeout(function() { 
              alert(link.val());
          }, 500);
        });
       jQuery(".search-btn").on("click",function(e) {
          e.preventDefault();
          var listing = jQuery(".listing-1").find(".dropdown-chose").attr('data-value');
          var location = jQuery("#location-1").val();
          if(location!=null){
            var location = location.filter(function (el) {
              return el != null && el != "";
            });
            if(location.length==0){
              location = null;
            }
          }
          var property_type = jQuery("#property_type-1").val();
          var bedroom = jQuery(".bedroom-1").find(".dropdown-chose").attr('data-value');
          var min_price = jQuery(".min_price-1").find(".dropdown-chose").attr('data-value');
          var max_price = jQuery(".max_price-1").find(".dropdown-chose").attr('data-value');
          var sort = jQuery(".sort_data.active").attr('data-id');
          var search = jQuery("#search").val();
          var page_size = <?=$wiipagesize;?>;
          var lang = '<?=$my_current_lang?>';
          if(parseInt(min_price)>parseInt(max_price)){
            jQuery("#min_max_error").html("<?=_e('Max Price can not be less than Min Price','wp-resale');?>");
            return false;
          }else{
            jQuery("#min_max_error").html("");
          }
          var advance_search = [];
          jQuery('.advanced-search-drop select').each(function(){
            advance_search.push($(this).find(":selected").val());
          });
          $('.total_content_load').show();
          $('.realestate-img-section').hide();
          jQuery.ajax({
             type : "post",
             dataType : "json",
             url :  myAjax.ajaxurl,
             data : {action: "get_search_result",sort:sort,listing:listing,location:location,property_type:property_type,bedroom:bedroom,min_price:min_price,max_price:max_price,search:search,advance_search:advance_search,page_size:page_size,lang:lang,url_lang:url_lang},
             success: function(response) {
                $('.total_content_load').hide();
                $('.realestate-img-section').show();
                if(response.type == "success") {
                  location = location!=null?location:'';
                  property_type = property_type!=null?property_type:'';
                  var url = '<?=get_permalink();?>'+'?listing='+listing+'&location='+location+'&property_type='+property_type+'&bedroom='+bedroom+'&min_price='+min_price+'&max_price='+max_price+'&search=';
                  $('#page_permalink').val('');
                  $('#page_permalink').val(url);
                  var obj = { Title: 'Page1', Url: url };
                  history.pushState(obj, obj.Title, obj.Url);
                  jQuery(".grid-view-filter").html(response.grid);
                  jQuery(".list-view-filter").html(response.list);
                  jQuery(".pagination").html(response.pagination);
                  var pagination = jQuery(".pagination-1").val();
                  pagination_fun(pagination);
                }
                else {
                  alert("<?=_e('No data found','wp-resale');?>");
                }
             }
          })   
       })
       jQuery(".sort_data").on("click",function(e){
        $(".sort_data.active").attr('class','sort_data');
        $(this).attr('class','sort_data active');
        var sort = $(this).attr('data-Id');
        var listing = jQuery(".listing-1").find(".dropdown-chose").attr('data-value');
        var location = jQuery("#location-1").val();
        if(location!=null){
        location = location.filter(function (el) {
            return el != null && el != "";
          });
          if(location.length==0){
            location = null;
          }
        }
        var property_type = jQuery("#property_type-1").val();
        var bedroom = jQuery(".bedroom-1").find(".dropdown-chose").attr('data-value');
        var min_price = jQuery(".min_price-1").find(".dropdown-chose").attr('data-value');
        var max_price = jQuery(".max_price-1").find(".dropdown-chose").attr('data-value');
        var search = jQuery("#search").val();
        var page_size = <?=$wiipagesize;?>;
        var lang = '<?=$my_current_lang?>';
        if(parseInt(min_price)>parseInt(max_price)){
          jQuery("#min_max_error").html("<?_e('Max Price can not be less than Min Price','wp-resale');?>");
          return false;
        }else{
          jQuery("#min_max_error").html("");
        }
        var advance_search = [];
        jQuery('.advanced-search-drop select').each(function(){
          advance_search.push($(this).find(":selected").val());
        });
        $('.total_content_load').show();
        $('.realestate-img-section').hide();
        jQuery.ajax({
           type : "post",
           dataType : "json",
           url :  myAjax.ajaxurl,
           data : {action: "get_search_result",listing:listing,location:location,property_type:property_type,bedroom:bedroom,min_price:min_price,max_price:max_price,sort:sort,search:search,advance_search:advance_search,page_size:page_size,lang:lang,url_lang:url_lang},
           success: function(response) {
            $('.total_content_load').hide();
            $('.realestate-img-section').show();
              if(response.type == "success") {
                jQuery(".grid-view-filter").html(response.grid);
                jQuery(".list-view-filter").html(response.list);
                jQuery(".pagination").html(response.pagination);
              }
              else {
                 alert("<?=_e('No data found','wp-resale');?>");
              }
           }
        })
       })
       $(document).on('change', '.pagination-1', function() {
             var pagination = jQuery(this).val();
             $(".pagination-1").val(pagination).attr('selected');
             pagination_fun(pagination);
       })
       $(document).on('click', '.angle-left', function() {
             var pagination = jQuery(".pagination-1").val()-1;
             $(".pagination-1").val(pagination).attr('selected');
             pagination_fun(pagination);
       })
       $(document).on('click', '.angle-double-left', function() {
             var pagination = 1;
             $(".pagination-1").val(pagination).attr('selected');
             pagination_fun(pagination);
       })
       $(document).on('click', '.angle-right', function() {
             var pagination = parseInt(jQuery(".pagination-1").val())+1;
             $(".pagination-1").val(pagination).attr('selected');
             pagination_fun(pagination);
       })
       $(document).on('click', '.angle-double-right', function() {
             var pagination = jQuery(".pagination-1 option").length/2;
             $(".pagination-1").val(pagination).attr('selected');
             pagination_fun(pagination);
       })
         jQuery(".min_price-1").on("click",function(e){
          var min_price = jQuery(this).find('.dropdown-chose-list span i').attr('data-id');
            ajax_search_count('get_search_count',null,null,null,null,min_price);
         })
         jQuery(".max_price-1").on("click",function(e){
          var max_price = jQuery(this).find('.dropdown-chose-list span i').attr('data-id');
            ajax_search_count('get_search_count',null,null,null,null,null,max_price);
         })
          jQuery(".listing-1").on("keyup click",function(e){
          var listing = jQuery(this).find('.dropdown-chose-list span i').attr('data-id');
          ajax_search_count('get_search_count',listing);
         })
         jQuery(".location-1").on("keyup click",function(e){
          var location = [];
          jQuery(this).find('.dropdown-chose-list span i').each(function(){
            var data = jQuery(this).attr('data-id');
            if(data != ''){
              location.push(data);
            }
           })
          if(location!=null){
            location = location.filter(function (el) {
              return el != null && el != "";
            });
            if(location.length==0){
              location = null;
            }
          }
          ajax_search_count('get_search_count',null,location);
         })
         jQuery(".property_type-1").on("keyup click",function(e){
          var property_type = [];
          jQuery(this).find('.dropdown-chose-list span i').each(function(){
            var data = jQuery(this).attr('data-id');
            if(data != ''){
              property_type.push(data);
            }
            
           })
          ajax_search_count('get_search_count',null,null,property_type);
         })
         jQuery(".bedroom-1").on("keyup click",function(e){
          e.preventDefault();
          var bedroom = jQuery(this).find('.dropdown-chose-list span i').attr('data-id');
          ajax_search_count('get_search_count',null,null,null,bedroom);
         })
         jQuery(".advanced-search-drop").on("keyup click",function(e){
          ajax_search_count('get_search_count');
         })
         jQuery('#advance-search-1').click(function(){
          $('.advanced-border').toggle();
          $('#advance-search-1').toggleClass("ub-down-button");
         })
      })
      function pagination_fun(page){
        var listing = jQuery(".listing-1").find(".dropdown-chose").attr('data-value');
        var location = jQuery("#location-1").val();
        if(location!=null){
          location = location.filter(function (el) {
            return el != null && el != "";
          });
          if(location.length==0){
            location = null;
          }
        }
        var property_type = jQuery("#property_type-1").val();
        var bedroom = jQuery(".bedroom-1").find(".dropdown-chose").attr('data-value');
        var min_price = jQuery(".min_price-1").find(".dropdown-chose").attr('data-value');
        var max_price = jQuery(".max_price-1").find(".dropdown-chose").attr('data-value');
        var sort = jQuery(".sort_data.active").attr('data-id');
        var lang = '<?=$my_current_lang?>';
        var url_lang = '<?=$url_current_lang?>';
        if(parseInt(min_price)>parseInt(max_price)){
          jQuery("#min_max_error").html("<?php echo _e('Max Price can not be less than Min Price','wp-resale');?>");
          return false;x
        }else{
          jQuery("#min_max_error").html("");
        }
        var pagination = page;
        var pagination_length = jQuery(".pagination-1 option").length/2;
        var page_size = <?=$wiipagesize;?>;
        if(pagination == '' || pagination<=1){
          $('.angle-double-left').attr('style','opacity: 0; position: relative; z-index: -1;');
          $('.angle-left').attr('style','opacity: 0; position: relative; z-index: -1;');
        }else{
          $('.angle-double-left').attr('style','opacity: 1; position: unset; z-index: 0;');
          $('.angle-left').attr('style','opacity: 1; position: unset; z-index: 0;');
        }
        if(pagination == '' || pagination>=pagination_length){
          $('.angle-double-right').attr('style','opacity: 0; position: relative; z-index: -1;');
          $('.angle-right').attr('style','opacity: 0; position: relative; z-index: -1;');
        }else{
          $('.angle-double-right').attr('style','opacity: 1; position: unset; z-index: 0;');
          $('.angle-right').attr('style','opacity: 1; position: unset; z-index: 0;');
        }
        var advance_search = [];
        jQuery('.advanced-search-drop select').each(function(){
          advance_search.push($(this).find(":selected").val());
        });
        var search = jQuery("#search").val();
        $('.total_content_load').show();
        $('.realestate-img-section').hide();
        jQuery.ajax({
           type : "post",
           dataType : "json",
           url :  myAjax.ajaxurl,
           data : {action: "get_search_result",sort:sort,listing:listing,location:location,property_type:property_type,bedroom:bedroom,min_price:min_price,max_price:max_price,advance_search:advance_search,pagination:pagination,search:search,page_size:page_size,lang:lang,url_lang:url_lang},
           success: function(response) {
            $('.total_content_load').hide();
            $('.realestate-img-section').show();
            if(response.type == "success") {
              jQuery(".grid-view-filter").html(response.grid);
              jQuery(".list-view-filter").html(response.list);
            }
            else {
              alert("<?=_e('No data found','wp-resale');?>");
            }
          }
        })
      }
      function ajax_search_count(page,listing=null,location=null,property_type=null,bedroom=null,min_price=null,max_price=null){
        if(listing==null){
          var listing = jQuery(".listing-1").find(".dropdown-chose").attr('data-value');
        }
        if(location==null){
          var location = jQuery("#location-1").val();
        }
        if(location!=null){
          location = location.filter(function (el) {
            return el != null && el != "";
          });
          if(location.length==0){
            location = null;
          }
        }
        if(property_type==null){
          var property_type = jQuery("#property_type-1").val();
        }
        if(bedroom==null){
          var bedroom = jQuery(".bedroom-1").find(".dropdown-chose").attr('data-value');
        }
        if(min_price==null){
          var min_price = jQuery(".min_price-1").find(".dropdown-chose").attr('data-value');
        }
        if(max_price==null){
          var max_price = jQuery(".max_price-1").find(".dropdown-chose").attr('data-value');
        }
        if(parseInt(min_price)>parseInt(max_price)){
          jQuery("#min_max_error").html("<?=_e('Max Price can not be less than Min Price','wp-resale');?>");
          return false;
        }else{
          jQuery("#min_max_error").html("");
        }
        var lang = '<?=$my_current_lang?>';
        var url_lang = '<?=$url_current_lang?>';
        var advance_search = [];
          jQuery('.advanced-search-drop select').each(function(){
            advance_search.push($(this).find(":selected").val());
          });
          $('#count_content_load').show();
          $('.search-count').hide();
          jQuery.ajax({
           type : "post",
           dataType : "json",
           url :  myAjax.ajaxurl,
           data : {action: page,listing:listing,location:location,property_type:property_type,bedroom:bedroom,min_price:min_price,max_price:max_price,advance_search:advance_search,lang:lang,url_lang:url_lang},
           success: function(response) {
              $('#count_content_load').hide();
              $('.search-count').show();
              if(response.type == "success") {
                 jQuery(".search-count").html(response.total);
              }
              else {
                 alert("<?=_e("No data found","wp-resale")?>");
              }
           }
        })
      }
      </script>
  <?php
  $out = ob_get_contents();
  ob_end_clean();
  return $out;
}

add_shortcode('resales_search','search_method');

function search_method($atts){
  $my_current_lang = !empty(apply_filters( 'wpml_current_language', NULL ))?apply_filters( 'wpml_current_language', NULL ):'en';
  $url_current_lang = !empty(apply_filters( 'wpml_current_language', NULL ))?apply_filters( 'wpml_current_language', NULL ):'';
  $location = load_plugin_textdomain( 'wp-resale', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
  $newdev = isset($atts['newdev'])?$atts['newdev']:'only';
  $price_min = isset($atts['price_min'])?$atts['price_min']:1000;
  $price_max = isset($atts['price_max'])?$atts['price_max']:1000000;
  $price_step = isset($atts['price_step'])?$atts['price_step']:10000;
  $prop_box = isset($atts['prop_box'])?$atts['prop_box']:0;
  $loc_box = isset($atts['loc_box'])?$atts['loc_box']:0;

  global $wpdb;
  ob_start();
  $property_table = $wpdb->prefix.'property';
  $listing_table = $wpdb->prefix.'listing';
  ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
  <!-- jQuery Modal -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
  <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');
     </style>
  <link rel="stylesheet" href="<?= WP_RESALES_MARBELLA_PLUGIN_URL.'/css/style.css'?>" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <link href="https://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
  <link rel="stylesheet" type="text/css" href="<?= WP_RESALES_MARBELLA_PLUGIN_URL.'/css/jquery.dropdown.min.css';?>">
  <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
  <link rel="stylesheet" type="text/css" href="<?= WP_RESALES_MARBELLA_PLUGIN_URL.'/css/jquery.dropdown.css';?>">
  <script src="<?= WP_RESALES_MARBELLA_PLUGIN_URL.'/js/jquery.dropdown.js';?>"></script>
  <script>
    $(document).ready(function () {
    
    
          $('.list-view-button').click(function () {
              if ($(this).hasClass('grid-view-button')) {
        
          $('.grid-view-filter').css('display', 'flex')
          $('.list-view-filter').css('display', 'none')
              }
        
        else{
        
        $('.list-view-filter').css('display', 'block')
        $('.grid-view-filter').css('display', 'none')
        };
        });
    });
  </script>
  <section class="realestate">
    <form method="get" action="<?=site_url().($url_current_lang?'/'.$url_current_lang:'');?>/list-property">
      <div class="section-search">
        <div class="real-estate-search">
          <input type="hidden" name="lang" value="<?=$my_current_lang?>">
          <div class="estate-drop">
            <div class="dropdown-sin-1 listing-1">
              <select name="listing" id="listing-1" placeholder="<?=_e('Listing','wp-resale');?>"> 
                <option <?php if (isset($list)){ if(1==$list){ echo 'selected'; } } ?> value="1"><?=_e('Sales','wp-resale');?></option>
                <option <?php if (isset($list)){ if(2==$list){ echo 'selected'; } } ?> value="2"><?=_e('Short Term Rental','wp-resale');?></option>
                <option <?php if (isset($list)){ if(3==$list){ echo 'selected'; } } ?> value="3"><?=_e('Long Term Rental','wp-resale');?></option>
                <option <?php if (isset($list)){ if(4==$list){ echo 'selected'; } } ?> value="4"><?=_e('Featured','wp-resale')?></option>   
              </select>
            </div>
          </div>
          <?php 
          if(!empty($loc_box)){
          ?>
          <div class="estate-drop">
            <div class="dropdown-mul-1 location-1">
              <select name="location[]" id="location-1" multiple placeholder="<?=_e("Location","wp-resale");?>"> 
                <?php 
                $sql = "SELECT DISTINCT(location) FROM $property_table WHERE lang = '$url_current_lang'";
                $locations = $wpdb->get_results( $sql );
                foreach($locations as $location){
                ?>
                <option value="<?=$location->location?>"><?=strtolower($location->location)?><?=_e("Location",'wp-resale');?></option>
                <?php 
                }
                ?>
              </select>
            </div>
          </div>
          <?php 
          }
          if(!empty($prop_box)){
          ?>
          <div class="estate-drop">
            <div class="dropdown-mul-1 property_type-1">
              <select name="property_type[]" id="property_type-1" multiple placeholder="<?=_e('Property Type','wp-resale');?>"> 
                <?php 
                $sql = "SELECT property_type FROM $property_table WHERE lang = '$url_current_lang'";
                $property_types = $wpdb->get_results( $sql );
                $unique_property = [];
                foreach($property_types as $property_type){
                  $property_type = json_decode($property_type->property_type);
                  if(in_array($property_type->NameType, $unique_property)){
                    continue;
                  }else{
                    $unique_property[] = $property_type->NameType;
                  }
                ?>
                <option value="<?=$property_type->NameType?>"><?=strtolower($property_type->NameType);?></option>
                <?php 
                }
                ?>
              </select>
            </div>
          </div>
          <?php 
          }
          ?>
          <div class="estate-drop">
            <div class="dropdown-sin-1 bedroom-1">
              <select name="bedroom" id="bedroom-1" placeholder="<?= _e("Bedroom",'wp-resale')?>"> 
                <option value=""><?= _e("Bedroom",'wp-resale')?></option>
                <?php 
                $sql = "SELECT DISTINCT(bedrooms) FROM $property_table WHERE lang = '$url_current_lang'";
                $bedrooms = $wpdb->get_results( $sql );
                foreach($bedrooms as $bedroom){
                ?>
                <option value="<?=$bedroom->bedrooms?>"><?=strtolower($bedroom->bedrooms)?> <?=_e("Bedroom",'wp-resale');?></option>
                <?php 
                }
                ?>
              </select>
            </div>
          </div>
          <div class="estate-drop last-drop ">
            <div class="dropdown-sin-1 last-dropdown min_price-1">
              <select class="filter-1" id="min_price-1" name="min_price" class="listing drp5" placeholder="<?=_e('Min Price','wp-resale');?>">
                <option value=""><?=_e("Min Price",'wp-resale');?></option>
                <?php 
                $price = $price_min;
                while($price<=$price_max){
                ?>
                <option value="<?=$price;?>"><?=$price;?></option>
                <?php 
                $price+=$price_step;
                } 
                ?>
              </select>
            </div>
            <div class="dropdown-sin-1 last-dropdown max_price-1">
              <select class="filter-1" id="max_price-1" name="max_price" class="listing drp6" placeholder="<?=_e('Max Price','wp-resale');?>">
                <option value=""><?=_e("Max Price",'wp-resale');?></option>
                <?php 
                $price = $price_min;
                while($price<=$price_max){
                ?>
                <option value="<?=$price;?>"><?=$price;?></option>
                <?php 
                $price+=$price_step;
                } 
                ?>
              </select>
            </div>
          </div>
          <div class="search-bar homepage-search-btn ">
            <button class="search-btn" id="search" type="submit" name="search" ><div>
                        <i class="fas fa-search search-icon1" aria-hidden="true"></i>
                      </div></button>
          </div>
        </div>
      </div>
    </form>
  </section>
  <script type="text/javascript">
    $('.dropdown-mul-1').dropdown({
      limitCount: 40,
      multipleMode: 'label',
      choice: function () {
        // console.log(arguments,this);
      }
    });
    $('.dropdown-sin-1').dropdown({
      readOnly: true,
      input: '<input type="text" maxLength="20" placeholder="<?=_e('Select','wp-resale')?>">'
    });
    jQuery(document).ready( function() {
      $('.location-1').find('input').attr('placeholder', '<?=_e("Location",'wp-resale');?>');

      $('.property_type-1').find('input').attr('placeholder', '<?=_e("Property Type",'wp-resale');?>');
    });
  </script>
  <?php
  $out = ob_get_contents();
  ob_end_clean();
  return $out;
}