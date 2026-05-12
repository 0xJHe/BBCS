<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../classes/Database.php';
require_once '../classes/HospitalAnalytics.php';

$analytics = new HospitalAnalytics($con);

// Fetch available years and selected year
$availableYears = $analytics->getAvailableYears();
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)$availableYears[0];

// Yearly stats
$totalYearlyUnits = $analytics->getYearlyUnits($selectedYear);


$totalDonors = $analytics->getTotalDonors();
$totalUnitsDonated = $analytics->getTotalUnitsDonated();

// Current month stats
$report_month = date('J Y');
$monthStats = $analytics->getCurrentMonthStats();
$total_units_collected = $monthStats['total_units_collected'] ?? 0;
$critical_events = $monthStats['critical_events'] ?? 0;

// Recent donations and inventory history
$recentDonations = $analytics->getRecentDonations();
$history = $analytics->getInventoryHistory();

// Top donors by blood type
$topDonors = $analytics->getTopDonors();

?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Reporting & Analytics</title>

<header class="top-header">
  <div class="header-left">
    <div class="header-icon">📊</div>
    <div class="header-title">
      <div class="title-main">Reporting & Analytics</div>
      <div class="title-sub">Blood Bank System</div>
    </div>
  </div>

  <div class="header-right">
    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
  </div>
</header>

<style>

    /* ===== TOP HEADER ===== */
