<?php

function normalizeBcNumber($value): string
{
    if ($value === null || $value === '') {

        return '0';
    }

    /*
    Remove commas/spaces
    */

    $value = str_replace(
        [',', ' '],
        '',
        (string) $value
    );

    /*
    Convert scientific notation
    */

    if (stripos($value, 'e') !== false) {

        $value = sprintf('%.0f', (float) $value);
    }

    /*
    Final validation
    */

    return preg_match('/^-?\d+(\.\d+)?$/', $value)
        ? $value
        : '0';
}