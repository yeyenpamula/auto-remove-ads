<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Plugin Name: Automatic Remove Ads
 * Description: This plugin is simple and created for auto remove and auto extend display ads on classified website
 * Version: 1.1.5
 * Author: Yeyen Pamula
 * Author URI: https://tsabitlabs.com
 */

add_action( 'admin_menu', 'ara_create_plugin_settings_page' );

    function ara_create_plugin_settings_page() {
    	// Add the menu item and page
    	$page_title = 'Automatic Remove Ads';
    	$menu_title = 'Auto Remove Ads';
    	$capability = 'manage_options';
    	$slug = 'auto-remove-ads';
    	$callback = 'ara_plugin_settings_page_content';
    	$icon = 'dashicons-admin-plugins';
    	$position = 100;

    	add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
    }

    function ara_plugin_settings_page_content() {

        if( $_POST['updated'] === 'true' ) {
            ara_handle_form();
        } ?> 
    	<div class="wrap">
    		<h2>Automatic Remove and Extend Ads Display Setting</h2>
    		<form method="POST">
                <input type="hidden" name="updated" value="true" />
                <?php wp_nonce_field( 'awesome_update', 'awesome_form' ); ?>
                <table class="form-table">
                	<tbody>
                        <tr>
                    		<th><label for="ads_period">Active Ads Periods (day)</label></th>
                    		<td><input name="ads_period" id="ads_period" type="text" value="<?php echo get_option('ads_period'); ?>" class="regular-text" /></td>
                    	</tr>
                        <tr>
                    		<th><label for="extend_ads_period">Extend Active Ads Period (day)</label></th>
                    		<td><input name="extend_ads_period" id="extend_ads_period" type="text" value="<?php echo get_option('extend_ads_period'); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                    		<th><label for="ad_remider_day">Day before Ads expired (for send email reminder)</label></th>
                    		<td><input name="ad_reminder_day" id="ad_reminder_day" type="text" value="<?php echo get_option('ad_reminder_day'); ?>" class="regular-text" /></td>
                    	</tr>
                        <tr>
                    		<th><label for="email_sender">Email Sender</label></th>
                    		<td><input name="email_sender" id="email_sender" type="text" value="<?php echo get_option('email_sender'); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                    		<th><label for="email_subject">Email Subject</label></th>
                    		<td><input name="email_subject" id="email_subject" type="text" value="<?php echo get_option('email_subject'); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                    		<th><label for="email_reminder">Email Body</label></th>
                    		<td><textarea name="email_reminder" id="email_reminder" class="regular-text" rows="10"><?php echo get_option('email_reminder'); ?></textarea></td>
                        </tr>
                        <tr>
                    		<th><label for="cronjob_schedule">Cronjob Schedule</label></th>
                    		<td><input name="cronjob_schedule" id="cronjob_schedule" type="text" value="<?php echo get_option('cronjob_schedule'); ?>" class="regular-text" /></td>
                    	</tr>
                	</tbody>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save">
                </p>
    		</form>
    	</div> <?php
    }

    function ara_handle_form() {
        if( ! isset( $_POST['awesome_form'] ) || ! wp_verify_nonce( $_POST['awesome_form'], 'awesome_update' ) ) { ?>
           <div class="error">
               <p>Sorry, your nonce was not correct. Please try again.</p>
           </div> <?php
           exit;
        } else {
            $ads_period = sanitize_text_field( $_POST['ads_period'] );
            $extend_ads_period = sanitize_text_field( $_POST['extend_ads_period'] );
            $ad_reminder_day = sanitize_text_field( $_POST['ad_reminder_day'] );
            $email_sender = sanitize_text_field( $_POST['email_sender'] );
            $email_subject = sanitize_text_field( $_POST['email_subject'] );
            $email_reminder = $_POST['email_reminder'];
            $cronjob_schedule = sanitize_text_field( $_POST['cronjob_schedule'] );

            //if( in_array( $username, $valid_usernames ) && in_array( $email, $valid_emails ) ){
                update_option( 'ads_period', $ads_period );
                update_option( 'extend_ads_period', $extend_ads_period );
                update_option( 'ad_reminder_day', $ad_reminder_day );
                update_option( 'email_sender', $email_sender);
                update_option( 'email_subject', $email_subject);
                update_option( 'email_reminder', $email_reminder );
                update_option( 'cronjob_schedule', $cronjob_schedule );
                
                ?>
                <div class="updated">
                    <p>Settings were saved!</p>
                </div> <?php
            //} else { ?>
                <!--<div class="error">
                    <p>Your input fields were invalid.</p>
                </div> --><?php
            //}
        }
    }

