<?php
/* Template Name: Select Number */
get_header();

// Bootstrap CSS
echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">';
echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">';


// Lấy danh mục sản phẩm SIM
$sim_category_slug = 'sim';

// Xử lý bộ lọc từ Form 1
$nha_mang = isset($_GET['nha_mang']) ? sanitize_text_field($_GET['nha_mang']) : '';
$phone_search = isset($_GET['phone_search']) ? sanitize_text_field($_GET['phone_search']) : '';

// Xử lý bộ lọc từ Form 2
$network_filter = isset($_GET['network_filter']) ? sanitize_text_field($_GET['network_filter']) : '';
$birth_year_filter_2 = isset($_GET['birth_year']) ? sanitize_text_field($_GET['birth_year']) : '';

// Tạo truy vấn sản phẩm
$args = array(
    'post_type' => 'product',
    'posts_per_page' => -1,
    'tax_query' => array(
        array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $sim_category_slug,
        ),
    ),
    'meta_query' => array(
        'relation' => 'AND',
    ),
);

// Thêm điều kiện lọc từ Form 1
if ($nha_mang) {
    $args['meta_query'][] = array(
        'key'   => 'nha_mang',
        'value' => $nha_mang,
        'compare' => '='
    );
}

if ($phone_search) {
    $phone_search = trim($phone_search);

    if (strpos($phone_search, '*') !== false) {
        if (strpos($phone_search, '*') === 0) {
            $phone_search = str_replace('*', '', $phone_search);
            $args['meta_query'][] = array(
                'key'     => 'so-dien-thoai',
                'value'   => $phone_search,
                'compare' => 'LIKE',
            );
        } elseif (substr($phone_search, -1) === '*') {
            $phone_search = str_replace('*', '', $phone_search);
            $args['meta_query'][] = array(
                'key'     => 'so-dien-thoai',
                'value'   => $phone_search,
                'compare' => 'LIKE',
            );
        } else {
            $phone_search_parts = explode('*', $phone_search);
            if (count($phone_search_parts) == 2) {
                $args['meta_query'][] = array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'so-dien-thoai',
                        'value'   => $phone_search_parts[0],
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key'     => 'so-dien-thoai',
                        'value'   => $phone_search_parts[1],
                        'compare' => 'LIKE',
                    ),
                );
            }
        }
    } else {
        $args['meta_query'][] = array(
            'key'     => 'so-dien-thoai',
            'value'   => $phone_search,
            'compare' => 'LIKE',
        );
    }
}

// Thêm điều kiện lọc từ Form 2
if ($network_filter) {
    $args['meta_query'][] = array(
        'key'   => 'nha_mang',
        'value' => $network_filter,
        'compare' => '='
    );
}

if ($birth_year_filter_2) {
    $args['meta_query'][] = array(
        'key'   => 'so-dien-thoai',
        'value' => $birth_year_filter_2,
        'compare' => 'LIKE'
    );
}

