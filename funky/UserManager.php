<?php

class UserManager
{
    private const CSV_FILE = __DIR__ . '/users.csv';
    private array $users = [];

    private array $userRolls = [
        'system' => 'System',
        'admin' => 'Admin',
        'user' => 'User',
        'guest' => 'guest'
    ];


    # load users from csv file

    public function __construct()
    {
        $this->loadUsersFromCSV();
    }

    private function loadUsersFromCSV(): void
    {
        $csvFile = self::CSV_FILE;
        if (($handle = fopen($csvFile, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ",", '"', '\\');
            while (($row = fgetcsv($handle, 1000, ",", '"', '\\')) !== false) {
                $user = array_combine($header, $row);
                $this->users[$user['id']] = $user;
            }
            fclose($handle);
        }
    }

    public function getUserData($id): array
    {
        return $this->users[$id] ?? [];
    }

    public function renderInfo(string $userID): string
    {
        if($userID === null) return "No user selected.";
        $userData = $this->getUserData($userID);
        extract($userData);

        $timeago = $this->timeago($created_at);

        return "
        <div class='max-w-md mx-auto bg-white shadow-lg rounded-lg overflow-hidden'>
            <div class='px-6 py-4'>
                <div class='font-bold text-xl mb-2'>User Info: {$username}</div>
                <p class='text-gray-700 text-base'><strong>Email:</strong> {$email}</p>
                <p class='text-gray-700 text-base'><strong>Account Type:</strong> {$account_type}</p>
                <p class='text-gray-700 text-base'><strong>Created By:</strong> {$created_by}</p>
                <p class='text-gray-700 text-base'><strong>Created:</strong> {$timeago} ago</p>
                <p class='text-gray-700 text-base'><strong>Active:</strong> {$active}</p>
            </div>
        </div>
        ";
    }

    private function timeago(string $timestamp = "2024-12-08 13:00:42"): string
    {
        date_default_timezone_set('Europe/Berlin');
        $zeitstempel = strtotime($timestamp);
        $vergangen = time() - $zeitstempel;
        $vergangen = max(1, $vergangen);

        $zeitEinheiten = [
            31536000 => 'Jahr',
            2592000  => 'Monat',
            604800   => 'Woche',
            86400    => 'Tag',
            3600     => 'Stunde',
            60       => 'Minute',
            1        => 'Sekunde'
        ];

        $einheitenPlurale = [
            'Jahr'    => 'Jahren',
            'Monat'   => 'Monaten',
            'Woche'   => 'Wochen',
            'Tag'     => 'Tagen',
            'Stunde'  => 'Stunden',
            'Minute'  => 'Minuten',
            'Sekunde' => 'Sekunden'
        ];

        foreach ($zeitEinheiten as $sekunden => $einheit) {
            if ($vergangen < $sekunden) {
                continue;
            }
            $anzahl = floor($vergangen / $sekunden);
            $einheitText = $anzahl > 1 ? $einheitenPlurale[$einheit] : $einheit;
            return $anzahl . ' ' . $einheitText;
        }

        return 'keine Info';
    }

    public function addUser(array $userData): void
    {
        $this->users[] = $userData;
        $this->saveUsersToCSV();
    }

    private function saveUsersToCSV(): void
    {
        $csvFile = self::CSV_FILE;
        $handle = fopen($csvFile, 'w');
        fputcsv($handle, array_keys($this->users[0]));
        foreach ($this->users as $user) {
            fputcsv($handle, $user);
        }
        fclose($handle);
    }

    // public function newUser(): void
    // {
    //     $userData = [
    //         'id' => uniqid('', true),
    //         'username' => 'newuser',
    //         'email' => '    ',
    //         'password' =>  haspassword_hash('demo123', PASSWORD_DEFAULT) ,
    //         'account_type' => 'user',
    //         'created_at' => date('Y-m-d H:i:s'),
    //         'created_by' => 'admin',
    //         'active' => 'true'
    //     ];
    //     $this->addUser($userData);
    // }

    function renderUserForm(): string
    {
        return '
        <form method="post" class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg">
            <div class="mb-4">
            <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
            <input type="text" name="username" id="username" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">E-Mail</label>
            <input type="email" name="email" id="email" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-6">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
            <input type="password" name="password" id="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Submit
            </button>
            </div>
        </form>
        ';
    }

    function renderUserList(): string
    {
        // Assume the current user's ID is stored in the session.
        $currentUserId = $_SESSION['user_id'] ?? null;
        $users = $this->users;
        $currentUser = null;

        if ($currentUserId !== null && isset($users[$currentUserId])) {
            $currentUser = $users[$currentUserId];
            unset($users[$currentUserId]);
        }

        // If current user is not of type 'system', show only users created by the current user.
        if ($currentUser && $currentUser['account_type'] !== 'system') {
            foreach ($users as $id => $user) {
                if (!isset($user['created_by']) || $user['created_by'] !== $currentUserId) {
                    unset($users[$id]);
                }
            }
        }

        // Determine the first user to extract table headers.
        $firstUser = $currentUser ?? (count($users) ? reset($users) : null);
        if (!$firstUser) {
            return "<div class='text-center text-gray-600 p-4'>No Users found.</div>";
        }

        $html = "<div class='overflow-x-auto container mx-auto'>";
        $html .= "<table class='min-w-full divide-y divide-gray-200'>";
        
        // Create table header using keys from the first user, excluding 'password' and 'id'
        $html .= "<thead class='bg-gray-50'>";
        $html .= "<tr>";
        foreach ($firstUser as $header => $value) {
            if ($header === 'password' || $header === 'id') {
                continue;
            }
            $html .= "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>" . htmlspecialchars($header) . "</th>";
        }
        $html .= "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Action</th>";
        $html .= "</tr>";
        $html .= "</thead>";

        // Create table body with all user details, current user on top if available.
        $html .= "<tbody class='bg-white divide-y divide-gray-200'>";

        if ($currentUser) {
            $html .= "<tr class='bg-blue-100'>";
            foreach ($currentUser as $key => $value) {
                if ($key === 'password' || $key === 'id') {
                    continue;
                }
                if ($key === 'active') {
                    $badge = ($value === 'true' || $value === true)
                    ? "<span class='bg-green-500 text-white px-2 inline-flex text-xs leading-5 font-semibold rounded-full'>Active</span>"
                    : "<span class='bg-red-500 text-white px-2 inline-flex text-xs leading-5 font-semibold rounded-full'>Inactive</span>";
                    $html .= "<td class='px-6 py-4 whitespace-nowrap text-sm'>{$badge}</td>";
                } elseif ($key === 'created_by') {
                    $creator = $this->getUserData($value);
                    $creatorName = $creator['username'] ?? $value;
                    $html .= "<td class='px-6 py-4 whitespace-nowrap text-sm' title='ID: " . htmlspecialchars($value) . "'>" . htmlspecialchars($creatorName) . "</td>";
                } else {
                    $html .= "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($value) . "</td>";
                }
            }
            // Instead of action links, mark as 'Your Account'
            $html .= "<td class='px-6 py-4 whitespace-nowrap text-sm'><span class='font-bold text-blue-500'>Your Account</span></td>";
            $html .= "</tr>";
        }

        foreach ($users as $user) {
            $html .= "<tr>";
            foreach ($user as $key => $value) {
                if ($key === 'password' || $key === 'id') {
                    continue;
                }
                if ($key === 'active') {
                    $badge = ($value === 'true' || $value === true)
                    ? "<span class='bg-green-500 text-white px-2 inline-flex text-xs leading-5 font-semibold rounded-full'>Active</span>"
                    : "<span class='bg-red-500 text-white px-2 inline-flex text-xs leading-5 font-semibold rounded-full'>Inactive</span>";
                    $html .= "<td class='px-6 py-4 whitespace-nowrap text-sm'>{$badge}</td>";
                } elseif ($key === 'created_by') {
                    $creator = $this->getUserData($value);
                    $creatorName = $creator['username'] ?? $value;
                    $html .= "<td class='px-6 py-4 whitespace-nowrap text-sm' title='ID: " . htmlspecialchars($value) . "'>" . htmlspecialchars($creatorName) . "</td>";
                } else {
                    $html .= "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($value) . "</td>";
                }
            }
            $html .= "<td class='px-6 py-4 whitespace-nowrap text-sm'>";
            $html .= "<div class='inline-flex rounded overflow-hidden divide-x divide-gray-200 text-xs shadow'>";
            $html .= "<a href='".LOGIN_TARGET."/user/edit?user_id=" . htmlspecialchars($user['id']) . "' class='bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold py-1 px-3'>Edit</a>";
            $html .= "<a href='".LOGIN_TARGET."/user?user_id=" . htmlspecialchars($user['id']) . "' class='bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold py-1 px-3'>View</a>";
            $html .= "<a href='".LOGIN_TARGET."/user/delete?user_id=" . htmlspecialchars($user['id']) . "' class='bg-gray-50 hover:bg-gray-100 text-red-500 py-1 px-3'>Delete</a>";
            $html .= "</div>";
            $html .= "</td>";
            $html .= "</tr>";
        }
        $html .= "</tbody>";
        $html .= "</table>";
        $html .= "</div>";

        return $html;
    }


    public function rederUserHeader(): string
    {
        
        return "
    ";
    }
}
