<?php
/**
 * Template part for displaying posts
 *
 * @package Car_Leasing
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header mb-4">
        <?php
        if (is_singular()) :
            the_title('<h1 class="entry-title">', '</h1>');
        else :
            the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
        endif;

        if ('post' === get_post_type()) :
            ?>
            <div class="entry-meta mb-3">
                <span class="posted-on">
                    <i class="fas fa-calendar-alt me-1"></i> <?php echo get_the_date(); ?>
                </span>
                <span class="mx-2">|</span>
                <span class="posted-by">
                    <i class="fas fa-user me-1"></i> <?php the_author(); ?>
                </span>
                <?php if (has_category()) : ?>
                    <span class="mx-2">|</span>
                    <span class="cat-links">
                        <i class="fas fa-folder-open me-1"></i> <?php the_category(', '); ?>
                    </span>
                <?php endif; ?>
            </div><!-- .entry-meta -->
        <?php endif; ?>
    </header><!-- .entry-header -->

    <?php if (has_post_thumbnail()) : ?>
        <div class="entry-thumbnail mb-4">
            <?php the_post_thumbnail('large', array('class' => 'img-fluid rounded')); ?>
        </div>
    <?php endif; ?>

    <div class="entry-content">
        <?php
        the_content(
            sprintf(
                wp_kses(
                    /* translators: %s: Name of current post. Only visible to screen readers */
                    __('Continue reading<span class="screen-reader-text"> "%s"</span>', 'car-leasing'),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post(get_the_title())
            )
        );

        wp_link_pages(
            array(
                'before' => '<div class="page-links">' . esc_html__('Pages:', 'car-leasing'),
                'after'  => '</div>',
            )
        );
        ?>
    </div><!-- .entry-content -->

    <footer class="entry-footer mt-4">
        <?php if (has_tag()) : ?>
            <div class="tags-links mb-3">
                <i class="fas fa-tags me-1"></i> <?php the_tags('', ', '); ?>
            </div>
        <?php endif; ?>

        <?php
        edit_post_link(
            sprintf(
                wp_kses(
                    /* translators: %s: Name of current post. Only visible to screen readers */
                    __('Edit <span class="screen-reader-text">%s</span>', 'car-leasing'),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post(get_the_title())
            ),
            '<span class="edit-link">',
            '</span>'
        );
        ?>
    </footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->