add_action( 'post_updated', 'ara_add_expiration_date', 10, 3 );
function ara_add_expiration_date( $post_id, $post_after, $post_before ) {
    
    $post_type = get_post_type( $post_id );
    $post_date = $post_after->post_date;
    $ad_periode = get_option('ads_period'); //periode ad display
    $meta_count = get_post_meta($post_id, "expired_date", true);
    
    if ( $post_type == 'trade_ad' AND $meta_count == '' ) {

        //if( !wp_is_post_revision($post_id) ) {

            //$month = date( "m",strtotime( $event_datee ) );
            //$date = $ad_periode;
            $date = strtotime("+$ad_periode day", strtotime($post_date));
            $period_date = date("Y-m-d", $date);
            update_post_meta( $post_id, 'expired_date', $period_date );
            update_post_meta( $post_id, 'send_email_remainder', 'no' );
        //}

    }
}

function ara_update_ads_period($post_id) {
    $expired = get_post_meta($post_id, "expired_date", true);
    $extend = get_option('extend_ads_period');
    $extend_date = strtotime("+$extend day", strtotime($expired));
    $new_period = date('Y-m-d', $extend_date);
    update_post_meta( $post_id, 'expired_date', $new_period );
    update_post_meta( $post_id, 'send_email_remainder', 'no' );
}

/**
 * Schedules
 *
 * @param array $schedules
 *
 * @return array
 */
function ara_cronjob_schedules( $schedules ) {
    $schedules['auto_remove_ads_schedule'] = array(
        'interval' => get_option('cronjob_schedule'),
        'display'  => 'Auto Remove and Extend Display Ads',
    );

    return $schedules;
}
add_filter( 'cron_schedules', 'ara_cronjob_schedules', 10, 1 );

/**
 * Activate
 */
function ara_cronjob_activate() {
    if ( ! wp_next_scheduled( 'ara_send_email' ) ) {
        wp_schedule_event( time(), 'auto_remove_ads_schedule', 'ara_send_email' );
        //wp_schedule_event( strtotime('23:59:59'), 'daily', 'send_email' );
    }

    if ( ! wp_next_scheduled( 'ara_remove_ads' ) ) {
		wp_schedule_event( time(), 'auto_remove_ads_schedule', 'ara_remove_ads' );
        //wp_schedule_event( strtotime('23:59:59'), 'daily', 'remove_ads' );
    }
}
register_activation_hook( __FILE__, 'ara_cronjob_activate' );

/**
 * Deactivate
 */
function ara_cronjob_deactivate() {
    wp_unschedule_event( wp_next_scheduled( 'ara_send_email' ), 'ara_send_email' );
    wp_unschedule_event( wp_next_scheduled( 'ara_remove_ads' ), 'ara_remove_ads' );
}
register_deactivation_hook( __FILE__, 'ara_cronjob_deactivate' );

/**
 * Cronjob to send email
 */
