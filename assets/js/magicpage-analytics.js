jQuery(document).ready(function($) {
    $.post(MagicPageAnalytics.ajax_url, {
        action: 'magicpage_analytics_data',
        nonce: MagicPageAnalytics.nonce
    }, function(response) {
        if (!response.success) {
            console.error('Failed to fetch analytics data.');
            return;
        }

        const data = response.data;

        // 1️⃣ Page Visits Over Time
        new Chart(document.getElementById('chart-page-visits-over-time'), {
            type: 'line',
            data: {
                labels: Object.keys(data.visits_over_time),
                datasets: [{
                    label: 'Visits',
                    data: Object.values(data.visits_over_time),
                    backgroundColor: 'rgba(75,192,192,0.4)',
                    borderColor: 'rgba(75,192,192,1)',
                    fill: true
                }]
            }
        });

        // 2️⃣ Visits by Group
        new Chart(document.getElementById('chart-visits-by-group'), {
            type: 'bar',
            data: {
                labels: Object.keys(data.visits_by_group),
                datasets: [{
                    label: 'Pages',
                    data: Object.values(data.visits_by_group),
                    backgroundColor: '#4285f4'
                }]
            }
        });

        // 3️⃣ Visits by Location
        new Chart(document.getElementById('chart-visits-by-location'), {
            type: 'bar',
            data: {
                labels: Object.keys(data.visits_by_location),
                datasets: [{
                    label: 'Pages',
                    data: Object.values(data.visits_by_location),
                    backgroundColor: '#fbbc05'
                }]
            }
        });

        // 4️⃣ Top 10 Most Visited Pages
        new Chart(document.getElementById('chart-top-pages'), {
            type: 'horizontalBar',
            data: {
                labels: data.top_pages.map(p => p.title),
                datasets: [{
                    label: 'Visits',
                    data: data.top_pages.map(p => p.count),
                    backgroundColor: '#34a853'
                }]
            }
        });

        // 5️⃣ 404 Errors by URL
        new Chart(document.getElementById('chart-404s'), {
            type: 'bar',
            data: {
                labels: Object.keys(data.errors_404),
                datasets: [{
                    label: '404s',
                    data: Object.values(data.errors_404),
                    backgroundColor: '#ea4335'
                }]
            }
        });

        // 6️⃣ Page Visits by Device Type
        new Chart(document.getElementById('chart-device-type'), {
            type: 'pie',
            data: {
                labels: Object.keys(data.device_types),
                datasets: [{
                    data: Object.values(data.device_types),
                    backgroundColor: ['#3367d6', '#ff7043', '#9c27b0']
                }]
            }
        });

    }).fail(function() {
        console.error('AJAX request failed.');
    });
});