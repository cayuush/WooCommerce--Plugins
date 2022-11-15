<?php

// $username = "sostec";
// $password = "sostec@123";
// $MerchantID ="654486";
// $amount ='1';
// $CustomerWalletID ='0615080326';



function login($username,$password)
{

	$data = array();
	$curl = curl_init();
	$authorization = base64_encode($username . ':' . $password);
	curl_setopt_array($curl, array(
		CURLOPT_URL => "https://staging.premierwallets.com/APIUAT/api/MerchantLogin",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_HTTPHEADER => array(
			"Content-Type: application/json",
			"Content-Length: 0",
			"MachineID: ds@#13ds!WE4C#FW$672@",
			"ChannelID: 104",
			"DeviceType: 205",
			"Authorization: Basic $authorization"
		),
	));
	$response = curl_exec($curl);
	curl_close($curl);
	//json decode
	$character = json_decode($response);
	global $token;
	$token = $character->Data->Token;
	$api_status1 = json_decode($response);
	$code1 = $api_status1->Response->Code;
	$message1 = $api_status1->Response->Messages;
	return [
		'code' => $code1,
		'token' => $token
	];
}

function pushPayment($token,$amount,$CustomerWalletID,$MerchantID)
	{
		
		if(strlen((string)$CustomerWalletID)>9)
		{
		  if(strlen((string)$CustomerWalletID)==10 || strlen((string)$CustomerWalletID)==12)
				{
						
				   if (substr($CustomerWalletID, 0,2) == '06')
						{
							 $CustomerWalletID = str_replace('06', '002526', $CustomerWalletID);
							 //echo $CustomerWalletID;
								
						} 
				   else if(substr($CustomerWalletID, 0,3) == '252')
						{
						 $CustomerWalletID = str_replace('252', '002526', $CustomerWalletID);
						 echo $CustomerWalletID.'   '.'252';
							
						}
				   else{
						 wc_add_notice( 'Please enter the Phone Number for Billing (Format: 00252615080326 )', 'error' );
							 return;
				   }
			   }
   
		}
	   //	echo $CustomerWalletID .'     '.'Push';
		$body = json_encode(array(
			'CustomerWalletID' => $CustomerWalletID,
			'Amount' => $amount,
			'Remarks' => 'ABYAZ',
			'Category'=> '1',
			'LoginUserName'=> $MerchantID,
		));
		$header =array(
				"MachineID: ds@#13ds!WE4C#FW$672@",
				"ChannelID: 104",
				"DeviceType: 205",
				"Content-Type: application/json",
				"Authorization: Bearer $token"
			);
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://staging.premierwallets.com/APIUAT/api/PushPayment',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>$body,
			CURLOPT_HTTPHEADER => $header
		));

		$json_response = curl_exec($curl);
		curl_close($curl);
		$api_data = json_decode($json_response);
	   // var_dump($json_response.'      ' .'Push');
		global $transaction_id;
		
		$api_status2 = json_decode($json_response);
		$code = $api_status2->Response->Code;
		if($code != '001'){
			$error_message =$api_status2->Response->Errors[0]->Message;
			$transaction_id ='';
			
		}
		else{
			$error_message ='';
			$transaction_id = $api_data->Data->TransactionID;
		}
		
	
		return [
			'code' => $code,
			'TransactionID' => $transaction_id,
			 'error_message' =>$error_message
		];
		
	}
		
function callBackApi($token,$transaction_id,$MerchantID)
        	{
        	     //$order = new WC_Order( $order_id );
              // echo $transaction_id;
                      $data = json_encode(array(
                        "TransactionID"  =>$transaction_id,
                        "LoginUserName" => $MerchantID
                        ));
        
        		$curl = curl_init();
        		curl_setopt_array($curl, array(
        			CURLOPT_URL => 'https://staging.premierwallets.com/APIUAT/api/GetPaymentDetails',
        			CURLOPT_RETURNTRANSFER => true,
        			CURLOPT_ENCODING => '',
        			CURLOPT_MAXREDIRS => 10,
        			CURLOPT_TIMEOUT => 0,
        			CURLOPT_FOLLOWLOCATION => true,
        			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        			CURLOPT_CUSTOMREQUEST => 'POST',
            		CURLOPT_POSTFIELDS => $data,
        			CURLOPT_HTTPHEADER => array(
        				"MachineID: ds@#13ds!WE4C#FW$672@",
        				"ChannelID: 104",
        				"DeviceType: 205",
        				"Content-Type: application/json",
        				"Authorization: Bearer $token"
        			),
        		));
        
        		$json_response_callBack= curl_exec($curl);
        		curl_close($curl);
        			//json decode
        			$api_status_callBack = json_decode($json_response_callBack);
            		global $status_callBack;
            		$status_callBack = $api_status_callBack->Data->Status;
            		$api_callback_code = json_decode($json_response_callBack);
            		$code_callBack = $api_callback_code->Response->Code;
        			return [
        			'codeCallback' => $code_callBack,
        			'status' => $status_callBack
        		];
                
                  
        	}
	
function walletpayPay_rest($username,$password,$amount,$CustomerWalletID,$MerchantID) 
{
		$code1 = '';
		$code = '';
		$callbackstatus ='';
		//echo $Username;
		 $code1 = login($username,$password);
		 
		 if ($code1['code'] === "001") 
		  {
			$code = pushPayment($code1['token'],$amount,$CustomerWalletID,$MerchantID);
			//	var_dump($code);
			  $trnsaction_code =$code['code'];
			  $error_message =$code['error_message'];
				  // if(!empty( $error_message ) || $code['code'] !== 001 )
				  // {
				  // return;
				
			   // }
		
				if ($code['code'] == "001") 
				{
				   sleep(7);
				   $callbackstatus = callBackApi($code1['token'],$code['TransactionID'],$MerchantID);
				   //var_dump($callbackstatus);
					// echo $code['TransactionID'];
						if($callbackstatus['codeCallback']  != "001" and $callbackstatus['status']  !='Executed')
						{
							echo $callbackstatus['status'] ;
						}
						else{
							echo $callbackstatus['status'] ;
						}
					
				}
				else{
					echo $error_message;
				}
				
		}
	
}