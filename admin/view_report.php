<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../classes/AdminReport.php';

$report = new AdminReport();
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Fetch Basic Stats
$total_donors = $report->getUsersByRole('donor');
$total_hospitals = $report->getUsersByRole('hospital');
$total_organizers = $report->getUsersByRole('organizer');

$completed_appointments = $report->getAppointmentStats($selected_year, 'Completed');
$total_events = $report->getEventsCount($selected_year);
$monthly_data = $report->getMonthlyAppointments($selected_year);

// Fetch Operational Insights
$top_locations = $report->getTopLocations($selected_year);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annual Report</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card, .chart-container, .controls-card, .list-card {
            background: #23253a;
            border: 1px solid #3e4059;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            border-radius: 8px;
            padding: 20px;
        }

        .content-row {
            display: flex;
            gap: 25px; 
            align-items: stretch; 
        }


        .chart-container {
            flex: 0 0 70%; 
            max-width: 70%;
            position: relative;
            min-height: 400px; 
        }

        .list-card {
            flex: 0 0 25%; 
            max-width: 25%;
        }

        .controls-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            text-align: center;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            color: #aab0c7;
            font-size: 0.9rem;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card .number {
            color: #ff6b6b;
            font-size: 2.5rem;
            font-weight: bold;
        }

        .stat-card small {
            color: #7f8fa6;
            display: block;
            margin-top: 5px;
        }

        select {
            background: #353755;
            color: white;
            border: 1px solid #4a4c68;
            padding: 8px 15px;
            border-radius: 4px;
            font-size: 1rem;
            outline: none;
        }

        .list-card h3 { color: #fff; margin-bottom: 15px; font-size: 1.1rem; border-bottom: 1px solid #3e4059; padding-bottom: 10px; }
        .list-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #353755; color: #aab0c7; }
        .list-item:last-child { border-bottom: none; }
        .list-item .count { font-weight: bold; color: #ff6b6b; white-space: nowrap; margin-left: 10px; }
        .list-item .rank { width: 25px; color: #7f8fa6; font-style: italic; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>BloodBank System</h3>
                <div style="font-size:12px">Main Dashboard</div>
            </div>
            <nav>
                <ul>
                    <li><a href="manage_users.php">Manage Users</a></li>
                    <li><a href="pending_reg.php">Registrations</a></li>
                    <li><a href="view_report.php"  style="color: white; background: rgba(255,255,255,0.05);">Annual Report</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

    <div class="main-content">
        <header class="top-header">
            <h1>Annual System Report</h1>
        </header>

        <div class="content-body">
            <div class="controls-card">
                <form method="GET" style="display: flex; align-items: center; gap: 15px;">
                    <label for="year"><strong>Select Year:</strong></label>
                    <select name="year" id="year" onchange="this.form.submit()">
                        <?php
                        $cur = date('Y');
                        for ($i = $cur; $i >= $cur - 5; $i--) {
                            $sel = ($i == $selected_year) ? 'selected' : '';
                            echo "<option value='$i' $sel>$i</option>";
                        }
                        ?>
                    </select>
                </form>
                <a href="generate_pdf_report.php?year=<?php echo $selected_year; ?>" class="btn-pdf" target="_blank">Download PDF Report</a>
            </div>

            <!-- KPI Cards (5 Total) -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Donors</h3>
                    <div class="number"><?php echo $total_donors; ?></div>
                    <small>Registered (All Time)</small>
                </div>
                <div class="stat-card">
                    <h3>Total Hospitals</h3>
                    <div class="number"><?php echo $total_hospitals; ?></div>
                    <small>Registered Partners</small>
                </div>
                <div class="stat-card">
                    <h3>Total Organizers</h3>
                    <div class="number"><?php echo $total_organizers; ?></div>
                    <small>Registered Partners</small>
                </div>
                <div class="stat-card">
                    <h3>Events Conducted</h3>
                    <div class="number"><?php echo $total_events; ?></div>
                    <small>In <?php echo $selected_year; ?></small>
                </div>
                <div class="stat-card">
                    <h3>Donations</h3>
                    <div class="number"><?php echo $completed_appointments; ?></div>
                    <small>Successfully Completed</small>
                </div>
            </div>

            <div class="content-row">
                <!-- Monthly Activity Chart -->
                <div class="chart-container" style="position: relative;">
                    <canvas id="monthlyChart"></canvas>
                </div>

                <!-- Top Locations List -->
                <div class="list-card">
                    <h3>Top Event Venues (<?php echo $selected_year; ?>)</h3>
                    <?php if (count($top_locations) > 0): ?>
                        <?php $rank = 1; foreach ($top_locations as $loc): ?>
                            <div class="list-item">
                                <div>
                                    <span class="rank">#<?php echo $rank++; ?></span>
                                    <span><?php echo htmlspecialchars($loc['location']); ?></span>
                                </div>
                                <span class="count"><?php echo $loc['count']; ?> Events</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #7f8fa6; font-style: italic; padding: 10px;">
                            No venue data available for this year.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    Chart.defaults.color = '#aab0c7';
    Chart.defaults.borderColor = '#3e4059';
    Chart.defaults.font.family = "'Poppins', sans-serif";

    // Monthly Bar Chart
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Donations',
                data: <?php echo json_encode(array_values($monthly_data)); ?>,
                backgroundColor: 'rgba(255, 107, 107, 0.7)',
                hoverBackgroundColor: 'rgba(255, 107, 107, 1)',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: 'Monthly Donation Activity', color: '#fff' },
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, suggestedMax: 10, grid: { borderDash: [5,5] }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });
</script>

</body>
</html>