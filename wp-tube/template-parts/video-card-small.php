<?php
/**
 * Small Video Card Template Part (for sidebar/recommendations)
 */
if (!defined('ABSPATH')) {
    exit;
}

$video_id = get_the_ID();
$video_duration = get_post_meta($video_id, '_video_duration', true);
$video_views = get_post_meta($video_id, '_video_views', true);
$author_id = get_the_author_meta('ID');
$channel_name = get_the_author_meta('display_name', $author_id);
?>

<article class="video-card-small" data-video-id="<?php echo esc_attr($video_id); ?>" style="display: flex; gap: 10px; cursor: pointer;">
    <a href="<?php the_permalink(); ?>" style="flex-shrink: 0; width: 168px;">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail(array(168, 94), array('class' => 'thumbnail', 'style' => 'width: 168px; height: 94px; object-fit: cover;')); ?>
        <?php else : ?>
            <div class="thumbnail" style="width: 168px; height: 94px; background: #000; display: flex; align-items: center; justify-content: center; color: #fff;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M10 16.5l6-4.5-6-4.5v9zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                </svg>
            </div>
        <?php endif; ?>
        
        <?php if ($video_duration) : ?>
            <span style="position: absolute; bottom: 5px; right: 5px; background: rgba(0,0,0,0.8); color: #fff; padding: 2px 5px; font-size: 10px; border-radius: 2px;">
                <?php echo esc_html($video_duration); ?>
            </span>
        <?php endif; ?>
    </a>
    
    <div class="video-info" style="flex: 1;">
        <h4 class="video-title" style="font-size: 14px; margin-bottom: 5px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h4>
        <div class="channel-name" style="color: #606060; font-size: 12px;">
            <?php echo esc_html($channel_name); ?>
        </div>
        <div class="video-meta" style="color: #606060; font-size: 12px;">
            <?php echo number_format($video_views ? $video_views : 0); ?> <?php _e('views', 'wp-tube'); ?> • 
            <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ' . __('ago', 'wp-tube'); ?>
        </div>
    </div>
</article>
