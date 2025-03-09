<?php

class LoginSystem
{
    private static $instance = null;
    private const CSV_FILE = __DIR__.'/users.csv';
    private $users = [];

    private function __construct()
    {
        // Start the session if it hasn't already been started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Load users from CSV file
        $this->loadUsersFromCSV(self::CSV_FILE);
    }

    public static function getInstance(): LoginSystem
    {
        if (self::$instance === null) {
            self::$instance = new LoginSystem();
        }
        return self::$instance;
    }

    private function loadUsersFromCSV(string $csvFile): void
    {
        if (($handle = fopen($csvFile, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ",", '"', '\\');
            while (($row = fgetcsv($handle, 1000, ",", '"', '\\')) !== false) {
                $user = array_combine($header, $row);
                $this->users[$user['id']] = $user;
            }
            fclose($handle);
        }
    }

    private function checkCredentials(string $identifier, string $password): bool
    {
        foreach ($this->users as $user) {
            if (($identifier === $user['username'] || $identifier === $user['email']) && password_verify($password, $user['password']) && $user['active'] === 'true') {
                // Store user information in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['logged_in'] = true;
                return true;
            }
        }
        return false;
    }

    private function isFormSubmitted(): bool
    {
        return isset($_POST['identifier'], $_POST['password']);
    }

    public function process(): string
    {
        if ($this->isFormSubmitted()) {
            if ($this->checkCredentials($_POST['identifier'], $_POST['password'])) {
                $this->redirectAfterLogin();
                session_regenerate_id(true);
                return 'Login erfolgreich';
            }

            $_SESSION['login_error'] = 'Login fehlgeschlagen';
            header('Refresh: 0');
            exit;
        }

        return $this->renderForm() . $this->displayError();
    }

    private function setUserSession(string $identifier): void
    {
        $_SESSION['logged_in'] = true;
    }

    private function displayError(): string
    {
        return isset($_SESSION['login_error']) ? '<p>' . $_SESSION['login_error'] . '</p>' : '';
    }

    private function redirectAfterLogin(): void
    {
        header('Location: ' . LOGIN_TARGET);
        exit;
    }

    private function isLoggedIn(): bool
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public function renderForm(): string
    {
        $identifier = htmlspecialchars($_POST['identifier'] ?? '', ENT_QUOTES, 'UTF-8');
        $password = htmlspecialchars($_POST['password'] ?? '', ENT_QUOTES, 'UTF-8');

        return '
            <form method="post" class="max-w-md mx-auto p-6 bg-white rounded-lg shadow-md">
            <div class="mb-4">
                <label for="identifier" class="block text-gray-700 font-semibold mb-2">Username oder Email</label>
                <input type="text" name="identifier" placeholder="Username oder Email" value="' . $identifier . '" required class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
                <input type="password" name="password" placeholder="Password" value="' . $password . '" required class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            </div>
            <div class="flex items-center justify-between">
                <input type="submit" value="Login" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            </div>
            </form>
        ';
    }

    public function renderLogoutLink(): string
    {
        return '<a href="?logout">Logout</a>';
    }

    public function handleLogout(): void
    {
        if (isset($_GET['logout'])) {
            session_destroy();
            $this->redirectAfterLogout();
        }
    }

    private function redirectAfterLogout(): void
    {
        header('Location: ' . LOGOUT_TARGET);
        exit;
    }

    public function getUserData(string $userId): ?array
    {
        return $this->users[$userId] ?? null;
    }


    public function checkAccess(): void
    {
        if (!$this->isLoggedIn()) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Zugriff verweigert';
            exit;
        }
    }
}