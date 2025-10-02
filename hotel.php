<?php
require_once 'db.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id <= 0) { echo "Invalid hotel id"; exit; }
 
// Hotel
$hstmt = $mysqli->prepare("SELECT * FROM hotels WHERE id = ? LIMIT 1");
$hstmt->bind_param('i',$id);
$hstmt->execute();
$hotel = $hstmt->get_result()->fetch_assoc();
if(!$hotel){ echo "Hotel not found"; exit; }
 
// Rooms
$rstmt = $mysqli->prepare("SELECT * FROM rooms WHERE hotel_id = ?");
$rstmt->bind_param('i',$id);
$rstmt->execute();
$rooms = $rstmt->get_result();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/><title><?php echo htmlspecialchars($hotel['name']); ?> — Hilton Clone</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
    body{font-family:Inter,Arial;background:#f3f7ff;margin:0;color:#05102a}
    .hero{height:320px;background-image:url('<?php echo htmlspecialchars($hotel['cover_image']); ?>');background-size:cover;background-position:center;display:flex;align-items:flex-end;padding:24px;color:#fff}
    .hero .panel{background:linear-gradient(0deg,rgba(3,6,23,0.6),rgba(3,6,23,0.2));padding:18px;border-radius:12px}
    .container{max-width:1000px;margin:-40px auto 40px;padding:0 18px}
    .hotel-info{background:#fff;padding:18px;border-radius:12px;box-shadow:0 12px 30px rgba(7,12,30,0.06);display:flex;gap:16px;align-items:center}
    .hotel-info img{width:120px;height:90px;object-fit:cover;border-radius:8px}
    .muted{color:#6b7280}
    .rooms{margin-top:18px;display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px}
    .room{background:#fff;padding:14px;border-radius:12px;box-shadow:0 10px 30px rgba(10,20,50,0.05)}
    .room img{width:100%;height:160px;object-fit:cover;border-radius:8px}
    .price{font-weight:800;color:#0b5cff}
    /* Booking form modal */
    .modal{position:fixed;top:0;left:0;width:100%;height:100%;display:none;align-items:center;justify-content:center;background:rgba(3,6,23,0.48)}
    .modal .box{background:#fff;padding:18px;border-radius:12px;width:360px;max-width:90%}
    input,select{width:100%;padding:10px;border-radius:8px;border:1px solid #eef2ff;margin:8px 0}
    button{padding:10px;border-radius:8px;border:0;cursor:pointer}
    @media(max-width:700px){.hotel-info{flex-direction:column;align-items:flex-start}}
  </style>
</head>
<body>
  <div class="hero">
    <div class="panel">
      <div style="font-weight:800;font-size:20px"><?php echo htmlspecialchars($hotel['name']); ?></div>
      <div class="muted"><?php echo htmlspecialchars($hotel['address']); ?> • Rating: <?php echo htmlspecialchars($hotel['rating']); ?></div>
    </div>
  </div>
 
  <div class="container">
    <div class="hotel-info">
      <img src="<?php echo htmlspecialchars($hotel['cover_image']); ?>" alt="">
      <div style="flex:1">
        <div style="font-weight:700;font-size:18px"><?php echo htmlspecialchars($hotel['name']); ?></div>
        <div class="muted" style="margin-top:6px"><?php echo htmlspecialchars($hotel['description']); ?></div>
        <div style="margin-top:10px">
          <?php
            $amen = json_decode($hotel['amenities'], true);
            if(is_array($amen)){
                foreach(array_slice($amen,0,5) as $a){
                    echo '<span style="display:inline-block;margin-right:8px;padding:6px 8px;border-radius:8px;background:#f5f8ff;font-size:13px;color:#0b5cff">'.$a.'</span>';
                }
            }
          ?>
        </div>
      </div>
      <div style="text-align:right">
        <button style="background:#fff;border:1px solid #eef2ff;padding:8px;border-radius:8px" onclick="goBack()">Back</button>
      </div>
    </div>
 
    <h3 style="margin-top:18px">Available rooms</h3>
    <div class="rooms">
      <?php while($room = $rooms->fetch_assoc()): $images = json_decode($room['images'], true); ?>
        <div class="room">
          <img src="<?php echo htmlspecialchars($images[0] ?? 'https://picsum.photos/seed/room/800/500'); ?>" alt="">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
            <div>
              <div style="font-weight:700"><?php echo htmlspecialchars($room['title']); ?></div>
              <div class="muted">Capacity: <?php echo intval($room['capacity']); ?> guests</div>
            </div>
            <div style="text-align:right">
              <div class="price">$<?php echo number_format($room['price'],2); ?></div>
              <div class="muted">per night</div>
            </div>
          </div>
          <p class="muted" style="margin-top:10px"><?php echo htmlspecialchars(mb_strimwidth($room['description'],0,140,'...')); ?></p>
          <div style="display:flex;gap:8px;margin-top:12px">
            <button style="flex:1;background:#fff;border:1px solid #eef2ff" onclick="viewGallery(<?php echo $room['id']; ?>,<?php echo $room['hotel_id']; ?>)">Gallery</button>
            <button style="flex:1;background:#0b5cff;color:#fff" onclick="openBooking(<?php echo $room['id']; ?>,<?php echo $room['hotel_id']; ?>, '<?php echo addslashes($room['title']); ?>', <?php echo $room['price']; ?>)">Book</button>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
 
  <!-- Booking Modal -->
  <div class="modal" id="bookingModal">
    <div class="box">
      <div style="display:flex;justify-content:space-between;align-items:center">
        <div style="font-weight:800">Confirm booking</div>
        <div style="font-size:13px;color:#6b7280" id="roomLabel"></div>
      </div>
      <form id="bookingForm" method="post" action="process_booking.php">
        <input type="hidden" name="room_id" id="room_id" />
        <input type="hidden" name="hotel_id" id="hotel_id" />
        <label>Name</label>
        <input type="text" name="guest_name" required />
        <label>Email</label>
        <input type="text" name="guest_email" required />
        <label>Phone</label>
        <input type="text" name="guest_phone" />
        <label>Check-in</label>
        <input type="date" name="checkin" required />
        <label>Check-out</label>
        <input type="date" name="checkout" required />
        <label>Guests</label>
        <select name="guests">
          <option>1</option><option selected>2</option><option>3</option><option>4</option>
        </select>
        <div style="display:flex;gap:8px;margin-top:10px">
          <button type="button" style="flex:1;background:#eef6ff;color:#0b5cff" onclick="closeModal()">Cancel</button>
          <button type="submit" style="flex:1;background:#0b5cff;color:#fff">Confirm & Book</button>
        </div>
      </form>
    </div>
  </div>
 
  <script>
    function goBack(){ window.history.back(); }
    function viewGallery(roomId, hotelId){
      // simple behavior: open hotel page fragment or new tab; for demo, open the same page with room param
      window.location.href = 'hotel.php?id=' + <?php echo $hotel['id']; ?> + '&viewroom=' + roomId;
    }
    function openBooking(roomId, hotelId, title, price){
      document.getElementById('bookingModal').style.display = 'flex';
      document.getElementById('roomLabel').innerText = title + ' — $' + price;
      document.getElementById('room_id').value = roomId;
      document.getElementById('hotel_id').value = hotelId;
      // prefill today's date and tomorrow by default
      const today = new Date().toISOString().split('T')[0];
      const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
      document.querySelector('input[name="checkin"]').value = today;
      document.querySelector('input[name="checkout"]').value = tomorrow;
    }
    function closeModal(){ document.getElementById('bookingModal').style.display = 'none'; }
    // close on click outside
    document.getElementById('bookingModal').addEventListener('click', function(e){
      if(e.target === this) closeModal();
    });
  </script>
</body>
</html>
