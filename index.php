<?php
/**
 * Template name: New Ads Template
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage trade
 * @since trade 2.0
 */
global $trade_theme_options;

if ( !is_user_logged_in() ) {

	$login = $trade_theme_options['login'];
	$login_url = get_page_link( $login );
	wp_redirect( $login_url ); exit;
}

$postTitleError = '';
$typeofAdError = '';
$conditionError = '';
$priceError = '';
$brandError = '';
$CategoiesError = '';
$AdditionalCatError = '';
$locationError = '';
$DeliveryError = '';
$contactError = '';
$activeadsError = '';
$sendError = '';
$galleryError = '';
$featPlanMesage = '';
$featuredADS = '';
$postTitle = $typeofAd = $conditionAd = $price = $brandName = $additionalCat = $locationName = $stateName = $cityName = '';
$countryName = $zipCode = $Contact = $ActiveAds = $Delivery = $send = $lattitude = $longitude = '';

$hasError = false;
	if( isset($_POST['_post_ad']) ){
		
		if (isset( $_POST['cpt_nonce_field'] ) && wp_verify_nonce( $_POST['cpt_nonce_field'], 'cpt_nonce_action' ) ) {	

			if(trim($_POST['_ad_title']) === '') {
				set_transient('postTitleError', 'Voer een titel in.', 60);
				$hasError = true;
			} else {
				$postTitle = trim($_POST['_ad_title']);
			}

			if( empty($_POST['_td_type_of_ad']) && $_POST['_td_type_of_ad'] == '' ) {
				set_transient('typeofAdError', 'Kies het type advertentie.', 60);
				$hasError = true;
			} else {
				$typeofAd = $_POST['_td_type_of_ad'];
			}

			if ( sizeof($_FILES['_td_image']['name']) > 4 ){
				set_transient('galleryError', 'Maximale bestandsuploadlimiet overschreden.', 60);
				$hasError = true;
			} else {
				$hasError = false;
			}


			if(trim($_POST['_td_price']) === '') {
				set_transient('priceError', 'Voer de prijs in.', 60);
				$hasError = true;
			} else {
				$price = trim($_POST['_td_price']);
			}


			if( !empty($_POST['_td_categories']) ){
				$cat_ids = $_POST['_td_categories'];
				$AdCategories[] = $cat_ids;
				if( !empty($_POST['_td_childCat']) ){					
					$child_cat_id = $_POST['_td_childCat'];
					$AdCategories = array_merge($AdCategories,$child_cat_id);
				}
			}else{
				set_transient('CategoiesError', 'Selecteer AD-categorieën.', 60);
				$cat_ids = '';
				$hasError = true;
			}

			if( !empty($_POST['_td_additional']) ){ 
				$additionalCat = $_POST['_td_additional'];
			}else{
				$additionalCat = '';
			}

			if(trim($_POST['_td_location']) === '') {
				set_transient('locationError', 'Voer uw locatie in.', 60);
			} 
			else {
				$locationName = trim($_POST['_td_location']);
				$stateName = ( isset( $_POST['administrative_area_level_1'] ) ) ? trim($_POST['administrative_area_level_1']) : '';
				$cityName = ( isset( $_POST['locality'] ) ) ? trim($_POST['locality']) : '';
				$countryName = ( isset( $_POST['country'] ) ) ? trim($_POST['country']) : '';
				$zipCode = ( isset( $_POST['postal_code'] ) ) ? trim($_POST['postal_code']) : '';				
				$lattitude = ( isset( $_POST['_td_lattitude'] ) ) ? trim($_POST['_td_lattitude']) : '';
				$longitude = ( isset( $_POST['_td_longitude'] ) ) ? trim($_POST['_td_longitude']) : '';
			}

			if(trim($_POST['_td_contact']) === '') {
				set_transient('contactError', 'Voer uw contactnummer in.', 60);
				$hasError = true;
			} else {
				$Contact = trim($_POST['_td_contact']);
			}

            /* active ads during */
			if(trim($_POST['_td_active_ads']) === '') {
				set_transient('activeadsError', 'Please enter the number of active days of the ads.', 60);
				$hasError = true;
			} else {
				$ActiveAds = trim($_POST['_td_active_ads']);
			}

			if(trim($_POST['_td_delivery']) === '') {
				set_transient('DeliveryError', 'Voer uw bezorgtype in.', 60);
				$hasError = true;
			} else {
				$Delivery = trim($_POST['_td_delivery']);
			}

			if( empty($_POST['send']) &&  !isset( $_POST['send'] ) ) {
				set_transient('sendError', 'Controleer de algemene voorwaarden.', 60);
				$hasError = true;
			} else {
				$send = 'checked';
			}

			
			// create post object with the form values
			if( $hasError == false ) {
				$postAction = ( isset( $trade_theme_options['ad_publish'] ) && $trade_theme_options['ad_publish'] == 'publish' ) ? 'publish' : 'pending';
				$my_cptpost_args = array(

				'post_title'    => $_POST['_ad_title'],
				'post_category' => array($AdCategories),
				'post_status'   => $postAction,
				'post_type' => $_POST['_post_type'],

				);

				// insert the post into the database

				$cpt_id = wp_insert_post( $my_cptpost_args);
				
				// save expired ads date postmeta
				$the_post = get_post( $cpt_id );
				$post_date = $the_post->post_date;
				if($ActiveAds == '') {
				    $ad_periode = get_option('ads_period'); //periode ad display
				} else {
				    $ad_periode = $ActiveAds;
				}
				$meta_count = get_post_meta($cpt_id, "expired_date", true);

				if ($meta_count == '' ) {
                    if($ad_periode == 0) {
                        $period_date = 0;
                    } else {
                        $date = strtotime("+$ad_periode day", strtotime($post_date));
                        $period_date = date("Y-m-d", $date);
                    }

					update_post_meta( $cpt_id, 'expired_date', $period_date );
					update_post_meta( $cpt_id, 'send_email_remainder', 'no' );
				}

				if ( $_FILES ) { 
										
					$feat_file = $_FILES['_td_feat_image'];	
					$files = $_FILES["_td_image"]; 
					if ($feat_file['name']) { 
						$file = array( 
							'name' => $feat_file['name'],
		 					'type' => $feat_file['type'], 
							'tmp_name' => $feat_file['tmp_name'], 
							'error' => $feat_file['error'],
	 						'size' => $feat_file['size']
						);
						$_FILES = array ("_td_feat_image" => $file);
					
						foreach ($_FILES as $file => $array) {
							$newupload = trade_frontend_handle_attachment($file,$cpt_id);
							set_post_thumbnail($cpt_id, $newupload);
						}
					}
					$mediaArr = array();
					foreach ($files['name'] as $key => $value) {    
						if ($files['name'][$key]) { 
							$file = array( 
								'name' => $files['name'][$key],
								'type' => $files['type'][$key], 
								'tmp_name' => $files['tmp_name'][$key], 
								'error' => $files['error'][$key],
								'size' => $files['size'][$key]
							);
							$_FILES = array ("_td_image" => $file);           
							foreach ($_FILES as $file => $array) {        
								$newupload = trade_handle_attachment($file,$cpt_id); 
								$img_src = wp_get_attachment_image_src( $newupload , 'full');
								$mediaArr[$newupload] = $img_src[0];
							}       
						}
					}
					update_post_meta( $cpt_id, '_td_gallery', $mediaArr  );					
				}
				wp_set_object_terms( $cpt_id, $AdCategories, 'ad_category' );
				wp_set_object_terms( $cpt_id, $additionalCat, 'additional_cat' );
				wp_set_object_terms( $cpt_id, $typeofAd ,'ad_type' );
				wp_set_object_terms( $cpt_id, $conditionAd,'ad_condition');
				update_post_meta( $cpt_id, '_td_price', $price );
				if( isset($_POST['_td_price_negotiable'] )) {
					update_post_meta( $cpt_id, '_td_price_negotiable' , $_POST['_td_price_negotiable'] );
				}
				wp_set_object_terms( $cpt_id, $brandName, 'ad_brand');
				update_post_meta( $cpt_id, '_td_model', $_POST['_td_model'] );
				update_post_meta( $cpt_id, '_td_description', $_POST['_td_description'] );
				update_post_meta( $cpt_id, '_td_location', $locationName );
				update_post_meta( $cpt_id, 'locality', $cityName );
				update_post_meta( $cpt_id, 'administrative_area_level_1', $stateName );
				update_post_meta( $cpt_id, 'country', $countryName );
				update_post_meta( $cpt_id, 'postal_code', $zipCode );
				update_post_meta( $cpt_id, '_td_lattitude', $lattitude );
				update_post_meta( $cpt_id, '_td_longitude', $longitude );
				update_post_meta( $cpt_id, '_td_delivery', $Delivery );
				update_post_meta( $cpt_id, '_td_contact', $Contact );

				$permalink = get_permalink( $cpt_id );

				if(trim($_POST['edit-feature-plan']) != '') {
					$featurePlanID = trim($_POST['edit-feature-plan']);
					global $wpdb;
					$current_user = wp_get_current_user();
				    $userID = $current_user->ID;
					$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpcads_paypal WHERE main_id = $featurePlanID" );
					if ( $result ) {
						$featuredADS = 0;
						foreach ( $result as $info ) {
							if($info->status != "in progress" && $info->status != "pending") {
								$featuredADS++;
								if(empty($info->ads)) {
									$availableADS = esc_html__('Unlimited','trade');
									$infoAds = esc_html__('Unlimited','trade');
								} else {
									$availableADS = $info->ads - $info->used;
									$infoAds = $info->ads;
								}
								if(empty($info->days)) {
									$infoDays = esc_html__('Unlimited','trade');
								} else {
									$infoDays = $info->days;
								}
								if($info->used != esc_html__('Unlimited','trade') && $infoAds != esc_html__('Unlimited','trade') && $info->used == $infoAds) {
									$featPlanMesage = __( 'Please select another plan', 'trade' );
								} else {
									global $wpdb;
									$newUsed = $info->used +1;
									$update_data = array('used' => $newUsed);
								    $where = array('main_id' => $featurePlanID);
								    $update_format = array('%s');
								    $wpdb->update($wpdb->prefix.'wpcads_paypal', $update_data, $where, $update_format);
								    update_post_meta($post_id, 'post_price_plan_id', $featurePlanID );
									$dateActivation = date('m/d/Y H:i:s');
									update_post_meta($post_id, 'post_price_plan_activation_date', $dateActivation );
									$daysToExpire = $infoDays;
									$dateExpiration_Normal = date("m/d/Y H:i:s", strtotime("+ ".$daysToExpire." days"));
									update_post_meta($post_id, 'post_price_plan_expiration_date_normal', $dateExpiration_Normal );
									$dateExpiration = strtotime(date("m/d/Y H:i:s", strtotime("+ ".$daysToExpire." days")));
									update_post_meta($post_id, 'post_price_plan_expiration_date', $dateExpiration );
									update_post_meta($post_id, 'featured_post', "1" );
							    }
							}
						}
					}
				}
			global $trade_theme_options;
			$publish = $trade_theme_options['publish_ad'];
			$publish_url = get_page_link( $publish );
			wp_redirect( $publish_url.'?message=success' );		
		}
	}
}

