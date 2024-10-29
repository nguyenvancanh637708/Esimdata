<?php
/*
Plugin Name: SIM Package Selector
Plugin URI: https://yourwebsite.com
Description: Chọn gói cước khi thêm SIM vào giỏ hàng
Version: 1.0
Author: Your Name
Author URI: https://yourwebsite.com
*/

// Chặn truy cập trực tiếp vào tệp
if (!defined('ABSPATH')) {
    exit;
}

// Thêm script và style cho popup
function sim_package_selector_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('sim-package-selector', plugin_dir_url(__FILE__) . 'js/sim-package-selector.js', array('jquery'), '1.0', true);
    wp_enqueue_style('sim-package-selector', plugin_dir_url(__FILE__) . 'css/sim-package-selector.css');
}
add_action('wp_enqueue_scripts', 'sim_package_selector_enqueue_scripts');

// Thêm HTML popup vào footer
function sim_package_selector_popup_html() {
    ?>
    <div id="package-popup" style="display:none;">
        <div class="popup-content">
            <h2>Chọn gói cước</h2>
            <select id="package-select"></select>
            <button class="button add-package">Thêm vào giỏ hàng</button>
            <button class="button close-popup">Đóng</button>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'sim_package_selector_popup_html');

// Xử lý AJAX lấy gói cước theo nhà mạng và các biến thể
add_action('wp_ajax_get_packages_by_network', 'get_packages_by_network');
add_action('wp_ajax_nopriv_get_packages_by_network', 'get_packages_by_network');

function get_packages_by_network() {
    $network_provider = isset($_POST['network_provider']) ? sanitize_text_field($_POST['network_provider']) : '';
    
    // Truy vấn các sản phẩm gói cước có cùng nhà mạng (dùng trường 'nha_mang')
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => 'goi-cuoc',
            ),
        ),
        'meta_query' => array(
            array(
                'key' => 'nha_mang',
                'value' => $network_provider,
                'compare' => '='
            ),
        ),
    );
    
    $query = new WP_Query($args);
    $options = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product = wc_get_product(get_the_ID());

            // Kiểm tra xem sản phẩm có biến thể không
            if ($product->is_type('variable')) {
                // Lấy tất cả các biến thể
                $available_variations = $product->get_available_variations();
                foreach ($available_variations as $variation) {
                    $variation_id = $variation['variation_id'];
                    $variation_name = implode(', ', array_map(function($attr) {
                        return $attr;
                    }, $variation['attributes']));
                    
                    $price = wc_price($variation['display_price']); // Lấy giá của biến thể
                    $options .= '<option value="' . $variation_id . '">' . $product->get_name() . ' - ' . $variation_name . ' (' . $price . ')</option>';
                }
            } else {
                $price = wc_price($product->get_price()); // Lấy giá của sản phẩm
                $options .= '<option value="' . get_the_ID() . '">' . get_the_title() . ' (' . $price . ')</option>';
            }
        }
        wp_reset_postdata();
    } else {
        $options = '<option value="">Không có gói cước nào</option>';
    }

    echo $options;
    wp_die();
}


// Xử lý AJAX thêm gói cước vào giỏ hàng
add_action('wp_ajax_add_to_cart', 'ajax_add_to_cart');
add_action('wp_ajax_nopriv_add_to_cart', 'ajax_add_to_cart');

function ajax_add_to_cart() {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if ($product_id > 0) {
        WC()->cart->add_to_cart($product_id);
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }

    wp_die();
}
