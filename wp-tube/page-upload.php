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
            <label for="video_file"><?php _e('Video File *', 'wp-tube'); ?></label>
            <div class="video-upload-wrapper">
                <input type="hidden" name="video_file" id="video_file" value="" required />
                <button type="button" class="button upload-video-btn" id="upload-video-btn">
                    <?php _e('Select Video File', 'wp-tube'); ?>
                </button>
                <div id="video-preview" class="video-preview" style="display:none;">
                    <p class="filename"></p>
                    <progress id="upload-progress" value="0" max="100" style="width: 100%; display: none;"></progress>
                    <button type="button" class="button remove-video-btn" id="remove-video-btn">
                        <?php _e('Remove', 'wp-tube'); ?>
                    </button>
                </div>
            </div>
            <small><?php _e('Upload your video file directly (MP4, WebM, Ogg). Max size depends on server settings.', 'wp-tube'); ?></small>
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
    var mediaUploader;
    
    // Handle video file upload
    $('#upload-video-btn').on('click', function(e) {
        e.preventDefault();
        
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        mediaUploader = wp.media({
            title: '<?php _e('Choose Video File', 'wp-tube'); ?>',
            button: {
                text: '<?php _e('Use this video', 'wp-tube'); ?>'
            },
            library: {
                type: ['video/mp4', 'video/webm', 'video/ogg']
            },
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#video_file').val(attachment.url);
            $('#video-preview .filename').text(attachment.title);
            $('#video-preview').show();
            $('#upload-video-btn').hide();
        });
        
        mediaUploader.open();
    });
    
    // Remove selected video
    $('#remove-video-btn').on('click', function(e) {
        e.preventDefault();
        $('#video_file').val('');
        $('#video-preview').hide();
        $('#upload-video-btn').show();
    });
    
    // Handle form submission with file upload
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
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total * 100;
                        $('#upload-progress').val(percentComplete).show();
                    }
                }, false);
                return xhr;
            },
            beforeSend: function() {
                $('#upload-status').show().html('<?php _e('Uploading video...', 'wp-tube'); ?>');
                $('#upload-progress').show();
            },
            success: function(response) {
                if (response.success) {
                    $('#upload-status').html('<div style="color: green;">' + response.data.message + '</div>');
                    $('#video-upload-form')[0].reset();
                    $('#video-preview').hide();
                    $('#upload-video-btn').show();
                    $('#upload-progress').hide();
                    setTimeout(function() {
                        window.location.href = '<?php echo home_url('/?post_type=video'); ?>';
                    }, 2000);
                } else {
                    $('#upload-status').html('<div style="color: red;">' + response.data.message + '</div>');
                    $('#upload-progress').hide();
                }
            },
            error: function() {
                $('#upload-status').html('<div style="color: red;"><?php _e('Upload failed. Please try again.', 'wp-tube'); ?></div>');
                $('#upload-progress').hide();
            }
        });
    });
});
</script>

<?php get_footer(); ?>
