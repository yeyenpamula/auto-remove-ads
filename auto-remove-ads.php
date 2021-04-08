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