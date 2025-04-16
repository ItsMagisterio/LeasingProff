<?php
/**
 * The template for displaying all single posts
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

                    get_template_part('template-parts/content', 'single');

                    the_post_navigation(
                        array(
                            'prev_text' => '<span class="nav-subtitle">' . esc_html__('Previous:', 'car-leasing') . '</span> <span class="nav-title">%title</span>',
                            'next_text' => '<span class="nav-subtitle">' . esc_html__('Next:', 'car-leasing') . '</span> <span class="nav-title">%title</span>',
                        )
                    );

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
