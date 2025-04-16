<?php
/**
 * The main template file
 *
 * @package Car_Leasing
 */

get_header();
?>

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-8">
            <main id="primary" class="site-main">

                <?php
                if (have_posts()) :

                    if (is_home() && !is_front_page()) :
                        ?>
                        <header>
                            <h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
                        </header>
                        <?php
                    endif;

                    /* Start the Loop */
                    while (have_posts()) :
                        the_post();

                        /*
                         * Include the Post-Type-specific template for the content.
                         * If you want to override this in a child theme, then include a file
                         * called content-___.php (where ___ is the Post Type name) and that will be used instead.
                         */
                        get_template_part('template-parts/content', get_post_type());

                    endwhile;

                    the_posts_navigation();

                else :

                    get_template_part('template-parts/content', 'none');

                endif;
                ?>

            </main><!-- #primary -->
        </div>
        <div class="col-md-4">
            <?php get_sidebar(); ?>
        </div>
    </div>
</div>

<?php
get_footer();