.top-header{
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:14px 22px;
  background:linear-gradient(180deg, #0f172a, #0b1220);
  border-bottom:1px solid rgba(255,255,255,.1);
  box-shadow:0 10px 30px rgba(0,0,0,.4);
}

.header-left{
  display:flex;
  align-items:center;
  gap:12px;
}

.header-icon{
  width:42px;
  height:42px;
  border-radius:12px;
  display:grid;
  place-items:center;
  font-size:20px;
  background:linear-gradient(135deg, #60a5fa, #a78bfa);
  box-shadow:0 0 20px rgba(96,165,250,.4);
}

.header-title .title-main{
  font-size:18px;
  font-weight:900;
  letter-spacing:.3px;
}

.header-title .title-sub{
  font-size:12px;
  color:#a5b4fc;
  opacity:.8;
}

.header-right .back-link{
  color:#a5b4fc;
  text-decoration:none;
  font-weight:800;
  font-size:13px;
  padding:8px 14px;
  border-radius:10px;
  border:1px solid rgba(255,255,255,.15);
  background:rgba(255,255,255,.04);
  transition:.2s ease;
}

.header-right .back-link:hover{
  background:rgba(255,255,255,.12);
  transform:translateX(-3px);
}
body{
  margin:0;
  font-family:system-ui;
  background:#0b1220;
  color:#eaf0ff;
}

.panel {
  style="display:flex;
  justify-content:space-between;
  align-items:center;"
}

.wrap{
  max-width:1200px;
  margin:20px auto;
  padding:20px;
}

h1,h2{
  margin:0 0 12px;
}

/* ===== STAT CARDS ===== */
.stats-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
  gap:20px;
  margin-bottom:30px;   /* SPACE BELOW STATS */
}

.stat-card{
  background:#0f1a33;
  border:1px solid rgba(255,255,255,.1);
  border-radius:18px;
  padding:20px;
  text-align:center;
}

.stat-card .label{
  font-size:12px;
  color:#a5b4fc;
  margin-bottom:6px;
}

.stat-card .value{
  font-size:28px;
  font-weight:900;
}

/* ===== PANELS ===== */
.panel{
  background:#0f1a33;
  border:1px solid rgba(255,255,255,.1);
  border-radius:18px;
  padding:20px;
  margin-bottom:25px;
}

/* EXTRA SPACE BETWEEN MONTHLY + STATS */
.monthly-panel{
  margin-top:15px;
  margin-bottom:35px;   /* SPACE BELOW MONTHLY */
}

/* ===== TABLE ===== */
table{
  width:100%;
  border-collapse:collapse;
  table-layout:fixed;
}

th,td{
  padding:12px;
  border-bottom:1px solid rgba(255,255,255,.1);
  vertical-align:middle;
}

th{
  text-transform:uppercase;
  font-size:11px;
  color:#a5b4fc;
  text-align:center;
}

td:nth-child(1){text-align:left;}
td:nth-child(2),
td:nth-child(3),
td:nth-child(4),
td:nth-child(5){text-align:center;}

/* ===== STATUS BADGES ===== */
.badge{
  padding:4px 10px;
  border-radius:999px;
  font-size:12px;
  font-weight:700;
}

.ok{background:#14532d;color:#bbf7d0}
.low{background:#713f12;color:#fde68a}
.crit{background:#7f1d1d;color:#fecaca}

/* ===== BUTTON ===== */
.btn{
  padding:6px 12px;
  border-radius:10px;
  border:1px solid rgba(255,255,255,.2);
  background:#1e293b;
  color:#a5b4fc;
  text-decoration:none;
  font-size:12px;
  font-weight:700;
  display:inline-block;
}

.btn:hover{
  background:#334155;
}
</style>
</head>

<body>
<div class="wrap">

<h1>📊 Reporting & Analytics</h1>

<div class="panel">
    <form method="GET" class="filter-form">
        <label><b>Select Year:</b></label>
        <select name="year" class="filter-select" onchange="this.form.submit()">
            <!-- Show the option for every available years -->
            <?php foreach($availableYears as $y): ?>
                <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="panel">
  <div style="display:flex; justify-content:space-between; align-items:center;">
      <div>
        <h2>Yearly Performance: <?= $selectedYear ?></h2>
        <p>Total Units Collected in <?= $selectedYear ?>: <b><?= $totalYearlyUnits ?> Units</b></p>
      </div>
      <div>
        <a href="generate_report.php?year=<?= $selectedYear ?>" target="_blank" class="btn">
            Export <?= $selectedYear ?> Report
        </a>
      </div>
  </div>
</div>


<!-- ===== STATS ===== -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="label">Total Donors</div>
    <div class="value"><?= $totalDonors ?></div>
  </div>

  <div class="stat-card">
    <div class="label">Total Units Donated</div>
    <div class="value"><?= $totalUnitsDonated ?></div>
  </div>

  <div class="stat-card">
    <div class="label">Current Month Collected</div>
    <div class="value"><?= $total_units_collected ?? 'N/A' ?></div>
  </div>

  <div class="stat-card">
    <div class="label">Critical Events</div>
    <div class="value"><?= $critical_events ?? 0 ?></div>
  </div>
</div>

<!-- ===== INVENTORY HISTORY ===== -->
<div class="panel">
  <h2>📈 Current Inventory Status</h2>

  <table>
    <thead>
      <tr>
        <th>Blood Type</th>
        <th>Units</th>
        <th>Status</th>
        <th>Updated</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($history as $h):
      $s = strtolower($h['status']);
      $cls = $s==='normal'?'ok':($s==='low'?'low':'crit');
    ?>
      <tr>
        <td><?= $h['blood_type'] ?></td>
        <td><?= $h['units'] ?></td>
        <td><span class="badge <?= $cls ?>"><?= $h['status'] ?></span></td>
        <td><?= $h['recorded_at'] ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ===== MOST DONATED ===== -->
<div class="panel">
  <h2>Top Donors by Blood Type (Most Donated)</h2>
  <table>
    <thead>
      <tr>
        <th>Blood Type</th>
        <th>Top Donor</th>
        <th>Total Donations</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <!-- If top donors not empty -->
    <?php if(!empty($topDonors)): ?>
        <!-- Loop For every each top donor -->
        <?php foreach($topDonors as $data): ?>
          <tr>
            <td><?= htmlspecialchars($data['blood_type']) ?></td>
            <td>
                <?= htmlspecialchars($data['full_name']) ?>
            </td>
            <td><b><?= $data['total_donations'] ?></b> Units</td>
            <td>
              <a class="btn" href="userprofile.php?donor_id=<?= $data['donor_id'] ?>">Request Blood</a>
            </td>
          </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="4" style="color:#a5b4fc;">No completed donations recorded yet.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- ===== RECENT DONATIONS ===== -->
<div class="panel">
  <h2>🧑‍🤝‍🧑 Recent Donations</h2>

  <table>
    <thead>
      <tr>
        <th>Donor</th>
        <th>Blood Type</th>
        <th>Units</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($recentDonations as $d): ?>
      <tr>
        <td>
  <a 
    href="userprofile.php?donor_id=<?= $d['donor_id'] ?>" 
    style="color:#60a5fa;font-weight:800;text-decoration:none"
  >
    <?= htmlspecialchars($d['full_name']) ?>
  </a>
</td>
        <td><?= $d['blood_type'] ?></td>
        <td><?= $d['units_donated'] ?></td>
        <td><?= $d['donation_date'] ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>



</div>
</body>
</html>