<?php
/*
Plugin Name: Order Management Esim
Description: Plugin quản lý danh sách đơn hàng
Version: 1.5
Author: MaiATech
Author URI: https://www.maiatech.com.vn/
*/

if ( !defined('ABSPATH') ) {
    exit; // Exit if accessed directly.
}

define( 'ORDER_URI', plugin_dir_url( __FILE__ ) );
define( 'ORDER', plugin_dir_path( __FILE__ ) );
define( 'ORDER_VERSION', '1.0' );

function my_theme_scripts() {
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_enqueue_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js', array(), null, true);
    }
    wp_enqueue_style( 'order', ORDER_URI . 'assets/css/index.css' );
    wp_enqueue_script( 'order', ORDER_URI . 'assets/js/index.js', array( 'jquery' ), ORDER_VERSION, true );
    wp_localize_script( 'order', 'order_obj', array(
        'ajax_url'   => admin_url( 'admin-ajax.php' ),
        'order_nonce' => wp_create_nonce( 'order-nonce' )
    ));
}
add_action('wp_enqueue_scripts', 'my_theme_scripts');

// Thêm mục chính cho danh sách đơn hàng
function my_custom_menu_page() {
    add_menu_page(
        'Danh sách đơn hàng',
        'Đơn hàng Esim',
        'manage_options',
        'danh-sach-don-hang',
        'list_custom_order_html',
        'dashicons-admin-generic',
        60
    );

    // Thêm trang biểu đồ
    add_submenu_page(
        'danh-sach-don-hang',
        'Biểu đồ đơn hàng',
        'Biểu đồ',
        'manage_options',
        'bieu-do-don-hang',
        'render_chart_page'
    );
    add_submenu_page(
        'danh-sach-don-hang',
        'Báo cáo doanh thu',
        'Doanh thu theo tháng',
        'manage_options',
        'bao-cao-doanh-thu',
        'render_revenue_report_page'
    );
}
add_action( 'admin_menu', 'my_custom_menu_page' );

