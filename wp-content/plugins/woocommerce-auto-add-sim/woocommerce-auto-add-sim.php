<?php
/*
Plugin Name: WooCommerce SIM and Service Package Integration
Description: Tự động thêm SIM vào giỏ hàng khi thêm gói cước.
Version: 1.0
Author: Your Name
*/

// Kiểm tra nếu template là 'test.php' thuộc theme Hello Elementor
function check_template_before_plugin() {
    // Kiểm tra xem template hiện tại có phải là 'test.php' và theme hiện tại có phải là Hello Elementor không
    if (!is_page_template('test.php') || wp_get_theme()->get('Name') !== 'Hello Elementor') {
        // Hủy bỏ các hook của plugin nếu không đúng
        remove_action('woocommerce_after_single_product', 'display_related_by_network', 10);
        remove_action('woocommerce_add_to_cart', 'add_random_sim_with_same_network', 10);
        remove_filter('woocommerce_cart_item_name', 'display_sim_with_service_package', 10);
        return; // Không làm gì nếu không phải
    }
}
add_action('wp', 'check_template_before_plugin');

// Hiển thị sản phẩm liên quan dựa trên trường "Nhà mạng"
add_action('woocommerce_after_single_product', 'display_related_by_network', 10);
function display_related_by_network() {
    global $post;

    // Kiểm tra xem template và theme hiện tại có đúng không
    if (!is_page_template('test.php') || wp_get_theme()->get('Name') !== 'Hello Elementor') {
        return; // Không làm gì nếu không phải
    }

    // Lấy giá trị của trường "Nhà mạng"
    $network_provider = get_field('nha_mang', $post->ID);

    if ($network_provider) {
        // Query để lấy các sản phẩm cùng nhà mạng
        $args = array(
            'post_type' => 'product',
            'meta_key' => 'nha_mang',
            'meta_value' => $network_provider,
            'posts_per_page' => 5, // Số sản phẩm muốn hiển thị
            'post__not_in' => array($post->ID), // Loại bỏ sản phẩm hiện tại
        );
        $related_products = new WP_Query($args);

        if ($related_products->have_posts()) {
            echo '<h3>Sản phẩm cùng nhà mạng:</h3>';
            echo '<ul class="related-products">';
            while ($related_products->have_posts()) {
                $related_products->the_post();
                wc_get_template_part('content', 'product');
            }
            echo '</ul>';
            wp_reset_postdata();
        }
    }
}

// Thêm SIM ngẫu nhiên vào giỏ hàng khi thêm gói cước
add_action('woocommerce_add_to_cart', 'add_random_sim_with_same_network', 10, 6);
function add_random_sim_with_same_network($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    // Kiểm tra xem template và theme hiện tại có đúng không
    if (!is_page_template('test.php') || wp_get_theme()->get('Name') !== 'Hello Elementor') {
        return; // Không làm gì nếu không phải
    }

    // Kiểm tra xem sản phẩm có phải là Gói cước hay không
    $product_cats = wp_get_post_terms($product_id, 'product_cat');
    $is_service_package = false;

    foreach ($product_cats as $cat) {
        if ($cat->slug == 'goi-cuoc') { // Thay 'goi-cuoc' bằng slug của danh mục Gói cước
            $is_service_package = true;
            break;
        }
    }

    // Nếu là Gói cước, tìm một SIM ngẫu nhiên có cùng Nhà mạng
    if ($is_service_package) {
        $network_provider = get_field('nha_mang', $product_id); // Lấy thông tin Nhà mạng của Gói cước

        if ($network_provider) {
            // Query để tìm SIM có cùng Nhà mạng
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => 1, // Chỉ cần lấy ngẫu nhiên 1 SIM
                'orderby' => 'rand',
                'meta_query' => array(
                    array(
                        'key' => 'nha_mang',
                        'value' => $network_provider,
                        'compare' => '='
                    )
                ),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => 'sim', // Thay 'sim' bằng slug của danh mục SIM
                    ),
                ),
            );

            $random_sim = new WP_Query($args);

            if ($random_sim->have_posts()) {
                while ($random_sim->have_posts()) {
                    $random_sim->the_post();
                    $sim_id = get_the_ID();

                    // Kiểm tra xem sản phẩm đang thêm vào giỏ hàng có phải là SIM không
                    $is_sim_product = false;
                    $sim_cats = wp_get_post_terms($sim_id, 'product_cat');
                    foreach ($sim_cats as $cat) {
                        if ($cat->slug == 'sim') {
                            $is_sim_product = true;
                            break;
                        }
                    }

                    if ($is_sim_product) {
                        // Thêm SIM vào giỏ hàng
                        WC()->cart->add_to_cart($sim_id, 1);
                        // Lưu tên SIM đã thêm vào dữ liệu giỏ hàng
                        WC()->cart->cart_contents[$cart_item_key]['sim_added'] = get_the_title();
                    }
                }
                wp_reset_postdata();
            }
        }
    }
}

// Hiển thị tên SIM đã thêm vào giỏ hàng nếu có
add_filter('woocommerce_cart_item_name', 'display_sim_with_service_package', 10, 3);
function display_sim_with_service_package($product_name, $cart_item, $cart_item_key) {
    // Kiểm tra xem sản phẩm có phải là Gói cước không
    $product_id = $cart_item['product_id'];
    $product_cats = wp_get_post_terms($product_id, 'product_cat');
    
    foreach ($product_cats as $cat) {
        if ($cat->slug == 'goi-cuoc') {
            // Hiển thị tên SIM ngẫu nhiên đã thêm
            $sim_added = '';
            if (isset($cart_item['sim_added'])) {
                $sim_added = $cart_item['sim_added'];
            }
            return $product_name . '<br><strong>SIM đi kèm:</strong> ' . $sim_added;
        }
    }

    return $product_name;
}
?>
