<?php
/*
Plugin Name: Custom Description Shortcode
Description: Plugin cho phép hiển thị mô tả của danh mục hoặc sản phẩm bằng shortcode.
Version: 1.4
Author: Your Name
*/

// Tạo menu trong quản trị
add_action('admin_menu', 'cds_create_menu');
function cds_create_menu()
{
    add_menu_page(
        'Custom Description Shortcode',
        'Mô tả ngắn',
        'manage_options',
        'cds-settings',
        'cds_settings_page',
        'dashicons-editor-textcolor',
        20
    );
}

// Tạo trang cài đặt trong admin
function cds_settings_page()
{
?>
    <div class="wrap">
        <h1>Chọn danh mục hoặc sản phẩm để tạo shortcode</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('cds_settings_group');
            do_settings_sections('cds-settings');
            submit_button();
            ?>
        </form>
    </div>
<?php
}

// Đăng ký các tùy chọn
add_action('admin_init', 'cds_register_settings');
function cds_register_settings()
{
    register_setting('cds_settings_group', 'cds_selected_term');
    register_setting('cds_settings_group', 'cds_selected_product'); // Đăng ký tùy chọn cho sản phẩm

    add_settings_section('cds_main_section', 'Thiết lập hiển thị', null, 'cds-settings');

    add_settings_field('cds_select_term', 'Chọn danh mục hoặc sản phẩm:', 'cds_select_term_field', 'cds-settings', 'cds_main_section');
}

// Tạo trường chọn danh mục hoặc sản phẩm
function cds_select_term_field()
{
    $selected_term = get_option('cds_selected_term');
    $selected_product = get_option('cds_selected_product'); // Lấy tùy chọn sản phẩm đã chọn

    // Lấy danh mục sản phẩm
    $categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false
    ));

    // Lấy sản phẩm
    $products = wc_get_products(array(
        'limit' => -1,
        'orderby' => 'name',
        'order' => 'ASC',
        'return' => 'ids'
    ));

    // Dropdown để chọn danh mục
    echo '<label for="cds_selected_term">Danh mục sản phẩm:</label><br>';
    echo '<select name="cds_selected_term" id="cds_selected_term">';
    echo '<option value="">Chọn danh mục...</option>';
    foreach ($categories as $category) {
        echo '<option value="' . esc_attr($category->term_id) . '"' . selected($selected_term, $category->term_id, false) . '>' . esc_html($category->name) . '</option>'; // Sửa ở đây
    }
    echo '</select>';

    // Dropdown để chọn sản phẩm
    echo '<br><br><label for="cds_selected_product">Hoặc chọn sản phẩm:</label><br>';
    echo '<select name="cds_selected_product" id="cds_selected_product">';
    echo '<option value="">Chọn sản phẩm...</option>';
    foreach ($products as $product_id) {
        $product = wc_get_product($product_id);
        echo '<option value="' . esc_attr($product_id) . '"' . selected($selected_product, $product_id, false) . '>' . esc_html($product->get_name()) . '</option>';
    }
    echo '</select>';
}

// Tạo shortcode hiển thị mô tả
add_shortcode('custom_description', 'cds_custom_description_shortcode');
function cds_custom_description_shortcode($atts)
{
    $selected_term = get_option('cds_selected_term');
    $selected_product = get_option('cds_selected_product');

    if ($selected_term) {
        $term = get_term($selected_term);
        $description = term_description($selected_term, 'product_cat');
    } elseif ($selected_product) {
        $product = wc_get_product($selected_product);
        $description = $product ? $product->get_description() : '';
    } else {
        return 'Vui lòng chọn danh mục hoặc sản phẩm từ cài đặt.';
    }

    if (!$description) {
        return 'Không có mô tả cho mục này.';
    }

    ob_start();
?>
    <div class="term-description">
        <div class="term-description-short">
            <?php echo $description; ?>
        </div>
        <div class="term-description-full" style="display: none;">
            <?php echo $description; ?>
        </div>
        <button class="see-more">Xem thêm</button>
    </div>

    <style>
        .term-description-short {
            display: -webkit-box;
            -webkit-line-clamp: 10;
            /* Hiển thị 10 dòng */
            -webkit-box-orient: vertical;
            overflow: hidden;
            position: relative;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1) 60%, rgba(0, 0, 0, 0));
            /* Tạo hiệu ứng mờ dần */
        }

        .term-description-full {
            display: none;
        }

        .see-more {
            margin: 10px auto;
            cursor: pointer;
            background-color: white;
            color: #0073aa;
            border: 1px solid #0073aa;
            padding: 10px;
            font-size: 14px;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            width: fit-content;
        }

        .see-more:hover {
            background-color: aliceblue;
            /* Màu nền khi hover */
            color: #0073aa;
            /* Màu chữ vẫn giữ nguyên */
            border: 1px solid #0073aa;
            /* Màu viền vẫn giữ nguyên */
        }

        .see-more:active,
        .see-more:focus {
            background-color: white;
            /* Giữ màu nền khi active và focus */
            color: #0073aa;
            /* Màu chữ vẫn giữ nguyên */
            border: 1px solid #0073aa;
            /* Màu viền vẫn giữ nguyên */
        }

        /* Thêm quy tắc để xóa màu nền mặc định khi có trạng thái focus */
        .see-more:focus {
            outline: none;
            /* Xóa đường viền mặc định của trình duyệt */
        }

        .see-more::after {
            content: "▼";
            font-size: 10px;
        }

        .see-more.open::after {
            content: "▲";
        }
    </style>

    <script>
        document.querySelector('.see-more').addEventListener('click', function() {
            var shortDesc = document.querySelector('.term-description-short');
            var fullDesc = document.querySelector('.term-description-full');

            if (fullDesc.style.display === "none") {
                fullDesc.style.display = "block";
                shortDesc.style.display = "none";
                this.innerText = "Thu gọn ";
                this.classList.add('open'); // Thêm lớp 'open' khi mở mô tả đầy đủ
            } else {
                fullDesc.style.display = "none";
                shortDesc.style.display = "-webkit-box"; // Giới hạn lại thành 5 dòng
                this.innerText = "Xem thêm";
                this.classList.remove('open'); // Bỏ lớp 'open' khi thu gọn
            }
        });
    </script>
<?php
    return ob_get_clean();
}
