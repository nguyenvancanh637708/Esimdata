jQuery(document).ready(function($) {
    const ctx = document.getElementById('ordersChart').getContext('2d');

    // Function to fetch chart data via AJAX
    function fetchChartData() {
        const package = $('#package').val();
        const fromDate = $('#from_date').val();
        const toDate = $('#to_date').val();
        const fromMonth = $('#from_month').val();
        const toMonth = $('#to_month').val();

        // Send AJAX request to fetch data
        $.ajax({
            url: order_obj.ajax_url,
            method: 'GET',
            data: {
                action: 'fetch_chart_data',
                package: package,
                from_date: fromDate,
                to_date: toDate,
                from_month: fromMonth,
                to_month: toMonth,
                order_nonce: order_obj.order_nonce
            },
            success: function(response) {
                const data = JSON.parse(response);
                // Draw chart with received data
                drawChart(data.labels, data.revenueData, data.orderCountData);
            }
        });
    }

    // Function to draw chart
    function drawChart(labels, revenueData, orderCountData) {
        const myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Doanh thu',
                        data: revenueData,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        fill: false,
                    },
                    {
                        label: 'Số đơn hàng',
                        data: orderCountData,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        fill: false,
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Call function when user clicks filter button
    $('#chart-filter').on('submit', function(e) {
        e.preventDefault();
        fetchChartData();
    });
});
