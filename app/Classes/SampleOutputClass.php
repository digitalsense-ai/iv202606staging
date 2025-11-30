<?php

namespace App\Classes;

class SampleOutputClass
{        
   public function pdftextoutput($format)
    {
//PIVS-May 
$result['pivs-may'] =           
 [ 
  "statement" => [
    "summary" => [
      "period" => "31 May 2023",
      "total" => "£901.93"
    ],
    "company" => [
      "name" => "Nordic Sporting Clays ApS",
      "address" => "IntraVAT, Ventrupvej 6, Greve, 2670",
      "VAT registration number" => "437386566",
      "EORI number" => "GB437386566000"
    ],
    "important document" => "Keep this statement as proof of import VAT postponed for your VAT return",
    "date produced" => "8 June 2023",
    "page" => "1"
  ],
  "declarants" => [
    0 => [
      "EORI number" => "GB720660854000",
      "name" => "CUSTOMS CLEARANCE LIMITED",
      "total postponed" => "£67.80"
    ],
    1 => [
      "EORI number" => "GB759894254003",
      "name" => "DSV ROAD LIMITED",
      "total postponed" => "£834.13"
    ]
  ],
  "page" => "2",
  "date of import" => [
    0 => [
      "date" => "26/05/2023",
      "movement reference number" => "23GB5RV8CVP8VTNAR3",
      "declarant" => "DSV ROAD LIMITED",
      "declarant's EORI number" => "GB759894254003",
      "declarant's reference number" => "HORUK-L5554",
      "VAT due" => "£834.13"
    ],
    1 => [
      "date" => "27/05/2023",
      "movement reference number" => "23GB5QD1THGZBS6AR0",
      "declarant" => "CUSTOMS CLEARANCE LIMITED",
      "declarant's EORI number" => "GB720660854000",
      "declarant's reference number" => "GLS24547600BL",
      "VAT due" => "£67.80"
    ]
  ],
  "month total" => "£901.93"
];

//PIVS-June
$result['pivs-june'] = [ 
  "statement_date" => "10 July 2023",
  "company_name" => "Nordic Sporting Clays ApS",
  "address" => "IntraVAT, Ventrupvej 6, Greve, 2670",
  "VAT_registration_number" => "437386566",
  "EORI_number" => "GB437386566000",
  "total_VAT_postponed" => "£922.32",
  "declarants" => [
    0 => [
      "declarant_EORI" => "GB720660854000",
      "declarant_name" => "CUSTOMS CLEARANCE LIMITED",
      "total_postponed_VAT" => "£493.43"
    ],
    1 => [
      "declarant_EORI" => "GB759894254003",
      "declarant_name" => "DSV ROAD LIMITED",
      "total_postponed_VAT" => "£428.89"
    ]
  ],
  "import_details" => [
    0 => [
      "date_of_import" => "01/06/2023",
      "MRN" => "23GB5XHIGZ07C89AR6",
      "declarant_EORI" => "GB720660854000",
      "declarant_reference_number" => "GLS24564900BF",
      "VAT_due" => "£29.56"
    ],
    1 => [
      "date_of_import" => "02/06/2023",
      "MRN" => "23GB5YX6T2KBB6IAR7",
      "declarant_EORI" => "GB720660854000",
      "declarant_reference_number" => "GLS24571800AE",
      "VAT_due" => "£195.89"
    ],
    2 => [
      "date_of_import" => "05/06/2023",
      "MRN" => "23GB65WAT0JXNRPAR0",
      "declarant_EORI" => "GB759894254003",
      "declarant_reference_number" => "HORUK-L6349",
      "VAT_due" => "£428.89"
    ],
    3 => [
      "date_of_import" => "08/06/2023",
      "MRN" => "23GB67JNV1VZZO3AR8",
      "declarant_EORI" => "GB720660854000",
      "declarant_reference_number" => "GLS24603400AU",
      "VAT_due" => "£115.80"
    ],
    4 => [
      "date_of_import" => "10/06/2023",
      "MRN" => "23GB6AKAIOQMIB7AR0",
      "declarant_EORI" => "GB720660854000",
      "declarant_reference_number" => "GLS24618100AF",
      "VAT_due" => "£122.69"
    ],
    5 => [
      "date_of_import" => "23/06/2023",
      "MRN" => "23GB6SVZ83V53K4AR5",
      "declarant_EORI" => "GB720660854000",
      "declarant_reference_number" => "GLS24684000AG",
      "VAT_due" => "£29.49"
    ]
  ]
];

//PIVS-July
$result['pivs-july'] = [
  "statement" => [
    "summary" => [
      "period" => "31 July 2023",
      "date" => "8 August 2023",
      "total" => "£23,795.75"
    ],
    "company" => [
      "name" => "DESIGNBROKERS HOSPITALITY DK APS",
      "VAT registration number" => "423909296",
      "EORI number" => "GB423909296000"
    ],
    "important document" => "Keep this statement as proof of import VAT postponed for your VAT return"
  ],
  "page 1" => [
    "date" => "8 August 2023",
    "page number" => "1",
    "address" => "HM REVENUE AND CUSTOMS RUBY HOUSE 8 - AB10 1ZP",
    "VAT registration number" => "423909296",
    "EORI number" => "GB423909296000",
    "total VAT postponed" => "£23,795.75"
  ],
  "page 2" => [
    "date" => "8 August 2023",
    "page number" => "2",
    "company" => "DESIGNBROKERS HOSPITALITY DK APS",
    "VAT registration number" => "423909296",
    "EORI number" => "GB423909296000",
    "important document" => "Keep this statement as proof of import VAT postponed for your VAT return",
    "imports" => [
      0 => [
        "date of import" => "07/07/2023",
        "movement reference number" => "23GB7CTFEO2ZC7TAR3",
        "declarant" => "LEMAN INTERNATIONAL",
        "declarant's EORI number" => "GB974696748000",
        "declarant's reference number" => "986336",
        "VAT due" => "£8,147.66"
      ],
      1 => [
        "date of import" => "25/07/2023",
        "movement reference number" => "23GB876ZG7O8LQIAR2",
        "declarant" => "DHL INTERNATIONAL (UK) LIMIT ED",
        "declarant's EORI number" => "GB751812341004",
        "declarant's reference number" => "6755073474",
        "VAT due" => "£43.47"
      ],
      2 => [
        "date of import" => "27/07/2023",
        "movement reference number" => "23GB86VOC2X1YXSAR3",
        "declarant" => "LEMAN INTERNATIONAL",
        "declarant's EORI number" => "GB974696748000",
        "declarant's reference number" => "990206",
        "VAT due" => "£6,348.38"
      ],
      3 => [
        "date of import" => "27/07/2023",
        "movement reference number" => "23GB84236U7726TAR5",
        "declarant" => "LEMAN INTERNATIONAL",
        "declarant's EORI number" => "GB974696748000",
        "declarant's reference number" => "989620",
        "VAT due" => "£9,256.24"
      ]
    ],
    "month total" => "£23,795.75"
  ]
];

//Committee-xxiv - commercial_invoice_1411
$result['ci-1'] = [
  "invoice_number" => "1411",
  "date" => "2024-08-26",
  "collie" => "14",
  "delivery_term" => "DAP",
  "customs_credit_number" => "33479445",
  "combined_per_style" =>[
    0 => [
      "style_number" => "I234424IVY-Tara",
      "style_name" => "Jeans Wash Cool Excellent Black",
      "quality" => "Cotton 76% Polyester 22% Elastan 2%",
      "tariff" => "6104620000TR T2",
      "origin" => "1,5",
      "kg_qty" => "5",
      "price_nok_t" => "433,00",
      "price_nok" => "2.165,00"
    ],
    1 =>[
      "style_number" => "I234856IVY-Johanna",
      "style_name" => "Jeans Color SS24",
      "quality" => "Cotton 91% Polyester 7% Elastane 2%",
      "tariff" => "6104620000TR T2",
      "origin" => "0,3",
      "kg_qty" => "1",
      "price_nok_t" => "540,86",
      "price_nok" => "540,86"
    ],
    2 => [
      "style_number" => "I234975IVY-Johanna",
      "style_name" => "Jeans Excl. Greece Dark Blue",
      "quality" => "Reused Cotton 89% Polyester 8% Elastane 3%",
      "tariff" => "6104620000TR T2",
      "origin" => "0,3",
      "kg_qty" => "1",
      "price_nok_t" => "1.106,46",
      "price_nok" => "1.106,46"
    ],
    3 => [
      "style_number" => "I235012IVY-Tonya",
      "style_name" => "Jeans Wash Salamanca",
      "quality" => "Cotton 54% Recycled Polyester 25% Reused Cotton 19% Elastane 2%",
      "tariff" => "6104620000TR T2",
      "origin" => "0,3",
      "kg_qty" => "1",
      "price_nok_t" => "1.106,46",
      "price_nok" => "1.106,46"
    ],
    4 => [
      "style_number" => "I235031IVY-Ann Charlotte",
      "style_name" => "Earth Jeans Wash Cayenne",
      "quality" => "Recycled Polyester 50%, 31% Cotton, 17% Reused Cotton, 2% Elastane",
      "tariff" => "6104620000TR T2",
      "origin" => "0,3",
      "kg_qty" => "1",
      "price_nok_t" => "500,00",
      "price_nok" => "500,00"
    ],
    5 => [
      "style_number" => "I235040IVY-Augusta",
      "style_name" => "Oversize Jacket Wash Istanbul Black",
      "quality" => "Cotton 99% Elastan 1%",
      "tariff" => "6202920090TR T2",
      "origin" => "0,3",
      "kg_qty" => "1",
      "price_nok_t" => "583,10",
      "price_nok" => "583,10"
    ],
    6 => [
      "style_number" => "T1085 TRW-Jenna",
      "style_name" => "Jeans Wash Prato",
      "quality" => "Organic cotton 93,07% Recycled polyester 4,84% Elastan 2,09%",
      "tariff" => "6104620000TR T2",
      "origin" => "31,2104",
      "kg_qty" => "1",
      "price_nok_t" => "1.015,42",
      "price_nok" => "105.604,10"
    ],
    7 => [
      "style_number" => "T1118 Moussa",
      "style_name" => "Hepburn Shirt",
      "quality" => "Cotton 74% Polyamide 22% Elastan 4%",
      "tariff" => "6206300090TR T2",
      "origin" => "1,0",
      "kg_qty" => "1",
      "price_nok_t" => "1.440,00",
      "price_nok" => "1.440,00"
    ]
  ],
  "collect_lines" => [
    0 => [
      "tariff" => "6104620000TR T2",
      "origin" => "33,9113",
      "kg_qty" => "111.022,88"
    ],
    1 => [
      "tariff" => "6202920090TR T2",
      "origin" => "0,3",
      "kg_qty" => "583,10"
    ],
    2 => [
      "tariff" => "6206300090TR T2",
      "origin" => "1,0",
      "kg_qty" => "1.440,00"
    ]
  ],
  "total" => [
    "tariff" => "35,2115",
    "kg_qty" => "113.045,98"
  ],
  "stock_type" => [
    0 => [
      "type" => "T1",
      "kg_qty" => "0,0",
      "price_nok" => "0,00"
    ],
    1 => [
      "type" => "T2",
      "kg_qty" => "35,2115",
      "price_nok" => "113.045,98"
    ]
  ],
  "total_amount" => [
    "amount" => "113.045,98",
    "freight" => "1.561,54",
    "total" => "114.607,52"
  ],
  "commercial_invoice" => "144505, 144512, 313322-313328",
  "proforma_invoice" => "1045-1049",
  "delivery_numbers" => "150757, 150764, 150785, 150802-150804, 150806-150807, 150809, 150811-150812, 150814, 150816, 150818",
  "exporter" => [
    "name" => "Committee XXIV",
    "address" => "Gl. Skartved 11",
    "city" => "Bjert-Kolding",
    "country" => "Denmark",
    "email" => "hello@committee-xxiv.com",
    "phone" => "+45 88 33 88 55",
    "vat_number" => "32772498"
  ],
  "customs_authorization_number" => "DK/20/740775",
  "preferential_origin" => "EEA/Turkish"
];

//Committee-xxiv - commercial_invoice_1411
$result['ci-2'] = [
  "invoice_number" => "1411",
  "date" => "2024-08-26",
  "collie" => "14",
  "delivery_term" => "DAP",
  "customs_credit_number" => "33479445",
  "combined_per_style" => [
    0 => [
      "style_number" => "I234424IVY-Tara",
      "style_name" => "Jeans Wash Cool Excellent Black",
      "quality" => "Cotton 76% Polyester 22% Elastan 2%",
      "tariff" => "6104620000TR T2",
      "origin" => "1,5",
      "kg" => "5",
      "price_nokt" => "433,00",
      "price_nok" => "2.165,00"
    ],
    1 => [
      "style_number" => "I234856IVY-Johanna",
      "style_name" => "Jeans Color SS24",
      "quality" => "Cotton 91% Polyester 7% Elastane 2%",
      "tariff" => "6104620000TR T2",
      "origin" => "0,3",
      "kg" => "1",
      "price_nokt" => "540,86",
      "price_nok" => "540,86"
    ],
    2 => [
      "style_number" => "I234975IVY-Johanna",
      "style_name" => "Jeans Excl. Greece Dark Blue",
      "quality" => "Reused Cotton 89% Polyester 8% Elastane 3%",
      "tariff" => "6104620000TR T2",
      "origin" => "0,3",
      "kg" => "1",
      "price_nokt" => "1.106,46",
      "price_nok" => "1.106,46"
    ],
    3 => [
      "style_number" => "I235012IVY-Tonya",
      "style_name" => "Jeans Wash Salamanca",
      "quality" => "Cotton 54% Recycled Polyester 25% Reused Cotton 19% Elastane 2%",
      "tariff" => "6104620000TR T2",
      "origin" => "0,3",
      "kg" => "1",
      "price_nokt" => "1.106,46",
      "price_nok" => "1.106,46"
    ],
    4 => [
      "style_number" => "I235031IVY-Ann Charlotte",
      "style_name" => "Earth Jeans Wash Cayenne",
      "quality" => "Recycled Polyester 50%, 31% Cotton, 17% Reused Cotton, 2% Elastane",
      "tariff" => "6104620000TR T2",
      "origin" => "0,3",
      "kg" => "1",
      "price_nokt" => "500,00",
      "price_nok" => "500,00"
    ],
    5 => [
      "style_number" => "I235040IVY-Augusta",
      "style_name" => "Oversize Jacket Wash Istanbul Black",
      "quality" => "Cotton 99% Elastan 1%",
      "tariff" => "6202920090TR T2",
      "origin" => "0,3",
      "kg" => "1",
      "price_nokt" => "583,10",
      "price_nok" => "583,10"
    ],
    6 => [
      "style_number" => "T1085 TRW-Jenna",
      "style_name" => "Jeans Wash Prato",
      "quality" => "Organic cotton 93,07% Recycled polyester 4,84% Elastan 2,09%",
      "tariff" => "6104620000TR T2",
      "origin" => "31,2104",
      "kg" => "1",
      "price_nokt" => "1.015,42",
      "price_nok" => "105.604,10"
    ],
    7 => [
      "style_number" => "T1118 Moussa",
      "style_name" => "Hepburn Shirt",
      "quality" => "Cotton 74% Polyamide 22% Elastan 4%",
      "tariff" => "6206300090TR T2",
      "origin" => "1,0",
      "kg" => "1",
      "price_nokt" => "1.440,00",
      "price_nok" => "1.440,00"
    ]
  ],
  "collect_lines" => [
    0 => [
      "tariff" => "6104620000TR T2",
      "origin" => "33,9113",
      "kg" => "111.022,88"
    ],
    1 => [
      "tariff" => "6202920090TR T2",
      "origin" => "0,3",
      "kg" => "583,10"
    ],
    2 => [
      "tariff" => "6206300090TR T2",
      "origin" => "1,0",
      "kg" => "1.440,00"
    ]
  ],
  "total" => [
    "tariff" => "35,2115",
    "kg" => "113.045,98"
  ],
  "stock_type" => [
    0 => [
      "type" => "T1",
      "kg" => "0,0",
      "price_nok" => "0,00"
    ],
    1 => [
      "type" => "T2",
      "kg" => "35,2115",
      "price_nok" => "113.045,98"
    ]
  ],
  "total_amount" => [
    "amount" => "113.045,98",
    "freight" => "1.561,54",
    "total" => "114.607,52"
  ],
  "commercial_invoice" => "Based on the Sale Invoices: 144505, 144512, 313322-313328 and the Proforma Invoices: 1045-1049 and the Delivery Nos: 150757, 150764, 150785, 150802-150804, 150806-150807, 150809, 150811-150812, 150814, 150816, 150818",
  "exporter" => [
    "name" => "Committee XXIV",
    "address" => "Gl. Skartved 11",
    "city" => "Bjert-Kolding",
    "country" => "Denmark",
    "email" => "hello@committee-xxiv.com",
    "phone" => "+45 88 33 88 55",
    "vat_number" => "32772498"
  ]
];

$result['ci-2-1'] = [
  "Sale Invoices" => [
    0 => 144505,
    1 => 144512,
    2 => 313322,
    3 => 313323,
    4 => 313324,
    5 => 313325,
    6 => 313326,
    7 => 313327,
    8 => 313328
  ],
  "Proforma Invoices" => [
    0 => 1045,
    1 => 1046,
    2 => 1047,
    3 => 1048,
    4 => 1049
  ],
  "Delivery Nos" => [
    0 => 150757,
    1 => 150764,
    2 => 150785,
    3 => 150802,
    4 => 150803,
    5 => 150804,
    6 => 150806,
    7 => 150807,
    8 => 150809,
    9 => 150811,
    10 => 150812,
    11 => 150814,
    12 => 150816,
    13 => 150818
  ]
];

//Committee-xxiv - commercial_invoice_1411
$result['ci-3'] =  [
  "invoice_number" => "1411",
  "date" => "2024-08-26",
  "collie" => "14",
  "delivery_term" => "DAP",
  "customs_credit_number" => "33479445",
  "combined_per_style" => [
    0 => [
      "style_number" => "I234424IVY-Tara",
      "style_name" => "Jeans Wash Cool Excellent Black",
      "quality" => "Cotton 76% Polyester 22% Elastan 2%",
      "tariff" => "6104620000TR T2",
      "origin" => "1,5",
      "kg_qty" => "5",
      "price_nok_t" => "433,00",
      "price_nok" => "2.165,00"
    ],
    1 => [
      "style_number" => "I234856IVY-Johanna",
      "style_name" => "Jeans Color SS24",
      "quality" => "Cotton 91% Polyester 7% Elastane 2%",
      "tariff" => "6104620000TR T2",
      "origin" => "0,3",
      "kg_qty" => "1",
      "price_nok_t" => "540,86",
      "price_nok" => "540,86"
    ],
    2 => [
      "style_number" => "I234975IVY-Johanna",
      "style_name" => "Jeans Excl. Greece Dark Blue",
      "quality" => "Reused Cotton 89% Polyester 8% Elastane 3%",
      "tariff" => "6104620000TR T2",
      "origin" => "0,3",
      "kg_qty" => "1",
      "price_nok_t" => "1.106,46",
      "price_nok" => "1.106,46"
    ],
    3 => [
      "style_number" => "I235012IVY-Tonya",
      "style_name" => "Jeans Wash Salamanca",
      "quality" => "Cotton 54% Recycled Polyester 25% Reused Cotton 19% Elastane 2%",
      "tariff" => "6104620000TR T2",
      "origin" => "0,3",
      "kg_qty" => "1",
      "price_nok_t" => "1.106,46",
      "price_nok" => "1.106,46"
    ],
    4 => [
      "style_number" => "I235031IVY-Ann Charlotte",
      "style_name" => "Earth Jeans Wash Cayenne",
      "quality" => "Recycled Polyester 50%, 31% Cotton, 17% Reused Cotton, 2% Elastane",
      "tariff" => "6104620000TR T2",
      "origin" => "0,3",
      "kg_qty" => "1",
      "price_nok_t" => "500,00",
      "price_nok" => "500,00"
    ],
    5 => [
      "style_number" => "I235040IVY-Augusta",
      "style_name" => "Oversize Jacket Wash Istanbul Black",
      "quality" => "Cotton 99% Elastan 1%",
      "tariff" => "6202920090TR T2",
      "origin" => "0,3",
      "kg_qty" => "1",
      "price_nok_t" => "583,10",
      "price_nok" => "583,10"
    ],
    6 => [
      "style_number" => "T1085 TRW-Jenna",
      "style_name" => "Jeans Wash Prato",
      "quality" => "Organic cotton 93,07% Recycled polyester 4,84% Elastan 2,09%",
      "tariff" => "6104620000TR T2",
      "origin" => "31,2104",
      "kg_qty" => "1",
      "price_nok_t" => "1.015,42",
      "price_nok" => "105.604,10"
    ],
    7 => [
      "style_number" => "T1118 Moussa",
      "style_name" => "Hepburn Shirt",
      "quality" => "Cotton 74% Polyamide 22% Elastan 4%",
      "tariff" => "6206300090TR T2",
      "origin" => "1,0",
      "kg_qty" => "1",
      "price_nok_t" => "1.440,00",
      "price_nok" => "1.440,00"
    ]
  ],
  "collect_lines" => [
    0 => [
      "tariff" => "6104620000TR T2",
      "origin" => "33,9113",
      "kg_qty" => "111.022,88"
    ],
    1 => [
      "tariff" => "6202920090TR T2",
      "origin" => "0,3",
      "kg_qty" => "583,10"
    ],
    2 => [
      "tariff" => "6206300090TR T2",
      "origin" => "1,0",
      "kg_qty" => "1.440,00"
    ]
  ],
  "total" => [
    "tariff" => "35,2115",
    "kg_qty" => "113.045,98"
  ],
  "stock_type" => [
    0 => [
      "type" => "T1",
      "kg_qty" => "0,0",
      "price_nok" => "0,00"
    ],
    1 => [
      "type" => "T2",
      "kg_qty" => "35,2115",
      "price_nok" => "113.045,98"
    ]
  ],
  "total_amount" => [
    "amount" => "113.045,98",
    "freight" => "1.561,54",
    "total" => "114.607,52"
  ],
  "commercial_invoice" => [
    "sale_invoices" => [
      0 => "144505",
      1 => "144512",
      2 => "313322-313328"
    ],
    "proforma_invoices" => [
      0 => "1045-1049"
    ],
    "delivery_nos" => [
      0 => "150757",
      1 => "150764",
      2 => "150785",
      3 => "150802-150804",
      4 => "150806-150807",
      5 => "150809",
      6 => "150811-150812",
      7 => "150814",
      8 => "150816",
      9 => "150818"
    ],
    "exporter" => "Committee XXIV - Gl. Skartved 11 - 6091 Bjert-Kolding - Denmark - Email: hello@committee-xxiv.com - Phone: +45 88 33 88 55 - VAT No: 32772498",
    "customs_authorization_no" => "DK/20/740775",
    "origin_declaration" => "The exporter of the products covered by this document customs authorization No. DK/20/740775 declares that except where otherwise cleary indicated, these products are of EEA/Turkish preferential origin"
  ]
];

//Committee-xxiv - commercial_invoice_1411
$result['ci-4'] = [
  "invoice_number" => "1411",
  "date" => "2024-08-26",
  "collie" => "14",
  "delivery_term" => "DAP",
  "customs_credit_no" => "33479445",
  "combined_per_style" => [
    0 => [
      "style_number" => "I234424IVY-Tara",
      "style_name" => "Jeans Wash Cool Excellent Black",
      "quality" => "Cotton 76% Polyester 22% Elastan 2%",
      "tariff" => "6104620000TR T2",
      "origin" => "T2",
      "kg" => "1,5",
      "quantity" => "5",
      "price_nokt" => "433,00",
      "price_nok" => "2.165,00"
    ],
    1 => [
      "style_number" => "I234856IVY-Johanna",
      "style_name" => "Jeans Color SS24",
      "quality" => "Cotton 91% Polyester 7% Elastane 2%",
      "tariff" => "6104620000TR T2",
      "origin" => "T2",
      "kg" => "0,3",
      "quantity" => "1",
      "price_nokt" => "540,86",
      "price_nok" => "540,86"
    ],
    2 => [
      "style_number" => "I234975IVY-Johanna",
      "style_name" => "Jeans Excl. Greece Dark Blue",
      "quality" => "Reused Cotton 89% Polyester 8% Elastane 3%",
      "tariff" => "6104620000TR T2",
      "origin" => "T2",
      "kg" => "0,3",
      "quantity" => "1",
      "price_nokt" => "1.106,46",
      "price_nok" => "1.106,46"
    ],
    3 => [
      "style_number" => "I235012IVY-Tonya",
      "style_name" => "Jeans Wash Salamanca",
      "quality" => "Cotton 54% Recycled Polyester 25% Reused Cotton 19% Elastane 2%",
      "tariff" => "6104620000TR T2",
      "origin" => "T2",
      "kg" => "0,3",
      "quantity" => "1",
      "price_nokt" => "1.106,46",
      "price_nok" => "1.106,46"
    ],
    4 => [
      "style_number" => "I235031IVY-Ann Charlotte",
      "style_name" => "Earth Jeans Wash Cayenne",
      "quality" => "Recycled Polyester 50%, 31% Cotton, 17% Reused Cotton, 2% Elastane",
      "tariff" => "6104620000TR T2",
      "origin" => "T2",
      "kg" => "0,3",
      "quantity" => "1",
      "price_nokt" => "500,00",
      "price_nok" => "500,00"
    ],
    5 => [
      "style_number" => "I235040IVY-Augusta",
      "style_name" => "Oversize Jacket Wash Istanbul Black",
      "quality" => "Cotton 99% Elastan 1%",
      "tariff" => "6202920090TR T2",
      "origin" => "T2",
      "kg" => "0,3",
      "quantity" => "1",
      "price_nokt" => "583,10",
      "price_nok" => "583,10"
    ],
    6 => [
      "style_number" => "T1085 TRW-Jenna",
      "style_name" => "Jeans Wash Prato",
      "quality" => "Organic cotton 93,07% Recycled polyester 4,84% Elastan 2,09%",
      "tariff" => "6104620000TR T2",
      "origin" => "T2",
      "kg" => "31,2104",
      "quantity" => "1",
      "price_nokt" => "1.015,42",
      "price_nok" => "105.604,10"
    ],
    7 => [
      "style_number" => "T1118 Moussa",
      "style_name" => "Hepburn Shirt",
      "quality" => "Cotton 74% Polyamide 22% Elastan 4%",
      "tariff" => "6206300090TR T2",
      "origin" => "T2",
      "kg" => "1,0",
      "quantity" => "1",
      "price_nokt" => "1.440,00",
      "price_nok" => "1.440,00"
    ]
  ],
  "collect_lines" => [
    0 => [
      "tariff" => "6104620000TR T2",
      "origin" => "T2",
      "kg" => "33,9113",
      "quantity" => "111.022,88"
    ],
    1 => [
      "tariff" => "6202920090TR T2",
      "origin" => "T2",
      "kg" => "0,3",
      "quantity" => "583,10"
    ],
    2 => [
      "tariff" => "6206300090TR T2",
      "origin" => "T2",
      "kg" => "1,0",
      "quantity" => "1.440,00"
    ]
  ],
  "total" => [
    "stock_type" => [
      0 => [
        "type" => "T1",
        "kg" => "0,0",
        "quantity" => "0",
        "price_nok" => "0,00"
      ],
      1 => [
        "type" => "T2",
        "kg" => "35,2115",
        "quantity" => "113.045,98",
        "price_nok" => "113.045,98"
      ]
    ],
    "total_amount" => [
      "amount" => "113.045,98",
      "freight" => "1.561,54",
      "total" => "114.607,52"
    ]
  ],
  "customs_authorization_no" => "DK/20/740775",
  "declaration" => "The exporter of the products covered by this document customs authorization No. DK/20/740775 declares that except where otherwise cleary indicated, these products are of EEA/Turkish preferential origin"
];

//BECKSONDERGAARD ApS - NIC00924
$result['ci-5'] = [
  "invoice_number" => "NIC00924",
  "date" => "22-03-24",
  "exporter" => [
    "name" => "becksöndergaard aps",
    "address" => "Emdrupvej 26 D, 1",
    "city" => "København",
    "country" => "Danmark",
    "phone" => "+45 3583 7083",
    "fax" => "+45 3583 7084",
    "email" => "email@becksondergaard.com",
    "vat_number" => "DK26990564",
    "tax_number" => "928996212",
    "bank" => [
      "name" => "Handelsbanken NO NOK",
      "account_number" => "90461201619",
      "swift_code" => "HANDNOKK",
      "reg_number" => "NO9790461201619",
      "iban" => "NO9790461201619"
    ],
    "contact_person" => "Samlefaktura NIC00924"
  ],
  "customer" => [
    "name" => "Becksøndergaard ApS (NO B2C)",
    "address" => "c/o IntraVat Nedre Strandgate 80",
    "city" => "NO-3012 Drammen",
    "country" => "Norway",
    "org_number" => "928996212",
    "tax_credit" => "33490202",
    "customer_number" => "IC47002",
    "vat_number" => "928996212"
  ],
  "delivery_terms" => "DAP",
  "payment_terms" => "Valutakode: NOK",
  "items" => [
    0 => [
      "item_number" => "23012200011140101",
      "description" => "Lilli Rua Bag",
      "origin" => "INDIA",
      "tariff_number" => "4202210090",
      "gross_weight" => "0.44",
      "net_weight" => "0.40",
      "quantity" => "1",
      "discount" => "0.00",
      "unit_price" => "699.60",
      "total_price" => "699.60"
    ],
    1 => [
      "item_number" => "11118560054570627",
      "description" => "Glitter Drake Sock",
      "origin" => "CHINA",
      "tariff_number" => "6115990000",
      "gross_weight" => "0.03",
      "net_weight" => "0.03",
      "quantity" => "1",
      "discount" => "0.00",
      "unit_price" => "47.60",
      "total_price" => "47.60"
    ],
    2 => [
      "item_number" => "22078890014560123",
      "description" => "Solid Willow Bra",
      "origin" => "CHINA",
      "tariff_number" => "6208920000",
      "gross_weight" => "0.11",
      "net_weight" => "0.10",
      "quantity" => "1",
      "discount" => "0.00",
      "unit_price" => "199.60",
      "total_price" => "199.60"
    ],
    3 => [
      "item_number" => "22078890018410123",
      "description" => "Solid Willow Bra",
      "origin" => "CHINA",
      "tariff_number" => "6208920000",
      "gross_weight" => "0.11",
      "net_weight" => "0.10",
      "quantity" => "1",
      "discount" => "0.00",
      "unit_price" => "199.60",
      "total_price" => "199.60"
    ],
    4 => [
      "item_number" => "23108040030010010",
      "description" => "Luster Scrunchie",
      "origin" => "CHINA",
      "tariff_number" => "9615900000",
      "gross_weight" => "0.03",
      "net_weight" => "0.03",
      "quantity" => "1",
      "discount" => "0.00",
      "unit_price" => "59.60",
      "total_price" => "59.60"
    ],
    5 => [
      "item_number" => "23108040032012010",
      "description" => "Luster Scrunchie",
      "origin" => "CHINA",
      "tariff_number" => "9615900000",
      "gross_weight" => "0.03",
      "net_weight" => "0.03",
      "quantity" => "1",
      "discount" => "0.00",
      "unit_price" => "59.60",
      "total_price" => "59.60"
    ],
    6 => [
      "item_number" => "11114580074730125",
      "description" => "Zecora Biddy Bikini Cheeky",
      "origin" => "CHINA",
      "tariff_number" => "6112419000",
      "gross_weight" => "0.11",
      "net_weight" => "0.10",
      "quantity" => "1",
      "discount" => "0.00",
      "unit_price" => "319.20",
      "total_price" => "319.20"
    ],
    7 => [
      "item_number" => "11114580076230125",
      "description" => "Zecora Biddy Bikini Cheeky",
      "origin" => "CHINA",
      "tariff_number" => "6112419000",
      "gross_weight" => "0.11",
      "net_weight" => "0.10",
      "quantity" => "1",
      "discount" => "0.00",
      "unit_price" => "159.60",
      "total_price" => "159.60"
    ],
    8 => [
      "item_number" => "11114580064730124",
      "description" => "Zecora Ezra Bikini Top",
      "origin" => "CHINA",
      "tariff_number" => "6112419000",
      "gross_weight" => "0.11",
      "net_weight" => "0.10",
      "quantity" => "1",
      "discount" => "0.00",
      "unit_price" => "319.20",
      "total_price" => "319.20"
    ],
    9 => [
      "item_number" => "11114580066230124",
      "description" => "Zecora Ezra Bikini Top",
      "origin" => "CHINA",
      "tariff_number" => "6112419000",
      "gross_weight" => "0.11",
      "net_weight" => "0.10",
      "quantity" => "1",
      "discount" => "0.00",
      "unit_price" => "159.60",
      "total_price" => "159.60"
    ],
    10 => [
      "item_number" => "00004123503540101",
      "description" => "Gerry Strap",
      "origin" => "CHINA",
      "tariff_number" => "4202918090",
      "gross_weight" => "0.11",
      "net_weight" => "0.10",
      "quantity" => "1",
      "discount" => "0.00",
      "unit_price" => "167.60",
      "total_price" => "167.60"
    ],
    11 => [
      "item_number" => "00002202000100101",
      "description" => "Lullo Rua Bag",
      "origin" => "INDIA",
      "tariff_number" => "4202210090",
      "gross_weight" => "0.44",
      "net_weight" => "0.40",
      "quantity" => "1",
      "discount" => "0.00",
      "unit_price" => "699.60",
      "total_price" => "699.60"
    ],
    12 => [
      "item_number" => "24014820050025012",
      "description" => "Flozita Luelle Kimono",
      "origin" => "CHINA",
      "tariff_number" => "6208990099",
      "gross_weight" => "0.22",
      "net_weight" => "0.20",
      "quantity" => "3",
      "discount" => "0.00",
      "unit_price" => "759.20",
      "total_price" => "759.20"
    ],
    13 => [
      "item_number" => "11118080010010010",
      "description" => "Jazzy Hairbrace",
      "origin" => "CHINA",
      "tariff_number" => "9615900000",
      "gross_weight" => "0.11",
      "net_weight" => "0.10",
      "quantity" => "1",
      "discount" => "0.00",
      "unit_price" => "67.60",
      "total_price" => "67.60"
    ],
    14 => [
      "item_number" => "23077990019007010",
      "description" => "Bartletts Scarf",
      "origin" => "CHINA",
      "tariff_number" => "6214400030",
      "gross_weight" => "0.31",
      "net_weight" => "0.28",
      "quantity" => "1",
      "discount" => "0.00",
      "unit_price" => "319.60",
      "total_price" => "319.60"
    ]
  ],
  "invoice_total" => "4,236.80",
  "discount" => "15.00",
  "freight" => "2.17",
  "insurance" => "2.39"
];

//BECKSONDERGAARD ApS - NIC00924
$result['ci-6'] = [
  "invoice_number" => "NIC00924",
  "date" => "22-03-24",
  "exporter" => [
    "name" => "becksöndergaard aps",
    "address" => "Emdrupvej 26 D, 1",
    "city" => "København",
    "country" => "Danmark",
    "phone" => "+45 3583 7083",
    "fax" => "+45 3583 7084",
    "email" => "email@becksondergaard.com",
    "vat_number" => "DK26990564",
    "bank" => [
      "name" => "Handelsbanken NO NOK",
      "account_number" => "90461201619",
      "swift_code" => "HANDNOKK",
      "reg_number" => "NO9790461201619",
      "iban" => "NO9790461201619"
    ],
    "contact_person" => [
      "name" => "Becksøndergaard ApS (NO B2C)",
      "address" => "c/o IntraVat",
      "city" => "Drammen",
      "country" => "Norway",
      "org_number" => "928996212",
      "toll_credit" => "33490202"
    ]
  ],
  "customer_number" => "IC47002",
  "vat_number" => "928996212",
  "delivery_terms" => "DAP",
  "payment_terms" => "NOK",
  "invoice_total" => 4236.8,
  "discount" => 15.0,
  "freight" => 2.17,
  "insurance" => 2.39,
  "products" => [
    0 => [
      "item_number" => "23012200011140101",
      "description" => "Lilli Rua Bag",
      "origin_country" => "INDIA",
      "tariff_number" => "4202210090",
      "gross_weight" => 0.44,
      "net_weight" => 0.4,
      "quantity" => 1,
      "discount" => 0.0,
      "unit_price" => 699.6,
      "total_price" => 699.6
    ],
    1 => [
      "item_number" => "11118560054570627",
      "description" => "Glitter Drake Sock",
      "origin_country" => "CHINA",
      "tariff_number" => "6115990000",
      "gross_weight" => 0.03,
      "net_weight" => 0.03,
      "quantity" => 1,
      "discount" => 0.0,
      "unit_price" => 47.6,
      "total_price" => 47.6
    ],
    2 => [
      "item_number" => "22078890014560123",
      "description" => "Solid Willow Bra",
      "origin_country" => "CHINA",
      "tariff_number" => "6208920000",
      "gross_weight" => 0.11,
      "net_weight" => 0.1,
      "quantity" => 1,
      "discount" => 0.0,
      "unit_price" => 199.6,
      "total_price" => 199.6
    ],
    3 => [
      "item_number" => "22078890018410123",
      "description" => "Solid Willow Bra",
      "origin_country" => "CHINA",
      "tariff_number" => "6208920000",
      "gross_weight" => 0.11,
      "net_weight" => 0.1,
      "quantity" => 1,
      "discount" => 0.0,
      "unit_price" => 199.6,
      "total_price" => 199.6
    ],
    4 => [
      "item_number" => "23108040030010010",
      "description" => "Luster Scrunchie",
      "origin_country" => "CHINA",
      "tariff_number" => "9615900000",
      "gross_weight" => 0.03,
      "net_weight" => 0.03,
      "quantity" => 1,
      "discount" => 0.0,
      "unit_price" => 59.6,
      "total_price" => 59.6
    ],
    5 => [
      "item_number" => "23108040032012010",
      "description" => "Luster Scrunchie",
      "origin_country" => "CHINA",
      "tariff_number" => "9615900000",
      "gross_weight" => 0.03,
      "net_weight" => 0.03,
      "quantity" => 1,
      "discount" => 0.0,
      "unit_price" => 59.6,
      "total_price" => 59.6
    ],
    6 => [
      "item_number" => "11114580074730125",
      "description" => "Zecora Biddy Bikini Cheeky",
      "origin_country" => "CHINA",
      "tariff_number" => "6112419000",
      "gross_weight" => 0.11,
      "net_weight" => 0.1,
      "quantity" => 1,
      "discount" => 0.0,
      "unit_price" => 319.2,
      "total_price" => 319.2
    ],
    7 => [
      "item_number" => "11114580076230125",
      "description" => "Zecora Biddy Bikini Cheeky",
      "origin_country" => "CHINA",
      "tariff_number" => "6112419000",
      "gross_weight" => 0.11,
      "net_weight" => 0.1,
      "quantity" => 1,
      "discount" => 0.0,
      "unit_price" => 159.6,
      "total_price" => 159.6
    ],
    8 => [
      "item_number" => "11114580064730124",
      "description" => "Zecora Ezra Bikini Top",
      "origin_country" => "CHINA",
      "tariff_number" => "6112419000",
      "gross_weight" => 0.11,
      "net_weight" => 0.1,
      "quantity" => 1,
      "discount" => 0.0,
      "unit_price" => 319.2,
      "total_price" => 319.2
    ],
    9 => [
      "item_number" => "11114580066230124",
      "description" => "Zecora Ezra Bikini Top",
      "origin_country" => "CHINA",
      "tariff_number" => "6112419000",
      "gross_weight" => 0.11,
      "net_weight" => 0.1,
      "quantity" => 1,
      "discount" => 0.0,
      "unit_price" => 159.6,
      "total_price" => 159.6
    ],
    10 => [
      "item_number" => "00004123503540101",
      "description" => "Gerry Strap",
      "origin_country" => "CHINA",
      "tariff_number" => "4202918090",
      "gross_weight" => 0.11,
      "net_weight" => 0.1,
      "quantity" => 1,
      "discount" => 0.0,
      "unit_price" => 167.6,
      "total_price" => 167.6
    ],
    11 => [
      "item_number" => "00002202000100101",
      "description" => "Lullo Rua Bag",
      "origin_country" => "INDIA",
      "tariff_number" => "4202210090",
      "gross_weight" => 0.44,
      "net_weight" => 0.4,
      "quantity" => 1,
      "discount" => 0.0,
      "unit_price" => 699.6,
      "total_price" => 699.6
    ],
    12 => [
      "item_number" => "24014820050025012",
      "description" => "Flozita Luelle Kimono",
      "origin_country" => "CHINA",
      "tariff_number" => "6208990099",
      "gross_weight" => 0.22,
      "net_weight" => 0.2,
      "quantity" => 3,
      "discount" => 0.0,
      "unit_price" => 759.2,
      "total_price" => 759.2
    ],
    13 => [
      "item_number" => "11118080010010010",
      "description" => "Jazzy Hairbrace",
      "origin_country" => "CHINA",
      "tariff_number" => "9615900000",
      "gross_weight" => 0.11,
      "net_weight" => 0.1,
      "quantity" => 1,
      "discount" => 0.0,
      "unit_price" => 67.6,
      "total_price" => 67.6
    ],
    14 => [
      "item_number" => "23077990019007010",
      "description" => "Bartletts Scarf",
      "origin_country" => "CHINA",
      "tariff_number" => "6214400030",
      "gross_weight" => 0.31,
      "net_weight" => 0.28,
      "quantity" => 1,
      "discount" => 0.0,
      "unit_price" => 319.6,
      "total_price" => 319.6
    ]
  ],
  "replacement_statements" => "serving as proof of preferential origin according to rules of origin of the Generalized System of Preferences is given in the attached export proforma invoice if any"
];

//BERENDSOHN AG - 983799620MVA_CI_0000041790_END
$result['ci-7'] = [
  "invoice_no" => "41790",
  "organization_no" => "NO 983 799 620 MVA",
  "tollkredit" => "30255748",
  "EORI_number" => "DE2222701",
  "products" => [
    0 => [
      "product_id" => "VN 147860",
      "delivery_note" => "81840591",
      "loading_list_no" => "41790",
      "invoice_no" => "SAMPLE 9000019430",
      "quantity" => "1,000",
      "weight" => "0,113 KG",
      "unit_price" => "30,89",
      "total_weight" => "0,113",
      "total_value" => "30,89",
      "value_in_euro" => "2,70",
      "description" => "SAMPLE",
      "commodity_code" => "9000019430",
      "country_of_origin" => "CN"
    ],
    1 => [
      "product_id" => "VN 147850",
      "delivery_note" => "81840591",
      "loading_list_no" => "41790",
      "invoice_no" => "SAMPLE 9000019430",
      "quantity" => "1,000",
      "weight" => "0,046 KG",
      "unit_price" => "21,17",
      "total_weight" => "0,046",
      "total_value" => "21,17",
      "value_in_euro" => "1,85",
      "description" => "SAMPLE",
      "commodity_code" => "9000019430",
      "country_of_origin" => "CN"
    ],
    2 => [
      "product_id" => "CULTURE HERO (kosmetikkveske)",
      "delivery_note" => "",
      "loading_list_no" => "",
      "invoice_no" => "",
      "quantity" => "2,000",
      "weight" => "0,159 KG",
      "unit_price" => "52,06",
      "total_weight" => "0,159",
      "total_value" => "52,06",
      "value_in_euro" => "4,55",
      "description" => "CULTURE HERO (kosmetikkveske)",
      "commodity_code" => "42029211",
      "country_of_origin" => "CN"
    ],
    3 => [
      "product_id" => "VN 147500",
      "delivery_note" => "81840591",
      "loading_list_no" => "41790",
      "invoice_no" => "SAMPLE 9000019430",
      "quantity" => "1,000",
      "weight" => "1,400 KG",
      "unit_price" => "134,68",
      "total_weight" => "1,400",
      "total_value" => "134,68",
      "value_in_euro" => "11,77",
      "description" => "SAMPLE",
      "commodity_code" => "9000019430",
      "country_of_origin" => "CN"
    ],
    4 => [
      "product_id" => "Brett Pit (brød- og smulebrett)",
      "delivery_note" => "",
      "loading_list_no" => "",
      "invoice_no" => "",
      "quantity" => "1,000",
      "weight" => "1,400 KG",
      "unit_price" => "134,68",
      "total_weight" => "1,400",
      "total_value" => "134,68",
      "value_in_euro" => "11,77",
      "description" => "Brett Pit (brød- og smulebrett)",
      "commodity_code" => "44191900",
      "country_of_origin" => "CN",
    ],
    5 => [
      "product_id" => "VN 147600",
      "delivery_note" => "81840591",
      "loading_list_no" => "41790",
      "invoice_no" => "SAMPLE 9000019430",
      "quantity" => "1,000",
      "weight" => "0,320 KG",
      "unit_price" => "58,13",
      "total_weight" => "0,320",
      "total_value" => "58,13",
      "value_in_euro" => "5,08",
      "description" => "SAMPLE",
      "commodity_code" => "9000019430",
      "country_of_origin" => "CN"
    ],
    6 => [
      "product_id" => "LUFTIKUS (USB-vifte)",
      "delivery_note" => "",
      "loading_list_no" => "",
      "invoice_no" => "",
      "quantity" => "1,000",
      "weight" => "0,320 KG",
      "unit_price" => "58,13",
      "total_weight" => "0,320",
      "total_value" => "58,13",
      "value_in_euro" => "5,08",
      "description" => "LUFTIKUS (USB-vifte)",
      "commodity_code" => "84145925",
      "country_of_origin" => "CN"
    ],
    7 => [
      "product_id" => "VN 145700",
      "delivery_note" => "81840591",
      "loading_list_no" => "41790",
      "invoice_no" => "SAMPLE 9000019430",
      "quantity" => "1,000",
      "weight" => "0,897 KG",
      "unit_price" => "184,57",
      "total_weight" => "0,897",
      "total_value" => "184,57",
      "value_in_euro" => "16,13",
      "description" => "SAMPLE",
      "commodity_code" => "9000019430",
      "country_of_origin" => "CN"
    ],
    8 => [
      "product_id" => "KLANGVOLL (Overear-hodetelefonene)",
      "delivery_note" => "",
      "loading_list_no" => "",
      "invoice_no" => "",
      "quantity" => "1,000",
      "weight" => "0,897 KG",
      "unit_price" => "184,57",
      "total_weight" => "0,897",
      "total_value" => "184,57",
      "value_in_euro" => "16,13",
      "description" => "KLANGVOLL (Overear-hodetelefonene)",
      "commodity_code" => "85183000",
      "country_of_origin" => "CN"
    ]
  ],
  "total_quantity" => "5,000",
  "total_weight" => "2,776 KG",
  "total_value" => "429,44",
  "total_value_in_euro" => "37,53",
  "notes" => "Samples of no commercial value. Invoice only for customs clearance. (No.: 9990 99 25) The exporter of the products covered by these document (customs  authorization no. DE/4600/EA/0296) declares that, except where  otherwise  clearly indicated, these products are of EEA preferential  origin. We hereby confirm, that every information, given in this invoice, will be true and correct.",
  "consignor" => [
    "company_name" => "Berendsohn AG",
    "shipping_address" => "Versandstelle Musterlager Berendsohnstraße 3 19071 Brüsewitz",
    "phone" => "038874-860",
    "fax" => "038874-860200",
    "country" => "Germany"
  ],
  "consignee" => [
    "company_name" => "Berendsohn AG (NUF)",
    "address" => "c/o intraVAT Nedre Strandgate 80 3012 DRAMMEN NORWEGEN"
  ],
  "consolidated_invoice" => [
    "page" => "1",
    "date" => "18.03.2024"
  ]
];

//BESSIE - Samlefaktura 22-03-24
$result['ci-8'] = [
  "invoice_number" => "3590",
  "date" => "22-03-2024",
  "customs_credit_number" => "13003849",
  "exporter" => [
    "name" => "Bessie A/S",
    "address" => "Vasekær 10",
    "city" => "Herlev",
    "postal_code" => "2730",
    "country" => "Danmark",
    "phone" => "+45 44 92 52 52",
    "vat_number" => "DK11553672"
  ],
  "exporter_declaration" => "FOR RIGTIGHEDEN BEKRÆFTER BESSIE A/S AT VARERNE ER PRODUCERET OG HAR OPRINDELSE I PORTUGAL",
  "exporter_origin" => "Eksportøren af varer, der er omfattet af nærværende dokument, (toldmyndighedernes tilladelse nr. DK/22/686605) erklærer, at varerne, medmindre andet tydeligt er angivet, har præferenceoprindelse i Portugal",
  "exporter_additional_info" => "Bessie A/S Vasekær 10 - 2730  Herlev - Danmark Reg. nr.:  VAT. NO.: 11553672 TLF: +45 44 92 52 52",
  "importer" => [
    "name" => "Bessie AS",
    "org_number" => "981353986MVA",
    "vat_representative" => [
      "name" => "IntraVAT AS",
      "org_number" => "861772322",
      "address" => "Nedre Strandgate 80",
      "city" => "Drammen",
      "postal_code" => "3012",
      "country" => "Norge"
    ]
  ],
  "items" => [
    0 => [
      "item_number" => "FRAGTGLS-NORGE",
      "item_code" => "0,00",
      "quantity" => "1,00",
      "amount" => "0,00",
      "item_name" => "Fragt GLS Norge"
    ],
    1 => [
      "item_number" => "LONDON-SI16",
      "item_code" => "61069090",
      "quantity" => "8,00",
      "amount" => "2.760,00",
      "item_name" => "Silk shirt 70%vis 30%silk"
    ],
    2 => [
      "item_number" => "LONDON-SI27",
      "item_code" => "61069090",
      "quantity" => "8,00",
      "amount" => "2.760,00",
      "item_name" => "Silk shirt 70%vis 30%silk"
    ],
    3 => [
      "item_number" => "FLOWER-LIB26",
      "item_code" => "62044990",
      "quantity" => "184,00",
      "amount" => "94.576,00",
      "item_name" => "Dress linn flower 100% linnen"
    ],
    4 => [
      "item_number" => "FLAIR-GE3",
      "item_code" => "62046231",
      "quantity" => "21,00",
      "amount" => "7.245,00",
      "item_name" => "Jeans flair stretch 71%cot 26%pol 3%ela"
    ],
    5 => [
      "item_number" => "FLAIR-SU27",
      "item_code" => "62046231",
      "quantity" => "1,00",
      "amount" => "345,00",
      "item_name" => "Jeans flair stretch 87%cot 10%pol 3%ela"
    ],
    6 => [
      "item_number" => "INEZ-SH28",
      "item_code" => "62046231",
      "quantity" => "14,00",
      "amount" => "4.140,00",
      "item_name" => "7/8 Jeans denim stretch 90% cot, 8% pol, 2% lyc"
    ],
    7 => [
      "item_number" => "SHORTS-CB4",
      "item_code" => "62046290",
      "quantity" => "33,00",
      "amount" => "11.682,00",
      "item_name" => "Shorts striped stretch 97% cot, 3% ela"
    ],
    8 => [
      "item_number" => "SIGNE-P98",
      "item_code" => "62046318",
      "quantity" => "18,00",
      "amount" => "6.210,00",
      "item_name" => "Bengalin trousers 48%cot48%pol4%ela"
    ]
  ],
  "total_items" => "287,00",
  "total_amount" => "129.719,00",
  "total_packages" => "26,00",
  "net_weight" => "164,11",
  "gross_weight" => "190,11"
];

//BESSIE - Samlefaktura 22-03-24
$result['ci-9'] = [ 
  "invoice_number" => "3590",
  "date" => "22-03-2024",
  "customs_credit_number" => "13003849",
  "exporter" => [
    "name" => "Bessie A/S",
    "address" => "Vasekær 10",
    "city" => "Herlev",
    "postal_code" => "2730",
    "country" => "Danmark",
    "phone" => "+45 44 92 52 52",
    "vat_number" => "DK11553672"
  ],
  "exporter_representative" => [
    "name" => "Bessie A/S",
    "address" => "Vasekær 10",
    "city" => "Herlev",
    "postal_code" => "2730",
    "country" => "Danmark",
    "phone" => "+45 44 92 52 52",
    "vat_number" => "DK11553672"
  ],
  "importer" => [
    "name" => "Bessie AS",
    "org_number" => "981353986MVA",
    "vat_representative" => [
      "name" => "IntraVAT AS",
      "org_number" => "861772322",
      "address" => "Nedre Strandgate 80",
      "city" => "Drammen",
      "postal_code" => "3012",
      "country" => "Norge"
    ]
  ],
  "total" => [
    "quantity" => "287",
    "net_weight" => "164.11",
    "gross_weight" => "190.11",
    "amount" => "129719.00"
  ],
  "packages" => [
    "number" => "26"
  ],
  "items" => [
    0 => [
      "item_number" => "FRAGTGLS-NORGE",
      "item_code" => "0,00",
      "quantity" => "1,00",
      "amount" => "0,00",
      "item_name" => "Fragt GLS Norge"
    ],
    1 => [
      "item_number" => "LONDON-SI16",
      "item_code" => "61069090",
      "quantity" => "8,00",
      "amount" => "2760,00",
      "item_name" => "Silk shirt 70%vis 30%silk"
    ],
    2 => [
      "item_number" => "LONDON-SI27",
      "item_code" => "61069090",
      "quantity" => "8,00",
      "amount" => "2760,00",
      "item_name" => "Silk shirt 70%vis 30%silk"
    ],
    3 => [
      "item_number" => "FLOWER-LIB26",
      "item_code" => "62044990",
      "quantity" => "184,00",
      "amount" => "94576,00",
      "item_name" => "Dress linn flower 100% linnen"
    ],
    4 => [
      "item_number" => "FLAIR-GE3",
      "item_code" => "62046231",
      "quantity" => "21,00",
      "amount" => "7245,00",
      "item_name" => "Jeans flair stretch 71%cot 26%pol 3%ela"
    ],
    5 => [
      "item_number" => "FLAIR-SU27",
      "item_code" => "62046231",
      "quantity" => "1,00",
      "amount" => "345,00",
      "item_name" => "Jeans flair stretch 87%cot 10%pol 3%ela"
    ],
    6 => [
      "item_number" => "INEZ-SH28",
      "item_code" => "62046231",
      "quantity" => "14,00",
      "amount" => "4140,00",
      "item_name" => "7/8 Jeans denim stretch 90% cot, 8% pol, 2% lyc"
    ],
    7 => [
      "item_number" => "SHORTS-CB4",
      "item_code" => "62046290",
      "quantity" => "33,00",
      "amount" => "11682,00",
      "item_name" => "Shorts striped stretch 97% cot, 3% ela"
    ],
    8 => [
      "item_number" => "SIGNE-P98",
      "item_code" => "62046318",
      "quantity" => "18,00",
      "amount" => "6210,00",
      "item_name" => "Bengalin trousers 48%cot48%pol4%ela"
    ]
  ]
];

//BLACK COLOUR - NO-337
$result['ci-10'] = [
  "invoice_number" => "NO-337",
  "date" => "20/03/2024",
  "invoice_address" => [
    "company_name" => "Black Colour ApS",
    "kontrollnummer" => "33485657",
    "contact_person" => "C/O Intravat AS",
    "address" => "Nedre Strandgate 80",
    "city" => "Drammen",
    "postal_code" => "3012",
    "country" => "NO",
    "vat_number" => "920893902"
  ],
  "delivery_address" => [
    "company_name" => "Black Colour ApS",
    "kontrollnummer" => "33485657",
    "contact_person" => "C/O Intravat AS",
    "address" => "Nedre Strandgate 80",
    "city" => "Drammen",
    "postal_code" => "3012",
    "country" => "NO",
    "vat_number" => "920893902"
  ],
  "comments" => "HS Description HS Tariff codeHS Country of Origin Status Total weightQuantity Total Price",
  "items" => [
    0 => [
      "hs_description" => "BAGS",
      "hs_tariff_code" => "42022290",
      "hs_country_of_origin" => "INDIA",
      "status" => "",
      "total_weight" => "0.3 kg",
      "quantity" => "6",
      "total_price" => "780.00 NOK"
    ],
    1 => [
      "hs_description" => "BAGS",
      "hs_tariff_code" => "42022290",
      "hs_country_of_origin" => "CHINA",
      "status" => "",
      "total_weight" => "1.9 kg",
      "quantity" => "6",
      "total_price" => "1,711.50 NOK"
    ],
    2 => [
      "hs_description" => "BLOUSES",
      "hs_tariff_code" => "62064000",
      "hs_country_of_origin" => "Italy - EU Non Preferential Origin",
      "status" => "",
      "total_weight" => "0.6 kg",
      "quantity" => "6",
      "total_price" => "1,068.00 NOK"
    ],
    3 => [
      "hs_description" => "BLOUSES",
      "hs_tariff_code" => "62064000",
      "hs_country_of_origin" => "INDIA",
      "status" => "",
      "total_weight" => "2 kg",
      "quantity" => "16",
      "total_price" => "4,564.00 NOK"
    ],
    4 => [
      "hs_description" => "BLOUSES",
      "hs_tariff_code" => "62063000",
      "hs_country_of_origin" => "Italy - EU Non Preferential Origin",
      "status" => "",
      "total_weight" => "4.4 kg",
      "quantity" => "22",
      "total_price" => "3,135.00 NOK"
    ],
    5 => [
      "hs_description" => "DRESSES",
      "hs_tariff_code" => "62044300",
      "hs_country_of_origin" => "INDIA",
      "status" => "",
      "total_weight" => "9 kg",
      "quantity" => "30",
      "total_price" => "9,406.50 NOK"
    ],
    6 => [
      "hs_description" => "DRESSES",
      "hs_tariff_code" => "62044200",
      "hs_country_of_origin" => "INDIA",
      "status" => "",
      "total_weight" => "2.6 kg",
      "quantity" => "6",
      "total_price" => "2,136.00 NOK"
    ],
    7 => [
      "hs_description" => "HAIRCLIPS",
      "hs_tariff_code" => "96151100",
      "hs_country_of_origin" => "CHINA",
      "status" => "",
      "total_weight" => "1.5 kg",
      "quantity" => "84",
      "total_price" => "3,672.75 NOK"
    ],
    8 => [
      "hs_description" => "KIMONOS & JACKETS",
      "hs_tariff_code" => "62043390",
      "hs_country_of_origin" => "INDIA",
      "status" => "",
      "total_weight" => "0.6 kg",
      "quantity" => "4",
      "total_price" => "1,141.00 NOK"
    ],
    9 => [
      "hs_description" => "KNITTED TOPS",
      "hs_tariff_code" => "61103099",
      "hs_country_of_origin" => "Italy - EU preferential Origin",
      "status" => "",
      "total_weight" => "66.1 kg",
      "quantity" => "171",
      "total_price" => "58,838.25 NOK"
    ],
    10 => [
      "hs_description" => "SCARVES",
      "hs_tariff_code" => "62144000",
      "hs_country_of_origin" => "INDIA",
      "status" => "",
      "total_weight" => "0.3 kg",
      "quantity" => "5",
      "total_price" => "890.00 NOK"
    ],
    11 => [
      "hs_description" => "SCARVES",
      "hs_tariff_code" => "62143000",
      "hs_country_of_origin" => "CHINA",
      "status" => "",
      "total_weight" => "0.8 kg",
      "quantity" => "18",
      "total_price" => "1,593.00 NOK"
    ],
    12 => [
      "hs_description" => "SCARVES",
      "hs_tariff_code" => "62142000",
      "hs_country_of_origin" => "Italy - EU preferential Origin",
      "status" => "",
      "total_weight" => "3.6 kg",
      "quantity" => "65",
      "total_price" => "9,262.50 NOK"
    ],
    13 => [
      "hs_description" => "SCARVES",
      "hs_tariff_code" => "62149000",
      "hs_country_of_origin" => "INDIA",
      "status" => "",
      "total_weight" => "0.2 kg",
      "quantity" => "6",
      "total_price" => "531.00 NOK"
    ],
    14 => [
      "hs_description" => "SCARVES",
      "hs_tariff_code" => "62144000",
      "hs_country_of_origin" => "Italy - EU Non Preferential Origin",
      "status" => "",
      "total_weight" => "1.3 kg",
      "quantity" => "18",
      "total_price" => "2,478.00 NOK"
    ],
    15 => [
      "hs_description" => "TROUSERS",
      "hs_tariff_code" => "62046231",
      "hs_country_of_origin" => "Italy - EU Non Preferential Origin",
      "status" => "",
      "total_weight" => "3.6 kg",
      "quantity" => "8",
      "total_price" => "2,854.00 NOK"
    ]
  ],
  "delivery_terms" => "DAP Black Colour ApS, C/O Inter-Distrans AS Drammen",
  "consolidated_invoice_numbers" => [
    0 => "5004956",
    1 => "5004957",
    2 => "5004958",
    3 => "5004959",
    4 => "5004960",
    5 => "5004961",
    6 => "5004962",
    7 => "5004963",
    8 => "5004964",
    9 => "5004965",
    10 => "5004966",
    11 => "5004967",
    12 => "5004968",
    13 => "5004969",
    14 => "5004970",
    15 => "5004971",
    16 => "5004972",
    17 => "5004973",
    18 => "5004974"
  ],
  "track_numbers" => [
    0 => "92181269643",
    1 => "92181269645",
    2 => "92181269646",
    3 => "92181269647",
    4 => "92181269648",
    5 => "92181269649",
    6 => "92181269650",
    7 => "92181269651",
    8 => "92181269657",
    9 => "92181269658",
    10 => "92181269662",
    11 => "92181269664",
    12 => "92181269665",
    13 => "92181269666",
    14 => "92181269668",
    15 => "92181269680",
    16 => "92181269682",
    17 => "92181269683",
    18 => "92181269694",
    19 => "92181269698",
    20 => "92181269707"
  ],
  "total_value" => "104,061.50 NOK",
  "handling_fee" => "75.00 NOK",
  "shipping" => "2,081.16 NOK",
  "total_price_ex_vat" => "106,217.66 NOK",
  "exporter" => [
    "company_name" => "Black Colour ApS",
    "address" => "Håndværkervej 2",
    "city" => "Hadsund",
    "region" => "Nordjylland",
    "postal_code" => "9560",
    "country" => "DK",
    "vat_number" => "DK32563546",
    "eori_number" => "GB322320752000",
    "voec_number" => "33485657 / 98575677",
    "email" => "info@blackcolour.dk",
  ],
  "value_is_equal_to" => "salg til slutkunder",
  "weight" => "103 kg",
  "colli" => "21"
];

//BLACK COLOUR - NO-337
$result['ci-11'] = [
  "invoice_number" => "NO-337",
  "date" => "20/03/2024",
  "invoice_address" => [
    "company_name" => "Black Colour ApS",
    "kontrollnummer" => "33485657",
    "address" => "Nedre Strandgate 80",
    "city" => "Drammen",
    "postal_code" => "3012",
    "country" => "NO",
    "vat_number" => "920893902"
  ],
  "delivery_address" => [
    "company_name" => "Black Colour ApS",
    "kontrollnummer" => "33485657",
    "address" => "Nedre Strandgate 80",
    "city" => "Drammen",
    "postal_code" => "3012",
    "country" => "NO",
    "vat_number" => "920893902"
  ],
  "comments" => "Værdi er lig salg til slutkunder",
  "total_price_ex_vat" => "106,217.66 NOK",
  "handling_fee" => "75.00 NOK",
  "shipping" => "2,081.16 NOK",
  "subtotal" => "104,061.50 NOK",
  "consolidated_invoice_number" => "NO-337",
  "hs_description" => [
    0 => [
      "description" => "BAGS",
      "hs_tariff_code" => "42022290",
      "hs_country_of_origin" => "INDIA",
      "status" => "",
      "total_weight" => "0.3 kg",
      "quantity" => "6",
      "total_price" => "780.00 NOK"
    ],
    1 => [
      "description" => "BAGS",
      "hs_tariff_code" => "42022290",
      "hs_country_of_origin" => "CHINA",
      "status" => "",
      "total_weight" => "1.9 kg",
      "quantity" => "6",
      "total_price" => "1,711.50 NOK"
    ],
    2 => [
      "description" => "BLOUSES",
      "hs_tariff_code" => "62064000",
      "hs_country_of_origin" => "Italy - EU Non Preferential Origin",
      "status" => "",
      "total_weight" => "0.6 kg",
      "quantity" => "6",
      "total_price" => "1,068.00 NOK"
    ],
    3 => [
      "description" => "BLOUSES",
      "hs_tariff_code" => "62064000",
      "hs_country_of_origin" => "INDIA",
      "status" => "",
      "total_weight" => "2 kg",
      "quantity" => "16",
      "total_price" => "4,564.00 NOK"
    ],
    4 => [
      "description" => "BLOUSES",
      "hs_tariff_code" => "62063000",
      "hs_country_of_origin" => "Italy - EU Non Preferential Origin",
      "status" => "",
      "total_weight" => "4.4 kg",
      "quantity" => "22",
      "total_price" => "3,135.00 NOK"
    ],
    5 => [
      "description" => "DRESSES",
      "hs_tariff_code" => "62044300",
      "hs_country_of_origin" => "INDIA",
      "status" => "",
      "total_weight" => "9 kg",
      "quantity" => "30",
      "total_price" => "9,406.50 NOK"
    ],
    6 => [
      "description" => "DRESSES",
      "hs_tariff_code" => "62044200",
      "hs_country_of_origin" => "INDIA",
      "status" => "",
      "total_weight" => "2.6 kg",
      "quantity" => "6",
      "total_price" => "2,136.00 NOK"
    ],
    7 => [
      "description" => "HAIRCLIPS",
      "hs_tariff_code" => "96151100",
      "hs_country_of_origin" => "CHINA",
      "status" => "",
      "total_weight" => "1.5 kg",
      "quantity" => "84",
      "total_price" => "3,672.75 NOK"
    ],
    8 => [
      "description" => "KIMONOS & JACKETS",
      "hs_tariff_code" => "62043390",
      "hs_country_of_origin" => "INDIA",
      "status" => "",
      "total_weight" => "0.6 kg",
      "quantity" => "4",
      "total_price" => "1,141.00 NOK"
    ],
    9 => [
      "description" => "KNITTED TOPS",
      "hs_tariff_code" => "61103099",
      "hs_country_of_origin" => "Italy - EU preferential Origin",
      "status" => "",
      "total_weight" => "66.1 kg",
      "quantity" => "171",
      "total_price" => "58,838.25 NOK"
    ],
    10 => [
      "description" => "SCARVES",
      "hs_tariff_code" => "62144000",
      "hs_country_of_origin" => "INDIA",
      "status" => "",
      "total_weight" => "0.3 kg",
      "quantity" => "5",
      "total_price" => "890.00 NOK"
    ],
    11 => [
      "description" => "SCARVES",
      "hs_tariff_code" => "62143000",
      "hs_country_of_origin" => "CHINA",
      "status" => "",
      "total_weight" => "0.8 kg",
      "quantity" => "18",
      "total_price" => "1,593.00 NOK"
    ],
    12 => [
      "description" => "SCARVES",
      "hs_tariff_code" => "62142000",
      "hs_country_of_origin" => "Italy - EU preferential Origin",
      "status" => "",
      "total_weight" => "3.6 kg",
      "quantity" => "65",
      "total_price" => "9,262.50 NOK"
    ],
    13 => [
      "description" => "SCARVES",
      "hs_tariff_code" => "62149000",
      "hs_country_of_origin" => "INDIA",
      "status" => "",
      "total_weight" => "0.2 kg",
      "quantity" => "6",
      "total_price" => "531.00 NOK"
    ],
    14 => [
      "description" => "SCARVES",
      "hs_tariff_code" => "62144000",
      "hs_country_of_origin" => "Italy - EU Non Preferential Origin",
      "status" => "",
      "total_weight" => "1.3 kg",
      "quantity" => "18",
      "total_price" => "2,478.00 NOK"
    ],
    15 => [
      "description" => "TROUSERS",
      "hs_tariff_code" => "62046231",
      "hs_country_of_origin" => "Italy - EU Non Preferential Origin",
      "status" => "",
      "total_weight" => "3.6 kg",
      "quantity" => "8",
      "total_price" => "2,854.00 NOK"
    ]
  ],
  "leveringsbetingelser" => "DAP Black Colour ApS, C/O Inter-Distrans AS Drammen",
  "samlefaktura" => "vedr. Salgsfaktura: 5004956, 5004957, 5004958, 5004959, 5004960, 5004961, 5004962, 5004963, 5004964, 5004965, 5004966, 5004967, 5004968, 5004969, 5004970, 5004971, 5004972, 5004973, 5004974",
  "track_no" => "92181269643, 92181269645, 92181269646, 92181269647, 92181269648, 92181269649, 92181269650, 92181269651, 92181269657, 92181269658, 92181269662, 92181269664, 92181269665, 92181269666, 92181269668, 92181269680, 92181269682, 92181269683, 92181269694, 92181269698, 92181269707",
  "vaegt" => "103 kg",
  "colli" => "21",
  "exporter" => [
    "customs_authorities_license_no" => "DK32563546",
    "declaration" => "unless otherwise clearly indicated, these products are of EU origin."
  ],
  "sender" => [
    "company_name" => "Black Colour ApS",
    "address" => "Håndværkervej 2",
    "city" => "Hadsund Nordjylland",
    "postal_code" => "9560",
    "country" => "DK",
    "vat_number" => "DK32563546",
    "eori_number" => "GB322320752000",
    "voec_number" => "33485657 / 98575677",
    "email" => "info@blackcolour.dk"
  ]
];

$result['ci-11-1'] = [
  "Salgsfaktura" => [
    0 => "5004956",
    1 => "5004957",
    2 => "5004958",
    3 => "5004959",
    4 => "5004960",
    5 => "5004961",
    6 => "5004962",
    7 => "5004963",
    8 => "5004964",
    9 => "5004965",
    10 => "5004966",
    11 => "5004967",
    12 => "5004968",
    13 => "5004969",
    14 => "5004970",
    15 => "5004971",
    16 => "5004972",
    17 => "5004973",
    18 => "5004974"
  ]
];

//BODO MOLLER CHEMIE - DK618744
$result['ci-12'] = [ 
  "company" => "Bodo Möller Chemie Denmark Aps",
  "address" => "Dam Holme 14 - 16",
  "city" => "Stenløse",
  "postalCode" => "DK-3660",
  "country" => "Denmark",
  "phone" => "+45 4816 3470",
  "fax" => "",
  "email" => "info@bm-chemie.dk",
  "website" => "www.bm-chemie.dk",
  "bankAccounts" => [
    0 => [
      "bankName" => "Danske Bank",
      "currency" => "DKK",
      "IBAN" => "DK15 3000 0010 3037 37",
      "BIC" => "DABADKKK",
      "REG" => "3430",
      "accountNumber" => "10303737"
    ],
    1 => [
      "bankName" => "Danske Bank",
      "currency" => "EURO",
      "IBAN" => "DK10 3000 3431 371 219",
      "BIC" => "DABADKKK",
      "REG" => "3430",
      "accountNumber" => "3431371219 EUR"
    ]
  ],
  "registeredPlaceOfBusiness" => "Bodo Möller Chemie Denmark Aps",
  "registeredAddress" => "Dam Holme 14 - 16",
  "registeredCity" => "Stenløse",
  "registeredPostalCode" => "DK-3660",
  "registeredCountry" => "Denmark",
  "VATNumber" => "DK31622670",
  "customerReference" => "ORDER-NO.: 800012 DATED 06.10.2023",
  "customerNumber" => "NO200017",
  "customerName" => "Jotun",
  "customerOrderNumber" => "100905 DK21620125.10.2023LO",
  "customerOrderDate" => "25.10.2023",
  "customerOrderBr" => "S 1",
  "invoiceNumber" => "DK618744",
  "deliveryDate" => "25.10.2023",
  "deliveryNote" => "DK419599",
  "shipment" => "DAP",
  "customerVATNumber" => "NO929560191",
  "invoiceDetails" => [
    0 => [
      "position" => "1",
      "itemNumber" => "2006016",
      "quantity" => "14",
      "unit" => "1.000",
      "totalQuantity" => "14.000 KG",
      "unitPrice" => "1.310,00",
      "totalPrice" => "18.340,00",
      "description" => "Speswhite PD, 1.000 kg - Bigbag",
      "productType" => "Kaolin - BULK",
      "tariffNumber" => "25070020",
      "originCountry" => "Great Britain",
      "paymentTerms" => "UNTIL 24.11.2023 NET",
    ]
  ],
  "totalAmount" => "18.340,00",
  "paymentTerms" => "UNTIL 24.11.2023 NET",
  "contradiction" => "We hereby contradict by order given purchase and delivery terms. Debet S.E.&amp;O. VAT free according to §4,(1)b,6A UStG."
];

//BYIC - consolidated-invoice-9895-2024-03-22-14-01-11
$result['ci-13'] = [
  "consolidated_invoice" => [
    "date" => "2024-03-22",
    "time" => "14:01:12",
    "invoice_number" => "CR-NO-000014077",
    "sender" => [
      "name" => "BY IC",
      "org_vat" => "DK 37635715",
      "address" => "Østerågade 17, 2 th. 9000 Aalborg"
    ],
    "receiver" => [
      "name" => "BY IC",
      "org_number" => "925216283 MVA",
    ],
    "delivery_condition" => "DAP",
    "payment_condition" => "DAP",
    "total_invoice_value" => "66141.06 NOK",
    "total_net_weight_kg" => 26.85,
    "total_gross_weight_kg" => 31.25,
    "pallets" => 2,
    "references" => [
      0 => "NO-61076-1",
      1 => "B2B157054-1",
      2 => "B2B157045-1",
      3 => "NO-61237-1",
      4 => "B2B157343-1",
      5 => "NO-61313-1",
      6 => "NO-61370-1",
      7 => "NO-61392-1",
      8 => "NO-61394-1",
      9 => "NO-61395-1",
      10 => "NO-61415-1",
      11 => "NO-61420-1",
      12 => "NO-61452-1",
      13 => "NO-61468-1",
      14 => "NO-61484-1",
      15 => "NO-61485-1",
      16 => "NO-61486-1",
      17 => "NO-61562-1",
      18 => "NO-61563-1",
      19 => "NO-61564-1",
      20 => "NO-61565-1",
      21 => "NO-61566-1",
      22 => "NO-61568-1",
      23 => "NO-61569-1",
      24 => "NO-61573-1",
      25 => "NO-61575-1",
      26 => "NO-61576-1",
      27 => "NO-61577-1",
      28 => "NO-61579-1",
      29 => "NO-61580-1",
      30 => "NO-61581-1",
      31 => "NO-61582-1",
      32 => "NO-61584-1",
      33 => "NO-61585-1",
      34 => "NO-61586-1",
      35 => "NO-61587-1",
      36 => "B2B157344-1",
      37 => "B2B157344-1",
      38 => "B2B157045-1",
      39 => "B2B157049-1",
      40 => "B2B157049-1",
      41 => "B2B157313-1",
      42 => "B2B157313-1",
      43 => "NO-61606-1"
    ]
  ]
];

//COMMITTEE - commercial_invoice_1311
$result['ci-14'] = [
  "Committee" => "Committee-xxiv",
  "Address" => "co/ intravat AS Nedre strandgate 80 3012 Drammen Norway",
  "VAT No." => "922905886",
  "Commercial Invoice" => [
    "Number" => "1311",
    "Date" => "2024-03-22",
    "Collie" => 12,
    "Delivery Term" => "DAP",
    "Customs Credit No." => "33479445",
    "Total Styles" => 7,
    "Styles" => [
      0 => [
        "Style No." => "B4005",
        "Style Name" => "NOOS-Marija Jeans Wash Washington",
        "Quality" => "Cotton 93% Polyester 6% Elastan 1%",
        "Tariff" => "6204623990TR T2",
        "Origin" => "T2",
        "KG" => 5.1,
        "Qty" => 17,
        "Price NOK" => 7650
      ],
      1 => [
        "Style No." => "J234073PD",
        "Style Name" => "Anika Support Chino",
        "Quality" => "Tencel 53% Cotton 44% Elastane 3%",
        "Tariff" => "6104620000TR T2",
        "Origin" => "T2",
        "KG" => 35.7119,
        "Qty" => 525,
        "Price NOK" => 62475
      ],
      2 => [
        "Style No." => "J234239PD",
        "Style Name" => "Trisha Jeans White",
        "Quality" => "Cotton 92% Elastan 8%",
        "Tariff" => "6104620000TR T2",
        "Origin" => "T2",
        "KG" => 2.1,
        "Qty" => 7,
        "Price NOK" => 3500
      ],
      3 => [
        "Style No." => "J234413PD",
        "Style Name" => "Marija Jeans White",
        "Quality" => "Cotton 92% Elastan 8%",
        "Tariff" => "6104620000TR T2",
        "Origin" => "T2",
        "KG" => 4.5,
        "Qty" => 15,
        "Price NOK" => 7500
      ],
      4 => [
        "Style No." => "J234414PD",
        "Style Name" => "Jelena Jeans White",
        "Quality" => "Cotton 92% Elastan 8%",
        "Tariff" => "6104620000TR T2",
        "Origin" => "T2",
        "KG" => 8.1,
        "Qty" => 27,
        "Price NOK" => 13500
      ],
      5 => [
        "Style No." => "J234462PD",
        "Style Name" => "Trisha Jeans Colors",
        "Quality" => "Cotton 65.5% Pre-Consumer Recycled Cotton 16% Recycled Polyester 9% Reused Cotton 7% Elastan 2.5%",
        "Tariff" => "6104620000TR T2",
        "Origin" => "T2",
        "KG" => 0.3,
        "Qty" => 1,
        "Price NOK" => 375
      ],
      6 => [
        "Style No." => "J234468PD",
        "Style Name" => "Anika Midi Skirt Wash Veneto",
        "Quality" => "Cotton 100%",
        "Tariff" => "6204520090TR T2",
        "Origin" => "T2",
        "KG" => 3.4,
        "Qty" => 17,
        "Price NOK" => 7361
      ]
    ],
    "Total Price NOK" => 102361,
    "Collect Lines" => [
      0 => [
        "Tariff" => "6104620000TR T2",
        "Origin" => "T2",
        "KG" => 50.7169,
        "Qty" => 87.35
      ],
      1 => [
        "Tariff" => "6204520090TR T2",
        "Origin" => "T2",
        "KG" => 3.4,
        "Qty" => 7.361
      ],
      2 => [
        "Tariff" => "6204623990TR T2",
        "Origin" => "T2",
        "KG" => 5.1,
        "Qty" => 7.65
      ]
    ],
    "Total Collect Lines" => [
      "Total KG" => 59.2203,
      "Total Price NOK" => 102361
    ],
    "Total Stock Type" => [
      0 => [
        "Stock Type" => "T1",
        "KG" => 0,
        "Qty" => 0,
        "Total Price NOK" => 0
      ],
      1 => [
        "Stock Type" => "T2",
        "KG" => 59.2203,
        "Qty" => 102361,
        "Total Price NOK" => 102361
      ]
    ],
    "Total Amount" => [
      "Amount" => 102361,
      "Freight" => 1800,
      "Total" => 104161,
    ],
    "Commercial Invoice based on" => "Sale Invoices: 312284, 312286-312295 and the Delivery Nos: 143367, 143373-143374, 143378, 143384-143385, 143393-143395, 143403, 143422",
    "Exporter" => "The exporter of the products covered by this document customs authorization No. DK 12-022041 declares that except where otherwise cleary indicated, these products are of EEA/Turkish preferential origin"
  ]
];

//COMMITTEE - commercial_invoice_1311
$result['ci-14-1'] = [
  "Sale Invoices" => [
    0 => 312284,
    1 => 312286,
    2 => 312287,
    3 => 312288,
    4 => 312289,
    5 => 312290,
    6 => 312291,
    7 => 312292,
    8 => 312293,
    9 => 312294,
    10 => 312295
  ],
  "Delivery Nos" => [
    0 => 143367,
    1 => 143373,
    2 => 143374,
    3 => 143378,
    4 => 143384,
    5 => 143385,
    6 => 143393,
    7 => 143394,
    8 => 143395,
    9 => 143403,
    10 => 143422
  ]
];

//HORN BORDPLADER - 2024-02-01 Proformafaktura 6205
$result['ci-15'] = [
  "company" => [
    "name" => "Horn Bordplader A/S",
    "address" => "Farvervej 40, 7490 Aulum, Danmark",
    "VAT_number" => "25798902",
    "org_number" => "992659823MVA"
  ],
  "invoice" => [
    "number" => "6205",
    "date" => "01-02-2024",
    "total_amount" => "75.492,82 NOK",
    "items" => [
      0 => [
        "description" => "Bordplader",
        "quantity" => 1,
        "unit_price" => "14.293,25",
        "discount" => "",
        "amount" => "14.293,25",
        "weight" => "83,23",
        "currency" => "NOK"
      ],
      1 => [
        "description" => "Bordplader",
        "quantity" => 1,
        "unit_price" => "36.837,84",
        "discount" => "",
        "amount" => "36.837,84",
        "weight" => "77,64",
        "currency" => "NOK"
      ],
      2 => [
        "description" => "Bordplader",
        "quantity" => 1,
        "unit_price" => "24.361,73",
        "discount" => "",
        "amount" => "24.361,73",
        "weight" => "76,75",
        "currency" => "NOK"
      ]
    ],
    "total_weight" => "237,62 M2",
    "total_discount" => "0,00",
    "total_taxable_amount" => "75.492,82 NOK",
    "total_tax" => "0,00",
    "total_amount_with_tax" => "75.492,82 NOK",
    "related_invoices" => [
      0 => "S0054801",
      1 => "S0056163",
      2 => "S0058895"
    ]
  ]
];

//MILLARCO - Millarco_PROFORMA_faktura_NOPF52071
$result['ci-16'] = [
  "company" => [
    "name" => "Einhell Nordic A/S",
    "website" => "www.einhell.dk",
    "address" => "Rokhøj 26, 8520 Lystrup, Danmark",
    "phone" => "+45 8743 4200",
    "fax" => "+45 8743 4220",
    "email" => "info@einhell.dk",
    "VAT_number" => "31622875",
    "bank_details" => [
      "bank_name" => "Nordea NO",
      "account_number" => "6026.05.57044",
      "SWIFT" => "",
      "IBAN" => "NO7760260557044"
    ]
  ],
  "recipient" => [
    "name" => "Einhell Nordic A/S",
    "address" => "c/o IntraVAT, Nedre Strandgate 80, 3012 Drammen, NO",
    "VAT_number" => "923 791 957 MVA"
  ],
  "invoice_details" => [
    "invoice_number" => "NOPF52071",
    "date" => "22-03-2024",
    "Incoterm" => "DDP",
    "page" => "1"
  ],
  "products" => [
    0 => [
      "product_number" => "4020600",
      "product_description" => "Einhell TE-AC 6 stillegående kompressor 6 liter 8 bar",
      "product_code" => "84148022",
      "country_region" => "CN",
      "quantity_per_package" => "4",
      "quantity" => "4.00",
      "amount" => "5,944.15"
    ],
    1 => [
      "product_number" => "4152590",
      "product_description" => "Einhell TC-IG 2000 bensindrevet strømaggregat",
      "product_code" => "85022020",
      "country_region" => "CN",
      "quantity_per_package" => "1",
      "quantity" => "1.00",
      "amount" => "4,749.05"
    ],
    2 => [
      "product_number" => "4499910",
      "product_description" => "Einhell GC-CS 235 E kjedeslipemaskin Ø145 mm",
      "product_code" => "84603900",
      "country_region" => "CN",
      "quantity_per_package" => "20",
      "quantity" => "20.00",
      "amount" => "15,725.00"
    ],
    3 => [
      "product_number" => "49511100",
      "product_description" => "Kwb forsenkersett 8/12/16 mm 3 deler",
      "product_code" => "82077090",
      "country_region" => "CN",
      "quantity_per_package" => "1",
      "quantity" => "5.00",
      "amount" => "310.08"
    ]
  ],
  "total" => [
    "number_of_packages" => "26",
    "total_weight_kg" => "245.62",
    "total_amount_NOK" => "26,728.28"
  ],
  "related_invoices" => [
    0 => "NO120111",
    1 => "NO120112",
    2 => "NO120113",
    3 => "NO120114",
    4 => "NO120115",
    5 => "NO120119"
  ],
  "shipment_numbers" => [
    0 => "40170733740964096476",
    1 => "40170733740964096490",
    2 => "40170733740964096551",
    3 => "40170733740964096599",
    4 => "40170733740964096612",
    5 => "40170733740964096636"
  ],
  "related_sales_orders" => [
    0 => "98214",
    1 => "98217",
    2 => "98218",
    3 => "98220",
    4 => "98222",
    5 => "98903"
  ]
];

//NOSCOMED - 14948FakturaNOSAM2925_250324_094043
$result['ci-17'] = [
  "Samlefaktura" => [
    "Afsender" => [
      "Adresse" => "Svanemøllevej 11, 2100 København Ø, Danmark"
    ],
    "Fakturaadresse" => [
      "Adresse" => "Tomtegata 80, 3012 Drammen, Norge",
      "Org Nr." => "915 704 573 MVA",
      "C/O" => "IntraVAT"
    ],
    "Told Kredit Nr." => "33 20 19 04",
    "Leveringsbetingel se" => "DAP ( Noscomed Medical Supply A/S, C/O IntraVAT, Drammen )",
    "FakturaNr." => "NOSAM2925",
    "KundeNr." => "NOP9998",
    "Specifikation" => [
      "Varenummer" => [
        0 => [
          "Beskrivelse" => "Hydra-Cool Serum 15 ml",
          "BrugsTarifNr." => "33049900",
          "Oprindelsesland" => "USA",
          "Antal" => 5,
          "Pris (NOK)" => 379.68,
          "Beløb (NOK)" => 1898.4
        ],
        1 => [
          "Beskrivelse" => "GeneXC Serum 15 ml",
          "BrugsTarifNr." => "33049900",
          "Oprindelsesland" => "USA",
          "Antal" => 1,
          "Pris (NOK)" => 566.68,
          "Beløb (NOK)" => 566.68
        ],
        2 => [
          "Beskrivelse" => "Firming Complex 50 ml",
          "BrugsTarifNr." => "33049900",
          "Oprindelsesland" => "USA",
          "Antal" => 1,
          "Pris (NOK)" => 818.86,
          "Beløb (NOK)" => 818.86
        ],
        3 => [
          "Beskrivelse" => "C-Eye Serum Advance+ 15 ml",
          "BrugsTarifNr." => "33049900",
          "Oprindelsesland" => "USA",
          "Antal" => 1,
          "Pris (NOK)" => 402.71,
          "Beløb (NOK)" => 402.71
        ],
        4 => [
          "Beskrivelse" => "Extreme Protect SPF30 100 g",
          "BrugsTarifNr." => "33049900",
          "Oprindelsesland" => "USA",
          "Antal" => 6,
          "Pris (NOK)" => 490.76,
          "Beløb (NOK)" => 2944.56
        ],
        5 => [
          "Beskrivelse" => "Extreme Protect SPF40 PerfecTint Bronze 100 g",
          "BrugsTarifNr." => "33049900",
          "Oprindelsesland" => "USA",
          "Antal" => 2,
          "Pris (NOK)" => 469.94,
          "Beløb (NOK)" => 939.88
        ],
        6 => [
          "Beskrivelse" => "SPF 5+1 DEAL - SPF30",
          "BrugsTarifNr." => "33049900",
          "Oprindelsesland" => "USA",
          "Antal" => 1
        ]
      ],
      "Total" => [
        "Antal" => 17,
        "Beløb (NOK)" => 7571.09
      ]
    ]
  ]
];

//PANDORA KITCHEN LIVING - Proforma 5 07-03-2024
$result['ci-18'] = [
  "Date" => "07-03-2024",
  "Total Gross Weight" => "55 Kg",
  "Items" => [
    0 => [
      "Qty" => 2,
      "Unit Price" => "1.496,00 NOK",
      "Total Amount" => "2.992,00 NOK"
    ],
    1 => [
      "Qty" => 1,
      "Unit Price" => "3.192,00 NOK",
      "Total Amount" => "3.192,00 NOK"
    ],
    2 => [
      "Qty" => 1,
      "Unit Price" => "1.540,00 NOK",
      "Total Amount" => "1.540,00 NOK"
    ],
    3 => [
      "Qty" => 1,
      "Unit Price" => "1.356,00 NOK",
      "Total Amount" => "1.356,00 NOK"
    ],
    4 => [
      "Qty" => 1,
      "Unit Price" => "824,00 NOK",
      "Total Amount" => "824,00 NOK"
    ],
    5 => [
      "Qty" => 1,
      "Unit Price" => "392,00 NOK",
      "Total Amount" => "392,00 NOK"
    ]
  ],
  "Total Invoice Value" => "10.296,00 NOK",
  "Freight Cost" => "2.080,00 NOK",
  "Insurance Cost" => "12.376,00 NOK",
  "Address" => [
    "Street" => "Nedre Standgade 80",
    "City" => "Drammen",
    "Country" => "Norge",
    "VAT Number" => "NO 926498010 MVA"
  ],
  "Seller" => [
    "Name" => "Pandora Kitchen ApS",
    "VAT Number" => "DK926498010",
    "Street" => "Frederikshåbvej 53",
    "City" => "Randbøldal",
    "Country" => "Danmark"
  ],
  "Reference Numbers" => [
    0 => "11",
    1 => "12",
    2 => "13",
    3 => "14"
  ],
  "Items Description" => [
    0 => "Foldemadrass - NEW YORK",
    1 => "SOFA - Utendørs",
    2 => "3-delt foldemadrass - Medium - Vattert",
    3 => "3-delt foldemadrass - Medium",
    4 => "Ryggputer - 90 cm",
    5 => "Normal stoftype betræk til klassisk skummadras, 90x200x12",
    6 => "TRIANGLE - Utendørs Sakkosekk"
  ],
  "HS Code" => "94041000",
  "Country of Manufacture" => "DK",
  "Total Pieces" => 6,
  "Currency" => "NOK",
  "Delivery Terms" => "DAP",
  "Transportation" => "Bring Home",
  "Invoice Number" => "Invoice No5",
  "Contact Number" => "+45 75 55 07 26",
  "Company" => "Pandora Living",
  "Additional Information" => "Eksportøren af varer, der er omfattet af dette dokument, erklærer, at varerne, medmindre andet tydeligt er angivet, har præferenseoprindelse i DK"
];

//REX HOLM (ID) - Proformafaktura PROF02421
$result['ci-19'] = [
  "invoice_number" => "PROF02421",
  "exporter_customs_authorisation" => "DK/19/862390",
  "EU_preferential_origin" => true,
  "invoice_details" => [
    "number_of_invoices" => 2,
    "number_of_packages" => 10,
    "total_amount_excl_tax" => 15324.76,
    "currency" => "NOK",
    "products" => [
      0 => [
        "tariff_code" => "61103099",
        "origin_country" => "CN",
        "description" => "Microfleece cardigan, dame",
        "quantity" => 25,
        "unit_price" => 239.25,
        "total_price" => 5981.25
      ],
      1 => [
        "tariff_code" => "62053000",
        "origin_country" => "BD",
        "description" => "Skjorter , Polyester / Bomuld T/C",
        "quantity" => 25,
        "unit_price" => 371.68,
        "total_price" => 9292.12
      ],
      2 => [
        "tariff_code" => "65050030",
        "origin_country" => "CN",
        "description" => "Caps",
        "quantity" => 1,
        "unit_price" => 51.39,
        "total_price" => 51.39
      ]
    ]
  ],
  "invoice_references" => [
    "orders" => [
      0 => "9008122 (S4594042), 9008122 (S4594564)",
      1 => "9008122 (S4595772), 9008122 (S4596684)",
      2 => "9008122 (S4598151), 9008122 (S4599202)",
      3 => "9008122 (S4601022), 9008122 (S4603060)",
      4 => "9008121 (W7434382)"
    ],
    "packages" => [
      0 => "9008121 (921865057208)",
      1 => "9008121 (921865057239)",
      2 => "9008122 (921865057758)",
      3 => "9008122 (921865057772)",
      4 => "9008122 (921865057802)",
      5 => "9008122 (921865057765)",
      6 => "9008122 (921865057888)",
      7 => "9008122 (921865057963)",
      8 => "9008122 (921865057857)",
      9 => "9008122 (921865057840)"
    ]
  ],
  "net_weight_kg" => 22.6425,
  "gross_weight_kg" => 28.685,
  "sender_details" => [
    "company_name" => "ID Identity A/S",
    "address" => "Lægårdvej 138, DK-7500 Holstebro",
    "phone" => "+4597492144",
    "fax" => null,
    "VAT_number" => "DK 16278874",
    "website" => "www.id.dk"
  ],
  "recipient_details" => [
    "company_name" => "Rexholm A/S c/o IntraVAT",
    "address" => "Nedre Strandgate 80, NO-3012 Drammen, Norge",
    "fax" => null,
    "VAT_number" => "16278874",
  ],
  "shipping_date" => "21-03-2024",
  "org_number" => "995167352",
  "customs_number" => "DAP",
  "delivery_terms" => "DAP Leveringsbetingelser"
];

//SEBRA INTERIOR - Samlefaktura 11231
$result['ci-20'] = [
  "Samlefaktura" => [
    "Afsender" => "Sebra Interiør ApS",
    "Fakturadato" => "27-02-2024",
    "Fakturanummer" => "11231",
    "Adresse" => "Lillebæltsvej 93, DK-6715 Esbjerg N",
    "Kundenummer" => "",
    "Reference" => "Jette",
    "CVR nr." => "28864663",
    "Faktura adresse" => [
      "Virksomhed" => "Sebra Interiør ApS",
      "C/O" => "IntraVat",
      "Org. Nr." => "914 821 924 MVA",
      "Adresse" => "Nedre Strandgate 80, 3012 Drammen, Norge",
      "Told kredit nr." => "33 03 77 - 55"
    ],
    "Leveringsbetingelse" => "DAP ( Sebra Interiør ApS, C/O Inter-Distrans AS, Drammen )",
    "Varebetegnelse" => [
      0 => [
        "Vare" => "T1 varer",
        "Antal" => "0 STK",
        "Enh" => "-",
        "Pris" => "",
        "Beløb" => ""
      ],
      1 => [
        "Vare" => "T2 varer",
        "Antal" => "4419 STK",
        "Pris" => "237.328,65",
        "Beløb" => ""
      ]
    ],
    "Samlefakturaen dækker" => "salgsfaktura 8351 og 8352",
    "Total NOK" => "237.328,65",
    "Total vægt i kg." => "1.232,00",
    "Total Kolli" => "9",
    "Eksportøren af varer" => "erklærer at varerne, med mindre andet tydeligt er angivet har præferenceoprindelse i EØS og Kina (land er blot eksempel)"
  ]
];



      return $result[$format];
    }  
}