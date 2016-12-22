<?php
	require "header.php";
	require "php/savesettings.php";
	// Reread ini file as things might have been changed
	$setupVars = parse_ini_file("/etc/pihole/setupVars.conf");
?>

<?php if(isset($debug)){ ?>
<div id="alDebug" class="alert alert-warning alert-dismissible fade in" role="alert">
    <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4><i class="icon fa fa-warning"></i> Debug</h4>
    <?php print_r($_POST); ?>
</div>
<?php } ?>

<?php if(strlen($success) > 0){ ?>
<div id="alInfo" class="alert alert-info alert-dismissible fade in" role="alert">
    <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4><i class="icon fa fa-info"></i> Info</h4>
    <?php echo $success; ?>
</div>
<?php } ?>

<?php if(strlen($error) > 0){ ?>
<div id="alError" class="alert alert-danger alert-dismissible fade in" role="alert">
    <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4><i class="icon fa fa-ban"></i> Error</h4>
    <?php echo $error; ?>
</div>
<?php } ?>

<div class="row">
	<div class="col-md-6">
<?php
	// Networking
	if(isset($setupVars["PIHOLE_INTERFACE"])){
		$piHoleInterface = $setupVars["PIHOLE_INTERFACE"];
	} else {
		$piHoleInterface = "unknown";
	}
	if(isset($setupVars["IPV4_ADDRESS"])){
		$piHoleIPv4 = $setupVars["IPV4_ADDRESS"];
	} else {
		$piHoleIPv4 = "unknown";
	}
	if(isset($setupVars["IPV6_ADDRESS"])){
		$piHoleIPv6 = $setupVars["IPV6_ADDRESS"];
	} else {
		$piHoleIPv6 = "unknown";
	}
	$hostname = trim(file_get_contents("/etc/hostname"), "\x00..\x1F");
?>
		<div class="box box-warning">
			<div class="box-header with-border">
				<h3 class="box-title">Networking</h3>
			</div>
			<div class="box-body">
				<div class="form-group">
					<label>Pi-Hole Ethernet Interface</label>
					<div class="input-group">
						<div class="input-group-addon"><i class="fa fa-plug"></i></div>
						<input type="text" class="form-control" disabled value="<?php echo $piHoleInterface; ?>">
					</div>
				</div>
				<div class="form-group">
					<label>Pi-Hole IPv4 address</label>
					<div class="input-group">
						<div class="input-group-addon"><i class="fa fa-plug"></i></div>
						<input type="text" class="form-control" disabled value="<?php echo $piHoleIPv4; ?>">
					</div>
				</div>
				<div class="form-group">
					<label>Pi-Hole IPv6 address</label>
					<div class="input-group">
						<div class="input-group-addon"><i class="fa fa-plug"></i></div>
						<input type="text" class="form-control" disabled value="<?php echo $piHoleIPv6; ?>">
					</div>
				</div>
				<div class="form-group">
					<label>Pi-Hole hostname</label>
					<div class="input-group">
						<div class="input-group-addon"><i class="fa fa-laptop"></i></div>
						<input type="text" class="form-control" disabled value="<?php echo $hostname; ?>">
					</div>
				</div>
			</div>
		</div>
<?php
	// Pi-Hole DHCP server
	if(isset($setupVars["DHCP_ACTIVE"]))
	{
		if($setupVars["DHCP_ACTIVE"] == 1)
		{
			$DHCP = true;
		}
		else
		{
			$DHCP = false;
		}
		// Read setings from config file
		$DHCPstart = $setupVars["DHCP_START"];
		$DHCPend = $setupVars["DHCP_END"];
		$DHCProuter = $setupVars["DHCP_ROUTER"];
	}
	else
	{
		$DHCP = false;
		// Try to guess initial settings
		if($piHoleIPv4 !== "unknown") {
			$DHCPdomain = explode(".",$piHoleIPv4);
			$DHCPstart  = $DHCPdomain[0].".".$DHCPdomain[1].".".$DHCPdomain[2].".201";
			$DHCPend    = $DHCPdomain[0].".".$DHCPdomain[1].".".$DHCPdomain[2].".251";
			$DHCProuter = $DHCPdomain[0].".".$DHCPdomain[1].".".$DHCPdomain[2].".1";
		}
		else {
			$DHCPstart  = "";
			$DHCPend    = "";
			$DHCProuter = "";
		}
	}
	if(isset($setupVars["PIHOLE_DOMAIN"])){
		$piHoleDomain = $setupVars["PIHOLE_DOMAIN"];
	} else {
		$piHoleDomain = "local";
	}
