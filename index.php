<?php
session_start();
require 'db.php';

// Fetch products from home_product table
$products = [];
$sql = "SELECT * FROM home_product";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $images = [];
        if (!empty($row['image'])) {
            $decoded = json_decode($row['image'], true);
            if (is_array($decoded)) {
                $images = $decoded;
            } else {
                $images[] = $row['image'];
            }
        }
        $row['images'] = $images;
        $products[] = $row;
    }
}

// Fetch promotional banners from DB (array of images)
$promo_banner = ["https://cdn.pixabay.com/photo/2016/11/29/09/32/camera-1868773_1280.jpg"];
$sql = "SELECT images FROM promotional_banners ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $promo_banner = json_decode($row['images'], true);
    if (!is_array($promo_banner)) $promo_banner = [$promo_banner];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prasad Electronic - Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap & Font Awesome -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .navbar { background: #007bff; }
        .navbar-brand, .nav-link, .navbar-text { color: #fff !important; }
        .promo-banner-slider {
            width: 100vw;
            max-width: 100vw;
            height: 60vh;
            min-height: 320px;
            position: relative;
            left: 50%;
            right: 50%;
            transform: translate(-50%, 0);
            overflow: hidden;
            margin-bottom: 0;
            z-index: 1;
        }
        .promo-banner-slider img {
            width: 100vw;
            max-width: 100vw;
            height: 60vh;
            min-height: 320px;
            object-fit: cover;
            border-radius: 0;
            box-shadow: 0 0 20px #ccc;
            position: absolute;
            left: 0; top: 0;
            opacity: 0;
            transition: opacity 1s;
        }
        .promo-banner-slider img.active {
            opacity: 1;
            z-index: 2;
        }
        .promo-banner-slider .banner-indicators {
            position: absolute;
            bottom: 18px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 3;
        }
        .promo-banner-slider .banner-indicators span {
            display: block;
            width: 14px;
            height: 14px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 0 4px #888;
            opacity: 0.6;
            cursor: pointer;
            transition: opacity 0.3s, background 0.3s;
        }
        .promo-banner-slider .banner-indicators span.active {
            opacity: 1;
            background: #007bff;
        }
        @media (max-width: 900px) {
            .promo-banner-slider, .promo-banner-slider img { height: 30vh; min-height: 180px; }
            .deal-grid { gap: 18px; }
            .deal-card { width: 95vw; min-width: 220px; }
        }
        .deals { padding: 40px 0; }
        .deal-grid { display: flex; flex-wrap: wrap; gap: 30px; justify-content: center; }
        .deal-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 0 18px #e0e0e0;
            padding: 24px 18px 18px 18px;
            margin-bottom: 20px;
            width: 320px;
            min-height: 420px;
            text-align: center;
            position: relative;
            transition: transform 0.3s cubic-bezier(.4,2,.3,1), box-shadow 0.3s;
            overflow: hidden;
            animation: fadeInUp 0.8s;
        }
        .deal-card:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 8px 32px #d0d0d0;
        }
        .deal-image-slider {
            position: relative;
            width: 100%;
            height: 220px;
            margin-bottom: 18px;
            border-radius: 12px;
            overflow: hidden;
            background: #f8f8f8;
        }
        .deal-image-slider img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 12px;
            position: absolute;
            left: 0; top: 0;
            opacity: 0;
            transition: opacity 0.7s;
        }
        .deal-image-slider img.active {
            opacity: 1;
            z-index: 2;
        }
        .deal-title {
            font-weight: bold;
            font-size: 1.25rem;
            margin: 18px 0 10px 0;
        }
        .deal-price {
            color: #28a745;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .deal-old {
            text-decoration: line-through;
            color: #888;
            margin-left: 10px;
            font-size: 1.1rem;
        }
        .deal-off {
            color: #dc3545;
            font-size: 1rem;
            margin-left: 10px;
            font-weight: 600;
        }
        .star { color: #ffc107; font-size: 1.2rem; }
        .deal-rating { margin: 10px 0 0 0; font-size: 1.1rem; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px);}
            to { opacity: 1; transform: translateY(0);}
        }
        .category-row {
            display: flex;
            justify-content: center;
            gap: 32px;
            margin: 40px 0 30px 0;
            flex-wrap: wrap;
        }
        .category-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px #eee;
            padding: 24px 18px 18px 18px;
            min-width: 140px;
            max-width: 160px;
            text-align: center;
            transition: box-shadow 0.2s, transform 0.2s;
            cursor: pointer;
            border: 2px solid #f4f4f4;
        }
        .category-card:hover {
            box-shadow: 0 8px 24px #d0d0d0;
            transform: translateY(-4px) scale(1.04);
            border-color: #007bff;
        }
        .category-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px auto;
            font-size: 2rem;
            color: #fff;
            background: #007bff; /* Blue icon background */
            box-shadow: 0 2px 8px #b3d1ff;
        }
        .category-name {
            font-weight: 500;
            color: #333;
            font-size: 1.08rem;
            margin-top: 5px;
        }
        /* WhatsApp Float Button */
        #whatsapp-float {
            position: fixed;
            right: 28px;
            bottom: 28px;
            z-index: 9999;
            animation: bounceIn 1.2s;
            box-shadow: 0 4px 16px #3c3;
            border-radius: 50%;
            background: #25d366;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        #whatsapp-float:hover {
            box-shadow: 0 8px 32px #25d366;
            transform: scale(1.08);
        }
        @keyframes bounceIn {
            0% { transform: scale(0.2); opacity: 0; }
            60% { transform: scale(1.1); opacity: 1; }
            80% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="#">Prasad Electronic</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Order</a></li>
            </ul>
            <form class="form-inline my-2 my-lg-0 mr-3">
                <input class="form-control mr-sm-2" type="search" placeholder="Search for items..." aria-label="Search">
                <button class="btn btn-outline-light my-2 my-sm-0" type="submit">Search</button>
            </form>
            <span class="navbar-text">
                <a href="login.php" class="btn btn-light btn-sm mr-2">
                    <svg width="18" height="18" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16" style="vertical-align:middle;">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                        <path fill-rule="evenodd" d="M8 9a5 5 0 0 0-5 5v1h10v-1a5 5 0 0 0-5-5z"/>
                    </svg>
                    Login/Register
                </a>
                <a href="admin_panal_login.php" class="btn btn-warning btn-sm" title="Admin Panel">
                    <svg width="18" height="18" fill="currentColor" class="bi bi-person-gear" viewBox="0 0 16 16" style="vertical-align:middle;">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                        <path d="M8 9a5 5 0 0 0-4.546 2.916.5.5 0 0 0-.908.418A6.002 6.002 0 0 0 8 15a6.002 6.002 0 0 0 5.454-2.666.5.5 0 0 0-.908-.418A5 5 0 0 0 8 9z"/>
                        <path d="M13.5 12.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                        <path d="M12.5 11.5v-1a.5.5 0 0 1 1 0v1a.5.5 0 0 1-1 0z"/>
                    </svg>
                    Admin
                </a>
            </span>
        </div>
    </nav>

    <!-- Promotional Banner Slider -->
    <div class="promo-banner-slider" id="banner-slider">
        <?php foreach ($promo_banner as $idx => $img): ?>
            <img src="<?php echo htmlspecialchars($img); ?>"
                 class="<?php echo $idx === 0 ? 'active' : ''; ?>"
                 alt="Promotional Banner">
        <?php endforeach; ?>
        <div class="banner-indicators">
            <?php foreach ($promo_banner as $idx => $img): ?>
                <span class="<?php echo $idx === 0 ? 'active' : ''; ?>" data-idx="<?php echo $idx; ?>"></span>
            <?php endforeach; ?>
        </div>
    </div>

    <!--Browse by Categories-->
    <div class="container text-center mt-4">
        <h3 class="mb-4">Browse by Category</h3>
        <div class="category-row">
            <div class="category-card">
                <div class="category-icon" style="background:#007bff;">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="category-name">Electric Items</div>
            </div>
            <div class="category-card">
                <div class="category-icon" style="background:#ffc107;">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div class="category-name">Bulb</div>
            </div>
            <div class="category-card">
                <div class="category-icon" style="background:#e83e8c;">
                    <i class="fas fa-spa"></i>
                </div>
                <div class="category-name">Flowers</div>
            </div>
            <div class="category-card">
                <div class="category-icon" style="background:#28a745;">
                    <i class="fas fa-bicycle"></i>
                </div>
                <div class="category-name">Bycicle</div>
            </div>
            <div class="category-card">
                <div class="category-icon" style="background:#6f42c1;">
                    <i class="fas fa-puzzle-piece"></i>
                </div>
                <div class="category-name">Toy</div>
            </div>
           
        </div>   

    <!-- Today Deals Animated Grid -->
    <div class="container deals mt-3">
        <h3 class="mb-4 text-center">Today Deals</h3>
        <div class="deal-grid">
            <?php foreach ($products as $idx => $product): ?>
                <div class="deal-card" data-index="<?php echo $idx; ?>">
                    <div class="deal-image-slider" id="slider-<?php echo $idx; ?>">
                        <?php
                        $imgs = $product['images'];
                        if (count($imgs) === 0) $imgs[] = $product['image'];
                        foreach ($imgs as $imgIdx => $imgUrl):
                        ?>
                            <img src="<?php echo htmlspecialchars($imgUrl); ?>"
                                 class="<?php echo $imgIdx === 0 ? 'active' : ''; ?>"
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php endforeach; ?>
                        <!-- Wishlist Button -->
                        <?php
                        // Check if product is in wishlist (requires user session and wishlist table)
                        $isWishlisted = false;
                        if (isset($_SESSION['user_id'])) {
                            $wishSql = "SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?";
                            $wishStmt = $conn->prepare($wishSql);
                            $wishStmt->bind_param("ii", $_SESSION['user_id'], $product['id']);
                            $wishStmt->execute();
                            $wishStmt->store_result();
                            $isWishlisted = $wishStmt->num_rows > 0;
                            $wishStmt->close();
                        }
                        ?>
                        <form method="POST" action="add_to_wishlist.php" class="wishlist-form" style="position:absolute; top:10px; right:10px; z-index:3;">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="btn btn-sm wishlist-btn" style="background:transparent; border:none; font-size:1.4rem;" title="Add to Wishlist">
                                <i class="fas fa-heart" style="color:<?php echo $isWishlisted ? '#dc3545' : '#fff'; ?>; background:transparent; transition:color 0.2s;"></i>
                            </button>
                        </form>
                    </div>
                    <div class="deal-title"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div>
                        <span class="deal-price">
                            <?php echo isset($product['price']) ? 'LKR.' . number_format($product['price'], 2) : ''; ?>
                        </span>
                        <?php if (!empty($product['old_price'])): ?>
                            <span class="deal-old">
                                LKR.<?php echo number_format($product['old_price'], 2); ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($product['discount_percent'])): ?>
                            <span class="deal-off">
                                -<?php echo intval($product['discount_percent']); ?>%
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="deal-rating">
                        <span class="star">&#9733;</span>
                        <?php echo isset($product['rating']) ? intval($product['rating']) : '0'; ?>
                    </div>
                    <!-- View Product Button -->
                    <a href="view_product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-block mt-3">View Product</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    </div>  
    <!--Featured warrenty-->
    <div class="container text-center my-5">
        <h3 class="mb-4">Why Shop With Us?</h3>
        <div class="row">
            <div class="col-md-4">
                <i class="fas fa-shield-alt fa-3x mb-3" style="color:#007bff;"></i>
                <h5>Secure Payment</h5>
                <p>Your payment information is processed securely.</p>
            </div>
            <div class="col-md-4">
                <i class="fas fa-truck fa-3x mb-3" style="color:#28a745;"></i>
                <h5>Fast Delivery</h5>
                <p>We ensure quick and reliable delivery of your orders.</p>
            </div>
            <div class="col-md-4">
                <i class="fas fa-headset fa-3x mb-3" style="color:#ffc107;"></i>
                <h5>24/7 Support</h5>
                <p>Our support team is here to help you anytime.</p>
            </div>
        </div>
    
    <!-- Footer -->
    <!-- Footer -->