// Hàm hiển thị danh sách đơn hàng
function list_custom_order_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    global $wpdb;
    $table_name = 'wp_esim_orders';

    $from_date = isset($_GET['from_date']) && !empty($_GET['from_date']) ? sanitize_text_field($_GET['from_date']) : date('Y-m-d', strtotime('-7 days'));
    $to_date = isset($_GET['to_date']) && !empty($_GET['to_date']) ? sanitize_text_field($_GET['to_date']) : date('Y-m-d');
    
    $sales_channel = isset($_GET['sales_channel']) ? sanitize_text_field($_GET['sales_channel']) : '';
    $customer_phone = isset($_GET['customer_phone']) ? sanitize_text_field($_GET['customer_phone']) : '';
    $user_id = isset($_GET['user_id']) ? sanitize_text_field($_GET['user_id']) : '';

    // Xây dựng câu truy vấn dựa trên filter
    $query = "SELECT * FROM $table_name WHERE 1=1";

    if ($from_date) {
        $query .= " AND created_date >= '$from_date'";
    }
    if ($to_date) {
        $to_date_end = $to_date . ' 23:59:59';
        $query .= " AND created_date <= '$to_date_end'";
    }
    
    if ($sales_channel) {
        $query .= " AND sales_channel = '$sales_channel'";
    }
    if ($customer_phone) {
        $query .= " AND customer_phone LIKE '%$customer_phone%'";
    }
    if ($user_id) {
        $query .= " AND user_id LIKE '%$user_id%'";
    }

    $orders = $wpdb->get_results($query);
    ?>

    <div class="wrap">
        <h1 class="wp-heading-inline">Danh sách đơn hàng</h1>
        <form id="orders-filter" method="get">
            <input type="hidden" name="page" value="danh-sach-don-hang">
            <div class="tablenav top">
                <div class="alignleft actions">
                    <span>Thời gian đặt hàng:</span>
                    <span>từ ngày </span><input type="date" name="from_date" value="<?php echo esc_attr($from_date); ?>">
                    <span> đến ngày </span><input type="date" name="to_date" value="<?php echo esc_attr($to_date); ?>">
                </div>
                <div class="alignleft actions">
                    <select name="sales_channel">
                        <option value="">Kênh bán</option>
                        <option <?php selected($sales_channel, 'Esimdata'); ?> value="Esimdata">Esimdata</option>
                        <option <?php selected($sales_channel, 'Landing'); ?> value="Landing">Landing</option>
                    </select>
                    <input type="text" name="customer_phone" placeholder="Số điện thoại" value="<?php echo esc_attr($customer_phone); ?>">
                    <input type="text" name="user_id" placeholder="Nhân viên gọi" value="<?php echo esc_attr($user_id); ?>">
                    <input type="submit" name="filter_action" id="order-query-submit" class="button" value="Lọc">
                </div>
            </div>
            <table class="wp-list-table widefat fixed striped table-view-list orders wc-orders-list-table wc-orders-list-table-shop_order">
                <thead>
                    <tr>
                        <th class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox"></th>
                        <th>Thời gian đặt</th>
                        <th>Họ tên KH</th>
                        <th>Số điện thoại</th>
                        <th>Gói cước</th>
                        <th>Sim số chọn</th>
                        <th>Giá sim</th>
                        <th>Giá gói cước</th>
                        <th>Tổng thanh toán</th>
                        <th>Kênh bán</th>
                        <th>Nhân viên gọi</th>
                        <th>Ghi chú</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="the-list" data-wp-lists="list:order">
                    <?php
                    if ($orders) {
                        foreach ($orders as $order) { ?>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td><?php echo esc_html($order->created_date); ?></td>
                                <td><?php echo esc_html($order->customer_name); ?></td>
                                <td><?php echo esc_html($order->customer_phone); ?></td>
                                <td><?php echo esc_html(wc_get_product($order->goicuoc_id)->get_name()); ?></td>
                                <td><?php echo esc_html(wc_get_product($order->sim_id)->get_name()); ?></td>
                                <td><?php echo number_format($order->sim_price, 0, ',', '.'); ?></td>
                                <td><?php echo number_format($order->goicuoc_price, 0, ',', '.'); ?></td>
                                <td><strong><?php echo number_format($order->total_price, 0, ',', '.'); ?></strong></td>
                                <td><?php echo esc_html($order->sales_channel); ?></td>
                                <td><?php echo esc_html($order->user_id); ?></td>
                                <td><?php echo esc_html($order->note); ?></td>
                                <td><?php echo esc_html($order->status); ?></td>
                                <td>
                                    <button type="button" class="button">Xem</button>
                                    <button type="button" class="button">Sửa</button>
                                </td>
                            </tr>
                        <?php } 
                    } else { ?>
                        <tr><td colspan="13">Không tìm thấy đơn hàng nào.</td></tr>
                    <?php } ?>
                </tbody>
            </table>    
        </form>
    </div>
<?php }

//BIỂU ĐỒ

