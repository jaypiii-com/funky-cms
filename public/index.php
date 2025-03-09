<?php
const APP_NAME = 'FUNKY CMS';
const APP_VERSION = '0.0.1';

const LOGIN_FORM = '/login';
const LOGIN_TARGET = '/dashbaord';
const LOGOUT_TARGET = '/logout';

const ROOT = __DIR__ . DIRECTORY_SEPARATOR . '..';
require_once ROOT . DIRECTORY_SEPARATOR . 'funky' . DIRECTORY_SEPARATOR . 'LoginSystem.php';
require_once ROOT . DIRECTORY_SEPARATOR . 'funky' . DIRECTORY_SEPARATOR . 'UserManager.php';

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) ?: '/';

// Create an instance of the LoginSystem using the Singleton pattern
$loginSystem = LoginSystem::getInstance();
// Handle logout if requested
$loginSystem->handleLogout();



if (strpos($path, ltrim(LOGIN_TARGET, '/'))) {
    $loginSystem->checkAccess();
    $userManager = new UserManager;
}

function renderNavigation($currentUri)
{
    // Define navigation items for easier future extension
    $navItems = [
        'Users' => LOGIN_TARGET . '/users',
        // Add more items here if needed
    ];

    echo "<nav class='flex flex-col items-center bg-neutral-100 w-65 min-w-64 max-w-64'>";
    foreach ($navItems as $name => $url) {
        // Determine active link by checking if the URL exists in the current URI
        $activeClass = (strpos($currentUri, $url) !== false)
            ? "bg-gray-700 text-white"
            : "text-gray-400";
        echo "<a href='{$url}' class='block w-full py-2 pl-4 hover:bg-gray-600 {$activeClass}'>{$name}</a>";
    }
    echo "</nav>";
}

function renderLayout(callable $contentRenderer)
{
    // Start output buffering to capture all output and render at once
    ob_start();
?>
    <!DOCTYPE html>
    <html lang="de">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= APP_NAME ?></title>
        <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    </head>

    <body>
        <header class="bg-neutral-200 text-neutral-600 p-4 flex justify-between items-center shadow border-b border-neutral-300">
            <div class='container mx-auto flex justify-between space-x-4'>
                <div>
                    <h1 class="font-semibold relative z-10">
                        <span class="z-10"><a href="<?= LOGIN_TARGET; ?>"><?= APP_NAME ?></span>
                        <span class="absolute h-[100%] w-[50%] left-[25%] top-0 border-b-2 border-neutral-400 z-0"></span>
                        </a>
                    </h1>
                </div>
                <nav>
                    <a href='/admin/?logout' class='px-4 py-2 outline outline-neutral-300 shadow text-xs bg-neutral-200 rounded hover:bg-white/10'>Logout</a>
                </nav>
            </div>
        </header>
        <div class="bg-neutral-100">
            <div class="min-h-screen flex container mx-auto">
                <?php renderNavigation($_SERVER["REQUEST_URI"]); ?>
                <main class="flex-grow flex flex-col bg-white">
                    <div class="flex-grow">
                        <?php $contentRenderer(); ?>
                    </div>
                    <footer class=" border-t text-xs">
                        Footer Content
                    </footer>
                </main>
            </div>
        </div>
    </body>

    </html>
    <?php
    // Flush the buffer
    echo ob_get_clean();
}

if ($path === LOGIN_TARGET) {

    renderLayout(function () {
    ?>

        <div class="container mx-auto p-4">
            <h1 class="text-3xl font-semibold">Dashboard</h1>
            <p>Hello USERNAME, willkommen in deinem Dashboard</p>
        </div>

    <?php
    });
}
if ($path === LOGIN_TARGET . '/my-data') {
    renderNavigation($_SERVER["REQUEST_URI"]);
    echo $userManager->renderInfo($_SESSION['user_id']);
}
if ($path === LOGIN_TARGET . '/add-user') {
    echo $userManager->renderUserForm();
}
if ($path === LOGIN_TARGET . '/user') {
    echo $userManager->renderInfo($_GET['user_id'] ?? null);
}

