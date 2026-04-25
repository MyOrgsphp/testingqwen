<?php
/**
 * Playlists Page Template
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
$playlists = get_user_meta($user_id, '_playlists', true);

if (!is_array($playlists)) {
    $playlists = array();
}
?>

<div class="playlists-page">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2><?php _e('My Playlists', 'wp-tube'); ?></h2>
        <button id="create-playlist-btn" class="btn btn-subscribe"><?php _e('+ New Playlist', 'wp-tube'); ?></button>
    </div>
    
    <?php if (!empty($playlists)) : ?>
        <div class="playlists-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            <?php foreach ($playlists as $playlist) : ?>
                <div class="playlist-card" data-playlist-id="<?php echo esc_attr($playlist['id']); ?>" style="background: #fff; padding: 20px; border-radius: 5px; cursor: pointer;">
                    <h3 style="margin-bottom: 10px;"><?php echo esc_html($playlist['name']); ?></h3>
                    <p style="color: #606060;"><?php echo count($playlist['videos']); ?> <?php _e('videos', 'wp-tube'); ?></p>
                    <p style="color: #999; font-size: 12px;"><?php echo date(__('F j, Y', 'wp-tube'), $playlist['created']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Playlist Detail Modal -->
        <div id="playlist-detail-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
            <div style="background: #fff; max-width: 900px; margin: 50px auto; padding: 30px; border-radius: 5px; max-height: 80vh; overflow-y: auto;">
                <h3 id="playlist-detail-title"></h3>
                <div id="playlist-videos" style="margin-top: 20px;"></div>
                <button id="close-playlist-detail" class="btn" style="margin-top: 20px;"><?php _e('Close', 'wp-tube'); ?></button>
            </div>
        </div>
    <?php else : ?>
        <p><?php _e('No playlists yet. Create your first playlist!', 'wp-tube'); ?></p>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Create new playlist
    $('#create-playlist-btn').on('click', function() {
        var playlistName = prompt('<?php _e("Enter playlist name:", "wp-tube"); ?>');
        if (playlistName) {
            $.ajax({
                url: wpTubeAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_tube_create_playlist',
                    playlist_name: playlistName,
                    nonce: wpTubeAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
    });
    
    // View playlist details
    $('.playlist-card').on('click', function() {
        var playlistId = $(this).data('playlist-id');
        var playlistName = $(this).find('h3').text();
        
        $('#playlist-detail-title').text(playlistName);
        $('#playlist-detail-modal').show();
        
        loadPlaylistVideos(playlistId);
    });
    
    // Close modal
    $('#close-playlist-detail').on('click', function() {
        $('#playlist-detail-modal').hide();
    });
    
    function loadPlaylistVideos(playlistId) {
        $.ajax({
            url: wpTubeAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_tube_get_playlists',
                nonce: wpTubeAjax.nonce
            },
            success: function(response) {
                if (response.success && response.data.playlists) {
                    var playlist = response.data.playlists.find(function(p) {
                        return p.id === playlistId;
                    });
                    
                    if (playlist && playlist.videos.length > 0) {
                        $.ajax({
                            url: wpTubeAjax.ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'wp_tube_get_playlist_videos',
                                video_ids: playlist.videos,
                                nonce: wpTubeAjax.nonce
                            },
                            success: function(videoResponse) {
                                if (videoResponse.success && videoResponse.data.videos) {
                                    var html = '<div class="video-grid">';
                                    videoResponse.data.videos.forEach(function(video) {
                                        html += '<div class="history-item" onclick="window.location.href=\'' + video.url + '\'">' +
                                            '<strong>' + video.title + '</strong><br>' +
                                            '<small>' + video.author + '</small>' +
                                            '</div>';
                                    });
                                    html += '</div>';
                                    $('#playlist-videos').html(html);
                                } else {
                                    $('#playlist-videos').html('<p><?php _e("No videos in this playlist", "wp-tube"); ?></p>');
                                }
                            }
                        });
                    } else {
                        $('#playlist-videos').html('<p><?php _e("No videos in this playlist", "wp-tube"); ?></p>');
                    }
                }
            }
        });
    }
});
</script>

<?php get_footer(); ?>