?>
		<div class="box box-warning">
			<div class="box-header with-border">
				<h3 class="box-title">Pi-Hole DHCP Server</h3>
			</div>
			<div class="box-body">
				<form role="form" method="post">
				<div class="form-group">
					<div class="checkbox"><label><input type="checkbox" name="active" <?php if($DHCP){ ?>checked<?php } ?> id="DHCPchk"> DHCP server enabled</label></div>
				</div>
				<label>Range of IP addresses to hand out</label>
				<div class="form-group">
					<div class="col-md-6">
						<div class="input-group">
							<div class="input-group-addon">From</div>
								<input type="text" class="form-control DHCPgroup" name="from" value="<?php echo $DHCPstart; ?>" data-inputmask="'alias': 'ip'" data-mask <?php if(!$DHCP){ ?>disabled<?php } ?>>
					</div>
					</div>
					<div class="col-md-6">
						<div class="input-group">
							<div class="input-group-addon">To</div>
								<input type="text" class="form-control DHCPgroup" name="to" value="<?php echo $DHCPend; ?>" data-inputmask="'alias': 'ip'" data-mask <?php if(!$DHCP){ ?>disabled<?php } ?>>
						</div>
					</div>
					<label>Router (gateway) IP address</label>
					<div class="col-md-12">
						<div class="input-group">
							<div class="input-group-addon">Router</div>
								<input type="text" class="form-control DHCPgroup" name="router" value="<?php echo $DHCProuter; ?>" data-inputmask="'alias': 'ip'" data-mask <?php if(!$DHCP){ ?>disabled<?php } ?>>
						</div>
					</div><br/>
					<label>Pi-Hole domain name</label>
					<div class="col-md-12">
						<div class="input-group">
							<div class="input-group-addon">Domain</div>
								<input type="text" class="form-control DHCPgroup" name="domain" value="<?php echo $piHoleDomain; ?>" <?php if(!$DHCP){ ?>disabled<?php } ?>>
						</div>
					</div>
				<br/>
<?php if($DHCP) {

	// Read leases file
	$leasesfile = true;
	$dhcpleases = fopen('/etc/pihole/dhcp.leases', 'r') or $leasesfile = false;
	$dhcp_leases  = [];

	while(!feof($dhcpleases) && $leasesfile)
	{
		$line = explode(" ",trim(fgets($dhcpleases)));
		if(count($line) == 5)
		{
			array_push($dhcp_leases,["MAC"=>$line[1], "IP"=>$line[2], "NAME"=>$line[3]]);
		}
	}
	?>
					<label>DHCP leases</label>
					<div class="col-md-12">

						<table id="DHCPLeasesTable" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
							<thead>
								<tr>
									<th>IP address</th>
									<th>Hostname</th>
									<th>MAC address</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($dhcp_leases as $lease) { ?><tr><td><?php echo $lease["IP"]; ?></td><td><?php echo $lease["NAME"]; ?></td><td><?php echo $lease["MAC"]; ?></td></tr><?php } ?>
							</tbody>
						</table>
					</div>
<?php } ?>
				</div>
			</div>
			<div class="box-footer">
				<input type="hidden" name="field" value="DHCP">
				<button type="submit" class="btn btn-primary pull-right">Save</button>
			</div>
			</form>
		</div>

