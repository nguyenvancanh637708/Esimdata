<?php
/**
 * Plugin Name: Load More Products
 * Description: AJAX Load More Products for WooCommerce.
 * Version: 1.0
 * Author: Your Name
 */

// Enqueue scripts
add_action('wp_enqueue_scripts', 'enqueue_load_more_products_script');
function enqueue_load_more_products_script() {
    wp_enqueue_script('load-more-products', plugin_dir_url(__FILE__) . 'load-more-products.js', array('jquery'), null, true);
    wp_localize_script('load-more-products', 'loadMoreProducts', array('ajax_url' => admin_url('admin-ajax.php')));
}

// AJAX handler for loading more products
add_action('wp_ajax_load_more_products', 'load_more_products');
add_action('wp_ajax_nopriv_load_more_products', 'load_more_products');

function load_more_products() {
    $show_all = isset($_POST['show_all']) && $_POST['show_all'] === '1';
    $initial_posts_per_page = 6;

    // Set up query arguments
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $show_all ? -1 : $initial_posts_per_page,
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => 'goi-cuoc',
            ),
        ),
    );

    // Query products
    $loop = new WP_Query($args);

    if ($loop->have_posts()) {
        while ($loop->have_posts()) : $loop->the_post();
            global $product;
            $networkName = get_field('nha_mang', $product->get_id());

            // Lấy giá thấp nhất
            if ($product->is_type('variable')) {
                $prices = $product->get_variation_prices();
                $min_price = min($prices['price']);
            } else {
                $min_price = $product->get_price();
            }
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="thumbnail">
                            <a href="<?php the_permalink(); ?>">
                                <?php echo $product->get_image('medium', array('class' => 'card-img-top')); ?>
                            </a>
                        </div>
                        <div class="content">
                            <h5 class="card-title productName">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h5>
                            <div class="rating mb-2 d-flex align-items-center">
                                <div class="star-rating me-2">
                                    <?php
                                    $average = $product->get_average_rating();
                                    $full_stars = floor($average);
                                    $half_star = ($average - $full_stars) >= 0.5;
                                    $empty_stars = 5 - ceil($average);

                                    for ($i = 0; $i < $full_stars; $i++) {
                                        echo '<i class="bi bi-star-fill text-warning"></i>';
                                    }
                                    if ($half_star) {
                                        echo '<i class="bi bi-star-half text-warning"></i>';
                                    }
                                    for ($j = 0; $j < $empty_stars; $j++) {
                                        echo '<i class="bi bi-star text-warning"></i>';
                                    }
                                    ?>
                                </div>
                                <span class="d-block">Đã bán: <?php echo $product->get_total_sales(); ?></span>
                            </div>
                            <div class="mb-3">
                                Cước phí: <span class="price h5 text-danger"><?php echo wc_price($min_price); ?></span>/tháng
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="btn btn-primary add_to_cart_button flex-grow-1 me-1">Mua ngay</a>
                                <a href="http://localhost/test1/?product_id=<?php echo $product->get_id(); ?>&nha_mang=<?php echo urlencode($networkName); ?>" class="btn btn-secondary flex-grow-1 ms-1">Chọn số</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        endwhile;
    } else {
        echo '<p class="text-center">' . esc_html__('Không có sản phẩm nào được tìm thấy.') . '</p>';
    }
    wp_reset_postdata();
    die(); // Kết thúc hàm để tránh việc in thêm nội dung
}
?>
