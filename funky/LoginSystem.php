<?php

class LoginSystem
{
    private static $instance = null;
    private const CSV_FILE = __DIR__.'/users.csv';
    private const LOGIN_TARGET = '/admin'; // Redirect target after successful login
    private const LOGOUT_TARGET = '/logout'; // Redirect target after logout
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
                return 'Login erfolgreich';
            }

            $_SESSION['login_error'] = 'Login fehlgeschlagen';
            header('Refresh: 0');
            exit;
        }

        if ($this->isLoggedIn()) {
            $userData = $this->getUserData($_SESSION['user_id']);
            return $this->renderLogoutLink() . $this->displayUserData($userData);
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
        header('Location: ' . self::LOGIN_TARGET);
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
            <form method="post">
                <label for="identifier">Username oder Email</label>
                <input type="text" name="identifier" placeholder="Username oder Email" value="' . $identifier . '" required>
                <label for="password">Password</label>
                <input type="password" name="password" placeholder="Password" value="' . $password . '" required>
                <input type="submit" value="Login">
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
        header('Location: ' . self::LOGOUT_TARGET);
        exit;
    }

    public function getUserData(string $userId): ?array
    {
        return $this->users[$userId] ?? null;
    }

    private function displayUserData(array $userData): string
    {
        if ($userData) {
            return '
                <div>
                    <h2>Benutzerdaten</h2>
                    <p>Username: ' . htmlspecialchars($userData['username']) . '</p>
                    <p>Email: ' . htmlspecialchars($userData['email']) . '</p>
                    <p>Account-Typ: ' . htmlspecialchars($userData['account_type']) . '</p>
                    <p>Erstellt von: ' . htmlspecialchars($userData['created_by']) . '</p>
                    <p>Erstellt am: ' . htmlspecialchars($userData['created_at']) . '</p>
                    <p>Aktiv: ' . htmlspecialchars($userData['active']) . '</p>
                </div>
            ';
        }
        return '<p>Keine Benutzerdaten gefunden.</p>';
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