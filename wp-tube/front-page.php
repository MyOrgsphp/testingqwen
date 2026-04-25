<?php
/**
 * Front Page Template - Video Grid Homepage
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get latest videos
$args = array(
    'post_type' => 'video',
    'posts_per_page' => 20,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
);

$videos = new WP_Query($args);
?>

<div class="video-grid">
    <?php if ($videos->have_posts()) : while ($videos->have_posts()) : $videos->the_post(); ?>
        <?php get_template_part('template-parts/video', 'card'); ?>
    <?php endwhile; wp_reset_postdata(); else : ?>
        <p><?php _e('No videos found. Upload your first video!', 'wp-tube'); ?></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
