jQuery(document).ready(function($) {
    $('#toggle-more-products').on('click', function() {
        const showAll = $(this).data('show-all') === 1 ? 0 : 1;
        $(this).text(showAll ? 'Thu gọn' : 'Xem thêm').data('show-all', showAll);

        $.post(loadMoreProducts.ajax_url, { show_all: showAll }, function(data) {
            $('#product-list').html(data);
        });
    });
});
document.getElementById('toggle-more-products').addEventListener('click', function() {
    const button = this;
    const showAll = button.getAttribute('data-show-all');

    console.log('AJAX request sent'); // Thêm dòng này

    // Gửi yêu cầu AJAX để lấy sản phẩm
    $.post(ajaxurl, {
        action: 'load_more_products',
        show_all: showAll,
    }, function(response) {
        if (response) {
            document.getElementById('product-list').insertAdjacentHTML('beforeend', response);
            button.setAttribute('data-show-all', showAll === '0' ? '1' : '0');
            button.textContent = showAll === '0' ? 'Thu gọn' : 'Xem thêm';
        } else {
            button.remove(); // Ẩn nút nếu không còn sản phẩm
        }
    });
});

