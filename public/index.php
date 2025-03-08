<?php
const ROOT = __DIR__ . DIRECTORY_SEPARATOR . '..';
require_once ROOT . DIRECTORY_SEPARATOR . 'funky' . DIRECTORY_SEPARATOR . 'LoginSystem.php';

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) ?: '/';


// Create an instance of the LoginSystem using the Singleton pattern
$loginSystem = LoginSystem::getInstance();

// Handle logout if requested
$loginSystem->handleLogout();

if ($path === '/admin') {
    $loginSystem->checkAccess();
    echo $loginSystem->renderLogoutLink();
    echo "<h1>Welcome to the admin area!</h1>";
    echo "<a href='/admin/settings'>Settings</a>";
} else if ($path === '/admin/settings') {
    $loginSystem->checkAccess();
    // Get user data from session
    $userData = $loginSystem->getUserData($_SESSION['user_id']);

    // Display user data
    echo '<h1>Willkommen auf der geschützten Seite!</h1>';
    echo '<h2>Benutzerdaten</h2>';
    echo '<p>Username: ' . htmlspecialchars($userData['username']) . '</p>';
    echo '<p>Email: ' . htmlspecialchars($userData['email']) . '</p>';
    echo '<p>Account-Typ: ' . htmlspecialchars($userData['account_type']) . '</p>';
    echo '<p>Erstellt von: ' . htmlspecialchars($userData['created_by']) . '</p>';
    echo '<p>Erstellt am: ' . htmlspecialchars($userData['created_at']) . '</p>';
    echo '<p>Aktiv: ' . htmlspecialchars($userData['active']) . '</p>';
    echo $loginSystem->renderLogoutLink();
} else if ($path === '/login') {
    echo $loginSystem->process();
} else if ($path === '/logout') {
    echo "Du hast dich erfolgreich ausgeloggt!";
    echo "<a href='/login'>Zum Login</a>";
}

# echo $path;
# echo password_hash('demo123', PASSWORD_DEFAULT);
# echo uniqid('', true);