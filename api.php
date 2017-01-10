<?php
	error_reporting(E_ALL);
	$api = true;
	require "scripts/pi-hole/php/password.php";
	require "scripts/pi-hole/php/auth.php";

	check_cors();
	require("scripts/pi-hole/php/FTL.php");
	$socket = connectFTL("127.0.0.1");
	header('Content-type: application/json');

	$data = [];

	if (isset($_GET['summary']) || isset($_GET['summaryRaw']))
	{
		sendRequestFTL("stats");
		$return = getResponseFTL();

		$stats = [];
		foreach($return as $line)
		{
			$tmp = explode(" ",$line);

			if(isset($_GET['summary']))
			{
				if($tmp[0] !== "ads_percentage_today")
				{
					$stats[$tmp[0]] = number_format($tmp[1]);
				}
				else
				{
					$stats[$tmp[0]] = number_format($tmp[1], 1, '.', '');
				}
			}
			else
			{
				$stats[$tmp[0]] = $tmp[1];
			}
		}
		$data = array_merge($data,$stats);
	}

	if (isset($_GET['overTimeData10mins']))
	{
		sendRequestFTL("overTime");
		$return = getResponseFTL();

		$domains_over_time = [];
		$ads_over_time = [];
		foreach($return as $line)
		{
			$tmp = explode(" ",$line);
			$domains_over_time[] = $tmp[1];
			$ads_over_time[] = $tmp[2];
		}
		$result = ['domains_over_time' => $domains_over_time,
		           'ads_over_time' => $ads_over_time];
		$data = array_merge($data, $result);
	}

	if (isset($_GET['topItems']) && $auth)
	{
		sendRequestFTL("top-domains");
		$return = getResponseFTL();
		$top_queries = [];
		foreach($return as $line)
		{
			$tmp = explode(" ",$line);
			$top_queries[$tmp[2]] = $tmp[1];
		}

		sendRequestFTL("top-ads");
		$return = getResponseFTL();
		$top_ads = [];
		foreach($return as $line)
		{
			$tmp = explode(" ",$line);
			$top_ads[$tmp[2]] = $tmp[1];
		}

		$result = ['top_queries' => $top_queries,
		           'top_ads' => $top_ads];

		$data = array_merge($data, $result);
	}

	if ((isset($_GET['topClients']) || isset($_GET['getQuerySources'])) && $auth)
	{
		sendRequestFTL("top-clients");
		$return = getResponseFTL();
		$top_clients = [];
		foreach($return as $line)
		{
			$tmp = explode(" ",$line);
			if(count($tmp) == 4)
			{
				$top_clients[$tmp[3]."|".$tmp[2]] = $tmp[1];
			}
			else
			{
				$top_clients[$tmp[2]] = $tmp[1];
			}
		}

		$result = ['top_sources' => $top_clients];
		$data = array_merge($data, $result);
	}

	if (isset($_GET['getForwardDestinations']) && $auth)
	{
		sendRequestFTL("forward-dest");
		$return = getResponseFTL();
		$forward_dest = [];
		foreach($return as $line)
		{
			$tmp = explode(" ",$line);
			if(count($tmp) == 4)
			{
				$forward_dest[$tmp[3]."|".$tmp[2]] = $tmp[1];
			}
			else
			{
				$forward_dest[$tmp[2]] = $tmp[1];
			}
		}

		$result = ['forward_destinations' => $forward_dest];
		$data = array_merge($data, $result);
	}

	if (isset($_GET['getQueryTypes']) && $auth)
	{
		sendRequestFTL("querytypes");
		$return = getResponseFTL();
		$querytypes = [];
		$querytypes["A (IPv4)"] = explode(" ",$return[0])[1];
		$querytypes["AAAA (IPv6)"] = explode(" ",$return[1])[1];

		$result = ['querytypes' => $querytypes];
		$data = array_merge($data, $result);
	}

	if (isset($_GET['getAllQueries']) && $auth)
	{
		sendRequestFTL("getallqueries");
		$return = getResponseFTL();
		$allQueries = [];
		foreach($return as $line)
		{
			$tmp = explode(" ",$line);
			$allQueries[] = $tmp;
		}

		$result = ['data' => $allQueries];
		$data = array_merge($data, $result);
	}

	if (isset($_GET['enable'], $_GET['token']) && $auth) {
		check_csrf($_GET['token']);
		exec('sudo pihole enable');
		$data = array_merge($data, ["status" => "enabled"]);
	}
	elseif (isset($_GET['disable'], $_GET['token']) && $auth) {
		check_csrf($_GET['token']);
		$disable = intval($_GET['disable']);
		// intval returns the integer value on success, or 0 on failure
		if($disable > 0)
		{
			exec("sudo pihole disable ".$disable."s");
		}
		else
		{
			exec('sudo pihole disable');
		}
		$data = array_merge($data, ["status" => "disabled"]);
	}

	echo json_encode($data);

	disconnectFTL();

?>
