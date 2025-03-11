<?php

namespace FunkyCMS\Core;

use RuntimeException;

class CSVLoader
{
    private string $filePath;
    private int $length;
    private string $delimiter;
    private string $enclosure;
    private string $escape;

    public function __construct(
        string $filePath,
        int $length = 1000,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escape = '\\'
    ) {
        $this->filePath  = $filePath;
        $this->length    = $length;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape    = $escape;
    }

    /**
     * Loads CSV data from the file.
     *
     * @return array<int, array<string, mixed>> Parsed CSV data.
     *
     * @throws RuntimeException If the file is not readable or parsing fails.
     */
    public function load(): array
    {
        if (!is_readable($this->filePath)) {
            throw new RuntimeException(sprintf(
                'Data file not found or not readable: %s',
                $this->filePath
            ));
        }

        $handle = fopen($this->filePath, 'r');
        if ($handle === false) {
            throw new RuntimeException(sprintf(
                'Error opening file: %s',
                $this->filePath
            ));
        }

        $data   = [];
        $header = fgetcsv($handle, $this->length, $this->delimiter, $this->enclosure, $this->escape);
        if ($header === false) {
            fclose($handle);
            throw new RuntimeException(sprintf(
                'Failed to read header row from: %s',
                $this->filePath
            ));
        }

        // Optionally process header row
        $header = array_map([$this, 'castValue'], $header);

        while (($row = fgetcsv($handle, $this->length, $this->delimiter, $this->enclosure, $this->escape)) !== false) {
            // Fehlerhafte Zeilen überspringen, wenn die Spaltenzahl nicht passt
            if (empty($row) || count($row) !== count($header)) {
            continue;
            }

            $convertedRow = array_map([$this, 'castValue'], $row);
            $data[] = array_combine($header, $convertedRow);
        }

        fclose($handle);
        return $data;
    }

    /**
     * Casts a string value to its appropriate type.
     *
     * @param string $value The value to be cast.
     *
     * @return mixed The casted value.
     */
    private function castValue(string $value): mixed
    {
        $trimmed = trim($value);

        // Check for boolean values.
        if (strcasecmp($trimmed, 'true') === 0) {
            return true;
        }
        if (strcasecmp($trimmed, 'false') === 0) {
            return false;
        }

        // Check for integer values (including negatives).
        if (preg_match('/^-?\d+$/', $trimmed)) {
            return (int)$trimmed;
        }

        // Return the original value if no conversion is applied.
        return $value;
    }
}
