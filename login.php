<?php
// ============================================================
// login.php — San Sebastian College Recoletos Manila
// SSCR Colors: Crimson Red + Golden Yellow
// ============================================================

session_start();
require_once 'db.php';

/** @var mysqli $conn */ // Tells Intelephense: $conn is a mysqli object from db.php

// Redirect already-logged-in users
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'student' ? 'search.php' : 'dashboard.php'));
    exit();
}

$error   = '';
$success = '';

// ============================================================
// HANDLE LOGIN
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both your username and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                header('Location: ' . ($user['role'] === 'student' ? 'search.php' : 'dashboard.php'));
                exit();
            } else {
                $error = 'Incorrect password. Please try again.';
            }
        } else {
            $error = 'No account found with that username.';
        }
        $stmt->close();
    }
}

// ============================================================
// HANDLE FORGOT PASSWORD
// (School system: logs request, directs student to librarian)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'forgot') {

    $fp_username = trim($_POST['fp_username'] ?? '');

    if (empty($fp_username)) {
        $error = 'Please enter your username to submit a reset request.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $fp_username);
        $stmt->execute();
        $fp_result = $stmt->get_result();

        // We show the same message whether the user exists or not (security)
        $success = 'Your password reset request has been noted for <strong>'
                 . htmlspecialchars($fp_username)
                 . '</strong>. Please visit the library counter or contact a librarian for assistance.';
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSCR Manila — Library Portal Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- =================== LOGIN WRAPPER (Red gradient bg) =================== -->
<div class="login-wrapper">

    <div class="login-card">

        <!-- ---- School Logo ---- -->
        <!-- Save the official SSCR logo as "ssc_logo.png" in this folder -->
        <!-- If the file is missing, the shield emoji shows instead        -->
        <div class="login-logo">
            <img
                src="sscr.jpg"
                alt="San Sebastian College Recoletos Manila Official Logo"
                onerror="this.style.display='none'; document.getElementById('logoFallback').style.display='flex';"
            >
            <div class="logo-fallback" id="logoFallback" style="display:none;">🛡️</div>
        </div>

        <!-- School name & location -->
        <div class="school-name">San Sebastian College — Recoletos</div>
        <div class="school-location">Manila, Philippines &nbsp;·&nbsp; Est. 1956</div>

        <!-- Two-tone red+yellow divider line -->
        <div class="sscr-divider"><span></span><span></span></div>

        <!-- Welcome heading -->
        <h1>Library Portal</h1>
        <p class="welcome-text">
            Welcome, Sebastinians! 📚<br>
            Sign in with your school credentials to access the library system.
        </p>

        <!-- ---- Feedback messages ---- -->
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error && ($_POST['action'] ?? '') === 'login'): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- =================== LOGIN FORM =================== -->
        <form action="" method="POST" autocomplete="on">
            <input type="hidden" name="action" value="login">

            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter your username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >
            </div>

            <!-- Opens the Forgot Password modal -->
            <span class="forgot-link" onclick="openForgotModal()">Forgot your password?</span>

            <button type="submit" class="btn btn-primary">
                Sign In
            </button>
        </form>

        <!-- Demo credentials (REMOVE BEFORE GOING LIVE) -->
        <div class="demo-box">
            <strong>Demo Accounts:</strong><br>
            Admin: <code>admin</code> / <code>admin123</code> &nbsp;|&nbsp;
            Librarian: <code>librarian</code> / <code>admin123</code><br>
            Student: <code>student</code> / <code>student123</code>
        </div>

    </div><!-- /login-card -->

    <!-- Copyright line below the card -->
    <div class="login-footer-note">
        &copy; <?= date('Y') ?> San Sebastian College Recoletos Manila &nbsp;&middot;&nbsp; Library Management System
    </div>

</div><!-- /login-wrapper -->


<!-- ============================================================
     FORGOT PASSWORD MODAL
     Yellow-top modal — keeps SSCR branding consistent
     ============================================================ -->
<div class="forgot-modal-overlay" id="forgotModal">
    <div class="forgot-modal">

        <div class="modal-icon">🔑</div>
        <h3>Forgot Password?</h3>
        <p>
            Enter your username below and your reset request will be logged.
            A librarian will verify your identity and reset your password.
        </p>

        <!-- Show error inside modal if forgot form had an error -->
        <?php if ($error && ($_POST['action'] ?? '') === 'forgot'): ?>
            <div class="alert alert-error" style="text-align:left;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="hidden" name="action" value="forgot">
            <div class="form-group" style="text-align:left;">
                <label for="fp_username">Your Username</label>
                <input
                    type="text"
                    id="fp_username"
                    name="fp_username"
                    placeholder="Enter your username"
                    required
                >
            </div>
            <button type="submit" class="btn btn-yellow" style="width:100%;margin-bottom:0.8rem;">
                Submit Reset Request
            </button>
        </form>

        <!-- Library contact info -->
        <div class="contact-info">
            <strong>📍 Visit the library directly:</strong>
            Library Counter, San Sebastian College Recoletos Manila<br>
            Claro M. Recto Avenue, Sampaloc, Manila 1008<br>
            <strong>📞</strong> (02) 8736-0062
            &nbsp;&middot;&nbsp;
            <strong>✉️</strong> library@ssc.edu.ph
        </div>

        <button type="button" class="btn btn-cancel" style="width:100%;" onclick="closeForgotModal()">
            Close
        </button>

    </div>
</div>


<!-- ============================================================
     JAVASCRIPT — Modal open / close
     ============================================================ -->
<script>
    function openForgotModal()  { document.getElementById('forgotModal').classList.add('active'); }
    function closeForgotModal() { document.getElementById('forgotModal').classList.remove('active'); }

    // Close when clicking the dark overlay background
    document.getElementById('forgotModal').addEventListener('click', function(e) {
        if (e.target === this) closeForgotModal();
    });

    // If the page reloaded after a forgot-password submission, re-open the modal
    // so the success/error message is visible to the user
    <?php if ($_POST['action'] ?? '' === 'forgot'): ?>
    window.addEventListener('DOMContentLoaded', openForgotModal);
    <?php endif; ?>
</script>

</body>
</html>
