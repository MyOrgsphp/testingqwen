<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WP_Tube
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
        <?php
        if ( is_singular() ) :
            the_title( '<h1 class="entry-title">', '</h1>' );
        else :
            the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
        endif;
        ?>
    </header>

    <?php if ( is_singular() ) : ?>
        <div class="entry-content">
            <?php
            the_content();

            wp_link_pages(
                array(
                    'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'wp-tube' ),
                    'after'  => '</div>',
                )
            );
            ?>
        </div>
    <?php endif; ?>

    <footer class="entry-footer">
        <span class="posted-on"><?php echo get_the_date(); ?></span>
        <?php if ( get_post_type() === 'video' ) : ?>
            <span class="video-meta"><?php echo esc_html( get_video_duration( get_the_ID() ) ); ?></span>
        <?php endif; ?>
    </footer>
</article>
