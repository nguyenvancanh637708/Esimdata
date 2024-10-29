<?php
/*

Plugin Name: Send Comments

Description: Plugin cho phép khách hàng gửi bình luận.

Version: 1.5

Author: MaiATech

Author URI: 

*/
if ( !defined('ABSPATH') ) {
    exit; // Exit if accessed directly.
}
register_uninstall_hook(__FILE__, 'customer_plugin_uninstall');

function customer_plugin_uninstall() {
    // Xóa tất cả các comment liên quan đến plugin này
    global $wpdb;

    // Giả sử rằng các comment liên quan đến plugin này có một meta key đặc biệt hoặc một điều kiện xác định khác
    $wpdb->query("DELETE FROM {$wpdb->comments} WHERE comment_type = 'comment_post'");

}

define( 'CMT_URI', plugin_dir_url( __FILE__ ) );
define( 'CMT', plugin_dir_path( __FILE__ ) );
define( 'CMT_VERSION', '1.0' );

function my_theme_scripts() {
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_enqueue_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js', array(), null, true);
    }
    wp_enqueue_style( 'cmt', CMT_URI . 'assets/css/comment.css' );

	wp_enqueue_script( 'cmt', CMT_URI . 'assets/js/comment.js', array( 'jquery' ), CMT_VERSION, true );
    wp_localize_script( 'cmt', 'cmt_obj', array(
		'ajax_url'   => admin_url( 'admin-ajax.php' ),
		'cmt_nonce' => wp_create_nonce( 'cmt-nonce' )

	) );
}
add_action('wp_enqueue_scripts', 'my_theme_scripts');

//Xử lý bình luận bằng ajax
add_action( 'wp_ajax_comment_submit', 'customer_ajax_submit_comment' );
add_action( 'wp_ajax_nopriv_comment_submit', 'customer_ajax_submit_comment' );
function customer_ajax_submit_comment() {
    $nonce = $_POST['cmt_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'cmt-nonce' ) ) {
        die( $_POST['cmt_nonce'] );
    }
    // Lấy múi giờ hiện tại
    $timezone = get_option('timezone_string');
    if (!$timezone) {
        $timezone = 'UTC+7'; // Mặc định là múi giờ Việt Nam nếu không được thiết lập
    }
   
    $commentdata = array(
        'comment_post_ID' => sanitize_text_field($_POST['comment_post_ID']),
        'comment_content' =>  sanitize_text_field( $_POST['comment_content']) ,
        'comment_author' =>  sanitize_text_field( $_POST['comment_author']),
        'comment_type' => 'comment_post',
        'comment_parent' => intval($_POST['comment_parent_id']),// Khách hàng không đăng nhập
        'comment_approved' => 1,
        'comment_date'=>current_time('mysql'),
        'comment_date_gmt' => current_time('mysql', 1)
    );
    $comment_id = wp_insert_comment($commentdata);

    if ($comment_id) {
        $comment = get_comment($comment_id);
        $comment_html= (int)$comment->comment_parent== 0 ? customer_get_comment_html($comment) : customer_get_comment_child_html($comment);
        
        $response = array(
            'comment_html' => $comment_html,
            'comment_parent_id' => $comment->comment_parent
        );
        wp_send_json_success($response);
    } else {
        wp_send_json_error('Gửi bình luận thất bại. Vui lòng thử lại.');
    }
   
}
function get_number_of_child_comment($comment_id){
    $args = array(
        'type' => 'comment_post',
        'status' => 'approve',
        'post_id' => get_the_ID(),
        'parent' => $comment_id,
    );
    $comments_sub = get_comments($args);

    return count($comments_sub);
}
// XỬ lý like bình luận
function handle_like_comment() {
    $nonce = $_POST['cmt_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'cmt-nonce' ) ) {
        die( $_POST['cmt_nonce'] );
    }
    $comment_id = intval($_POST['comment_id']);

    if ($comment_id) {
        $like_count = get_comment_meta($comment_id, 'like_count', true);
        $like_count = ($like_count) ? $like_count + 1 : 1;

        update_comment_meta($comment_id, 'like_count', $like_count);

        wp_send_json_success(array('like_count' => $like_count));
    } else {
        wp_send_json_error('Có lỗi xảy ra khi like bình luận.');
    }
}

