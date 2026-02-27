<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

$db = new \Database();
$conn = $db->getConnection();

$message = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Get property images first
    $query = "SELECT main_image FROM properties WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete image file if exists
    if ($property && $property['main_image'] && file_exists('uploads/properties/' . $property['main_image'])) {
        unlink('uploads/properties/' . $property['main_image']);
    }

    // Delete from database
    $query = "DELETE FROM properties WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        $message = "Property deleted successfully!";
    } else {
        $error = "Failed to delete property.";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $property_type = $_POST['property_type'];
    $location = trim($_POST['location']);
    $bedrooms = $_POST['bedrooms'];
    $bathrooms = $_POST['bathrooms'];
    $area_sqm = $_POST['area_sqm'];
    $status = $_POST['status'];
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Handle image upload
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($filetype), $allowed)) {
            $image_name = uniqid() . '.' . $filetype;
            $upload_path = 'uploads/properties/' . $image_name;

            // Create directory if it doesn't exist
            if (!is_dir('uploads/properties')) {
                mkdir('uploads/properties', 0777, true);
            }

            move_uploaded_file($_FILES['image']['tmp_name'], $upload_path);
        }
    }

    if ($id) {
        // Update existing property
        if ($image_name) {
            $query = "UPDATE properties SET title = :title, description = :description, price = :price, 
                     property_type = :property_type, location = :location, bedrooms = :bedrooms, 
                     bathrooms = :bathrooms, area_sqm = :area_sqm, status = :status, featured = :featured, 
                     main_image = :image WHERE id = :id";
        } else {
            $query = "UPDATE properties SET title = :title, description = :description, price = :price, 
                     property_type = :property_type, location = :location, bedrooms = :bedrooms, 
                     bathrooms = :bathrooms, area_sqm = :area_sqm, status = :status, featured = :featured 
                     WHERE id = :id";
        }

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':property_type', $property_type);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':bedrooms', $bedrooms);
        $stmt->bindParam(':bathrooms', $bathrooms);
        $stmt->bindParam(':area_sqm', $area_sqm);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':featured', $featured);
        if ($image_name) $stmt->bindParam(':image', $image_name);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            $message = "Property updated successfully!";
        } else {
            $error = "Failed to update property.";
        }
    } else {
        // Add new property
        $query = "INSERT INTO properties (title, description, price, property_type, location, bedrooms, 
                 bathrooms, area_sqm, status, featured, main_image, created_by) 
                 VALUES (:title, :description, :price, :property_type, :location, :bedrooms, 
                 :bathrooms, :area_sqm, :status, :featured, :image, :created_by)";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':property_type', $property_type);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':bedrooms', $bedrooms);
        $stmt->bindParam(':bathrooms', $bathrooms);
        $stmt->bindParam(':area_sqm', $area_sqm);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':featured', $featured);
        $stmt->bindParam(':image', $image_name);
        $stmt->bindParam(':created_by', $_SESSION['admin_id']);

        if ($stmt->execute()) {
            $message = "Property added successfully!";
        } else {
            $error = "Failed to add property.";
        }
    }
}

// Get all properties
$query = "SELECT * FROM properties ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get property for editing
$edit_property = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM properties WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $edit_property = $stmt->fetch(PDO::FETCH_ASSOC);
}

