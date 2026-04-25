<?php
/**
 * Main Footer Template
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
    </main>
</div>

<footer style="background: #fff; padding: 20px; margin-top: 40px; border-top: 1px solid #e5e5e5;">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> WP Tube. <?php _e('All rights reserved.', 'wp-tube'); ?></p>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
