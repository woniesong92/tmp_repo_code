<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package onboardiq
 */

get_header(); ?>
<a class="btn btn-primary" role="button" data-toggle="collapse" href=".widget-area" aria-expanded="false" aria-controls="collapseExample">
  <div class="sidebar-toggle-button"></div>
</a>
<div class="row">
  <?php get_sidebar(); ?>
    <div class="main-content">
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
              <div class="single-page-thumbnail-img" <?php if (is_null(catch_that_image())){?>style="display:none"<?php } ?>>
                <img src=<?php echo catch_that_image() ?>>
                <div><a href=<?php echo catch_that_image() ?>>Sourcing</a></div>
              </div>
              <div class="single-page-post-text">
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
                        the_content( sprintf(
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
                </div><!-- single-page-post-text -->
        </article><!-- #post-## -->
  </div><!--main-content -->
<?php
get_footer();
