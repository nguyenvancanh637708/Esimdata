<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_VERSION', '3.1.1' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup() {
		if ( is_admin() ) {
			hello_maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'hello_elementor_register_menus', true ) ) {
			register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'hello-elementor' ) ] );
			register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'hello-elementor' ) ] );
		}

		if ( apply_filters( 'hello_elementor_post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'hello_elementor_add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);

			/*
			 * Editor Style.
			 */
			add_editor_style( 'classic-editor.css' );

			/*
			 * Gutenberg wide images.
			 */
			add_theme_support( 'align-wide' );

			/*
			 * WooCommerce.
			 */
			if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
	$theme_version_option_name = 'hello_theme_version';
	// The theme version saved in the database.
	$hello_theme_db_version = get_option( $theme_version_option_name );

	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
		update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
	}
}

if ( ! function_exists( 'hello_elementor_display_header_footer' ) ) {
	/**
	 * Check whether to display header footer.
	 *
	 * @return bool
	 */
	function hello_elementor_display_header_footer() {
		$hello_elementor_header_footer = true;

		return apply_filters( 'hello_elementor_header_footer', $hello_elementor_header_footer );
	}
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_elementor_scripts_styles() {
		$min_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( apply_filters( 'hello_elementor_enqueue_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor',
				get_template_directory_uri() . '/style' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor-theme-style',
				get_template_directory_uri() . '/theme' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( hello_elementor_display_header_footer() ) {
			wp_enqueue_style(
				'hello-elementor-header-footer',
				get_template_directory_uri() . '/header-footer' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
		if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_elementor_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
	}
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( ! function_exists( 'hello_elementor_add_description_meta_tag' ) ) {
	/**
	 * Add description meta tag with excerpt text.
	 *
	 * @return void
	 */
	function hello_elementor_add_description_meta_tag() {
		if ( ! apply_filters( 'hello_elementor_description_meta_tag', true ) ) {
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( empty( $post->post_excerpt ) ) {
			return;
		}

		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );

// Admin notice
if ( is_admin() ) {
	require get_template_directory() . '/includes/admin-functions.php';
}

// Settings page
require get_template_directory() . '/includes/settings-functions.php';

// Header & footer styling option, inside Elementor
require get_template_directory() . '/includes/elementor-functions.php';

if ( ! function_exists( 'hello_elementor_customizer' ) ) {
	// Customizer controls
	function hello_elementor_customizer() {
		if ( ! is_customize_preview() ) {
			return;
		}

		if ( ! hello_elementor_display_header_footer() ) {
			return;
		}

		require get_template_directory() . '/includes/customizer-functions.php';
	}
}
add_action( 'init', 'hello_elementor_customizer' );

if ( ! function_exists( 'hello_elementor_check_hide_title' ) ) {
	/**
	 * Check whether to display the page title.
	 *
	 * @param bool $val default value.
	 *
	 * @return bool
	 */
	function hello_elementor_check_hide_title( $val ) {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$current_doc = Elementor\Plugin::instance()->documents->get( get_the_ID() );
			if ( $current_doc && 'yes' === $current_doc->get_settings( 'hide_title' ) ) {
				$val = false;
			}
		}
		return $val;
	}
}
add_filter( 'hello_elementor_page_title', 'hello_elementor_check_hide_title' );

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if ( ! function_exists( 'hello_elementor_body_open' ) ) {
	function hello_elementor_body_open() {
		wp_body_open();
	}
}
//18-9-2024
//Cập nhật số điện thoại cho Danh mục sản phẩm Sim
// Cập nhật trường "Số điện thoại" khi sản phẩm được cập nhật
function update_phone_number_on_product_save($post_id) {
    // Kiểm tra nếu đây là một sản phẩm
    if (get_post_type($post_id) !== 'product') {
        return;
    }

    // Kiểm tra nếu sản phẩm thuộc danh mục SIM
    if (has_term('sim', 'product_cat', $post_id)) {
        // Lấy tên sản phẩm
        $product_name = get_the_title($post_id);

        // Cập nhật trường "Số điện thoại" trong ACF
        update_post_meta($post_id, 'so-dien-thoai', $product_name);
    }
}
add_action('save_post', 'update_phone_number_on_product_save');

// Cập nhật số lượng sản phẩm danh mục Sim
//Thiết lập số lượng sản phẩm cho danh mục Sim
function limit_sim_quantity_in_cart( $quantity, $product ) {
    // Kiểm tra nếu sản phẩm thuộc danh mục SIM
    $categories = array( 'sim' ); // Thay 'sim' bằng slug của danh mục SIM
    
    if ( has_term( $categories, 'product_cat', $product->get_id() ) ) {
        $quantity = 1; // Giới hạn số lượng mua là 1
    }
    
    return $quantity;
}
add_filter( 'woocommerce_quantity_input_args', 'limit_sim_quantity_in_cart', 10, 2 );

function set_sim_stock_quantity( $post_id, $post, $update ) {
    // Kiểm tra nếu đây là sản phẩm và thuộc danh mục SIM
    if ( get_post_type( $post_id ) === 'product' && has_term( 'sim', 'product_cat', $post_id ) ) {
        // Thiết lập số lượng tồn kho là 1
        update_post_meta( $post_id, '_stock', 1 );
        // Đảm bảo tình trạng sản phẩm là còn hàng
        update_post_meta( $post_id, '_stock_status', 'instock' );
        // Đảm bảo quản lý tồn kho được bật
        update_post_meta( $post_id, '_manage_stock', 'yes' );
    }
}
add_action( 'save_post', 'set_sim_stock_quantity', 10, 3 );

//Gán link Bootstrap cho template
function enqueue_bootstrap_icons() {
    wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css');
}
add_action('wp_enqueue_scripts', 'enqueue_bootstrap_icons');

//Đếm số lượt xem sản phẩm
// Tăng số lần xem khi người dùng xem sản phẩm
add_action('wp_head', 'increase_product_views');
function increase_product_views() {
    if (is_product()) {
        global $post;

        $views = get_post_meta($post->ID, 'product_views', true);
        $views = $views ? $views : 0;
        $views++;
        update_post_meta($post->ID, 'product_views', $views);
    }
}

// Thêm cột mới cho số lượt xem
add_filter('manage_edit-product_columns', 'add_views_column');

function add_views_column($columns) {
    $columns['product_views'] = __('Lượt xem', 'your-text-domain'); // Thay 'your-text-domain' bằng text domain của bạn
    return $columns;
}

// Hiển thị dữ liệu cho cột lượt xem
add_action('manage_product_posts_custom_column', 'show_views_column', 10, 2);

function show_views_column($column, $post_id) {
    if ($column == 'product_views') {
        $views = get_post_meta($post_id, 'product_views', true); // Sử dụng meta key của bạn
        echo esc_html($views ? $views : '0'); // Hiển thị số lượt xem, nếu không có hiển thị 0
    }
}

// Cho phép sắp xếp theo cột lượt xem
add_filter('manage_edit-product_sortable_columns', 'views_column_sortable');

function views_column_sortable($columns) {
    $columns['product_views'] = 'views'; // Đặt tên cho cột
    return $columns;
}

// Xử lý sắp xếp
add_action('pre_get_posts', 'sort_views_column');

function sort_views_column($query) {
    if (!is_admin()) return;
    if ($query->is_main_query() && $query->get('post_type') === 'product') {
        if ('views' === $query->get('orderby')) {
            $query->set('meta_key', 'product_views'); // Sử dụng meta key cho số lượt xem
            $query->set('orderby', 'meta_value_num'); // Sắp xếp theo giá trị số
        }
    }
}



