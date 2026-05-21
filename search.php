<?php
// ============================================================
// search.php — OPAC for Students
// SSCR Manila — Crimson Red + Golden Yellow Theme
// ============================================================

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }
if ($_SESSION['role'] !== 'student') { header('Location: dashboard.php'); exit(); }

// ============================================================
// FETCH BOOKS — search + genre filter
// ============================================================
$search = trim($_GET['search'] ?? '');
$genre  = trim($_GET['genre']  ?? '');

$sql    = "SELECT * FROM books WHERE 1=1";
$params = [];
$types  = '';

if (!empty($search)) {
    $sql    .= " AND (title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
    $like    = '%' . $search . '%';
    $params  = array_merge($params, [$like, $like, $like]);
    $types  .= 'sss';
}
if (!empty($genre)) {
    $sql    .= " AND genre = ?";
    $params  = array_merge($params, [$genre]);
    $types  .= 's';
}
$sql .= " ORDER BY title ASC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $books_result = $stmt->get_result();
} else {
    $books_result = $conn->query($sql);
}

// All distinct genres for the dropdown
$genres_result = $conn->query("SELECT DISTINCT genre FROM books WHERE genre != '' ORDER BY genre ASC");
$genres        = $genres_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSCR Manila — Library Catalog (OPAC)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ==================== NAVBAR ==================== -->
<nav class="navbar">
    <div class="brand">
        🛡️ <span class="brand-yellow">SSCR</span>&nbsp;Manila — Library Catalog
    </div>
    <div class="nav-right">
        <span>👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
        <span class="nav-badge">Student</span>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<!-- ==================== OPAC HERO (Red gradient + yellow border) ==================== -->
<div class="opac-hero">
    <h2>📖 San Sebastian College Library</h2>
    <p>Search our collection of books. Browse by title, author, ISBN, or genre.</p>

    <!-- Search form — GET so URL is shareable/bookmarkable -->
    <form action="" method="GET">
        <div class="search-bar">
            <input
                type="text"
                name="search"
                placeholder="Search by title, author, or ISBN…"
                value="<?= htmlspecialchars($search) ?>"
                autofocus
            >
            <select name="genre">
                <option value="">All Genres</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= htmlspecialchars($g['genre']) ?>"
                        <?= $genre === $g['genre'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g['genre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">🔍 Search</button>
        </div>
    </form>
</div>

<!-- ==================== RESULTS ==================== -->
<main class="main-content">

    <!-- Results count + clear link -->
    <div class="page-header">
        <h2 style="font-size:1.05rem;color:var(--muted);font-weight:500;">
            <?php if ($search || $genre): ?>
                <?= $books_result->num_rows ?> result(s)
                <?= $search ? ' for "<em>' . htmlspecialchars($search) . '</em>"' : '' ?>
                <?= $genre  ? ' in <em>' . htmlspecialchars($genre) . '</em>' : '' ?>
            <?php else: ?>
                All Books (<?= $books_result->num_rows ?>)
            <?php endif; ?>
        </h2>
        <?php if ($search || $genre): ?>
            <a href="search.php" class="btn btn-cancel btn-small">✕ Clear Search</a>
        <?php endif; ?>
    </div>

    <!-- =================== BOOK GRID =================== -->
    <?php if ($books_result->num_rows === 0): ?>
        <div class="no-results">
            <span class="no-results-icon">📭</span>
            <p style="font-size:1.05rem;margin:0.4rem 0;">No books found matching your search.</p>
            <p><a href="search.php">Browse all books</a></p>
        </div>

    <?php else: ?>
        <div class="book-grid">
            <?php while ($book = $books_result->fetch_assoc()):
                $avail = intval($book['copies_available']);
                $total = intval($book['copies_total']);
            ?>
            <div class="book-card">

                <!-- Title + Author -->
                <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                <div class="book-author">by <?= htmlspecialchars($book['author']) ?></div>

                <!-- Metadata -->
                <?php if ($book['genre']): ?>
                    <div class="book-meta">🏷️ <span><?= htmlspecialchars($book['genre']) ?></span></div>
                <?php endif; ?>
                <?php if ($book['year_published']): ?>
                    <div class="book-meta">📅 <span><?= $book['year_published'] ?></span></div>
                <?php endif; ?>
                <?php if ($book['isbn']): ?>
                    <div class="book-meta">🔢 <span><?= htmlspecialchars($book['isbn']) ?></span></div>
                <?php endif; ?>

                <!-- Availability badge -->
                <div style="margin-top:0.75rem;">
                    <?php if ($avail === 0): ?>
                        <span class="badge badge-none">❌ Not Available</span>
                    <?php elseif ($avail === 1): ?>
                        <span class="badge badge-low">⚠️ Last Copy</span>
                    <?php else: ?>
                        <span class="badge badge-available">✅ Available (<?= $avail ?>)</span>
                    <?php endif; ?>
                </div>

            </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <!-- Info note for students -->
    <div class="alert alert-info mt-2" style="font-size:0.87rem;">
        💡 <strong>To borrow a book</strong>, visit the library counter with your school ID and show this page to the librarian.
    </div>

</main>

</body>
</html>
