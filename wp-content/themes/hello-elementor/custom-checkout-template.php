<?php
/*
Template Name: Check-out
*/

get_header();
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access
}

$checkout = WC()->checkout(); // Get checkout object

wc_print_notices(); // Display WooCommerce notices

do_action( 'woocommerce_before_checkout_form', $checkout );

// Do not display form if cart is empty
if ( ! $checkout->get_checkout_fields() ) {
    return;
}

// Full list of provinces and districts in Vietnam
// Danh sách đầy đủ Tỉnh/Thành phố và Quận/Huyện ở Việt Nam
$locations = array(
    "Hà Nội" => array("Ba Đình", "Hoàn Kiếm", "Tây Hồ", "Long Biên", "Cầu Giấy", "Đống Đa", "Hai Bà Trưng", "Hoàng Mai", "Thanh Xuân", "Hà Đông", "Sóc Sơn", "Đông Anh", "Gia Lâm", "Nam Từ Liêm", "Thanh Trì", "Bắc Từ Liêm", "Mê Linh", "Sơn Tây", "Phúc Thọ", "Thạch Thất", "Quốc Oai", "Chương Mỹ", "Đan Phượng", "Hoài Đức", "Thanh Oai", "Mỹ Đức", "Ứng Hòa", "Phú Xuyên", "Thường Tín"),
    "TP Hồ Chí Minh" => array("Quận 1", "Quận 2", "Quận 3", "Quận 4", "Quận 5", "Quận 6", "Quận 7", "Quận 8", "Quận 9", "Quận 10", "Quận 11", "Quận 12", "Bình Thạnh", "Gò Vấp", "Phú Nhuận", "Tân Bình", "Tân Phú", "Thủ Đức", "Bình Tân", "Củ Chi", "Hóc Môn", "Bình Chánh", "Nhà Bè", "Cần Giờ"),
    "Đà Nẵng" => array("Hải Châu", "Thanh Khê", "Sơn Trà", "Ngũ Hành Sơn", "Liên Chiểu", "Cẩm Lệ", "Hòa Vang"),
    "Hải Phòng" => array("Hồng Bàng", "Ngô Quyền", "Lê Chân", "Hải An", "Kiến An", "Đồ Sơn", "Dương Kinh", "Thủy Nguyên", "An Dương", "An Lão", "Kiến Thụy", "Tiên Lãng", "Vĩnh Bảo", "Cát Hải", "Bạch Long Vĩ"),
    "Cần Thơ" => array("Ninh Kiều", "Ô Môn", "Bình Thủy", "Cái Răng", "Thốt Nốt", "Vĩnh Thạnh", "Cờ Đỏ", "Phong Điền", "Thới Lai"),
    "An Giang" => array("Long Xuyên", "Châu Đốc", "An Phú", "Tân Châu", "Phú Tân", "Châu Phú", "Tịnh Biên", "Tri Tôn", "Châu Thành", "Chợ Mới", "Thoại Sơn"),
    "Bà Rịa - Vũng Tàu" => array("Châu Đức", "Xuyên Mộc", "Long Điền", "Đất Đỏ", "Tân Thành", "Côn Đảo"),
    "Bạc Liêu" => array("Hồng Dân", "Phước Long", "Vĩnh Lợi", "Giá Rai", "Đông Hải", "Hòa Bình"),
    "Bắc Giang" => array("Yên Thế", "Tân Yên", "Lạng Giang", "Lục Nam", "Lục Ngạn", "Sơn Động", "Yên Dũng", "Việt Yên", "Hiệp Hòa"),
    "Bắc Kạn" => array("Pác Nặm", "Ba Bể", "Ngân Sơn", "Bạch Thông", "Chợ Đồn", "Chợ Mới", "Na Rì"),
    "Bắc Ninh" => array("Yên Phong", "Quế Võ", "Tiên Du", "Từ Sơn", "Thuận Thành", "Gia Bình", "Lương Tài"),
    "Bến Tre" => array("Châu Thành", "Chợ Lách", "Mỏ Cày Bắc", "Mỏ Cày Nam", "Giồng Trôm", "Bình Đại", "Ba Tri", "Thạnh Phú"),
    "Bình Dương" => array("Thủ Dầu Một", "Dĩ An", "Thuận An", "Bến Cát", "Tân Uyên", "Phú Giáo", "Bắc Tân Uyên", "Dầu Tiếng", "Bàu Bàng"),
    "Bình Định" => array("Quy Nhơn", "An Lão", "Hoài Ân", "Hoài Nhơn", "Phù Mỹ", "Vĩnh Thạnh", "Tây Sơn", "Phù Cát", "An Nhơn", "Tuy Phước", "Vân Canh"),
    "Bình Phước" => array("Đồng Xoài", "Chơn Thành", "Bình Long", "Phước Long", "Lộc Ninh", "Bù Đốp", "Hớn Quản", "Bù Đăng", "Bù Gia Mập", "Phú Riềng"),
    "Bình Thuận" => array("Phan Thiết", "La Gi", "Tuy Phong", "Bắc Bình", "Hàm Thuận Bắc", "Hàm Thuận Nam", "Tánh Linh", "Đức Linh", "Hàm Tân", "Phú Quý"),
    "Cà Mau" => array("Cái Nước", "Đầm Dơi", "Năm Căn", "Ngọc Hiển", "Phú Tân", "Thới Bình", "Trần Văn Thời", "U Minh"),
    "Cao Bằng" => array("Bảo Lạc", "Bảo Lâm", "Nguyên Bình", "Thạch An", "Hạ Lang", "Quảng Uyên", "Trà Lĩnh", "Trà Vinh", "Thạch An"),
    "Đắk Lắk" => array("Buôn Ma Thuột", "Buôn Hồ", "Cư Kuin", "Ea H'Leo", "Ea Súp", "Krông Ana", "Krông Búk", "Lăk", "M'Đrắk", "Đắk Mil", "Đắk Glong"),
    "Đắk Nông" => array("Gia Nghĩa", "Đắk Mil", "Đắk R'lấp", "Đắk Song", "Cư Jút", "Krông Nô", "Tuy Đức"),
    "Điện Biên" => array("Mường Lay", "Điện Biên", "Mường Nhé", "Mường Ảng", "Tủa Chùa", "Nậm Pồ", "Điện Biên Đông"),
    "Hà Giang" => array("Đồng Văn", "Vị Xuyên", "Quản Bạ", "Yên Minh", "Bắc Mê", "Hoàng Su Phì", "Mèo Vạc", "Xín Mần", "Bắc Quang", "Vị Xuyên"),
    "Hà Nam" => array("Phủ Lý", "Duy Tiên", "Kim Bảng", "Bình Lục", "Lý Nhân"),
    "Hà Tĩnh" => array("Hương Khê", "Kỳ Anh", "Cẩm Xuyên", "Đức Thọ", "Nghi Xuân", "Thạch Hà", "Can Lộc", "Vũ Quang", "Hương Sơn"),
    "Hòa Bình" => array("Cao Phong", "Đà Bắc", "Kim Bôi", "Lương Sơn", "Mai Châu", "Tân Lạc", "Yên Thủy", "Lạc Sơn"),
    "Hưng Yên" => array("An Thi", "Bình Giang", "Khoái Châu", "Kim Động", "Mỹ Hào", "Phố Nối", "Tiên Lữ", "Yên Mỹ", "Văn Lâm"),
    "Khánh Hòa" => array("Nha Trang", "Cam Ranh", "Ninh Hòa", "Diên Khánh", "Vạn Ninh", "Khánh Vĩnh", "Cam Lâm"),
    "Kiên Giang" => array("Rạch Giá", "Hà Tiên", "Phú Quốc", "Kiên Lương", "Gò Quao", "Tây An", "Châu Thành", "Giang Thành", "An Biên", "An Minh"),
    "Kon Tum" => array("Đăk Hà", "Đăk Tô", "Ngọc Hồi", "Sa Thầy", "Tu Mơ Rông", "Kon Plong", "Đăk Glei", "Tây Trà", "Ngọc Hồi"),
    "Lai Châu" => array("Mường Tè", "Nậm Nhùn", "Tam Đường", "Sìn Hồ", "Phong Thổ", "Tân Uyên", "Than Uyên"),
    "Lâm Đồng" => array("Đà Lạt", "Bảo Lộc", "Đơn Dương", "Đức Trọng", "Lạc Dương", "Lạc Sơn", "Di Linh", "Bảo Lâm", "Đạ Huoai", "Đạ Tẻh"),
    "Lạng Sơn" => array("Cao Lộc", "Đình Lập", "Hữu Lũng", "Lộc Bình", "Tràng Định", "Chi Lăng", "Văn Lãng", "Bắc Sơn", "Đồng Đăng"),
    "Nam Định" => array("Nam Trực", "Đức Long", "Trực Ninh", "Vụ Bản", "Nghĩa Hưng", "Xuân Trường", "Giao Thủy", "Hải Hậu", "Ý Yên"),
    "Nghệ An" => array("Vinh", "Thái Hòa", "Cửa Lò", "Thái Lão", "Hưng Nguyên", "Nam Đàn", "Đô Lương", "Nghĩa Đàn", "Quế Phong", "Tương Dương", "Con Cuông", "Kỳ Sơn", "Thanh Chương", "Hưng Nguyên"),
    "Ninh Bình" => array("Tam Điệp", "Kim Sơn", "Yên Khánh", "Yên Mô", "Hoa Lư", "Gia Viễn"),
    "Ninh Thuận" => array("Phan Rang - Tháp Chàm", "Ninh Hải", "Ninh Sơn", "Bác Ái", "Thuận Bắc", "Thuận Nam"),
    "Phú Thọ" => array("Việt Trì", "Phù Ninh", "Đoan Hùng", "Hạ Hòa", "Lâm Thao", "Tam Nông", "Tân Sơn", "Thanh Sơn", "Thanh Thủy", "Yên Lập"),
    "Phú Yên" => array("Tuy Hòa", "Sông Cầu", "Đồng Xuân", "Tây Hòa", "Phú Hòa", "Đông Hòa", "Sơn Hòa", "Tuy An"),
    "Quảng Bình" => array("Đồng Hới", "Bố Trạch", "Quảng Trạch", "Tuyên Hóa", "Minh Hóa", "Lệ Thủy", "Quảng Ninh", "Bố Trạch"),
    "Quảng Nam" => array("Tam Kỳ", "Hội An", "Điện Bàn", "Duy Xuyên", "Thăng Bình", "Phú Ninh", "Nông Sơn", "Tiên Phước", "Nam Giang", "Đại Lộc"),
    "Quảng Ngãi" => array("Sơn Tịnh", "Tư Nghĩa", "Bình Sơn", "Ba Tơ", "Mộ Đức", "Nghĩa Hành", "Trà Bồng", "Lý Sơn"),
    "Quảng Ninh" => array("Hạ Long", "Móng Cái", "Cẩm Phả", "Đông Triều", "Uông Bí", "Quảng Yên", "Vân Đồn", "Tiên Yên", "Bình Liêu", "Đầm Hà"),
    "Sóc Trăng" => array("Ngọc Mỹ", "Kế Sách", "Châu Thành", "Long Phú", "Mỹ Tú", "Thạnh Trị", "Vĩnh Châu", "Trần Đề"),
    "Sơn La" => array("Mộc Châu", "Mai Sơn", "Sốp Cộp", "Yên Châu", "Mường La", "Quỳnh Nhai", "Thường Xuân", "Phù Yên"),
    "Tây Ninh" => array("Tân Biên", "Tân Châu", "Hòa Thành", "Dương Minh Châu", "Châu Thành", "Bến Cầu", "Gò Dầu"),
    "Thái Bình" => array("Thái Thụy", "Hưng Hà", "Quỳnh Phụ", "Tiền Hải", "Vũ Thư", "Đông Hưng", "Kiến Xương"),
    "Thái Nguyên" => array("Sông Công", "Phổ Yên", "Đại Từ", "Định Hóa", "Phú Bình", "T.X Thái Nguyên"),
    "Thanh Hóa" => array("Thanh Hóa", "TP Thanh Hóa", "Bỉm Sơn", "Sầm Sơn", "Mường Lát", "Quan Hóa", "Ngọc Lạc", "Thạch Thành", "Thường Xuân", "Vĩnh Lộc", "Như Thanh", "Như Xuân", "Hậu Lộc", "Tĩnh Gia"),
    "Thừa Thiên Huế" => array("Huế", "Hương Thủy", "Hương Trà", "Phong Điền", "Quảng Điền", "Phú Vang", "Nam Đông", "A Lưới", "Phú Lộc"),
    "Tiền Giang" => array("Mỹ Tho", "Gò Công", "Gò Công Tây", "Châu Thành", "Chợ Gạo", "Cái Bè", "Tân Phước", "Tân Phú Đông"),
    "Vĩnh Long" => array("Vũng Liêm", "Mang Thít", "Tam Bình", "Bình Minh", "Long Hồ", "Trà Ôn", "Loan Mỹ"),
    "Yên Bái" => array("Yên Bình", "Mù Cang Chải", "Văn Chấn", "Trấn Yên", "Lục Yên", "Văn Yên", "Nguyễn Khải"),
);