<?php
	// DNS settings
	if(isset($setupVars["PIHOLE_DNS_1"])){
		if(isset($primaryDNSservers[$setupVars["PIHOLE_DNS_1"]]))
		{
			$piHoleDNS1 = $primaryDNSservers[$setupVars["PIHOLE_DNS_1"]];
		}
		else
		{
			$piHoleDNS1 = "Custom";
		}
	} else {
		$piHoleDNS1 = "unknown";
	}

	if(isset($setupVars["PIHOLE_DNS_2"])){
		if(isset($secondaryDNSservers[$setupVars["PIHOLE_DNS_2"]]))
		{
			$piHoleDNS2 = $secondaryDNSservers[$setupVars["PIHOLE_DNS_2"]];
		}
		else
		{
			$piHoleDNS2 = "Custom";
		}
	} else {
		$piHoleDNS2 = "unknown";
	}

	if(isset($setupVars["DNS_FQDN_REQUIRED"])){
		if($setupVars["DNS_FQDN_REQUIRED"])
		{
			$DNSrequiresFQDN = true;
		}
		else
		{
			$DNSrequiresFQDN = false;
		}
	} else {
		$DNSrequiresFQDN = true;
	}

	if(isset($setupVars["DNS_BOGUS_PRIV"])){
		if($setupVars["DNS_BOGUS_PRIV"])
		{
			$DNSbogusPriv = true;
		}
		else
		{
			$DNSbogusPriv = false;
		}
	} else {
		$DNSbogusPriv = true;
	}
?>
		<div class="box box-warning">
			<div class="box-header with-border">
				<h3 class="box-title">Upstream DNS Servers</h3>
			</div>
			<div class="box-body">
				<form role="form" method="post">
				<div class="col-lg-6">
					<label>Primary DNS Server</label>
					<div class="form-group">
						<?php foreach ($primaryDNSservers as $key => $value) { ?> <div class="radio"><label><input type="radio" name="primaryDNS" value="<?php echo $value;?>" <?php if($piHoleDNS1 === $value){ ?>checked<?php } ?> ><?php echo $value;?> (<?php echo $key;?>)</label></div> <?php } ?>
						<label>Custom</label>
						<div class="input-group">
							<div class="input-group-addon"><input type="radio" name="primaryDNS" value="Custom"
							<?php if($piHoleDNS1 === "Custom"){ ?>checked<?php } ?>></div>
							<input type="text" name="DNS1IP" class="form-control" data-inputmask="'alias': 'ip'" data-mask
							<?php if($piHoleDNS1 === "Custom"){ ?>value="<?php echo $setupVars["PIHOLE_DNS_1"]; ?>"<?php } ?>>
						</div>
					</div>
				</div>
				<div class="col-lg-6">
					<label>Secondary DNS Server</label>
					<div class="form-group">
						<?php foreach ($secondaryDNSservers as $key => $value) { ?> <div class="radio"><label><input type="radio" name="secondaryDNS" value="<?php echo $value;?>" <?php if($piHoleDNS2 === $value){ ?>checked<?php } ?> ><?php echo $value;?> (<?php echo $key;?>)</label></div> <?php } ?>
						<label>Custom</label>
						<div class="input-group">
							<div class="input-group-addon"><input type="radio" name="secondaryDNS" value="Custom"
							<?php if($piHoleDNS2 === "Custom"){ ?>checked<?php } ?>></div>
							<input type="text" name="DNS2IP" class="form-control" data-inputmask="'alias': 'ip'" data-mask
							<?php if($piHoleDNS2 === "Custom"){ ?>value="<?php echo $setupVars["PIHOLE_DNS_2"]; ?>"<?php } ?>>
						</div>
					</div>
				</div>
				<div class="col-lg-12">
				<div class="box box-warning collapsed-box">
					<div class="box-header with-border">
						<h3 class="box-title">Advanced DNS settings</h3>
						<div class="box-tools pull-right"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button></div>
					</div>
					<div class="box-body">
						<div class="col-lg-12">
							<div class="form-group">
								<div class="checkbox"><label><input type="checkbox" name="DNSrequiresFQDN" <?php if($DNSrequiresFQDN){ ?>checked<?php } ?>> never forward non-FQDNs</label></div>
							</div>
							<div class="form-group">
								<div class="checkbox"><label><input type="checkbox" name="DNSbogusPriv" <?php if($DNSbogusPriv){ ?>checked<?php } ?>> never forward reverse lookups for private IP ranges</label></div>
							</div>
							<p>Note that enabling these two options may increase your privacy slightly, but may also prevent you from being able to access local hostnames if the Pi-Hole is not used as DHCP server</p>
						</div>
					</div>
				</div>
				</div>
			</div>
			<div class="box-footer">
				<input type="hidden" name="field" value="DNS">
				<button type="submit" class="btn btn-primary pull-right">Save</button>
			</div>
			</form>
		</div>
	</div>
	<div class="col-md-6">
