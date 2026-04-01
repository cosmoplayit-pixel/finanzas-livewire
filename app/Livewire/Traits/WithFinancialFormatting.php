<?php

namespace App\Livewire\Traits;

trait WithFinancialFormatting
{
    /**
     * Analiza inteligentemente una cadena de texto (soporta formatos US, EU y crudos)
     * y retorna un float.
     */
    protected function parseFormattedFloat(?string $value): float
    {
        $v = trim((string) $value);
        if ($v === '') {
            return 0.0;
        }

        $v = str_replace([' ', "\u{00A0}"], '', $v);

        $lastComma = strrpos($v, ',');
        $lastDot = strrpos($v, '.');

        if ($lastComma !== false && $lastDot !== false) {
            // Tiene ambos (ej. 1.234,56 o 1,234.56)
            if ($lastComma > $lastDot) {
                // Coma es el decimal (Europeo)
                $v = str_replace('.', '', $v);
                $v = str_replace(',', '.', $v);
            } else {
                // Punto es el decimal (Americano)
                $v = str_replace(',', '', $v);
            }
        } elseif ($lastComma !== false) {
            // Solo tiene coma (ej. 1234,56) -> asumimos decimal
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        } else {
            // Solo tiene punto o ninguno -> asumimos decimal crudo (1234.56 o 1234)
            $v = str_replace(',', '', $v);
        }

        return is_numeric($v) ? (float) $v : 0.0;
    }

    /**
     * Igual a parseFormattedFloat, pero retorna null si la cadena está vacía.
     */
    protected function parseNullableFormattedFloat(?string $value): ?float
    {
        $v = trim((string) $value);
        if ($v === '') {
            return null;
        }

        return $this->parseFormattedFloat($v);
    }

    /**
     * Formatea un float a string para inputs (1.234,56).
     */
    protected function formatFloatValue(?float $value, int $decimals = 2): string
    {
        if ($value === null) {
            return '';
        }

        return number_format($value, $decimals, ',', '.');
    }
}
