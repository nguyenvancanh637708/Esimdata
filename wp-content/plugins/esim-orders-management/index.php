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
define( 'ORDER_VERSION', '1.1' );

function my_theme_scripts() {
    wp_enqueue_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js', array(), null, true);
  // Thêm CSS và JS của Select2
  wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
  wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), null, true);

    wp_enqueue_style( 'order', ORDER_URI . 'assets/css/index.css');
    wp_enqueue_script( 'order', ORDER_URI . 'assets/js/index.js', array( 'jquery' ), ORDER_VERSION, true );
    wp_localize_script( 'order', 'order_obj', array(
        'ajax_url'   => admin_url( 'admin-ajax.php' ),
        'order_nonce' => wp_create_nonce( 'order-nonce' )
    ));
     // Khởi tạo Select2 cho #sales_channel
     wp_add_inline_script('select2-js', "
     jQuery(document).ready(function($) {
         $('#goicuoc_id').select2({
             allowClear: true,
             placeholder: 'Loại gói cước',
         });

         $('#user_id').select2({
             allowClear: true,
             placeholder: 'Nhân viên tư vấn',
         });
         
     });
 ");
    
}
add_action('wp_enqueue_scripts', 'my_theme_scripts');
add_action('admin_enqueue_scripts', 'my_theme_scripts'); 

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
    add_submenu_page(
        null, // Không hiển thị trong menu chính
        'Edit Order', // Tiêu đề trang
        'Edit Order', // Tiêu đề menu
        'manage_options', // Quyền truy cập
        'edit_order_info', // Slug của trang
        'update_customer_order_info_html' // Hàm hiển thị trang
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

// Xử lý cập nhật thông tin danh sách khách hàng đặt hàng
// Import file edit-order.php
require_once plugin_dir_path(__FILE__) . 'edit-order.php';
require_once plugin_dir_path(__FILE__) . 'revenue-report.php';

//Hiển thị danh sách khách hàng đặt hàng
function list_custom_order_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    global $wpdb;
    $table_name = 'wp_esim_orders';
    $args_goi_cuoc = [
        'post_type'      => ['product', 'product_variation'],
        'posts_per_page' => -1,
        'tax_query'      => [
            [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => 'goi-cuoc', // Danh mục "Gói cước"
            ],
        ],
        
    ];
    $listGoiCuoc = new WP_Query($args_goi_cuoc);

   // Lấy và xử lý các tham số đầu vào
    $from_date = isset($_GET['from_date']) && !empty($_GET['from_date']) ? sanitize_text_field($_GET['from_date']) : date('Y-m-d', strtotime('-7 days'));
    $to_date = isset($_GET['to_date']) && !empty($_GET['to_date']) ? sanitize_text_field($_GET['to_date']) : date('Y-m-d');
    $sales_channel = isset($_GET['sales_channel']) ? sanitize_text_field($_GET['sales_channel']) : '';
    $customer_phone = isset($_GET['customer_phone']) ? sanitize_text_field($_GET['customer_phone']) : '';

    // Lọc và chuẩn hóa user_id
    $user_id = isset($_GET['user_id']) && is_array($_GET['user_id']) ? array_filter($_GET['user_id'], 'is_numeric') : array();
    $user_id = array_map('intval', $user_id);

    // Lọc và chuẩn hóa goicuoc_id
    $goicuoc_id = isset($_GET['goicuoc_id']) && is_array($_GET['goicuoc_id']) ? array_filter($_GET['goicuoc_id'], 'is_numeric') : array();
    $goicuoc_id = array_map('intval', $goicuoc_id);

    // Khởi tạo câu truy vấn
    $query = "SELECT * FROM $table_name WHERE 1=1";
    $params = [];

    // Điều kiện từ ngày
    if ($from_date) {
        $query .= " AND created_date >= %s";
        $params[] = $from_date;
    }

    // Điều kiện đến ngày
    if ($to_date) {
        $to_date_end = $to_date . ' 23:59:59';
        $query .= " AND created_date <= %s";
        $params[] = $to_date_end;
    }

    // Điều kiện kênh bán hàng
    if ($sales_channel) {
        $query .= " AND sales_channel = %s";
        $params[] = $sales_channel;
    }

    // Điều kiện số điện thoại khách hàng
    if ($customer_phone) {
        $query .= " AND customer_phone LIKE %s";
        $params[] = '%' . $wpdb->esc_like($customer_phone) . '%';
    }

    // Điều kiện user_id
    if (!empty($user_id)) {
        $user_ids = implode(',', $user_id);
        $query .= " AND user_id IN ($user_ids)";
    }

    // Điều kiện goicuoc_id
    if (!empty($goicuoc_id)) {
        $all_ids = [];
        
        foreach ($goicuoc_id as $parent_id) {
            $all_ids[] = $parent_id;

            // Lấy biến thể của sản phẩm
            $product = wc_get_product($parent_id);
            if ($product && $product->is_type('variable')) {
                $variation_ids = $product->get_children();
                $all_ids = array_merge($all_ids, $variation_ids);
            }
        }

        // Chỉ lấy các giá trị số
        $all_ids = array_map('intval', array_unique($all_ids));

        if ($all_ids) {
            $placeholders = implode(',', array_fill(0, count($all_ids), '%d'));
            $query .= " AND goicuoc_id IN ($placeholders)";
            $params = array_merge($params, $all_ids);
        }
    }

    
    $per_page = 20; // Số lượng đơn hàng trên mỗi trang
    $query1 = $wpdb->prepare($query, ...$params);
    $total_count = count($wpdb->get_results($query1));
    $total_pages = ceil($total_count / $per_page);

    // Phân trang
    $current_page = max(1, (isset($_GET['paged']) ? intval($_GET['paged']) : 1));
    if($current_page > $total_pages) $current_page = $total_pages;
    $offset = ($current_page - 1) * $per_page; // Tính toán offset
    $query .= " ORDER BY created_date DESC LIMIT %d OFFSET %d";
    $params[] = $per_page;
    $params[] = $offset;


    // Chuẩn bị và thực hiện truy vấn
    $query = $wpdb->prepare($query, ...$params);
    $orders = $wpdb->get_results($query);

    $users = get_users();
    ?>

    <div class="wrap">
        <h1 class="wp-heading-inline">Danh sách đơn hàng</h1>
        <form id="orders-filter" method="get">
            <!-- Thêm hidden field để giữ giá trị 'page' -->
            <input type="hidden" name="page" value="danh-sach-don-hang">
            <div class="tablenav top">
                <div class="alignleft actions">
                    <span>Thời gian đặt hàng:</span>
                    <span>từ ngày </span><input type="date" name="from_date" value="<?php echo esc_attr($from_date); ?>">
                    <span> đến ngày </span><input type="date" name="to_date" value="<?php echo esc_attr($to_date); ?>">

                </div>
                <div class="alignleft actions filter-order">
                    <input type="text" name="customer_phone" placeholder="Số điện thoại" value="<?php echo esc_attr($customer_phone); ?>">
                    <select name="sales_channel" id="sales_channel">
                        <option value="">Kênh bán</option>
                        <option <?php selected($sales_channel, 'Esimdata'); ?> value="Esimdata">Esimdata</option>
                        <option <?php selected($sales_channel, 'Landing'); ?> value="Landing">Landing</option>
                    </select>
                    <select name="goicuoc_id[]" id="goicuoc_id" multiple >
                        <?php while ($listGoiCuoc->have_posts()) : $listGoiCuoc->the_post();?>
                        <option value="<?php echo get_the_ID(); ?>" <?php echo in_array(get_the_ID(),$goicuoc_id) ? 'selected' : ''; ?>>
                                <?php echo esc_html(get_the_title()); ?>
                            </option>
                        <?php endwhile?>
                    </select>
                    
                    <select name="user_id[]" id="user_id" multiple>
                        <option value="">-- Chọn người tư vấn --</option>
                        <?php 
                            foreach ($users as $user) {
                                echo '<option value="' . esc_attr($user->ID) . '" ' . (in_array($user->ID, $user_id) ? 'selected' : '') . '>' . esc_html($user->display_name) . '</option>';
                            }
                        ?>
                    </select>
                    <input type="submit" name="filter_action" id="order-query-submit" class="button" value="Tìm kiếm">
                </div>
            </div>
            <table class="wp-list-table widefat fixed striped table-view-list orders wc-orders-list-table wc-orders-list-table-shop_order">
                <thead>
                    <tr>
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
                        foreach ($orders as $order) { 
                            $edit_url = admin_url('admin.php?page=edit_order_info&order_id=' . $order->id);?>
                            <tr>
                                <td><?php echo esc_html($order->created_date); ?></td>
                                <td><?php echo esc_html($order->customer_name); ?></td>
                                <td><?php echo esc_html($order->customer_phone); ?></td>
                                <td class="name-item"><?php echo esc_html($order->package_name); ?></td>
                                <td class="name-item"><?php echo esc_html($order->phone_number); ?></td>
                                <td><?php echo number_format($order->sim_price, 0, ',', '.'); ?></td>
                                <td><?php echo number_format($order->goicuoc_price, 0, ',', '.'); ?></td>
                                <td><strong><?php echo number_format($order->total_price, 0, ',', '.'); ?></strong></td>
                                <td><?php echo esc_html($order->sales_channel); ?></td>
                                <td><?php echo get_user_by('ID', $order->user_id)->display_name;?></td>
                                <td><?php echo esc_html($order->note); ?></td>
                                <td><?php 
                                if($order->status==0){
                                    echo "--";
                                }else if($order->status==1){
                                    echo "<p style='color:green'>Thành công</p>";
                                }else{
                                    echo "<p style='color:red'>Thất bại</p>";
                                }
                                ?></td>
                                <td>
                                    <a href="<?php echo $edit_url?>" class="button">Sửa</a>
                                  
                                </td>
                            </tr>
                        <?php } 
                    } else { ?>
                        <tr><td colspan="13">Không tìm thấy đơn hàng.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php if ($total_pages > 1) : ?>
                        <span class="displaying-num"><?php echo number_format($total_count); ?> bản ghi</span>
                        <span class="pagination-links">
                            <!-- Previous Page Link -->
                            <?php if ($current_page > 1) : ?>
                                <a href="<?php echo add_query_arg('paged', 1); ?>"><<</a>
                                <a href="<?php echo add_query_arg('paged', $current_page - 1); ?>"><</a>
                            <?php endif; ?>

                            <!-- Current Page Info -->
                            <span><?php echo $current_page; ?> trên <?php echo $total_pages; ?></span>

                            <!-- Next Page Link -->
                            <?php if ($current_page < $total_pages) : ?>
                                <a href="<?php echo add_query_arg('paged', $current_page + 1); ?>">></a>
                                <a href="<?php echo add_query_arg('paged', $total_pages); ?>">>></a>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

        </form>
    </div>
<?php } ?>
