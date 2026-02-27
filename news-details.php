<?php
session_start();
include_once 'config/database.php';

// Get news ID or slug
$news_id = null;
$slug = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $news_id = (int)$_GET['id'];
} elseif (isset($_GET['slug'])) {
    $slug = $_GET['slug'];
} else {
    header("Location: index.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Fetch news article
if ($news_id) {
    $query = "SELECT n.*, a.full_name as author_name 
              FROM news n 
              LEFT JOIN admin_users a ON n.created_by = a.id 
              WHERE n.id = :id AND n.status = 'published' LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $news_id);
} else {
    $query = "SELECT n.*, a.full_name as author_name 
              FROM news n 
              LEFT JOIN admin_users a ON n.created_by = a.id 
              WHERE n.slug = :slug AND n.status = 'published' LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':slug', $slug);
}

$stmt->execute();
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    header("Location: index.php");
    exit();
}

// Update view count
$update_views = $conn->prepare("UPDATE news SET views = views + 1 WHERE id = :id");
$update_views->bindParam(':id', $news['id']);
$update_views->execute();

// Fetch news images
$img_query = "SELECT * FROM news_images WHERE news_id = :id ORDER BY display_order ASC";
$img_stmt = $conn->prepare($img_query);
$img_stmt->bindParam(':id', $news['id']);
$img_stmt->execute();
$news_images = $img_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch related articles
$related_query = "SELECT * FROM news 
                  WHERE category = :category 
                  AND id != :id 
                  AND status = 'published' 
                  ORDER BY published_at DESC 
                  LIMIT 3";
$related_stmt = $conn->prepare($related_query);
$related_stmt->bindParam(':category', $news['category']);
$related_stmt->bindParam(':id', $news['id']);
$related_stmt->execute();
$related_articles = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

