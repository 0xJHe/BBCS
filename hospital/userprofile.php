<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../classes/Database.php';
require_once '../classes/Donor.php';

$service = new Donor($con);
$donor_id = isset($_GET['donor_id']) ? (int)$_GET['donor_id'] : 0;

// Fetch donor details
$donor = $service->getDonorDetails($donor_id);

if (!$donor) {
    die("Donor not found.");
}

// Fetch last donation date
$lastDonation = $service->getLastDonationDate($donor_id);

// Send Notification
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    if ($service->sendNotification($donor_id, $message)) {
        $success = true;
    }
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Donor Profile</title>

<style>
body{
  margin:0;font-family:system-ui;background:#0b1220;color:#eaf0ff;
}
.wrap{max-width:900px;margin:30px auto;padding:20px}

/* HEADER */
.top-header{
  display:flex;justify-content:space-between;align-items:center;
  padding:14px 22px;background:linear-gradient(180deg,#0f172a,#0b1220);
  border-bottom:1px solid rgba(255,255,255,.1)
}
.header-left{display:flex;gap:12px;align-items:center}
.header-icon{
  width:42px;height:42px;border-radius:12px;
  display:grid;place-items:center;
  background:linear-gradient(135deg,#60a5fa,#a78bfa);
  font-size: 20px;
}
.back-link{
  color:#a5b4fc;text-decoration:none;font-weight:800;
  padding:8px 14px;border-radius:10px;
  border:1px solid rgba(255,255,255,.15)
}

/* CARD */
.card{
  background:#0f1a33;border:1px solid rgba(255,255,255,.1);
  border-radius:18px;padding:20px;margin-bottom:25px
}
label{display:block;margin-bottom:6px;font-weight:700}
input,textarea{
  width:100%;padding:12px;border-radius:12px;
  border:1px solid rgba(255,255,255,.2);
  background:#0b1220;color:white;
  box-sizing: border-box;
}
.btn{
  margin-top:12px;padding:12px 18px;border-radius:12px;
  border:1px solid rgba(255,255,255,.2);
  background:linear-gradient(135deg,#60a5fa,#a78bfa);
  color:white;font-weight:900;cursor:pointer
}
.success{
  background:rgba(34,197,94,.15);
  border-left:5px solid #22c55e;
  padding:12px;border-radius:12px;margin-bottom:16px
}
</style>
</head>

<body>

<header class="top-header">
  <div class="header-left">
    <div class="header-icon">👤</div>
    <div>
      <div style="font-weight:900">Donor Profile</div>
      <div style="font-size:12px;color:#a5b4fc">Send Email & SMS Notification</div>
    </div>
  </div>
  <a href="analytics.php" class="back-link">← Back to Analytics</a>
</header>

<div class="wrap">

<?php if($success): ?>
  <div class="success">
    ✅ Notification sent successfully via Email, SMS, and In-App!
  </div>
<?php endif; ?>

<div class="card">
  <h2>🧑 Donor Information</h2>
  <p><b>Name:</b> <?= htmlspecialchars($donor['full_name']) ?></p>
  <p><b>Blood Type:</b> <span><?= $donor['blood_type'] ?></span></p>
  <p><b>Email:</b> <?= htmlspecialchars($donor['email']) ?></p>
  <p><b>Phone:</b> <?= htmlspecialchars($donor['phone']) ?></p>
  <p><b>Last Donation:</b> <?= $lastDonation ? $lastDonation : 'No completed donations yet' ?></p>
</div>

<div class="card">
  <h2>🔔 Send Notification</h2>

  <form method="post">
    <label>Message to Donor</label>
    <textarea 
      name="message" 
      rows="4" 
      required
      placeholder="Example: Your blood type is urgently needed. Please consider donating soon."
    ></textarea>

    <button class="btn">Send via Email & SMS</button>
  </form>
</div>

</div>
</body>
</html>