// Handle AJAX request to fetch chart data
function fetch_chart_data() {
    // Kiểm tra nonce để bảo mật
    check_ajax_referer('order-nonce', 'order_nonce');

    global $wpdb;
    $table_name = 'wp_esim_orders';

    // Lấy dữ liệu từ AJAX request
    $package = isset($_GET['package']) ? sanitize_text_field($_GET['package']) : '';
    $from_date = isset($_GET['from_date']) ? sanitize_text_field($_GET['from_date']) : '';
    $to_date = isset($_GET['to_date']) ? sanitize_text_field($_GET['to_date']) : '';
    $from_month = isset($_GET['from_month']) ? sanitize_text_field($_GET['from_month']) : '';
    $to_month = isset($_GET['to_month']) ? sanitize_text_field($_GET['to_month']) : '';

    // Tạo query để lấy dữ liệu
    $query = "SELECT DATE(created_date) as order_date, SUM(total_price) as total_revenue, COUNT(*) as total_orders FROM $table_name WHERE 1=1";

    if ($package) {
        $query .= $wpdb->prepare(" AND goicuoc_id = %s", $package);
    }
    if ($from_date) {
        $query .= $wpdb->prepare(" AND created_date >= %s", $from_date);
    }
    if ($to_date) {
        $query .= $wpdb->prepare(" AND created_date <= %s", $to_date . ' 23:59:59');
    }
    if ($from_month) {
        $query .= $wpdb->prepare(" AND DATE_FORMAT(created_date, '%Y-%m') >= %s", $from_month);
    }
    if ($to_month) {
        $query .= $wpdb->prepare(" AND DATE_FORMAT(created_date, '%Y-%m') <= %s", $to_month);
    }

    $query .= " GROUP BY order_date ORDER BY order_date";
    $results = $wpdb->get_results($query);

    // Chuẩn bị dữ liệu cho biểu đồ
    $labels = [];
    $revenueData = [];
    $orderCountData = [];

    foreach ($results as $result) {
        $labels[] = $result->order_date;
        $revenueData[] = $result->total_revenue;
        $orderCountData[] = $result->total_orders;
    }

    // Trả dữ liệu về cho AJAX
    wp_send_json([
        'labels' => $labels,
        'revenueData' => $revenueData,
        'orderCountData' => $orderCountData,
    ]);
}

add_action('wp_ajax_fetch_chart_data', 'fetch_chart_data');
add_action('wp_ajax_nopriv_fetch_chart_data', 'fetch_chart_data');




// Render chart page
function render_chart_page() {
    ?>
    <div class="wrap">
        <h1>Biểu đồ đơn hàng</h1>
        <form id="chart-filter" method="get">
            <input type="hidden" name="page" value="bieu-do-don-hang">
            <div>
                <label for="package">Gói cước:</label>
                <select name="package" id="package">
                    <option value="">Chọn gói cước</option>
                    <?php
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
                    );
                    $products = get_posts($args);
                    foreach ($products as $product) {
                        echo '<option value="' . esc_attr($product->ID) . '">' . esc_html($product->post_title) . '</option>';
                    }
                    ?>
                </select>

                <label for="from_date">Từ ngày:</label>
                <input type="date" name="from_date" id="from_date">
                <label for="to_date">Đến ngày:</label>
                <input type="date" name="to_date" id="to_date">
                <label for="from_month">Tháng từ:</label>
                <input type="month" name="from_month" id="from_month">
                <label for="to_month">Tháng đến:</label>
                <input type="month" name="to_month" id="to_month">
                <input type="submit" class="button" value="Lọc">
            </div>
        </form>
        <canvas id="ordersChart" width="400" height="200"></canvas>
    </div>
    <?php
}




// Thêm script cho biểu đồ
function my_chart_scripts($hook) {
    if ($hook !== 'toplevel_page_bieu-do-don-hang') {
        return;
    }

    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), null, true);
    wp_enqueue_script('chart-custom', ORDER_URI . 'assets/js/chart.js', array('chart-js', 'jquery'), null, true);
    wp_localize_script('chart-custom', 'order_obj', array(
        'ajax_url'   => admin_url('admin-ajax.php'),
        'order_nonce' => wp_create_nonce('order-nonce')
    ));
}
add_action('admin_enqueue_scripts', 'my_chart_scripts');