// Form start
?>
<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <!-- SIM Type -->
                <h3><?php _e('Loại hình SIM', 'woocommerce'); ?></h3>
                <div class="form-group">
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="sim_type" value="sim-vat-ly" id="sim_vat_ly" checked>
                        <label class="form-check-label" for="sim_vat_ly"><?php _e('Sim vật lý', 'woocommerce'); ?></label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="sim_type" value="esim" id="esim">
                        <label class="form-check-label" for="esim"><?php _e('eSIM', 'woocommerce'); ?></label>
                    </div>
                </div>

                <!-- Receiver Information -->
                <h3><?php _e('Thông tin người nhận hàng', 'woocommerce'); ?></h3>
                <div class="form-group">
                    <label for="billing_first_name"><?php _e('Họ và tên', 'woocommerce'); ?> <span class="required">*</span></label>
                    <input type="text" class="form-control" name="billing_first_name" id="billing_first_name" value="<?php echo esc_attr( $checkout->get_value( 'billing_first_name' ) ); ?>" required>
                </div>
                <div class="form-group">
                    <label for="billing_phone"><?php _e('Số điện thoại', 'woocommerce'); ?> <span class="required">*</span></label>
                    <input type="tel" class="form-control" name="billing_phone" id="billing_phone" value="<?php echo esc_attr( $checkout->get_value( 'billing_phone' ) ); ?>" required>
                </div>
                <div class="form-group">
                    <label for="billing_email"><?php _e('Email', 'woocommerce'); ?> <span class="required">*</span></label>
                    <input type="email" class="form-control" name="billing_email" id="billing_email" value="<?php echo esc_attr( $checkout->get_value( 'billing_email' ) ); ?>" required>
                </div>

                <!-- Address Information -->
                <h3><?php _e('Địa chỉ nhận SIM', 'woocommerce'); ?></h3>
                <div class="form-group">
                    <label for="billing_state"><?php _e('Tỉnh/Thành phố', 'woocommerce'); ?> <span class="required">*</span></label>
                    <select class="form-control" name="billing_state" id="billing_state" required>
                        <option value=""><?php _e('Chọn Tỉnh/Thành phố', 'woocommerce'); ?></option>
                        <?php foreach ($locations as $province => $districts) : ?>
                            <option value="<?php echo esc_attr($province); ?>"><?php echo esc_html($province); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="billing_city"><?php _e('Quận/Huyện', 'woocommerce'); ?> <span class="required">*</span></label>
                    <select class="form-control" name="billing_city" id="billing_city" required>
                        <option value=""><?php _e('Chọn Quận/Huyện', 'woocommerce'); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="billing_address_1"><?php _e('Địa chỉ chi tiết', 'woocommerce'); ?> <span class="required">*</span></label>
                    <input type="text" class="form-control" name="billing_address_1" id="billing_address_1" value="<?php echo esc_attr( $checkout->get_value( 'billing_address_1' ) ); ?>" required>
                </div>
                <div class="form-group">
                    <label for="order_comments"><?php _e('Ghi chú', 'woocommerce'); ?></label>
                    <textarea class="form-control" name="order_comments" id="order_comments" placeholder="<?php _e('Ghi chú cho đơn hàng của bạn', 'woocommerce'); ?>"></textarea>
                </div>

                <!-- Payment Methods -->
                <h3><?php _e('Phương thức thanh toán', 'woocommerce'); ?></h3>
                <div class="form-group">
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="payment_method" value="vnpay" id="payment_vnpay" checked>
                        <label class="form-check-label" for="payment_vnpay"><?php _e('Thanh toán bằng VNPAY', 'woocommerce'); ?></label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="payment_method" value="cod" id="payment_cod">
                        <label class="form-check-label" for="payment_cod"><?php _e('Thanh toán tiền mặt khi nhận hàng', 'woocommerce'); ?></label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="payment_method" value="income_verified" id="payment_income_verified">
                        <label class="form-check-label" for="payment_income_verified"><?php _e('Dành cho người đã chứng minh thu nhập', 'woocommerce'); ?></label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="payment_method" value="momo" id="payment_momo">
                        <label class="form-check-label" for="payment_momo"><?php _e('Thanh toán online qua ví điện tử Momo', 'woocommerce'); ?></label>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Order Information -->
                <h3><?php _e('Đơn hàng', 'woocommerce'); ?></h3>
                <div class="order-summary">
                    <?php
                    $items = WC()->cart->get_cart();
                    foreach( $items as $cart_item_key => $cart_item ){
                        $product = $cart_item['data'];
                        $product_name = $product->get_name();
                        $product_price = $product->get_price();
                        $product_quantity = $cart_item['quantity'];

                        echo '<p>' . esc_html( $product_name ) . ' - Số lượng: ' . esc_html( $product_quantity ) . ' - Giá: ' . wc_price( $product_price ) . '</p>';
                    }
                    ?>
                </div>

                <!-- Discount Code -->
                <h3><?php _e('Nhập mã giảm giá', 'woocommerce'); ?></h3>
                <div class="form-group">
                    <input type="text" class="form-control" name="coupon_code" id="coupon_code" placeholder="<?php esc_attr_e('Mã giảm giá', 'woocommerce'); ?>" value="">
                    <button type="button" class="btn btn-primary mt-2" name="apply_coupon"><?php _e('Áp dụng', 'woocommerce'); ?></button>
                </div>
                <p><?php _e('Mã giảm giá đã áp dụng:', 'woocommerce'); ?> <span id="applied_coupon"><?php echo WC()->cart->get_applied_coupons() ? implode(', ', WC()->cart->get_applied_coupons()) : ''; ?></span></p>

                <!-- Payment Summary -->
                <h3><?php _e('Thanh toán', 'woocommerce'); ?></h3>
                <p><?php _e('Tạm tính:', 'woocommerce'); ?> <?php echo wc_price( WC()->cart->get_subtotal() ); ?></p>
                <p><?php _e('Phí vận chuyển:', 'woocommerce'); ?> <?php echo wc_price( WC()->cart->get_shipping_total() ); ?></p>
                <p><?php _e('Mã giảm giá:', 'woocommerce'); ?> <?php echo wc_price( WC()->cart->get_discount_total() ); ?></p>
                <p><?php _e('Thành tiền:', 'woocommerce'); ?> <?php echo wc_price( WC()->cart->total ); ?></p>
                <p><?php _e('Cần thanh toán:', 'woocommerce'); ?> <?php echo wc_price( WC()->cart->total ); ?></p>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="confirm_payment" required>
                    <label class="form-check-label" for="confirm_payment"><?php _e('Xác nhận Tôi đã kiểm tra và xác định thông tin thanh toán là chính xác', 'woocommerce'); ?></label>
                </div>

                <button type="submit" class="btn btn-success mt-3"><?php _e('Thanh toán', 'woocommerce'); ?></button>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const provinceSelect = document.getElementById('billing_state');
        const citySelect = document.getElementById('billing_city');

        const districts = <?php echo json_encode($locations); ?>;

        provinceSelect.addEventListener('change', function () {
            const selectedProvince = this.value;
            citySelect.innerHTML = '<option value=""><?php _e('Chọn Quận/Huyện', 'woocommerce'); ?></option>';
            
            if (districts[selectedProvince]) {
                districts[selectedProvince].forEach(function (district) {
                    const option = document.createElement('option');
                    option.value = district;
                    option.text = district;
                    citySelect.appendChild(option);
                });
            }
        });
    });
</script>

<?php
do_action( 'woocommerce_checkout_order_review' ); // Review Order
get_footer();
?>
