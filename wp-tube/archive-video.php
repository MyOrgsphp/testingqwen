<?php
/**
 * Archive Template for Video Post Type
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$args = array(
    'post_type' => 'video',
    'posts_per_page' => 20,
    'post_status' => 'publish',
    'paged' => $paged,
    'orderby' => 'date',
    'order' => 'DESC',
);

// Handle category filtering
if (is_tax('video_category')) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'video_category',
            'field' => 'slug',
            'terms' => get_queried_object()->slug,
        ),
    );
}

$videos = new WP_Query($args);
?>

<div class="archive-page">
    <h1 style="margin-bottom: 30px;">
        <?php 
        if (is_tax('video_category')) {
            single_term_title();
        } else {
            _e('All Videos', 'wp-tube');
        }
        ?>
    </h1>
    
    <?php if ($videos->have_posts()) : ?>
        <div class="video-grid">
            <?php while ($videos->have_posts()) : $videos->the_post(); ?>
                <?php get_template_part('template-parts/video', 'card'); ?>
            <?php endwhile; ?>
        </div>
        
        <!-- Pagination -->
        <div class="pagination" style="margin-top: 40px; text-align: center;">
            <?php
            echo paginate_links(array(
                'total' => $videos->max_num_pages,
                'current' => $paged,
                'prev_text' => __('« Previous', 'wp-tube'),
                'next_text' => __('Next »', 'wp-tube'),
            ));
            ?>
        </div>
    <?php else : ?>
        <p><?php _e('No videos found.', 'wp-tube'); ?></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
