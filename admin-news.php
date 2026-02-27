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

// Handle Delete Image
if (isset($_GET['delete_image'])) {
    $image_id = $_GET['delete_image'];

    // Get image path
    $query = "SELECT image_path FROM news_images WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $image_id);
    $stmt->execute();
    $img = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($img && file_exists('uploads/news/' . $img['image_path'])) {
        unlink('uploads/news/' . $img['image_path']);
    }

    // Delete from database
    $query = "DELETE FROM news_images WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $image_id);
    $stmt->execute();

    $message = "Image deleted successfully!";
}

// Handle Delete News
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Get all news images
    $query = "SELECT image_path FROM news_images WHERE news_id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Delete image files
    foreach ($images as $img) {
        if (file_exists('uploads/news/' . $img['image_path'])) {
            unlink('uploads/news/' . $img['image_path']);
        }
    }

    // Delete from database (news_images will cascade delete)
    $query = "DELETE FROM news WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        $message = "News article deleted successfully!";
    } else {
        $error = "Failed to delete news article.";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $excerpt = trim($_POST['excerpt']);
    $author = trim($_POST['author']);
    $category = $_POST['category'];
    $status = $_POST['status'];

    // Create slug from title
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

    if ($id) {
        // Update existing news
        $query = "UPDATE news SET title = :title, slug = :slug, content = :content, 
                 excerpt = :excerpt, author = :author, category = :category, status = :status, 
                 published_at = " . ($status == 'published' ? 'NOW()' : 'NULL') . " 
                 WHERE id = :id";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':excerpt', $excerpt);
        $stmt->bindParam(':author', $author);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            // Handle multiple image uploads
            if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $order = 0;

                for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                    if ($_FILES['images']['error'][$i] == 0) {
                        $filename = $_FILES['images']['name'][$i];
                        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

                        if (in_array(strtolower($filetype), $allowed)) {
                            $image_name = uniqid() . '.' . $filetype;
                            $upload_path = 'uploads/news/' . $image_name;

                            // Create directory if it doesn't exist
                            if (!is_dir('uploads/news')) {
                                mkdir('uploads/news', 0777, true);
                            }

                            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $upload_path)) {
                                // Insert into news_images table
                                $img_query = "INSERT INTO news_images (news_id, image_path, display_order) 
                                            VALUES (:news_id, :image_path, :order)";
                                $img_stmt = $conn->prepare($img_query);
                                $img_stmt->bindParam(':news_id', $id);
                                $img_stmt->bindParam(':image_path', $image_name);
                                $img_stmt->bindParam(':order', $order);
                                $img_stmt->execute();
                                $order++;
                            }
                        }
                    }
                }
            }

            $message = "News article updated successfully!";
            // Redirect to news list after update
            header("Location: admin-news.php");
            exit();
        } else {
            $error = "Failed to update news article.";
        }
    } else {
        // Add new news
        $query = "INSERT INTO news (title, slug, content, excerpt, author, category, status, 
                 published_at, created_by) 
                 VALUES (:title, :slug, :content, :excerpt, :author, :category, :status, 
                 " . ($status == 'published' ? 'NOW()' : 'NULL') . ", :created_by)";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':excerpt', $excerpt);
        $stmt->bindParam(':author', $author);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':created_by', $_SESSION['admin_id']);

        if ($stmt->execute()) {
            $news_id = $conn->lastInsertId();

            // Handle multiple image uploads
            if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $order = 0;

                for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                    if ($_FILES['images']['error'][$i] == 0) {
                        $filename = $_FILES['images']['name'][$i];
                        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

                        if (in_array(strtolower($filetype), $allowed)) {
                            $image_name = uniqid() . '.' . $filetype;
                            $upload_path = 'uploads/news/' . $image_name;

                            // Create directory if it doesn't exist
                            if (!is_dir('uploads/news')) {
                                mkdir('uploads/news', 0777, true);
                            }

                            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $upload_path)) {
                                // Insert into news_images table
                                $img_query = "INSERT INTO news_images (news_id, image_path, display_order) 
                                            VALUES (:news_id, :image_path, :order)";
                                $img_stmt = $conn->prepare($img_query);
                                $img_stmt->bindParam(':news_id', $news_id);
                                $img_stmt->bindParam(':image_path', $image_name);
                                $img_stmt->bindParam(':order', $order);
                                $img_stmt->execute();
                                $order++;
                            }
                        }
                    }
                }
            }

            $message = "News article added successfully!";
            // Redirect to news list after adding
            header("Location: admin-news.php");
            exit();
        } else {
            $error = "Failed to add news article.";
        }
    }
}