$query = new WP_Query($args);
?>
<div class="desktop-layou">
    <!-- Breakcrumd -->
    <!-- Tiêu đề trang -->
    <div class="title-page">
        <h3>Chọn số đẹp, số yêu thích</h3>
    </div>

    <!-- Bộ lọc thường: Nhà mạng và Số điện thoại -->
    <div class="filter-container-normal container">
        <form method="GET" id="filter-normal" action="">

            <!-- Tìm kiếm số điện thoại -->
            <input type="text" name="phone_search" id="phone_search" placeholder="Nhập số cần tìm. VD: *2606*" value="<?php echo esc_attr($phone_search); ?>" />

            <!-- Nút Lọc -->
            <button type="submit">Tìm số</button>

            <!-- Lọc Nhà mạng -->
            <select name="nha_mang" id="nha_mang">
                <option value="">Chọn Nhà mạng</option>
                <option value="Viettel" <?php selected($nha_mang, 'Viettel'); ?>>Viettel</option>
                <option value="Mobifone" <?php selected($nha_mang, 'Mobifone'); ?>>Mobifone</option>
                <option value="Vinaphone" <?php selected($nha_mang, 'Vinaphone'); ?>>Vinaphone</option>
                <option value="Saymee" <?php selected($nha_mang, 'Saymee'); ?>>Saymee</option>
            </select>
        </form>
    </div>
    <!-- Hướng dẫn lọc theo Nhà mạng -->
    <div class="instruct container">
        <p class="title">* Lưu ý</p>
        <p>- Tìm sim có số 2605 bạn hãy gõ 2605</p>
        <p>- Tìm sim có đầu 089 đuôi 2605 hãy gõ 089*2605</p>
        <p>- Tìm sim có đuôi 2605 hãy gõ *2605</p>
        <p>- Tìm sim bắt đầu bằng 0904 đuôi bất kỳ hãy gõ 0904*</p>
    </div>

    <!-- Bộ lọc nhanh: Nhà mạng và Năm sinh -->
    <div class="filter-container-fast">
        <form method="GET" id="filter-fast" action="">
            <label for="filter-fast">Tìm nhanh</label>
            <!-- Lọc Năm sinh -->
            <select name="birth_year" id="birth_year">
                <option value="">SIM năm sinh</option>
                <?php
                $current_year = date('Y');
                for ($year = 1990; $year <= $current_year; $year++) {
                    echo '<option value="' . esc_attr($year) . '" ' . selected($birth_year_filter_2, $year, false) . '>' . esc_html($year) . '</option>';
                }
                ?>
            </select>

            <!-- Lọc Nhà mạng -->
            <select name="network_filter" id="network_filter">
                <option value="">Nhà mạng</option>
                <option value="Viettel" <?php selected($network_filter, 'Viettel'); ?>>Viettel</option>
                <option value="Mobifone" <?php selected($network_filter, 'Mobifone'); ?>>Mobifone</option>
                <option value="Vinaphone" <?php selected($network_filter, 'Vinaphone'); ?>>Vinaphone</option>
                <option value="Saymee" <?php selected($network_filter, 'Saymee'); ?>>Saymee</option>
            </select>

        </form>
    </div>

    <!-- Hiển thị sản phẩm -->
    <?php if ($query->have_posts()) : ?>

        <div class="product-list-container">
            <div class="product-list-header">
                <div class="product-item stt">STT</div>
                <div class="product-item so-dien-thoai">Số điện thoại</div>
                <div class="product-item nha-mang">Nhà mạng</div>
                <div class="product-item hinh-thuc">Hình thức</div>
                <div class="product-item gia-sim">Giá SIM</div>
                <div class="product-item action">Hành động</div>
            </div>

            <?php
            $stt = 1;
            while ($query->have_posts()) : $query->the_post();
                global $product;

                $nha_mang = get_post_meta(get_the_ID(), 'nha_mang', true);
                $gia_sim = $product->get_price();
            ?>
                <div class="product-list-row">
                    <div class="product-item stt"><?php echo $stt; ?></div>
                    <div class="product-item so-dien-thoai"><?php echo esc_html(get_post_meta(get_the_ID(), 'so-dien-thoai', true)); ?></div>
                    <div class="product-item nha-mang networks">
                        <?php
                        // Đảm bảo bạn có hình ảnh phù hợp cho từng nhà mạng
                        $nha_mang_img = '';

                        switch ($nha_mang) {
                            case 'Viettel':
                                $nha_mang_img = 'http://localhost/test1/wp-content/uploads/2024/09/Viettel.png';
                                break;
                            case 'Mobifone':
                                $nha_mang_img = 'http://localhost/test1/wp-content/uploads/2024/09/Mobifone.png';
                                break;
                            case 'Vinaphone':
                                $nha_mang_img = 'http://localhost/test1/wp-content/uploads/2024/09/Vinaphone.png';
                                break;
                            case 'Saymee':
                                $nha_mang_img = 'http://localhost/test1/wp-content/uploads/2024/09/Saymee.png';
                                break;
                                // Add more cases for other networks if needed
                        }

                        if (!empty($nha_mang_img)) {
                            echo '<img src="' . esc_url($nha_mang_img) . '" alt="' . esc_attr($nha_mang) . ' logo">';
                        }
                        ?>
                    </div>
                    <div class="product-item hinh-thuc">
                        <?php
                        // Lấy sản phẩm
                        $product = wc_get_product(get_the_ID());

                        // Lấy thuộc tính 'pa_loai-hinh-sim'
                        $loai_hinh_sim = $product->get_attribute('pa_loai-hinh-sim');

                        // Chỉ hiển thị 'eSIM' và mặc định chọn 'Sim vật lý'
                        $eSim_selected = false; // Giá trị mặc định cho checkbox

                        // Kiểm tra xem thuộc tính có giá trị không
                        if (!empty($loai_hinh_sim)) {
                            // Chia tách các giá trị thuộc tính
                            $options = explode('|', $loai_hinh_sim); // Giả sử các giá trị được phân tách bằng dấu '|'

                            // Lấy giá trị đã chọn từ GET request (nếu có)
                            $selected_values = isset($_GET['loai_hinh_sim']) ? (array) $_GET['loai_hinh_sim'] : [];

                            // Kiểm tra xem có giá trị 'eSIM' đã được chọn không
                            if (in_array('eSIM', $selected_values)) {
                                $eSim_selected = true; // Nếu có chọn eSIM
                            }

                            // Hiển thị checkbox cho 'eSIM'
                            echo '<div class="sim-selection">';
                            echo '<label>';
                            echo '<input type="checkbox" name="loai_hinh_sim[]" value="eSIM" ' . ($eSim_selected ? 'checked' : '') . '> eSIM';
                            echo '</label>';

                            // Hiển thị icon "i" với tooltip
                            echo '<div class="tooltip-container">';
                            echo '<i class="bi bi-info-circle info-icon"></i>';
                            echo '<span class="tooltip-text">Chọn hình thức cho SIM: eSIM (khi chọn checkbox) hoặc Sim vật lý (khi không chọn checkbox).</span>';
                            echo '</div>';

                            echo '</div>'; // Kết thúc sim-selection div

                            // Hiển thị thông báo hoặc điều gì đó khác cho 'Sim vật lý'
                            echo '<input type="hidden" name="loai_hinh_sim[]" value="Sim vật lý" ' . (!$eSim_selected ? 'checked' : '') . '>';
                        }
                        ?>
                    </div>

                    <div class="product-item gia-sim"><?php echo wc_price($gia_sim); ?></div>
                    <div class="product-item action">
                        <a href="#" class="chon-so-button" data-product-id="<?php echo get_the_ID(); ?>" data-network-provider="<?php echo esc_attr(get_post_meta(get_the_ID(), 'nha_mang', true)); ?>" data-phone-number="<?php echo esc_attr(get_post_meta(get_the_ID(), 'so-dien-thoai', true)); ?>">Chọn số</a>
                    </div>
                </div>
            <?php
                $stt++;
            endwhile;
            ?>

        </div>
        <div class="description container">
            <?php echo do_shortcode('[custom_description]'); ?>
        </div>
        <div class="send-comment container">
            <?php echo do_shortcode('[customer_comment]'); ?>
        </div>
