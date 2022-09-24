<?php

$wp_root = dirname(__FILE__) .'/../../../../';
if(file_exists($wp_root . 'wp-load.php')) {
	require_once($wp_root . "wp-load.php");
} else if(file_exists($wp_root . 'wp-config.php')) {
	require_once($wp_root . "wp-config.php");
} else {
	exit;
}

require_once dirname(__FILE__) . '/CsvCsvImportExportHelpers.php';

$type = isset($_POST['csv-sample-type']) ? $_POST['csv-sample-type'] : '';
// switch ($type) {
// 	case 'items':
// 		$defaultPostFields = CsvCsvImportExportHelpers::getDefaultPostFields();
// 		$itemConfig = CsvCsvImportExportHelpers::getRawConfig('csv-item');
// 		$itemFields = CsvCsvImportExportHelpers::getPostMetaFields($itemConfig);
// 		$fields = array_merge($defaultPostFields, $itemFields);
// 		break;

// 	default:
// 		break;
// }

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=".$type.".csv");
header("Pragma: no-cache");
header("Expires: 0");

if (in_array($type, array_keys(CsvCsvImportExportHelpers::getSupportedCpts()) )) {
	cptSample($type);
} elseif (CsvCsvImportExportHelpers::isSupportedTax($type)) {
	taxonomySample($type);
}


function cptSample($type)
{
	$defaultPostFields = CsvCsvImportExportHelpers::getDefaultPostFields();
	$metaConfig = CsvCsvImportExportHelpers::getRawConfig($type);
	$metaFields = CsvCsvImportExportHelpers::getPostMetaFields($metaConfig);
	$parents = $metaFields['parents'];
	$allNewMetaFields = $metaFields['newFields'];
	$taxonomyFields = CsvCsvImportExportHelpers::getPostTaxonomyFields($type);
	$fields = array_merge($defaultPostFields, $taxonomyFields, $allNewMetaFields);
	$metaKey = CsvCsvImportExportHelpers::getMetaKey($type);



	$data = array();

	// fix for microsoft office
	$data[0] = array('sep=;');
	$data[1] = array_keys($fields);


	outputCSV($data);
}

function taxonomySample($type)
{
	$fields = CsvCsvImportExportHelpers::getTaxonomyFields($type);
	$fields = array_merge($fields, CsvCsvImportExportHelpers::getTaxonomyMetaFields($type));
	$data = array();

	// fix for microsoft office
	$data[0] = array('sep=;');
	$data[1] = array_keys($fields);


	outputCSV($data);
}



function outputCSV($data) {
	$outstream = fopen("php://output", 'w');
	function __outputCSV(&$vals, $key, $filehandler) {
		fputcsv($filehandler, $vals, ';', '"');
	}
	array_walk($data, '__outputCSV', $outstream);
	fclose($outstream);
}
?>