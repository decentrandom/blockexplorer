<?

function get_tx_hash($string) {
	
	$string = base64_decode($string);
	$hex = hash('sha256', $string);
	
	return strtoupper(substr($hex, 0, 65));
}

function get_hash($string) {
	
	$string = base64_decode($string);
	$hex = hash('sha256', $string);
	
	return $hex;
}

function decode_tx($hashing) {
  return hash('sha256', base64_decode($hashing));
}

function decode_tx_to_string($hashing) {
  return base64_decode($hashing);
}

function urand_to_rand($value) {
	
	$value = $value / 1000000;
	$original_string = number_format($value, 6, '.', ',');
	
	$str_length = strlen($original_string);
	
	$return_string = "";
	$zero_target = 0;
	
	for ($i = $str_length; $i > 0; $i--) {
		
		$compare_str = substr($original_string, ($i - 1), 1);
	
		if ($compare_str == "0") {
			$zero_target++;
		} else {
			break;
		}
	}
	
	if ($zero_target > 0 && $zero_target < 6) {
		
		$return_string = substr($original_string, 0, $str_length - $zero_target);
		
	} elseif ($zero_target > 0 && $zero_target == 6) {
		
		$return_string = substr($original_string, 0, $str_length - $zero_target - 1);
		
	} else {
		
		$return_string = $original_string;
	}
	
	return $return_string;
}

function decode_tx_json($result) {
	
	$return_array = array();
	
	$return_array["gas_used"] = strip_to_digit($result["gas_used"]);
	$return_array["gas_wanted"] = strip_to_digit($result["gas_wanted"]);
	$return_array["height"] = strip_to_digit($result["height"]);
	$return_array["event_type"] = 0;
	$return_array["event_data"] = "";
	$return_array["timestamp"] = $result["timestamp"];
	
	if ($result["success"] == "false") {
		
		$return_array["status"] = "2";
		$return_array["log_data"] = $result["logs"][0]["log"]["message"];
		
	} elseif ($result["success"] == "success") { 
		
		$return_array["status"] = "1";
		
	} else {
		
		if ($result["logs"][0]["success"] == "true") {
			
			$return_array["status"] = "1";
			
		} else {
			
			if ($result["code"] == "12" || $result["code"] == "103") {
			
				$error_json = json_decode(stripslashes($result["raw_log"]), true);
				
				$return_array["status"] = "2";
				$return_array["log_data"] = $error_json["message"];
				
				if ($return_array["log_data"] == "") {
					$json_string = stripslashes($result["logs"][0]["log"]);
					$error_json = json_decode($json_string, true);
					$return_array["log_data"] = $error_json["message"];
				}
				
			} else {
				
				$return_array["status"] = "0"; // 정의되지 않은 유형
			}
		}
		
	}
	
	$tx_index = 0;
	
	$number_of_msgs = count($result["tx"]["value"]["msg"]);
	
	for ($i = 0; $i < $number_of_msgs; $i++) {
		
		$msg = $result["tx"]["value"]["msg"][$i];
		
		$msg_type = $msg["type"];
		$return_array["tx"][$tx_index]["tx_type"] = $msg_type;
		
		if ($msg_type == "cosmos-sdk/MsgSend") { // 전송
			
			$return_array["tx"][$tx_index]["event_type"] = 1;
			$return_array["tx"][$tx_index]["from_address"] = strip_to_alphanumeric($msg["value"]["from_address"]);
			$return_array["tx"][$tx_index]["to_address"] = strip_to_alphanumeric($msg["value"]["to_address"]);
			$return_array["tx"][$tx_index]["amount"] = strip_to_digit($msg["value"]["amount"][$tx_index]["amount"]);
			$return_array["tx"][$tx_index]["fee"] = strip_to_digit($result["tx"]["value"]["fee"]["amount"][$tx_index]["amount"]);
			
			$tx_index++;
			
		} elseif ($msg_type == "cosmos-sdk/MsgCreateValidator") { // 검증인 신규 생성
			 
			$return_array["tx"][$tx_index]["event_type"] = 2;
			$return_array["tx"][$tx_index]["from_address"] = strip_to_alphanumeric($msg["value"]["delegator_address"]);
			$return_array["tx"][$tx_index]["destination_validator_address"] = strip_to_alphanumeric($msg["value"]["validator_address"]);
			$return_array["tx"][$tx_index]["amount"] = strip_to_digit($msg["value"]["value"]["amount"]);
			$return_array["tx"][$tx_index]["fee"] = strip_to_digit($result["tx"]["value"]["fee"]["amount"][$tx_index]["amount"]);
			
		} elseif ($msg_type == "cosmos-sdk/MsgDelegate") { // 위임
			 
			$return_array["tx"][$tx_index]["event_type"] = 3;
			$return_array["tx"][$tx_index]["from_address"] = strip_to_alphanumeric($msg["value"]["delegator_address"]);
			$return_array["tx"][$tx_index]["destination_validator_address"] = strip_to_alphanumeric($msg["value"]["validator_address"]);
			$return_array["tx"][$tx_index]["amount"] = strip_to_digit($msg["value"]["amount"]["amount"]);
			$return_array["tx"][$tx_index]["fee"] = strip_to_digit($result["tx"]["value"]["fee"]["amount"][$tx_index]["amount"]);
			
		} elseif ($msg_type == "cosmos-sdk/MsgBeginRedelegate") { // 재위임
			 
			$return_array["tx"][$tx_index]["event_type"] = 4;
			$return_array["tx"][$tx_index]["from_address"] = strip_to_alphanumeric($msg["value"]["delegator_address"]);
			$return_array["tx"][$tx_index]["source_validator_address"] = strip_to_alphanumeric($msg["value"]["validator_src_address"]);
			$return_array["tx"][$tx_index]["destination_validator_address"] = strip_to_alphanumeric($msg["value"]["validator_dst_address"]);
			$return_array["tx"][$tx_index]["amount"] = strip_to_digit($msg["value"]["amount"]["amount"]);
			$return_array["tx"][$tx_index]["fee"] = strip_to_digit($result["tx"]["value"]["fee"]["amount"][$tx_index]["amount"]);
			
		} elseif ($msg_type == "cosmos-sdk/MsgWithdrawDelegationReward") { // 보상수령
			 
			$return_array["tx"][$tx_index]["event_type"] = 6;
			$return_array["tx"][$tx_index]["from_address"] = strip_to_alphanumeric($msg["value"]["delegator_address"]);
			$return_array["tx"][$tx_index]["source_validator_address"] = strip_to_alphanumeric($msg["value"]["validator_address"]);
			$return_array["tx"][$tx_index]["fee"] = strip_to_digit($result["tx"]["value"]["fee"]["amount"][$tx_index]["amount"]);
			
			$number_of_events = count($result["events"]);
			for ($j = 0; $j < $number_of_events; $j++) {
				
				$event = $result["events"][$i];
				
				if ($event["type"] == "withdraw_rewards") {
					
					$number_of_attributes = count($event["attributes"][$tx_index]);
					
					for ($k = 0; $k < $number_of_attributes; $k++) {
						
						$attribute = $event["attributes"][$k];
						
						if ($attribute["key"] == "amount") {
							$return_array["tx"][$tx_index]["amount"] = strip_to_digit($attribute["value"]);
						}
					}
				}
			}
			
			// to-do 멀티 보상 수령을 가능하도록
			$tx_index++;
			
		} elseif ($msg_type == "cosmos-sdk/MsgUnjail") { // Unjail
			
			$return_array["tx"][$tx_index]["event_type"] = 7;
			$return_array["tx"][$tx_index]["from_address"] = strip_to_alphanumeric($msg["value"]["address"]);
			$return_array["tx"][$tx_index]["fee"] = strip_to_digit($result["tx"]["value"]["fee"]["amount"][$tx_index]["amount"]);
		}
	}
	
	return $return_array;

}

