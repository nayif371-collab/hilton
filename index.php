<?php
require_once 'db.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Hilton Clone — Home</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
    /* Internal CSS: polished, modern hotel-like */
    :root{--accent:#0b5cff;--muted:#6b7280;--card-bg:#ffffff;--shadow:0 8px 30px rgba(11,20,60,0.08);}
    *{box-sizing:border-box;font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;}
    body{margin:0;background:linear-gradient(180deg,#f6f9ff,#ffffff);color:#06102b;line-height:1.4}
    header{padding:28px 36px;display:flex;align-items:center;justify-content:space-between}
    .brand{display:flex;gap:12px;align-items:center}
    .logo{width:54px;height:54px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#3ea0ff);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;box-shadow:var(--shadow)}
    h1{font-size:20px;margin:0}
    .search-card{max-width:980px;margin:20px auto;padding:22px;background:var(--card-bg);border-radius:12px;box-shadow:var(--shadow);display:flex;gap:12px;align-items:center}
    .search-field{flex:1;display:flex;gap:8px}
    input[type=text], input[type=date], select{padding:12px;border-radius:8px;border:1px solid #e6e9ef;background:#fafbff;min-width:130px}
    button.search-btn{background:var(--accent);color:#fff;padding:12px 18px;border-radius:10px;border:0;font-weight:600;cursor:pointer}
    .featured{max-width:1100px;margin:28px auto;padding:0 18px}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px}
    .card{background:var(--card-bg);border-radius:12px;overflow:hidden;box-shadow:var(--shadow);display:flex;flex-direction:column}
    .card img{width:100%;height:160px;object-fit:cover}
    .card-body{padding:14px}
    .muted{color:var(--muted);font-size:13px}
    .rating{float:right;background:#f0f7ff;padding:6px 10px;border-radius:20px;font-weight:600;color:var(--accent)}
    footer{padding:36px;text-align:center;color:var(--muted);font-size:14px}
    @media(max-width:680px){header{flex-direction:column;gap:12px}.search-card{flex-direction:column;align-items:stretch}}
  </style>
</head>
<body>
  <header>
    <div class="brand">
      <div class="logo">H</div>
      <div>
        <h1>Hilton Clone</h1>
        <div class="muted">Search and book hotels fast</div>
      </div>
    </div>
    <nav class="muted">Modern UI • Responsive • PHP + MySQL</nav>
  </header>
 
  <section class="search-card" role="search">
    <div style="flex:1">
      <div style="font-weight:700;margin-bottom:6px">Find your stay</div>
      <div class="search-field">
        <input id="destination" type="text" placeholder="City or hotel name (e.g., Karachi)" />
        <input id="checkin" type="date" />
        <input id="checkout" type="date" />
        <select id="guests">
          <option value="1">1 guest</option>
          <option value="2" selected>2 guests</option>
          <option value="3">3 guests</option>
          <option value="4">4 guests</option>
        </select>
      </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:10px">
      <button class="search-btn" onclick="doSearch()">Search</button>
      <button style="background:#eef6ff;border:0;padding:10px 14px;border-radius:10px;cursor:pointer" onclick="goHotels()">Browse all</button>
    </div>
  </section>
 
  <section class="featured">
    <h2 style="margin:6px 0 14px">Featured hotels</h2>
    <div class="grid">
      <?php
      $stmt = $mysqli->prepare("SELECT id,name,city,rating,description,cover_image FROM hotels ORDER BY rating DESC LIMIT 6");
      $stmt->execute();
      $res = $stmt->get_result();
      while ($h = $res->fetch_assoc()):
      ?>
      <div class="card">
        <img src="<?php echo htmlspecialchars($h['cover_image']); ?>" alt="<?php echo htmlspecialchars($h['name']); ?>" />
        <div class="card-body">
          <div style="display:flex;align-items:center;justify-content:space-between">
            <div>
              <div style="font-weight:700"><?php echo htmlspecialchars($h['name']); ?></div>
              <div class="muted"><?php echo htmlspecialchars($h['city']); ?></div>
            </div>
            <div class="rating"><?php echo htmlspecialchars($h['rating']); ?></div>
          </div>
          <p class="muted" style="margin-top:10px;font-size:13px"><?php echo htmlspecialchars(mb_strimwidth($h['description'],0,100,'...')); ?></p>
          <div style="margin-top:12px;display:flex;gap:8px">
            <button style="flex:1;padding:10px;border-radius:8px;border:1px solid #eef2ff;background:#fff;cursor:pointer" onclick="viewHotel(<?php echo $h['id']; ?>)">View</button>
            <button style="flex:1;padding:10px;border-radius:8px;border:0;background:var(--accent);color:#fff;cursor:pointer" onclick="quickSearch('<?php echo addslashes($h['city']); ?>')">Search in <?php echo htmlspecialchars($h['city']); ?></button>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </section>
 
  <footer>
    © <?php echo date('Y'); ?> Hilton Clone — Demo project. Built with internal CSS & JS.
  </footer>
 
  <script>
    function doSearch(){
      const dest = document.getElementById('destination').value.trim();
      const ci = document.getElementById('checkin').value;
      const co = document.getElementById('checkout').value;
      const guests = document.getElementById('guests').value;
      // Build query and use JS redirection to hotels.php
      const params = new URLSearchParams();
      if(dest) params.set('q', dest);
      if(ci) params.set('checkin', ci);
      if(co) params.set('checkout', co);
      if(guests) params.set('guests', guests);
      window.location.href = 'hotels.php?' + params.toString();
    }
    function viewHotel(id){
      window.location.href = 'hotel.php?id=' + id;
    }
    function quickSearch(city){
      const params = new URLSearchParams({ q: city });
      window.location.href = 'hotels.php?' + params.toString();
    }
    function goHotels(){ window.location.href = 'hotels.php'; }
  </script>
</body>
</html>
