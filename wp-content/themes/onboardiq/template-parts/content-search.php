<?php
/**
 * Template part for displaying results in search pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package onboardiq
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <div class="post-content-text">
      <div class="entry-meta">
          <?php onboardiq_posted_on(); ?>
          <p style="padding-right: 15px; padding-left: 15px">|</p>
          <?php comments_number(); ?>
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
    </div>
</article><!-- #post-## -->
