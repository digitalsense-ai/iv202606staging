<?php

namespace App\Helpers;
//use Illuminate\Support\Facades\Log;
class VatRateHelper
{
    /**
     * Normalize VAT rate
     *
     * Examples:
     * 25%      => 25
     * 25,0 %   => 25
     * 8,1%     => 8,1
     * VAT 25   => 25
     * 7.7 %    => 7,7
     */
    public static function normalize(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim($value);

        /**
         * Remove all non-numeric separators
         * Keep only digits, comma, dot
         */
        $value = preg_replace('/[^0-9.,]/', '', $value);

        if (!$value) {
            return null;
        }

        /**
         * If both comma and dot exist:
         * assume comma = thousand separator
         * dot = decimal separator
         *
         * Example:
         * 1,234.56 => 1234.56
         */
        if (str_contains($value, '.') && str_contains($value, ',')) {

            $value = str_replace(',', '', $value);
        }

        /**
         * If only comma exists:
         * convert comma decimal to dot
         *
         * Example:
         * 8,1 => 8.1
         */
        elseif (str_contains($value, ',')) {

            $value = str_replace(',', '.', $value);
        }

        /**
         * Validate numeric
         */
        if (!is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        /**
         * Return integer without decimals
         * 25.0 => 25
         */
        if (floor($number) == $number) {
            return (string) intval($number);
        }

        /**
         * Return European decimal format
         * 8.1 => 8,1
         */
        return str_replace('.', ',', rtrim(rtrim((string) $number, '0'), '.'));
    }

    /**
     * Calculate VAT rate from net + vat amount
     */
    public static function calculate(
        float $netAmount,
        float $vatAmount
    ): string {

        if ($netAmount == 0) {
            return '0';
        }

        $rate = ($vatAmount / $netAmount) * 100;

        /**
         * Special handling
         * 8.1 → preserve
         */
        if ($rate >= 8 && $rate < 9) {
            return '8,1';
        }

        return (string) round($rate);
    }

    /**
     * Resolve final VAT rate
     */
    public static function resolve(
        ?string $extractedVatRate,
        float $netAmount,
        float $vatAmount
    ): ?string {
        $calculatedRate = self::calculate(
            $netAmount,
            $vatAmount
        );
 
        $calculatedRateValue = abs((float) str_replace(',', '.', $calculatedRate));

        /**
         * No extracted VAT rate
         */
        if (!$extractedVatRate) {
            /**
             * Keep extracted if the calculated ranges between 21 to 24
             */
            if (($calculatedRateValue >= 21) && 
                ($calculatedRateValue <= 24)
            ) {
                return 25;
            }
            
            // return $calculatedRate;

            if ($calculatedRateValue > 25)
                return null;

            if ($calculatedRateValue > 0 && $calculatedRateValue < 15)
                return null;
        }

        $normalized = self::normalize($extractedVatRate);

        if (!$normalized) {
            return $calculatedRate;
        }

        if($calculatedRateValue == 0 && $normalized !== 0)
        {
            return $normalized;
        }

        /**
         * If extracted matches calculated
         */
        if ((float) str_replace(',', '.', $normalized)
            == $calculatedRateValue) {

            return $normalized;
        }

        /**
         * Keep extracted if unusually high
         */
        if ($calculatedRateValue >= 25) {
            if($normalized === 2)
                return $calculatedRate;
            else
                return $normalized;
        }

        /**
         * Keep extracted if the calculated ranges between 21 to 24
         */
        if (($calculatedRateValue >= 21) && 
            ($calculatedRateValue <= 24)
        ) {
            return $normalized;
        }

        if ($normalized)
            return $normalized;
        else
            return $calculatedRate;
    }
}