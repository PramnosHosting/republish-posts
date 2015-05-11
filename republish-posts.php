<?php
/**
 * Plugin Name:	Republish Posts
 * Description:	Allows a Wordpress administrator to Schedule a post to be republished.
 * Plugin URI:  https://github.com/PramnosHosting/republish-posts
 * Version:     1.0
 * Author:	Pramnos Hosting Ltd.
 * Author URI:	http://www.pramhost.com
 * License:     MIT
 */

// check wp
if ( ! function_exists( 'add_action' ) ) {
    return;
}

/**
 * Setup metaboxes and actions for the administration panel
 * @wp-hook	plugins_loaded
 * @return	void
 */
function rpInit()
{
    if ( ! is_admin() ) { //Some basic security check
        return;
    }
    require_once dirname( __FILE__ ) . '/includes/admin.php';
    add_action('add_meta_boxes_post', 'rpPostMeta');
    add_action('save_post_post', 'rpSavePostMeta');
}

add_action( 'plugins_loaded', 'rpInit' );
add_action( 'wp', 'rpSetupSchedule' );
add_action( 'rpUpdatePostsHourlyEvent', 'rpUpdatePostsHourly' );
register_deactivation_hook( __FILE__, 'rpDeactivation' );



/**
 * On deactivation, remove all functions from the scheduled action hook.
 */
function rpDeactivation()
{
    wp_clear_scheduled_hook( 'rpUpdatePostsHourlyEvent' );
}

/**
 * Setup the hook for the event to run every hour
 */
function rpSetupSchedule()
{
    if ( ! wp_next_scheduled( 'rpUpdatePostsHourlyEvent' ) ) {
            wp_schedule_event( time(), 'hourly', 'rpUpdatePostsHourlyEvent');
    }
}



/**
 * Here is the actual function to republish the posts
 * @wp-hook	rpUpdatePostsHourlyEvent
 * @return	void
 */
function rpUpdatePostsHourly()
{
    $args = array(
	'posts_per_page'   => -1,
	'meta_key'         => 'rpRepublishDate',
	'post_type'        => 'post',
	'post_status'      => 'publish',
	'suppress_filters' => false
    );
    $posts_array = get_posts( $args );
    foreach ($posts_array as $post) {
        #var_dump($post);
        $newDate = get_post_meta($post->ID, 'rpRepublishDate', true);
        if ((int)$newDate < time()) {
            $my_post = array(
                'ID' => $post->ID,
                'post_date' => date('Y-m-d H:i:s'),
                'post_status' => 'draft'
            );
            wp_update_post( $my_post );
            delete_post_meta($post->ID, 'rpRepublishDate');
            $my_post['post_status'] = 'publish';
            wp_update_post( $my_post );
        }
    }
}