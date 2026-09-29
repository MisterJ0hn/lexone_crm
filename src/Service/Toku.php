<?php

namespace App\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class Toku{
    public $url = "https://api.trytoku.com";
    public $apiKey= "Wyb1WAGsomZ6alcoaG7m83er9Ejv86hOFzdyIRFxsZM";
    public $accountKey="04K_PH7RLR_XuhVZmojjE6q2Z4PBWiteq82GjGTe36g";
    public $opciones="";
    public $header="";

    public function __construct(){

       
    }
    
    public function crearCustomer(bool $enviarCorreo, String $correo, String $rut ,String $nombre, String $telefono,  String $mandato ){
        
        $this->header=array(
            "Content-Type: application/json",
            "Accept: application/json",
            "x-api-key: ".$this->apiKey
        );
    
        $client = new \GuzzleHttp\Client();
        $rut=str_replace(".","",$rut);
        $rut=str_replace("-","",$rut);
        $str_telefono="";
        $str_agentTelefono="";
        $str_agentCorreo="";

        $str_telefono=',"phone_number":"'.$telefono.'"';
        /*if (strlen($telefono)==12) {
            if (strpos($telefono,'+569') !== false) {
                $str_telefono=',"phone_number":"'.$telefono.'"';
                
            } else if(strpos($telefono,'+568') !== false) {
                $str_telefono=',"phone_number":"'.$telefono.'"';
                
            } else if(strpos($telefono,'+567') !== false) {
                $str_telefono=',"phone_number":"'.$telefono.'"';
                
            }
            
        }*/
        
        /*if (strlen($agenteTelefono)==12) {
            if (strpos($agenteTelefono,'+569') !== false) {
                
                $str_agentTelefono=',"agent_phone_number":"'.$agenteTelefono.'"';
            } else if(strpos($agenteTelefono,'+568') !== false) {
                
                $str_agentTelefono=',"agent_phone_number":"'.$agenteTelefono.'"';
            }
        }
        if ($agenteCorreo!=null) {
            $str_agentCorreo=',"default_agent":"'.$agenteCorreo.'"';
        }
        if ($agenteCorreo!=null) {
            $str_agentCorreo=',"default_agent":"'.$agenteCorreo.'"';
        }*/

        
        try {
            error_log('{"send_mail":true,"name":"'.$nombre.'","government_id":"'.$rut.'"'.$str_telefono.', "mail":"'.$correo.'","pac_mandate_id":"'.$mandato.'" '.$str_agentCorreo.$str_agentTelefono.'}',3,"/home/micrm.cl/test/TokuWebhook_log"); 
            $response = $client->request('POST',$this->url.'/customers', [
            'body' =>'{"send_mail":true,"name":"'.$nombre.'","government_id":"'.$rut.'"'.$str_telefono.', "mail":"'.$correo.'","pac_mandate_id":"'.$mandato.'"'.$str_agentCorreo.$str_agentTelefono.'}',
            'headers' =>[
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'x-api-key' => $this->apiKey,
                ],
            ]);
            return $response->getBody();
        } catch (Exception $e) {
            //throw $th;
            error_log('Error customer: '.$e->getMessage().' {"send_mail":true,"name":"'.$nombre.'","government_id":"'.$rut.'"'.$str_telefono.', "mail":"'.$correo.'","pac_mandate_id":"'.$mandato.'"'.$str_agentCorreo.$str_agentTelefono.'}',3,"/home/micrm.cl/test/customer_error_log"); 
            return false;
        }
        
       // echo $response->getBody();
        

       
    }

    public function crearInvoice(String $customer, String $product_id, float $amount, String $due_date, bool $isPaid=false,bool $isVoid=false){

        $body=["customer"=>$customer,
                "product_id"=>$product_id,
                "amount"=>$amount,
                "due_date"=>$due_date,
                "is_paid"=>$isPaid,
                "is_void"=>$isVoid];

        $body=json_encode($body);
       $response="";
    
        try{

    
            $client = new \GuzzleHttp\Client();

            error_log("<br><br> Paso Crear : ".print_r($body),3,"/home/micrm.cl/test/invoice_error");
            
            $response = $client->request('POST', $this->url.'/invoices', [
            'body' => $body,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'x-api-key' => $this->apiKey,
               
            ],
            ]);

            return $response->getBody();

            return false;

        }catch(Exception $e){
            error_log("<br><br> Error Crear : ".$e->getMessage().print_r($body),3,"/home/micrm.cl/test/invoice_error");
            return false;
        }
        error_log("<br><br> Response  Crear : ".print_r($response),3,"/home/micrm.cl/test/TokuWebhook_log");

        

       // echo $response->getBody();

       


    }

    public function anularInvoice(String $invoice){

       
        $this->header=array(
            "Accept: application/json",
            "x-api-key: ".$this->apiKey,
        );
    
        try{

            $client= new \GuzzleHttp\Client();
            $httpClient = $client->Request('POST',$this->url.'/invoices/'.$invoice.'/void',
                    [
                        'headers'=>[
                            'Accept'=>'application/json',
                            'x-api-key'=>$this->apiKey
                            ]
                    ]
                        );

           

        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => $this->url.'/invoices/'.$invoice.'/void',
        //     CURLOPT_CUSTOMREQUEST => 'POST',
        //     CURLOPT_HTTPHEADER => $this->header,
        //     )
        // );


        // $response = curl_exec($curl);
        
        // curl_close($curl);

        error_log("<br><br> Response  Anular : ".print_r($httpClient->getBody()),3,"/home/micrm.cl/test/TokuWebhook_log");
        return $httpClient->getBody();

        }catch(Exception $e){
            error_log("<br><br> Error Anular : ".$e->getMessage(),3,"/home/micrm.cl/test/TokuWebhook_log");
            return false;
        }
        


    }
    

}