get_header();
?>
<?php if( $hasError == true ) { ?>
<?php get_template_part( 'template-parts/trade', 'message' ); ?>
<?php } ?>
<div class="adpost-details aa">	
	<div class="row">
		<div class="col-md-8">
			<form class="form-item row" action="<?php the_permalink(); ?>" id="primaryPostForm" method="POST" enctype="multipart/form-data">				
				<fieldset>
					<div class="section postdetails aa">
						<h4><?php _e('Sell an item or service', 'trade'); ?> <span class="pull-right"> * <?php _e('Mandatory Fields', 'trade'); ?> </span></h4>									
						<div class="row form-group">
							<label class="col-sm-3"><?php echo esc_html__('Type of ad','trade'); ?><span class="required">*</span></label>
							<div class="col-sm-9 user-type">
								<?php $type_ad = get_terms( 'ad_type', 'orderby=count&hide_empty=0' ); ?>
							     	<select name="_td_type_of_ad" id="tdAdtype" class="form-control">
							     		<option value=""><?php echo esc_html__('Select Type of ad','trade'); ?> </option>
								<?php if ( ! empty( $type_ad ) && ! is_wp_error( $type_ad ) ){ ?>
								    <?php foreach ( $type_ad as $term ) { ?>
										<?php $selected = ( isset( $_POST['_td_type_of_ad'] ) && $_POST['_td_type_of_ad'] == $term->slug ) ? 'selected' : ''; ?>
								    	<option value="<?php echo esc_html( $term->slug ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $term->name ); ?></option>
								    <?php } ?>
								<?php }	?>
								</select>
							</div>
						</div>
						<div class="row form-group add-title aa">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Title for your Ad','trade'); ?><span class="required">*</span></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="text" name="_ad_title" value="<?php echo ( !empty( $_POST['_ad_title'] ) ) ? $_POST['ss'] : ''; ?>">
							</div>
						</div>
						<div class="row form-group add-image aa">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Featured photo','trade'); ?><span><?php echo esc_html__('(This will be your cover photo )','trade'); ?></span> </label>
							<div class="col-sm-9">
								<h5><i class="fa fa-upload" aria-hidden="true"></i><?php echo esc_html__('Select Single File to Upload','trade'); ?><span></span></h5>
								<div class="upload-section">
									<label for="upload-image-one aa">
										<input type="file" name="_td_feat_image" id="upload-image-one">
									</label>	
								</div>
							</div>
						</div>
						<div class="row form-group add-image aa">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Photos for your ad','trade'); ?><span><?php echo esc_html__('(This will be your ads gallery )','trade'); ?></span> </label>
							<div class="col-sm-9">
								<h5><i class="fa fa-upload" aria-hidden="true"></i><?php echo esc_html__('Select Files to Upload','trade'); ?><span><?php echo esc_html__('You can add maximum 4 images.','trade'); ?></span></h5>
								<div class="upload-section">									
								
									<label  for="upload-image-two aa">
										<input type="file" name="_td_image[]" id="upload-image-two" multiple="multiple">
									</label>	
								</div>
							</div>
						</div>
						
						<div class="row form-group select-price">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Price','trade'); ?><span class="required">*</span></label>
							<div class="col-sm-9">
								<?php 
									global $trade_theme_options;
									if(($trade_theme_options['sign-code'] == 'sign' )) {
										$sign_admin = $trade_theme_options['currency-sign'];
										$sign =  $sign_admin;
									}elseif(($trade_theme_options['sign-code'] == 'code' )) {
										$sign = $trade_theme_options['currency-code'];
									}
								?>
								<label><?php printf( '%s', $sign ); ?></label>
								<input type="text" name="_td_price" value="<?php echo ( !empty( $_POST['_td_price'] ) ) ? $_POST['_td_price'] : ''; ?>" class="form-control" id="text1">
								<div class="checkbox negotiable-check">
									<label for="negotiable"><input type="checkbox" name="_td_price_negotiable" id="negotiable" value="off" <?php echo ( isset( $_POST['_td_price_negotiable'] ) ) ? 'checked' : ''; ?>> <?php echo esc_html__('Negotiable','trade'); ?></label>	
								</div>
							</div>
						</div>
						<div class="row form-group ad-category">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Categories','trade'); ?><span class="required">*</span></label>
							<div class="col-sm-9 cat-dropdown">
								<div class="sub-cat-lavl" id="parent-cat">
									<?php $categories = get_terms( 'ad_category', 'parent=0&orderby=count&hide_empty=0' ); ?>
								    <select name="_td_categories" class="form-control ad_categories" id="ad_categories">
									<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ){ ?>
								    	<option value=""><?php _e( 'Select Categories', 'trade'); ?> </option>
									    <?php foreach ( $categories as $term ) { ?>
											<?php $selectedCat = ( isset( $_POST['_td_categories'] ) && $_POST['_td_categories'] == $term->slug ) ? 'selected' : ''; ?>
									    	<option value="<?php echo esc_html( $term->slug ); ?>" data-id="<?php echo esc_attr( $term->term_id ); ?>" <?php echo esc_attr( $selectedCat ); ?>><?php echo esc_html( $term->name ); ?></option>
									    <?php } ?>
									<?php } ?>
									</select>
								</div>
							</div>
						</div>
						
						
						
						
						
						<div class="row form-group item-description">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Description','trade'); ?></label>
							<div class="col-sm-9">
								<textarea class="form-control" name="_td_description" id="textarea" rows="8"><?php echo ( !empty( $_POST['_td_description'] ) ) ? $_POST['_td_description'] : ''; ?></textarea>		
							</div>
						</div>
						
						<div class="row form-group delivery-name">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Delivery','trade'); ?><span class="required">*</span></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="delivery" name="_td_delivery" value="<?php echo ( !empty( $_POST['_td_delivery'] ) ) ? $_POST['_td_delivery'] : ''; ?>">	
							</div>
						</div>
						<div class="row form-group contact-name">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Contact Number','trade'); ?><span class="required">*</span></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="contact" name="_td_contact" value="<?php echo ( !empty( $_POST['_td_contact'] ) ) ? $_POST['_td_contact'] : ''; ?>">	
							</div>
						</div>							
                        <div class="row form-group active-ads">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Actieve advertenties gedurende (dagen)','trade'); ?><span class="required">*</span></label>
							<div class="col-sm-9">
								<!--<input type="number" class="form-control" id="active-ads" name="_td_active_ads" value="<?php echo ( !empty( $_POST['_td_active_ads'] ) ) ? $_POST['_td_active_ads'] : ''; ?>" placeholder="<?php _e( 'ex, 5' , 'trade' ); ?>">-->
                                <select class="form-control" id="active-ads" name="_td_active_ads">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="0">Permanent</option>
                                </select>
							</div>
						</div>
					</div><!-- section -->		
					
					<div class="checkbox section agreement">
						<label for="send">
							<input type="checkbox" name="send" id="send" <?php echo ( isset( $_POST['send'] ) ) ? 'checked' : 'checked'; ?>>
							<?php $term_conditions = $trade_theme_options['_terms_condition']; printf( '%s', $term_conditions ) ; ?>
						</label>
						<?php wp_nonce_field( 'cpt_nonce_action', 'cpt_nonce_field' ); ?>
						<input type="hidden" name="_td_lattitude" id="_td_lattitude" value="<?php echo ( !empty( $_POST['_td_lattitude'] ) ) ? $_POST['_td_lattitude'] : ''; ?>" />
						<input type="hidden" name="_td_longitude" id="_td_longitude" value="<?php echo ( !empty( $_POST['_td_longitude'] ) ) ? $_POST['_td_longitude'] : ''; ?>" />
						<input type="hidden" name="_post_type" id="post_type" value="trade_ad" />
						<input type="submit" class="btn btn-primary" name="_post_ad" value="<?php echo esc_html__('Post Your Ad','trade'); ?>">
					</div><!-- section -->					
				</fieldset>
			</form><!-- form -->	
		</div>
	

		<!-- quick-rules -->	
		<div class="col-md-4">
			<div class="section quick-rules">
				<?php $quickRules = $trade_theme_options['_quick_rules']; printf( '%s', $quickRules );  ?>
			</div>
		</div><!-- quick-rules -->	
	</div><!-- photos-ad -->				
</div>

<?php get_footer(); ?>