<?php
	// Query logging
	if(isset($setupVars["QUERY_LOGGING"]))
	{
		if($setupVars["QUERY_LOGGING"] == 1)
		{
			$piHoleLogging = true;
		}
		else
		{
			$piHoleLogging = false;
		}
	} else {
		$piHoleLogging = true;
	}
?>
		<div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title">Query Logging<?php if($piHoleLogging) { ?> (size of log <?php echo formatSizeUnits(filesize("/var/log/pihole.log")); ?>)<?php } ?></h3>
			</div>
			<div class="box-body">
				<p>Current status:
				<?php if($piHoleLogging) { ?>
					Enabled (recommended)
				<?php }else{ ?>
					Disabled
				<?php } ?></p>
				<?php if($piHoleLogging) { ?>
					<p>Note that disabling will render graphs on the web user interface useless</p>
				<?php } ?>
			</div>
			<div class="box-footer">
				<form role="form" method="post">
				<input type="hidden" name="field" value="Logging">
				<?php if($piHoleLogging) { ?>
					<input type="hidden" name="action" value="Disable">
					<button type="submit" class="btn btn-primary pull-right">Disable query logging</button>
				<?php } else { ?>
					<input type="hidden" name="action" value="Enable">
					<button type="submit" class="btn btn-primary pull-right">Enable query logging</button>
				<?php } ?>
				</form>
			</div>
		</div>
<?php
	// Excluded domains in API Query Log call
	if(isset($setupVars["API_EXCLUDE_DOMAINS"]))
	{
		$excludedDomains = explode(",", $setupVars["API_EXCLUDE_DOMAINS"]);
	} else {
		$excludedDomains = [];
	}

	// Exluded clients in API Query Log call
	if(isset($setupVars["API_EXCLUDE_CLIENTS"]))
	{
		$excludedClients = explode(",", $setupVars["API_EXCLUDE_CLIENTS"]);
	} else {
		$excludedClients = [];
	}

	// Exluded clients
	if(isset($setupVars["API_QUERY_LOG_SHOW"]))
	{
		$queryLog = $setupVars["API_QUERY_LOG_SHOW"];
	} else {
		$queryLog = "all";
	}
?>
		<div class="box box-success">
			<div class="box-header with-border">
				<h3 class="box-title">API</h3>
			</div>
			<form role="form" method="post">
			<div class="box-body">
				<h4>Top Lists</h4>
				<p>Exclude the following domains from being shown in</p>
				<div class="col-lg-6">
					<div class="form-group">
					<label>Top Domains / Top Advertisers</label>
					<textarea name="domains" class="form-control" rows="4" placeholder="Enter one domain per line"><?php foreach ($excludedDomains as $domain) { echo $domain."\n"; } ?></textarea>
					</div>
				</div>
				<div class="col-lg-6">
					<div class="form-group">
					<label>Top Clients</label>
					<textarea name="clients" class="form-control" rows="4" placeholder="Enter one IP address per line"><?php foreach ($excludedClients as $client) { echo $client."\n"; } ?></textarea>
					</div>
				</div>
				<h4>Query Log</h4>
				<div class="form-group">
					<div class="checkbox"><label><input type="checkbox" name="querylog-permitted" <?php if($queryLog === "permittedonly" || $queryLog === "all"){ ?>checked<?php } ?>> Show permitted queries</label></div>
					<div class="checkbox"><label><input type="checkbox" name="querylog-blocked" <?php if($queryLog === "blockedonly" || $queryLog === "all"){ ?>checked<?php } ?>> Show blocked queries</label></div>
				</div>
			</div>
			<div class="box-footer">
				<input type="hidden" name="field" value="API">
				<button type="submit" class="btn btn-primary pull-right">Save</button>
			</div>
			</form>
		</div>