add_action('wp_ajax_like_comment', 'handle_like_comment');
add_action('wp_ajax_nopriv_like_comment', 'handle_like_comment');

// Hàm trả về HTML của một bình luận cha
function customer_get_comment_html($comment) {
    ob_start();
    $url_send = CMT_URI . 'assets/img/send.svg';
    $url_like = CMT_URI . 'assets/img/like.svg';

    ?>
        <div class="reply-list" id="comment-<?php echo esc_html($comment->comment_ID); ?>">
            <div class="reply-header cmt-flex-center">
                <img src="https://esimdata.vn/wp-content/uploads/2024/07/anhs.svg" alt="image avatar" class="reply-avt">
                <span class="reply-user"><?php echo esc_html($comment->comment_author); ?></span>
            </div>
            <div class="reply-content"><?php echo esc_html($comment->comment_content); ?></div>
            <div class="reply-action cmt-flex-center">
                <span class="btn-reply" data-commentid="<?php echo esc_html($comment->comment_ID); ?>">Trả lời</span>
                <div class="cmt-flex-center like-count" data-commentId="<?php echo esc_html($comment->comment_ID); ?>" data-liked="false">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="#FF0000" xmlns="http://www.w3.org/2000/svg">
                        <path class="svg-like" fill-rule="evenodd" clip-rule="evenodd" d="M6.75 0C6.41179 0 6.11542 0.226366 6.02643 0.552662L3.92715 8.25H0.75C0.335786 8.25 0 8.58579 0 9V17.25C0 17.6642 0.335786 18 0.75 18H13.8203C14.5364 17.9992 15.2286 17.7424 15.7718 17.2758C16.315 16.8093 16.6734 16.1637 16.7823 15.456L17.59 10.206C17.6558 9.77852 17.6283 9.34187 17.5095 8.926C17.3907 8.51012 17.1834 8.12485 16.9018 7.7966C16.6202 7.46834 16.2709 7.20485 15.8779 7.02421C15.4849 6.84356 15.0575 6.75002 14.625 6.75H9.75V3C9.75 2.20435 9.43393 1.44129 8.87132 0.87868C8.30871 0.31607 7.54565 0 6.75 0ZM3.75 16.5V9.75H1.5V16.5H3.75ZM5.25 9.10044L7.2949 1.60247C7.48645 1.67716 7.66247 1.79115 7.81066 1.93934C8.09196 2.22064 8.25 2.60217 8.25 3V7.5C8.25 7.91421 8.58579 8.25 9 8.25H14.625C14.8412 8.25002 15.0549 8.29679 15.2514 8.3871C15.4479 8.47743 15.6225 8.60917 15.7633 8.7733C15.9041 8.93743 16.0078 9.13006 16.0672 9.338C16.1266 9.54592 16.1403 9.76428 16.1075 9.978L15.2997 15.228C15.2453 15.5819 15.0661 15.9046 14.7945 16.1379C14.5229 16.3712 14.1768 16.4996 13.8187 16.5H5.25V9.10044Z" fill="#767B92"/>
                    </svg>
                    <span>Thích <span id="countLike-<?php echo esc_html($comment->comment_ID)?>">
                        (<?php echo get_comment_meta( $comment->comment_ID, 'like_count', true ) ?: 0?>)
                    </span></span>
                </div>
            </div>
            <div id="create-form-reply" >
                <div class="reply-form" id="reply-form-<?php echo esc_html($comment->comment_ID);?>" style="display:none">
                    <form class="formReply"  data-commentId= "<?php echo esc_html($comment->comment_ID);?>">
                        <input type="hidden" name="comment_post_ID" id="post_id_<?php echo $comment->comment_ID?>" value="<?php echo get_the_ID(); ?>">
                        <input type="hidden" name="comment_parent_id" id="comment_parent_<?php echo $comment->comment_ID ?>" value="<?php echo $comment->comment_ID; ?>" />
                        <input type="text" placeholder="Nhập họ và tên" class="cmt-input" name="comment_author" id="user_<?php echo $comment->comment_ID?>" class="username-cmt">
                        <div style="position:relative">
                            <input type="text"  class="cmt-input" name="comment_content" id="content_<?php echo $comment->comment_ID?>" placeholder="Viết bình luận"></input>
                            <button type="submit" class="btn-submit-reply" data-commentid="<?php echo esc_html($comment->comment_ID); ?>">
                                <img src="<?php echo $url_send?>" alt="icon sent comment"></img>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="reply-reply-box">
                
                <?php
                $paged = 1;
                $comments_per_page = 3; 
                if(get_number_of_child_comment($comment->comment_ID) > 0): ?>
                <div class="reply-reply-list" id="reply-list-<?php echo $comment->comment_ID?>">
                    <?php customer_show_comments_child($comment,$paged,$comments_per_page)?>
                </div>
                <?php endif; 
                if(get_number_of_child_comment($comment->comment_ID)>$comments_per_page){
                    customer_show_load_more_button(get_number_of_child_comment($comment->comment_ID), $paged, $comments_per_page, $comment->comment_ID);
                }
                ?>
               
            </div>
           
        </div>
    <?php
    return ob_get_clean();
}

