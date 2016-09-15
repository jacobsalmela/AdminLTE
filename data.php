<?php
    $log = array();
    $gravity = array();
    $hostname = "";
    $clientFilter="";
    $domainFilter="";
    $ipv6 = file_exists("/etc/pihole/.useIPv6");
    $hosts = file_exists("/etc/hosts") ? file("/etc/hosts") : array();

    /*******   Public Members ********/
    function getSummaryData() {
        global $ipv6;

        $domains_being_blocked = gravityCount() / ($ipv6 ? 2 : 1);

        $dns_queries_today = getQueryCount();

        $ads_blocked_today = getAdsCount();

        $ads_percentage_today = $dns_queries_today > 0 ? ($ads_blocked_today / $dns_queries_today * 100) : 0;

        return array(
            'domains_being_blocked' => $domains_being_blocked,
            'dns_queries_today' => $dns_queries_today,
            'ads_blocked_today' => $ads_blocked_today,
            'ads_percentage_today' => $ads_percentage_today,
        );
    }

    function getAdsCount(){
        $count = 0;
        $log = readInLog();
        $gravity = readInGrav();
        $hostname = readInHostname();
        $clientFilter = readInClientFilter();
        $domainFilter = readInDomainFilter();

        foreach($log as $logLine)
        {
            $exploded = explode(" ", $logLine);
            $logType = substr($exploded[count($exploded)-4],0,5) == "query";

            if ($logType == 1)
            {
                $domainRequested = $exploded[count($exploded) -3];
                $requestedFromClient = trim($exploded[count($exploded) -1], "\x00..\x1F");
                $clientIsFiltered = preg_match('/(' . $clientFilter . ')/', $requestedFromClient);
                $domainIsFiltered = preg_match('/(' . $domainFilter . ')/', $domainRequested);

                //echo "$domainRequested is ($domainIsFiltered) @@@ $requestedFromClient is ($clientIsFiltered)\r\n";

                if ($domainRequested != "pi.hole" && $domainRequested != $hostname && $domainIsFiltered == 0){
                      if (isset($gravity[$domainRequested]) && $clientIsFiltered == 0){
                            $count ++;
                        }
                }
            }
        }
        return $count;
    }

    function getAds(){
        $returnArray = array();
        $log = readInLog();
        $gravity = readInGrav();
        $hostname = readInHostname();
        $clientFilter = readInClientFilter();
        $domainFilter = readInDomainFilter();

        foreach($log as $logLine)
        {
            $exploded = explode(" ", $logLine);
            $logType = substr($exploded[count($exploded)-4],0,5) == "query";

            if ($logType == 1)
            {
                $domainRequested = $exploded[count($exploded) -3];
                $requestedFromClient = trim($exploded[count($exploded) -1], "\x00..\x1F");
                $clientIsFiltered = preg_match('/(' . $clientFilter . ')/', $requestedFromClient);
                $domainIsFiltered = preg_match('/(' . $domainFilter . ')/', $domainRequested);

                //echo "$domainRequested is ($domainIsFiltered) @@@ $requestedFromClient is ($clientIsFiltered)\r\n";

                if ($domainRequested != "pi.hole" && $domainRequested != $hostname && $domainIsFiltered == 0){
                    if (isset($gravity[$domainRequested]) && $clientIsFiltered == 0){
                        array_push($returnArray,$logLine);
                    }
                }
            }
        }
        return $returnArray;
    }

    function getQueryCount(){
        $count = 0;
        $log = readInLog();
        $clientFilter = readInClientFilter();
        $domainFilter = readInDomainFilter();

        foreach($log as $logLine)
        {
            $exploded = explode(" ", $logLine);
            $logType = substr($exploded[count($exploded)-4],0,5) == "query";

            if ($logType == 1)
            {
                $domainRequested = $exploded[count($exploded) -3];
                $requestedFromClient = trim($exploded[count($exploded) -1], "\x00..\x1F");
                $clientIsFiltered = preg_match('/(' . $clientFilter . ')/', $requestedFromClient);
                $domainIsFiltered = preg_match('/(' . $domainFilter . ')/', $domainRequested);

                //echo "$domainRequested is ($domainIsFiltered) @@@ $requestedFromClient is ($clientIsFiltered)\r\n";

                if ($domainIsFiltered == 0){
                    if ($clientIsFiltered == 0){
                        $count ++;
                    }
                }
            }
        }
        return $count;
    }

    function getQueries(){
        $returnArray = array();
        $log = readInLog();
        $clientFilter = readInClientFilter();
        $domainFilter = readInDomainFilter();

        foreach($log as $logLine)
        {
            $exploded = explode(" ", $logLine);
            $logType = substr($exploded[count($exploded)-4],0,5) == "query";

            if ($logType == 1)
            {
                $domainRequested = $exploded[count($exploded) -3];
                $requestedFromClient = trim($exploded[count($exploded) -1], "\x00..\x1F");
                $clientIsFiltered = preg_match('/(' . $clientFilter . ')/', $requestedFromClient);
                $domainIsFiltered = preg_match('/(' . $domainFilter . ')/', $domainRequested);

                //echo "$domainRequested is ($domainIsFiltered) @@@ $requestedFromClient is ($clientIsFiltered)\r\n";

                if ($domainIsFiltered == 0){
                    if ($clientIsFiltered == 0){
                        array_push($returnArray,$logLine);
                    }
                }
            }
        }
        return $returnArray;
    }

    function getOverTimeData() {
        $dns_queries = getQueries();
        $ads_blocked =getAds();

        $domains_over_time = overTime($dns_queries);
        $ads_over_time = overTime($ads_blocked);
        alignTimeArrays($ads_over_time, $domains_over_time);
        return Array(
            'domains_over_time' => $domains_over_time,
            'ads_over_time' => $ads_over_time,
        );
    }

    function getTopItems() {
        $dns_queries = getQueries();
        $ads_blocked = getAds();

        $topAds = topItems($ads_blocked);
        $topQueries = topItems($dns_queries, $topAds);

        return Array(
            'top_queries' => $topQueries,
            'top_ads' => $topAds,
        );
    }

    function getForwards(){
      $returnArray = array();
      $log = readInLog();
      //$clientFilter = readInClientFilter();
      $domainFilter = readInDomainFilter();

      foreach($log as $logLine)
      {
        $exploded = explode(" ", $logLine);
        $logType = substr($exploded[count($exploded)-4],0,9) == "forwarded";

        if ($logType == 1)
        {
          $domainRequested = $exploded[count($exploded) -3];
          //$requestedFromClient = trim($exploded[count($exploded) -1], "\x00..\x1F");
         // $clientIsFiltered = preg_match('/(' . $clientFilter . ')/', $requestedFromClient);
          $domainIsFiltered = preg_match('/(' . $domainFilter . ')/', $domainRequested);

          //echo "$domainRequested is ($domainIsFiltered) @@@ $requestedFromClient is ($clientIsFiltered)\r\n";

          if ($domainIsFiltered == 0){
           // if ($clientIsFiltered == 0){
              array_push($returnArray,$logLine);
          //  }
          }
        }
      }
      return $returnArray;
    }

    //Not sure this function is actually used
    function getRecentItems($qty) {

        $dns_queries = getQueries();
        return Array(
            'recent_queries' => getRecent($dns_queries, $qty)
        );
    }

    function getIpvType() {
        $dns_queries = getQueries();
        $queryTypes = array();

        foreach($dns_queries as $query) {
            $info = trim(explode(": ", $query)[1]);
            $queryType = explode(" ", $info)[0];
            if (isset($queryTypes[$queryType])) {
                $queryTypes[$queryType]++;
            }
            else {
                $queryTypes[$queryType] = 1;
            }
        }

        return $queryTypes;
    }

    function getForwardDestinations() {
        $forwards = getForwards();
        $destinations = array();
        foreach ($forwards as $forward) {
            $exploded = explode(" ", trim($forward));
            $dest = $exploded[count($exploded) - 1];
            if (isset($destinations[$dest])) {
                $destinations[$dest]++;
            }
            else {
                $destinations[$dest] = 0;
            }
        }

        return $destinations;

    }

    function getQuerySources() {
        $dns_queries = getQueries();
        $sources = array();
        foreach($dns_queries as $query) {
            $exploded = explode(" ", $query);
            $ip = hasHostName(trim($exploded[count($exploded)-1]));
            if (isset($sources[$ip])) {
                $sources[$ip]++;
            }
            else {
                $sources[$ip] = 1;
            }
        }
        arsort($sources);
        $sources = array_slice($sources, 0, 10);
        return Array(
            'top_sources' => $sources
        );
    }

    function getAllQueries() {
        $allQueries = array("data" => array());
        //$log = readInLog();
        $dns_queries = getQueries();
        $hostname = gethostname();
        $gravity=readInGrav();

        foreach ($dns_queries as $query) {
            $time = date_create(substr($query, 0, 16));
            $exploded = explode(" ", trim($query));

            $type = substr($exploded[count($exploded)-4], 6, -1);
            $domain = $exploded[count($exploded)-3];
            $client = $exploded[count($exploded)-1];

            if (isset($gravity[$domain]) && $domain != "pi.hole" && $domain != $hostname)
            {
              $status="Pi-holed";
            }
            else
            {
              $status="OK";
            }


              array_push($allQueries['data'], array(
                $time->format('Y-m-d\TH:i:s'),
                $type,
                $domain,
                hasHostName($client),
                $status,
              ));



        }
        return $allQueries;
    }

    /******** Private Members ********/
    function gravityCount() {
        //returns count of domains in blocklist.
        $gravity="/etc/pihole/gravity.list";
        $swallowed = 0;
        $NGC4889 = fopen($gravity, "r");
        while ($stars = fread($NGC4889, 1024000)) {
          $swallowed += substr_count($stars, "\n");
        }
        fclose($NGC4889);

        return $swallowed;

    }

    function readInClientFilter(){
        global $clientFilter;

        if ($clientFilter != "")
        {
            return $clientFilter;
        }
        else{
            $tmp = file_exists("/etc/pihole/webClientFilter.conf") ? file("/etc/pihole/webClientFilter.conf") : array("@@@@@");
            $tmp = array_map('trim', $tmp);
            foreach ($tmp as $key => $value) {$tmp[$key] = $value . "$";}
            return implode('|',$tmp);
        }
    }

    function readInDomainFilter(){
        global $domainFilter;

        if ($domainFilter != "")
        {
            return $domainFilter;
        }
        else{
            $tmp = file_exists("/etc/pihole/webDomainFilter.conf") ? file("/etc/pihole/webDomainFilter.conf") : array("@@@@@");
            $tmp = array_map('trim', $tmp);
            foreach ($tmp as $key => $value) {$tmp[$key] = $value . "$";}
            return implode('|',$tmp);
        }
    }

    function readInHostname(){
        global $hostname;
        return $hostname != "" ? $hostname :
          trim(file_get_contents("/etc/hostname"), "\x00..\x1F");
    }

    function readInLog() {
        global $log;
        return count($log) > 1 ? $log :
            file("/var/log/pihole.log");
    }

    function readInGrav() {
        global $gravity;

        if (count($gravity) > 1){

            return $gravity;
        }
        else{
            $fileName = '/etc/pihole/gravity.list';
            //Turn gravity.list into an array
            $lines = explode("\n", file_get_contents($fileName));

            //Create a new array and set domain name as index instead of value, with value as 1
            foreach(array_values($lines) as $v){
                $new_lines[trim(strstr($v, ' '))] = 1;
            }
            return $new_lines;
        }

    }

    function topItems($queries, $exclude = array(), $qty=10) {
        $splitQueries = array();
        foreach ($queries as $query) {
            $exploded = explode(" ", $query);
            $domain = trim($exploded[count($exploded) - 3]);
            if (!isset($exclude[$domain])) {
                if (isset($splitQueries[$domain])) {
                    $splitQueries[$domain]++;
                }
                else {
                    $splitQueries[$domain] = 1;
                }
            }
        }
        arsort($splitQueries);
        return array_slice($splitQueries, 0, $qty);
    }

    function overTime($entries) {
        $byTime = array();
        foreach ($entries as $entry) {
            $time = date_create(substr($entry, 0, 16));
            $hour = $time->format('G');

            if (isset($byTime[$hour])) {
                $byTime[$hour]++;
            }
            else {
                $byTime[$hour] = 1;
            }
        }
        return $byTime;
    }

    function alignTimeArrays(&$times1, &$times2) {
        $max = max(array(max(array_keys($times1)), max(array_keys($times2))));
        $min = min(array(min(array_keys($times1)), min(array_keys($times2))));

        for ($i = $min; $i <= $max; $i++) {
            if (!isset($times2[$i])) {
                $times2[$i] = 0;
            }
            if (!isset($times1[$i])) {
                $times1[$i] = 0;
            }
        }

        ksort($times1);
        ksort($times2);
    }

    function getRecent($queries, $qty){
        $recent = array();
        foreach (array_slice($queries, -$qty) as $query) {
            $queryArray = array();
            $exploded = explode(" ", $query);
            $time = date_create(substr($query, 0, 16));
            $queryArray['time'] = $time->format('h:i:s a');
            $queryArray['domain'] = trim($exploded[count($exploded) - 3]);
            $queryArray['ip'] = trim($exploded[count($exploded)-1]);
            array_push($recent, $queryArray);

        }
        return array_reverse($recent);
    }

    function hasHostName($var){
        global $hosts;
        foreach ($hosts as $host){
            $x = preg_split('/\s+/', $host);
            if ( $var == $x[0] ){
                $var = $x[1] . "($var)";
            }
        }
        return $var;
    }
?>
