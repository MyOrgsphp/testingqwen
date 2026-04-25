<?php
/**
 * Template part for displaying posts navigation
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WP_Tube
 */
?>

<nav class="navigation pagination" role="navigation" aria-label="<?php esc_attr_e( 'Posts', 'wp-tube' ); ?>">
    <h2 class="screen-reader-text"><?php esc_html_e( 'Posts navigation', 'wp-tube' ); ?></h2>
    <div class="nav-links">
        <?php
        the_posts_pagination(
            array(
                'mid_size'  => 2,
                'prev_text' => sprintf(
                    '<span class="nav-prev-text">%s</span>',
                    esc_html__( 'Previous', 'wp-tube' )
                ),
                'next_text' => sprintf(
                    '<span class="nav-next-text">%s</span>',
                    esc_html__( 'Next', 'wp-tube' )
                ),
            )
        );
        ?>
    </div>
</nav>
