jQuery(document).ready(function($) {
    $('#chart-filter').on('submit', function(e) {
        e.preventDefault();
        
        // Lấy dữ liệu từ form
        var formData = $(this).serialize();

        // Gọi AJAX
        $.ajax({
            url: order_obj.ajax_url,
            type: 'GET',
            data: formData + '&action=fetch_chart_data&order_nonce=' + order_obj.order_nonce,
            success: function(response) {
                // Vẽ biểu đồ với dữ liệu nhận được
                const ctx = document.getElementById('ordersChart').getContext('2d');
                const ordersChart = new Chart(ctx, {
                    type: 'line', // Hoặc 'bar' cho biểu đồ cột
                    data: {
                        labels: response.labels,
                        datasets: [
                            {
                                label: 'Doanh thu',
                                data: response.revenueData,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                fill: false
                            },
                            {
                                label: 'Số đơn hàng',
                                data: response.orderCountData,
                                borderColor: 'rgba(255, 99, 132, 1)',
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Ngày'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Giá trị'
                                },
                                beginAtZero: true
                            }
                        }
                    }
                });
            },
            error: function(error) {
                console.log('Error fetching chart data:', error);
            }
        });
    });
});
