<?php

/* 
**  ===========
**  PlaatProtect
**  ===========
**
**  Created by wplaat
**
**  For more information visit the following website.
**  Website : www.plaatsoft.nl 
**
**  Or send an email to the following address.
**  Email   : info@plaatsoft.nl
**
**  All copyrights reserved (c) 2008-2016 PlaatSoft
*/

/**
 * @file
 * @brief contain lumanance report
 */
 
/*
** ---------------------
** PAGES
** ---------------------
*/

function plaatprotect_luminance_page() {

	// input
	global $pid;
	global $date;
	
	list($year, $month, $day) = explode("-", $date);	
	$day = ltrim($day ,'0');
	$month = ltrim($month ,'0');
	$current_date = mktime(0, 0, 0, $month, $day, $year);  
	
	$i=0;	
	$offset = 24;
	$step = (24*60*60)/$offset;
		
	$data="";
	
	while ($i<$offset) {

		$first = true;
		
		$timestamp1 = date("Y-m-d H:i:s", $current_date+($step*$i));
		$timestamp2 = date("Y-m-d H:i:s", $current_date+($step*(++$i)));
				
		$sql1 = 'select zid, type from zwave where type="Sensor" order by zid';
		$result1 = plaatprotect_db_query($sql1);
		while ($node = plaatprotect_db_fetch_object($result1)) {
		
			$sql2  = 'select timestamp, zid, luminance from sensor where luminance>0 and ';
			$sql2 .= 'timestamp>="'.$timestamp1.'" and timestamp<="'.$timestamp2.'" and zid='.$node->zid.' order by timestamp';
		
			$result2 = plaatprotect_db_query($sql2);
			$row = plaatprotect_db_fetch_object($result2);
			
			$value = 0;
			if (isset($row->zid)) {
				$value = $row->luminance;
			}
			
			if (strlen($data)>0) {
				$data .= ',';
			}
				
			if ($first) {
				$data .= "['".$i."',";
				$first=false;
			}
			
			$data .= $value;			
		}				
		$data .= ']';
	}
	
	$json2 = "[".$data."]";
		
	$page = '
		   <script type="text/javascript" src="https://www.google.com/jsapi"></script>
			<script type="text/javascript">
			google.load("visualization", "1", {packages:["line"]});
			google.setOnLoadCallback(drawChart);

			function drawChart() {

				var data = new google.visualization.DataTable();
				data.addColumn("string", "Time");';
				
				$sql1 = 'select zid, type from zwave where type="Sensor" order by zid';
				$result1 = plaatprotect_db_query($sql1);
				while ($node = plaatprotect_db_fetch_object($result1)) {
				
					$page .= 'data.addColumn("number",  "'.plaatprotect_db_zwave($node->zid)->location.'"); ';
				};
				$page .= 'data.addRows('.$json2.');

				var options = {
					legend: { position: "top", textStyle: {fontSize: 10} },
					vAxis: {format: "decimal", title: ""},
					hAxis: {title: ""},
					backgroundColor: "transparent",
					chartArea: {
						backgroundColor: "transparent"
					}
				};

				var chart = new google.charts.Line(document.getElementById("chart_div"));
				chart.draw(data, google.charts.Line.convertOptions(options));
		}
		</script>';
	
	$page .= '<h1>Luminance Chart '.plaatprotect_dayofweek($date).' '.$day.'-'.$month.'-'.$year.'</h1>';

	$page .= '<div id="chart_div" style="width:950px; height:350px"></div>';
	
	$page .= '<div class="nav">';
	$page .= plaatprotect_link('pid='.$pid.'&date='.plaatprotect_prev_day($date), t('LINK_PREV'));
	$page .= plaatprotect_link('pid='.PAGE_HOME, t('LINK_HOME'));
	$page .= plaatprotect_link('pid='.$pid.'&date='.plaatprotect_next_day($date), t('LINK_NEXT'));
	$page .=  '</div>';

	return $page;
}

/*
** ---------------------
** HANDLER
** ---------------------
*/

function plaatprotect_luminance() {

  /* input */
  global $pid;  
	
	/* Page handler */
	switch ($pid) {

		case PAGE_LUMINANCE:
			return plaatprotect_luminance_page();
			break;
	}
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>
