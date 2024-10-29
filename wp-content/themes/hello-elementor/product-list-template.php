<?php
/*
Template Name: Product List Template
*/

get_header(); ?>

<div class="product-filter-container">
    <form id="filter-form" method="GET" action="">
        <?php
        // Retrieve unique network provider values from products
        global $wpdb;
        $network_providers = $wpdb->get_col("
            SELECT DISTINCT meta_value
            FROM {$wpdb->postmeta}
            WHERE meta_key = 'nha_mang'
        ");
        ?>
        <select name="nha_mang" id="network-provider">
            <option value="">Chọn Nhà mạng</option>
            <?php
            // Display unique network providers as options
            foreach ($network_providers as $provider) {
                $selected = isset($_GET['nha_mang']) && $_GET['nha_mang'] === $provider ? 'selected' : '';
                echo '<option value="' . esc_attr($provider) . '" ' . $selected . '>' . esc_html($provider) . '</option>';
            }
            ?>
        </select>
        <button type="submit" class="button">Lọc</button>
    </form>
</div>

<div class="product-table-container">
    <table class="product-table">
        <thead>
            <tr>
                <th>STT</th>
                <th>Số điện thoại</th>
                <th>Nhà mạng</th>
                <th>Hình thức</th>
                <th>Giá SIM</th>
                <th>Chọn số</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Get selected network provider from the filter
            $selected_network_provider = isset($_GET['nha_mang']) ? sanitize_text_field($_GET['nha_mang']) : '';

            // Query for SIM products
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field'    => 'slug',
                        'terms'    => 'sim',
                    ),
                ),
                'meta_query' => array(
                    array(
                        'key' => 'nha_mang',
                        'value' => $selected_network_provider,
                        'compare' => $selected_network_provider ? '=' : 'LIKE',
                    ),
                ),
            );
            $query = new WP_Query($args);
            if ($query->have_posts()) :
                $index = 1;
                while ($query->have_posts()) : $query->the_post();
                    global $product;
                    $product_id = $product->get_id();
                    $network_provider = get_post_meta($product_id, 'nha_mang', true);
                    $price = $product->get_price_html();
            ?>
                <tr>
                    <td><?php echo $index++; ?></td>
                    <td><?php the_title(); ?></td>
                    <td><?php echo esc_html($network_provider); ?></td>
                    <td></td>
                    <td><?php echo $price; ?></td>
                    <td>
                        <button type="button" class="button select-number" data-product-id="<?php echo esc_attr($product_id); ?>" data-network-provider="<?php echo esc_attr($network_provider); ?>" data-phone-number="<?php the_title(); ?>">Chọn số</button>
                    </td>
                </tr>
            <?php
                endwhile;
            endif;
            wp_reset_postdata();
            ?>
        </tbody>
    </table>
</div>

<!-- Popup HTML -->
<div id="package-popup" style="display:none;">
    <div class="popup-content">
        <h2>Chọn gói cước</h2>
        <select id="package-select"></select>
        <button class="button add-package">Thêm vào giỏ hàng</button>
        <button class="button close-popup">Đóng</button>
    </div>
</div>

<?php get_footer(); ?>
