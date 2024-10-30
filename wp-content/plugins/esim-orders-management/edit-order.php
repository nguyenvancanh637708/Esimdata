<?php
function update_customer_order_info_html(){
    global $wpdb;
    $table_name = 'wp_esim_orders';
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    //Lấy ra đơn hàng tương ứng với order_id
    $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $order_id));

    
 
    // Kiểm tra nếu không tìm thấy đơn hàng
    if (!$order) {
        echo '<div class="wrap"><h1 class="wp-heading-inline">Đơn hàng không tồn tại</h1></div>';
        return;
    }
    else{
        $sim = wc_get_product($order->sim_id);
        $afc_nha_mang = $sim ? $sim->get_meta('nha_mang') : '';
        $goicuoc = wc_get_product($order->goicuoc_id);

        if ($afc_nha_mang) {
            // Thiết lập truy vấn lấy sản phẩm thuộc danh mục "Sim"
            $args_sim = [
                'post_type'      => 'product',
                'posts_per_page' => -1,
                'tax_query'      => [
                    [
                        'taxonomy' => 'product_cat',
                        'field'    => 'slug',
                        'terms'    => 'sim', // Danh mục "Sim"
                    ],
                ],
                'meta_query'     => [
                    [
                        'key'     => 'nha_mang',
                        'value'   => $afc_nha_mang,
                        'compare' => '=', 
                    ],
                ],
            ];

            // Thiết lập truy vấn lấy sản phẩm thuộc danh mục "Gói cước"
            $args_goi_cuoc = [
                'post_type'      => 'product',
                'posts_per_page' => -1,
                'tax_query'      => [
                    [
                        'taxonomy' => 'product_cat',
                        'field'    => 'slug',
                        'terms'    => 'goi-cuoc', // Danh mục "Gói cước"
                    ],
                ],
                'meta_query'     => [
                    [
                        'key'     => 'nha_mang',
                        'value'   => $afc_nha_mang,
                        'compare' => '=', 
                    ],
                ],
            ];
            
            // Thực hiện truy vấn Sim
            $query_sim = new WP_Query($args_sim);
            $sim_products = [];
            if ($query_sim->have_posts()) {
                while ($query_sim->have_posts()) {
                    $query_sim->the_post();
                    $sim_products[] = wc_get_product(get_the_ID());
                }
                wp_reset_postdata();
            }

            // Thực hiện truy vấn Gói cước
            $query_goi_cuoc = new WP_Query($args_goi_cuoc);
            $goi_cuoc_variations = [];

            if ($query_goi_cuoc->have_posts()) {
                while ($query_goi_cuoc->have_posts()) {
                    $query_goi_cuoc->the_post();
                    $product = wc_get_product(get_the_ID());

                    // Kiểm tra nếu sản phẩm là loại variable (có biến thể)
                    if ($product->is_type('variable')) {
                        $variations = $product->get_children(); 

                        foreach ($variations as $variation_id) {
                            $variation = wc_get_product($variation_id);
                            $goi_cuoc_variations[] = $variation; 
                        }
                    } else {
                        // Nếu không có biến thể, chỉ thêm sản phẩm chính nếu meta khớp
                        if ($product->get_meta('nha_mang') == $afc_nha_mang) {
                            $goi_cuoc_variations[] = $product;
                        }
                    }
                }
                wp_reset_postdata(); // Đặt lại dữ liệu sau truy vấn
            }


        } else {
            echo '<div class="wrap"><h1 class="wp-heading-inline">Không lấy được nhà mạng của SIM</h1></div>';
            return;
        }
    }




    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //Cập nhật danh sách đơn hàng khách hàng đặt
        $updated_order = array(
            'customer_add'   => sanitize_text_field($_POST['customer_add']),
            'goicuoc_id' => sanitize_textarea_field($_POST['goicuoc_id']),
            'sim_id' => sanitize_textarea_field($_POST['sim_id']),
            'sim_type' => sanitize_textarea_field($_POST['sim_type']),
            'user_id' => sanitize_textarea_field($_POST['user_id']),
            'user_note' => sanitize_textarea_field($_POST['user_note']),
            'status' => sanitize_textarea_field($_POST['order_status']),
            'sim_priceShip'=>sanitize_textarea_field($_POST['sim_priceShip']),
            'sim_price'=>sanitize_textarea_field($_POST['sim_price']),
            'goicuoc_price'=>sanitize_textarea_field($_POST['goicuoc_price']),
            'total_price'=>sanitize_textarea_field($_POST['total_price']),

        ); 
        var_dump($updated_order);
        $where = array('id' => $order_id);
        // Thực hiện cập nhật dữ liệu
        $wpdb->update($table_name, $updated_order, $where);
        // Chuyển hướng về trang danh sách sau khi cập nhật thành công
        wp_redirect(admin_url('admin.php?page=danh-sach-don-hang'));
        exit;
    }
    $users = get_users();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Chỉnh sửa danh sách đặt hàng</h1>
        <form method="POST" action="">
            <table class="form-table">
                <tr>
                    <th><label for="customer_name">Họ tên KH</label></th>
                    <td><input name="customer_name" type="text" id="customer_name" readonly value="<?php echo esc_attr($order->customer_name); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="customer_phone">Số điện thoại</label></th>
                    <td><input name="customer_phone" type="text" id="customer_phone" readonly value="<?php echo esc_attr($order->customer_phone); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="customer_add">Địa chỉ nhận hàng</label></th>
                    <td><textarea rows="3" cols="30" class="regular-text" name="customer_add"><?php echo esc_attr($order->customer_add); ?></textarea>
                </tr>
                <tr>
                    <th><label for="note">Ghi chú của KH</label></th>
                    <td><textarea rows="3" cols="30" class="regular-text" name="note"><?php echo esc_attr($order->note); ?></textarea>
                </tr>
                <tr>
                    <th><label for="customer_date">Ngày đặt hàng</label></th>
                    <td><?php echo esc_attr($order->created_date);?></td>
                </tr>
                <tr>
                    <th><label for="sim_id">Sim số đã chọn</label></th>
                    <td>
                        <?php 
                            echo '<select name="sim_id" id="sim_id" class="regular-text">';
                            foreach ($sim_products as $product) {
                                $sim_id = $product->get_id();
                                $sim_name = $product->get_name();
                                $sim_price = $product->get_price();

                                $selected = ($sim_id == $order->sim_id) ? 'selected' : '';
                                echo '<option value="' . esc_attr($sim_id) . '" ' . $selected . ' data-sim-price="'.$sim_price.'">';
                                echo esc_html($sim_name);
                                echo " | Giá: ";
                                echo wc_price($sim_price);
                                echo '</option>';
                            }
                            echo '</select>';
                        ?>
                    </td>
                </tr>
                <tr>
                    <th><label for="goicuoc_id">Gói cước</label></th>
                    <td>
                        <?php 
                            echo '<select name="goicuoc_id" id="goicuoc_id" class="regular-text">';
                            foreach ($goi_cuoc_variations as $product) {
                                $goicuoc_id = $product->get_id();
                                $goicuoc_name = $product->get_name();
                                $goicuoc_price = $product->get_price();
                                $selected = ($goicuoc_id == $order->goicuoc_id) ? 'selected' : '';
                                echo '<option value="' . esc_attr($goicuoc_id) . '" ' . $selected . ' data-goicuoc-price="'.$goicuoc_price.'">';
                                echo esc_html($goicuoc_name);
                                echo " | Giá: ";
                                echo wc_price($goicuoc_price);
                                echo '</option>';
                            }
                            echo '</select>';
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th><label for="sim_type">Loại hình sim</label></th>
                    <td>
                        <select class="regular-text" name="sim_type">
                            <option value="0" <?php echo $order->sim_type==0?"selected":""?>>Sim vật lý</option>
                            <option value="1" <?php echo $order->sim_type==1?"selected":""?>>Esim</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="sim_price">Giá sim</label></th>
                    <td>
                        <span id="sim_price"><?php echo number_format($order->sim_price, 0, ',', '.'); ?></span>
                        <input type="hidden" name="sim_price" id="sim_price_hidden" class="regular-text" value="<?php echo $order->sim_price?>"> VNĐ
                    </td>
                </tr>
                
                <tr>
                    <th><label for="goicuoc_price">Giá gói cước</label></th>
                    <td>
                        <span id="goicuoc_price"><?php echo number_format($order->goicuoc_price, 0, ',', '.'); ?></span>
                        <input type="hidden" name="goicuoc_price" id="goicuoc_price_hidden" class="regular-text" value="<?php echo $order->goicuoc_price?>"> VNĐ 
                    </td>
                       
                </tr>
                <tr>
                    <th><label for="sim_priceShip">Tiền ship</label></th>
                    <td><input name="sim_priceShip" id="sim_priceShip" type="number" value="<?php echo $order->sim_priceShip ?>" class="regular-text"> VNĐ</td>
                </tr>
                <tr>
                    <th><label for="total_price">Tổng thanh toán</label></th>
                    <td><span id="total_price"><?php echo number_format($order->total_price, 0, ',', '.'); ?></span> 
                        <input name="total_price" id="total_price_hidden" type="hidden" value="<?php echo $order->total_price ?>" class="regular-text"> VNĐ</td>
                </tr>
                <tr>
                    <th><label for="sales_channel">Kênh bán</label></th>
                    <td><?php echo esc_html($order->sales_channel); ?></td>
                </tr>
                <tr>
                    <th><label for="user_id">Nhân viên gọi</label></th>
                    <td>
                        <select name="user_id" class="regular-text">
                            <option value="<?php echo esc_html($order->user_id==null || $order->user_id==0 ? "0": $order->user_id ); ?>"><?php echo get_user_by('ID', $order->user_id)->display_name;?></option>
                            <?php
                                foreach($users as $user){?>
                                    <option value="<?php echo esc_html($user->ID); ?>"><?php echo $user->display_name;?></option>
                                <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="user_note">Ghi chú của nhân viên</label></th>
                    <td>
                    <textarea rows="5" cols="30" id="user_note" name="user_note" class="regular-text"><?php echo esc_html($order->user_note); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th><label for="order_status">Trạng thái</label></th>
                    <td>
                        <select class="regular-text" name="order_status">
                            <?php 
                                if($order->status==0){
                                    echo "<option value='0'>Chọn trạng thái</option>";
                                }else if($order->status==1){
                                    echo "<option value='1'>Thành công</option>";
                                }else{
                                    echo "<option value='-1'>Thất bại</option>";
                                }
                                ?>
                            <option value="-1">Thất bại</option>
                            <option value="1">Thành công</option>
                        </select>
                    </td>
                </tr>
            </table>
            <p class="submit"><button type="submit" class="button button-primary">Lưu</button></p>
        </form>
    </div>
<?php }
?>
