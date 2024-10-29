<?php
/* Template Name: Product List */

get_header(); // Gọi header từ theme

// Lọc sản phẩm theo danh mục "Gói cước"
$args = array(
    'post_type' => 'product', // Loại bài post là sản phẩm
    'posts_per_page' => 10, // Hiển thị 10 sản phẩm
    'post_status' => 'publish', // Chỉ lấy sản phẩm đã xuất bản
    'tax_query' => array(
        array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => 'goi-cuoc', // Thay đổi slug danh mục ở đây
        ),
    ),
);

// Xử lý bộ lọc theo nhà mạng
$nha_mang_filter = isset($_GET['nha_mang']) ? sanitize_text_field($_GET['nha_mang']) : '';
if ($nha_mang_filter) {
    $args['meta_query'] = array(
        array(
            'key'     => 'nha_mang',
            'value'   => $nha_mang_filter,
            'compare' => '=',
        ),
    );
}


// Xử lý bộ lọc theo thứ tự
$order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : '';
switch ($order) {
    case 'asc':
        $args['meta_key'] = '_price'; // Thêm key giá để sắp xếp theo giá
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'ASC';
        break;
    case 'desc':
        $args['meta_key'] = '_price'; // Thêm key giá để sắp xếp theo giá
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;
    case 'popularity':
        $args['meta_key'] = 'product_views'; // Đặt meta key là số lần xem
        $args['orderby'] = 'meta_value_num'; // Sắp xếp theo giá trị số
        $args['order'] = 'DESC'; // Giảm dần
        break;
    case 'sales':
        $args['meta_key'] = 'total_sales'; // Đặt meta key là số lượt bán
        $args['orderby'] = 'meta_value_num'; // Sắp xếp theo số lượng bán
        $args['order'] = 'DESC'; // Giảm dần
        break;
    case 'data_capacity': // Thêm trường hợp cho dung lượng
        $args['meta_key'] = 'data_capacity'; // Tên meta key cho dung lượng
        $args['orderby'] = 'meta_value_num'; // Sắp xếp theo số
        $args['order'] = 'DESC'; // Giảm dần
        break;
    default:
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        break;
}


$loop = new WP_Query($args);

if ($loop->have_posts()) : ?>
    <div class="product-filter container my-4">
        <div class="title-page">
            <h3>Sim Data 4G</h3>
            <p>Sim data dành cho mọi nhu cầu</p>
        </div>
        <div class="filter-buttons d-flex">
            <?php
            $network_providers = array(
                'Viettel'   => 'http://localhost/test1/wp-content/uploads/2024/09/Viettel.png',
                'Mobifone'  => 'http://localhost/test1/wp-content/uploads/2024/09/MobiFone.png',
                'Vinaphone' => 'http://localhost/test1/wp-content/uploads/2024/09/vinaphone.png',
                'Saymee'    => 'http://localhost/test1/wp-content/uploads/2024/09/Saymee.png'
            );
            foreach ($network_providers as $provider => $image_url) :
                $active = $nha_mang_filter === $provider ? 'active' : '';
            ?>
                <a href="<?php echo esc_url(add_query_arg('nha_mang', $provider)); ?>" class="filter-button mx-2 <?php echo esc_attr($active); ?>">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($provider); ?>" class="img-fluid" />
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Bộ lọc sản phẩm -->
        <div class="product-sort container my-3">
            <div class="d-flex flex-wrap align-items-center">
                <div class="title-filter me-1"> <!-- Giảm khoảng cách bên phải tiêu đề -->
                    <h3>Bộ lọc sản phẩm</h3>
                </div>
                <div class="filter-buttons d-flex overflow-auto mt-2 mt-md-0"> <!-- Không thay đổi gì ở đây -->
                    <?php
                    $order_options = array(
                        'sales' => 'Bán chạy',
                        'popularity' => 'Xem nhiều',
                        'data_capacity' => 'Theo dung lượng',
                        'asc' => 'Giá tăng dần',
                        'desc' => 'Giá giảm dần',
                    );

                    foreach ($order_options as $value => $label) :
                        $active = $value === $order ? 'active' : '';
                    ?>
                        <a href="<?php echo esc_url(add_query_arg('order', $value)); ?>" class="btn btn-outline-primary mx-1 <?php echo esc_attr($active); ?>">
                            <?php echo esc_html($label); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>


    </div>

    <div class="list container">
        <div class="row">
            <?php while ($loop->have_posts()) : $loop->the_post();
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
                                <!-- Hiển thị ảnh đại diện sản phẩm -->
                                <a href="<?php the_permalink(); ?>">
                                    <?php echo $product->get_image('medium', array('class' => 'card-img-top')); ?>
                                </a>
                            </div>
                            <div class="content">
                                <!-- Hiển thị tên sản phẩm -->
                                <h5 class="card-title productName">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h5>
                                <!-- Hiển thị đánh giá và số lượng đã bán -->
                                <div class="rating mb-2 d-flex align-items-center">
                                    <div class="star-rating me-2"> <!-- Thêm lớp `me-2` để tạo khoảng cách giữa ngôi sao và số lượng đã bán -->
                                        <?php
                                        $average = $product->get_average_rating(); // Lấy điểm đánh giá trung bình
                                        $full_stars = floor($average); // Số ngôi sao đầy
                                        $half_star = ($average - $full_stars) >= 0.5; // Kiểm tra ngôi sao nửa
                                        $empty_stars = 5 - ceil($average); // Số ngôi sao rỗng

                                        // Hiển thị ngôi sao đầy
                                        for ($i = 0; $i < $full_stars; $i++) {
                                            echo '<i class="bi bi-star-fill text-warning"></i>'; // Ngôi sao đầy
                                        }

                                        // Hiển thị ngôi sao nửa
                                        if ($half_star) {
                                            echo '<i class="bi bi-star-half text-warning"></i>'; // Ngôi sao nửa
                                        }

                                        // Hiển thị ngôi sao rỗng
                                        for ($j = 0; $j < $empty_stars; $j++) {
                                            echo '<i class="bi bi-star text-warning"></i>'; // Ngôi sao rỗng
                                        }
                                        ?>
                                    </div>
                                    <span class="d-block">Đã bán: <?php echo $product->get_total_sales(); ?></span>
                                </div>

                                <!-- Hiển thị giá thấp nhất sản phẩm -->
                                <div class="mb-3">
                                    Cước phí: <span class="price h5 text-danger"><?php echo wc_price($min_price); ?></span>/tháng
                                </div>
                                <!-- Các nút mua hàng -->
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="btn btn-primary add_to_cart_button flex-grow-1 me-1">Mua ngay</a>
                                    <a href="http://localhost/test1/?product_id=<?php echo $product->get_id(); ?>&nha_mang=<?php echo urlencode($networkName); ?>" class="btn btn-secondary flex-grow-1 ms-1">Chọn số</a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="description container">
            <?php echo do_shortcode('[custom_description]'); ?>
        </div>
        <div class="send-comment container">
            <?php echo do_shortcode('[customer_comment]'); ?>
        </div>
        <div class="mo-ta-sp">
            <!--Cú pháp gán Block Content-->
            <?php echo do_shortcode('[block id="420"]'); ?>
        </div>
    </div>
