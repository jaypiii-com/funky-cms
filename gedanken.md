## CSV HANDLING 


- jede collection in seperates CSV File speichern 
- mit flock verhindern das daten zeitgeilch überschrieben wenden 
- collection manager mit max, min, first und last erweitern  (nach filterung und/oder sortierung)
- extend methode schreiben damit man daten aus zwei collectents zusamen fügen kann



## Verschlüsselung ??
$key = 'ich bin der key :)'; 
```php
$key = 'ich bin der key :)'; 

// Daten, die verschlüsselt werden sollen (z.B. CSV-Dateiinhalt)
$csvData = file_get_contents(__DIR__.'/../app/data/test.csv');
$iv = openssl_random_pseudo_bytes(16); 
$encryptedData = openssl_encrypt($csvData, 'aes-256-cbc', $key, 0, $iv);
file_put_contents(__DIR__.'/../app/data/test2.csv', base64_encode($iv . $encryptedData));

echo "Die CSV-Datei wurde verschlüsselt.\n";

// Verschlüsselte Datei laden
$encryptedFile = file_get_contents(__DIR__.'/../app/data/test2.csv');

// Die Base64-codierten Daten dekodieren
$decodedData = base64_decode($encryptedFile);

// Der Initialisierungsvektor und die verschlüsselten Daten trennen
$iv = substr($decodedData, 0, 16); // Der IV ist 16 Bytes lang
$encryptedData = substr($decodedData, 16);
$decryptedData = openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);

// Entschlüsselte Daten in einer CSV-Datei speichern
file_put_contents(__DIR__.'/../app/data/test.csv', $decryptedData);

echo "Die CSV-Datei wurde entschlüsselt.\n";

```