<?php
ignore_user_abort(true);
set_time_limit(70);
header('Content-type: application/json', true);
$response = array(
	'status' => 'no',
	'message' => '',
	'url' => '',
	'index' => 0
);

$index = 0;
$url = '';

if (isset($_POST['index'])) {
	$index = intval($_POST['index']);
}
if (isset($_POST['url'])) {
	$url = trim(urldecode($_POST['url']));
}

if ('' == $url) {
	$response['status'] = 'dead';
	$response['message'] = 'Empty url.';
	echo json_encode($response);
	exit();
}

/* $url = 'http://localhost/'; */

$response['index'] = $index;
$response['url'] = $url;

$check = trim(file_get_contents('backlink_check.txt'));
$check = explode("\n", $check);

$check_regexp = trim(file_get_contents('backlink_check_regexp.txt'));
$check_regexp = explode("\n", $check_regexp);

if (array() == $check && array() == $check_regexp) {
	$response['status'] = 'dead';
	$response['message'] = 'No patterns.';
	echo json_encode($response);
	exit();
}

$t = array();
foreach ($check as $c) {
	$c = str_replace(array("\n", "\r"), '', $c);
	if ('' != trim($c)) {
		$t[] = strtolower($c);
	}
}
$check = $t;
unset($t);

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_REFERER, $url);
$res = curl_exec($ch);
$i = curl_getinfo($ch);
curl_close($ch);

if (200 != $i['http_code']) {
	$response['status'] = 'dead';
	$response['message'] = 'Possibly dead. Header code : '.$i['http_code'];
	echo json_encode($response);
	exit();
}

if ('' == $res) {
	$response['status'] = 'dead';
	$response['message'] = 'Possibly dead / page size is too big. Empty response.';
	echo json_encode($response);
	exit();
}

$res = strtolower($res);
$found = array();
$max_check = count($check);
for ($i=0;$i<$max_check;$i++) {
	if (false !== strpos($res, $check[$i])) {
		$found[] = htmlentities(trim($check[$i]));
	}
}

$i=1;
if (array() != $check_regexp) {
	foreach ($check_regexp as $cr) {
		$cr = trim($cr);
		if ('' != $cr) {
			if (preg_match($cr, $res)) {
				$found[] = 'Regexp ['.$i.']';
			}
		}
		$i++;
	}
}

if (array() == $found) {
	$response['status'] = 'no';
	$response['message'] = 'String not found.';
} else {
	$response['status'] = 'ok';
	$response['message'] = 'Found : '.trim(implode(', ', $found));
}
echo json_encode($response);
?>