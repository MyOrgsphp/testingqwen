<?php
/**
 * Subscriptions Page Template
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

get_header();

$user_id = get_current_user_id();
$subscriptions = get_user_meta($user_id, '_subscribed_channels', true);

$videos = array();
$subscribed_channels = array();

if (is_array($subscriptions) && !empty($subscriptions)) {
    $subscribed_channels = $subscriptions;
    
    // Get videos from subscribed channels
    $args = array(
        'post_type' => 'video',
        'posts_per_page' => 50,
        'post_status' => 'publish',
        'author__in' => $subscriptions,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    $videos_query = new WP_Query($args);
    $videos = $videos_query->posts;
}
?>

<div class="subscriptions-page">
    <h2><?php _e('Subscriptions', 'wp-tube'); ?></h2>
    
    <?php if (!empty($subscribed_channels)) : ?>
        <!-- Subscribed Channels -->
        <div style="margin-bottom: 30px;">
            <h3><?php _e('Your Channels', 'wp-tube'); ?></h3>
            <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px;">
                <?php foreach ($subscribed_channels as $channel_id) : 
                    $channel_name = get_the_author_meta('display_name', $channel_id);
                    $channel_avatar = get_avatar_url($channel_id, array('size' => 80));
                ?>
                    <a href="<?php echo get_author_posts_url($channel_id); ?>" style="text-align: center;">
                        <img src="<?php echo esc_url($channel_avatar); ?>" alt="<?php echo esc_attr($channel_name); ?>" style="width: 80px; height: 80px; border-radius: 50%;"/>
                        <div style="font-size: 12px; margin-top: 5px; max-width: 80px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo esc_html($channel_name); ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Videos from subscribed channels -->
        <h3><?php _e('Latest from your subscriptions', 'wp-tube'); ?></h3>
        
        <?php if (!empty($videos)) : ?>
            <div class="video-grid">
                <?php foreach ($videos as $video) : setup_postdata($video); ?>
                    <?php 
                    global $post;
                    $post = $video;
                    setup_postdata($post);
                    ?>
                    <article class="video-card" data-video-id="<?php echo esc_attr($video->ID); ?>">
                        <a href="<?php echo get_permalink($video->ID); ?>">
                            <?php if (has_post_thumbnail($video->ID)) : ?>
                                <?php echo get_the_post_thumbnail($video->ID, 'video-thumbnail', array('class' => 'thumbnail')); ?>
                            <?php else : ?>
                                <div class="thumbnail" style="background: #000; display: flex; align-items: center; justify-content: center; color: #fff;">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M10 16.5l6-4.5-6-4.5v9zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </a>
                        
                        <div class="video-info">
                            <h3 class="video-title">
                                <a href="<?php echo get_permalink($video->ID); ?>"><?php echo get_the_title($video->ID); ?></a>
                            </h3>
                            <div class="channel-name">
                                <?php echo get_the_author_meta('display_name', $video->post_author); ?>
                            </div>
                            <div class="video-meta">
                                <?php echo number_format(get_post_meta($video->ID, '_video_views', true)); ?> <?php _e('views', 'wp-tube'); ?> • 
                                <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ' . __('ago', 'wp-tube'); ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; wp_reset_postdata(); ?>
            </div>
        <?php else : ?>
            <p><?php _e('No new videos from your subscriptions yet.', 'wp-tube'); ?></p>
        <?php endif; ?>
    <?php else : ?>
        <p><?php _e('You haven\'t subscribed to any channels yet.', 'wp-tube'); ?></p>
        <p><a href="<?php echo home_url('/channels/'); ?>"><?php _e('Browse channels to subscribe', 'wp-tube'); ?></a></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
