<?php

namespace App\Classes;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use App\Models\ClientCvr;
use App\Models\ClientLegalRep;

use GuzzleHttp\Client as GuzzleClient;

class CVRApiClass
{       
    /*CVR API - COMPANY */  
    public function getCVRCompany($cvrNumber, $client_id = null, $refresh = [])
    {
      try
      {       
        $url = "http://distribution.virk.dk/cvr-permanent/virksomhed/_search";
       
        $headers = [                            
          'Content-Type' => 'application/json'
        ];
        $params = [
          'query' => [
            'bool' => [
              'must' => [
                [
                  'match' => [
                    'Vrvirksomhed.cvrNummer' => $cvrNumber 
                  ]
                ]
              ]
            ]
          ]
        ];                 
        $guzzleClient = new GuzzleClient();   
                             
        $response = $guzzleClient->request('POST', $url, [
              'auth' => [
                  'Digitalsense_CVR_I_SKYEN', 
                  '24cca9d3-6acd-4980-96dd-ef4674daee06'
              ],
              'body' => json_encode($params),
              'headers' => $headers
        ]);

        $response_data = json_decode($response->getBody()); 
        
        $client_cvr = [];
        if(!empty($response_data->hits->hits))
        {            
          $participant_Relation =$response_data->hits->hits[0]->_source->Vrvirksomhed->deltagerRelation;
          $jsoncontent = json_decode(json_encode($participant_Relation),true);
         
          $person_address_details = "";

          foreach($participant_Relation as $participant)        
          {
              // Getting the organizations
                 $organizations =   $participant->organisationer;  
                 $organizations_name = json_encode($organizations);
                 $org_array = json_decode(json_encode($organizations),true);
              
                 $person = isset($participant->deltager) ? $participant->deltager->navne[0] : '';
                 $person_address = isset($participant->deltager) ? $participant->deltager->beliggenhedsadresse : '';
               
               // if (stripos(trim($person->navn), "Jonas Pichard Hedegaard") !== false)  
               //    dd(count($person_address), $person_address);
              if(!empty($person_address))
              {
                $last_index = count($person_address) - 1;

                  // $person_address_details = 'Road code:'. (empty($person_address[$last_index]->vejkode ) ? '-' : $person_address[$last_index]->vejkode)
                  //                         .', Road Name:'. (empty($person_address[$last_index]->vejnavn) ? '-' : $person_address[$last_index]->vejnavn) 
                  //                         . ', Municipality Code:' . (empty($person_address[$last_index]->kommune->kommuneKode) ? '-' : $person_address[$last_index]->kommune->kommuneKode)
                  //                         . ', Municipality Name:' . (empty($person_address[$last_index]->kommune->kommuneNavn) ? '-' : $person_address[$last_index]->kommune->kommuneNavn) 
                  //                         . ', Country code:' . (empty($person_address[$last_index]->landekode) ? '-' : $person_address[$last_index]->landekode)
                  //                         . ', Postal code:' . (empty($person_address[$last_index]->postnummer) ? '-' : $person_address[$last_index]->postnummer);

                $person_address_details = 'Road code:'. ($person_address[$last_index]->vejkode ?? '-')
                                          .', Road Name:'. ($person_address[$last_index]->vejnavn ?? '-') . ' ' . ($person_address[$last_index]->husnummerFra ?? '') . ($person_address[$last_index]->bogstavFra ?? '')
                                          . ', Municipality Code:' . ($person_address[$last_index]->kommune->kommuneKode ?? '-')
                                          . ', Municipality Name:' . ($person_address[$last_index]->kommune->kommuneNavn ?? '-') 
                                          . ', Country code:' . ($person_address[$last_index]->landekode ?? '-')
                                          . ', Postal code:' . ($person_address[$last_index]->postnummer ?? '-');
              }
                 
                 foreach($org_array as $organization_name)
                 {
                    $member_details = [];
                    if(!empty($organization_name['medlemsData']))
                    {                      
                      $member_datas = $organization_name['medlemsData'][0]['attributter'];

                      $member_details = [];
                      foreach($member_datas as $member) 
                      {                 
                        $member_details[] = $member['vaerdier'][0]['vaerdi'];
                      }
                    }
                     
                    if($client_id)          
                    {
                      $organizations_details[] = [
                        'organization_name' => $organization_name['organisationsNavn'][0]['navn'],                    
                        'organization_no' => $organization_name['enhedsNummerOrganisation'],  
                        'person_name' => ($person) ? $person->navn : '-', 
                        'person_designation' => $member_details,
                        'person_address' => $person_address_details                        
                      ];
                    }
                    else
                    { 
                      $lrep_role = '';
                      if(strtoupper($organization_name['organisationsNavn'][0]['navn']) == 'DIREKTØR' || $organization_name['organisationsNavn'][0]['navn'] == 'Direktion')
                        $lrep_role = 'director';
                      else if(strtolower($organization_name['organisationsNavn'][0]['navn']) == 'reel ejer')
                        $lrep_role = 'ultimate-beneficial-owner';
                      else if(strtoupper($organization_name['organisationsNavn'][0]['navn']) == 'EJERREGISTER')
                        $lrep_role = 'legal-owner';
                      
                      if($lrep_role)  
                      {
                        $last_index = count($person_address) - 1;

                        // $organizations_details[] = [                        
                        //   'id' => '',
                        //   'lrep_role' => $lrep_role,
                        //   'lrep_fname' => ($person) ? $person->navn : '', 
                        //   'lrep_sname' => '',
                        //   'lrep_address' => (empty($person_address[$last_index]->vejnavn) ? '' : $person_address[$last_index]->vejnavn),
                        //   'lrep_postcode' => (empty($person_address[$last_index]->postnummer) ? '' : $person_address[$last_index]->postnummer),
                        //   'lrep_city' => (empty($person_address[$last_index]->bynavn) ? '' : $person_address[$last_index]->bynavn),
                        //   'lrep_country' => (empty($person_address[$last_index]->landekode) ? '' : $person_address[$last_index]->landekode)
                        // ];

                        $organizations_details[] = [                        
                          'id' => '',
                          'lrep_role' => $lrep_role,
                          'lrep_fname' => ($person) ? $person->navn : '', 
                          'lrep_sname' => '',
                          'lrep_address' => ($person_address[$last_index]->vejnavn ?? '-') . ' ' . ($person_address[$last_index]->husnummerFra ?? '') . ($person_address[$last_index]->bogstavFra ?? ''),
                          'lrep_postcode' => $person_address[$last_index]->postnummer ?? '',
                          'lrep_city' => $person_address[$last_index]->bynavn ?? ($person_address[$last_index]->postdistrikt ?? ''),
                          'lrep_country' => $person_address[$last_index]->landekode ?? ''
                        ];
                      } //has role
                    }
                 }
                 
               // Getting the organizations


          }
         
          if($client_id)          
          {
            // Delete the cvr datas for the client id and insert with updated datas 
            $del_client_cvr = ClientCvr::where('client_id', $client_id)->delete();

            /* Store Client CVR Details */
            foreach($organizations_details as $organizations_detail) 
            {                
              foreach($organizations_detail['person_designation'] as $person_designation)  
              {                    
                $client_cvr = ClientCvr::updateOrCreate(
                  [
                    'client_id' => $client_id, 
                    'organization_name' => $organizations_detail['organization_name'],
                    'organization_no' => $organizations_detail['organization_no'],
                    'person_name' => $organizations_detail['person_name'],                        
                    'person_designation' => $person_designation ,
                    'person_address' => $organizations_detail['person_address']                        
                  ],
                  [
                    'client_id' => $client_id, 
                    'organization_name' => $organizations_detail['organization_name'],
                    'organization_no' => $organizations_detail['organization_no'],
                    'person_name' => $organizations_detail['person_name'],                        
                    'person_designation' => $person_designation ,
                    'person_address' => $organizations_detail['person_address']                        
                  ]
                );
              }                
            }
          } //not direct
          else
          {
            $client_cvr_view = '';
            foreach($organizations_details as $clientlegalrepkey => $organizations_detail) 
            {    
              if($refresh)
              {
                $client_legalrep = ClientLegalRep::updateOrCreate(
                  [
                    'client_id' => $refresh['client_id'],
                    'lrep_fname' => $organizations_detail['lrep_fname'],
                  ],              
                  [                
                    'client_id' => $refresh['client_id'], 
                    'lrep_role' => $organizations_detail['lrep_role'],
                    'lrep_fname' => $organizations_detail['lrep_fname'],       
                    'lrep_sname' => $organizations_detail['lrep_sname'],
                    'lrep_address' => $organizations_detail['lrep_address'],       
                    'lrep_postcode' => $organizations_detail['lrep_postcode'],
                    'lrep_city' => $organizations_detail['lrep_city'],                
                    'lrep_country' => $organizations_detail['lrep_country'],
                    'updated_by' => 1
                  ]
                );
              } // if refresh

              $clientlegalrep = json_decode(json_encode($organizations_detail));
              
              /* -- RENDER VIEW -- */
              $client_cvr_view .= view('_partials._content._company.legalrep-row-repeater', 
                      compact(
                        'clientlegalrepkey',
                        'clientlegalrep'
                      )
                  )->render();
              /* --end RENDER VIEW -- */
            }

            return $client_cvr_view;
          } //direct
        }  
        
        return $client_cvr;
        /*End  Store Client CVR Details */
        
      }
      catch (Exception $e) {
        //return  $e->getMessage();
      }
    }
}
