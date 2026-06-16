function loadImmunizationChart() {
    console.log('Loading immunization chart...');
    
    const chartCanvas = document.getElementById('immunizationChart');
    if (!chartCanvas) {
        console.error('Chart canvas not found!');
        return;
    }
    
    fetch('get_immunization_data.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Chart data received:', data);
            console.log('=== CHART DATA DEBUG ===');
            console.log('Full data:', data);
            console.log('Months:', data.months);
            console.log('Targets:', data.targets);
            console.log('Actual:', data.actual);
            console.log('=== END DEBUG ===');
            
            if (data.error) {
                console.error('Server error:', data.error);
                showChartError('Server error: ' + data.error);
                return;
            }
        
            if (chartCanvas.chart) {
                chartCanvas.chart.destroy();
            }
            
            const ctx = chartCanvas.getContext('2d');
            chartCanvas.chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: data.months,
        datasets: [
            {
                label: 'Target Quota',
                data: data.targets,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                borderWidth: 1
            },
            {
                label: 'Actual FIC',
                data: data.actual,
                borderColor: '#1cc88a',
                backgroundColor: 'rgba(28, 200, 138, 0.8)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Cumulative Target Population'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Months'
                }
            }
        }
    }
});
            
            console.log('Chart created successfully!');
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
            showChartError('Error loading chart data. Please try again.');
        });
}

function showChartError(message) {
    const chartContainer = document.querySelector('.chart-container');
    if (chartContainer) {
        chartContainer.innerHTML = '<div class="text-center text-muted p-4">' + message + '</div>';
    }
}