<footer class="bg-dark text-white pt-5 pb-3 mt-5 w-100" style="background:#343a40;">
    <div class="container-fluid">
        <div class="row justify-content-center text-left" 
             style="background:#343a40; border-radius:0; box-shadow:0 2px 16px #222; padding:40px 20px 20px 20px;">
            <div class="col-md-4 mb-4">
                <h5 class="text-uppercase mb-3 font-weight-bold">PRASAD ELECTRONIC</h5>
                <p>Leading electronics store for Mobiles, Appliances, Gadgets, and more. Trusted by thousands of happy customers.</p>
                <div>
                    <a href="#" class="text-white mr-3"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-white mr-3"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-white mr-3"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
            </div>
            <div class="col-md-2 mb-4">
                <h6 class="text-uppercase mb-3 font-weight-bold">QUICK LINKS</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white-50">Home</a></li>
                    <li><a href="#" class="text-white-50">Products</a></li>
                    <li><a href="#" class="text-white-50">Contact</a></li>
                    <li><a href="#" class="text-white-50">Order</a></li>
                    <li><a href="admin_panal_login.php" class="text-white-50">Admin Panel</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h6 class="text-uppercase mb-3 font-weight-bold">CUSTOMER SERVICE</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white-50">FAQ</a></li>
                    <li><a href="#" class="text-white-50">Shipping & Returns</a></li>
                    <li><a href="#" class="text-white-50">Warranty</a></li>
                    <li><a href="#" class="text-white-50">Support</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h6 class="text-uppercase mb-3 font-weight-bold">CONTACT US</h6>
                <p class="mb-1"><i class="fas fa-map-marker-alt mr-2"></i>123 Main Street, Colombo, Sri Lanka</p>
                <p class="mb-1"><i class="fas fa-phone mr-2"></i>+94 77 123 4567</p>
                <p class="mb-1"><i class="fas fa-envelope mr-2"></i>info@prasadelectronic.lk</p>
            </div>
        </div>
        <hr class="bg-secondary">
        <div class="row">
            <div class="col text-center">
                <small>
                    &copy; <?php echo date("Y"); ?> Prasad Electronic. All rights reserved.
                </small>
            </div>
        </div>
    </div>
