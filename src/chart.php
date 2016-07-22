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

function plaatprotect_chart_page() {

	// input
	global $pid;
	global $date;
	
	list($year, $month, $day) = explode("-", $date);	
	$day = ltrim($day ,'0');
	$month = ltrim($month ,'0');
	$current_date = mktime(0, 0, 0, $month, $day, $year);  
	
   $i=0;	
	$offset = 12*24;
	$step = (24*60*60)/$offset;
		
	$data="";
	
	while ($i++<$offset) {

		$timestamp1 = date("Y-m-d H:i:s", $current_date+($step*$i));
		$timestamp2 = date("Y-m-d H:i:s", $current_date+($step*($i+1)));
			
		$sql  = 'select eid from event where category='.CATEGORY_ZWAVE.' and action like "%alarm%" and ';
		$sql .= 'timestamp>="'.$timestamp1.'" and timestamp<="'.$timestamp2.'"';
		
		$value = 0;
		$result = plaatprotect_db_query($sql);
		$row = plaatprotect_db_fetch_object($result);
		if (isset($row->eid)) {
			$value = 1;
		}
		if (strlen($data)>0) {
			$data .= ',';
		}
		$data .= "['".date("H:i", $current_date+($step*$i))."',";
		$data .= round($value,2).']';
	}
	
	$json = "[".$data."]";
		
	$page = '
		   <script type="text/javascript" src="https://www.google.com/jsapi"></script>
			<script type="text/javascript">
			google.load("visualization", "1", {packages:["line"]});
			google.setOnLoadCallback(drawChart);

			function drawChart() {

				var data = new google.visualization.DataTable();
				data.addColumn("string", "Time");
				data.addColumn("number",  "Movement");
				data.addRows('.$json.');

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
	
	$page .= '<h1>Movement Chart '.plaatprotect_dayofweek($date).' '.$day.'-'.$month.'-'.$year.'</h1>';

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

function plaatprotect_chart() {

  /* input */
  global $pid;  
	
	/* Page handler */
	switch ($pid) {

		case PAGE_CHART:
			return plaatprotect_chart_page();
			break;
	}
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>
