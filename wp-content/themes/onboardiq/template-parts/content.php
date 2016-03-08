<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package onboardiq
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <div class="container">
    <div class="row whole-content-post">
      <div class="col-sm-5 post-thumbnail-img" <?php if (is_null(catch_that_image())){?>style="display:none"<?php } ?>>
        <img class="" src=<?php echo catch_that_image() ?>>
        <div><a href=<?php echo catch_that_image() ?>>Sourcing</a></div>
      </div>
      <div class="post-content-text<?php if (!is_null(catch_that_image())) echo ' col-sm-7'; ?>">
        <div class="entry-meta">
            <?php onboardiq_posted_on(); ?>
        </div><!-- .entry-meta -->
        <header class="entry-header">
            <?php
                if ( is_single() ) {
                    the_title( '<h1 class="entry-title">', '</h1>' );
                } else {
                    the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
                }

            if ( 'post' === get_post_type() ) : ?>
            <?php
            endif; ?>
        </header><!-- .entry-header -->

        <div class="entry-content">
            <?php
                the_excerpt( sprintf(
                    /* translators: %s: Name of current post. */
                    wp_kses( __( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'onboardiq' ), array( 'span' => array( 'class' => array() ) ) ),
                    the_title( '<span class="screen-reader-text">"', '"</span>', false )
                ) );
                wp_link_pages( array(
                    'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'onboardiq' ),
                    'after'  => '</div>',
                ) );
            ?>
            <a href="<?php echo get_permalink(); ?>"> Read more</a>
        </div><!-- .entry-content -->

        <footer class="entry-footer">
            <?php onboardiq_entry_footer(); ?>
        </footer><!-- .entry-footer -->
        </div><!-- .col-md-7 -->
      </div><!-- .row -->
  </div><!-- .container -->
</article><!-- #post-## -->
