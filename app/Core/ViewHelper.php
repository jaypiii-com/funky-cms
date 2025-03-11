<?php

namespace FunkyCMS\Core;

class ViewHelper
{
    public static function arrayToTable($array, array $filterKeys = ['id'])
    {
        if (empty($array)) {
            return "Das Array ist leer.";
        }

        // Wenn $filterKeys nicht gesetzt ist, verwende alle Keys der ersten Zeile
        if ($filterKeys === null) {
            $filterKeys = array_keys($array[0]);
        }

        // Vorverarbeitung: Zerlege Filterkeys, falls sie einen Doppelpunkt enthalten.
        // z.B. "id:Nr" -> key: "id", header: "Nr".
        $columns = [];
        foreach ($filterKeys as $filterKey) {
            if (strpos($filterKey, ':') !== false) {
                list($realKey, $headerLabel) = explode(':', $filterKey, 2);
            } else {
                $realKey = $filterKey;
                $headerLabel = $filterKey;
            }
            $columns[] = [
                'key'    => $realKey,
                'header' => $headerLabel
            ];
        }

        // Überspringe die Kopfzeile, falls sie in den Daten vorhanden ist
        $dataRows = array_slice($array, 1);

        // Filtere Spalten heraus, bei denen in keinem Datenreihe der Key existiert.
        $columns = array_filter($columns, function($col) use ($dataRows) {
            foreach ($dataRows as $row) {
                if (array_key_exists($col['key'], $row)) {
                    return true;
                }
            }
            return false;
        });
        // Konvertiere zurück zu einem numerisch indexierten Array, falls nötig.
        $columns = array_values($columns);

        // Tabelle mit modernem Styling
        $table = '<table style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;">';

        // Tabellenkopf mit farblicher Hervorhebung
        $table .= '<tr style="background-color: #3498db; color: #fff;">';
        foreach ($columns as $col) {
            $table .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">' 
                . htmlspecialchars($col['header']) . '</th>';
        }
        $table .= '</tr>';

        foreach ($dataRows as $index => $row) {
            $bgColor = (($index + 1) % 2 !== 0) ? 'background-color: #f2f2f2;' : '';
            $table .= '<tr style="' . $bgColor . '">';

            foreach ($columns as $col) {
                $cell = $row[$col['key']] ?? null;
                if ($cell === null) {
                    // Falls Key nicht vorhanden, überspringen
                    continue;
                }
                if (is_bool($cell)) {
                    $display = $cell 
                        ? '<span style="color: green;">true</span>' 
                        : '<span style="color: red;">false</span>';
                } elseif (is_int($cell)) {
                    $display = '<span style="color: blue;">' . $cell . '</span>';
                } else {
                    $display = htmlspecialchars($cell);
                }
                $table .= '<td style="padding: 10px; border: 1px solid #ddd;">' . $display . '</td>';
            }
            $table .= '</tr>';
        }

        $table .= '</table>';

        return $table;
    }
}