if ($path === LOGIN_TARGET . '/user/edit') {
    renderLayout(function () use ($userManager) {

        $userdata =  $userManager->getUserData($_GET['user_id']);

        #var_dump($userdata);
    ?>

        <form action="<?= LOGIN_TARGET . '/user/edit' ?>" method="post" enctype="multipart/form-data" class="max-w-xl mx-auto p-6 bg-white shadow rounded border border-gray-200">
            <input type="hidden" name="id" value="<?= htmlspecialchars($userdata['id']) ?>">
            <!-- Preview block with default image and made clickable -->

            <div class="flex gap-4">
                <div class="mb-4">
                    <div id="preview" class="size-36 border border-gray-300 rounded overflow-hidden cursor-pointer">
                        <img src="/placeholder/funcy.png" class="object-cover w-full h-full" alt="Default image">
                    </div>
                </div>

                <div class="mb-4 hidden">
                    <label for="image" class="block text-gray-800 font-medium mb-2">Profile Image</label>
                    <input type="file" id="image" name="image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-gray-300" style="display: none;">
                </div>

                <div class="flex flex-col gap-2 w-full">
                    <div>
                        <label for="username" class="block text-neutral-400 font-medium text-xs pb-2">Username</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($userdata['username']) ?>" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-gray-300" required>
                    </div>
                    <div>
                        <label for="email" class="block text-neutral-400 font-medium text-xs pb-2">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($userdata['email']) ?>" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-gray-300" required>
                    </div>
                </div>

            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-800 font-medium mb-2">Password</label>
                <input type="password" id="password" name="password" placeholder="Leave blank if unchanged" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-gray-300">
            </div>
            <div class="mb-4">
                <label for="account_type" class="block text-gray-800 font-medium mb-2">Account Type</label>
                <select id="account_type" name="account_type" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-gray-300">
                    <option value="admin" <?= $userdata['account_type'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="user" <?= $userdata['account_type'] === 'user' ? 'selected' : '' ?>>User</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="active" class="block text-gray-800 font-medium mb-2">Active</label>
                <select id="active" name="active" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-gray-300">
                    <option value="true" <?= $userdata['active'] === 'true' ? 'selected' : '' ?>>True</option>
                    <option value="false" <?= $userdata['active'] === 'false' ? 'selected' : '' ?>>False</option>
                </select>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700 focus:outline-none">Update User</button>
                <a href="<?= LOGIN_TARGET . '/users' ?>" class="text-gray-800 hover:underline">Cancel</a>
            </div>
        </form>
        <script>
            // Default image HTML
            const defaultImageHTML = '<img src="/placeholder/funcy.png" class="object-cover w-full h-full" alt="Default image">';
            
            // When the preview square is clicked, trigger the file input
            document.getElementById('preview').addEventListener('click', function() {
                document.getElementById('image').click();
            });

            // Update preview when a file is selected
            document.getElementById('image').addEventListener('change', function(event) {
                const file = event.target.files[0];
                // If file selection is cancelled, keep the last changed image
                if (!file) {
                    return;
                }
                const preview = document.getElementById('preview');
                preview.innerHTML = '';
                const img = document.createElement('img');
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                img.src = URL.createObjectURL(file);
                preview.appendChild(img);
            });
        </script>

<?php

    });
}


if ($path === LOGIN_TARGET . '/users') {
    renderLayout(function () use ($userManager) {
        echo $userManager->renderUserList();
    });
}



if ($path === LOGIN_FORM) {
    echo $loginSystem->process();
}
if ($path === '/logout') {
    echo "Du hast dich erfolgreich ausgeloggt!";
    echo "<a href='/login'>Zum Login</a>";
}




# echo $path;
# echo password_hash('demo123', PASSWORD_DEFAULT);
# echo uniqid('', true);