</div>
<?php
    endif;
    wp_reset_postdata();
?>

<!-- Thêm mã JavaScript để xử lý popup và các hành động -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addToCartButtons = document.querySelectorAll('.chon-so-button');

        addToCartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                const networkProvider = this.getAttribute('data-network-provider');
                const phoneNumber = this.getAttribute('data-phone-number');
                const esimCheckbox = document.querySelector(`.esim-checkbox[data-product-id="${productId}"]`);
                const isEsimChecked = esimCheckbox ? esimCheckbox.checked : false; // Check if eSIM checkbox is checked


                // Gọi AJAX để lấy các gói cước có cùng nhà mạng
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            'action': 'get_packages_by_network',
                            'network_provider': networkProvider,
                            'is_esim': isEsimChecked // Pass whether eSIM is checked
                        })
                    })
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('package-select').innerHTML = data;
                        document.getElementById('package-popup').style.display = 'block';
                        document.getElementById('package-popup').dataset.productId = productId;
                        document.getElementById('package-popup').dataset.phoneNumber = phoneNumber;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
        });

        // Đóng popup
        document.querySelector('.close-popup').addEventListener('click', function() {
            document.getElementById('package-popup').style.display = 'none';
        });

        // Thêm gói cước vào giỏ hàng
        document.querySelector('.add-package').addEventListener('click', function() {
            const selectedPackageId = document.getElementById('package-select').value;
            const productId = document.getElementById('package-popup').dataset.productId;
            const phoneNumber = document.getElementById('package-popup').dataset.phoneNumber;

            if (selectedPackageId) {
                // Thêm gói cước vào giỏ hàng
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            'action': 'add_to_cart',
                            'product_id': selectedPackageId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Thêm sản phẩm SIM vào giỏ hàng với số điện thoại
                            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: new URLSearchParams({
                                        'action': 'add_to_cart',
                                        'product_id': productId,
                                        'phone_number': phoneNumber
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        alert('Gói cước và SIM đã được thêm vào giỏ hàng.');
                                        document.getElementById('package-popup').style.display = 'none';
                                    } else {
                                        alert('Có lỗi xảy ra khi thêm gói cước vào giỏ hàng.');
                                    }
                                });
                        } else {
                            alert('Có lỗi xảy ra khi thêm gói cước vào giỏ hàng.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            } else {
                alert('Vui lòng chọn một gói cước.');
            }
        });
        
    });

    document.getElementById('filter-fast').addEventListener('change', function() {
        this.submit();
    });
</script>
<style>
    .sim-selection {
        display: flex;
        align-items: center;
        /* Đảm bảo các phần tử nằm trên cùng một hàng */
    }

    .sim-selection label {
        margin-right: 10px;
        /* Khoảng cách giữa checkbox và icon */
    }

    .tooltip-container {
        position: relative;
        display: inline-block;
    }

    .info-icon {
        font-size: 15px;
        /* Kích thước icon */
        color: #161F42;
        cursor: pointer;
    }

    .tooltip-text {
        visibility: hidden;
        width: 200px;
        background-color: #555;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 8px;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        left: 50%;
        margin-left: -100px;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .tooltip-container:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }
</style>

<div id="package-popup" class="popup" style="display:none;">
    <div class="popup-overlay"></div>
    <div class="popup-content">
        <button class="close-popup" aria-label="Close Popup" title="Close Popup">✖</button> <!-- X button -->
        <h2>Chọn gói cước</h2>
        <select id="package-select"></select>
        <button class="button add-package">Thêm vào giỏ hàng</button>
        <button class="button proceed-to-checkout">Tiến hành thanh toán</button>

    </div>
</div>

<?php get_footer(); ?>