// Tạo shortcode để hiển thị form và bình luận
function customer_comment_shortcode($atts) {
    ob_start();
    customer_comment_form();
    return ob_get_clean();
}
add_shortcode('customer_comment', 'customer_comment_shortcode');
function customer_comment_form(){ ?>
    <section id="comment-container">
        <div class="comment-area">
            <div class="comment-box">
                <div class="comment-title">Bình luận</div>
                <form id="commentForm" action="post">
                    <input type="hidden" name="comment_post_ID" id="post_id" value="<?php echo get_the_ID(); ?>">
                    <input type="hidden" name="comment_parent_id" id="comment_parent" value="0" />
                    <input type="text" placeholder="Nhập họ tên" class="cmt-input" id="username-cmt" name="comment_author" required>
                    <textarea name="comment_content" id="content-cmt" cols="45" class="cmt-input" placeholder="Để lại ý kiến của bạn..."  required></textarea>
                    <button type="submit" class="btn" id="sent-cmt">Gửi bình luận</button>
                </form>
            </div>                           
        </div> 
        <div class="reply-ctn">
            <div class="reply-box">
                <?php customer_show_comments()?>
            </div>
            <?php 
            $paged = 1;
            $comments_per_page = 5; 
            show_load_more_parent(get_comments(array(
                'post_id' => get_the_ID(),
                'parent' => 0,
                'count' => true
            )),$paged,$comments_per_page)
            ?>
        </div>
    </section>
<?php } 

// Hiển thị bình luận CHA hiện có
function customer_show_comments($paged=1,$comments_per_page = 5) {
    $comments = get_comments(array(
        'post_id' => get_the_ID(),
        'status' => 'approve',
        'parent' => 0,
        'paged' => $paged,
        'number' => $comments_per_page,
        'offset' => ($paged - 1) * $comments_per_page,
       
    ));
    if ($comments) {
        foreach ($comments as $comment) {
            echo customer_get_comment_html($comment);
        }
    } 
    else {
        echo '<p class="no-comment">Chưa có bình luận.</p>';
    }
}
// Hiển thị bình luận CON hiện có
function customer_show_comments_child($comment, $paged = 1, $comments_per_page = 3) {
    $args = array(
        'type' => 'comment_post',
        'status' => 'approve',
        'post_id' => get_the_ID(),
        'parent' => $comment->comment_ID,
        'order' => 'DESC',
        'paged' => $paged,
        'number' => $comments_per_page,
        'offset' => ($paged - 1) * $comments_per_page,
    );
    $comments_sub = get_comments($args);
    if ($comments_sub) {?>
            <?php
                foreach ($comments_sub as $cmtSub) {
                    
                    echo customer_get_comment_child_html($cmtSub);
                }
            ?>
    <?php 
    } 
}
function customer_get_comment_child_html($cmtSub){
    ob_start();
    $current_time = current_time('timestamp');
    $commentSub_time = strtotime($cmtSub->comment_date);
    $child_time_diff = human_time_diff($commentSub_time, $current_time);
?>
    <div class="reply-reply-item">
        <div class="reply-header cmt-flex-center">
            <img src="https://esimdata.vn/wp-content/uploads/2024/07/Avatar-Mobi.svg" alt="image avatar" class="reply-avt">
            <span class="reply-user"><?php echo $cmtSub->comment_author?> <img src="https://esimdata.vn/wp-content/uploads/2024/07/live-area.svg"/></span>
        </div>
        <div class="reply-content"><?php echo $cmtSub->comment_content?></div>
        <span class="reply-time"><?php echo $child_time_diff; ?> trước </span>
    </div>   
<?php 
 return ob_get_clean();
}

