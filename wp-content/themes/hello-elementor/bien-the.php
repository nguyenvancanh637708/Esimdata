<?php
/*
Template Name: Gói Cước
*/

get_header(); ?>

<div class="container">
    <h1>Danh sách sản phẩm Gói cước</h1>
    
    <?php
    // Thiết lập truy vấn sản phẩm trong danh mục 'Gói cước'
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => 'goi-cuoc', // Thay đổi slug danh mục nếu cần
            ),
        ),
        'meta_query' => array(
            array(
                'key' => 'nha_mang', // Tên trường tùy chỉnh
                'value' => 'viettel', // Giá trị mà bạn muốn lọc
                'compare' => '=', // So sánh bằng
            ),
        ),
    );
    
    $loop = new WP_Query($args);
    
    if ($loop->have_posts()) :
        while ($loop->have_posts()) : $loop->the_post();
            global $product;
    
            // Kiểm tra nếu sản phẩm có biến thể
            if ($product->is_type('variable')) {
                // Lấy tất cả các biến thể
                $variations = $product->get_available_variations();
                foreach ($variations as $variation) {
                    $variation_obj = new WC_Product_Variation($variation['variation_id']);
                    
                    // Kiểm tra xem biến thể có giá trị loai-hinh-sim là esim không
                    $loai_hinh_sim = $variation_obj->get_attribute('pa_loai-hinh-sim'); // Lấy thuộc tính biến thể
                    echo 'Tên sản phẩm gốc: ' . $loai_hinh_sim . '<br>';
                    
                    if ($loai_hinh_sim === 'eSIM') {
                        // Hiển thị thông tin sản phẩm gốc và biến thể
                        echo 'Tên sản phẩm gốc: ' . $loai_hinh_sim . '<br>';
                        echo 'Biến thể: ' . $variation_obj->get_name() . '<br>'; // Tên biến thể
                        echo 'Giá: ' . $variation_obj->get_price_html() . '<br>'; // Giá biến thể
                        echo '<hr>';
                    }
                }
            }
        endwhile;
    else :
        echo '<p>Không có sản phẩm nào phù hợp với tiêu chí lọc.</p>';
    endif;
    
    wp_reset_postdata();
    
    ?>
</div>

<?php get_footer(); ?>
