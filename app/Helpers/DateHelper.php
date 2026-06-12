<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DateHelper
{   
  public static function parseInvoiceDate(?string $value): ?string
  {
      if (!$value) {
          return null;
      }

      $original = $value;
      $value = trim(preg_replace('/\s+/', ' ', $value));

      /*
      |--------------------------------------------------------------------------
      | 1 Accept already valid ISO format (fast path)
      |--------------------------------------------------------------------------
      */
      if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
          [$year, $month, $day] = explode('-', $value);

          if (checkdate((int)$month, (int)$day, (int)$year)) {
              return $value;
          }
      }

      /*
      |--------------------------------------------------------------------------
      | 2️ Normalize known OCR / supplier quirks
      |--------------------------------------------------------------------------
      */

      // 25/02-26 → 25/02/26
      //$value = preg_replace('/^(\d{1,2}\/\d{1,2})-(\d{2})$/', '$1/$2', $value);

      // 2. marts 2026 → 2 marts 2026
      //$value = preg_replace('/^(\d{1,2})\.\s*/', '$1 ', $value);

      // 25/02-26 or 11/09-2025 → 25/02/26 or 11/09/2025
      $value = preg_replace('/^(\d{1,2}\/\d{1,2})-(\d{2,4})$/', '$1/$2', $value);      

      // 2. marts 2026 → 2 marts 2026 (only when month name follows)
      $value = preg_replace('/^(\d{1,2})\.\s+(?=[A-Za-z])/', '$1 ', $value);

      // // Danish → English months
      // $value = str_ireplace(
      //     ['januar','februar','marts','april','maj','juni','juli','august','september','oktober','november','december'],
      //     ['January','February','March','April','May','June','July','August','September','October','November','December'],
      //     $value
      // );

      // Danish, Norwegian, Swedish → English months
      // $value = str_ireplace(
      //     [
      //         // Danish
      //         'januar','februar','marts','april','maj','juni','juli','august','september','oktober','november','december',

      //         // Norwegian
      //         'mars','mai','desember',

      //         // Swedish
      //         'januari','februari','augusti'
      //     ],
      //     [
      //         // Danish
      //         'January','February','March','April','May','June','July','August','September','October','November','December',

      //         // Norwegian
      //         'March','May','December',

      //         // Swedish
      //         'January','February','August'
      //     ],
      //     $value
      // );

      $months = [
        // Danish
        'januar'    => 'January',
        'februar'   => 'February',
        'marts'     => 'March',
        'april'     => 'April',
        'maj'       => 'May',
        'juni'      => 'June',
        'juli'      => 'July',
        'august'    => 'August',
        'september' => 'September',
        'oktober'   => 'October',
        'november'  => 'November',
        'december'  => 'December',

        // Norwegian
        'mars'      => 'March',
        'mai'       => 'May',
        'desember'  => 'December',

        // Swedish
        'januari'   => 'January',
        'februari'  => 'February',
        'augusti'   => 'August',
    ];

    // Replace ONLY full month words
    $pattern = '/\b(' .
        implode('|', array_map(
            fn($m) => preg_quote($m, '/'),
            array_keys($months)
        )) .
    ')\b/iu';

    $value = preg_replace_callback(
        $pattern,
        function ($matches) use ($months) {
            return $months[strtolower($matches[1])];
        },
        $value
    );

      //Convert uppercase months like JUN → Jun before parsing:
      $value = preg_replace_callback('/\/([A-Z]{3})\//', function ($m) {
          return '/' . ucfirst(strtolower($m[1])) . '/';
      }, $value);

      //Log::info("Invoice date : {$value}");

      $value = trim($value);
      
      // Remove trailing dot(s) or spaces
      $value = rtrim($value, ". \t\n\r\0\x0B");
      
      /*
      |--------------------------------------------------------------------------
      | 3️ Strict deterministic formats
      |--------------------------------------------------------------------------
      */

      $formats = [
          'j.m.y',
          'j.m.Y',
          'd.m.y',
          'd.m.Y',
          'd-m-y',
          'd-m-Y',
          'd/m/y',
          'd/m/Y',
          'd/M/Y',
          'd F Y',
          'j F Y',
          'd M Y',
          'j M Y',
          'm/d/Y',
          'n/j/Y',
          'dmY',
          'dmy',
      ];

      /*
      foreach ($formats as $format) {

          if (!Carbon::hasFormat($value, $format)) {
              continue;
          }

          $date = Carbon::createFromFormat($format, $value);

          // Ensure strict match
          if ($date->format($format) !== $value) {
              continue;
          }

          // Validate real calendar date
          if (!checkdate($date->month, $date->day, $date->year)) {
              continue;
          }

          return $date->format('Y-m-d');
      }
      */

      foreach ($formats as $format) {
          try {
              $date = Carbon::createFromFormat($format, $value);

              // strict match
              if ($date->format($format) !== $value) {
                  continue;
              }

              if (!checkdate($date->month, $date->day, $date->year)) {
                  continue;
              }

              return $date->format('Y-m-d');

          } catch (\Exception $e) {
              continue;
          }
      }

      /*
      |--------------------------------------------------------------------------
      | 4️ Final failure
      |--------------------------------------------------------------------------
      */
      
      Log::warning('Invoice date could not be parsed', [
          'original' => $original,
          'normalized' => $value,
          'formats_tried' => $formats,
      ]);

      return null;
  }
}