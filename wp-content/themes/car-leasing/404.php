<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package Car_Leasing
 */

get_header();
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <main id="primary" class="site-main error-404 not-found">
                <header class="page-header">
                    <h1 class="page-title display-1 text-danger mb-4"><?php esc_html_e('404', 'car-leasing'); ?></h1>
                    <h2 class="mb-4"><?php esc_html_e('Oops! That page can&rsquo;t be found.', 'car-leasing'); ?></h2>
                </header><!-- .page-header -->

                <div class="page-content mb-5">
                    <p><?php esc_html_e('It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'car-leasing'); ?></p>

                    <?php get_search_form(); ?>

                    <div class="mt-5">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i><?php esc_html_e('Back to Homepage', 'car-leasing'); ?>
                        </a>
                    </div>
                </div><!-- .page-content -->
            </main><!-- #primary -->
        </div>
    </div>
</div>

<?php
get_footer();