//Báo cáo doanh thu
function render_revenue_report_page() {
    ?>
    <div class="wrap">
        <h1>Báo cáo doanh thu theo gói cước</h1>
        <form id="revenue-filter" method="get">
            <input type="hidden" name="page" value="bao-cao-doanh-thu">
            <div class="tablenav top">
                <div class="alignleft actions">
                    <span>Thời gian đặt hàng:</span>
                    <span>từ tháng </span><input type="month" name="from_date" value="<?php echo esc_attr($_GET['from_date'] ?? ''); ?>">
                    <span> đến tháng </span><input type="month" name="to_date" value="<?php echo esc_attr($_GET['to_date'] ?? ''); ?>">
                </div>
                <div class="alignleft actions">
                    <select name="package" id="package">
                        <option value="">Chọn gói cước</option>
                        <?php
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
                        );
                        $products = get_posts($args);
                        foreach ($products as $product) {
                            $selected = isset($_GET['package']) && $_GET['package'] == $product->ID ? 'selected' : '';
                            echo '<option value="' . esc_attr($product->ID) . '" ' . $selected . '>' . esc_html($product->post_title) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="alignleft actions">
                    <select name="sales_channel">
                        <option value="">Kênh bán</option>
                        <option <?php selected($_GET['sales_channel'] ?? '', 'Esimdata'); ?> value="Esimdata">Esimdata</option>
                        <option <?php selected($_GET['sales_channel'] ?? '', 'Landing'); ?> value="Landing">Landing</option>
                    </select>
                </div>
                <div class="alignleft actions">
                    <input type="submit" name="filter_action" id="order-query-submit" class="button" value="Tìm kiếm">
                    <input type="submit" name="export_excel" class="button" value="Xuất Excel">
                </div>
            </div>
        </form>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Loại sim</th>
                    <th>Số lượng bán</th>
                    <th>Doanh thu BH</th>
                </tr>
            </thead>
            <tbody>
                <?php
                global $wpdb;
                $table_name = 'wp_esim_orders';

                // Lọc thời gian đặt hàng
                $from_date = $_GET['from_date'] ?? '';
                $to_date = $_GET['to_date'] ?? '';
                $package = $_GET['package'] ?? '';
                $sales_channel = $_GET['sales_channel'] ?? '';

                $query = "SELECT goicuoc_id, COUNT(*) AS total_orders, SUM(total_price) AS total_revenue
                          FROM $table_name 
                          WHERE 1=1";

                // Điều kiện thời gian
                if ($from_date) {
                    $query .= $wpdb->prepare(" AND created_date >= %s", $from_date . '-01');
                }
                if ($to_date) {
                    $query .= $wpdb->prepare(" AND created_date <= %s", date("Y-m-t", strtotime($to_date . '-01')));
                }

                // Điều kiện gói cước
                if ($package) {
                    $query .= $wpdb->prepare(" AND goicuoc_id = %d", $package);
                }

                // Điều kiện kênh bán
                if ($sales_channel) {
                    $query .= $wpdb->prepare(" AND sales_channel = %s", $sales_channel);
                }

                $query .= " GROUP BY goicuoc_id ORDER BY goicuoc_id";
                $results = $wpdb->get_results($query);

                // Kiểm tra nếu có yêu cầu xuất Excel
                if (isset($_GET['export_excel'])) {
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment; filename="doanh_thu_theo_goi_cuoc.xls"');
                    echo "Loại sim\tSố lượng bán\tDoanh thu BH\n";
                    foreach ($results as $result) {
                        $product = wc_get_product($result->goicuoc_id);
                        $product_name = $product ? $product->get_name() : 'Không xác định';
                        echo $product_name . "\t" . $result->total_orders . "\t" . number_format($result->total_revenue, 0, ',', '.') . "\n";
                    }
                    exit;
                }

                // Hiển thị dữ liệu trên bảng
                foreach ($results as $result) {
                    $product = wc_get_product($result->goicuoc_id);
                    $product_name = $product ? esc_html($product->get_name()) : 'Không xác định';

                    echo '<tr>';
                    echo '<td>' . $product_name . '</td>';
                    echo '<td>' . esc_html($result->total_orders) . '</td>';
                    echo '<td>' . number_format($result->total_revenue, 0, ',', '.') . ' VNĐ</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}


