<?php
require_once 'config/db.php';

echo "<h1>Test Breaking News Data</h1>";

// Test 1: Breaking News
$breakingNews = $pdo->query("
    SELECT id, title, slug 
    FROM news 
    WHERE status = 'published' AND category = 'urgent'
    ORDER BY published_at DESC 
    LIMIT 5
")->fetchAll();

echo "<h3>Breaking News (" . count($breakingNews) . " items):</h3>";
if (empty($breakingNews)) {
    echo "<p style='color:red;'>❌ KHÔNG CÓ BREAKING NEWS!</p>";
} else {
    foreach ($breakingNews as $news) {
        echo "<p>✅ " . $news['title'] . "</p>";
    }
}

// Test 2: Hero News
$heroNews = $pdo->query("
    SELECT id, title 
    FROM news 
    WHERE status = 'published'
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();

echo "<h3>Hero News (" . count($heroNews) . " items):</h3>";
if (empty($heroNews)) {
    echo "<p style='color:red;'>❌ KHÔNG CÓ HERO NEWS!</p>";
} else {
    foreach ($heroNews as $news) {
        echo "<p>✅ " . $news['title'] . "</p>";
    }
}

// Test 3: Swiper Check
echo "<h3>Swiper Library Check:</h3>";
echo "<script>
if (typeof Swiper !== 'undefined') {
    document.write('<p style=\"color:green;\">✅ Swiper đã load</p>');
} else {
    document.write('<p style=\"color:red;\">❌ Swiper CHƯA load - THIẾU LIBRARY!</p>');
}
</script>";
?>