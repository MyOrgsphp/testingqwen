<?php
/**
 * Single Video Template - Watch Page
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) : the_post();
    $video_id = get_the_ID();
    $video_url = get_post_meta($video_id, '_video_url', true);
    $video_duration = get_post_meta($video_id, '_video_duration', true);
    $video_views = intval(get_post_meta($video_id, '_video_views', true));
    $video_likes = intval(get_post_meta($video_id, '_video_likes', true));
    $video_dislikes = intval(get_post_meta($video_id, '_video_dislikes', true));
    $author_id = get_the_author_meta('ID');
    $channel_name = get_the_author_meta('display_name', $author_id);
    $channel_description = get_the_author_meta('channel_description', $author_id);
    
    // Track view
    wp_tube_track_video_view($video_id);
    
    // Check user interactions
    $user_id = get_current_user_id();
    $is_liked = false;
    $is_disliked = false;
    $is_subscribed = false;
    
    if ($user_id) {
        $liked_videos = get_user_meta($user_id, '_liked_videos', true);
        $disliked_videos = get_user_meta($user_id, '_disliked_videos', true);
        $subscriptions = get_user_meta($user_id, '_subscribed_channels', true);
        
        $is_liked = is_array($liked_videos) && in_array($video_id, $liked_videos);
        $is_disliked = is_array($disliked_videos) && in_array($video_id, $disliked_videos);
        $is_subscribed = is_array($subscriptions) && in_array($author_id, $subscriptions);
    }
    
    // Get related videos
    $related_args = array(
        'post_type' => 'video',
        'posts_per_page' => 10,
        'post_status' => 'publish',
        'post__not_in' => array($video_id),
        'orderby' => 'rand',
    );
    $related_videos = new WP_Query($related_args);
?>

<div class="video-player-container">
    <div class="player-section">
        <div class="video-player">
            <?php 
            if ($video_url) {
                // Try oEmbed first (for YouTube, Vimeo, etc.)
                if (wp_oembed_get($video_url)) {
                    echo wp_oembed_get($video_url);
                } else {
                    // Self-hosted video
                    echo '<video controls width="100%" height="100%" style="background: #000;">';
                    echo '<source src="' . esc_url($video_url) . '" type="video/mp4">';
                    echo __('Your browser does not support the video tag.', 'wp-tube');
                    echo '</video>';
                }
            } else {
                echo '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #fff; background: #000;">';
                echo __('Video not available', 'wp-tube');
                echo '</div>';
            }
            ?>
        </div>
        
        <h1 class="video-title" style="font-size: 20px; margin-bottom: 10px;"><?php the_title(); ?></h1>
        
        <div class="video-actions">
            <div class="video-stats">
                <strong><?php echo number_format($video_views); ?></strong> <?php _e('views', 'wp-tube'); ?> • 
                <strong><?php echo get_the_date(); ?></strong>
            </div>
            
            <div class="action-buttons">
                <button class="btn btn-like" data-video-id="<?php echo esc_attr($video_id); ?>" data-action="like">
                    👍 <span class="count"><?php echo number_format($video_likes); ?></span>
                    <?php if ($is_liked) echo '<span class="active">Liked</span>'; ?>
                </button>
                <button class="btn btn-dislike" data-video-id="<?php echo esc_attr($video_id); ?>" data-action="dislike">
                    👎 <span class="count"><?php echo number_format($video_dislikes); ?></span>
                    <?php if ($is_disliked) echo '<span class="active">Disliked</span>'; ?>
                </button>
                <button class="btn" data-action="share">
                    <?php _e('Share', 'wp-tube'); ?>
                </button>
                <button class="btn" data-action="save" data-video-id="<?php echo esc_attr($video_id); ?>">
                    <?php _e('Save', 'wp-tube'); ?>
                </button>
            </div>
        </div>
        
        <div class="channel-info">
            <img src="<?php echo get_avatar_url($author_id, array('size' => 50)); ?>" alt="<?php echo esc_attr($channel_name); ?>" class="channel-avatar"/>
            <div>
                <div style="font-weight: bold;">
                    <a href="<?php echo esc_url(get_author_posts_url($author_id)); ?>"><?php echo esc_html($channel_name); ?></a>
                </div>
                <div style="color: #606060; font-size: 14px;">
                    <?php 
                    $subscriber_count = count_users_by_meta('_subscribed_channels', $author_id);
                    echo number_format($subscriber_count) . ' ' . __('subscribers', 'wp-tube');
                    ?>
                </div>
            </div>
            <button class="btn btn-subscribe" data-channel-id="<?php echo esc_attr($author_id); ?>" data-subscribed="<?php echo $is_subscribed ? '1' : '0'; ?>">
                <?php echo $is_subscribed ? __('Subscribed', 'wp-tube') : __('Subscribe', 'wp-tube'); ?>
            </button>
        </div>
        
        <div class="description">
            <h3><?php _e('Description', 'wp-tube'); ?></h3>
            <div style="margin-top: 10px;">
                <?php the_content(); ?>
            </div>
        </div>
        
        <!-- Comments Section -->
        <div style="margin-top: 30px;">
            <?php
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;
            ?>
        </div>
    </div>
    
    <!-- Related Videos Sidebar -->
    <aside class="related-videos" style="width: 400px;">
        <h3><?php _e('Related Videos', 'wp-tube'); ?></h3>
        <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
            <?php if ($related_videos->have_posts()) : while ($related_videos->have_posts()) : $related_videos->the_post(); ?>
                <?php get_template_part('template-parts/video', 'card-small'); ?>
            <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>
    </aside>
</div>

<?php endwhile; ?>

<?php get_footer(); ?>

<?php
// Helper function to count subscribers
function count_users_by_meta($meta_key, $meta_value) {
    global $wpdb;
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE %s",
        $meta_key,
        '%' . $wpdb->esc_like(serialize((string)$meta_value)) . '%'
    ));
    return intval($count);
}
?>
