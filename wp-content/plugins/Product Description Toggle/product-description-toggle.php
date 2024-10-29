<?php
/*
Plugin Name: Product Description Toggle
Description: A shortcode to display the category description from the "SIM" category with "See more" and "Collapse" functionality.
Version: 1.0
Author: Your Name
*/

// Bảo đảm rằng file này không bị truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Không cho phép truy cập trực tiếp
}

// Đăng ký shortcode
function product_description_toggle_shortcode() {
    // Lấy thông tin danh mục sản phẩm "SIM"
    $category = get_term_by( 'slug', 'sim', 'product_cat' ); // Thay thế 'sim' bằng slug của danh mục sản phẩm SIM

    if ( ! $category ) {
        return 'Không tìm thấy danh mục sản phẩm SIM.';
    }

    // Mô tả danh mục
    $description = wp_kses_post( $category->description );

    // Bắt đầu ghi đệm đầu ra
    ob_start();
    ?>
    <div class="product-category-description">
        <h3>Mô tả danh mục: <?php echo esc_html( $category->name ); ?></h3>
        <p class="description-content"><?php echo $description; ?></p>
        <button class="toggle-description">Xem thêm</button>
    </div>
    <?php

    // Trả về nội dung đã ghi đệm
    return ob_get_clean();
}

// Đăng ký shortcode
add_shortcode( 'product_description_toggle', 'product_description_toggle_shortcode' );

// Thêm jQuery và CSS cho plugin
function product_description_toggle_scripts() {
    if ( is_page() || is_single() ) { // Kiểm tra nếu là trang hoặc bài viết
        wp_enqueue_script('jquery');
        ?>
        <script>
        jQuery(document).ready(function($) {
            $(".toggle-description").click(function() {
                var content = $(this).siblings(".description-content");
                if (content.hasClass("expanded")) {
                    // Nếu đang mở rộng, thu gọn lại
                    content.removeClass("expanded");
                    content.css({
                        'overflow': 'hidden',
                        'display': '-webkit-box',
                        '-webkit-line-clamp': '5',
                        '-webkit-box-orient': 'vertical'
                    });
                    $(this).text("Xem thêm");
                } else {
                    // Nếu đang thu gọn, mở rộng
                    content.addClass("expanded");
                    content.css({
                        'overflow': 'visible',
                        'display': 'block',
                        '-webkit-line-clamp': 'unset',
                        '-webkit-box-orient': 'unset'
                    });
                    $(this).text("Thu gọn");
                }
            });
        });
        </script>
        <style>
        .description-content {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 5; /* Hiển thị 5 dòng */
            -webkit-box-orient: vertical;
            transition: all 0.3s ease;
        }
        </style>
        <?php
    }
}
add_action( 'wp_footer', 'product_description_toggle_scripts' );
