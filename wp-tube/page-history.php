<?php
/**
 * Watch History Page Template
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
$history = get_user_meta($user_id, '_watch_history', true);

$videos = array();
if (is_array($history) && !empty($history)) {
    $args = array(
        'post_type' => 'video',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post__in' => $history,
        'orderby' => 'post__in',
    );
    $videos_query = new WP_Query($args);
    $videos = $videos_query->posts;
}
?>

<div class="history-page">
    <h2><?php _e('Watch History', 'wp-tube'); ?></h2>
    
    <?php if (!empty($videos)) : ?>
        <div class="video-grid">
            <?php foreach ($videos as $video) : setup_postdata($video); ?>
                <?php 
                // Create a temporary post context
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
                            <?php echo number_format(get_post_meta($video->ID, '_video_views', true)); ?> <?php _e('views', 'wp-tube'); ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; wp_reset_postdata(); ?>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <button class="btn" onclick="clearHistory()" style="background: #f44336; color: white;"><?php _e('Clear Watch History', 'wp-tube'); ?></button>
        </div>
    <?php else : ?>
        <p><?php _e('No watch history yet. Start watching videos!', 'wp-tube'); ?></p>
    <?php endif; ?>
</div>

<script>
function clearHistory() {
    if (confirm('<?php _e("Are you sure you want to clear your watch history?", "wp-tube"); ?>')) {
        jQuery.post(wpTubeAjax.ajaxurl, {
            action: 'wp_tube_clear_history',
            nonce: wpTubeAjax.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            }
        });
    }
}
</script>

<?php get_footer(); ?>
