@extends('layouts.app')
@include('common.navbar')

@section('content')
<style>
    body {
        background-image: url('/storage/images/bg-photo.jpeg');
        background-size: cover; /* Cover the entire viewport */
        background-position: center; /* Center the background image */
        background-repeat: no-repeat; /* Prevent the image from repeating */
    }
    .dashboard-card {
        background: #565656;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
    }
    
    .card-header {
        background: #3a8f66 !important;
        color: white;
        font-weight: bold;
    }
    
    .stat-number {
        font-size: 2.2rem;
        font-weight: bold;
        color: #3a8f66;
    }
    
    .chart-container {
        height: 400px;
        background: #565656;
        border-radius: 8px;
        padding: 15px;
        margin-top: 20px;
    }
    
    .recent-sales-table {
        background: #565656;
        color: white;
    }
    
    .recent-sales-table th {
        background: #3a8f66 !important;
    }
</style>

<div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
    <div class="row mt-4">
        <!-- Quick Stats -->
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="text-white">TOTAL REVENUE</div>
                <div class="stat-number">$12,450</div>
                <small class="text-success">↑ 12% from last month</small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="text-white">TODAY'S SALES</div>
                <div class="stat-number">$1,230</div>
                <small class="text-success">↑ 8% yesterday</small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="text-white">AVG ORDER VALUE</div>
                <div class="stat-number">$45.60</div>
                <small class="text-warning">↓ 2% from last month</small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="text-white">ITEMS SOLD</div>
                <div class="stat-number">2,340</div>
                <small class="text-success">↑ 15% from last month</small>
            </div>
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card recent-sales-table">
                <div class="card-header">Recent Transactions</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Transaction ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['recent_sales'] as $sale)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($sale->sales_date)->format('Y-m-d') }}</td>
                                    <td>#{{ $sale->sales_id }}</td>
                                    <td>{{ $sale->first_name }} {{ $sale->last_name }}</td>
                                    <td>${{ number_format($sale->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{  empty($sale->payment_method) || $sale->payment_method === 'cash' ? 'success' : 'primary' }}">
                                            {{ strtoupper($sale->payment_method  ?? 'cash') }}
                                        </span>
                                    </td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Chart -->
<div class="chart-container">
    <h5 class="text-white mb-3">Monthly Sales Overview</h5>
    <canvas id="salesChart"></canvas>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get chart context
    const ctx = document.getElementById('salesChart').getContext('2d');
    const monthlySales = @json($stats['monthly_sales']);
    
    // Process data for chart
    const months = [];
    const salesData = [];
    
    monthlySales.forEach(month => {
        // Convert month number to short name (Jan, Feb, etc)
        const monthName = new Date(2024, month.month - 1)
            .toLocaleString('default', { month: 'short' });
        months.push(monthName);
        salesData.push(month.total);
    });

    // Create the chart
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Sales',
                data: salesData,
                borderColor: '#3a8f66',
                tension: 0.4,
                fill: false,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#3a8f66',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    display: false
                },
                tooltip: {
                    backgroundColor: '#565656',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#3a8f66',
                    borderWidth: 1,
                    padding: 12
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { 
                        color: '#777',
                        borderDash: [5]
                    },
                    ticks: { 
                        color: '#fff',
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: { 
                        color: '#777',
                        display: false
                    },
                    ticks: { 
                        color: '#fff'
                    }
                }
            }
        }
    });
});
</script>
@endsection