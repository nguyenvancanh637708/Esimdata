<?php
/* Template Name: demo0 */
get_header();

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

<!-- Breakcrumd -->
<!-- Tiêu đề trang -->
<div class="container mt-3">
    <h3>Chọn số đẹp, số yêu thích</h3>
</div>

<!-- Bộ lọc thường: Nhà mạng và Số điện thoại -->
<div class="container mt-3">
    <form method="GET" id="filter-normal" action="">
        <div class="row g-3">
            <!-- Tìm kiếm số điện thoại -->
            <div class="col-md-8 col-12">
                <input type="text" name="phone_search" class="form-control" id="phone_search" placeholder="Nhập số cần tìm. VD: *2606*" value="<?php echo esc_attr($phone_search); ?>" />
            </div>
            <div class="col-md-4 col-12 d-grid">
                <button type="submit" class="btn btn-primary w-100">Tìm số</button>
            </div>
        </div>

        <!-- Lọc Nhà mạng -->
        <div class="row mt-3">
            <div class="col-12">
                <select name="nha_mang" class="form-select" id="nha_mang">
                    <option value="">Chọn Nhà mạng</option>
                    <option value="Viettel" <?php selected($nha_mang, 'Viettel'); ?>>Viettel</option>
                    <option value="Mobifone" <?php selected($nha_mang, 'Mobifone'); ?>>Mobifone</option>
                    <option value="Vinaphone" <?php selected($nha_mang, 'Vinaphone'); ?>>Vinaphone</option>
                    <option value="Saymee" <?php selected($nha_mang, 'Saymee'); ?>>Saymee</option>
                </select>
            </div>
        </div>

        <!-- Nút Reset -->
        <div class="row mt-3">
            <div class="col-12 d-grid">
                <a href="<?php echo esc_url(remove_query_arg(array('nha_mang', 'phone_search'))); ?>" class="btn btn-secondary w-100">Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- Hướng dẫn lọc theo Nhà mạng -->
<div class="container mt-3">
    <p class="fw-bold">* Lưu ý</p>
    <ul class="list-unstyled">
        <li>- Tìm sim có số 2605 bạn hãy gõ 2605</li>
        <li>- Tìm sim có đầu 089 đuôi 2605 hãy gõ 089*2605</li>
        <li>- Tìm sim có đuôi 2605 hãy gõ *2605</li>
        <li>- Tìm sim bắt đầu bằng 0904 đuôi bất kỳ hãy gõ 0904*</li>
    </ul>
</div>

<!-- Bộ lọc nhanh: Nhà mạng và Năm sinh -->
<div class="container mt-3">
    <form method="GET" id="filter-fast" action="">
        <div class="row g-3">
            <!-- Lọc Năm sinh -->
            <div class="col-md-6 col-12">
                <select name="birth_year" class="form-select" id="birth_year">
                    <option value="">SIM năm sinh</option>
                    <?php
                    $current_year = date('Y');
                    for ($year = 1990; $year <= $current_year; $year++) {
                        echo '<option value="' . esc_attr($year) . '" ' . selected($birth_year_filter_2, $year, false) . '>' . esc_html($year) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <!-- Lọc Nhà mạng -->
            <div class="col-md-6 col-12">
                <select name="network_filter" class="form-select" id="network_filter">
                    <option value="">Nhà mạng</option>
                    <option value="Viettel" <?php selected($network_filter, 'Viettel'); ?>>Viettel</option>
                    <option value="Mobifone" <?php selected($network_filter, 'Mobifone'); ?>>Mobifone</option>
                    <option value="Vinaphone" <?php selected($network_filter, 'Vinaphone'); ?>>Vinaphone</option>
                    <option value="Saymee" <?php selected($network_filter, 'Saymee'); ?>>Saymee</option>
                </select>
            </div>
        </div>

        <!-- Nút Reset -->
        <div class="row mt-3">
            <div class="col-12 d-grid">
                <a href="<?php echo esc_url(remove_query_arg(array('network_filter', 'birth_year'))); ?>" class="btn btn-secondary w-100">Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- Hiển thị sản phẩm -->
<?php if ($query->have_posts()) : ?>

    <div class="container mt-3">
        <div class="row g-3 text-center fw-bold">
            <div class="col">STT</div>
            <div class="col">Số điện thoại</div>
            <div class="col">Nhà mạng</div>
            <div class="col">Giá bán</div>
            <div class="col"></div>
        </div>

        <?php $counter = 1; while ($query->have_posts()) : $query->the_post(); ?>
            <div class="row g-3 text-center">
                <div class="col"><?php echo $counter; ?></div>
                <div class="col"><?php echo get_field('so-dien-thoai'); ?></div>
                <div class="col"><?php echo get_field('nha_mang'); ?></div>
                <div class="col"><?php echo number_format(get_field('price'), 0, ',', '.'); ?>đ</div>
                <div class="col">
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#popup<?php echo $counter; ?>">Mua</button>
                </div>
            </div>

            <!-- Popup modal for buying SIM -->
            <div class="modal fade" id="popup<?php echo $counter; ?>" tabindex="-1" aria-labelledby="popupLabel<?php echo $counter; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="popupLabel<?php echo $counter; ?>">Xác nhận mua SIM</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Bạn có chắc chắn muốn mua SIM: <?php echo get_field('so-dien-thoai'); ?> của nhà mạng <?php echo get_field('nha_mang'); ?> với giá <?php echo number_format(get_field('price'), 0, ',', '.'); ?>đ không?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="button" class="btn btn-primary">Xác nhận mua</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php $counter++; endwhile; ?>

    </div>
    
<?php else : ?>
    <div class="container mt-3">
        <p class="text-center fw-bold">Không tìm thấy sản phẩm nào.</p>
    </div>
<?php endif; ?>

<?php get_footer(); ?>
