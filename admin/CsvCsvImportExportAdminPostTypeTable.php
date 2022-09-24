<?php

class CsvCsvImportExportAdminPostTypeTable
{

	private static $instance;


	// public function __construct() {

	// }





	public static function getInstance(){
		if(self::$instance == null){
			self::$instance = new self;
			return self::$instance;
		}
		return self::$instance;
	}



	public function createPostTable($fields, $postTypeData){
		$containerId = 'csv-csv-container-' . $postTypeData['name'];

		?>
		<div id="<?php echo $containerId ?>" data-slug="<?php echo $postTypeData['name'] ?>" class="csv-csv-container">
			<div class="csv-csv-block csv-csv-part1">
				<table>
					<tr>
						<th><?php _e('Column name in CSV file', 'csv-csv-import-export'); ?></th>
						<th><?php _e('Type', 'csv-csv-import-export'); ?></th>
					</tr>

					<?php
					foreach ($fields as $field => $values) {?>
					<tr>
						<td><?php echo $field; ?></td>
						<td><?php echo $values['type']; ?></td>
					</tr>
					<?php } ?>
				</table>
				<form class="csv-csv-sample-form" action="<?php echo plugin_dir_url( __FILE__ ) ?>sample.php" method="post" enctype="multipart/form-data">
					<input type="hidden" name="csv-sample-type" value="<?php echo $postTypeData['name'] ?>">
					<div class="csv-csv-button">
						<input type="submit" value="<?php _e('Download sample CSV', 'csv-csv-import-export'); ?>" class="csv-button download">
					</div>
				</form>
			</div>

			<div class="csv-opt-container csv-opt-file-upload-main csv-csv-block csv-csv-part2">
				<h4><?php _e('Import from file', 'csv-csv-import-export'); ?></h4>
				<form class="csv-csv-import-form" action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" onsubmit="javascript: importCSV(event, '<?php echo $containerId ?>', '<?php echo $postTypeData["type"] ?>');" enctype="multipart/form-data">

					<div class="csv-opt csv-opt-file-upload csv-csv-file">
						<input type="hidden" name="slug" value="<?php echo $postTypeData['name']; ?>">
						<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'form-nonce' );?>" />
						<input type="hidden" name="upload-handler" value="<?php echo plugin_dir_url( __FILE__ ) ?>upload-handler.php" class="upload-handler" />
						<div class="csv-opt-wrapper">
							<label class="csv-opt-file-wrapper">
								<span class="csv-opt-file-input"><?php _ex('Choose your file', 'import', 'csv-admin') ?></span>
								<input name="posts_csv" type="file" accept=".csv" class="file-select">
								<span class="csv-opt-btn"><?php _ex('Browse', 'browse file from disk button label', 'csv-admin') ?></span>
							</label>
						</div>
					</div>
					<div class="csv-backup-action">
						<input type="submit" value="<?php _e('Import from CSV', 'csv-csv-import-export'); ?>" class="csv-button upload positive uppercase">
					</div>
				</form>

				<div class="progress csv-loader" data-max="0" data-current="0">
					<p class="loader-status"><span class="loader-value">0</span>/<span class="loader-max">0</span></p>
					<div class="loader-bar"></div>
				</div>
			</div>

			<div class="csv-csv-report alert alert-danger"></div>

		</div>

	<?php
	}

}

