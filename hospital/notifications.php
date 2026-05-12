<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../classes/Database.php';
require_once '../classes/Notification.php';


$hid = $_SESSION['user_id'];
$notificationService = new Notification($con);

// Generate alerts based on current inventory
$notificationService->generateInventoryAlerts($hid);

// Fetch recent notifications
$notifications = $notificationService->getRecentNotifications(20);

?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>System Notifications</title>

<style>
body{
  margin:0;
  font-family:system-ui;
  background:#0b1220;
  color:#eaf0ff;
}

/* ===== TOP HEADER (MATCHES YOUR SYSTEM) ===== */
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
  width:42px;height:42px;border-radius:12px;
  display:grid;place-items:center;
  background:linear-gradient(135deg,#60a5fa,#a78bfa);
  font-size:20px;
}

.header-title .title-main{font-size:18px;font-weight:900}
.header-title .title-sub{font-size:12px;color:#a5b4fc}

.back-link{
  color:#a5b4fc;
  text-decoration:none;
  font-weight:800;
  padding:8px 14px;
  border-radius:10px;
  border:1px solid rgba(255,255,255,.15);
}

/* ===== LAYOUT ===== */
.wrap{
  max-width:1100px;
  margin:30px auto;
  padding:0 20px;
}

/* ===== NOTIFICATION CARDS ===== */
.notify{
  background:#0f1a33;
  border:1px solid rgba(255,255,255,.1);
  border-radius:16px;
  padding:16px 18px;
  margin-bottom:14px;
  display:flex;
  justify-content:space-between;
  gap:16px;
  align-items:center;
}

.notify.critical{border-left:5px solid #ef4444;background:rgba(239,68,68,.08)}
.notify.low{border-left:5px solid #facc15;background:rgba(250,204,21,.08)}
.notify.info{border-left:5px solid #60a5fa;background:rgba(96,165,250,.08)}

.notify .msg{font-weight:700}
.notify .meta{font-size:12px;color:#a5b4fc}
</style>
</head>

<body>

<header class="top-header">
  <div class="header-left">
    <div class="header-icon">🔔</div>
    <div class="header-title">
      <div class="title-main">System Notifications</div>
      <div class="title-sub">Alerts, updates & reminders</div>
    </div>
  </div>

  <div>
    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
  </div>
</header>

<div class="wrap">

<h2>📢 Recent Notifications</h2>

<?php if(!$notifications): ?>
  <div class="notify info">
    <div class="msg">No notifications available for selected criteria.</div>
  </div>
<?php endif; ?>

<?php foreach($notifications as $n): 
  $cls = 'info';
  if(stripos($n['message'],'CRITICAL') !== false) $cls='critical';
  elseif(stripos($n['message'],'Low') !== false) $cls='low';
?>
  <div class="notify <?= $cls ?>">
    <div>
      <div class="msg"><?= htmlspecialchars($n['message']) ?></div>
      <div class="meta">
        Channel: <?= $n['channel'] ?> • Status: <?= $n['status'] ?>
      </div>
    </div>
    <div class="meta">
      <?= date('M d, Y H:i', strtotime($n['sent_date'])) ?>
    </div>
  </div>
<?php endforeach; ?>

</div>
</body>
</html>