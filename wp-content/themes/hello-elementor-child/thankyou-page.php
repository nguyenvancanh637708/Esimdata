<?php
// On your thank-you.php template or similar
/* Template Name: thanh toán custom */
session_start();
get_header();

if (isset($_SESSION['checkout_data'])) {
    $checkout_data = $_SESSION['checkout_data'];
    unset($_SESSION['checkout_data']); // Xóa dữ liệu sau khi sử dụng
    ?>
    <h2>Cảm ơn, <?php echo esc_html($checkout_data['name']); ?>!</h2>
    <p>Thanh toán của bạn đã được xử lý thành công.</p>
    <p>Phương thức thanh toán: <?php echo esc_html($checkout_data['payment_method']); ?></p>
    <p>Loại SIM: <?php echo esc_html($checkout_data['sim_type'] == '1' ? 'eSIM' : 'SIM Vật lý'); ?></p>
    <p>Chúng tôi sẽ liên lạc với bạn qua số điện thoại: <?php echo esc_html($checkout_data['phone']); ?></p>
    <?php
} else {
    echo "<h2>Không có dữ liệu thanh toán.</h2>";
}

get_footer();