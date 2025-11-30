<?php

namespace App\Classes;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use App\Models\ClientCvr;

use GuzzleHttp\Client as GuzzleClient;

class CVRApiClass
{       
    /*CVR API - COMPANY */  
    public function getCVRCompany($cvrNumber, $client_id = null)
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
               
              if(!empty($person_address))
              {
                  $person_address_details = 'Road code:'. (empty($person_address[0]->vejkode ) ? '-' : $person_address[0]->vejkode)
                                          .', Road Name:'. (empty($person_address[0]->vejnavn) ? '-' : $person_address[0]->vejnavn) 
                                          . ', Municipality Code:' . (empty($person_address[0]->kommune->kommuneKode) ? '-' : $person_address[0]->kommune->kommuneKode)
                                          . ', Municipality Name:' . (empty($person_address[0]->kommune->kommuneNavn) ? '-' : $person_address[0]->kommune->kommuneNavn) 
                                          . ', Country code:' . (empty($person_address[0]->landekode) ? '-' : $person_address[0]->landekode)
                                          . ', Postal code:' . (empty($person_address[0]->postnummer) ? '-' : $person_address[0]->postnummer);
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
                      if(strtoupper($organization_name['organisationsNavn'][0]['navn']) == 'DIREKTØR')
                        $lrep_role = 'director';
                      else if(strtolower($organization_name['organisationsNavn'][0]['navn']) == 'reel ejer')
                        $lrep_role = 'ultimate-beneficial-owner';
                      else if(strtoupper($organization_name['organisationsNavn'][0]['navn']) == 'EJERREGISTER')
                        $lrep_role = 'legal-owner';
                      
                      if($lrep_role)  
                      {
                        $organizations_details[] = [                        
                          'id' => '',
                          'lrep_role' => $lrep_role,
                          'lrep_fname' => ($person) ? $person->navn : '', 
                          'lrep_sname' => '',
                          'lrep_address' => (empty($person_address[0]->vejnavn) ? '' : $person_address[0]->vejnavn),
                          'lrep_postcode' => (empty($person_address[0]->postnummer) ? '' : $person_address[0]->postnummer),
                          'lrep_city' => (empty($person_address[0]->bynavn) ? '' : $person_address[0]->bynavn),
                          'lrep_country' => (empty($person_address[0]->landekode) ? '' : $person_address[0]->landekode)
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
