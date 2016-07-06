<?php
function my_read_file($file) {
	$s = trim(file_get_contents($file));
	if ('' == $s) {
		return array();
	}

	$temp = explode("\n", $s);
	$lines = array();

	foreach ($temp as $t) {
		$t = trim($t);
		if ('' != $t) {
			$lines[] = $t;
		}
	}

	return $lines;
}
?>