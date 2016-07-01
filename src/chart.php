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

   $i=0;	
	$offset = 360;
	$step = (24*60*60)/$offset;
	
	$current_date = $lastday = mktime(0, 0, 0, 7, 1, 2016);
	
	$data="";
	
	while ($i++<$offset) {

		$timestamp1 = date("Y-m-d H:i:s", $current_date+($step*$i));
		$timestamp2 = date("Y-m-d H:i:s", $current_date+($step*($i+1)));
			
		$sql  = 'select timestamp, value from event where value>0 and ';
		$sql .= 'timestamp>="'.$timestamp1.'" and timestamp<="'.$timestamp2.'"';
	
		$result = plaatprotect_db_query($sql);
		$row = plaatprotect_db_fetch_object($result);
		
		$value =0;
		if (isset($row->value)) {	
			$value=$row->value;
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
	
	$page .= '<h1>Movement Detection Chart</h1>';

	$page .= '<div id="chart_div" style="width:950px; height:350px"></div>';
	
	$page .= '<div class="nav">';
	$page .= plaatprotect_link('pid='.PAGE_HOME, t('LINK_HOME'));
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