$show_form = isset($_GET['action']) && $_GET['action'] == 'add' || isset($_GET['edit']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Properties - SafeHaven Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #4B2C6B;
            --primary-dark: #2D1B47;
            --accent-color: #D4AF37;
            --text-dark: #333333;
            --white: #ffffff;
            --light-gray: #EEE9F8;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light-gray);
        }

        .admin-header {
            background: var(--primary-color);
            color: var(--white);
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .admin-header .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-color);
            letter-spacing: 1px;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .btn-logout {
            background: var(--accent-color);
            color: var(--primary-dark);
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: var(--white);
        }

        .admin-nav {
            background: var(--primary-dark);
            padding: 15px 0;
            border-bottom: 2px solid var(--accent-color);
        }

        .admin-nav .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .admin-nav ul {
            list-style: none;
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .admin-nav a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .admin-nav a:hover,
        .admin-nav a.active {
            color: var(--accent-color);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            color: var(--primary-color);
            font-size: 2rem;
        }

        .btn-primary {
            background: var(--accent-color);
            color: var(--primary-dark);
            padding: 12px 25px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-primary:hover {
            background: var(--primary-color);
            color: var(--accent-color);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .form-container {
            background: var(--white);
            padding: 35px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .form-container h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
            font-size: 1.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-color);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn-submit {
            background: var(--accent-color);
            color: var(--primary-dark);
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: var(--primary-color);
            color: var(--accent-color);
        }

        .btn-cancel {
            background: #666;
            color: var(--white);
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            background: #888;
        }

        .properties-table {
            background: var(--white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: var(--light-gray);
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: var(--text-dark);
            border-bottom: 2px solid var(--accent-color);
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #fafafa;
        }

        .property-img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-edit,
        .btn-delete {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #007bff;
            color: var(--white);
        }

        .btn-edit:hover {
            background: #0056b3;
        }

        .btn-delete {
            background: #dc3545;
            color: var(--white);
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-available {
            background: #d4edda;
            color: #155724;
        }

        .status-sold {
            background: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <header class="admin-header">
        <div class="container">
            <div class="admin-logo">SAFEHAVEN ADMIN</div>
            <div class="admin-user">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <a href="admin-logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </header>

    <nav class="admin-nav">
        <div class="container">
            <ul>
                <li><a href="admin-dashboard.php">Dashboard</a></li>
                <li><a href="admin-properties.php" class="active">Manage Properties</a></li>
                <li><a href="admin-news.php">Manage News</a></li>
                <li><a href="index.php" target="_blank">View Website →</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Manage Properties</h1>
            <?php if (!$show_form): ?>
                <a href="?action=add" class="btn-primary">+ Add New Property</a>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($show_form): ?>
            <div class="form-container">
                <h2><?php echo $edit_property ? 'Edit Property' : 'Add New Property'; ?></h2>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit_property): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_property['id']; ?>">
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Property Title *</label>
                            <input type="text" name="title" required
                                value="<?php echo $edit_property ? htmlspecialchars($edit_property['title']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label>Property Type *</label>
                            <select name="property_type" required>
                                <option value="">Select Type</option>
                                <option value="Duplex" <?php echo ($edit_property && $edit_property['property_type'] == 'Duplex') ? 'selected' : ''; ?>>Duplex</option>
                                <option value="Apartment" <?php echo ($edit_property && $edit_property['property_type'] == 'Apartment') ? 'selected' : ''; ?>>Apartment</option>
                                <option value="Land" <?php echo ($edit_property && $edit_property['property_type'] == 'Land') ? 'selected' : ''; ?>>Land</option>
                                <option value="Villa" <?php echo ($edit_property && $edit_property['property_type'] == 'Villa') ? 'selected' : ''; ?>>Villa</option>
                                <option value="House" <?php echo ($edit_property && $edit_property['property_type'] == 'House') ? 'selected' : ''; ?>>House</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Price (₦) *</label>
                            <input type="number" name="price" step="0.01" required
                                value="<?php echo $edit_property ? $edit_property['price'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label>Location *</label>
                            <input type="text" name="location" required
                                value="<?php echo $edit_property ? htmlspecialchars($edit_property['location']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label>Bedrooms</label>
                            <input type="number" name="bedrooms" min="0"
                                value="<?php echo $edit_property ? $edit_property['bedrooms'] : '0'; ?>">
                        </div>

                        <div class="form-group">
                            <label>Bathrooms</label>
                            <input type="number" name="bathrooms" min="0"
                                value="<?php echo $edit_property ? $edit_property['bathrooms'] : '0'; ?>">
                        </div>

                        <div class="form-group">
                            <label>Area (sqm)</label>
                            <input type="number" name="area_sqm" step="0.01"
                                value="<?php echo $edit_property ? $edit_property['area_sqm'] : '0'; ?>">
                        </div>

                        <div class="form-group">
                            <label>Status *</label>
                            <select name="status" required>
                                <option value="available" <?php echo ($edit_property && $edit_property['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                                <option value="sold" <?php echo ($edit_property && $edit_property['status'] == 'sold') ? 'selected' : ''; ?>>Sold</option>
                                <option value="pending" <?php echo ($edit_property && $edit_property['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label>Description</label>
                            <textarea name="description"><?php echo $edit_property ? htmlspecialchars($edit_property['description']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Property Image</label>
                            <input type="file" name="image" accept="image/*">
                            <?php if ($edit_property && $edit_property['main_image']): ?>
                                <small style="display:block; margin-top:5px; color:#666;">Current: <?php echo htmlspecialchars($edit_property['main_image']); ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" name="featured" id="featured"
                                    <?php echo ($edit_property && $edit_property['featured']) ? 'checked' : ''; ?>>
                                <label for="featured" style="margin:0;">Featured Property</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <?php echo $edit_property ? 'Update Property' : 'Add Property'; ?>
                        </button>
                        <a href="admin-properties.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="properties-table">
            <h2 style="color: var(--primary-color); margin-bottom: 20px;">All Properties</h2>
            <?php if (count($properties) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($properties as $property): ?>
                            <tr>
                                <td>
                                    <?php if ($property['main_image'] && file_exists('uploads/properties/' . $property['main_image'])): ?>
                                        <img src="uploads/properties/<?php echo htmlspecialchars($property['main_image']); ?>"
                                            alt="Property" class="property-img">
                                    <?php else: ?>
                                        <div class="property-img" style="background:#ddd; display:flex; align-items:center; justify-content:center; color:#999;">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($property['title']); ?></td>
                                <td><?php echo htmlspecialchars($property['property_type']); ?></td>
                                <td><?php echo htmlspecialchars($property['location']); ?></td>
                                <td>₦<?php echo number_format($property['price']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $property['status']; ?>">
                                        <?php echo ucfirst($property['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?php echo $property['id']; ?>" class="btn-edit">Edit</a>
                                        <a href="?delete=<?php echo $property['id']; ?>"
                                            class="btn-delete"
                                            onclick="return confirm('Are you sure you want to delete this property?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #999; text-align: center; padding: 40px 0;">No properties found. Add your first property!</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>