<?php
/**
 * The template for displaying all pages
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
                while (have_posts()) :
                    the_post();

                    get_template_part('template-parts/content', 'page');

                    // If comments are open or we have at least one comment, load up the comment template.
                    if (comments_open() || get_comments_number()) :
                        comments_template();
                    endif;

                endwhile; // End of the loop.
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
