<?php
ob_start();
$backlinks = trim(file_get_contents('backlinks.txt'));
$backlinks = explode("\n", $backlinks);
if (array() == $backlinks) {
	echo 'No backlink.';
	exit();
}

$backlink_check = trim(file_get_contents('backlink_check.txt'));
$backlink_check_regexp = trim(file_get_contents('backlink_check_regexp.txt'));

if ('' == $backlink_check && '' == $backlink_check_regexp) {
	echo 'Nothing to check.';
	exit();
}

$t = array();
foreach ($backlinks as $b) {
	$b = trim($b);
	if ('' != $b) {
		if (!preg_match('/^http(s)?\:\/\//i', $b)) {
			$b = 'http://'.$b;
		}
		if (!in_array($b, $t)) {
			$t[] = $b;
		}
	}
}
natcasesort($t);
$backlinks = $t;
unset($t);
?><html>
<head>
<title>Backlink Checker by Xrvel</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style type="text/css">
#id_found {
	background-color:#EFE;
}
#id_dead, #id_not_found {
	background-color:#FEE;
}
#id_pause {
	background-color:#FEE;
	color:#F00;
	cursor:pointer;
}
#id_start {
	background-color:#EFE;
	color:#0C0;
	cursor:pointer;
}
.no {
	color:#F00;
	font-weight:900;
}
.ok {
	color:#090;
	font-weight:900;
}
.output a {
	color:#00C !important;
}
.output a:visited {
	color:#090 !important;
}
textarea {
	width:99%;
}
</style>
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
</head>
<body>

<input type="hidden" id="autostart" value="0" />
<input type="hidden" id="backlink_count" value="<?php echo count($backlinks); ?>" />

<p>Checking <strong><?php echo number_format(count($backlinks)); ?></strong> links.</p>
<p>Processed = <span id="processed">0</span>. Unprocessed = <span id="unprocessed"><?php echo (count($backlinks)); ?></span></p>
<p>Found = <span id="ok" class="ok">0</span>. Not found = <span id="no" class="no">0</span>. Dead = <span id="dead" class="no">0</span>. Running threads = <span id="threads">0</span></p>
<p style="text-align:right">
<input type="button" id="id_start" value="Start" onclick="my_go('start')" />
<input type="button" id="id_pause" value="Pause" onclick="my_go('pause')" />
</p>
<div id="tabs" class="tabs">
	<ul>
		<li><a href="#tabs-1">Links</a></li>
		<li><a href="#tabs-2">Status</a></li>
		<li><a href="#tabs-3">About</a></li>
	</ul>
	<div id="tabs-1">
		<table border="1" cellpadding="5" cellspacing="0">
		<tr>
			<th>&nbsp;</th>
			<th>Link</th>
			<th>Status</th>
		</tr>
		<?php $i = 0; foreach ($backlinks as $b) : ?>
		<tr valign="top">
			<td style="text-align:right"><?php echo $i+1; ?></td>
			<td><input type="text" size="100" value="<?php echo htmlentities($b); ?>" readonly="1" onclick="this.select()" /> <a href="<?php echo htmlentities($b); ?>" target="_blank">Open</a></td>
			<td id="status_<?php echo $i; ?>"></td>
		</tr>
		<?php $i++; endforeach; ?>
		</table>
	</div>
	<div id="tabs-2">
		<label for="id_auto_scroll" style="cursor:pointer"><input type="checkbox" id="id_auto_scroll" checked="1" /> Auto scroll textarea</label>
		<div class="tabs" style="margin-top:1em">
			<ul>
				<li><a href="#tabs-2-1" id="id_tabs-2-1_label">Found</a></li>
				<li><a href="#tabs-2-2" id="id_tabs-2-2_label">Not Found</a></li>
				<li><a href="#tabs-2-3" id="id_tabs-2-3_label">Dead</a></li>
			</ul>
			<div id="tabs-2-1">
				<div class="tabs">
					<ul>
						<li><a href="#tabs-2-1-d">Detail</a></li>
						<li><a href="#tabs-2-1-t">Textarea</a></li>
					</ul>
					<div id="tabs-2-1-d">
						<div class="output" id="id_output_found">
						</div>
					</div>
					<div id="tabs-2-1-t">
						<textarea id="id_found" readonly="readonly" rows="10" wrap="off" onclick="this.select()"></textarea>
					</div>
				</div>
			</div>
			<div id="tabs-2-2">
				<div class="tabs">
					<ul>
						<li><a href="#tabs-2-2-d">Detail</a></li>
						<li><a href="#tabs-2-2-t">Textarea</a></li>
					</ul>
					<div id="tabs-2-2-d">
						<div class="output" id="id_output_not_found">
						</div>
					</div>
					<div id="tabs-2-2-t">
						<textarea id="id_not_found" readonly="readonly" rows="10" wrap="off" onclick="this.select()"></textarea>
					</div>
				</div>
			</div>
			<div id="tabs-2-3">
				<div class="tabs">
					<ul>
						<li><a href="#tabs-2-3-d">Detail</a></li>
						<li><a href="#tabs-2-3-t">Textarea</a></li>
					</ul>
					<div id="tabs-2-3-d">
						<div class="output" id="id_output_dead">
						</div>
					</div>
					<div id="tabs-2-3-t">
						<textarea id="id_dead" readonly="readonly" rows="10" wrap="off" onclick="this.select()"></textarea>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="tabs-3">
		<p>Made by <a href="http://www.xrvel.com">Xrvel</a></p>
	</div>
</div>
<img alt="Loading - preload" width="1" height="1" src="./loading.gif" />
<script type="text/javascript">
var max_backlinks = <?php echo count($backlinks); ?>;
var backlinks = ["<?php echo implode('", "', $backlinks); ?>"];
</script>
<script type="text/javascript" src="./my.js"></script>
</body>
</html>