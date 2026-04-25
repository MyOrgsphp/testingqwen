<?php
/**
 * Main Header Template
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="header">
    <div class="logo">
        <a href="<?php echo esc_url(home_url('/')); ?>">
            WP Tube
        </a>
    </div>
    
    <form class="search-bar" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
        <input type="search" name="s" placeholder="<?php esc_attr_e('Search videos...', 'wp-tube'); ?>" value="<?php echo get_search_query(); ?>" />
        <button type="submit">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
            </svg>
        </button>
    </form>
    
    <div class="user-menu">
        <?php if (is_user_logged_in()) : ?>
            <a href="<?php echo esc_url(wp_tube_get_upload_url()); ?>" class="upload-btn">
                <?php _e('Upload', 'wp-tube'); ?>
            </a>
            <a href="<?php echo esc_url(get_author_posts_url(get_current_user_id())); ?>">
                <div class="channel-avatar" style="background-image: url(<?php echo get_avatar_url(get_current_user_id()); ?>); background-size: cover;"></div>
            </a>
        <?php else : ?>
            <a href="<?php echo esc_url(wp_login_url()); ?>" class="btn"><?php _e('Sign In', 'wp-tube'); ?></a>
        <?php endif; ?>
    </div>
</header>

<div class="main-content">
    <aside class="sidebar">
        <nav>
            <ul>
                <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('🏠 Home', 'wp-tube'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/?post_type=video')); ?>"><?php _e('📺 All Videos', 'wp-tube'); ?></a></li>
                <?php if (is_user_logged_in()) : ?>
                    <li><a href="<?php echo esc_url(wp_tube_get_history_url()); ?>"><?php _e('🕐 Watch History', 'wp-tube'); ?></a></li>
                    <li><a href="<?php echo esc_url(wp_tube_get_playlists_url()); ?>"><?php _e('📋 Playlists', 'wp-tube'); ?></a></li>
                    <li><a href="<?php echo esc_url(wp_tube_get_liked_videos_url()); ?>"><?php _e('👍 Liked Videos', 'wp-tube'); ?></a></li>
                    <li><a href="<?php echo esc_url(wp_tube_get_subscriptions_url()); ?>"><?php _e('📢 Subscriptions', 'wp-tube'); ?></a></li>
                <?php endif; ?>
                <li><a href="<?php echo esc_url(home_url('/video-category/')); ?>"><?php _e('📁 Categories', 'wp-tube'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/channels/')); ?>"><?php _e('👥 Channels', 'wp-tube'); ?></a></li>
            </ul>
        </nav>
    </aside>
    
    <main class="content-area">
