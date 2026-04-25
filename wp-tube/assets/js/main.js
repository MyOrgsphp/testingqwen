/**
 * WP Tube Main JavaScript
 */

jQuery(document).ready(function($) {
    
    // Like/Dislike Video
    $(document).on('click', '.btn-like, .btn-dislike', function(e) {
        e.preventDefault();
        
        if (!wpTubeAjax.is_logged_in) {
            alert('<?php _e("Please log in to like videos", "wp-tube"); ?>');
            window.location.href = '<?php echo wp_login_url(); ?>';
            return;
        }
        
        var $btn = $(this);
        var videoId = $btn.data('video-id');
        var action = $btn.data('action');
        var ajaxAction = action === 'like' ? 'wp_tube_like_video' : 'wp_tube_dislike_video';
        
        $.ajax({
            url: wpTubeAjax.ajaxurl,
            type: 'POST',
            data: {
                action: ajaxAction,
                video_id: videoId,
                nonce: wpTubeAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var countSpan = $btn.find('.count');
                    countSpan.text(response.data.likes || response.data.dislikes);
                    
                    // Update button state
                    if (response.data.action === 'liked' || response.data.action === 'disliked') {
                        $btn.addClass('active');
                    } else {
                        $btn.removeClass('active');
                    }
                    
                    // If liked, remove dislike state and vice versa
                    if (action === 'like') {
                        $('.btn-dislike[data-video-id="' + videoId + '"]').removeClass('active');
                    } else {
                        $('.btn-like[data-video-id="' + videoId + '"]').removeClass('active');
                    }
                } else {
                    alert(response.data.message || '<?php _e("Action failed", "wp-tube"); ?>');
                }
            },
            error: function() {
                alert('<?php _e("An error occurred. Please try again.", "wp-tube"); ?>');
            }
        });
    });
    
    // Subscribe to Channel
    $(document).on('click', '.btn-subscribe', function(e) {
        e.preventDefault();
        
        if (!wpTubeAjax.is_logged_in) {
            alert('<?php _e("Please log in to subscribe", "wp-tube"); ?>');
            window.location.href = '<?php echo wp_login_url(); ?>';
            return;
        }
        
        var $btn = $(this);
        var channelId = $btn.data('channel-id');
        var isSubscribed = $btn.data('subscribed') === '1';
        
        $.ajax({
            url: wpTubeAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_tube_subscribe_channel',
                channel_id: channelId,
                nonce: wpTubeAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.action === 'subscribed') {
                        $btn.text('<?php _e("Subscribed", "wp-tube"); ?>');
                        $btn.data('subscribed', '1');
                    } else {
                        $btn.text('<?php _e("Subscribe", "wp-tube"); ?>');
                        $btn.data('subscribed', '0');
                    }
                } else {
                    alert(response.data.message || '<?php _e("Action failed", "wp-tube"); ?>');
                }
            },
            error: function() {
                alert('<?php _e("An error occurred. Please try again.", "wp-tube"); ?>');
            }
        });
    });
    
    // Save to Playlist
    $(document).on('click', '[data-action="save"]', function(e) {
        e.preventDefault();
        
        if (!wpTubeAjax.is_logged_in) {
            alert('<?php _e("Please log in to save videos", "wp-tube"); ?>');
            window.location.href = '<?php echo wp_login_url(); ?>';
            return;
        }
        
        var videoId = $(this).data('video-id');
        showPlaylistModal(videoId);
    });
    
    // Share Video
    $(document).on('click', '[data-action="share"]', function(e) {
        e.preventDefault();
        
        var shareUrl = window.location.href;
        
        if (navigator.share) {
            navigator.share({
                title: document.title,
                url: shareUrl
            }).catch(function(error) {
                console.log('Error sharing:', error);
            });
        } else {
            // Fallback: copy to clipboard
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(shareUrl).select();
            document.execCommand('copy');
            $temp.remove();
            alert('<?php _e("Link copied to clipboard!", "wp-tube"); ?>');
        }
    });
    
    // Playlist Modal
    function showPlaylistModal(videoId) {
        // Check if modal exists
        var $modal = $('#playlist-modal');
        
        if ($modal.length === 0) {
            // Create modal
            $modal = $('<div id="playlist-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">' +
                '<div style="background:#fff; max-width:500px; margin:100px auto; padding:20px; border-radius:5px;">' +
                '<h3><?php _e("Save to Playlist", "wp-tube"); ?></h3>' +
                '<div id="playlist-list" style="margin:20px 0; max-height:300px; overflow-y:auto;"></div>' +
                '<button id="create-new-playlist" class="btn" style="background:#f0f0f0;"><?php _e("+ Create New Playlist", "wp-tube"); ?></button>' +
                '<button id="close-playlist-modal" class="btn" style="float:right;"><?php _e("Close", "wp-tube"); ?></button>' +
                '</div>' +
                '</div>');
            
            $('body').append($modal);
            
            // Close modal
            $modal.on('click', '#close-playlist-modal, #playlist-modal', function(e) {
                if (e.target === this || e.target.id === 'close-playlist-modal') {
                    $modal.hide();
                }
            });
            
            // Create new playlist
            $modal.on('click', '#create-new-playlist', function() {
                var playlistName = prompt('<?php _e("Enter playlist name:", "wp-tube"); ?>');
                if (playlistName) {
                    createPlaylist(playlistName, videoId);
                }
            });
        }
        
        $modal.show();
        loadPlaylists(videoId);
    }
    
    // Load User Playlists
    function loadPlaylists(videoId) {
        $.ajax({
            url: wpTubeAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_tube_get_playlists',
                nonce: wpTubeAjax.nonce
            },
            success: function(response) {
                if (response.success && response.data.playlists) {
                    var html = '';
                    response.data.playlists.forEach(function(playlist) {
                        html += '<div class="playlist-item" data-playlist-id="' + playlist.id + '" style="padding:10px; border:1px solid #e5e5e5; margin-bottom:10px; cursor:pointer;">' +
                            '<strong>' + playlist.name + '</strong> (' + playlist.videos.length + ' videos)' +
                            '</div>';
                    });
                    $('#playlist-list').html(html);
                    
                    // Add to playlist on click
                    $('.playlist-item').on('click', function() {
                        var playlistId = $(this).data('playlist-id');
                        addToPlaylist(playlistId, videoId);
                    });
                }
            }
        });
    }
    
    // Create Playlist
    function createPlaylist(name, videoId) {
        $.ajax({
            url: wpTubeAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_tube_create_playlist',
                playlist_name: name,
                nonce: wpTubeAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    addToPlaylist(response.data.playlist.id, videoId);
                }
            }
        });
    }
    
    // Add to Playlist
    function addToPlaylist(playlistId, videoId) {
        $.ajax({
            url: wpTubeAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_tube_add_to_playlist',
                playlist_id: playlistId,
                video_id: videoId,
                nonce: wpTubeAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e("Video added to playlist!", "wp-tube"); ?>');
                    $('#playlist-modal').hide();
                } else {
                    alert(response.data.message || '<?php _e("Failed to add video", "wp-tube"); ?>');
                }
            }
        });
    }
    
    // Auto-hide sidebar on mobile
    $(window).on('resize', function() {
        if ($(window).width() <= 768) {
            $('.sidebar').hide();
        } else {
            $('.sidebar').show();
        }
    }).trigger('resize');
    
    // Mobile menu toggle
    $('<button class="mobile-menu-toggle" style="position:fixed; top:10px; left:10px; z-index:1001; display:none;">☰</button>')
        .prependTo('body')
        .on('click', function() {
            $('.sidebar').toggle();
        });
    
    if ($(window).width() <= 768) {
        $('.mobile-menu-toggle').show();
    }
});
