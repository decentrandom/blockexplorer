<?

include "./lib/database.php";
include "./lib/crypto.php";

// 한번에 20개씩 처리
for ($i = 0; $i < 40; $i++) {
	

	$query = "SELECT height FROM ".CHAIN_ID."_blocks ORDER BY height DESC LIMIT 1";
	$result = mysqli_query($database_connect, $query);
	$row = mysqli_fetch_assoc($result);


	$target_height = 1;

	if ($row["height"] > 0) {
		$target_height = $row["height"] + 1;
	}

	$rpc_url = "http://".RPC_ADDR."/block?height=".$target_height;

	$channel = curl_init();

	curl_setopt($channel, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($channel, CURLOPT_URL, $rpc_url);
	$result = curl_exec($channel);
	curl_close($channel);

	$number_of_updates = 0;

	$obj = json_decode($result, true);

	// 아직 블록이 생성되지 않은 경우
	if ($obj["error"]["message"] == "Internal error") {
		exit;
	}

	$block_hash = $obj["result"]["block_meta"]["block_id"]["hash"];
	$block_time = $obj["result"]["block_meta"]["header"]["time"];
	$proposer = $obj["result"]["block_meta"]["header"]["proposer_address"];
	$number_of_txs = $obj["result"]["block_meta"]["header"]["num_txs"];
	
	if (!$block_hash) {
		exit;
	}
	
	$query = "SELECT height FROM ".CHAIN_ID."_blocks WHERE height='".$target_height."' LIMIT 1";
	$result = mysqli_query($database_connect, $query);
	
	if (mysqli_num_rows($result) > 0) {
		exit;
	}

	$query = "INSERT INTO ".CHAIN_ID."_blocks (height, block_hash, block_time, proposer, number_of_txs) VALUES ('".$target_height."','".$block_hash."','".$block_time."','".$proposer."','".$number_of_txs."')";
	mysqli_query($database_connect, $query);
	
	// TX가 있는 경우
	if ($number_of_txs > 0) {
		
		$total_txs = count($obj["result"]["block"]["data"]["txs"]);
		
		for ($j = 0; $j < $total_txs; $j++) {
			
			$raw_tx = $obj["result"]["block"]["data"]["txs"][$j];
			$tx_hash = get_tx_hash($raw_tx);
			
			$sub_query = "INSERT INTO ".CHAIN_ID."_txs (hash, height) VALUES ('".$tx_hash."','".$target_height."')";
			mysqli_query($database_connect, $sub_query);
			
		}
		
	}
	
	
}

?>