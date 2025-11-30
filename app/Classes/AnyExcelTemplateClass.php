<?php

namespace App\Classes;

use Spatie\PdfToText\Pdf as PdfExtract;

use Str;
use Storage;
use Illuminate\Support\Carbon;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

use App\Models\Invoices;
use App\Models\VATReturnFiles;
//use App\Models\VATControlFiles;
use App\Models\ImportReconciliationSalesInvoices;

use App\Classes\CommonClass;
use App\Classes\ApiClass;

class AnyExcelTemplateClass
{ 
  /* -- GENERATE SYSTEM DEFAULT EXCEL - ANYEXCEL -- */
  public function generateSystemDefaultExcel($vatreg, $authUser, $downloadurlgroup = NULL, $groupfile = FALSE)
  {
    try
    {
      $commonClass = new CommonClass();
      $apiClass = new ApiClass();

      $vat_reg_id = $vatreg->vat_reg_id;

      $storage_path = storage_path('app/public/');
   
      $currencycode = '';     
      if($vatreg->country == "DK")     
          $currencycode = "DKK";          
      elseif($vatreg->country == "NO")
          $currencycode = "NOK";
      elseif($vatreg->country == "SE") 
          $currencycode = "SEK";
      elseif($vatreg->country == "GB")
          $currencycode = "GBP";          
      elseif($vatreg->country == "IN")  
          $currencycode = "INR";          
      elseif($vatreg->country == "FR")      
          $currencycode = "EUR";
      elseif($vatreg->country == "CH")      
          $currencycode = "CHF";  

      $anyexceltemplate = $vatreg->anyexceltemplate;
      $sheetcolumns = json_decode($anyexceltemplate->columns);      

      if($groupfile)
        $downloadurls = $downloadurlgroup;
      else  
        $downloadurls[0] = $downloadurlgroup;

      /* -- CLONE DEFAULT EXCEL TEMPLATE -- */
      $existingFilePath = $storage_path . "DefaultExcelTemplate.xlsx";

      // Load the existing Excel file
      $spreadsheet = IOFactory::load($existingFilePath);        
      /* --end CLONE DEFAULT EXCEL TEMPLATE -- */

      $sheetlist = [];
      foreach ($downloadurls as $urlkey => $downloadurl) 
      {
        /* -- SAVE ORIGNAL FILE TO STORAGE -- */            
        $original_file = $downloadurl['download_url'];
        $original_file_name = $downloadurl['name'];
        $original_file_extension = $downloadurl['file_extension'];

        $contents = (strpos($original_file, "https://") !== false) ? file_get_contents($original_file) : $original_file;        
        
        $original_filename = Str::random(10) . '.' . $original_file_extension;
        
        Storage::disk('public')->put($original_filename, $contents);
        /* --end SAVE ORIGNAL FILE TO STORAGE -- */

        /* -- READ ORIGNAL FILE -- */ 
        $original_inputFileName = $storage_path . $original_filename;
        $original_spreadsheet = IOFactory::load($original_inputFileName);
        
        $original_sheetCount = $original_spreadsheet->getSheetCount();       
        /* --end READ ORIGNAL FILE -- */         
        
        /* -- GET EXCEL COLUMNS -- */
        $excel_columns = $commonClass->listExcelColumns();
        /* --/ GET EXCEL COLUMNS -- */ 

        /* -- ORIGNAL FILE SHEETS -- */      
        $same_sheet = false;       
        $samesheet_highestRow = 0;
        
        $missing_mapped_column_value = [];
        /* -- BOTH TEMPLATE SHEET AND UPLOADED FILE SHEET COUNT SHOULD BE EQUAL -- */
        if(count($sheetcolumns) == $original_sheetCount)
        {
          /* -- for ORIGNAL FILE SHEETS -- */ 
          for ($i = 0; $i < $original_sheetCount; $i++) 
          { 
            $no_mapping_columns = [];   
               
            $original_activeSheet = $original_spreadsheet->getSheet($i);

            $original_highestRow = $original_activeSheet->getHighestRow();
            $original_highestColumn = $original_activeSheet->getHighestColumn();
  
            $original_sheetName = $original_activeSheet->getTitle();          
                        
            $filter_sheet = $sheetcolumns[$i];  

            if(isset($filter_sheet->data_index))
            {
              $data_index = $filter_sheet->data_index;
              $columns = $filter_sheet->columns;                    
              $sheet_name = $filter_sheet->sheet_name; 
                            
              // Step 1: Count how many times each mapped_column appears
              $mappedColumnCounts = [];
              foreach ($columns as $col) {
                  if (!empty($col->mapped_column)) {
                      $mappedColumnCounts[$col->mapped_column] = ($mappedColumnCounts[$col->mapped_column] ?? 0) + 1;
                  }
              }

              $requiredIndexes = array_keys(array_filter($columns, function ($col) use ($mappedColumnCounts) {
                  return !empty($col->mapped_column) && $mappedColumnCounts[$col->mapped_column] === 1;
              }));

              /* -- SHEET EXACT HIGHEST ROW -- */
              $chunkSize = 1000;
              $startRow = $data_index;
              $exact_highestRow = 0;
              /* -- do CHUNKS OF DATAS TO CACULATE THE TOTAL ROWS -- */
              do 
              {
                $endRow = min($startRow + $chunkSize - 1, $original_highestRow);

                /* --for CHUNKS OF DATAS TO CACULATE THE TOTAL ROWS-- */
                for ($row = $startRow; $row <= $endRow; $row++) 
                {                  
                  $rowData = $original_activeSheet->rangeToArray('A' . $row . ':' . $original_highestColumn . $row);

                  /* -- FILTER NON-EMPTY ROWS -- */
                  $filter_rowData = array_filter($rowData, function ($item) {         
                    $filter_item = array_filter($item, function($value) { return (!is_null($value) && $value !== ''); });

                    if (!empty($filter_item))
                      return $filter_item;         
                  });
                  /* --end FILTER NON-EMPTY ROWS -- */

                  if (!empty($filter_rowData)) 
                  {                    
                    $exact_highestRow++;
                  } /* --end if FILTER NON-EMPTY ROWS -- */
                }/* --end for CHUNKS OF DATAS TO CACULATE THE TOTAL ROWS -- */

                $startRow = $endRow + 1;

              } while ($startRow <= $original_highestRow); 
              /* --end while CHUNKS OF DATAS TO CACULATE THE TOTAL ROWS -- */

              $exact_highestRow = $exact_highestRow + $data_index;
              /* --end SHEET EXACT HIGHEST ROW -- */

              /* -- MISSING MAPPED COLUMN VALUES -- */
              /* -- do CHUNKS OF DATAS TO FIND THE MISSING VALUES -- */
              $startRow = $data_index;
              do 
              {
                $endRow = min($startRow + $chunkSize - 1, $exact_highestRow);

                /* --for CHUNKS OF DATAS TO CACULATE THE TOTAL ROWS-- */
                for ($row = $startRow; $row < $endRow; $row++) 
                {                  
                  $rowData = $original_activeSheet->rangeToArray('A' . $row . ':' . $original_highestColumn . $row);
                  
                  // Step 2: Check for any null values at those required indexes
                  $missing = array_filter($requiredIndexes, fn($i) => !isset($rowData[0][$i]) || is_null($rowData[0][$i]));

                  // Step 3: If any missing, return error
                  if (!empty($missing) && ($row != ($endRow - 1))) 
                  {                        
                    $missing = array_values($missing);                     

                    $mapped = array_map(
                      fn($index) => [                        
                        'errors' => "Missing value at column '{$columns[$index]->column}' for mapped field '" . str_replace('_', ' ', preg_replace('/^[A-Z]:/', '', $columns[$index]->mapped_column)) . "'."
                      ],
                      $missing
                    ); 

                    $missing_mapped_column_value = array_combine($missing, $mapped);                                       
                  }                                  
                }/* --end for CHUNKS OF DATAS TO CACULATE THE TOTAL ROWS -- */

                $startRow = $endRow + 1;

              } while ($startRow <= $exact_highestRow); 
              /* --end while CHUNKS OF DATAS TO FIND THE MISSING VALUES -- */

              if($missing_mapped_column_value)
              {               
                
              }/* --end MISSING MAPPED COLUMN VALUES -- */
              else
              {              
                /* -- TOTAL ROWS, if multiple files -- */
                if (empty($sheetlist) || !array_key_exists($sheet_name,$sheetlist))
                {
                  $same_sheet = false;
                
                  $sheetlist[$sheet_name] = $exact_highestRow;

                  $samesheet_highestRow = $sheetlist[$sheet_name];
                } /* --end if SHEET NAME AND INDEX -- */
                else          
                {            
                  $same_sheet = true;
                  
                  $sheet_data_row = $sheetlist[$sheet_name];
                 
                  $sheetlist[$sheet_name] = $sheet_data_row + $exact_highestRow;

                  $samesheet_highestRow = $sheetlist[$sheet_name];
                 
                  $start_write_row = $write_row;                
                } /* --end else SHEET NAME AND INDEX -- */
                /* --end TOTAL ROWS, if multiple files -- */

                $activeSheet = $spreadsheet->getSheetByName($sheet_name);

                // PREM - need to include invoice no. filter 
                /* -- SHEET HAS MULTIPLE MAPPING FOR SAME COLUMN -- */              
                $filter_same_column_mapped = collect($columns)
                                ->pluck('mapped_column')
                                ->filter()
                                ->countBy()
                                ->filter(fn($count) => $count > 1)                              
                                ;
                /* --end SHEET HAS MULTIPLE MAPPING FOR SAME COLUMN -- */   

                /* -- for SYSTEM DEFAULT EXCEL COLUMNS -- */
                foreach($excel_columns as $excel_column_key => $excel_column)
                {
                  $excel_column_names = explode(':', $excel_column_key);                
                  $excel_column_name = (count($excel_column_names) > 0) ? $excel_column_names[0] : '';
                 
                  if($excel_column_name != "")
                  {
                    $filter_columns = array_values(array_filter($columns, function ($column) use ($excel_column_key) {
                      return $excel_column_key == $column->mapped_column;
                    }));

                    if(count($filter_columns) > 0)
                    {
                      $write_row = ($same_sheet) ? $start_write_row : 3;

                      $chunkSize = 1000;
                      $startRow = $data_index;

                      /* -- do CHUNKS OF DATAS TO LOOP ROWS -- */
                      do 
                      {
                        $endRow = min($startRow + $chunkSize - 1, $exact_highestRow);

                        /* --for CHUNKS OF DATAS TO LOOP ROWS -- */
                        for ($row = $startRow; $row <= $endRow; $row++) 
                        {                       
                          /* -- SKIP HIDDEN ROWS -- */
                          if (!$original_activeSheet->getRowDimension($row)->getVisible())
                            continue;
                          /* --end SKIP HIDDEN ROWS -- */

                          $rowData = $original_activeSheet->rangeToArray('A' . $row . ':' . $original_highestColumn . $row);
                          
                          /* -- FILTER NON-EMPTY ROWS -- */
                          $filter_rowData = array_filter($rowData, function ($item) {         
                            $filter_item = array_filter($item, function($value) { return (!is_null($value) && $value !== ''); });

                            if (!empty($filter_item))
                              return $filter_item;         
                          });
                          /* --end FILTER NON-EMPTY ROWS -- */

                          /* -- if FILTERED DATA ROWS -- */
                          if (!empty($filter_rowData))                     
                          {      
                            /* -- SHEET HAS MULTIPLE MAPPING FOR SAME COLUMN -- */
                            $filter_multiple_columns = array_filter($columns, function ($column) use ($filter_same_column_mapped) {
                              $compare_key = $column->mapped_column;
                              return $filter_same_column_mapped->has($compare_key);                            
                            }); 
                            /* --end SHEET HAS MULTIPLE MAPPING FOR SAME COLUMN -- */  

                            $column_position = [];
                            $position_index = 0;
                            $column_position_name = '';
                            if(count($filter_multiple_columns) > 1)                        
                            {                                                        
                              foreach($filter_multiple_columns as $filter_multiple_column)
                              {
                                $multiple_column_name = str_replace('Column ', '', $filter_multiple_column->column); 

                                $column_value = trim($original_activeSheet->getCell($multiple_column_name.$row)->getFormattedValue());
                                
                                if($column_value != '' && $column_value != '-')
                                {
                                  if((stripos($column_value, "-") !== false))
                                  {                                   
                                    $column_position[] = $position_index;                                  
                                  }
                                  else
                                    $column_position[] = $position_index;

                                  $column_position_names = explode(':', $filter_multiple_column->mapped_column);                           
                                  $column_position_name = (count($column_position_names) > 0) ? $column_position_names[0] : '';
                                }
                                $position_index++;
                              } /* --FOR MULTIPLE MAPPING FOR SAME COLUMN -- */                                                 
                            } /* --if MULTIPLE MAPPING FOR SAME COLUMN -- */

                            $read_upto = 1;
                            if(isset($column_position))
                              $read_upto = (count($column_position) + (($groupfile) ? 1 : 0));
                            
                            if($read_upto == 0)
                                $read_upto = 1;    

                            for ($j = 0; $j < $read_upto; $j++) 
                            {                                
                              if(isset($column_position) && isset($column_position_name) && $excel_column_name == $column_position_name)
                              {
                                $_position = $column_position[$j];
                                $filter_column = $filter_columns[$_position];
                              } /* --end if E COLUMN -- */  
                              else  
                                $filter_column = $filter_columns[0];                           

                              $original_column_name = str_replace('Column ', '', $filter_column->column); 

                              $mapping_column_names = explode(':', $filter_column->mapped_column);                           
                              $mapping_column_name = (count($mapping_column_names) > 0) ? $mapping_column_names[0] : '';

                              $original_value = trim($original_activeSheet->getCell($original_column_name.$row)->getFormattedValue());
                             
                              $original_value = ($original_value == '-' || $original_value == '') ? 0 : $original_value;

                              if($excel_column_name == "B")
                              {                                
                                if (isset($original_value))
                                {                               
                                  $carbonDate = NULL;
                                  if (preg_match('/^\d{8}$/', $original_value))
                                    $carbonDate = Carbon::createFromFormat('Ymd', $original_value);
                                  elseif (preg_match('/^\d{2}-\d{2}-\d{4}$/', $original_value))
                                    $carbonDate = Carbon::createFromFormat('d-m-Y', $original_value);                               
                                  elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $original_value))
                                    $carbonDate = Carbon::createFromFormat('Y-m-d', $original_value);
                                  elseif (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $original_value))
                                    $carbonDate = Carbon::createFromFormat('n/j/Y', $original_value);
                                  elseif (preg_match('/^\d{2}-\d{2}-\d{2}$/', $original_value))
                                    $carbonDate = Carbon::createFromFormat('d-m-y', $original_value);

                                  if ($carbonDate instanceof Carbon)
                                    $original_value = $carbonDate->format('Y-m-d');
                                  else
                                    $original_value = "***remove row***";
                                }
                              } /* --end if COLUMN B - DATE FORMAT -- */  
                              else if($excel_column_name == "F")
                              {
                                $original_value = trim(str_replace(['%'], '', $original_value));
                              } /* --end if COLUMN F - VAT PERCENTAGE WITH PERCENTAGE SYMBOL -- */  
                              else if($excel_column_name == "E" || $excel_column_name == "G")
                              {                               
                                $original_value = $commonClass->floatvalue($original_value);                              
                              } /* --end if COLUMN E, G - FLOAT FORMAT -- */

                              /* -- REVERSE -- */
                              if(isset($filter_column->reverse))
                              {
                                if (str_starts_with($original_value, '-'))
                                  $original_value = ltrim($original_value, '-');
                                else
                                  $original_value = '-' . $original_value;
                              }
                              /* --end REVERSE -- */

                              /* -- FORMULA -- */
                              $vat_percentage = '';
                              $vat_amount = '';
                              if(isset($filter_column->formula))
                              {     
                                $formula_value = $filter_column->formula; 

                                $arr_formula = explode(' ', $formula_value);

                                $formula = '';
                                foreach ($arr_formula as $key => $value) 
                                {                                                          
                                  $single_value = str_replace(['(', ')'], '', $value);                              
                                  if (preg_match('/^[A-Z]$/', $single_value))
                                  {
                                    if($original_column_name == $single_value)
                                    {
                                      if($arr_formula[$key + 1] == '/' &&
                                        filter_var(str_replace(['(', ')'], '', $arr_formula[$key + 2]), FILTER_VALIDATE_FLOAT) !== false
                                      )
                                        $vat_percentage = str_replace(['(', ')'], '', trim($arr_formula[$key + 2])) * 100;
                                      else
                                      {                                                                              
                                        preg_match('/\d+(\.\d+)?/', trim($original_activeSheet->getCell($single_value.($data_index-1))->getFormattedValue()), $matches);
                                        $vat_percentage = $matches[0] ?? null;
                                      
                                        $vat_amount = $commonClass->floatvalue(trim($original_activeSheet->getCell($single_value.$row)->getFormattedValue()));
                                      }
                                     
                                      $arr_formula[$key] = str_replace($single_value, trim($original_value), $arr_formula[$key]);
                                    }
                                    else
                                    {
                                      $replace_value = $commonClass->floatvalue(trim($original_activeSheet->getCell($single_value.$row)->getFormattedValue()));
                                                                        
                                      $replace_value = ($arr_formula[$key - 1] == '/' && $replace_value == 0) ? 1 : $replace_value;
                                      $arr_formula[$key] = str_replace($single_value, $replace_value, $arr_formula[$key]);
                                    } /* --end else REPLACE RELEVANT COLUMN VALUE -- */                                  
                                  } /* --end if FORMULA COLUMN IS CHARACTER -- */
                                  else if(preg_match('/<span>.*?<\/span>/i', $single_value))
                                  {                                  
                                    $chars = str_split($single_value);
                                    foreach ($chars as $char) 
                                    {
                                      if (preg_match('/^[A-Z]$/', $char))
                                      {
                                        $replace_value = $commonClass->floatvalue($original_activeSheet->getCell($char.$row)->getFormattedValue());
                                        $arr_formula[$key] = str_replace($char, trim($replace_value), $arr_formula[$key]);
                                      } /* --end if CHAR IS CHARACTER -- */
                                    } /* --end for CHAR -- */

                                    $arr_formula[$key] = preg_replace('/<\/?span[^>]*>/i', '', $arr_formula[$key]);
                                  } /* --end if FORMULA COLUMN IS VALUE WITH FORMULA -- */
                                } /* --end for FORMULA -- */

                                /* -- FORMULA - REPLACE if any comma -- */
                                $arr_formula = array_map(function($item) {
                                    return strpos($item, ',') !== false ? str_replace(',', '.', $item) : $item;
                                }, $arr_formula);
                                /* -- FORMULA - REPLACE if any comma -- */

                                $expressionLanguage = new ExpressionLanguage();
                                $formula = implode('', $arr_formula);
                                $original_value = $expressionLanguage->evaluate($formula);  
                               
                                if(filter_var($original_value, FILTER_VALIDATE_FLOAT) !== false)
                                  $original_value = $commonClass->floatvalue(number_format($original_value, 2));                             
                              } /* --end if FORMULA -- */
                              /* --end FORMULA -- */

                              $activeSheet->setCellValue($mapping_column_name.$write_row, $original_value);                             
                              if($vat_percentage)
                              {
                                $final_vat_percentage = (intval($vat_percentage) == $vat_percentage) ? intval($vat_percentage) : $vat_percentage;
                                $activeSheet->setCellValue('F'.$write_row, $final_vat_percentage); 
                              }
                              
                              if($vat_amount)
                                $activeSheet->setCellValue('G'.$write_row, $vat_amount); 
                              
                              $write_row++; 
                            } /* --end for READ UPTO -- */
                          } /* --end if FILTERED DATA ROWS -- */
                        } /* --end for CHUNKS OF DATAS TO LOOP ROWS -- */

                        $startRow = $endRow + 1;                        
                      } while ($startRow <= $exact_highestRow);  
                      /* -- while CHUNKS OF DATAS TO LOOP ROWS -- */
                    } /* --end if FILTER COLUMN -- */
                    else                  
                      $no_mapping_columns[] = $excel_column_name;                     
                  } /* --end if SYSTEM DEFAULT EXCEL COLUMN NAME -- */
                } /* --end for SYSTEM DEFAULT EXCEL COLUMNS -- */              
              

                /* -- NO MAPPING COLUMNS */
                $sheet_row_count = ($activeSheet->getHighestRow() > $write_row) ? ($write_row - 1) : $activeSheet->getHighestRow();
                if(count($no_mapping_columns) > 0)
                {
                  /* -- for NO MAPPING COLUMNS -- */
                  foreach($no_mapping_columns as $no_mapping_column)
                  {
                    $column = $no_mapping_column;
                    $startRow = 3;                                  
                    for ($row = $startRow; $row <= $sheet_row_count; $row++)                
                    {
                      if($no_mapping_column == 'A')
                      {                      
                        $total_revenue_amount = $commonClass->floatvalue($activeSheet->getCell("L".$row)->getFormattedValue());
                        $total_net_amount = $commonClass->floatvalue($activeSheet->getCell("E".$row)->getFormattedValue());

                        if($sheet_name == 'Sales')
                        {
                          if(stripos($total_revenue_amount, "-") !== false || stripos($total_net_amount, "-") !== false)
                            $original_value = 'DSGS_CN';
                          else
                            $original_value = 'DSGS';
                        }
                        else if($sheet_name == 'Purchases')
                        {
                          if(stripos($total_revenue_amount, "-") !== false || stripos($total_net_amount, "-") !== false)
                            $original_value = 'DPGS_CN';
                          else
                            $original_value = 'DPGS';
                        }

                        $activeSheet->setCellValue('A'.$row, $original_value);  
                      } /* --end if COLUMN A:TAX CODE -- */
                      else if($no_mapping_column == 'B')
                      {                        
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $vatreg->service_start))
                          $carbonDate = Carbon::createFromFormat('Y-m-d', $vatreg->service_start);
                        
                        if ($carbonDate instanceof Carbon)
                          $original_value = $carbonDate->format('Y-m-d');
                        else
                          $original_value = "***remove row***";

                        $activeSheet->setCellValue('B'.$row, $original_value);  
                      } /* --end else COLUMN D:CURRENCY CODE -- */
                      else if($no_mapping_column == 'C')
                      {                                                
                        $original_value = "TESTINV" . $row - 2;
                        
                        $activeSheet->setCellValue('C'.$row, $original_value);  
                      } /* --end else COLUMN D:CURRENCY CODE -- */
                      else if($no_mapping_column == 'D')
                      {
                        $original_value = $currencycode; 

                        $activeSheet->setCellValue('D'.$row, $original_value);  
                      } /* --end else COLUMN D:CURRENCY CODE -- */
                      else if($no_mapping_column == 'F')
                      {
                        if(trim($activeSheet->getCell("F".$row)->getFormattedValue()) == '')
                        {
                          $original_value = ($currencycode == 'NOK') ? 25 : 0;

                          $activeSheet->setCellValue('F'.$row, $original_value);

                          if(trim($activeSheet->getCell("G".$row)->getFormattedValue()) == '')
                          {
                            if(trim($activeSheet->getCell("E".$row)->getFormattedValue()) != '')
                            {                           
                              $net_amount = $activeSheet->getCell("E".$row)->getFormattedValue();

                              $vat_amount = $commonClass->floatvalue(number_format((($net_amount * $original_value)/100), 2));

                              $activeSheet->setCellValue('G'.$row, $vat_amount);
                            } /* --end if NET AMOUNT NOT NULL -- */  
                          } /* --end if VAT AMOUNT IS NULL -- */  
                          else
                          {
                            if(trim($activeSheet->getCell("E".$row)->getFormattedValue()) != "" && trim($activeSheet->getCell("G".$row)->getFormattedValue()) != "")
                            {                              
                              $vat_amount = $activeSheet->getCell("G".$row)->getFormattedValue();
                              $net_amount = $activeSheet->getCell("E".$row)->getFormattedValue();
                              
                              $vat_percentage = 0;
                              if($net_amount != 0)
                                $vat_percentage = round(($vat_amount * 100)/$net_amount); 

                              $activeSheet->setCellValue('F'.$row, $vat_percentage);

                              if(trim($activeSheet->getCell("H".$row)->getFormattedValue()) == '')
                              {
                                $gross_amount = (float)$vat_amount + (float)$net_amount;
                                $activeSheet->setCellValue('H'.$row, $gross_amount);
                              } /* --end if GROSS AMOUNT IS NULL -- */  
                            } /* --end if CALCULATE F:VAT PERCENTAGE, H:GROSS AMOUNT  -- */
                            else if(trim($activeSheet->getCell("F".$row)->getFormattedValue()) != '' && trim($activeSheet->getCell("G".$row)->getFormattedValue()) != '')
                            {
                              $vat_amount = $activeSheet->getCell("G".$row)->getFormattedValue();
                              $vat_percentage = $activeSheet->getCell("F".$row)->getFormattedValue();

                              if($vat_percentage > 0)
                              {
                                $net_amount = $commonClass->floatvalue(number_format((($vat_amount * 100)/$vat_percentage), 2));  
                               
                                $activeSheet->setCellValue('E'.$row, $net_amount);

                                $gross_amount = $commonClass->floatvalue(number_format(((float)$vat_amount + (float)$net_amount), 2));

                                if(stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false)
                                  $activeSheet->setCellValue('H'.$row, $gross_amount);
                                else
                                  $activeSheet->setCellValue('H'.$row, $gross_amount);
                              }
                            } /* --end if CALCULATE E:NET AMOUNT FROM f:VAT PERCENTAGE AND G:VAT AMOUNT  -- */
                          } /* --end if VAT AMOUNT IS NOT NULL -- */ 
                        } /* --end if VAT PERCENTAGE IS NULL -- */    
                      } /* --end else COLUMN F:VAT RATE -- */
                      else
                      {
                        if(trim($activeSheet->getCell("E".$row)->getFormattedValue()) != "" && trim($activeSheet->getCell("G".$row)->getFormattedValue()) != "")
                        {
                          if(stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false)
                          {
                            $vat_amount = $activeSheet->getCell("G".$row)->getFormattedValue();
                            $net_amount = $activeSheet->getCell("E".$row)->getFormattedValue();
                          }
                          else
                          {
                            $vat_amount = $commonClass->floatvalue($activeSheet->getCell("G".$row)->getFormattedValue());
                            $net_amount = $commonClass->floatvalue($activeSheet->getCell("E".$row)->getFormattedValue());
                          } // SERVER                        

                          if(trim($activeSheet->getCell("F".$row)->getFormattedValue()) == '')
                          {
                            $vat_percentage = 0;
                            if($net_amount != 0)
                              $vat_percentage = round(($vat_amount * 100)/$net_amount);                                    
                            
                            $activeSheet->setCellValue('F'.$row, $vat_percentage);
                          } /* --end if VAT PERCENTAGE IS NULL -- */ 
                          else
                          {
                            $vat_percentage = trim($activeSheet->getCell("F".$row)->getFormattedValue());

                            // if($vat_percentage > 0)
                            // {
                            //   $new_net_amount = $commonClass->floatvalue(number_format((($vat_amount * 100)/$vat_percentage), 2));  

                            //   if($new_net_amount != $net_amount)   
                            //   {
                            //     $activeSheet->setCellValue('E'.$row, $new_net_amount);

                            //     $gross_amount = $commonClass->floatvalue(number_format(((float)$vat_amount + (float)$new_net_amount), 2));

                            //     if(stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false)
                            //       $activeSheet->setCellValue('H'.$row, $gross_amount);
                            //     else
                            //       $activeSheet->setCellValue('H'.$row, $gross_amount);
                            //   }
                            // }

                            $gross_amount = $commonClass->floatvalue(number_format(((float)$vat_amount + (float)$net_amount), 2));
                            $activeSheet->setCellValue('H'.$row, $gross_amount);
                          } /* --end if VAT PERCENTAGE IS NOT NULL -- */     

                          if(trim($activeSheet->getCell("H".$row)->getFormattedValue()) == '')
                          {
                            $gross_amount = $commonClass->floatvalue(number_format(((float)$vat_amount + (float)$net_amount), 2));
                            //if(stripos("localhost:8000", $_SERVER['HTTP_HOST']) !== false)
                              //$activeSheet->setCellValue('H'.$row, $gross_amount);
                            //else
                              $activeSheet->setCellValue('H'.$row, $gross_amount);
                          } /* --end if GROSS AMOUNT IS NULL -- */  
                        } /* --end if CALCULATE F:VAT PERCENTAGE, H:GROSS AMOUNT  -- */
                      } /* --end else OTHER EMPTY COLUMNS -- */
                    } /* --end for MAPPED EXCEL -- */
                  } /* --end for NO MAPPING COLUMNS -- */      
                } /* --end if NO MAPPING -- */  
                /* -- NO MAPPING COLUMNS */

                /* -- DELETE EXTRA ROWS IF ANY -- */
                $delete_rows = [];
                for ($row = 3; $row <= $sheet_row_count; $row++)                
                {
                  if($activeSheet->getCell("B".$row)->getFormattedValue() == '' && $activeSheet->getCell("C".$row)->getFormattedValue() == '')
                  {                  
                    $activeSheet->setCellValue('A'.$row, ''); 
                    $activeSheet->setCellValue('D'.$row, ''); 
                    $activeSheet->setCellValue('E'.$row, '');
                    $activeSheet->setCellValue('F'.$row, '');
                    $activeSheet->setCellValue('H'.$row, '');
                  }  
                  else
                  {                    
                    if( $activeSheet->getCell("B".$row)->getFormattedValue() == "***remove row***" ||
                      ($activeSheet->getCell("E".$row)->getFormattedValue() == 0 || $activeSheet->getCell("E".$row)->getFormattedValue() == '') && 
                      ($activeSheet->getCell("G".$row)->getFormattedValue() == 0 || $activeSheet->getCell("G".$row)->getFormattedValue() == '') && 
                      ($activeSheet->getCell("H".$row)->getFormattedValue() == 0 || $activeSheet->getCell("H".$row)->getFormattedValue() == '')
                    )                   
                      $delete_rows[] = $row;
                  }                
                } /* --end for DELETE ROWS -- */ 

                rsort($delete_rows);
                foreach ($delete_rows as $delete_row)
                  $activeSheet->removeRow($delete_row);             
                /* -- end DELETE EXTRA ROWS IF ANY -- */  

              }/* --end else NO MISSING MAPPED COLUMN VALUES -- */    
            } /* --end if ORIGNAL FILE HAS ACTIVE SHEET -- */ 
          } /* --end for ORIGNAL FILE SHEETS -- */

