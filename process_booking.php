<?php
require_once 'db.php';
 
// Only accept POST
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo "Invalid access method.";
    exit;
}
 
// sanitize inputs
$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
$hotel_id = isset($_POST['hotel_id']) ? intval($_POST['hotel_id']) : 0;
$guest_name = isset($_POST['guest_name']) ? trim($_POST['guest_name']) : '';
$guest_email = isset($_POST['guest_email']) ? trim($_POST['guest_email']) : '';
$guest_phone = isset($_POST['guest_phone']) ? trim($_POST['guest_phone']) : '';
$checkin = isset($_POST['checkin']) ? $_POST['checkin'] : '';
$checkout = isset($_POST['checkout']) ? $_POST['checkout'] : '';
$guests = isset($_POST['guests']) ? intval($_POST['guests']) : 1;
 
// Basic validation
$errors = [];
if($room_id <= 0) $errors[] = "Invalid room selected.";
if(!filter_var($guest_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";
if(!$checkin || !$checkout) $errors[] = "Check-in and check-out dates are required.";
if(strtotime($checkin) === false || strtotime($checkout) === false) $errors[] = "Invalid dates.";
if(strtotime($checkin) >= strtotime($checkout)) $errors[] = "Check-out must be after check-in.";
 
if(!empty($errors)){
    ?>
    <!doctype html><html><head><meta charset="utf-8"><title>Booking error</title>
    <style>body{font-family:Arial;padding:20px;background:#f7fbff} .box{background:#fff;padding:18px;border-radius:10px;box-shadow:0 8px 30px rgba(10,20,50,0.06)}</style>
    </head><body><div class="box"><h3>Booking error</h3><ul><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul><p><button onclick="window.history.back()">Go back</button></p></div></body></html>
    <?php
    exit;
}
 
// Insert booking
$ins = $mysqli->prepare("INSERT INTO bookings (room_id, hotel_id, guest_name, guest_email, guest_phone, checkin, checkout, guests) VALUES (?,?,?,?,?,?,?,?)");
$ins->bind_param('iisssssi',$room_id,$hotel_id,$guest_name,$guest_email,$guest_phone,$checkin,$checkout,$guests);
$ok = $ins->execute();
if(!$ok){
    echo "Database error: " . htmlspecialchars($mysqli->error);
    exit;
}
$booking_id = $mysqli->insert_id;
 
// For demo: simple confirmation page
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/><title>Booking Confirmed</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
    body{font-family:Inter,Arial;background:linear-gradient(180deg,#f6f9ff,#ffffff);margin:0;color:#05102a}
    .wrap{max-width:720px;margin:60px auto;padding:18px}
    .card{background:#fff;padding:22px;border-radius:12px;box-shadow:0 14px 40px rgba(10,20,50,0.06)}
    .muted{color:#6b7280}
    .btn{display:inline-block;padding:10px 14px;border-radius:8px;border:0;cursor:pointer}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h2 style="margin:0 0 6px">Booking confirmed!</h2>
      <div class="muted">Your booking reference: <strong>#<?php echo $booking_id; ?></strong></div>
      <hr style="margin:14px 0"/>
      <p><strong>Guest:</strong> <?php echo htmlspecialchars($guest_name); ?> — <?php echo htmlspecialchars($guest_email); ?></p>
      <p><strong>Dates:</strong> <?php echo htmlspecialchars($checkin); ?> to <?php echo htmlspecialchars($checkout); ?></p>
      <p><strong>Guests:</strong> <?php echo intval($guests); ?></p>
      <p style="margin-top:12px" class="muted">We stored your reservation in the system. In a real production app you'd receive an email confirmation as well.</p>
 
      <div style="display:flex;gap:10px;margin-top:18px">
        <button class="btn" style="background:#eef6ff;color:#0b5cff" onclick="window.location.href='hotel.php?id=<?php echo $hotel_id; ?>'">Back to hotel</button>
        <button class="btn" style="background:#0b5cff;color:#fff" onclick="window.location.href='index.php'">Search more</button>
      </div>
    </div>
  </div>
</body>
</html>
