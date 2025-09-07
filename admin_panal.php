<?php
session_start();
require 'db.php';

// Handle promotional banner update (file upload only, save to DB)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_banner'])) {
    $banner_images = [];
    for ($i = 1; $i <= 5; $i++) {
        if (!empty($_FILES["promo_banner_file$i"]['name'])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . basename($_FILES["promo_banner_file$i"]["name"]);
            if (move_uploaded_file($_FILES["promo_banner_file$i"]["tmp_name"], $target_file)) {
                $banner_images[] = $target_file;
            }
        }
    }
    if (!empty($banner_images)) {
        $images_json = json_encode($banner_images);
        // Remove previous banners (optional: keep history if you want)
        $conn->query("DELETE FROM promotional_banners");
        // Insert new banners
        $stmt = $conn->prepare("INSERT INTO promotional_banners (images) VALUES (?)");
        $stmt->bind_param("s", $images_json);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch promotional banners from DB
$promo_banner = ["https://cdn.pixabay.com/photo/2016/11/29/09/32/camera-1868773_1280.jpg"];
$sql = "SELECT images FROM promotional_banners ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $promo_banner = json_decode($row['images'], true);
    if (!is_array($promo_banner)) $promo_banner = [$promo_banner];
}

// Handle product addition
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $old_price = $_POST['old_price'] ?? null;
    $discount_percent = $_POST['discount_percent'] ?? null;
    $rating = $_POST['rating'] ?? 0;

    // Collect up to 5 images (file upload only)
    $images = [];
    for ($i = 1; $i <= 5; $i++) {
        $img_file = $_FILES["image_file$i"] ?? null;
        if (!empty($img_file['name'])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . basename($img_file["name"]);
            if (move_uploaded_file($img_file["tmp_name"], $target_file)) {
                $images[] = $target_file;
            }
        }
    }
    // Store images as JSON
    $image_json = json_encode($images);

    // Validate required fields
    if ($name && !empty($images) && $price !== '') {
        $old_price = ($old_price === '' || $old_price === null) ? null : $old_price;
        $discount_percent = ($discount_percent === '' || $discount_percent === null) ? null : $discount_percent;
        $rating = ($rating === '' || $rating === null) ? 0 : $rating;

        $stmt = $conn->prepare("INSERT INTO home_product (name, image, price, old_price, discount_percent, rating) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddii", $name, $image_json, $price, $old_price, $discount_percent, $rating);
        if ($stmt->execute()) {
            $msg = "Product added successfully!";
        } else {
            $msg = "Error adding product: " . $stmt->error;
        }
        $stmt->close();
    } else {
        if (!$msg) $msg = "Please fill all required fields and add at least one image.";
    }
}