</footer>


    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/94765347444" target="_blank" id="whatsapp-float" title="Chat on WhatsApp">
        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" style="width:60px; height:60px;">
    </a>

    <!-- Bootstrap JS CDN -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Banner slider animation -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Banner slider
            var banners = document.querySelectorAll('#banner-slider img');
            var indicators = document.querySelectorAll('#banner-slider .banner-indicators span');
            var current = 0;
            function showBanner(idx) {
                banners.forEach(function(img, i) {
                    img.classList.toggle('active', i === idx);
                });
                indicators.forEach(function(dot, i) {
                    dot.classList.toggle('active', i === idx);
                });
                current = idx;
            }
            // Auto animation
            setInterval(function() {
                var next = (current + 1) % banners.length;
                showBanner(next);
            }, 3500);
            // Click indicators
            indicators.forEach(function(dot, idx) {
                dot.addEventListener('click', function() {
                    showBanner(idx);
                });
            });

            // Product image slider
            document.querySelectorAll('.deal-image-slider').forEach(function(slider) {
                var imgs = slider.querySelectorAll('img');
                if (imgs.length <= 1) return;
                let currentImg = 0;
                setInterval(function() {
                    imgs[currentImg].classList.remove('active');
                    currentImg = (currentImg + 1) % imgs.length;
                    imgs[currentImg].classList.add('active');
                }, 2500);
            });
        });
    </script>
    <!-- Add this JS before </body> for instant color change on click -->
<script>
document.querySelectorAll('.wishlist-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = form.querySelector('.wishlist-btn i');
        btn.style.color = '#dc3545';
        // AJAX add to wishlist (optional, fallback to form submit)
        var fd = new FormData(form);
        fetch(form.action, {method:'POST', body:fd})
            .then(r => r.text())
            .then(res => {
                // Optionally show a message
            });
    });
});
</script>
</body>
</html>