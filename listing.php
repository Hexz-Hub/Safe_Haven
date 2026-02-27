<?php
session_start();
include_once 'config/database.php';

$db = new \Database();
$conn = $db->getConnection();

// Build query with filters
$where_conditions = ["status = 'available'"];
$params = [];

// Search keyword
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $where_conditions[] = "(title LIKE :search OR location LIKE :search2 OR description LIKE :search3)";
    $params[':search'] = $search;
    $params[':search2'] = $search;
    $params[':search3'] = $search;
}

// Property type filter
if (isset($_GET['type']) && !empty($_GET['type'])) {
    $where_conditions[] = "property_type = :type";
    $params[':type'] = $_GET['type'];
}

// Location filter
if (isset($_GET['location']) && !empty($_GET['location'])) {
    $location = '%' . $_GET['location'] . '%';
    $where_conditions[] = "location LIKE :location";
    $params[':location'] = $location;
}

// Price range
if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $where_conditions[] = "price >= :min_price";
    $params[':min_price'] = $_GET['min_price'];
}
if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $where_conditions[] = "price <= :max_price";
    $params[':max_price'] = $_GET['max_price'];
}

// Bedrooms
if (isset($_GET['bedrooms']) && !empty($_GET['bedrooms'])) {
    $where_conditions[] = "bedrooms >= :bedrooms";
    $params[':bedrooms'] = $_GET['bedrooms'];
}

// Bathrooms
if (isset($_GET['bathrooms']) && !empty($_GET['bathrooms'])) {
    $where_conditions[] = "bathrooms >= :bathrooms";
    $params[':bathrooms'] = $_GET['bathrooms'];
}

// Build final query
$where_clause = implode(' AND ', $where_conditions);
$query = "SELECT * FROM properties WHERE $where_clause ORDER BY featured DESC, created_at DESC";

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique property types and locations for filters
$types_query = "SELECT DISTINCT property_type FROM properties ORDER BY property_type";
$types_stmt = $conn->query($types_query);
$property_types = $types_stmt->fetchAll(PDO::FETCH_COLUMN);

$locations_query = "SELECT DISTINCT location FROM properties ORDER BY location";
$locations_stmt = $conn->query($locations_query);
$locations = $locations_stmt->fetchAll(PDO::FETCH_COLUMN);