<?php
	// CPU temperature unit
	if(isset($setupVars["TEMPERATUREUNIT"]))
	{
		$temperatureunit = $setupVars["TEMPERATUREUNIT"];
	}
	else
	{
		$temperatureunit = "C";
	}
	// Use $boxedlayout value determined in header.php
?>
		<div class="box box-success">
			<div class="box-header with-border">
				<h3 class="box-title">Web User Interface</h3>
			</div>
			<form role="form" method="post">
			<div class="box-body">
<?php /*
				<h4>Query Log Page</h4>
				<div class="col-lg-6">
					<div class="form-group">
						<label>Default value for <em>Show XX entries</em></label>
						<select class="form-control" disabled>
							<option>10</option>
							<option>25</option>
							<option>50</option>
							<option>100</option>
							<option>All</option>
						</select>
					</div>
				</div>
*/ ?>
				<h4>Interface appearance</h4>
				<div class="form-group">
					<div class="checkbox"><label><input type="checkbox" name="boxedlayout" value="yes" <?php if($boxedlayout){ ?>checked<?php } ?> >Use boxed layout (helpful when working on large screens)</label></div>
				</div>
				<h4>CPU Temperature Unit</h4>
				<div class="form-group">
					<div class="radio"><label><input type="radio" name="tempunit" value="C" <?php if($temperatureunit === "C"){ ?>checked<?php } ?> >Celsius</label></div>
					<div class="radio"><label><input type="radio" name="tempunit" value="K" <?php if($temperatureunit === "K"){ ?>checked<?php } ?> >Kelvin</label></div>
					<div class="radio"><label><input type="radio" name="tempunit" value="F" <?php if($temperatureunit === "F"){ ?>checked<?php } ?> >Fahrenheit</label></div>
				</div>
			</div>
			<div class="box-footer">
				<input type="hidden" name="field" value="webUI">
				<button type="submit" class="btn btn-primary pull-right">Save</button>
			</div>
			</form>
		</div>
<?php /*
		<div class="box box-info">
			<div class="box-header with-border">
				<h3 class="box-title">Blocking Page</h3>
			</div>
			<div class="box-body">
				<p>Show page with details if a site is blocked</p>
				<div class="form-group">
					<div class="radio"><label><input type="radio" name="blockingPage" value="Yes" <?php if($pishowBlockPage){ ?>checked<?php } ?> disabled>Yes</label></div>
					<div class="radio"><label><input type="radio" name="blockingPage" value="No" <?php if(!$pishowBlockPage){ ?>checked<?php } ?> disabled>No</label></div>
					<p>If Yes: Hide content for in page ads?</p>
					<div class="checkbox"><label><input type="checkbox" <?php if($pishowBlockPageInpage) { ?>checked<?php } ?> disabled>Enabled (recommended)</label></div>
				</div>
			</div>
		</div>
*/ ?>
		<div class="box box-danger">
			<div class="box-header with-border">
				<h3 class="box-title">System Administration</h3>
			</div>
			<div class="box-body">
				<button type="button" class="btn btn-default confirm-reboot">Restart system</button>
				<button type="button" class="btn btn-default confirm-restartdns">Restart DNS server</button>
				<button type="button" class="btn btn-default confirm-flushlogs">Flush logs</button>

				<form role="form" method="post" id="rebootform">
					<input type="hidden" name="field" value="reboot">
				</form>
				<form role="form" method="post" id="restartdnsform">
					<input type="hidden" name="field" value="restartdns">
				</form>
				<form role="form" method="post" id="flushlogsform">
					<input type="hidden" name="field" value="flushlogs">
				</form>
			</div>
		</div>
	</div>
</div>

<?php
    require "footer.php";
?>
<script src="js/other/jquery.inputmask.js"></script>
<script src="js/other/jquery.inputmask.extensions.js"></script>
<script src="js/other/jquery.confirm.min.js"></script>
<script src="js/pihole/settings.js"></script>

