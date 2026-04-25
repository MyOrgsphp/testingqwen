<?php
/**
 * Upload Video Page Template
 */
if (!defined('ABSPATH')) {
    exit;
}

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

get_header();

// Get video categories
$categories = get_terms(array(
    'taxonomy' => 'video_category',
    'hide_empty' => false,
));
?>

<div class="upload-form">
    <h2><?php _e('Upload Video', 'wp-tube'); ?></h2>
    
    <form id="video-upload-form" method="post">
        <?php wp_nonce_field('wp_tube_upload_video', 'upload_nonce'); ?>
        
        <div class="form-group">
            <label for="video_title"><?php _e('Video Title *', 'wp-tube'); ?></label>
            <input type="text" name="video_title" id="video_title" required placeholder="<?php esc_attr_e('Enter video title', 'wp-tube'); ?>"/>
        </div>
        
        <div class="form-group">
            <label for="video_description"><?php _e('Description', 'wp-tube'); ?></label>
            <textarea name="video_description" id="video_description" rows="5" placeholder="<?php esc_attr_e('Tell viewers about your video', 'wp-tube'); ?>"></textarea>
        </div>
        
        <div class="form-group">
            <label for="video_url"><?php _e('Video URL *', 'wp-tube'); ?></label>
            <input type="url" name="video_url" id="video_url" required placeholder="<?php esc_attr_e('YouTube URL, Vimeo URL, or direct video file URL', 'wp-tube'); ?>"/>
            <small><?php _e('Supported: YouTube, Vimeo, Dailymotion, or MP4/WebM files', 'wp-tube'); ?></small>
        </div>
        
        <div class="form-group">
            <label for="video_duration"><?php _e('Duration (mm:ss)', 'wp-tube'); ?></label>
            <input type="text" name="video_duration" id="video_duration" placeholder="<?php esc_attr_e('e.g., 3:45', 'wp-tube'); ?>"/>
        </div>
        
        <div class="form-group">
            <label for="video_category"><?php _e('Category', 'wp-tube'); ?></label>
            <select name="video_category" id="video_category">
                <option value=""><?php _e('Select a category', 'wp-tube'); ?></option>
                <?php foreach ($categories as $category) : ?>
                    <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_html($category->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="video_thumbnail"><?php _e('Thumbnail Image', 'wp-tube'); ?></label>
            <input type="file" name="video_thumbnail" id="video_thumbnail" accept="image/*"/>
            <small><?php _e('Recommended size: 1280x720 pixels', 'wp-tube'); ?></small>
        </div>
        
        <button type="submit" class="submit-btn"><?php _e('Upload Video', 'wp-tube'); ?></button>
    </form>
    
    <div id="upload-status" style="margin-top: 20px; display: none;"></div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#video-upload-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'wp_tube_upload_video');
        formData.append('nonce', wpTubeAjax.nonce);
        
        $.ajax({
            url: wpTubeAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#upload-status').show().html('<?php _e('Uploading...', 'wp-tube'); ?>');
            },
            success: function(response) {
                if (response.success) {
                    $('#upload-status').html('<div style="color: green;">' + response.data.message + '</div>');
                    $('#video-upload-form')[0].reset();
                    setTimeout(function() {
                        window.location.href = '<?php echo home_url('/?post_type=video'); ?>';
                    }, 2000);
                } else {
                    $('#upload-status').html('<div style="color: red;">' + response.data.message + '</div>');
                }
            },
            error: function() {
                $('#upload-status').html('<div style="color: red;"><?php _e('Upload failed. Please try again.', 'wp-tube'); ?></div>');
            }
        });
    });
});
</script>

<?php get_footer(); ?>
