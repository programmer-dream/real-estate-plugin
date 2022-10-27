<?php 
/* Template Name: Single Property */
get_header();
define('WP_RESALES_MARBELLA_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('WP_RESALES_MARBELLA_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
$my_current_lang = !empty(apply_filters( 'wpml_current_language', NULL ))?apply_filters( 'wpml_current_language', NULL ):'en';
$url_current_lang = !empty(apply_filters( 'wpml_current_language', NULL ))?apply_filters( 'wpml_current_language', NULL ):'';
$location = load_plugin_textdomain( 'wp-resale', false, plugin_basename( dirname( __DIR__ ) ) . '/languages/' );
?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <title><?=__('realestate')?></title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
      <link rel="stylesheet" href="<?= WP_RESALES_MARBELLA_PLUGIN_URL.'css/style.css';?>">
   </head>
   <style>
      @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');
   </style>
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
  $(document).ready(function(){ 
    $('.tab-a').click(function(){  
      $(".tab").removeClass('tab-active');
      $(".tab[data-id='"+$(this).attr('data-id')+"']").addClass("tab-active");
      $(".tab-a").removeClass('active-a');
      $(this).parent().find(".tab-a").addClass('active-a');
     });
});
var slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
  showSlides(slideIndex += n);
}

function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  var i;
  var slides = document.getElementsByClassName("slides");
  var dots = document.getElementsByClassName("slide-thumbnail");
  //var captionText = document.getElementById("caption");
  if (n > slides.length) {slideIndex = 1}
  if (n < 1) {slideIndex = slides.length}
  console.log(slideIndex);

  for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none";
      // slides[i].style.display = "inline";
  }
  for (i = 0; i < dots.length; i++) {
      dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex-1].style.display = "block";
  // slides[slideIndex-1].style.display = "inline";
  dots[slideIndex-1].className += " active";
 // captionText.innerHTML = dots[slideIndex-1].alt;

 
}
</script>
<?php 
if(isset($_GET['slug'])){
	global $wpdb;

	$property_table = $wpdb->prefix.'property';
	$sql = "SELECT * FROM $property_table where slug='$_GET[slug]'";
	$data = $wpdb->get_results( $sql );
	// echo '<pre>';print_r($data);
	if(empty($data)){
		header("Location: ".site_url().($url_current_lang?'/'.$url_current_lang:'')."/list-property/");
	}
	$property_type = json_decode($data[0]->property_type);
	$pics = json_decode($data[0]->pictures);
	$property_features = json_decode($data[0]->property_features);
	// print_r($property_features);
}else{
	header("Location: ".site_url().($url_current_lang?'/'.$url_current_lang:'')."/list-property/");
}
?>

   <body>
      	<section id="single-property" class="single-property">
         	<div class="main">
           		<div class="main-heading-cont">
		            <div class="heading-txt">
		                <?=$property_type->NameType;?> <?=_e('in','wp-resale');?> <?=$data[0]->location?:'';?>
		            </div>
		            <div class="top-btn">

		                <a href="<?= site_url().($url_current_lang?'/'.$url_current_lang:'');?>/list-property/"><button class="top-txt">
		                    <i class="fa fa-angle-left top-left" aria-hidden="true"></i>
		                   <span> <?=_e('Back to list','wp-resale');?></span>
		                </button></a>

		            </div>
           		</div>

	           	<!--  2nd section -->

				<div class="single-img-section">
					<div class="single-img">
				    	<div  class="property-img">
				  			<div class="holder">
							  	<?php 
							  	if(isset($pics)){
							  	foreach($pics->Picture as $pic){
							  	?>
							    <div class="slides">
							          <img class="zoom-in-img" src="<?= $pic->PictureURL;?>">
							    </div>
							    <?php 
								}} else{
									?>
										<div class="slides">
									          <img class="zoom-in-img" src="<?=  WP_RESALES_MARBELLA_PLUGIN_URL.'/images/realestate.jpg';?>">
									    </div>
								<?php
								}
							    ?>
				  			</div>
					  		<div class="realestate-single-price">
					    		<h5 ><b><?php if($data[0]->listing==1||$data[0]->listing==4){ echo _e('For Sale','wp-resale'); }elseif($data[0]->listing==2){ echo _e('Holiday Rentals','wp-resale'); }elseif($data[0]->listing==3){ echo _e('Long Term Rentals','wp-resale'); }?><br>€<?php if($data[0]->price!=0) { echo round($data[0]->price); }elseif($data[0]->original_price!=0) { echo round($data[0]->original_price); }else{ echo round($data[0]->rental_price1).' - €'.round($data[0]->rental_price2); } ?>
                     </b><span class="week"> <?php if(!empty($data->rental_period)) { echo _e('per').' '.$data->rental_period; } ?></span></h5>
					 		</div>
					 		<div class="zoom-in">
					    		<i class='fas fa-expand-arrows-alt' style="    font-size: 20px;"></i>
					 		</div>
					  		<div class="prevContainer"><a class="prev" onclick="plusSlides(-1)">
					    	

					    		<i class="fa fa-angle-left angle-right arrow-prev" aria-hidden="true"></i>

					    	</a></div>
					  		<div class="nextContainer"><a class="next" onclick="plusSlides(1)">
		
					    		<i class="fa fa-angle-right angle-right arrow-prev" aria-hidden="true"></i>
					    	</a></div>
						  	<!-- thumnails in a row -->
						  	<div class="row slider-single-img">
							  	<?php 
							  	$i=1;
							  	if(isset($pics)){
							  	foreach($pics->Picture as $pic){
							  	?>
							    <div class="column">
							      <img class="slide-thumbnail" src="<?= $pic->PictureURL;?>" onclick="currentSlide(<?=$i++;?>)" alt="Caption One">
							    </div>
							    <?php 
								}}else{
									?>
									<div class="column">
								      <img class="slide-thumbnail" src="<?= WP_RESALES_MARBELLA_PLUGIN_URL.'/images/realestate.jpg';?>" onclick="currentSlide(<?=$i++;?>)" alt="Caption One">
								    </div>
									<?php
								}
							    ?>
						  	</div>
	            		</div>
	            	</div>
		            <div class="property-details">
		                <h2 >
		                    <?=_e('Property highlights','wp-resale')?>
		                </h2>
		            <div class="property-table">
		                <table>
		                    <tr>
		                        <td><?=_e('Reference','wp-resale')?></td>
		                        <td><?=$data[0]->reference?:'';?></td>
		                    </tr>
		                    <tr>
		                      <td><?=_e('Price','wp-resale')?></td>
		                      <td>€<?php if($data[0]->price!=0) { echo round($data[0]->price); }elseif($data[0]->original_price!=0) { echo round($data[0]->original_price); }else{ echo round($data[0]->rental_price1).' - €'.round($data[0]->rental_price2); } ?></td>
		                    
		                    </tr>
		                    <tr>
		                      <td><?=_e('Bedrooms','wp-resale')?></td>
		                      <td><?=$data[0]->bedrooms?:'';?></td>
		                    
		                    </tr>
		                    <tr>
		                      <td><?=_e('Terrace','wp-resale');?></td>
		                      <td><?=$data[0]->terrace?$data[0]->terrace.' m2':'';?></td>
		                    
		                    </tr>
		                    <tr>
		                        <td><?=_e('Garden Plot','wp-resale');?></td>
		                        <td><?=$data[0]->garden_plot?$data[0]->garden_plot.' m2':'';?></td>
		                      
		                      </tr>
		                      <tr>
		                        <td><?=_e('Build Size','wp-resale');?></td>
		                        <td><?=$data[0]->built?$data[0]->built.' m2':'';?></td>
		                      
		                      </tr>
		                      <tr>
		                        <td><?=_e('Energy Rating','wp-resale');?></td>
		                        <td><?=$data[0]->energy_rated?:'';?></td>
		                      
		                      </tr>
		                      <tr>
		                        <td><?=_e('Location','wp-resale');?></td>
		                        <td><?=$data[0]->location?:'';?></td>
		                      
		                      </tr>
		                      <!-- <tr>
		                        <td></?=_e('Year Built','wp-resale');?></td>
		                        <td></?=$data[0]->built?:'';?></td>
		                      
		                      </tr> -->
		                  </table>
		                  </div>

		            </div>
		        </div>



		        <!-- 3rd section -->


		        <div class="tabs-section">
		            <div class="tabs">
		                <div class="tab-container">
		                    <div class="tab-menu">
		                        <ul>
		                         	<li><a  class="tab-a active-a" data-id="tab1"><?=_e('Description','wp-resale')?></a></li>
		                         	<li><a  class="tab-a" data-id="tab2"><?=_e('Location','wp-resale')?></a></li>
		                         	<li><a  class="tab-a" data-id="tab3"><?=_e('Features','wp-resale')?></a></li>
		                            <li><a  class="tab-a" data-id="tab4"><?=_e('Taxes & Fees','wp-resale')?></a></li>
		                            <li><a  class="tab-a" data-id="tab5"><?=_e('Energy Certificate','wp-resale')?></a></li>  
		                        </ul>
		                    </div><!--end of tab-menu-->
		                    <div  class="tab tab-active" data-id="tab1">
		                        <p class="w3-padding-32"><?=$data[0]->description?:'';?>
		                        </p>   
		                    </div><!--end of tab one-->
		                    <div  class="tab energy-tab" data-id="tab2">
		                        <h2></h2>
		                        <p><!-- <script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap"></script> --><?=_e('No Location found','wp-resale')?></p>
		                    </div><!--end of tab two--> 
		                    <div  class="tab " data-id="tab3">
		                        <div class="feature">
		                            <?php 
		                            $j=1;
		                            foreach($property_features->Category as $property_feature){
		                            ?>
		                           <div class="features-list1">
		                            <ul>
		                                <li><b><?=$property_feature->Type?:''?></b></li>
		                                <?php 
		                                foreach($property_feature->Value as $value){
		                                ?>
		                                <li><?=$value?:''?></li>
		                            <?php } ?>
		                              </ul>  
		                              
		                           </div>
		                           <?php
		                       		}
		                           ?>
		                        </div>  
		                    </div>
		                    <div  class="tab  taxes-table" data-id="tab4" >
		                        <table class="tab4-table">
		                            <tr>
		                              <td><?=_e('IBI Fees','wp-resale');?></td>
		                              <td>€ 0 <?=_e('per year','wp-resale');?></td>
		                            </tr>
		                            <tr>
		                              <td><?=_e('Basura Tax Fees','wp-resale')?></td>
		                              <td>€ 0 <?=_e('per year','wp-resale')?></td>
		                            </tr>
		                            <tr>
		                              <td><?=_e('Community Fees','wp-resale')?></td>
		                              <td>€ 0 <?=_e('per year','wp-resale')?></td>
		                            </tr>
		                        </table>   
		                 	</div>
		                 	<div  class="tab energy-tab " data-id="tab5">
		                   		<p><?=_e('No Certificate available','wp-resale')?></p>     
		             		</div>
		                    <!--end of tab five--> 
		                </div>
		            </div>
		            <div class="form-property">
			            <h3>
			                <?=_e('Ask about this Property','wp-resale');?>
			            </h3>
			            <div class="form-area">
			                <div >
				                <label for="fname"><?=_e('First Name','wp-resale')?></label>
				                <input type="text" id="fname" name="fname" placeholder="<?=_e('First Name','wp-resale');?>">
			                </div>
			                <div >
			                    <label for="lname"><?=_e('Last Name','wp-resale');?></label>
			                    <input type="text" id="lname" name="lname"  placeholder="<?=_e('Last Name','wp-resale')?>">
			                </div>
			                <div >
			                    <label for="email"><?=_e('Email','wp-resale');?></label>
			                    <input type="text" id="email" name="email"  placeholder="<?=_e('Email','wp-resale')?>">
			                </div>
			                <div >
			                    <label for="telephone"><?=_e('Telephone','wp-resale')?></label>
			                    <input type="text" id="telephone" name="telephone"  placeholder="<?=_e('Telephone','wp-resale')?>">
			                </div>
			                <div  class="enquiry-btn">
			                    <button>
			                        <span><?=_e('CLICK TO INQUIRE','wp-resale')?></span> 
			             						<i class='fa fa-envelope' style="padding-left: 8px;"></i>
			                    </button>
			                </div>
			            </div>
		        	</div>
		    	</div>
			</div>
		</section>
		<script type="text/javascript">
			jQuery(document).ready( function() {
				jQuery('.zoom-in').click(function(){
	        $('.zoom-in-img').toggleClass('zoomin-effect');
	       })
	     })
		</script>
   </body>
</html>
<?php
get_footer();
?>