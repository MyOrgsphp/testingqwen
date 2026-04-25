<?php
/**
 * Channel/Author Page Template
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$author_id = get_query_var('author');
$channel_name = get_the_author_meta('display_name', $author_id);
$channel_description = get_the_author_meta('channel_description', $author_id);
$channel_banner = get_the_author_meta('channel_banner', $author_id);
$channel_avatar = get_avatar_url($author_id, array('size' => 150));

// Get subscriber count
$subscriber_count = 0;
$args = array(
    'meta_key' => '_subscribed_channels',
    'count_total' => false,
);
$users = get_users($args);
foreach ($users as $user) {
    $subscriptions = get_user_meta($user->ID, '_subscribed_channels', true);
    if (is_array($subscriptions) && in_array($author_id, $subscriptions)) {
        $subscriber_count++;
    }
}

// Check if current user is subscribed
$is_subscribed = false;
if (is_user_logged_in()) {
    $current_user_id = get_current_user_id();
    $subscriptions = get_user_meta($current_user_id, '_subscribed_channels', true);
    $is_subscribed = is_array($subscriptions) && in_array($author_id, $subscriptions);
}

// Get channel videos
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$videos_args = array(
    'post_type' => 'video',
    'posts_per_page' => 20,
    'post_status' => 'publish',
    'author' => $author_id,
    'paged' => $paged,
    'orderby' => 'date',
    'order' => 'DESC',
);
$videos = new WP_Query($videos_args);
?>

<div class="channel-page">
    <?php if ($channel_banner) : ?>
        <div class="channel-banner" style="background-image: url(<?php echo esc_url($channel_banner); ?>); height: 200px; background-size: cover; background-position: center;"></div>
    <?php else : ?>
        <div class="channel-banner" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 200px;"></div>
    <?php endif; ?>
    
    <div class="channel-header" style="padding: 20px; display: flex; align-items: center; gap: 20px;">
        <img src="<?php echo esc_url($channel_avatar); ?>" alt="<?php echo esc_attr($channel_name); ?>" class="channel-avatar" style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid #fff; margin-top: -60px;"/>
        
        <div style="flex: 1;">
            <h1 style="font-size: 28px; margin-bottom: 5px;"><?php echo esc_html($channel_name); ?></h1>
            <div style="color: #606060;">
                <strong><?php echo number_format($subscriber_count); ?></strong> <?php _e('subscribers', 'wp-tube'); ?> • 
                <strong><?php echo number_format($videos->found_posts); ?></strong> <?php _e('videos', 'wp-tube'); ?>
            </div>
            <?php if ($channel_description) : ?>
                <p style="margin-top: 10px; max-width: 800px;"><?php echo esc_html($channel_description); ?></p>
            <?php endif; ?>
        </div>
        
        <?php if (is_user_logged_in() && get_current_user_id() != $author_id) : ?>
            <button class="btn btn-subscribe" data-channel-id="<?php echo esc_attr($author_id); ?>" data-subscribed="<?php echo $is_subscribed ? '1' : '0'; ?>" style="padding: 12px 24px; font-size: 16px;">
                <?php echo $is_subscribed ? __('Subscribed', 'wp-tube') : __('Subscribe', 'wp-tube'); ?>
            </button>
        <?php endif; ?>
    </div>
    
    <div class="channel-tabs" style="border-bottom: 1px solid #e5e5e5; padding: 0 20px;">
        <nav style="display: flex; gap: 20px;">
            <a href="#" class="tab active" style="padding: 15px 0; border-bottom: 3px solid #ff0000; font-weight: bold;"><?php _e('Videos', 'wp-tube'); ?></a>
            <a href="#" class="tab" style="padding: 15px 0; color: #606060;"><?php _e('Playlists', 'wp-tube'); ?></a>
            <a href="#" class="tab" style="padding: 15px 0; color: #606060;"><?php _e('About', 'wp-tube'); ?></a>
        </nav>
    </div>
    
    <div class="channel-content" style="padding: 20px;">
        <h2 style="margin-bottom: 20px;"><?php _e('Uploads', 'wp-tube'); ?></h2>
        
        <?php if ($videos->have_posts()) : ?>
            <div class="video-grid">
                <?php while ($videos->have_posts()) : $videos->the_post(); ?>
                    <?php get_template_part('template-parts/video', 'card'); ?>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <div class="pagination" style="margin-top: 30px; text-align: center;">
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
            <p><?php _e('No videos uploaded yet.', 'wp-tube'); ?></p>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