add_action('wp_ajax_load_more_comments_child', 'load_more_comments_child_callback');
add_action('wp_ajax_nopriv_load_more_comments_child', 'load_more_comments_child_callback');

function load_more_comments_child_callback() {
    $nonce = $_POST['cmt_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'cmt-nonce' ) ) {
        die( $_POST['cmt_nonce'] );
    }
    
    $comment_id = intval($_POST['comment_id']);
    $paged = intval($_POST['paged']);
    $comments_per_page = 3; // Số lượng bình luận con trên mỗi trang
    ob_start();
    customer_show_comments_child(get_comment($comment_id), $paged, $comments_per_page);
    $comments_html = ob_get_clean();
    // Cập nhật nút "Xem thêm" nếu cần
    ob_start();
    customer_show_load_more_button(get_comments(array(
        'post_id' => get_the_ID(),
        'parent' => $comment_id,
        'count' => true
    )), $paged, $comments_per_page, $comment_id);
    $pagination_html = ob_get_clean();

    wp_send_json_success(array(
        'comments_html' => $comments_html,
        'pagination_html' => $pagination_html
    ));
}

add_action('wp_ajax_load_more_comments_parent', 'load_more_comments_parent_callback');
add_action('wp_ajax_nopriv_load_more_comments_parent', 'load_more_comments_parent_callback');

function load_more_comments_parent_callback() {
    $nonce = $_POST['cmt_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'cmt-nonce' ) ) {
        die( $_POST['cmt_nonce'] );
    }
    
    //$comment_id = intval($_POST['comment_id']);
    $paged = intval($_POST['paged']);
    $comments_per_page = 5; // Số lượng bình luận con trên mỗi trang
    ob_start();
    customer_show_comments($paged, $comments_per_page);
    $comments_html = ob_get_clean();
    // Cập nhật nút "Xem thêm" nếu cần
    ob_start();
    show_load_more_parent(get_comments(array(
        'post_id' => get_the_ID(),
        'parent' => 0,
        'count' => true
    )), $paged, $comments_per_page);
    $pagination_html = ob_get_clean();

    wp_send_json_success(array(
        'comments_html' => $comments_html,
        'pagination_html' => $pagination_html
    ));
}

//load more comment parent
function show_load_more_parent($total_comments, $paged, $comments_per_page){
    $total_pages = ceil($total_comments / $comments_per_page);
    if ($total_pages > 1 && $paged < $total_pages) {?>
        <button class="btn-more" data-page="<?php echo $paged + 1; ?>">Xem thêm bình luận</button>
    <?php 
    }
}
//load more comment child
function customer_show_load_more_button($total_comments, $paged, $comments_per_page, $comment_id) {
    $url_rep = CMT_URI. 'assets/img/rep.svg';
    $total_pages = ceil($total_comments / $comments_per_page);
    if ($total_pages > 1 && $paged < $total_pages) {
        $remaining_comments = $total_comments - ($paged * $comments_per_page);
        ?>
        <div class="reply-more" data-commentId="<?php echo $comment_id ?>" data-page="<?php echo $paged + 1; ?>">
            <img src="<?php echo $url_rep?>"/>
            <span>Xem thêm <span><?php echo $remaining_comments ?></span> câu trả lời</span>
        </div>
        <?php
    }
}
?>




      
