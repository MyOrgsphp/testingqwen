<?php
/**
 * WP Tube Theme Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

// Theme Setup
function wp_tube_setup() {
    add_theme_support('title_tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    
    // Register Navigation Menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'wp-tube'),
        'footer' => __('Footer Menu', 'wp-tube'),
    ));
    
    // Add image sizes for video thumbnails
    add_image_size('video-thumbnail', 640, 360, true);
    add_image_size('channel-avatar', 150, 150, true);
}
add_action('after_setup_theme', 'wp_tube_setup');

// Enqueue Scripts and Styles
function wp_tube_scripts() {
    wp_enqueue_style('wp-tube-style', get_stylesheet_uri());
    wp_enqueue_script('wp-tube-main', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.0', true);
    
    // Localize script for AJAX
    wp_localize_script('wp-tube-main', 'wpTubeAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp_tube_nonce'),
        'user_id' => get_current_user_id(),
        'is_logged_in' => is_user_logged_in(),
    ));
}
add_action('wp_enqueue_scripts', 'wp_tube_scripts');

// Register Custom Post Type: Video
function wp_tube_register_video_post_type() {
    $labels = array(
        'name' => __('Videos', 'wp-tube'),
        'singular_name' => __('Video', 'wp-tube'),
        'add_new' => __('Add New Video', 'wp-tube'),
        'add_new_item' => __('Add New Video', 'wp-tube'),
        'edit_item' => __('Edit Video', 'wp-tube'),
        'new_item' => __('New Video', 'wp-tube'),
        'view_item' => __('View Video', 'wp-tube'),
        'search_items' => __('Search Videos', 'wp-tube'),
        'not_found' => __('No videos found', 'wp-tube'),
        'not_found_in_trash' => __('No videos found in trash', 'wp-tube'),
    );
    
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'video'),
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields'),
        'menu_icon' => 'dashicons-video-alt3',
        'show_in_rest' => true,
        'capability_type' => 'post',
        'map_meta_cap' => true,
    );
    
    register_post_type('video', $args);
}
add_action('init', 'wp_tube_register_video_post_type');

// Register Custom Post Type: Channel (using user profiles)
function wp_tube_add_channel_fields() {
    add_meta_box('channel_info', __('Channel Information', 'wp-tube'), 'wp_tube_channel_info_callback', 'user', 'normal');
}
add_action('show_user_profile', 'wp_tube_add_channel_fields');
add_action('edit_user_profile', 'wp_tube_add_channel_fields');

function wp_tube_channel_info_callback($user) {
    ?>
    <h3><?php _e('Channel Information', 'wp-tube'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="channel_description"><?php _e('Channel Description', 'wp-tube'); ?></label></th>
            <td>
                <textarea name="channel_description" id="channel_description" rows="5" class="regular-text"><?php echo esc_textarea(get_the_author_meta('channel_description', $user->ID)); ?></textarea>
            </td>
        </tr>
        <tr>
            <th><label for="channel_banner"><?php _e('Channel Banner URL', 'wp-tube'); ?></label></th>
            <td>
                <input type="text" name="channel_banner" id="channel_banner" value="<?php echo esc_attr(get_the_author_meta('channel_banner', $user->ID)); ?>" class="regular-text" />
            </td>
        </tr>
    </table>
    <?php
}

function wp_tube_save_channel_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    if (isset($_POST['channel_description'])) {
        update_user_meta($user_id, 'channel_description', sanitize_textarea_field($_POST['channel_description']));
    }
    
    if (isset($_POST['channel_banner'])) {
        update_user_meta($user_id, 'channel_banner', esc_url_raw($_POST['channel_banner']));
    }
}
add_action('personal_options_update', 'wp_tube_save_channel_fields');
add_action('edit_user_profile_update', 'wp_tube_save_channel_fields');

// Custom Taxonomy: Video Category
function wp_tube_register_video_taxonomy() {
    $labels = array(
        'name' => __('Video Categories', 'wp-tube'),
        'singular_name' => __('Video Category', 'wp-tube'),
        'search_items' => __('Search Categories', 'wp-tube'),
        'all_items' => __('All Categories', 'wp-tube'),
        'parent_item' => __('Parent Category', 'wp-tube'),
        'edit_item' => __('Edit Category', 'wp-tube'),
        'update_item' => __('Update Category', 'wp-tube'),
        'add_new_item' => __('Add New Category', 'wp-tube'),
        'new_item_name' => __('New Category Name', 'wp-tube'),
        'menu_name' => __('Categories', 'wp-tube'),
    );
    
    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'video-category'),
        'show_in_rest' => true,
    );
    
    register_taxonomy('video_category', array('video'), $args);
}
add_action('init', 'wp_tube_register_video_taxonomy');

// Add custom fields for video meta data
function wp_tube_add_video_meta_boxes() {
    add_meta_box('video_meta', __('Video Details', 'wp-tube'), 'wp_tube_video_meta_callback', 'video', 'normal', 'high');
}
add_action('add_meta_boxes', 'wp_tube_add_video_meta_boxes');

function wp_tube_video_meta_callback($post) {
    wp_nonce_field('wp_tube_video_meta', 'wp_tube_video_meta_nonce');
    
    $video_url = get_post_meta($post->ID, '_video_url', true);
    $video_duration = get_post_meta($post->ID, '_video_duration', true);
    $video_views = get_post_meta($post->ID, '_video_views', true);
    
    ?>
    <p>
        <label for="video_url"><?php _e('Video URL/Embed Code:', 'wp-tube'); ?></label><br/>
        <textarea name="video_url" id="video_url" rows="3" class="widefat"><?php echo esc_textarea($video_url); ?></textarea>
        <small><?php _e('Enter YouTube embed URL or self-hosted video file URL', 'wp-tube'); ?></small>
    </p>
    <p>
        <label for="video_duration"><?php _e('Duration (mm:ss):', 'wp-tube'); ?></label><br/>
        <input type="text" name="video_duration" id="video_duration" value="<?php echo esc_attr($video_duration); ?>" class="regular-text"/>
    </p>
    <p>
        <label for="video_views"><?php _e('View Count:', 'wp-tube'); ?></label><br/>
        <input type="number" name="video_views" id="video_views" value="<?php echo esc_attr($video_views ? $video_views : 0); ?>" class="regular-text"/>
    </p>
    <?php
}

function wp_tube_save_video_meta($post_id) {
    if (!isset($_POST['wp_tube_video_meta_nonce']) || !wp_verify_nonce($_POST['wp_tube_video_meta_nonce'], 'wp_tube_video_meta')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['video_url'])) {
        update_post_meta($post_id, '_video_url', sanitize_textarea_field($_POST['video_url']));
    }
    
    if (isset($_POST['video_duration'])) {
        update_post_meta($post_id, '_video_duration', sanitize_text_field($_POST['video_duration']));
    }
    
    if (isset($_POST['video_views'])) {
        update_post_meta($post_id, '_video_views', absint($_POST['video_views']));
    }
}
add_action('save_post_video', 'wp_tube_save_video_meta');

// Like/Dislike functionality
function wp_tube_add_like_dislike_meta() {
    add_meta_box('video_likes', __('Likes & Dislikes', 'wp-tube'), 'wp_tube_like_dislike_callback', 'video', 'side');
}
add_action('add_meta_boxes', 'wp_tube_add_like_dislike_meta');

function wp_tube_like_dislike_callback($post) {
    $likes = get_post_meta($post->ID, '_video_likes', true);
    $dislikes = get_post_meta($post->ID, '_video_dislikes', true);
    ?>
    <p><strong><?php _e('Likes:', 'wp-tube'); ?></strong> <?php echo intval($likes ? $likes : 0); ?></p>
    <p><strong><?php _e('Dislikes:', 'wp-tube'); ?></strong> <?php echo intval($dislikes ? $dislikes : 0); ?></p>
    <?php
}

// AJAX handlers for like/dislike
add_action('wp_ajax_wp_tube_like_video', 'wp_tube_handle_like_video');
add_action('wp_ajax_nopriv_wp_tube_like_video', 'wp_tube_handle_like_video');

function wp_tube_handle_like_video() {
    check_ajax_referer('wp_tube_nonce', 'nonce');
    
    $video_id = intval($_POST['video_id']);
    $user_id = get_current_user_id();
    
    if (!$video_id || !$user_id) {
        wp_send_json_error(array('message' => __('Invalid request', 'wp-tube')));
    }
    
    $liked_videos = get_user_meta($user_id, '_liked_videos', true);
    if (!is_array($liked_videos)) {
        $liked_videos = array();
    }
    
    $likes = intval(get_post_meta($video_id, '_video_likes', true));
    
    if (in_array($video_id, $liked_videos)) {
        // Unlike
        $liked_videos = array_diff($liked_videos, array($video_id));
        $likes--;
    } else {
        // Like
        $liked_videos[] = $video_id;
        $likes++;
        
        // Remove from disliked if previously disliked
        $disliked_videos = get_user_meta($user_id, '_disliked_videos', true);
        if (is_array($disliked_videos) && in_array($video_id, $disliked_videos)) {
            $disliked_videos = array_diff($disliked_videos, array($video_id));
            update_user_meta($user_id, '_disliked_videos', $disliked_videos);
            $dislikes = intval(get_post_meta($video_id, '_video_dislikes', true));
            update_post_meta($video_id, '_video_dislikes', max(0, $dislikes - 1));
        }
    }
    
    update_user_meta($user_id, '_liked_videos', $liked_videos);
    update_post_meta($video_id, '_video_likes', max(0, $likes));
    
    wp_send_json_success(array(
        'likes' => max(0, $likes),
        'action' => in_array($video_id, $liked_videos) ? 'liked' : 'unliked'
    ));
}

add_action('wp_ajax_wp_tube_dislike_video', 'wp_tube_handle_dislike_video');
add_action('wp_ajax_nopriv_wp_tube_dislike_video', 'wp_tube_handle_dislike_video');

function wp_tube_handle_dislike_video() {
    check_ajax_referer('wp_tube_nonce', 'nonce');
    
    $video_id = intval($_POST['video_id']);
    $user_id = get_current_user_id();
    
    if (!$video_id || !$user_id) {
        wp_send_json_error(array('message' => __('Invalid request', 'wp-tube')));
    }
    
    $disliked_videos = get_user_meta($user_id, '_disliked_videos', true);
    if (!is_array($disliked_videos)) {
        $disliked_videos = array();
    }
    
    $dislikes = intval(get_post_meta($video_id, '_video_dislikes', true));
    
    if (in_array($video_id, $disliked_videos)) {
        // Undo dislike
        $disliked_videos = array_diff($disliked_videos, array($video_id));
        $dislikes--;
    } else {
        // Dislike
        $disliked_videos[] = $video_id;
        $dislikes++;
        
        // Remove from liked if previously liked
        $liked_videos = get_user_meta($user_id, '_liked_videos', true);
        if (is_array($liked_videos) && in_array($video_id, $liked_videos)) {
            $liked_videos = array_diff($liked_videos, array($video_id));
            update_user_meta($user_id, '_liked_videos', $liked_videos);
            $likes = intval(get_post_meta($video_id, '_video_likes', true));
            update_post_meta($video_id, '_video_likes', max(0, $likes - 1));
        }
    }
    
    update_user_meta($user_id, '_disliked_videos', $disliked_videos);
    update_post_meta($video_id, '_video_dislikes', max(0, $dislikes));
    
    wp_send_json_success(array(
        'dislikes' => max(0, $dislikes),
        'action' => in_array($video_id, $disliked_videos) ? 'disliked' : 'undisliked'
    ));
}

// Subscription functionality
add_action('wp_ajax_wp_tube_subscribe_channel', 'wp_tube_handle_subscribe');
add_action('wp_ajax_nopriv_wp_tube_subscribe_channel', 'wp_tube_handle_subscribe');

function wp_tube_handle_subscribe() {
    check_ajax_referer('wp_tube_nonce', 'nonce');
    
    $channel_id = intval($_POST['channel_id']);
    $user_id = get_current_user_id();
    
    if (!$channel_id || !$user_id) {
        wp_send_json_error(array('message' => __('Please log in to subscribe', 'wp-tube')));
    }
    
    $subscriptions = get_user_meta($user_id, '_subscribed_channels', true);
    if (!is_array($subscriptions)) {
        $subscriptions = array();
    }
    
    if (in_array($channel_id, $subscriptions)) {
        // Unsubscribe
        $subscriptions = array_diff($subscriptions, array($channel_id));
        $action = 'unsubscribed';
    } else {
        // Subscribe
        $subscriptions[] = $channel_id;
        $action = 'subscribed';
    }
    
    update_user_meta($user_id, '_subscribed_channels', $subscriptions);
    
    wp_send_json_success(array(
        'action' => $action,
        'count' => count($subscriptions)
    ));
}

// Video view tracking
function wp_tube_track_video_view($video_id) {
    $views = intval(get_post_meta($video_id, '_video_views', true));
    update_post_meta($video_id, '_video_views', $views + 1);
    
    // Add to watch history
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $history = get_user_meta($user_id, '_watch_history', true);
        if (!is_array($history)) {
            $history = array();
        }
        
        // Remove if already exists to move to top
        $history = array_diff($history, array($video_id));
        array_unshift($history, $video_id);
        
        // Keep only last 100 videos
        $history = array_slice($history, 0, 100);
        
        update_user_meta($user_id, '_watch_history', $history);
    }
}

// Playlist functionality
add_action('wp_ajax_wp_tube_create_playlist', 'wp_tube_handle_create_playlist');
add_action('wp_ajax_wp_tube_add_to_playlist', 'wp_tube_handle_add_to_playlist');
add_action('wp_ajax_wp_tube_remove_from_playlist', 'wp_tube_handle_remove_from_playlist');

function wp_tube_handle_create_playlist() {
    check_ajax_referer('wp_tube_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in', 'wp-tube')));
    }
    
    $playlist_name = sanitize_text_field($_POST['playlist_name']);
    $user_id = get_current_user_id();
    
    $playlists = get_user_meta($user_id, '_playlists', true);
    if (!is_array($playlists)) {
        $playlists = array();
    }
    
    $new_playlist = array(
        'id' => uniqid(),
        'name' => $playlist_name,
        'videos' => array(),
        'created' => current_time('timestamp')
    );
    
    $playlists[] = $new_playlist;
    update_user_meta($user_id, '_playlists', $playlists);
    
    wp_send_json_success(array('playlist' => $new_playlist));
}

function wp_tube_handle_add_to_playlist() {
    check_ajax_referer('wp_tube_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in', 'wp-tube')));
    }
    
    $user_id = get_current_user_id();
    $playlist_id = sanitize_text_field($_POST['playlist_id']);
    $video_id = intval($_POST['video_id']);
    
    $playlists = get_user_meta($user_id, '_playlists', true);
    if (!is_array($playlists)) {
        wp_send_json_error(array('message' => __('Playlist not found', 'wp-tube')));
    }
    
    foreach ($playlists as &$playlist) {
        if ($playlist['id'] === $playlist_id) {
            if (!in_array($video_id, $playlist['videos'])) {
                $playlist['videos'][] = $video_id;
            }
            break;
        }
    }
    
    update_user_meta($user_id, '_playlists', $playlists);
    wp_send_json_success(array('message' => __('Video added to playlist', 'wp-tube')));
}

function wp_tube_handle_remove_from_playlist() {
    check_ajax_referer('wp_tube_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in', 'wp-tube')));
    }
    
    $user_id = get_current_user_id();
    $playlist_id = sanitize_text_field($_POST['playlist_id']);
    $video_id = intval($_POST['video_id']);
    
    $playlists = get_user_meta($user_id, '_playlists', true);
    if (!is_array($playlists)) {
        wp_send_json_error(array('message' => __('Playlist not found', 'wp-tube')));
    }
    
    foreach ($playlists as &$playlist) {
        if ($playlist['id'] === $playlist_id) {
            $playlist['videos'] = array_diff($playlist['videos'], array($video_id));
            break;
        }
    }
    
    update_user_meta($user_id, '_playlists', $playlists);
    wp_send_json_success(array('message' => __('Video removed from playlist', 'wp-tube')));
}

// Helper function to get channel ID from video author
function wp_tube_get_channel_id($video_id) {
    $post = get_post($video_id);
    return $post ? $post->post_author : 0;
}

// Shortcode for video player
function wp_tube_video_player_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0,
    ), $atts);
    
    if (!$atts['id']) {
        return '';
    }
    
    $video_id = intval($atts['id']);
    $video_url = get_post_meta($video_id, '_video_url', true);
    
    if (!$video_url) {
        return '';
    }
    
    ob_start();
    ?>
    <div class="video-player-wrapper">
        <?php echo wp_oembed_get($video_url) ? wp_oembed_get($video_url) : '<video controls src="' . esc_url($video_url) . '"></video>'; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('wp_tube_player', 'wp_tube_video_player_shortcode');

// Frontend upload form handler
add_action('wp_ajax_wp_tube_upload_video', 'wp_tube_handle_frontend_upload');
add_action('wp_ajax_nopriv_wp_tube_upload_video', 'wp_tube_handle_frontend_upload');

function wp_tube_handle_frontend_upload() {
    check_ajax_referer('wp_tube_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to upload videos', 'wp-tube')));
    }
    
    $title = sanitize_text_field($_POST['video_title']);
    $description = sanitize_textarea_field($_POST['video_description']);
    $video_url = sanitize_textarea_field($_POST['video_url']);
    $duration = sanitize_text_field($_POST['video_duration']);
    $category = intval($_POST['video_category']);
    
    if (!$title || !$video_url) {
        wp_send_json_error(array('message' => __('Title and video URL are required', 'wp-tube')));
    }
    
    $post_data = array(
        'post_title' => $title,
        'post_content' => $description,
        'post_status' => 'pending', // Require admin approval
        'post_type' => 'video',
        'post_author' => get_current_user_id(),
    );
    
    $video_id = wp_insert_post($post_data);
    
    if (is_wp_error($video_id)) {
        wp_send_json_error(array('message' => $video_id->get_error_message()));
    }
    
    update_post_meta($video_id, '_video_url', $video_url);
    update_post_meta($video_id, '_video_duration', $duration);
    update_post_meta($video_id, '_video_views', 0);
    update_post_meta($video_id, '_video_likes', 0);
    update_post_meta($video_id, '_video_dislikes', 0);
    
    if ($category) {
        wp_set_post_terms($video_id, array($category), 'video_category');
    }
    
    wp_send_json_success(array(
        'message' => __('Video uploaded successfully! It will be published after admin review.', 'wp-tube'),
        'video_id' => $video_id
    ));
}

// Get user playlists
add_action('wp_ajax_wp_tube_get_playlists', 'wp_tube_handle_get_playlists');
add_action('wp_ajax_nopriv_wp_tube_get_playlists', 'wp_tube_handle_get_playlists');

function wp_tube_handle_get_playlists() {
    check_ajax_referer('wp_tube_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in', 'wp-tube')));
    }
    
    $user_id = get_current_user_id();
    $playlists = get_user_meta($user_id, '_playlists', true);
    
    if (!is_array($playlists)) {
        $playlists = array();
    }
    
    wp_send_json_success(array('playlists' => $playlists));
}

// Helper function to get upload page URL
function wp_tube_get_upload_url() {
    return home_url('/upload/');
}

// Helper function to get history page URL
function wp_tube_get_history_url() {
    return home_url('/watch-history/');
}

// Helper function to get playlists page URL
function wp_tube_get_playlists_url() {
    return home_url('/playlists/');
}

// Helper function to get liked videos URL
function wp_tube_get_liked_videos_url() {
    return home_url('/liked-videos/');
}

// Helper function to get subscriptions URL
function wp_tube_get_subscriptions_url() {
    return home_url('/subscriptions/');
}

// Rewrite rules for custom pages
add_action('init', 'wp_tube_custom_rewrite_rules');
function wp_tube_custom_rewrite_rules() {
    add_rewrite_rule('^upload/?$', 'index.php?wp_tube_page=upload', 'top');
    add_rewrite_rule('^watch-history/?$', 'index.php?wp_tube_page=history', 'top');
    add_rewrite_rule('^playlists/?$', 'index.php?wp_tube_page=playlists', 'top');
    add_rewrite_rule('^liked-videos/?$', 'index.php?wp_tube_page=liked', 'top');
    add_rewrite_rule('^subscriptions/?$', 'index.php?wp_tube_page=subscriptions', 'top');
}

// Handle custom page queries
add_filter('query_vars', 'wp_tube_query_vars');
function wp_tube_query_vars($vars) {
    $vars[] = 'wp_tube_page';
    return $vars;
}

add_action('template_redirect', 'wp_tube_template_redirect');
function wp_tube_template_redirect() {
    $page = get_query_var('wp_tube_page');
    
    if ($page === 'upload') {
        locate_template('page-upload.php', true);
        exit;
    } elseif ($page === 'history') {
        locate_template('page-history.php', true);
        exit;
    } elseif ($page === 'playlists') {
        locate_template('page-playlists.php', true);
        exit;
    } elseif ($page === 'liked') {
        locate_template('page-liked.php', true);
        exit;
    } elseif ($page === 'subscriptions') {
        locate_template('page-subscriptions.php', true);
        exit;
    }
}

// Clear watch history
add_action('wp_ajax_wp_tube_clear_history', 'wp_tube_handle_clear_history');
add_action('wp_ajax_nopriv_wp_tube_clear_history', 'wp_tube_handle_clear_history');

function wp_tube_handle_clear_history() {
    check_ajax_referer('wp_tube_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in', 'wp-tube')));
    }
    
    $user_id = get_current_user_id();
    delete_user_meta($user_id, '_watch_history');
    
    wp_send_json_success(array('message' => __('Watch history cleared', 'wp-tube')));
}

// Get playlist videos
add_action('wp_ajax_wp_tube_get_playlist_videos', 'wp_tube_handle_get_playlist_videos');
add_action('wp_ajax_nopriv_wp_tube_get_playlist_videos', 'wp_tube_handle_get_playlist_videos');

function wp_tube_handle_get_playlist_videos() {
    check_ajax_referer('wp_tube_nonce', 'nonce');
    
    $video_ids = isset($_POST['video_ids']) ? array_map('intval', $_POST['video_ids']) : array();
    
    if (empty($video_ids)) {
        wp_send_json_success(array('videos' => array()));
    }
    
    $args = array(
        'post_type' => 'video',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post__in' => $video_ids,
    );
    
    $videos_query = new WP_Query($args);
    $videos = array();
    
    foreach ($videos_query->posts as $video) {
        $videos[] = array(
            'id' => $video->ID,
            'title' => get_the_title($video->ID),
            'url' => get_permalink($video->ID),
            'author' => get_the_author_meta('display_name', $video->post_author),
        );
    }
    
    wp_send_json_success(array('videos' => $videos));
}
