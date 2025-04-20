<?php
function proxy_url($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);
	if (curl_exec($ch) === false) {
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		echo "Downloading " . htmlspecialchars($url) . " failed: "
			. curl_error($ch) . "\n";
		exit(0);
	}
	curl_close($ch);

	echo $data;
}

$user = Auth::getUser();
if (!$user) exit;
$name = $user->customer->shortname;

if (isset($_GET['name'])) {
	if (!$user->isSuperUser()) {
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		echo "The name parameter is not allowed.\n";
		exit(0);
	}
	$name = $_GET['name'];
	if (!preg_match('/^[a-z0-9-]+$/', $name)) {
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		echo "The name parameter is not valid.\n";
		exit(0);
	}
}

if (isset($_GET['end'])) {
	$end = $_GET['end'];
	if (!preg_match('/^[0-9\.]+$/', $end)) {
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		echo "The end parameter is not valid.\n";
		exit(0);
	}
} else {
	$end = time();
}

if (isset($_GET['start'])) {
	$start = $_GET['start'];
	if (!preg_match('/^[0-9\.]+$/', $start)) {
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		echo "The start parameter is not valid.\n";
		exit(0);
	}
} else {
	$start = $end - 12 * 60 * 60;
}

if (isset($_GET['direction'])) {
	$direction = $_GET['direction'];
	if (!preg_match('/^(?:in|out)$/', $direction)) {
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		echo "The direction parameter is not valid.\n";
		exit(0);
	}
} else {
	$direction = 'out';
}
$other_direction = $direction == 'in' ? 'out' : 'in';

if (isset($_GET['step'])) {
	$step = $_GET['step'];
	if (!preg_match('/^[0-9]+$/', $step)) {
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		echo "The step parameter is not valid.\n";
		exit(0);
	}
} else {
	$step = '600';
}

$prom_params = array(
	'query'	=> "sum by($other_direction) (rate(sflow_router_bytes{"
			. "$direction=\"$name\""
			. "}[5m])) * 8 >= 1000",
	'start'	=> $start,
	'end'	=> $end,
	'step'	=> $step,
);

$prom_api = "http://server1.minap.it:9090/api";
$prom_url = "$prom_api/v1/query_range?" . http_build_query($prom_params);

if (isset($_GET['debug']) && $user->isSuperUser()) {
	echo "name: " . htmlspecialchars($name) . "<br>\n";
	echo "query: " . htmlspecialchars($prom_params['query']) . "<br>\n";
	echo "Prometheus URL: " . htmlspecialchars($prom_url) . "<br>\n";
	exit(0);
}

proxy_url($prom_url);