// Get all news
$query = "SELECT * FROM news ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$news_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get news for editing
$edit_news = null;
$edit_news_images = [];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM news WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $edit_news = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch images for this news article
    if ($edit_news) {
        $img_query = "SELECT * FROM news_images WHERE news_id = :id ORDER BY display_order ASC";
        $img_stmt = $conn->prepare($img_query);
        $img_stmt->bindParam(':id', $id);
        $img_stmt->execute();
        $edit_news_images = $img_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$show_form = isset($_GET['action']) && $_GET['action'] == 'add' || isset($_GET['edit']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News - SafeHaven Admin</title>
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
            min-height: 200px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-color);
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

        .news-table {
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

        .news-img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        /* Image upload section styles */
        .image-upload-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border: 2px dashed var(--accent-color);
        }

        .image-upload-section h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .drag-drop-area {
            border: 2px dashed #ccc;
            border-radius: 6px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: var(--white);
        }

        .drag-drop-area.active {
            background: #f0f8ff;
            border-color: var(--accent-color);
        }

        .drag-drop-area p {
            margin: 0;
            color: #666;
        }

        .drag-drop-area small {
            display: block;
            color: #999;
            margin-top: 5px;
        }

        #imageInput {
            display: none;
        }

        .image-preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .image-preview-item {
            position: relative;
            background: #f0f0f0;
            border-radius: 6px;
            overflow: hidden;
            aspect-ratio: 1;
        }

        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-preview-item .delete-img-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .image-preview-item .delete-img-btn:hover {
            background: #c82333;
            transform: scale(1.1);
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

        .status-published {
            background: #d4edda;
            color: #155724;
        }

        .status-draft {
            background: #f8d7da;
            color: #721c24;
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
                <li><a href="admin-properties.php">Manage Properties</a></li>
                <li><a href="admin-news.php" class="active">Manage News</a></li>
                <li><a href="index.php" target="_blank">View Website →</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Manage News</h1>
            <?php if (!$show_form): ?>
                <a href="?action=add" class="btn-primary">+ Add News Article</a>
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
                <h2><?php echo $edit_news ? 'Edit News Article' : 'Add News Article'; ?></h2>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit_news): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_news['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group full-width">
                        <label>Article Title *</label>
                        <input type="text" name="title" required
                            value="<?php echo $edit_news ? htmlspecialchars($edit_news['title']) : ''; ?>">
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Author *</label>
                            <input type="text" name="author" required
                                value="<?php echo $edit_news ? htmlspecialchars($edit_news['author']) : 'SafeHaven Team'; ?>">
                        </div>

                        <div class="form-group">
                            <label>Location (Delta State) *</label>
                            <select name="location" required>
                                <option value="Asaba">Asaba</option>
                                <option value="Agbor">Agbor</option>
                                <option value="Kwale">Kwale</option>
                                <option value="Abraka">Abraka</option>
                                <option value="Oghara">Oghara</option>
                                <option value="Ozoro">Ozoro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Category *</label>
                            <select name="category" required>
                                <option value="General" <?php echo ($edit_news && $edit_news['category'] == 'General') ? 'selected' : ''; ?>>General</option>
                                <option value="Market Update" <?php echo ($edit_news && $edit_news['category'] == 'Market Update') ? 'selected' : ''; ?>>Market Update</option>
                                <option value="Tips" <?php echo ($edit_news && $edit_news['category'] == 'Tips') ? 'selected' : ''; ?>>Tips</option>
                                <option value="News" <?php echo ($edit_news && $edit_news['category'] == 'News') ? 'selected' : ''; ?>>News</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Status *</label>
                            <select name="status" required>
                                <option value="draft" <?php echo ($edit_news && $edit_news['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo ($edit_news && $edit_news['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                            </select>
                        </div>
                    </div>

                    <div class="image-upload-section">
                        <h3>Article Images (Upload Multiple)</h3>
                        <div class="drag-drop-area" id="dragDropArea">
                            <p>Drag and drop images here or click to select</p>
                            <small>Supported formats: JPG, JPEG, PNG, GIF</small>
                        </div>
                        <input type="file" id="imageInput" name="images[]" accept="image/*" multiple>

                        <?php if ($edit_news && count($edit_news_images) > 0): ?>
                            <div style="margin-top: 20px;">
                                <h4 style="color: var(--primary-color); margin-bottom: 15px;">Current Images</h4>
                                <div class="image-preview-container">
                                    <?php foreach ($edit_news_images as $img): ?>
                                        <div class="image-preview-item">
                                            <img src="uploads/news/<?php echo htmlspecialchars($img['image_path']); ?>" alt="News Image">
                                            <a href="?edit=<?php echo $edit_news['id']; ?>&delete_image=<?php echo $img['id']; ?>"
                                                class="delete-img-btn"
                                                onclick="return confirm('Delete this image?')">×</a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="image-preview-container" id="previewContainer"></div>
                    </div>

                    <div class="form-group full-width">
                        <label>Excerpt (Short Description) *</label>
                        <textarea name="excerpt" rows="3" required><?php echo $edit_news ? htmlspecialchars($edit_news['excerpt']) : ''; ?></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label>Full Content *</label>
                        <textarea name="content" required><?php echo $edit_news ? htmlspecialchars($edit_news['content']) : ''; ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <?php echo $edit_news ? 'Update Article' : 'Add Article'; ?>
                        </button>
                        <a href="admin-news.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="news-table">
            <h2 style="color: var(--primary-color); margin-bottom: 20px;">All News Articles</h2>
            <?php if (count($news_list) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($news_list as $news): ?>
                            <tr>
                                <td>
                                    <?php if ($news['image'] && file_exists('uploads/news/' . $news['image'])): ?>
                                        <img src="uploads/news/<?php echo htmlspecialchars($news['image']); ?>"
                                            alt="News" class="news-img">
                                    <?php else: ?>
                                        <div class="news-img" style="background:#ddd; display:flex; align-items:center; justify-content:center; color:#999;">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($news['title']); ?></td>
                                <td><?php echo htmlspecialchars($news['category']); ?></td>
                                <td><?php echo htmlspecialchars($news['author']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $news['status']; ?>">
                                        <?php echo ucfirst($news['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($news['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?php echo $news['id']; ?>" class="btn-edit">Edit</a>
                                        <a href="?delete=<?php echo $news['id']; ?>"
                                            class="btn-delete"
                                            onclick="return confirm('Are you sure you want to delete this news article?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #999; text-align: center; padding: 40px 0;">No news articles found. Add your first article!</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Drag and drop functionality
        const dragDropArea = document.getElementById('dragDropArea');
        const imageInput = document.getElementById('imageInput');
        const previewContainer = document.getElementById('previewContainer');
        let selectedFiles = [];

        dragDropArea.addEventListener('click', () => imageInput.click());

        dragDropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dragDropArea.classList.add('active');
        });

        dragDropArea.addEventListener('dragleave', () => {
            dragDropArea.classList.remove('active');
        });

        dragDropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dragDropArea.classList.remove('active');

            const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
            handleFiles(files);
        });

        imageInput.addEventListener('change', (e) => {
            handleFiles(Array.from(e.target.files));
        });

        function handleFiles(files) {
            files.forEach((file, index) => {
                const reader = new window.FileReader();
                reader.onload = (e) => {
                    const fileId = Date.now() + index;
                    const preview = document.createElement('div');
                    preview.className = 'image-preview-item';
                    preview.id = 'file-' + fileId;
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="delete-img-btn" onclick="removePreview('${fileId}')">×</button>
                    `;
                    previewContainer.appendChild(preview);
                    selectedFiles.push({
                        id: fileId,
                        file: file
                    });
                };
                reader.readAsDataURL(file);
            });

            // Update the hidden input with selected files
            updateFileInput();
        }

        function removePreview(fileId) {
            const element = document.getElementById('file-' + fileId);
            if (element) element.remove();

            selectedFiles = selectedFiles.filter(f => f.id != fileId);
            updateFileInput();
        }

        function updateFileInput() {
            // Create a new DataTransfer to hold the files
            const dataTransfer = new window.DataTransfer();
            selectedFiles.forEach(f => dataTransfer.items.add(f.file));
            imageInput.files = dataTransfer.files;
        }
    </script>
</body>

</html>