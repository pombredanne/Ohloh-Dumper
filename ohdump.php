<?php
/**
 * Ohloh statistics dumper
 * 
 * @author Vladimir Sibirov
 * @version 1.0
 * @license http://www.gnu.org/licenses/gpl-2.0.txt The GNU General Public License
 * @copyright (c) 2011 Vladimir Sibirov
 */
 
require_once 'ohlohapi_class_inc.php';

// Configuration
// Put the projects you want dump statistics for into this array
// format: 'project_id' => 'Project Name'
$project_list = array(
	'cotonti' => 'Cotonti',
	'3624' => 'Kettle',
	'p4155' => 'Palo_Suite',
	'10374' => 'Talend',
	'4270' => 'CloverETL',
	'4526' => 'ScriptellaETL',
	'5784' => 'Apatar',
	'xaware' => 'XAware',
	'SQLServerMDDEStudio' => 'SQLServerMDDEStudio',
	'pentaho-bi-server-trunk' => 'PentahoBI',
	'flow-based-pgmg' => 'FBP',
	'pypes' => 'Pypes',
	'kamaelia' => 'Kamaelia',
	'ruffus' => 'Ruffus',
	'kilim' => 'Kilim',
	'fts' => 'FTS',
	'ejabberd' => 'ejabberd',
	'yaws' => 'Yaws',
	'couchdb' => 'CouchDB',
	'web_go' => 'web.go',
	'goprotobuf' => 'goprotobuf',
	'svgo' => 'svgo'
);
$apikey = 'Put your Ohloh API key here';

// Do not edit below
foreach ($project_list as $projectid => $projectname) {
	$ohloh = new ohlohapi($apikey, $projectid);
	
	$results = array();

	// Get size facts
	$sizefacts = $ohloh->sizeFactsNoAnalysis();
	foreach ($sizefacts->size_fact as $fact) {
		$month = substr($fact->month, 0, strpos($fact->month, 'T'));
		$results[$month] = array(
			'code' => $fact->code,
			'man_months' => $fact->man_months
		);
	}
	
	// Get activity facts
	$actfacts = $ohloh->getActivityFactsNoId();
	foreach ($actfacts->activity_fact as $fact) {
		$month = substr($fact->month, 0, strpos($fact->month, 'T'));
		$results[$month]['code_added'] = $fact->code_added;
		$results[$month]['commits'] = $fact->commits;
		$results[$month]['contributors'] = $fact->contributors;
	}
	
	// Save in a file
	save_results($projectname, $results);
	echo "Saved $projectname\n";
}

/**
 * Saves fetched results in a CSV file
 * @param string $name Project name
 * @param array $results Consolidated results
 */
function save_results($name, $results) {
	$fp = fopen("data/$name.csv", 'w');
	fputcsv($fp, array('Month', 'LOC', 'Commits', 'Contributors', 'Productivity'));
	foreach ($results as $month => $facts) {
		// Calculate productivity
		$prod = $facts['contributors'] > 0 ? round($facts['code_added'] / $facts['contributors']) : 0;
		// Write all
		fputcsv($fp, array($month, $facts['code'], $facts['commits'], $facts['contributors'], $prod));
	}
	fclose($fp);
}
?>
