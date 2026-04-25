# WP Tube - YouTube-like WordPress Theme

A fully functional WordPress theme that replicates YouTube's core features including video upload, watch, like/dislike, history, playlists, channels, and subscriptions.

## Features

### Core Functionality
- **Upload Videos**: Users can upload videos via frontend form (YouTube embed, Vimeo, or self-hosted)
- **Watch Videos**: Clean video player page with related videos sidebar
- **Like/Dislike Videos**: AJAX-powered like/dislike system
- **Video History**: Automatically tracks watched videos for logged-in users
- **Playlists**: Create and manage video playlists
- **Channels**: Each user has a channel page showing their uploaded videos
- **Subscriptions**: Subscribe to channels and see their latest content

### Technical Features
- Custom Post Type: `video`
- Custom Taxonomy: `video_category`
- AJAX-powered interactions
- Responsive design
- oEmbed support (YouTube, Vimeo, Dailymotion)
- Self-hosted video support (MP4/WebM)

## Installation

1. **Copy Theme Files**
   ```
   Copy the wp-tube folder to: /wp-content/themes/wp-tube/
   ```

2. **Activate Theme**
   - Go to WordPress Admin → Appearance → Themes
   - Activate "WP Tube"

3. **Flush Rewrite Rules**
   - Go to Settings → Permalinks
   - Click "Save Changes" (this is important for custom URLs to work)

4. **Create Required Pages** (Optional - theme handles these automatically)
   The theme uses custom rewrite rules for these URLs:
   - `/upload/` - Video upload page
   - `/watch-history/` - Watch history page
   - `/playlists/` - Playlists management
   - `/liked-videos/` - Liked videos
   - `/subscriptions/` - Subscriptions feed

5. **Configure User Registration** (Optional)
   - Go to Settings → General
   - Check "Anyone can register" if you want users to sign up
   - Set default role to "Subscriber"

## Usage

### For Administrators

1. **Add Video Categories**
   - Go to Videos → Categories
   - Add categories like: Music, Gaming, Education, Entertainment, etc.

2. **Moderate Uploaded Videos**
   - User uploads are set to "Pending" status
   - Go to Videos → All Videos
   - Review and publish videos

3. **Edit Channel Information**
   - Go to Users → Your Profile
   - Fill in "Channel Description" and "Channel Banner URL"

### For Users

1. **Upload a Video**
   - Click "Upload" button in header
   - Fill in title, description, video URL
   - Select category (optional)
   - Submit for admin review

2. **Interact with Videos**
   - Like/Dislike videos
   - Subscribe to channels
   - Save videos to playlists
   - View watch history

3. **Manage Playlists**
   - Go to "Playlists" from sidebar
   - Create new playlists
   - Add/remove videos

## File Structure

```
wp-tube/
├── style.css                 # Main stylesheet with theme info
├── functions.php             # Theme functions and features
├── header.php                # Site header with navigation
├── footer.php                # Site footer
├── front-page.php            # Homepage with video grid
├── single-video.php          # Single video watch page
├── archive-video.php         # Video archive/category pages
├── author.php                # Channel/author page
├── page-upload.php           # Frontend upload form
├── page-history.php          # Watch history page
├── page-playlists.php        # Playlists management
├── page-liked.php            # Liked videos page
├── page-subscriptions.php    # Subscriptions feed
├── assets/
│   ├── js/
│   │   └── main.js           # JavaScript for interactions
│   └── css/                  # Additional CSS (if needed)
└── template-parts/
    ├── video-card.php        # Standard video card
    └── video-card-small.php  # Small video card for sidebar
```

## Database Schema

The theme uses WordPress meta tables to store:

### Post Meta (videos)
- `_video_url` - Video URL or embed code
- `_video_duration` - Duration in mm:ss format
- `_video_views` - View count
- `_video_likes` - Like count
- `_video_dislikes` - Dislike count

### User Meta
- `_liked_videos` - Array of liked video IDs
- `_disliked_videos` - Array of disliked video IDs
- `_watch_history` - Array of watched video IDs (last 100)
- `_playlists` - Array of playlist objects
- `_subscribed_channels` - Array of subscribed channel/user IDs
- `channel_description` - Channel bio
- `channel_banner` - Channel banner image URL

## Customization

### Changing Colors
Edit `style.css` to customize:
- Primary color (currently #ff0000 - YouTube red)
- Background colors
- Font families

### Adding Features
Extend `functions.php` to add:
- Video comments moderation
- Advanced search filters
- Video recommendations algorithm
- Analytics dashboard

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- jQuery (included with WordPress)

## Notes

1. **Video Hosting**: This theme supports:
   - YouTube embeds (recommended)
   - Vimeo embeds
   - Self-hosted videos (consider storage and bandwidth)

2. **Performance**: For production use:
   - Use a CDN for video delivery
   - Enable caching
   - Optimize images
   - Consider lazy loading

3. **Security**: 
   - Videos require admin approval before publishing
   - AJAX actions are nonce-protected
   - User capabilities are checked

## License

GNU General Public License v2 or later

## Support

For issues and feature requests, please create an issue in the repository.
