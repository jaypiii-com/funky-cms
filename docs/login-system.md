# Login System Dokumentation 

## Übersicht
Die `LoginSystem`-Klasse ist darauf ausgelegt, die Benutzer-Authentifizierung in einer PHP-Anwendung zu verwalten. Sie unterstützt Funktionen wie Benutzer-Login, Logout, Sitzungsverwaltung und das Abrufen von Benutzerdaten aus einer CSV-Datei. Die CSV-Datei enthält Benutzerdetails wie Benutzername, E-Mail, gehashtes Passwort, Kontotyp und Status.

## Funktionen
- Benutzer-Login und Logout
- Sitzungsverwaltung
- Abrufen von Benutzerdaten
- CSV-basierte Speicherung von Benutzerdaten
- Zugriffskontrolle für geschützte Seiten

## CSV-Dateistruktur
Die CSV-Datei (`users.csv`) sollte die folgende Struktur haben:

```csv
id,username,email,password,account_type,created_by,created_at,active
1,funky,admin@funky-cms.de,$2y$10$wHk0v5F2Z0y1lOe1C8T4KeWCfK5T8pB2WaXH9k5T8G1k5v5OeE1Oe,admin,system,2025-03-08 13:00:42,true
```

## Klassenmethoden

### `LoginSystem::getInstance()`
Gibt die Singleton-Instanz der `LoginSystem`-Klasse zurück.

### `LoginSystem::process()`
Verarbeitet den Login-Vorgang, indem die übermittelten Anmeldeinformationen überprüft und Sitzungsdaten verwaltet werden. Zeigt das Anmeldeformular an, wenn der Benutzer nicht eingeloggt ist, und die Benutzerdaten, wenn der Benutzer eingeloggt ist.

### `LoginSystem::handleLogout()`
Verarbeitet den Logout-Vorgang, indem die Sitzung zerstört und der Benutzer zur Logout-Zielseite weitergeleitet wird.

### `LoginSystem::getUserData(string $userId): ?array`
Gibt die Benutzerdaten für die angegebene Benutzer-ID zurück. Gibt `null` zurück, wenn die Benutzer-ID nicht existiert.

### `LoginSystem::checkAccess()`
Überprüft, ob der Benutzer eingeloggt ist. Wenn nicht, wird der Benutzer zur Anmeldeseite weitergeleitet.

## Verwendung

### Anmeldeseite
Um eine Anmeldeseite zu erstellen, verwenden Sie den folgenden Code:

```php name=login.php
<?php
require_once 'LoginSystem.php';

// Erstellen Sie eine Instanz des LoginSystems unter Verwendung des Singleton-Musters
$loginSystem = LoginSystem::getInstance();

// Logout verarbeiten, wenn angefordert
$loginSystem->handleLogout();

// Login-Formular verarbeiten und den entsprechenden Inhalt anzeigen
echo $loginSystem->process();
?>
```

### Geschützte Seite
Um eine geschützte Seite zu erstellen, die einen Benutzer-Login erfordert, verwenden Sie den folgenden Code:

```php name=protected_page.php
<?php
require_once 'LoginSystem.php';

// Erstellen Sie eine Instanz des LoginSystems unter Verwendung des Singleton-Musters
$loginSystem = LoginSystem::getInstance();

// Überprüfen Sie, ob der Benutzer eingeloggt ist
$loginSystem->checkAccess();

// Benutzerdaten aus der Sitzung abrufen
$userData = $loginSystem->getUserData($_SESSION['user_id']);

// Benutzerdaten anzeigen
echo '<h1>Willkommen auf der geschützten Seite!</h1>';
echo '<h2>Benutzerdaten</h2>';
echo '<p>Username: ' . htmlspecialchars($userData['username']) . '</p>';
echo '<p>Email: ' . htmlspecialchars($userData['email']) . '</p>';
echo '<p>Account-Typ: ' . htmlspecialchars($userData['account_type']) . '</p>';
echo '<p>Erstellt von: ' . htmlspecialchars($userData['created_by']) . '</p>';
echo '<p>Erstellt am: ' . htmlspecialchars($userData['created_at']) . '</p>';
echo '<p>Aktiv: ' . htmlspecialchars($userData['active']) . '</p>';

// Logout-Link anzeigen
echo $loginSystem->renderLogoutLink();
?>
```

### Logout-Funktionalität
Um die Logout-Funktionalität zu verarbeiten, stellen Sie sicher, dass die `handleLogout`-Methode auf der Anmeldeseite aufgerufen wird. Diese Methode verarbeitet den Logout-Vorgang, wenn der `?logout`-Abfrageparameter gesetzt ist.

Beispiel:

```php
<?php
require_once 'LoginSystem.php';

// Erstellen Sie eine Instanz des LoginSystems unter Verwendung des Singleton-Musters
$loginSystem = LoginSystem::getInstance();

// Logout verarbeiten, wenn angefordert
$loginSystem->handleLogout();

// Anderer Code...
?>
```

## Beispiel CSV-Datei
Hier ist ein Beispiel für die `users.csv`-Datei:

```csv
id,username,email,password,account_type,created_by,created_at,active
1,funky,admin@funky-cms.de,$2y$10$wHk0v5F2Z0y1lOe1C8T4KeWCfK5T8pB2WaXH9k5T8G1k5v5OeE1Oe,admin,system,2025-03-08 13:00:42,true
```

## Fazit
Die `LoginSystem`-Klasse bietet eine robuste und flexible Möglichkeit, die Benutzer-Authentifizierung in einer PHP-Anwendung zu verwalten. Durch die Befolgung der in dieser Dokumentation bereitgestellten Beispiele und Richtlinien können Sie das Login-System einfach implementieren und an die Anforderungen Ihrer Anwendung anpassen.