// Format date
function formatDate($date)
{
    return date('F j, Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['title']); ?> | SafeHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/header-styles.php'; ?>
    <style>
        /* PAGE-SPECIFIC STYLES FOR NEWS DETAILS PAGE */

        /* Breadcrumb */
        .breadcrumb {
            background: white;
            padding: 15px 0;
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: var(--accent-color);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb span {
            color: #666;
        }

        /* Article Header */
        .article-header {
            background: white;
            padding: 40px 0 30px;
        }

        .article-title {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .article-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            color: #666;
            font-size: 0.95rem;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--accent-color);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .meta-item i {
            color: var(--accent-color);
        }

        .category-badge {
            background: var(--accent-color);
            color: var(--primary-color);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* Article Content */
        .article-content {
            padding: 40px 0;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }

        .main-content {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .featured-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        /* Image gallery styles */
        .article-gallery {
            margin-bottom: 30px;
        }

        .gallery-main {
            position: relative;
            background: #f0f0f0;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .gallery-main img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            display: block;
            transition: opacity 0.3s ease;
        }

        .gallery-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(37, 211, 102, 0.7);
            color: var(--accent-color);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            z-index: 10;
        }

        .gallery-arrow:hover {
            background: var(--primary-color);
            transform: translateY(-50%) scale(1.1);
        }

        .gallery-arrow.prev {
            left: 15px;
        }

        .gallery-arrow.next {
            right: 15px;
        }

        .gallery-counter {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: rgba(37, 211, 102, 0.8);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .gallery-thumbnails {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 10px;
        }

        .gallery-thumb {
            cursor: pointer;
            border: 3px solid transparent;
            border-radius: 6px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .gallery-thumb:hover {
            border-color: var(--accent-color);
            transform: scale(1.05);
        }

        .gallery-thumb.active {
            border-color: var(--accent-color);
        }

        .gallery-thumb img {
            width: 100%;
            height: 80px;
            object-fit: cover;
        }

        .article-body {
            font-size: 1.1rem;
            line-height: 1.9;
            color: #444;
            white-space: pre-line;
        }

        .article-body p {
            margin-bottom: 20px;
        }

        /* Sidebar */
        .sidebar {
            position: sticky;
            top: 100px;
        }

        .sidebar-widget {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .widget-title {
            font-size: 1.3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-color);
        }

        .related-article {
            display: block;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s;
        }

        .related-article:last-child {
            border-bottom: none;
        }

        .related-article:hover {
            background: #f9f9f9;
            padding-left: 10px;
        }

        .related-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .related-date {
            font-size: 0.85rem;
            color: #999;
        }

        .share-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .share-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            color: white;
        }

        .share-facebook {
            background: #3b5998;
        }

        .share-twitter {
            background: #1da1f2;
        }

        .share-whatsapp {
            background: #0066CC;
        }

        .share-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* Back to News */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 30px;
            transition: all 0.3s;
        }

        .back-link:hover {
            background: var(--primary-dark);
        }

        /* Footer */
        footer {
            background-color: var(--primary-dark);
            color: #ddd;
            padding: 30px 0;
            text-align: center;
            margin-top: 60px;
        }

        @media (max-width: 968px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }

            .article-title {
                font-size: 2rem;
            }

            .main-content {
                padding: 25px;
            }
        }

        @media (max-width: 768px) {
            .article-meta {
                flex-direction: column;
                gap: 10px;
            }

            .article-title {
                font-size: 1.6rem;
            }

            .article-body {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a> <span>/</span>
            <a href="index.php#news">News & Insights</a> <span>/</span>
            <span><?php echo htmlspecialchars($news['title']); ?></span>
        </div>
    </div>

    <!-- Article Header -->
    <section class="article-header">
        <div class="container">
            <h1 class="article-title"><?php echo htmlspecialchars($news['title']); ?></h1>

            <div class="article-meta">
                <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <span>By <?php echo htmlspecialchars($news['author'] ?: $news['author_name'] ?: 'SafeHaven Team'); ?></span>
                </div>

                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <span><?php echo formatDate($news['published_at'] ?: $news['created_at']); ?></span>
                </div>

                <div class="meta-item">
                    <i class="fas fa-eye"></i>
                    <span><?php echo number_format($news['views']); ?> views</span>
                </div>

                <span class="category-badge"><?php echo htmlspecialchars($news['category']); ?></span>
            </div>
        </div>
    </section>

    <!-- Article Content -->
    <section class="article-content">
        <div class="container">
            <div class="content-grid">
                <!-- Main Content -->
                <div class="main-content">
                    <?php if (count($news_images) > 0): ?>
                        <!-- Image Gallery Carousel -->
                        <div class="article-gallery">
                            <div class="gallery-main">
                                <img id="mainImage" src="uploads/news/<?php echo htmlspecialchars($news_images[0]['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($news['title']); ?>">

                                <?php if (count($news_images) > 1): ?>
                                    <button class="gallery-arrow prev" onclick="navigateGallery(-1)">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button class="gallery-arrow next" onclick="navigateGallery(1)">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                    <div class="gallery-counter">
                                        <span id="currentImageIndex">1</span> / <?php echo count($news_images); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (count($news_images) > 1): ?>
                                <div class="gallery-thumbnails">
                                    <?php foreach ($news_images as $index => $img): ?>
                                        <div class="gallery-thumb <?php echo $index == 0 ? 'active' : ''; ?>"
                                            onclick="goToImage(<?php echo $index; ?>)">
                                            <img src="uploads/news/<?php echo htmlspecialchars($img['image_path']); ?>"
                                                alt="<?php echo htmlspecialchars($news['title']); ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($news['image']): ?>
                        <!-- Fallback to legacy single image -->
                        <img src="uploads/news/<?php echo htmlspecialchars($news['image']); ?>"
                            alt="<?php echo htmlspecialchars($news['title']); ?>"
                            class="featured-image">
                    <?php endif; ?>

                    <div class="article-body">
                        <?php echo nl2br(htmlspecialchars($news['content'])); ?>
                    </div>

                    <a href="index.php#news" class="back-link">
                        <i class="fas fa-arrow-left"></i> Back to News
                    </a>
                </div>

                <!-- Sidebar -->
                <aside class="sidebar">
                    <!-- Share Widget -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title">Share This Article</h3>
                        <div class="share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"
                                target="_blank" class="share-btn share-facebook" title="Share on Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($news['title']); ?>"
                                target="_blank" class="share-btn share-twitter" title="Share on Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://wa.me/?text=<?php echo urlencode($news['title'] . ' - ' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"
                                target="_blank" class="share-btn share-whatsapp" title="Share on WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Related Articles -->
                    <?php if (count($related_articles) > 0): ?>
                        <div class="sidebar-widget">
                            <h3 class="widget-title">Related Articles</h3>
                            <?php foreach ($related_articles as $article): ?>
                                <a href="news-details.php?id=<?php echo $article['id']; ?>" class="related-article">
                                    <div class="related-title"><?php echo htmlspecialchars($article['title']); ?></div>
                                    <div class="related-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo formatDate($article['published_at'] ?: $article['created_at']); ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Categories Widget -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title">Categories</h3>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <?php
                            $cat_query = "SELECT DISTINCT category FROM news WHERE status = 'published' ORDER BY category";
                            $cat_stmt = $conn->query($cat_query);
                            $categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);

                            foreach ($categories as $cat):
                            ?>
                                <a href="index.php#news" style="color: var(--primary-color); text-decoration: none; padding: 8px 0; border-bottom: 1px solid #eee;">
                                    <i class="fas fa-folder" style="color: var(--accent-color);"></i>
                                    <?php echo htmlspecialchars($cat); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2026 SafeHaven. The Truth is Our Only Inventory.</p>
        </div>
    </footer>

    <script>
        <?php if (count($news_images) > 0): ?>
            // Gallery images array
            const galleryImages = [
                <?php foreach ($news_images as $index => $img): ?> 'uploads/news/<?php echo htmlspecialchars($img['image_path']); ?>'
                    <?php echo $index < count($news_images) - 1 ? ',' : ''; ?>
                <?php endforeach; ?>
            ];
            let currentImageIndex = 0;

            function navigateGallery(direction) {
                currentImageIndex += direction;

                // Loop around
                if (currentImageIndex < 0) {
                    currentImageIndex = galleryImages.length - 1;
                } else if (currentImageIndex >= galleryImages.length) {
                    currentImageIndex = 0;
                }

                updateGalleryImage();
            }

            function goToImage(index) {
                currentImageIndex = index;
                updateGalleryImage();
            }

            function updateGalleryImage() {
                // Update main image
                document.getElementById('mainImage').src = galleryImages[currentImageIndex];

                // Update counter
                const counterElement = document.getElementById('currentImageIndex');
                if (counterElement) {
                    counterElement.textContent = currentImageIndex + 1;
                }

                // Update active thumbnail
                const thumbs = document.querySelectorAll('.gallery-thumb');
                thumbs.forEach((thumb, index) => {
                    thumb.classList.toggle('active', index === currentImageIndex);
                });
            }

            // Auto-advance carousel every 5 seconds
            <?php if (count($news_images) > 1): ?>
                let autoAdvance = setInterval(() => navigateGallery(1), 5000);

                // Pause auto-advance on hover
                document.querySelector('.gallery-main')?.addEventListener('mouseenter', () => {
                    clearInterval(autoAdvance);
                });

                // Resume auto-advance on mouse leave
                document.querySelector('.gallery-main')?.addEventListener('mouseleave', () => {
                    autoAdvance = setInterval(() => navigateGallery(1), 5000);
                });
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>

</html>