function formatPrice($price)
{
    if ($price >= 1000000000) {
        return '₦' . number_format($price / 1000000000, 2) . 'B';
    } elseif ($price >= 1000000) {
        return '₦' . number_format($price / 1000000, 0) . 'M';
    } else {
        return '₦' . number_format($price, 0);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Listings | SafeHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/header-styles.php'; ?>
    <style>
        /* PAGE-SPECIFIC STYLES FOR LISTING PAGE */

        /* Hero */
        .page-hero {
            background: linear-gradient(rgba(75, 44, 107, 0.6), rgba(45, 27, 71, 0.55)),
                url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .page-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .page-hero p {
            color: var(--accent-color);
            font-style: italic;
        }

        /* Search Bar */
        .search-bar {
            background: white;
            padding: 30px;
            margin: -30px auto 40px;
            max-width: 1100px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        .search-input,
        .search-select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .search-input:focus,
        .search-select:focus {
            outline: none;
            border-color: var(--accent-color);
        }

        .search-btn {
            padding: 12px 30px;
            background: var(--accent-color);
            color: var(--primary-color);
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 6px;
        }

        .search-btn:hover {
            background: var(--gold-light);
        }

        /* Main Content */
        .listings-section {
            padding: 20px 0 60px;
        }

        .listings-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
        }

        /* Filter Sidebar */
        .filter-sidebar {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            height: fit-content;
            position: sticky;
            top: 80px;
        }

        .filter-title {
            font-size: 1.3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-color);
        }

        .filter-group {
            margin-bottom: 25px;
        }

        .filter-group h4 {
            font-size: 1rem;
            color: var(--text-dark);
            margin-bottom: 12px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .filter-group input[type="text"],
        .filter-group input[type="number"],
        .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: var(--accent-color);
        }

        .price-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .filter-btn {
            width: 100%;
            padding: 10px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
        }

        .filter-btn:hover {
            background: var(--primary-dark);
        }

        .reset-btn {
            background: #999;
            margin-top: 10px;
        }

        .reset-btn:hover {
            background: #777;
        }

        /* Results Section */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .results-count {
            font-size: 1.1rem;
            color: #666;
        }

        .results-count strong {
            color: var(--accent-color);
        }

        /* Property Grid */
        .property-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .property-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            position: relative;
        }

        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .property-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            position: relative;
        }

        .image-wrapper {
            position: relative;
            overflow: hidden;
        }

        .badge {
            position: absolute;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-featured {
            top: 10px;
            left: 10px;
            background: var(--accent-color);
            color: var(--primary-color);
        }

        .badge-verified {
            top: 10px;
            right: 10px;
            background: var(--success);
            color: white;
        }

        .property-body {
            padding: 20px;
        }

        .property-price {
            font-size: 1.4rem;
            color: var(--accent-color);
            font-weight: bold;
            margin-bottom: 8px;
        }

        .property-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .property-location {
            color: #777;
            font-size: 0.9rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .property-features {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding: 12px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
            color: #666;
        }

        .property-features span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .view-btn {
            display: block;
            text-align: center;
            background: var(--primary-color);
            color: white;
            padding: 10px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .view-btn:hover {
            background: var(--accent-color);
            color: var(--primary-color);
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .no-results i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .no-results h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        /* Footer */
        footer {
            background: var(--primary-dark);
            color: #ddd;
            padding: 30px 0;
            text-align: center;
            margin-top: 60px;
        }

        @media (max-width: 968px) {
            .listings-layout {
                grid-template-columns: 1fr;
            }

            .filter-sidebar {
                position: static;
            }

            .search-form {
                grid-template-columns: 1fr;
            }

            .search-btn {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .property-grid {
                grid-template-columns: 1fr;
            }

            .page-hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero -->
    <section class="page-hero">
        <div class="container">
            <h1>Find Your Perfect Property</h1>
            <p>Browse our verified inventory of premium properties</p>
        </div>
    </section>

    <!-- Search Bar -->
    <div class="container">
        <div class="search-bar">
            <form class="search-form" method="GET" action="listing.php">
                <input type="text" name="search" class="search-input"
                    placeholder="Search by keyword..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">

                <select name="type" class="search-select">
                    <option value="">All Property Types</option>
                    <?php foreach ($property_types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>"
                            <?php echo (isset($_GET['type']) && $_GET['type'] === $type) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="location" class="search-select">
                    <option value="">All Locations</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?php echo htmlspecialchars($loc); ?>"
                            <?php echo (isset($_GET['location']) && $_GET['location'] === $loc) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($loc); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>
    </div>

    <!-- Listings Section -->
    <section class="listings-section">
        <div class="container">
            <div class="listings-layout">
                <!-- Filter Sidebar -->
                <aside class="filter-sidebar">
                    <h3 class="filter-title">Advanced Filters</h3>

                    <form method="GET" action="listing.php">
                        <div class="filter-group">
                            <h4>Price Range</h4>
                            <div class="price-inputs">
                                <input type="number" name="min_price" placeholder="Min (₦)"
                                    value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
                                <input type="number" name="max_price" placeholder="Max (₦)"
                                    value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
                            </div>
                        </div>

                        <div class="filter-group">
                            <h4>Bedrooms</h4>
                            <select name="bedrooms">
                                <option value="">Any</option>
                                <option value="1" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] == '1') ? 'selected' : ''; ?>>1+</option>
                                <option value="2" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] == '2') ? 'selected' : ''; ?>>2+</option>
                                <option value="3" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] == '3') ? 'selected' : ''; ?>>3+</option>
                                <option value="4" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] == '4') ? 'selected' : ''; ?>>4+</option>
                                <option value="5" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] == '5') ? 'selected' : ''; ?>>5+</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <h4>Bathrooms</h4>
                            <select name="bathrooms">
                                <option value="">Any</option>
                                <option value="1" <?php echo (isset($_GET['bathrooms']) && $_GET['bathrooms'] == '1') ? 'selected' : ''; ?>>1+</option>
                                <option value="2" <?php echo (isset($_GET['bathrooms']) && $_GET['bathrooms'] == '2') ? 'selected' : ''; ?>>2+</option>
                                <option value="3" <?php echo (isset($_GET['bathrooms']) && $_GET['bathrooms'] == '3') ? 'selected' : ''; ?>>3+</option>
                                <option value="4" <?php echo (isset($_GET['bathrooms']) && $_GET['bathrooms'] == '4') ? 'selected' : ''; ?>>4+</option>
                            </select>
                        </div>

                        <!-- Hidden fields to preserve search parameters -->
                        <?php if (isset($_GET['search'])): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                        <?php endif; ?>
                        <?php if (isset($_GET['type'])): ?>
                            <input type="hidden" name="type" value="<?php echo htmlspecialchars($_GET['type']); ?>">
                        <?php endif; ?>
                        <?php if (isset($_GET['location'])): ?>
                            <input type="hidden" name="location" value="<?php echo htmlspecialchars($_GET['location']); ?>">
                        <?php endif; ?>

                        <button type="submit" class="filter-btn">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="listing.php" class="filter-btn reset-btn" style="display: block; text-align: center; text-decoration: none;">
                            <i class="fas fa-redo"></i> Reset Filters
                        </a>
                    </form>
                </aside>

                <!-- Results -->
                <div>
                    <div class="results-header">
                        <div class="results-count">
                            Found <strong><?php echo count($properties); ?></strong> properties
                        </div>
                    </div>

                    <?php if (count($properties) > 0): ?>
                        <div class="property-grid">
                            <?php foreach ($properties as $property): ?>
                                <div class="property-card">
                                    <div class="image-wrapper">
                                        <?php if ($property['main_image']): ?>
                                            <img src="uploads/properties/<?php echo htmlspecialchars($property['main_image']); ?>"
                                                alt="<?php echo htmlspecialchars($property['title']); ?>"
                                                class="property-image">
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/300x220?text=No+Image"
                                                alt="No image" class="property-image">
                                        <?php endif; ?>

                                        <?php if ($property['featured']): ?>
                                            <span class="badge badge-featured">
                                                <i class="fas fa-star"></i> Featured
                                            </span>
                                        <?php endif; ?>

                                        <?php if ($property['verification_status'] === 'verified'): ?>
                                            <span class="badge badge-verified">
                                                <i class="fas fa-shield-alt"></i> Verified
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="property-body">
                                        <div class="property-price"><?php echo formatPrice($property['price']); ?></div>
                                        <div class="property-title"><?php echo htmlspecialchars($property['title']); ?></div>
                                        <div class="property-location">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($property['location']); ?>
                                        </div>

                                        <?php if ($property['bedrooms'] > 0 || $property['bathrooms'] > 0 || $property['area_sqm'] > 0): ?>
                                            <div class="property-features">
                                                <?php if ($property['bedrooms'] > 0): ?>
                                                    <span><i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> Bed</span>
                                                <?php endif; ?>
                                                <?php if ($property['bathrooms'] > 0): ?>
                                                    <span><i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?> Bath</span>
                                                <?php endif; ?>
                                                <?php if ($property['area_sqm'] > 0): ?>
                                                    <span><i class="fas fa-ruler-combined"></i> <?php echo number_format($property['area_sqm']); ?>m²</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <a href="<?php echo isset($_SESSION['user_id']) ? 'property-details.php?id=' . $property['id'] : 'user-login.php'; ?>" class="view-btn">
                                            View Details <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <h3>No Properties Found</h3>
                            <p>Try adjusting your search filters or <a href="listing.php" style="color: var(--accent-color);">browse all properties</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2026 SafeHaven. The Truth is Our Only Inventory.</p>
        </div>
    </footer>

    <!-- WhatsApp Float -->
    <a href="https://wa.me/2348140097917" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i> Chat on WhatsApp
    </a>
</body>

</html>