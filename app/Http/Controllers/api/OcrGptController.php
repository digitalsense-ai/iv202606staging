<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

use App\Http\Controllers\api\BaseController as BaseController;

use App\Models\OcrPdf;


class OcrGptController extends BaseController
{  
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */    
  public function __construct()
  {
      $this->middleware('auth:sanctum');
  }  
  
  public function index(Request $request, $org_no)
  {
      $normalizedOrgNo = str_replace(' ', '', $org_no);
    // Fetch invoices by supplier or recipient org_number
      $invoices = OcrPdf::select('extracted_data')
                  //->where('extracted_data->supplier->org_number', $org_no)
                  //->orWhere('extracted_data->recipient->org_number', $org_no)
                  ->whereRaw(
                      "REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.supplier.org_number')), ' ', '') = ?",
                      [$normalizedOrgNo]
                  )
                  ->orWhereRaw(
                      "REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.recipient.org_number')), ' ', '') = ?",
                      [$normalizedOrgNo]
                  )
                  ->get();
dd($invoices);
      $flattened = $invoices->map(function($item) {
          return json_decode($item->extracted_data, true); // return only decoded array
      });
      
      return response()->json($flattened);
  }

  public function showForClient(Request $request, $org_no)
  {
      $user = $request->user(); // Authenticated via Bearer token

      // Check token ability
      if (!$request->user()->tokenCan('ocr-read')) {
          return response()->json(['error'=>'Forbidden'], 403);
      }
      
      $normalizedOrgNo = str_replace(' ', '', $org_no);

      $query = OcrPdf::where(function($q) use ($normalizedOrgNo) {
          $q->whereRaw(
              "REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.supplier.org_number')), ' ', '') = ?",
              [$normalizedOrgNo]
          )->orWhereRaw(
              "REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.recipient.org_number')), ' ', '') = ?",
              [$normalizedOrgNo]
          );
      });

      if ($request->has('from_date')) {
          $query->whereRaw(
              "JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.invoice_date')) >= ?",
              [$request->from_date]
          );
      }
      if ($request->has('to_date')) {
          $query->whereRaw(
              "JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.invoice_date')) <= ?",
              [$request->to_date]
          );
      }

      // Read page from request
      $page = (int) $request->query('page', 1);
      $perPage = min((int) $request->query('per_page', 10), 50);

      $paginated = $query->paginate($perPage, ['*'], 'page', $page);

      // Transform data (flatten JSON)
      $data = collect($paginated->items())->map(function ($item) {
          return json_decode($item->extracted_data, true);
      });
      
      // Return structured response (VERY IMPORTANT for GPT)
      return response()->json([
          'data' => $data,
          'meta' => [
              'current_page' => $paginated->currentPage(),
              'last_page' => $paginated->lastPage(),
              'per_page' => $paginated->perPage(),
              'total' => $paginated->total(),
          ]
      ]);
  }
}
