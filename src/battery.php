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
 * @brief contain day energy in report
 */
 
/*
** ---------------------
** PAGES
** ---------------------
*/

function plaatprotect_battery_page() {

	// input
	global $pid;
	global $date;
	
	list($year, $month, $day) = explode("-", $date);	
	$day = ltrim($day ,'0');
	$month = ltrim($month ,'0');
	$current_date = mktime(0, 0, 0, $month, $day, $year);  
	
	$timestamp1 = date("Y-m-d 00:00:00", $current_date);
	$timestamp2 = date("Y-m-d 23:59:59", $current_date);
			
	$sql  = 'select timestamp, zid, battery from sensor where battery>0 and ';
	$sql .= 'timestamp>="'.$timestamp1.'" and timestamp<="'.$timestamp2.'" order by timestamp';
		
	$data = "";
	
	$result = plaatprotect_db_query($sql);
	while ($row = plaatprotect_db_fetch_object($result)) {
	
		if (strlen($data)>0) {
			$data .= ',';
		}
		if ($row->zid==3) {
			$data .= "['".date("H:i",strtotime($row->timestamp))."',".$row->battery.",0,0,0]";
		}
		if ($row->zid==4) {
			$data .= "['".date("H:i",strtotime($row->timestamp))."',0,".$row->battery.",0,0]";
		}
		if ($row->zid==6) {
			$data .= "['".date("H:i",strtotime($row->timestamp))."',0,0,".$row->battery.",0]";
		}
		if ($row->zid==7) {
			$data .= "['".date("H:i",strtotime($row->timestamp))."',0,0,0,".$row->battery."]";
		}
	}
	
	$json2 = "[".$data."]";
		
	$page = '
		   <script type="text/javascript" src="https://www.google.com/jsapi"></script>
			<script type="text/javascript">
			google.load("visualization", "1", {packages:["line"]});
			google.setOnLoadCallback(drawChart);

			function drawChart() {

				var data = new google.visualization.DataTable();
				data.addColumn("string", "Time");
				data.addColumn("number",  "'.plaatprotect_db_zwave(3)->location.'");
				data.addColumn("number",  "'.plaatprotect_db_zwave(4)->location.'");
				data.addColumn("number",  "'.plaatprotect_db_zwave(6)->location.'");
				data.addColumn("number",  "'.plaatprotect_db_zwave(7)->location.'");
				data.addRows('.$json2.');

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
	
	$page .= '<h1>Battery Chart '.plaatprotect_dayofweek($date).' '.$day.'-'.$month.'-'.$year.'</h1>';

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

function plaatprotect_battery() {

  /* input */
  global $pid;  
	
	/* Page handler */
	switch ($pid) {

		case PAGE_BATTERY:
			return plaatprotect_battery_page();
			break;
	}
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>
