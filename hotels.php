<?php
require_once 'db.php';
 
// Get filter/sort from query params
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$minPrice = isset($_GET['min']) ? floatval($_GET['min']) : 0;
$maxPrice = isset($_GET['max']) ? floatval($_GET['max']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'relevance';
 
// Build SQL: join hotels & rooms, aggregate lowest price per hotel
$sql = "SELECT h.id,h.name,h.city,h.rating,h.description,h.cover_image, MIN(r.price) as min_price
        FROM hotels h
        JOIN rooms r ON r.hotel_id = h.id
        WHERE 1=1 ";
$params = [];
$types = '';
if($q !== ''){
    $sql .= " AND (h.city LIKE CONCAT('%',?,'%') OR h.name LIKE CONCAT('%',?,'%')) ";
    $params[] = $q; $params[] = $q; $types .= 'ss';
}
$sql .= " GROUP BY h.id ";
// Sorting
if($sort === 'price_asc') $sql .= " ORDER BY min_price ASC ";
elseif($sort === 'price_desc') $sql .= " ORDER BY min_price DESC ";
elseif($sort === 'rating') $sql .= " ORDER BY h.rating DESC ";
else $sql .= " ORDER BY h.rating DESC, min_price ASC ";
 
$stmt = $mysqli->prepare($sql);
if($types !== '') $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/><title>Listings — Hilton Clone</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
    /* Internal CSS: clean listing style */
    body{font-family:Inter,Arial;background:#f7fbff;color:#08102a;margin:0}
    header{padding:18px 24px;background:#fff;display:flex;align-items:center;justify-content:space-between;box-shadow:0 6px 20px rgba(10,20,50,0.04)}
    .brand{display:flex;gap:10px;align-items:center}
    .logo{width:44px;height:44px;border-radius:8px;background:linear-gradient(135deg,#0b5cff,#3ea0ff);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700}
    .container{max-width:1100px;margin:20px auto;padding:0 18px}
    .controls{display:flex;gap:12px;align-items:center;margin-bottom:14px}
    select,input[type=number]{padding:10px;border-radius:8px;border:1px solid #e6eefb;background:#fff}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px}
    .card{background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(10,20,50,0.06);overflow:hidden;display:flex;flex-direction:column}
    .card img{width:100%;height:180px;object-fit:cover}
    .card-body{padding:14px}
    .muted{color:#6b7280;font-size:13px}
    .price{font-weight:800;color:#0b5cff}
    @media(max-width:700px){.controls{flex-direction:column;align-items:stretch}}
  </style>
</head>
<body>
  <header>
    <div class="brand"><div class="logo">H</div><div><strong>Hilton Clone</strong><div style="font-size:12px;color:#6b7280">Listings</div></div></div>
    <div style="font-size:13px;color:#6b7280">Search results for: <strong><?php echo htmlspecialchars($q?:'All cities'); ?></strong></div>
  </header>
 
  <div class="container">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div class="controls">
        <label class="muted">Sort:</label>
        <select id="sort" onchange="applyFilters()">
          <option value="relevance" <?php if($sort==='relevance') echo 'selected'; ?>>Best match</option>
          <option value="price_asc" <?php if($sort==='price_asc') echo 'selected'; ?>>Price: low to high</option>
          <option value="price_desc" <?php if($sort==='price_desc') echo 'selected'; ?>>Price: high to low</option>
          <option value="rating" <?php if($sort==='rating') echo 'selected'; ?>>Top rated</option>
        </select>
        <label class="muted" style="margin-left:10px">Min</label>
        <input id="min" type="number" placeholder="0" value="<?php echo $minPrice>0?$minPrice:''; ?>" />
        <label class="muted">Max</label>
        <input id="max" type="number" placeholder="0" value="<?php echo $maxPrice>0?$maxPrice:''; ?>" />
        <button style="padding:10px 12px;border-radius:8px;background:#0b5cff;color:#fff;border:0;margin-left:6px;cursor:pointer" onclick="applyFilters()">Apply</button>
      </div>
      <div class="muted">Found: <?php echo $res->num_rows; ?> hotels</div>
    </div>
 
    <div class="grid">
      <?php while($h = $res->fetch_assoc()): ?>
      <div class="card">
        <img src="<?php echo htmlspecialchars($h['cover_image']); ?>" alt="" />
        <div class="card-body">
          <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <div>
              <div style="font-weight:700"><?php echo htmlspecialchars($h['name']); ?></div>
              <div class="muted"><?php echo htmlspecialchars($h['city']); ?></div>
            </div>
            <div style="text-align:right">
              <div class="price">$<?php echo number_format($h['min_price'],2); ?></div>
              <div class="muted">from</div>
            </div>
          </div>
          <p class="muted" style="margin-top:10px"><?php echo htmlspecialchars(mb_strimwidth($h['description'],0,120,'...')); ?></p>
          <div style="display:flex;gap:8px;margin-top:12px">
            <button style="flex:1;padding:10px;border-radius:8px;border:1px solid #eef2ff;background:#fff;cursor:pointer" onclick="viewHotel(<?php echo $h['id']; ?>)">Details</button>
            <button style="flex:1;padding:10px;border-radius:8px;border:0;background:#0b5cff;color:#fff;cursor:pointer" onclick="viewHotel(<?php echo $h['id']; ?>)">View rooms</button>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
 
  <script>
    function applyFilters(){
      const params = new URLSearchParams(window.location.search);
      params.set('sort', document.getElementById('sort').value);
      const min = document.getElementById('min').value;
      const max = document.getElementById('max').value;
      if(min) params.set('min', min); else params.delete('min');
      if(max) params.set('max', max); else params.delete('max');
      // Keep existing q, checkin, checkout, guests if present
      window.location.href = 'hotels.php?' + params.toString();
    }
    function viewHotel(id){ window.location.href = 'hotel.php?id=' + id + '&' + window.location.search.replace(/^\?/,''); }
  </script>
</body>
</html>