// // Save the cloned spreadsheet to a new location  
// $newFileName = $vat_reg_id . '.xlsx';    
// $newFilePath = $storage_path . $newFileName;

// $writer = new WriterXlsx($spreadsheet);
// $writer->save($newFilePath); 

// dd("looppppppppppppp");

          /* -- MISSING MAPPED COLUMN VALUES -- */
          if($missing_mapped_column_value)
          { 
            $errors = array_map(fn($item) => "<li class='pb-2'>{$item['errors']}</li>", $missing_mapped_column_value);

            /* -- RETURN JSON -- */
            return [   
                'status' => 'Error',                 
                'message' => "Data is missing for the mapped columns. Please upload an Excel file with the correct data." . "<ul class='mt-3 mb-0 text-start'>" . implode("", $errors) . "</ul>"                
            ];
            /* --end RETURN JSON -- */ 
          }/* --end if MISSING MAPPED COLUMN VALUES -- */         
          else 
          {
            /* -- GET VAT RETURN FILE ID TO STORE THE MAPPED FILE -- */
            $vatreturn_files = $vatreg->vatreturnfiles;          
            $vatreturnfile = $vatreturn_files->filter(function ($vatreturn_file, $key) use ($original_file_name) {          
              $vatreturn_o_files = $vatreturn_file->vatreturnofiles;
              $vatreturnofile = $vatreturn_o_files->filter(function ($vatreturn_o_file, $okey) use ($original_file_name) {
                return $vatreturn_o_file->file_name == $original_file_name;
              })->first();

              return $vatreturnofile;
            })->first();         
            $vatreturnfile_id = $vatreturnfile->id;
            /* --end GET VAT RETURN FILE ID TO STORE THE MAPPED FILE -- */

            /* -- DELETE OM PUBLIC FOLDER -- */
            Storage::disk('public')->delete($original_filename);           
            /* --end DELETE OM PUBLIC FOLDER -- */

            /* -- FINALLY SAVE -- */
            $final_save = false; 
            if($groupfile)
            {            
              if($urlkey == (count($downloadurls)-1))
                $final_save = true;
            }
            else
              $final_save = true;

            if($final_save)
            {
              try 
              {                          
                $newFileName = $vat_reg_id . '.xlsx';    
                $newFilePath = $storage_path . $newFileName;

                $writer = new WriterXlsx($spreadsheet);
                $writer->save($newFilePath); 
                
                /* -- STORE MAPPED FILE IN VAT RETURN FILES -- */
                $apiClass =  new ApiClass();

                $system = $commonClass->getSystemInfoLazy();
                $systemapi = $system->systemapi->first();
               
                $filecontent = file_get_contents($newFilePath);
                $file[0] = $filecontent;
               
                $fileDetails = $apiClass->uploadFileToOneDriveLazy($file, $vatreg, $authUser, $systemapi);
               
                $vatreturnfile = VATReturnFiles::where('id', $vatreturnfile_id)->first();
                /* --end STORE MAPPED FILE IN VAT RETURN FILES -- */

                /* -- DELETE FROM PUBLIC FOLDER -- */              
                Storage::disk('public')->delete($newFileName);  
                /* --end DELETE FROM PUBLIC FOLDER -- */   

                $commonClass->addLog($authUser, 'invoice-mapped', 
                  [          
                    'Client Name' => $vatreg->client->client_name,
                    'VAT Reg' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods
                  ]
                );

                /* -- RETURN JSON -- */
                return [   
                    'status' => 'Success',                 
                    'vatreturnfile' => $vatreturnfile
                ];
                /* --end RETURN JSON -- */ 
              } 
              catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) 
              {        
                /* -- RETURN JSON -- */
                return response()->json([   
                    'status' => 'Error',                 
                    'message' => $e->getMessage()
                ]);
                /* --end RETURN JSON -- */ 
              }
            } /* --end if FINAL SAVE TRUE -- */
            /* -- FINALLY SAVE -- */
          }/* --end else NO MISSING MAPPED COLUMN VALUES -- */    

        } /* --end if BOTH TEMPLATE SHEET AND UPLOADED FILE SHEET COUNT SHOULD BE EQUAL -- */  
        else
          /* -- RETURN JSON -- */
          return [   
              'status' => 'Error',                 
              'message' => "The uploaded file doesn't match with the choosen template" 
          ];
          /* --end RETURN JSON -- */ 
        /* --end BOTH TEMPLATE SHEET AND UPLOADED FILE SHEET COUNT SHOULD BE EQUAL -- */  
      } /* --end for GROUP FILES -- */
    }
    catch (\Exception $e) 
    {     
      //dd($e);      

      /* -- RETURN JSON -- */
      return [   
          'status' => 'Error',                 
          'message' => $e->getMessage()
      ];
      /* --end RETURN JSON -- */    
    } 
  }
  /* --end GENERATE SYSTEM DEFAULT EXCEL - ANYEXCEL -- */

  /* -- COMPARE VAT RETURN CONTROL EXCEL - ANYEXCEL -- */
  public function CompareControlExcel($vatreg, $authUser, $downloadurlgroup = NULL, $groupfile = FALSE, $type = 'vatcontrol')
  {
    try
    {      
      $commonClass = new CommonClass();
      $apiClass = new ApiClass();

      $vat_reg_id = $vatreg->vat_reg_id;

      $storage_path = storage_path('app/public/');
   
      $currencycode = '';
      $vat_rate = 0;      
      if($vatreg->country == "DK")  
      {   
        $currencycode = "DKK";          
        $vat_rate = 25;  
      }
      elseif($vatreg->country == "NO")
      {
        $currencycode = "NOK";
        $vat_rate = 25;  
      }
      elseif($vatreg->country == "SJ")
      {
        $currencycode = "NOK";
        $vat_rate = 0;  
      }//Svalbard
      elseif($vatreg->country == "SE") 
      {
        $currencycode = "SEK";
        $vat_rate = 25; 
      }
      elseif($vatreg->country == "GB")
      {
        $currencycode = "GBP";          
        $vat_rate = 20; 
      }
      elseif($vatreg->country == "IN")  
        $currencycode = "INR";          
      elseif($vatreg->country == "FR")
      {      
        $currencycode = "EUR";
        $vat_rate = 20; 
      }
      elseif($vatreg->country == "CH")  
      {    
        $currencycode = "CHF";  
        $vat_rate = 8.1;  
      }
      
      if($groupfile)
        $downloadurls = $downloadurlgroup;
      else  
        $downloadurls[0] = $downloadurlgroup;
      
      $excelData = [];
      $sheetlist = [];
      foreach ($downloadurls as $urlkey => $downloadurl_control) 
      {         
        $downloadurl = $downloadurl_control[0];

        $anyexceltemplate = $commonClass->getAnyExcelTemplates($downloadurl['anyexcel_template_id']);
        $sheetcolumns = json_decode($anyexceltemplate->columns);  

        /* -- SAVE ORIGNAL FILE TO STORAGE -- */            
        $original_file = $downloadurl['download_url'];
        $original_file_name = $downloadurl['name'];
        $original_file_extension = $downloadurl['file_extension'];

        $contents = (strpos($original_file, "https://") !== false) ? file_get_contents($original_file) : $original_file;        
        
        $original_filename = Str::random(10) . '.' . $original_file_extension;
        
        Storage::disk('public')->put($original_filename, $contents);
        /* --end SAVE ORIGNAL FILE TO STORAGE -- */

        /* -- READ ORIGNAL FILE -- */ 
        $original_inputFileName = $storage_path . $original_filename;
        $original_spreadsheet = IOFactory::load($original_inputFileName);
        
        $original_sheetCount = $original_spreadsheet->getSheetCount();       
        /* --end READ ORIGNAL FILE -- */         
        
        /* -- GET EXCEL COLUMNS -- */       
        $excel_columns = $commonClass->listExcelColumns();
        /* --/ GET EXCEL COLUMNS -- */ 

        /* -- ORIGNAL FILE SHEETS -- */      
        $same_sheet = false;       
        $samesheet_highestRow = 0;
       
        /* -- BOTH TEMPLATE SHEET AND UPLOADED FILE SHEET COUNT SHOULD BE EQUAL -- */
        if(count($sheetcolumns) == $original_sheetCount)
        {
          /* -- for ORIGNAL FILE SHEETS -- */ 
          for ($i = 0; $i < $original_sheetCount; $i++) 
          { 
            $no_mapping_columns = [];   
               
            $original_activeSheet = $original_spreadsheet->getSheet($i);

            $original_highestRow = $original_activeSheet->getHighestRow();
            $original_highestColumn = $original_activeSheet->getHighestColumn();
  
            $original_sheetName = $original_activeSheet->getTitle();          
                        
            $filter_sheet = $sheetcolumns[$i];  

            if(isset($filter_sheet->data_index))
            {
              $data_index = $filter_sheet->data_index;
              $columns = $filter_sheet->columns;                    
              $sheet_name = $filter_sheet->sheet_name; 
              
              /* -- SHEET EXACT HIGHEST ROW -- */
              $chunkSize = 1000;
              $startRow = $data_index;
              $exact_highestRow = 0;
              /* -- do CHUNKS OF DATAS TO CACULATE THE TOTAL ROWS -- */
              do 
              {
                $endRow = min($startRow + $chunkSize - 1, $original_highestRow);

                /* --for CHUNKS OF DATAS TO CACULATE THE TOTAL ROWS-- */
                for ($row = $startRow; $row <= $endRow; $row++) 
                {                  
                  $rowData = $original_activeSheet->rangeToArray('A' . $row . ':' . $original_highestColumn . $row);

                  /* -- FILTER NON-EMPTY ROWS -- */
                  $filter_rowData = array_filter($rowData, function ($item) {         
                    $filter_item = array_filter($item, function($value) { return (!is_null($value) && $value !== ''); });

                    if (!empty($filter_item))
                      return $filter_item;         
                  });
                  /* --end FILTER NON-EMPTY ROWS -- */

                  if (!empty($filter_rowData))              
                    $exact_highestRow++;
                }/* --end for CHUNKS OF DATAS TO CACULATE THE TOTAL ROWS -- */

                $startRow = $endRow + 1;

              } while ($startRow <= $original_highestRow); 
              /* --end while CHUNKS OF DATAS TO CACULATE THE TOTAL ROWS -- */

              $exact_highestRow = $exact_highestRow + $data_index;
              /* --end SHEET EXACT HIGHEST ROW -- */
             
              /* -- SHEET HAS MULTIPLE MAPPING FOR SAME COLUMN -- */              
              $filter_same_column_mapped = collect($columns)
                              ->pluck('mapped_column')
                              ->filter()
                              ->countBy()                            
                              ->filter(fn($count) => $count > 1)                             
                              ;
              /* --end SHEET HAS MULTIPLE MAPPING FOR SAME COLUMN -- */  
             
              $invoice_date_column_name = '';
              $invoice_no_column_name = '';
              $currency_code_column_name = '';
            
              /* -- for SYSTEM DEFAULT EXCEL COLUMNS -- */
              foreach($excel_columns as $excel_column_key => $excel_column)
              {
                $excel_column_names = explode(':', $excel_column_key);                
                $excel_column_name = (count($excel_column_names) > 0) ? $excel_column_names[0] : '';
               
                if($excel_column_name != "" && ($excel_column_name == "B" || $excel_column_name == "C" || $excel_column_name == "D" || $excel_column_name == "G"))
                {
                  $filter_columns = array_values(array_filter($columns, function ($column) use ($excel_column_key) {
                    return $excel_column_key == $column->mapped_column;
                  }));

                  if(count($filter_columns) > 0)
                  {                    
                    $chunkSize = 1000;
                    $startRow = $data_index;

                    /* -- do CHUNKS OF DATAS TO LOOP ROWS -- */
                    do 
                    {
                      $endRow = min($startRow + $chunkSize - 1, $exact_highestRow);

                      /* --for CHUNKS OF DATAS TO LOOP ROWS -- */
                      for ($row = $startRow; $row <= $endRow; $row++) 
                      {                       
                        /* -- SKIP HIDDEN ROWS -- */
                        if (!$original_activeSheet->getRowDimension($row)->getVisible())
                          continue;
                        /* --end SKIP HIDDEN ROWS -- */

                        $rowData = $original_activeSheet->rangeToArray('A' . $row . ':' . $original_highestColumn . $row);
                      
                        /* -- FILTER NON-EMPTY ROWS -- */
                        $filter_rowData = array_filter($rowData, function ($item) {         
                          $filter_item = array_filter($item, function($value) { return (!is_null($value) && $value !== ''); });

                          if (!empty($filter_item))
                            return $filter_item;         
                        });
                        /* --end FILTER NON-EMPTY ROWS -- */

                        /* -- if FILTERED DATA ROWS -- */
                        if (!empty($filter_rowData))                     
                        {      
                          /* -- SHEET HAS MULTIPLE MAPPING FOR SAME COLUMN -- */                         
                          $filter_multiple_columns = array_filter($columns, function ($column) use ($filter_same_column_mapped) {
                            $compare_key = $column->mapped_column;
                            return $filter_same_column_mapped->has($compare_key);                            
                          }); 
                          /* --end SHEET HAS MULTIPLE MAPPING FOR SAME COLUMN -- */  

                          $column_position = [];
                          $position_index = 0;
                          $column_position_name = '';
                          if(count($filter_multiple_columns) > 1)                        
                          {                                                        
                            foreach($filter_multiple_columns as $filter_multiple_column)
                            {
                              $multiple_column_name = str_replace('Column ', '', $filter_multiple_column->column); 

                              $column_value = trim($original_activeSheet->getCell($multiple_column_name.$row)->getFormattedValue());
                              
                              if($column_value != '' && $column_value != '-')
                              {
                                if((stripos($column_value, "-") !== false))
                                {                                
                                  $column_position[] = $position_index;                                  
                                }
                                else
                                  $column_position[] = $position_index;

                                $column_position_names = explode(':', $filter_multiple_column->mapped_column);                           
                                $column_position_name = (count($column_position_names) > 0) ? $column_position_names[0] : '';
                              }
                              $position_index++;
                            } /* --FOR MULTIPLE MAPPING FOR SAME COLUMN -- */                                                 
                          } /* --if MULTIPLE MAPPING FOR SAME COLUMN -- */

                          $read_upto = 1;
                          if(isset($column_position))
                            $read_upto = (count($column_position) + (($groupfile) ? 1 : 0));
                          
                          if($read_upto == 0)
                              $read_upto = 1;    
                          
                          for ($j = 0; $j < $read_upto; $j++) 
                          {                              
                            if(isset($column_position) && isset($column_position_name) && $excel_column_name == $column_position_name)
                            {
                              $_position = $column_position[$j];
                              $filter_column = $filter_columns[$_position];
                            } /* --end if E COLUMN -- */  
                            else  
                              $filter_column = $filter_columns[0];                           

                            $original_column_name = str_replace('Column ', '', $filter_column->column); 

                            $mapping_column_names = explode(':', $filter_column->mapped_column);                           
                            $mapping_column_name = (count($mapping_column_names) > 0) ? $mapping_column_names[0] : '';

                            $original_value = trim($original_activeSheet->getCell($original_column_name.$row)->getFormattedValue());
                           
                            $original_value = ($original_value == '-' || $original_value == '') ? 0 : $original_value;
                                                  
                            if($excel_column_name == "B")
                            {  
                              $invoice_date_column_name = $original_column_name;                                                      
                            } /* --end if COLUMN B - INVOICE DATE -- */    
                            else if($excel_column_name == "C")
                            {  
                              $invoice_no_column_name = $original_column_name;                                                      
                            } /* --end if COLUMN C - INVOICE NO. -- */    
                            else if($excel_column_name == "D")
                            {  
                              $currency_code_column_name = $original_column_name;                                                      
                            } /* --end if COLUMN D - CURRENCY CODE -- */                               
                            else if($excel_column_name == "G")
                            { 
                              $original_value = $commonClass->floatvalue($original_value);                              
                            } /* --end if COLUMN G - FLOAT FORMAT -- */                            

                            /* -- REVERSE -- */
                            $acc_reverse = 1;
                            if(isset($filter_column->reverse))
                            {
                              if (str_starts_with($original_value, '-'))                              
                                $acc_reverse = -1;
                              
                              $original_value = $acc_reverse * $original_value;
                            }
                            /* --end REVERSE -- */

                            /* -- FORMULA -- */
                            $vat_percentage = '';
                            $vat_amount = '';
                            if(isset($filter_column->formula))
                            {     
                              $formula_value = $filter_column->formula; 

                              $arr_formula = explode(' ', $formula_value);

                              $formula = '';
                              foreach ($arr_formula as $key => $value) 
                              {                                                          
                                $single_value = str_replace(['(', ')'], '', $value);                              
                                if (preg_match('/^[A-Z]$/', $single_value))
                                {
                                  if($original_column_name == $single_value)
                                  {
                                    if($arr_formula[$key + 1] == '/' &&
                                      filter_var(str_replace(['(', ')'], '', $arr_formula[$key + 2]), FILTER_VALIDATE_FLOAT) !== false
                                    )
                                      $vat_percentage = str_replace(['(', ')'], '', trim($arr_formula[$key + 2])) * 100;
                                    else
                                    {                                                                            
                                      preg_match('/\d+(\.\d+)?/', trim($original_activeSheet->getCell($single_value.($data_index-1))->getFormattedValue()), $matches);
                                      $vat_percentage = $matches[0] ?? null;
                                    
                                      $vat_amount = $commonClass->floatvalue(trim($original_activeSheet->getCell($single_value.$row)->getFormattedValue()));
                                    }
                                 
                                    $arr_formula[$key] = str_replace($single_value, trim($original_value), $arr_formula[$key]);
                                  }
                                  else
                                  {
                                    $replace_value = $commonClass->floatvalue(trim($original_activeSheet->getCell($single_value.$row)->getFormattedValue()));
                                                                    
                                    $replace_value = ($arr_formula[$key - 1] == '/' && $replace_value == 0) ? 1 : $replace_value;
                                    $arr_formula[$key] = str_replace($single_value, $replace_value, $arr_formula[$key]);
                                  } /* --end else REPLACE RELEVANT COLUMN VALUE -- */                                  
                                } /* --end if FORMULA COLUMN IS CHARACTER -- */
                                else if(preg_match('/<span>.*?<\/span>/i', $single_value))
                                {                                  
                                  $chars = str_split($single_value);
                                  foreach ($chars as $char) 
                                  {
                                    if (preg_match('/^[A-Z]$/', $char))
                                    {
                                      $replace_value = $commonClass->floatvalue($original_activeSheet->getCell($char.$row)->getFormattedValue());
                                      $arr_formula[$key] = str_replace($char, trim($replace_value), $arr_formula[$key]);
                                    } /* --end if CHAR IS CHARACTER -- */
                                  } /* --end for CHAR -- */

                                  $arr_formula[$key] = preg_replace('/<\/?span[^>]*>/i', '', $arr_formula[$key]);
                                } /* --end if FORMULA COLUMN IS VALUE WITH FORMULA -- */
                              } /* --end for FORMULA -- */

                              $expressionLanguage = new ExpressionLanguage();
                              $formula = implode('', $arr_formula);
                              $original_value = $expressionLanguage->evaluate($formula);  
                             
                              if(filter_var($original_value, FILTER_VALIDATE_FLOAT) !== false)
                                $original_value = $commonClass->floatvalue(number_format($original_value, 2));                             
                            } /* --end if FORMULA -- */
                            /* --end FORMULA -- */
 
                            /* -- CHECK VALUE IS IN INVOICE TABLE -- */                            
                            if($excel_column_name == "G")
                            { 
                              $invoice_date = '';
                              $original_invoice_date = trim($original_activeSheet->getCell($invoice_date_column_name.$row)->getFormattedValue());

                              if (isset($original_invoice_date))
                              {                               
                                $carbonDate = NULL;
                                if (preg_match('/^\d{8}$/', $original_invoice_date))
                                  $carbonDate = Carbon::createFromFormat('Ymd', $original_invoice_date);
                                elseif (preg_match('/^\d{2}-\d{2}-\d{4}$/', $original_invoice_date))
                                  $carbonDate = Carbon::createFromFormat('d-m-Y', $original_invoice_date);                               
                                elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $original_invoice_date))
                                  $carbonDate = Carbon::createFromFormat('Y-m-d', $original_invoice_date);
                                elseif (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $original_invoice_date))
                                  $carbonDate = Carbon::createFromFormat('n/j/Y', $original_invoice_date);

                                if ($carbonDate instanceof Carbon)
                                  $invoice_date = $carbonDate->format('Y-m-d');                                
                              }

                              $invoice_no = trim($original_activeSheet->getCell($invoice_no_column_name.$row)->getFormattedValue());
                              $currency_code = trim($original_activeSheet->getCell($currency_code_column_name.$row)->getFormattedValue());
                              if($currency_code == '')
                                $currency_code = $currencycode;

                              if($invoice_no != '')
                              {                                
                                if (!isset($excelData[$invoice_no]))                                
                                  $excelData[$invoice_no] =  ['invoice_date' => '', 'vat_amount' => 0];
                                
                                $excelData[$invoice_no]['invoice_date'] = $invoice_date;
                                $excelData[$invoice_no]['vat_amount'] += (float)$original_value;

                                $net_amount = ((float)$original_value * 100) / $vat_rate;
                                $excelData[$invoice_no]['net_amount'] = $net_amount;

                                $excelData[$invoice_no]['file_id'] = $downloadurl['file_id'];
                              }                              
                            } /* --end if COLUMN B - VAT AMOUNT -- */ 
                            /* --end CHECK VALUE IS IN INVOICE TABLE -- */                            
                          } /* --end for READ UPTO -- */
                        } /* --end if FILTERED DATA ROWS -- */
                      } /* --end for CHUNKS OF DATAS TO LOOP ROWS -- */

                      $startRow = $endRow + 1;                        
                    } while ($startRow <= $exact_highestRow);  
                    /* -- while CHUNKS OF DATAS TO LOOP ROWS -- */
                  } /* --end if FILTER COLUMN -- */
                  else                  
                    $no_mapping_columns[] = $excel_column_name;                     
                } /* --end if SYSTEM DEFAULT EXCEL COLUMN NAME -- */
              } /* --end for SYSTEM DEFAULT EXCEL COLUMNS -- */                                           
            } /* --end if ORIGNAL FILE HAS ACTIVE SHEET -- */ 
          } /* --end for ORIGNAL FILE SHEETS -- */
        } /* --end if BOTH TEMPLATE SHEET AND UPLOADED FILE SHEET COUNT SHOULD BE EQUAL -- */  
        else
          /* -- RETURN JSON -- */
          return [   
              'status' => 'Error',                 
              'message' => "The uploaded file doesn't match with the choosen template" 
          ];
          /* --end RETURN JSON -- */ 
        /* --end BOTH TEMPLATE SHEET AND UPLOADED FILE SHEET COUNT SHOULD BE EQUAL -- */ 
      } /* --end for GROUP FILES -- */

      /* -- GET MISSING INVOICES -- */  
      if($type == 'ircontrol')
      {
        $invoiceCollection = ImportReconciliationSalesInvoices::where('vat_reg_id', $vat_reg_id)
                                ->get();
      }
      else
      {
        $invoiceCollection = Invoices::where('vat_reg_id', $vat_reg_id)                                       
                                ->get();
      }

      // Index both collections by 'invoice_no' for fast lookup
      $dbInvoices = $invoiceCollection->keyBy('invoice_no');   

      //VAT Control Excel Data  
      $excelInvoices = collect($excelData);

      // Find missing in Excel
      $missingInExcel = $dbInvoices->keys()->diff($excelInvoices->keys());

      // Find missing in DB (i.e., extra in Excel)
      $missingInDB = $excelInvoices->keys()->diff($dbInvoices->keys());

      // Find amount mismatches
      $amountMismatch = $dbInvoices->filter(function ($item, $invoiceNo) use ($excelInvoices) {
        return isset($excelInvoices[$invoiceNo]) && $item['total_vat'] != $excelInvoices[$invoiceNo]['vat_amount'];       
      });

      // Optional: Collect mismatches nicely
      $mismatchedDetails = $amountMismatch->map(function ($item, $invoiceNo) use ($excelInvoices) {
        // $difference_vat_amount = $item['vat_amount'] - $item['vatcontrol_vat_amount'];
        // $difference_percent = ($item['vatcontrol_vat_amount'] != 0) ? ($difference_vat_amount / $item['vatcontrol_vat_amount']) * 100 : 0;

        $difference_vat_amount = $item['total_vat'] - $excelInvoices[$invoiceNo]['vat_amount'];
        $difference_percent = ($excelInvoices[$invoiceNo]['vat_amount'] != 0) ? ($difference_vat_amount / $excelInvoices[$invoiceNo]['vat_amount']) * 100 : 0;

          return [
              'file_id' => $excelInvoices[$invoiceNo]['file_id'],

              'invoice_type' => ucfirst($item['invoice_type']),
              'invoice_no' => $invoiceNo,
              'invoice_date' => $item['invoice_date'],             
              'net_amount' => $item['total_net'],
              'vat_amount' => $item['total_vat'],
              'currency_code' => $item['currency_code'],

              'control_invoice_date' => $excelInvoices[$invoiceNo]['invoice_date'],             
              'control_net_amount' => $excelInvoices[$invoiceNo]['net_amount'],
              'control_vat_amount' => $excelInvoices[$invoiceNo]['vat_amount'],                 
              
              'difference_vat_amount' => $difference_vat_amount,
              'difference_percent' => $difference_percent
          ];
      });      
      /* --end GET MISSING INVOICES -- */

      /* -- MISSING INVOICE EXCEL -- */
      if(count($mismatchedDetails) > 0)
      {         
        /* -- DELETE IN PUBLIC FOLDER -- */
        Storage::disk('public')->delete($original_filename);           
        /* --end DELETE IN PUBLIC FOLDER -- */

        /* -- FINALLY SAVE -- */       
        try 
        { 
          /* -- CREATE MISSING EXCEL -- */
          // Create new Spreadsheet object
          $spreadsheet = new Spreadsheet();
          $sheet = $spreadsheet->getActiveSheet();

          $sheet->getColumnDimension('A')->setWidth(10);
          $sheet->getColumnDimension('B')->setWidth(15);
          $sheet->getColumnDimension('C')->setWidth(15);
          $sheet->getColumnDimension('D')->setWidth(15);
          $sheet->getColumnDimension('E')->setWidth(15);
          $sheet->getColumnDimension('F')->setWidth(2);
          $sheet->getColumnDimension('G')->setWidth(15);
          $sheet->getColumnDimension('H')->setWidth(15);

          // Define header style
          $headerStyle = [
              'font' => [
                  'bold' => true,
                  'color' => ['argb' => Color::COLOR_WHITE],
                  'size' => 12,
              ],
              'fill' => [
                  'fillType' => Fill::FILL_SOLID,
                  'startColor' => ['argb' => 'FF4F81BD'], // blue background
              ],
              'alignment' => [
                  'horizontal' => Alignment::HORIZONTAL_CENTER,
                  'vertical' => Alignment::VERTICAL_CENTER,
              ],
          ];

          // Apply style to header row A1 to H1
          $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

          // Optional: Set row height or column width if needed
          //$sheet->getRowDimension(1)->setRowHeight(25);

          // Set headers
          $sheet->setCellValue('A1', 'Invoice Type');
          $sheet->setCellValue('B1', 'Invoice Number');
          $sheet->setCellValue('C1', 'Invoice Date');
          $sheet->setCellValue('D1', 'NET Amount');
          $sheet->setCellValue('E1', 'VAT Amount');
          $sheet->setCellValue('F1', '.');
          $sheet->setCellValue('G1', 'Currency');
          $sheet->setCellValue('H1', 'Difference');
          
          // Start from row 2
          $row = 2;
          $file_ids = [];
          foreach ($mismatchedDetails as $item) {
            $sheet->setCellValue("A{$row}", $item['invoice_type']);
            $sheet->setCellValue("B{$row}", $item['invoice_no']);
            $sheet->setCellValue("C{$row}", $item['invoice_date']);
            $sheet->setCellValue("D{$row}", $item['net_amount']);
            $sheet->setCellValue("E{$row}", $item['vat_amount']);
            $sheet->setCellValue("F{$row}", '');
            $sheet->setCellValue("G{$row}", $item['currency_code']);
            $sheet->setCellValue("H{$row}", $item['difference_vat_amount']);
            
            // Check for >5% change
            if (abs($item['difference_percent']) > 5)
              //$sheet->getStyle("H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_RED);
              $sheet->getStyle("H{$row}")->getFont()->getColor()->setARGB(Color::COLOR_RED);
            
            if(!in_array($item['file_id'], $file_ids, true))              
              array_push($file_ids, $item['file_id']);
            
            $row++;
          }
                        
          $newFileName = $vat_reg_id . '.xlsx';    
          $newFilePath = $storage_path . $newFileName;

          $writer = new WriterXlsx($spreadsheet);
          $writer->save($newFilePath); 
          /* --end CREATE MISSING EXCEL -- */       

          /* -- STORE MAPPED FILE IN VAT CONTROL FILES -- */
          $apiClass =  new ApiClass();

          $system = $commonClass->getSystemInfoLazy();
          $systemapi = $system->systemapi->first();
         
          $filecontent = file_get_contents($newFilePath);
          //$file[0] = $filecontent;
         
          $file = [
              'file_ids' => $file_ids,
              'file' => $filecontent,
              'file_type' => $type, //'vatcontrol',
              'file_type_title' => ($type == 'ircontrol') ? 'Import Reconciliation Control' : 'VAT Control'
          ];

          $fileDetails = $apiClass->uploadFileToOneDriveLazy($file, $vatreg, $authUser, $systemapi);                
          /* --end STORE MAPPED FILE IN VAT CONTROL FILES -- */

          /* -- DELETE FROM PUBLIC FOLDER -- */              
          Storage::disk('public')->delete($newFileName);  
          /* --end DELETE FROM PUBLIC FOLDER -- */   

          /* -- RETURN JSON -- */
          return [   
              'status' => 'Success',                 
              'fileDetails' => $fileDetails[0]
          ];
          /* --end RETURN JSON -- */ 
        } 
        catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) 
        {
          /* -- RETURN JSON -- */
          return response()->json([   
              'status' => 'Error',                 
              'message' => $e->getMessage()
          ]);
          /* --end RETURN JSON -- */ 
        }        
        /* -- FINALLY SAVE -- */
      } /* --end if HAS MISSING ROWS -- */
      else
      {
        /* -- RETURN JSON -- */
        return [   
            'status' => 'Success',                 
            'message' => 'No missing invoices'
        ];
            /* --end RETURN JSON -- */
      } /* --end if NO MISSING ROWS -- */
      /* --end MISSING INVOICE EXCEL -- */
    }
    catch (\Exception $e) 
    {     
      dd($e);      
      
      /* -- RETURN JSON -- */
      return [   
          'status' => 'Error',                 
          'message' => $e->getMessage()
      ];
      /* --end RETURN JSON -- */    
    } 
  }
  /* --end COMPARE VAT RETURN CONTROL EXCEL - ANYEXCEL -- */
}