function ara_send_email() {
    $ad_reminder_day = get_option( 'ad_reminder_day' ); //day befor ads expored then send email reminder
    $today = date('Y-m-d');
    $expired_date = strtotime("+$ad_reminder_day day", strtotime($today));
    $expired = date('Y-m-d', $expired_date);

    // The Query
    $the_query = new WP_Query( array('post_type' => 'trade_ad', 'meta_key' => 'expired_date', 'meta_value' => $expired, 'no_found_rows' => true, 'cache_results' => false) );
    
    // The Loop
    if ( $the_query->have_posts() ) {
        while ( $the_query->have_posts() ) {
            $the_query->the_post(); global $post;

            $post_id = $post->ID;
            $title = $post->post_title;
            $author_email = get_the_author_meta('user_email', $post->post_author);
            $author_name = get_the_author_meta('first_name', $post->post_author);

			$email_subject = get_option('email_subject');
            $email_sender = get_option('email_sender');
            $extend_period = get_option('extend_ads_period');
            $link = get_home_url().'/?ads_action=extend_period_display_ads&ads_id='.$post_id;
            $url = add_query_arg( '_wpnonce', wp_create_nonce( 'action' ), $link );
            $email_content = get_option('email_reminder');

            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            if(isset($email_sender)) {
                $headers[] = 'From:'.$email_sender.'';
            }
            //$headers = array('Content-Type: text/html; charset=UTF-8');
            $to = sanitize_email($author_email);
            $subject = sanitize_text_field(str_replace('{title}', $title, $email_subject));
            $message .= '<html><head><title>'.$subject.'</title><head><body>';
            $message .= str_replace(array('{name}', '{x}', '{y}', '{link}'), array($author_name, $reminder_day, $extend_period, $url), $email_content);
            $message .= '</body></html>';
            // $content = include './email-template.php';
            //check email send
            $receive_email = get_post_meta($post_id, "send_email_remainder", true);

            if($receive_email == 'no' || $receive_email == '' ) {
            
            update_post_meta( $post_id, 'send_email_remainder', 'yes' );
            wp_mail($to, $subject, $message, $headers);
            
            }

            // $content_type = function() { return 'text/html'; };
            // add_filter( 'wp_mail_content_type', $content_type );
            // wp_mail($to, $subject, $content);
            // remove_filter( 'wp_mail_content_type', $content_type );

        }
    } 

}
add_action( 'ara_send_email', 'ara_send_email' );

function ara_remove_ads() {
    // The Query
    $the_query = new WP_Query( array('post_type' => 'trade_ad', 'meta_query' => array( array(
        'key'     => 'expired_date',
        'value'   => date("Y-m-d"),
        'compare' => '<',
        'type'    => 'DATE'
    ), ), 'no_found_rows' => true, 'cache_results' => false) );
        
    // The Loop
    if ( $the_query->have_posts() ) {
        while ( $the_query->have_posts() ) {
            $the_query->the_post(); global $post;

            //set ads status "trash"
            $my_post = array(
                'ID'            => $post->ID,
                'post_status'   => 'trash',
            );
            wp_update_post( $my_post );
        }
    }
}
add_action( 'ara_remove_ads', 'ara_remove_ads' );


add_filter( 'query_vars', 'ara_add_query_vars');
/**
*   Add the 'ads_action' query variable so WordPress
*   won't remove it.
*/
function ara_add_query_vars($vars){
    $vars[] = "ads_action";
    return $vars;
}

/**
*   check for  'ads_action' query variable and do what you want if its there
*/
add_action('template_redirect', 'ara_my_template');

function ara_my_template($template) {
    global $wp_query;

    // If the 'ads_action' query var isn't appended to the URL,
    // don't do anything and return default
    if(!isset( $wp_query->query['ads_action'] ))
        return $template;

    // .. otherwise, 
    if($wp_query->query['ads_action'] == 'extend_period_display_ads') {
        if (!isset($_GET['_wpnonce'])) { ?>
            <div class="error">
               <p>Sorry, your nonce was not correct. Please try again.</p>
           </div> <?php
           exit;
        } else {

            //$id = $wp_query->query['ads_id'];
            $id = $_GET['ads_id'];

            if(get_post( $id ) == null) {
                return $template;
            }

            ara_update_ads_period($id);

            // echo '<h1>Update Ads period success! <a href="'.get_home_url().'">click here</a> to go home.</h1>';
            echo '<h1 style="text-align: center;">Advertentie periode gewijzigd! <a href="'.get_home_url().'">Click hier</a> om maar startpagina te gaan</h1>';
            exit;
        }
    }

    return $template;
}

// Add Expired Date to a column in WP-Admin
add_filter('manage_trade_ad_posts_columns', 'ara_posts_column_expired_date');
add_action('manage_trade_ad_posts_custom_column', 'ara_posts_custom_column_expired_date', 5, 2);

