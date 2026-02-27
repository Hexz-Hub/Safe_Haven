<?php
// Compatibility route for legacy /news.php URLs
// Redirect to the actual news details page while preserving query parameters.

$queryString = $_SERVER['QUERY_STRING'] ?? '';
$target = 'news-details.php';

if ($queryString !== '') {
    $target .= '?' . $queryString;
}

header('Location: ' . $target, true, 301);
exit();

