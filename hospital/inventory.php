<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../classes/Database.php';
require_once '../classes/Inventory.php';

$inventoryService = new Inventory($con);

// Get Hospital ID from Session
$hospital_id = $_SESSION['user_id']; 

// Get inventory rows and summary for hospital
$rows = $inventoryService->getInventoryRows($hospital_id);
$summary = $inventoryService->getSummary($rows);

$totalTypes = $summary['totalTypes'];
$critical = $summary['critical'];
$low = $summary['low'];
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Blood Bank Inventory</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>

.top-header{
  position:sticky;
  top:0;
  z-index:20;
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
html{scroll-behavior:smooth}

:root{
  --bg1:#0b1220;--bg2:#0d1730;
  --border:rgba(255,255,255,.1);
  --text:#eaf0ff;--muted:rgba(234,240,255,.65);
}
*{box-sizing:border-box}
body{
  margin:0;font-family:system-ui;
  color:var(--text);
  background:
    radial-gradient(900px 500px at 15% 10%, rgba(96,165,250,.18), transparent 60%),
    radial-gradient(900px 500px at 85% 10%, rgba(167,139,250,.14), transparent 60%),
    linear-gradient(180deg,var(--bg1),var(--bg2));
}

/* TOPBAR */
.topbar{position:sticky;top:0;z-index:10;
  background:rgba(10,16,30,.7);
  border-bottom:1px solid var(--border);
  padding:12px 18px;
  display:flex;justify-content:space-between;align-items:center}
.brand{display:flex;gap:10px;align-items:center;font-weight:900}
.logo{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;background:#1e293b}
.pill{font-size:12px;color:var(--muted)}

/* LAYOUT */
.wrap{max-width:1280px;margin:18px auto;padding:0 16px;
  display:grid;grid-template-columns:260px 1fr;gap:18px}
.sidebar{padding:16px;border-radius:20px;
  background:rgba(15,26,51,.6);border:1px solid var(--border)}
.nav a{display:block;padding:10px 12px;margin-bottom:6px;
  border-radius:14px;color:var(--muted);text-decoration:none}
.nav a.active,.nav a:hover{background:rgba(96,165,250,.15);color:white}

/* STATUS RULES */
.rules{margin-top:16px;padding:14px;border-radius:16px;
  background:rgba(255,255,255,.04);border:1px solid var(--border)}
.rules h3{margin:0 0 10px;font-size:14px}
.rule{margin-bottom:8px}
.rule span{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:900}
.r-ok{background:#14532d;color:#bbf7d0}
.r-low{background:#713f12;color:#fde68a}
.r-crit{background:#7f1d1d;color:#fecaca}

/* HERO */
.hero{padding:18px;border-radius:22px;
  background:linear-gradient(135deg, rgba(96,165,250,.15), rgba(167,139,250,.10));
  display:flex;justify-content:space-between;gap:14px}
.stats{display:flex;gap:10px}
.stat{background:rgba(255,255,255,.05);
  border:1px solid var(--border);
  border-radius:16px;padding:12px;min-width:120px}

/* PANELS */
.panel,.stock-panel{margin-top:16px;padding:18px;border-radius:22px;
  background:rgba(15,26,51,.6);border:1px solid var(--border)}

/* RINGS */
.ring-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.ring-card{background:rgba(255,255,255,.04);
  border:1px solid var(--border);border-radius:20px;padding:14px;text-align:center}
.chip.ok{background:#14532d;color:#bbf7d0;padding:4px 10px;border-radius:999px}
.chip.low{background:#713f12;color:#fde68a;padding:4px 10px;border-radius:999px}
.chip.crit{background:#7f1d1d;color:#fecaca;padding:4px 10px;border-radius:999px}
.ring{width:120px;height:120px;border-radius:50%;margin:10px auto;
  display:grid;place-items:center;
  background:conic-gradient(var(--c) calc(var(--p)*1%), #1f2933 0)}
.ringInner{width:86px;height:86px;border-radius:50%;
  background:white;color:#0b1220;
  display:grid;place-items:center;font-weight:900}

/* STOCK TABLE */
.stock-header{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:12px
}

.stock-tablewrap{
  border-radius:16px;
  overflow:hidden;
  border:1px solid rgba(255,255,255,.12)
}

.stock-table{
  width:100%;
  border-collapse:collapse;
  text-align:center;   /* ✅ center all table text */
}

.stock-table th{
  font-size:11px;
  text-transform:uppercase;
  color:var(--muted);
  padding:14px;
  border-bottom:1px solid rgba(255,255,255,.12);
  text-align:center;   /* ✅ center headers */
}

.stock-table td{
  padding:14px;
  border-bottom:1px solid rgba(255,255,255,.08);
  text-align:center;   /* ✅ center cells */
}

/* OPTIONAL: if you want Blood Type left aligned only */
.stock-table td:first-child,
.stock-table th:first-child{
  text-align:left;
}
.status-pill{padding:6px 12px;border-radius:999px;font-size:12px;font-weight:900}
.status-normal{background:rgba(34,197,94,.2);color:#bbf7d0}
.status-low{background:rgba(250,204,21,.2);color:#fef08a}
.status-critical{background:rgba(239,68,68,.2);color:#fecaca}
</style>
</head>

<body>

<header class="top-header">
  <div class="header-left">
    <div class="header-icon">🩸</div>
    <div class="header-title">
      <div class="title-main">Inventory Management</div>
      <div class="title-sub">Live Blood Stock Overview</div>
    </div>
  </div>

  <div class="header-right">
    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
  </div>
</header>

<div class="wrap">

<aside class="sidebar">
  <div class="nav">
    <a class="active" href="#inventory">🩸 Inventory</a>
    <a href="#table">📋 Stock Table</a>
  </div>

  <!-- STATUS RULES (ADDED) -->
  <div class="rules">
    <h3>Status Rules</h3>
    <div class="rule"><span class="r-ok">Normal</span> ≥ 21 units</div>
    <div class="rule"><span class="r-low">Low</span> 11–20 units</div>
    <div class="rule"><span class="r-crit">Critical</span> ≤ 10 units</div>
  </div>
</aside>

<main>

<section class="hero">
  <h1>Live Blood Stock Overview</h1>
  <div class="stats">
    <div class="stat">Total Types<br><b><?= $totalTypes ?></b></div>
    <div class="stat">Critical<br><b><?= $critical ?></b></div>
    <div class="stat">Low<br><b><?= $low ?></b></div>
  </div>
</section>

<section class="panel" id="inventory">
  <h2>Inventory (Circle View)</h2>
  <div class="ring-grid">
<?php foreach ($rows as $r):
  $s=strtolower($r['status']);
  if($s==='normal'){ $cls='ok';$c='#22c55e';}
  elseif($s==='low'){ $cls='low';$c='#facc15';}
  else{ $cls='crit';$c='#ef4444';}
?>
  <div class="ring-card">
    <h3><?= htmlspecialchars($r['blood_type']) ?></h3>
    <div class="chip <?= $cls ?>"><?= htmlspecialchars($r['status']) ?></div>
    <div class="ring" style="--p:<?= $r['percent_full'] ?>;--c:<?= $c ?>">
      <div class="ringInner"><?= $r['percent_full'] ?>%</div>
    </div>
    <div><b><?= $r['units'] ?></b> units</div>
  </div>
<?php endforeach; ?>
  </div>
</section>

<section class="stock-panel" id="table">
  <div class="stock-header">
    <h2>Stock Table</h2>
  </div>

  <div class="stock-tablewrap">
    <table class="stock-table">
      <thead>
        <tr>
          <th>Blood Type</th><th>Units</th><th>Status</th><th>Capacity</th><th>Updated</th>
        </tr>
      </thead>
      <tbody>
<?php foreach ($rows as $r):
  $s=strtolower($r['status']);
  if($s==='normal')$p='status-normal';
  elseif($s==='low')$p='status-low';
  else $p='status-critical';
?>
        <tr>
          <td><?= htmlspecialchars($r['blood_type']) ?></td>
          <td><?= $r['units'] ?></td>
          <td><span class="status-pill <?= $p ?>"><?= htmlspecialchars($r['status']) ?></span></td>
          <td><?= $r['capacity'] ?></td>
          <td>Today</td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

</main>
</div>

</body>
</html>