function ara_posts_column_expired_date($defaults) {
    $defaults['expired_date'] = __('Expired Date');
    return $defaults;
}
function ara_posts_custom_column_expired_date($column_name, $id) {
    if($column_name === 'expired_date') {
        $expired_date = get_post_meta($id, "expired_date", true);
        if( $expired_date == 0 ):
            echo "Permanent";
        else:
            echo $expired_date;
        endif;
    }
} ?>

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
				set_transient('postTitleError', 'Please enter a title.', 60);
				$hasError = true;
			} else {
				$postTitle = trim($_POST['_ad_title']);
			}

			if( empty($_POST['_td_type_of_ad']) && $_POST['_td_type_of_ad'] == '' ) {
				set_transient('typeofAdError', 'Please choose type of ad.', 60);
				$hasError = true;
			} else {
				$typeofAd = $_POST['_td_type_of_ad'];
			}

			if ( sizeof($_FILES['_td_image']['name']) > 4 ){
				set_transient('galleryError', 'Maximum file upload limit crossed.', 60);
				$hasError = true;
			} else {
				$hasError = false;
			}

			/*if( empty($_POST['_td_condition']) &&  !isset( $_POST['_td_condition'] ) ) {
				set_transient('conditionError', 'Please choose product condition.', 60);
				$hasError = true;
			} else {
				$conditionAd = $_POST['_td_condition'];
			}*/

			if(trim($_POST['_td_price']) === '') {
				set_transient('priceError', 'Please enter price.', 60);
				$hasError = true;
			} else {
				$price = trim($_POST['_td_price']);
			}

			/*if(trim($_POST['_td_brand_name']) === '') {
				set_transient('brandError', 'Please enter brand name.', 60);
				$hasError = true;
			} else {
				$brandName = $_POST['_td_brand_name'];
			}*/

			if( !empty($_POST['_td_categories']) ){
				$cat_ids = $_POST['_td_categories'];
				$AdCategories[] = $cat_ids;
				if( !empty($_POST['_td_childCat']) ){					
					$child_cat_id = $_POST['_td_childCat'];
					$AdCategories = array_merge($AdCategories,$child_cat_id);
				}
			}else{
				set_transient('CategoiesError', 'Please Select AD Categories.', 60);
				$cat_ids = '';
				$hasError = true;
			}

			if( !empty($_POST['_td_additional']) ){ 
				$additionalCat = $_POST['_td_additional'];
			}else{
				$additionalCat = '';
			}

			if(trim($_POST['_td_location']) === '') {
				set_transient('locationError', 'Please enter your location.', 60);
				$hasError = true;
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
				set_transient('contactError', 'Please enter your contact Number.', 60);
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
				set_transient('DeliveryError', 'Please enter your delivery type.', 60);
				$hasError = true;
			} else {
				$Delivery = trim($_POST['_td_delivery']);
			}

			if( empty($_POST['send']) &&  !isset( $_POST['send'] ) ) {
				set_transient('sendError', 'Please check the terms & condition.', 60);
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
<div class="adpost-details">	
	<div class="row">
		<div class="col-md-8">
			<form class="form-item row aa" action="<?php the_permalink(); ?>" id="primaryPostForm" method="POST" enctype="multipart/form-data">				
				<fieldset>
					<div class="section postdetails">
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
						<div class="row form-group add-title">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Title for your Ad','trade'); ?><span class="required">*</span></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="text" name="_ad_title" value="<?php echo ( !empty( $_POST['_ad_title'] ) ) ? $_POST['_ad_title'] : ''; ?>" placeholder="">
							</div>
						</div>
						<div class="row form-group add-image">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Featured photo','trade'); ?><span><?php echo esc_html__('(This will be your cover photo )','trade'); ?></span> </label>
							<div class="col-sm-9">
								<h5><i class="fa fa-upload" aria-hidden="true"></i><?php echo esc_html__('Selecteer één bestand om te uploaden','trade'); ?><span></span></h5>
								<!--<div class="upload-section">
									<label for="upload-image-one">
										<input type="file" name="_td_feat_image" id="upload-image-one">
									</label>	
								</div>-->
								<label for="file-upload" class="custom-file-upload"></label>
                                  <input id="upload-image-one" name='_td_feat_image' type="file">
							</div>
						</div>
						<div class="row form-group add-image">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Photos for your ad','trade'); ?><span><?php echo esc_html__('(Dit wordt uw advertentiegalerij )','trade'); ?></span> </label>
							<div class="col-sm-9">
								<h5><i class="fa fa-upload" aria-hidden="true"></i><?php echo esc_html__('Selecteer bestanden om te uploaden','trade'); ?><span><?php echo esc_html__('You can add maximum 4 images.','trade'); ?></span></h5>
								<div class="upload-section">									
								 <div id="image_preview"></div>
								<!--	<label  for="upload-image-two">
										<input type="file" name="_td_image[]" id="upload-image-two" multiple="multiple">
									</label>-->
									
									<label for="file-upload" id="multiplecount" class="custom-file-upload"></label>
   <input id="upload-image-two" name='_td_image[]' onchange="preview_image();" multiple="multiple" type="file" >
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
							<label class="col-sm-3 label-title"><?php echo esc_html__('Description','trade'); ?><span class="required">*</span></label>
							<div class="col-sm-9">
								<textarea class="form-control" name="_td_description" id="textarea" placeholder="<?php _e( 'Write few lines about your products', 'trade' ); ?>" rows="8"><?php echo ( !empty( $_POST['_td_description'] ) ) ? $_POST['_td_description'] : ''; ?></textarea>		
							</div>
						</div>

						<div class="row form-group location-name">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Location','trade'); ?><span class="required">*</span></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" name="_td_location" id="_td_location" value="<?php echo ( !empty( $_POST['_td_location'] ) ) ? $_POST['_td_location'] : ''; ?>" placeholder="<?php _e( 'ex, Los Angels,USA', 'trade' ); ?>">	
							</div>
						</div>

						<div class="row form-group city-name">
							<label class="col-sm-3 label-title"><?php echo esc_html__('City','trade'); ?></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" name="locality" id="locality" value="<?php echo ( !empty( $_POST['locality'] ) ) ? $_POST['locality'] : ''; ?>" placeholder="<?php _e( 'ex, Los Angels,USA' , 'trade' ); ?>">	
							</div>
						</div>

						<div class="row form-group state-name">
							<label class="col-sm-3 label-title"><?php echo esc_html__('State','trade'); ?></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" name="administrative_area_level_1" id="administrative_area_level_1" value="<?php echo ( !empty( $_POST['administrative_area_level_1'] ) ) ? $_POST['administrative_area_level_1'] : ''; ?>" placeholder="<?php _e( 'ex, Los Angels,USA' , 'trade' ); ?>">	
							</div>
						</div>

						<div class="row form-group zip-code">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Zip Code','trade'); ?></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" name="postal_code" id="postal_code" value="<?php echo ( !empty( $_POST['postal_code'] ) ) ? $_POST['postal_code'] : ''; ?>" placeholder="<?php _e( 'ex, 2014,8745' , 'trade' ); ?>">	
							</div>
						</div>
						
						<div class="row form-group country-name">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Country','trade'); ?></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" name="country" id="country" value="<?php echo ( !empty( $_POST['country'] ) ) ? $_POST['country'] : ''; ?>" placeholder="<?php _e( 'ex, United Kingdom' , 'trade' ); ?>">	
							</div>
						</div>
						<div class="row form-group delivery-name">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Delivery','trade'); ?><span class="required">*</span></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="delivery" name="_td_delivery" value="<?php echo ( !empty( $_POST['_td_delivery'] ) ) ? $_POST['_td_delivery'] : ''; ?>" placeholder="<?php _e( 'ophalen / wordt geleverd / in overleg' , 'trade' ); ?>">	
							</div>
						</div>
						<div class="row form-group contact-name">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Contact Number','trade'); ?><span class="required">*</span></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="contact" name="_td_contact" value="<?php echo ( !empty( $_POST['_td_contact'] ) ) ? $_POST['_td_contact'] : ''; ?>" placeholder="<?php _e( 'ex, 123456789' , 'trade' ); ?>">	
							</div>
						</div>
						<div class="row form-group active-ads">
							<label class="col-sm-3 label-title"><?php echo esc_html__('Active Ads During (days)','trade'); ?><span class="required">*</span></label>
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
<script type="text/javascript">
   $('#upload-image-one').change(function() {
       
  var i = $(this).prev('label').clone();
  var file = $('#upload-image-one')[0].files[0].name;
  $(this).prev('label').text(file);
});
/* $('#upload-image-two').change(function() {
     
  var i = $(this).prev('label').clone();
  var file = $('#upload-image-two')[0].files[0].name;
  $(this).prev('label').text(file);
});*/
function preview_image() 
{
 var total_file=document.getElementById("upload-image-two").files.length;
   //$('#multiplecount').text(total_file);
   var element = document.getElementById("multiplecount");
element.innerHTML = total_file + 'files';
}
</script>
<?php get_footer(); ?>