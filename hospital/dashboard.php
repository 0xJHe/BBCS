<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Blood Bank Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
:root{
  --bg1:#0b1220;
  --bg2:#0d1730;
  --border:rgba(255,255,255,.10);
  --text:#eaf0ff;
  --muted:rgba(234,240,255,.65);
  --accent1:#60a5fa;
  --accent2:#a78bfa;
}
*{box-sizing:border-box}
body{
  margin:0;
  font-family:system-ui,Segoe UI,Arial;
  color:var(--text);
  background:
    radial-gradient(900px 500px at 15% 10%, rgba(96,165,250,.18), transparent 60%),
    radial-gradient(900px 500px at 85% 10%, rgba(167,139,250,.14), transparent 60%),
    linear-gradient(180deg, var(--bg1), var(--bg2));
}

/* TOP BAR */
.topbar{
  padding:14px 20px;
  background:rgba(10,16,30,.75);
  border-bottom:1px solid var(--border);
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.brand{display:flex;gap:10px;align-items:center;font-weight:900}
.logo{
  width:40px;height:40px;border-radius:12px;
  display:grid;place-items:center;
  background:linear-gradient(135deg, var(--accent1), var(--accent2));
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

/* MAIN */
.wrap{
  max-width:1100px;
  margin:40px auto;
  padding:0 20px;
  text-align:center;
}

.hero{
  margin-bottom:40px;
}
.hero h1{
  font-size:34px;
  margin:0 0 10px;
}
.hero p{
  color:var(--muted);
  margin:0;
}

/* DASHBOARD CARDS */
.grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
  gap:22px;
  margin-top:40px;
}

.card{
  background:rgba(255,255,255,.04);
  border:1px solid var(--border);
  border-radius:24px;
  padding:28px;
  text-align:left;
  transition:.25s ease;
}
.card:hover{
  transform:translateY(-6px);
  border-color:rgba(96,165,250,.4);
  background:rgba(255,255,255,.06);
}

.card h2{
  margin:0 0 8px;
  font-size:22px;
}
.card p{
  margin:0 0 18px;
  color:var(--muted);
  font-size:14px;
}

/* BUTTONS */
.btn{
  display:inline-block;
  padding:12px 18px;
  border-radius:14px;
  text-decoration:none;
  font-weight:900;
  font-size:14px;
  border:1px solid var(--border);
  background:linear-gradient(135deg, rgba(96,165,250,.25), rgba(167,139,250,.20));
  color:white;
  transition:.2s;
}
.btn:hover{
  transform:scale(1.03);
  background:linear-gradient(135deg, rgba(96,165,250,.4), rgba(167,139,250,.35));
}

/* FOOTER */
.footer{
  margin-top:60px;
  color:var(--muted);
  font-size:12px;
}
</style>
</head>

<body>

<header class="topbar">
  <div class="brand">
    <div class="logo">🩸</div>
    <div>
      <div>BloodBank System</div>
      <div style="font-size:12px;color:var(--muted)">Main Dashboard</div>
    </div>
  </div>
  <div class="pill">Hospital: <b>General</b></div>
  <div class="header-right">
    <a href="../logout.php" class="back-link">Logout</a>
  </div>
</header>

<div class="wrap">

  <div class="hero">
    <h1>Blood Bank Management Dashboard</h1>
    <p>Central control panel for inventory, analytics, notifications, and reporting.</p>
  </div>

  <div class="grid">

    <!-- INVENTORY -->
    <div class="card">
      <h2>🩸 Inventory Management</h2>
      <p>
        View live blood stock levels, circular indicators, critical alerts,
        and real-time inventory status.
      </p>
      <a href="inventory.php" class="btn">Go to Inventory</a>
    </div>

    <!-- ANALYTICS -->
    <div class="card">
      <h2>📊 Reporting & Analytics</h2>
      <p>
        Analyze donation trends, monthly reports, blood usage,
        and inventory history for decision making.
      </p>
      <a href="analytics.php" class="btn">Go to Analytics</a>
    </div>

    <!-- NOTIFICATIONS (NEW) -->
    <div class="card">
      <h2>🔔 System Notifications</h2>
      <p>
        View critical shortage alerts, low inventory warnings,
        new donation updates, and system reminders.
      </p>
      <a href="notifications.php" class="btn">View Notifications</a>
    </div>

  </div>

  <div class="footer">
    Blood Bank System • Dashboard • <?= date('Y') ?>
  </div>

</div>

</body>
</html>