// Handle product edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $edit_id = intval($_POST['edit_id']);
    $name = $_POST['edit_name'] ?? '';
    $price = $_POST['edit_price'] ?? '';
    $old_price = $_POST['edit_old_price'] ?? null;
    $discount_percent = $_POST['edit_discount_percent'] ?? null;
    $rating = $_POST['edit_rating'] ?? 0;

    // Handle new images (file upload only)
    $images = [];
    for ($i = 1; $i <= 5; $i++) {
        $img_file = $_FILES["edit_image_file$i"] ?? null;
        if (!empty($img_file['name'])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . basename($img_file["name"]);
            if (move_uploaded_file($img_file["tmp_name"], $target_file)) {
                $images[] = $target_file;
            }
        }
    }
    // If no new images, keep old images
    if (empty($images)) {
        $stmt = $conn->prepare("SELECT image FROM home_product WHERE id=?");
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $stmt->bind_result($old_images_json);
        $stmt->fetch();
        $stmt->close();
        $images = json_decode($old_images_json, true);
    }
    $image_json = json_encode($images);

    $stmt = $conn->prepare("UPDATE home_product SET name=?, image=?, price=?, old_price=?, discount_percent=?, rating=? WHERE id=?");
    $stmt->bind_param("ssddiii", $name, $image_json, $price, $old_price, $discount_percent, $rating, $edit_id);
    if ($stmt->execute()) {
        $msg = "Product updated successfully!";
    } else {
        $msg = "Error updating product: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch products for admin view
$products = [];
$sql = "SELECT * FROM home_product";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Prasad Electronic</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background: #f8f9fa; }
        .container { margin-top: 40px; }
        .table img { max-width: 60px; height: auto; margin-right: 5px; }
        .promo-banner { margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Promotional Banner Section -->
         <h1>Add with Promotional Banner</h1>
        <div class="promo-banner text-center">
            <?php if (!empty($promo_banner)): ?>
                <div id="banner-slider" style="position:relative; width:100%; max-width:100vw; overflow:hidden;">
                    <?php foreach ($promo_banner as $idx => $img): ?>
                        <img src="<?php echo htmlspecialchars($img); ?>"
                             class="img-fluid <?php echo $idx === 0 ? 'active' : ''; ?>"
                             alt="Promotional Banner"
                             style="width:100vw; max-width:100vw; height:60vh; min-height:320px; object-fit:cover; border-radius:0; box-shadow:0 0 20px #ccc; position:absolute; left:0; top:0; opacity:<?php echo $idx === 0 ? '1' : '0'; ?>; transition:opacity 0.7s;">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" class="mt-3" enctype="multipart/form-data">
                <div class="form-row align-items-center justify-content-center">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <div class="col-auto">
                        <input type="file" name="promo_banner_file<?php echo $i; ?>" class="form-control-file" accept="image/*">
                    </div>
                    <?php endfor; ?>
                    <div class="col-auto">
                        <button type="submit" name="update_banner" class="btn btn-info">Upload Banner(s)</button>
                    </div>
                </div>
            </form>
        </div>

        <h2 class="mb-4">Admin Panel - Manage Products</h2>
        <?php if (!empty($msg)): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <form method="POST" class="mb-4" enctype="multipart/form-data">
            <div class="form-row">
                <div class="col">
                    <input type="text" name="name" class="form-control" placeholder="Product Name" required>
                </div>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="col">
                    <input type="file" name="image_file<?php echo $i; ?>" class="form-control-file" accept="image/*">
                </div>
                <?php endfor; ?>
                <div class="col">
                    <input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required>
                </div>
                <div class="col">
                    <input type="number" step="0.01" name="old_price" class="form-control" placeholder="Old Price">
                </div>
                <div class="col">
                    <input type="number" name="discount_percent" class="form-control" placeholder="Discount %">
                </div>
                <div class="col">
                    <input type="number" name="rating" class="form-control" placeholder="Rating" min="0" max="5">
                </div>
                <div class="col">
                    <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                </div>
            </div>
        </form>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Images</th>
                    <th>Price</th>
                    <th>Old Price</th>
                    <th>Discount %</th>
                    <th>Rating</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo $product['id']; ?></td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td>
                        <?php
                        $imgs = json_decode($product['image'], true);
                        if (is_array($imgs)) {
                            foreach ($imgs as $img) {
                                echo '<img src="' . htmlspecialchars($img) . '" alt="img">';
                            }
                        }
                        ?>
                    </td>
                    <td><?php echo 'LKR.' . number_format($product['price'], 2); ?></td>
                    <td><?php echo !empty($product['old_price']) ? 'LKR.' . number_format($product['old_price'], 2) : '-'; ?></td>
                    <td><?php echo !empty($product['discount_percent']) ? intval($product['discount_percent']) . '%' : '-'; ?></td>
                    <td><?php echo intval($product['rating']); ?></td>
                    <td>
                        <!-- Edit Button triggers modal -->
                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal<?php echo $product['id']; ?>">Edit</button>
                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?php echo $product['id']; ?>" tabindex="-1" role="dialog">
                          <div class="modal-dialog" role="document">
                            <form method="POST" enctype="multipart/form-data" class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title">Edit Product</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                              </div>
                              <div class="modal-body">
                                <input type="hidden" name="edit_id" value="<?php echo $product['id']; ?>">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="edit_name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Images (upload to replace)</label>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input type="file" name="edit_image_file<?php echo $i; ?>" class="form-control-file mb-1" accept="image/*">
                                    <?php endfor; ?>
                                </div>
                                <div class="form-group">
                                    <label>Price</label>
                                    <input type="number" step="0.01" name="edit_price" class="form-control" value="<?php echo $product['price']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Old Price</label>
                                    <input type="number" step="0.01" name="edit_old_price" class="form-control" value="<?php echo $product['old_price']; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Discount %</label>
                                    <input type="number" name="edit_discount_percent" class="form-control" value="<?php echo $product['discount_percent']; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Rating</label>
                                    <input type="number" name="edit_rating" class="form-control" value="<?php echo $product['rating']; ?>" min="0" max="5">
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="submit" name="edit_product" class="btn btn-success">Save Changes</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                              </div>
                            </form>
                          </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-secondary mt-3">Back to Home Page</a>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
document.addEventListener("DOMContentLoaded", function() {
    var banners = document.querySelectorAll('#banner-slider img');
    if (banners.length > 1) {
        let current = 0;
        setInterval(function() {
            banners[current].style.opacity = "0";
            current = (current + 1) % banners.length;
            banners[current].style.opacity = "1";
        }, 3000);
    }
});
</script>
</body>
</html>