function store_tx_data($hash, $result, $database_connect, $chain_id) {
	
	$height = $result["height"];
	$status = $result["status"];
	$log_data = $result["log_data"];
	$number_of_txs = count($result["tx"]);
	$timestamp = $result["timestamp"];
	
	$date = new DateTime($timestamp, new DateTimeZone('Zulu'));
	$datestring = $date->format('Y-m-d H:i:s');
		
	$init_query = "DELETE FROM ".$chain_id."_txs WHERE hash='".$hash."'";
	mysqli_query($database_connect, $init_query);
	
	for ($j = 0; $j < $number_of_txs; $j++) {
		
		$tx = $result["tx"][$j];
		
		
		$update_query = "INSERT INTO ".$chain_id."_txs (hash, status, tx_type, height, fee, gas_used, gas_wanted, from_address, to_address, source_validator_address, destination_validator_address, amount, memo, event_type, log_data, proc_time) VALUES ('".$hash."','".$status."','".$tx["tx_type"]."','".$height."','".$tx["fee"]."','".$tx["gas_used"]."','".$tx["gas_wanted"]."','".$tx["from_address"]."','".$tx["to_address"]."','".$tx["source_validator_address"]."','".$tx["destination_validator_address"]."','".$tx["amount"]."','".$tx["memo"]."','".$tx["event_type"]."','".$log_data."','".$datestring."')";
		mysqli_query($database_connect, $update_query);
		
	}
}

?>