<?php else: ?>
    <p class="text-center"><?php esc_html_e('Không có sản phẩm nào được tìm thấy.'); ?></p>
<?php endif;

wp_reset_postdata(); // Đặt lại dữ liệu truy vấn
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtonsContainer = document.querySelector('.filter-buttons');

        let isDown = false;
        let startX;
        let scrollLeft;

        filterButtonsContainer.addEventListener('mousedown', (e) => {
            isDown = true;
            filterButtonsContainer.classList.add('active');
            startX = e.pageX - filterButtonsContainer.offsetLeft;
            scrollLeft = filterButtonsContainer.scrollLeft;
        });

        filterButtonsContainer.addEventListener('mouseleave', () => {
            isDown = false;
            filterButtonsContainer.classList.remove('active');
        });

        filterButtonsContainer.addEventListener('mouseup', () => {
            isDown = false;
            filterButtonsContainer.classList.remove('active');
        });

        filterButtonsContainer.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - filterButtonsContainer.offsetLeft;
            const walk = (x - startX) * 2; // Tăng tốc độ cuộn
            filterButtonsContainer.scrollLeft = scrollLeft - walk;
        });

        // Touch screen support
        let touchStartX = 0;
        let touchScrollLeft = 0;

        filterButtonsContainer.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].pageX - filterButtonsContainer.offsetLeft;
            touchScrollLeft = filterButtonsContainer.scrollLeft;
        });

        filterButtonsContainer.addEventListener('touchmove', (e) => {
            const touchMoveX = e.touches[0].pageX - filterButtonsContainer.offsetLeft;
            const walk = (touchMoveX - touchStartX) * 2;
            filterButtonsContainer.scrollLeft = touchScrollLeft - walk;
        });
    });
</script>
<style>
    .product-sort {
        max-width: 900px;
    }

    /* Tiêu đề trang */
    .title-page h3 {
        color: #0078D8;
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .title-page p {
        color: #252731;
        font-size: 18px;
        font-weight: 500;
        line-height: 26px;
        margin-block-start: 0;
        margin-block-end: .9rem;
    }

    /* Bộ lọc sản phẩm */
    .btn-outline-primary {
        --bs-btn-color: #161F42;
        border-radius: 8px;
        font-weight: 500;
        --bs-btn-border-color: #E3E5ED;
        --bs-btn-hover-color: #161F42;
        --bs-btn-hover-bg: #fff;
        --bs-btn-hover-border-color: #0078D8;
        --bs-btn-focus-shadow-rgb: 13, 110, 253;
        --bs-btn-active-color: #161F42;
        --bs-btn-active-bg: #fff;
        --bs-btn-active-border-color: #0078D8;
        --bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
        --bs-btn-disabled-color: #161F42;
        --bs-btn-disabled-bg: transparent;
        --bs-btn-disabled-border-color: #E3E5ED;
        --bs-gradient: none;
    }

    .product-sort .title-filter {
        margin-bottom: 0;
        /* Loại bỏ khoảng cách dưới tiêu đề */
    }

    .product-sort .filter-buttons {
        margin-left: auto;
        /* Đẩy bộ lọc sang bên phải */
    }

    .product-sort {
        white-space: nowrap;
        /* Ngăn cách các phần tử xuống dòng */
    }

    .product-sort .form-select {
        display: inline-block;
        /* Đảm bảo rằng dropdown nằm trong hàng ngang */
        min-width: auto;
        /* Đặt chiều rộng tối thiểu */
    }

    .product-sort .d-flex {
        overflow-x: auto;
        /* Cho phép cuộn ngang */
    }
</style>

<?php

get_footer(); // Gọi footer từ theme
?>