<?php
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
                    <!-- <span>Chọn gói cước:</span><br> -->
                    <select name="package[]" id="goicuoc_id" multiple>
                        <option value="">--Tất cả--</option>
                        <?php
                        $package_name = $_GET['package_name'] ?? '';
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
                            's' => $package_name, // Thêm tham số tìm kiếm
                        );
                        $products = get_posts($args);
                        foreach ($products as $product) {
                            $selected = isset($_GET['package']) && in_array($product->ID, $_GET['package']) ? 'selected' : '';
                            echo '<option value="' . esc_attr($product->ID) . '" ' . $selected . '>';
                            echo esc_html($product->post_title);
                            echo '</option>';
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

                $from_date = $_GET['from_date'] ?? '';
                $to_date = $_GET['to_date'] ?? '';
                $packages = $_GET['package'] ?? [];
                $sales_channel = $_GET['sales_channel'] ?? '';

                $query = "SELECT goicuoc_id, COUNT(*) AS total_orders, SUM(total_price) AS total_revenue FROM $table_name WHERE 1=1";

                if ($from_date) {
                    $query .= $wpdb->prepare(" AND created_date >= %s", $from_date . '-01');
                }
                if ($to_date) {
                    $query .= $wpdb->prepare(" AND created_date <= %s", date("Y-m-t", strtotime($to_date . '-01')));
                }

                if (!empty($packages)) {
                    $placeholders = implode(',', array_fill(0, count($packages), '%d'));
                    $query .= $wpdb->prepare(" AND goicuoc_id IN ($placeholders)", ...$packages);
                }

                if ($sales_channel) {
                    $query .= $wpdb->prepare(" AND sales_channel = %s", $sales_channel);
                }

                $query .= " GROUP BY goicuoc_id ORDER BY goicuoc_id";
                $results = $wpdb->get_results($query);

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
function enqueue_revenue_report_styles() {
    wp_enqueue_style('revenue-report-style', plugin_dir_url(__FILE__) . 'index.css'); // Adjust path if necessary
}
add_action('admin_enqueue_scripts', 'enqueue_revenue_report_styles');