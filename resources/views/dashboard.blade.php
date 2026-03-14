<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tinoss Technology Sales Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f5f7fb;
        }

        h1 {
            margin-bottom: 20px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .filters {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        select, button, a.reset-btn {
            padding: 10px 12px;
            font-size: 14px;
        }

        button {
            cursor: pointer;
        }

        a.reset-btn {
            text-decoration: none;
            background: #ddd;
            color: #000;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        table th {
            background: #f0f0f0;
        }
    </style>
</head>
<body>

    <h1>Tinoss Technology Sales Dashboard</h1>

    <div class="card">
        <form method="GET" action="/">
            <div class="filters">
                <select name="period">
                    <option value="">All Periods</option>
                    @foreach($periods as $p)
                        <option value="{{ $p }}" {{ $period == $p ? 'selected' : '' }}>
                            {{ $p }}
                        </option>
                    @endforeach
                </select>

                <select name="product">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->product_code }}" {{ $product == $p->product_code ? 'selected' : '' }}>
                            {{ $p->product_code }} - {{ $p->product }}
                        </option>
                    @endforeach
                </select>

                <select name="product_group">
                    <option value="">All Product Groups</option>
                    @foreach($productGroups as $pg)
                        <option value="{{ $pg }}" {{ $productGroup == $pg ? 'selected' : '' }}>
                            {{ $pg }}
                        </option>
                    @endforeach
                </select>

                <button type="submit">Filter</button>
                <a href="/" class="reset-btn">Reset</a>
            </div>
        </form>
    </div>

    <div class="card">
    <h3>Total Sales by Product Group and Period-Year</h3>
    <canvas id="salesChart"></canvas>
</div>

    <div class="card">
        <h3>Sales Data</h3>
        <table>
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Product Code</th>
                    <th>Product</th>
                    <th>Product Group</th>
                    <th>Total Qty</th>
                    <th>Total FOC</th>
                    <th>Total Sales</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesData as $row)
                    <tr>
                        <td>{{ $row->period }}</td>
                        <td>{{ $row->product_code }}</td>
                        <td>{{ $row->product ?? '-' }}</td>
                        <td>{{ $row->product_group }}</td>
                        <td>{{ number_format($row->total_qty, 3) }}</td>
                        <td>{{ number_format($row->total_foc, 3) }}</td>
                        <td>{{ number_format($row->total_sales, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No data found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
           const ctx = document.getElementById('salesChart');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: @json($chartDatasets)
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 14
                    }
                },
                title: {
                    display: true,
                    text: 'Sum of Sales by Product Group'
                }
            },
            scales: {
                x: {
                    stacked: false,
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>

</body>
</html>