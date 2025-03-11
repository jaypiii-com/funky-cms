<?php

namespace FunkyCMS\Core;

use RuntimeException;

/**
 * Verwalter für CSV-Daten mit Method Chaining.
 *
 * Lädt CSV-Daten, validiert sie und erlaubt das Filtern sowie Sortieren der Datensätze.
 */
class CollectionManager
{
    private array $data = [];
    private array $results = [];
    private const DATA_PATH = __DIR__ . '/../collections/';

    public function __construct(string $collection = 'test')
    {
        $csvFile = self::DATA_PATH . $collection . '.csv';
        $csvLoader = new CSVLoader($csvFile);
        $data = $csvLoader->load();

        if (!is_array($data)) {
            throw new RuntimeException("Ungültige CSV-Daten aus der Datei geladen: {$csvFile}");
        }

        $this->data = $data;
        $this->results = $data;
    }

    /**
     * Gibt die aktuell gespeicherten Daten als Array zurück.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->results;
    }

    public function reset(): self
    {
        $this->results = $this->data;
        return $this;
    }

    public function find(string $key = 'id', string $value = '7', array $mode = ['check', '>']): self
    {
        $filterType = $mode[0] ?? 'check';
        $operator = $mode[1] ?? '>';
        $validTypes = ['exact', 'starts', 'contains', 'ends', 'check'];
        $filterType = in_array($filterType, $validTypes, true) ? $filterType : 'exact';

        $this->results = array_filter($this->results, function (array $item) use ($key, $value, $filterType, $operator): bool {
            if (!isset($item[$key])) {
                return false;
            }

            if ($filterType === 'check') {
                return match ($operator) {
                    '>'  => $item[$key] > $value,
                    '>=' => $item[$key] >= $value,
                    '<'  => $item[$key] < $value,
                    '<=' => $item[$key] <= $value,
                    default => false,
                };
            }

            $itemValue = (string) $item[$key];
            return match ($filterType) {
                'exact'    => $itemValue === $value,
                'starts'   => str_starts_with($itemValue, $value),
                'contains' => str_contains($itemValue, $value),
                'ends'     => str_ends_with($itemValue, $value),
                default    => false,
            };
        });

        return $this;
    }

    public function sort(array $keys): self
    {
        usort($this->results, function (array $firstRecord, array $secondRecord) use ($keys): int {
            foreach ($keys as $field => $sortOrder) {
                $firstValue  = $firstRecord[$field]  ?? null;
                $secondValue = $secondRecord[$field] ?? null;

                if ($firstValue == $secondValue) {
                    continue;
                }

                if (is_numeric($firstValue) && is_numeric($secondValue)) {
                    $comparison = $firstValue <=> $secondValue;
                } else {
                    $comparison = strcmp((string)$firstValue, (string)$secondValue);
                }

                return strtolower($sortOrder) === 'desc' ? -$comparison : $comparison;
            }

            return 0;
        });

        return $this;
    }


    public static function merge(string $key, array $keys, array ...$arrays): array 
    {
        $baseArray = array_shift($arrays);
        $indexedData = [];

        foreach ($arrays as $array) {
            foreach ($array as $item) {
                foreach ($keys as $referenceKey) {
                    if (isset($item[$referenceKey])) {
                        $indexedData[$item[$referenceKey]] = array_merge($indexedData[$item[$referenceKey]] ?? [], $item);
                    }
                }
            }
        }

        return array_map(function (array $item) use ($key, $indexedData): array {
            $item[$key] = $indexedData[$item[$key]] ?? [];
            return $item;
        }, $baseArray);

    }
}
