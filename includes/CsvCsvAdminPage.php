<?php


class CsvCsvAdminPage
{
	private static $instance;

	private $optionTitle;

	private $pluginFile;

	private $config;

	private $pageHookname;

	private $codeName = 'csv-csv-import-export';

	/**
	 * @var array
	 */
	protected $compatibleThemes = array('eventguide', 'skeleton', 'cityguide', 'directory2', 'businessfinder2', 'foodguide');

	protected $currentTheme;

	private $pageSlug = 'csv-csv-settings';
	protected static $paths;

	public function run($pluginFile)
	{
		self::$paths = (object) array(
			'dir' => (object) array(
				'pluginfile' => __FILE__,
				'root'       => dirname( __FILE__ ),
			),
			'url' => (object) array(
				'root'     => plugins_url('', __FILE__),
			),
		);
		$theme = wp_get_theme();
		$this->currentTheme = $theme->parent() != false ? $theme->parent()->stylesheet : $theme->stylesheet;

		$this->optionTitle = __("CSV Import/Export", "csv-csv-import-export");

		$this->pluginFile = $pluginFile;

		$this->config = require_once dirname($this->pluginFile) . '/config/admin-config.php';

		register_activation_hook($pluginFile, array($this, 'onPluginActivationCallback'));
		add_action('admin_menu', array($this, 'adminMenu'));
		add_action('admin_init', array($this, 'onAdminInitCallback'));
		add_action('switch_theme', array($this, 'onSwitchTheme'), 10);
	}



	public function adminMenu(){
		if ( defined('CSV_THEME_CODENAME') && in_array(CSV_THEME_CODENAME, $this->compatibleThemes) ) {
			add_filter( 'csv-enqueue-admin-assets', function($return){
			    if (strpos(get_current_screen()->id,'CSV_csv_options') !== false) {
			        return true;
			    }
			    return $return;
			});
			$this->pageHookname = add_submenu_page(
				'csv-theme-options',
				$this->optionTitle,
				$this->optionTitle,
				apply_filters('csv-import-export-menu-permission', 'edit_theme_options'),
				'CSV_csv_options',
				array($this, 'adminPage')
			);
		} else {
			add_action( 'admin_notices', array($this, 'deactivateMessage') );
		}
	}
	public function onPluginActivationCallback($network_wide )
	{
		$this->checkPluginCompatibility(true);
		if(class_exists('CsvCache')){
			CsvCache::clean();
		}

		if (  $network_wide )
			wp_die('CSV import / export Plugin is not allowed for network activation');

	}

	public function onAdminInitCallback()
	{
		if ( defined('CSV_THEME_CODENAME') && in_array(CSV_THEME_CODENAME, $this->compatibleThemes) ) {
			// render import page
			add_settings_section(
				'import',
				__('Import from CSV', 'csv-csv-import-export'),
				'__return_false',
				$this->codeName.'_options'
			);

			add_settings_field(
				'import',
				"",
				array($this, 'importAdminPage'),
				$this->codeName.'_options',
				'import'
			);

			// render export page
			add_settings_section(
				'export',
				__('Export to CSV', 'csv-csv-import-export'),
				'__return_false',
				$this->codeName.'_options'
			);

			add_settings_field(
				'export',
				"",
				array($this, 'exportAdminPage'),
				$this->codeName.'_options',
				'export'
			);
		} else {
			add_action( 'admin_notices', array($this, 'deactivateMessage') );
		}


	}

	public function adminPage(){
		echo "
		<script type='text/javascript'>
		jQuery(document).ready(function(){

			var context = jQuery('.theme-admin_page_CSV_quick_comments_options');

			var select = context.find('.csv-opt-page-select .chosen-wrapper select');
			select.addClass('chosen').chosen();
			context.find('.csv-opt-color .csv-colorpicker').colorpicker();
			csv.admin.options.Ui.onoff(context);

			var currentPage = 'csv-import-export';
			var id = '#csv-' + currentPage;
			new csv.admin.Tabs(jQuery(id + '-tabs'), jQuery(id + '-panels'), 'csv-admin-' + currentPage + '-page');
		});

		</script>";
		echo '<div class="wrap">';
			echo '<div id="'.$this->codeName.'-page" class="csv-admin-page csv-options-layout">';
				echo '<div class="csv-admin-page-wrap">';

					/* Hack for WP notifications, all will be placed right after this h2 */
					echo '<h2 style="display: none;"></h2>';

					echo '<div class="csv-options-page-header">';
						echo '<h3 class="csv-options-header-title">'. $this->optionTitle .'</h3>';
						echo '<div class="csv-options-header-tools">';
							echo '<a class="csv-scroll-to-top"><i class="fa fa-chevron-up"></i></a>';
						echo '</div>';

						echo '<div class="csv-sticky-header">';
							echo '<h4 class="csv-sticky-header-title">'. $this->optionTitle .'<i class="fa fa-circle"></i><span class="subtitle"></span></h4>';
						echo '</div>';
					echo '</div>';

					echo '<div class="csv-options-page">';
						echo '<div class="csv-options-page-content">';

							echo '<div class="csv-options-sidebar">';
								echo '<div class="csv-options-sidebar-content">';
									echo '<ul id="'.$this->codeName.'-tabs" class="csv-options-tabs">';
										foreach($this->config['admin'] as $section => $settings){
											echo '<li id="'.$this->codeName.'-'.$section.'-panel-tab" class=""><a href="#'.$this->codeName.'-'.$section.'-panel">'.$settings['title'].'</a></li>';
										}
									echo '</ul>';
								echo '</div>';
							echo '</div>';

							echo '<div class="csv-options-content">';
								echo '<div class="csv-options-controls-container">';
									echo '<div id="'.$this->codeName.'-panels" class="csv-options-controls csv-options-panels">';
										// echo '<form action="options.php" method="post" name="">';
										settings_fields($this->codeName.'_options');
										foreach($this->config['admin'] as $section => $settings){
											switch($section){
												case 'subscription':

												break;
												default:
													echo '<div id="'.$this->codeName.'-'.$section.'-panel" class="csv-options-group csv-options-panel '.$this->codeName.'-tabs-panel">';
														echo '<div id="csv-options-basic-'.$section.'" class="csv-controls-tabs-panel csv-options-basic">';
															// echo '<div class="csv-options-section ">';
																// echo '<div class="csv-opt-container csv-opt--main">';
																	// echo '<div class="csv-opt-wrap">';
																		do_settings_fields( $this->codeName.'_options', $section );
																	// echo '</div>';
																// echo '</div>';
															// echo '</div>';
														echo '</div>';
													echo '</div>';
												break;
											}
										}
										// echo '</form>';
									echo '</div>';
								echo '</div>';
							echo '</div>';

						echo '</div>';
					echo '</div>';

				echo '</div>';
			echo '</div>';
		echo '</div>';
	}



	public function importAdminPage(){
    	require_once dirname($this->pluginFile) . '/admin/CsvCsvImportExportHelpers.php';
    	$docsUrl = "https://www.csv-themes.club/doc/how-to-import-csv-file/";
		$cpts = CsvCsvImportExportHelpers::getSupportedCpts();
        $taxonomies = array();
        foreach ($cpts as $cpt => $cptData) {
            $taxonomies = array_merge($taxonomies, CsvCsvImportExportHelpers::getPostTaxonomyFields($cpt));

        }

        // i had to create indexed array of all types because of config sections
        // javascript displays sections that contain a selected id in the seciton name
        // if I select "csv-item" also section "csv-items" becomes visible
        $types = array();
        foreach ($cpts as $cpt) {
        	array_push($types, $cpt);
        }
        foreach ($taxonomies as $taxonomy) {
        	array_push($types, $taxonomy);
        }



        $languages = Csvlangs::getLanguagesList();



		?>




		<div class="csv-options-section">

			<div class='csv-opt-container csv-opt-select-main'>
				<div class='csv-opt-wrap'>
					<div class='csv-opt-label'>
						<div class='csv-label-wrapper'>
							<span class='csv-label'><?php _e('Language', 'csv-csv-import-export') ?></span>
						</div>
					</div>
					<div class='csv-opt csv-opt-select'>
						<div class='csv-opt-wrapper chosen-wrapper'>
							<select data-placeholder="<?php _e('Choose&hellip;', 'csv-admin') ?>" class="chosen" name="CSV_csv_options[language]">
								<!-- <option selected value="none"><?php _e('Choose&hellip;', 'csv-admin') ?></option> -->
							<?php foreach($languages as $language) : ?>
								<option value="<?php echo $language->slug ?>"><?php echo $language->name ?></option>
							<?php endforeach; ?>


							</select>
						</div>
					</div>
				</div>
			</div>

			<div class='csv-opt-container csv-opt-select-main'>
				<div class='csv-opt-wrap'>
					<div class='csv-opt-label'>
						<div class='csv-label-wrapper'>
							<span class='csv-label'><?php _e('Select type of data', 'csv-csv-import-export') ?></span>
						</div>
					</div>
					<div class='csv-opt csv-opt-select'>
						<div class='csv-opt-wrapper chosen-wrapper'>
							<select data-placeholder="<?php _e('Choose&hellip;', 'csv-admin') ?>" class="chosen" name="CSV_csv_options[postType]">
								<option selected value="none"><?php _e('Choose&hellip;', 'csv-admin') ?></option>
							<?php foreach($types as $index => $type) : ?>
								<option value="<?php echo $index ?>"><?php echo $type['label'] ?></option>
							<?php endforeach; ?>


							</select>
						</div>
					</div>
				</div>
			</div>
		</div>


		<?php foreach($types as $index => $type) : ?>
			<div class="csv-options-section  section-postType-<?php echo $index ?> csv-sec-title" id="postType-<?php echo $index ?>-basic">
				<h2 class="csv-options-section-title"><?php echo $type['label'] ?></h2>
				<div class="csv-options-section-help">
				<?php
					printf(__('Description for each type with examples: %s', 'csv-csv-import-export'),
					'<a target="blank" href="'.$docsUrl.'">'.$docsUrl.'</a>');
				?>
				</div>
				<?php
				$importData = array(
					'name'  => $type['slug'],
					'label' => $type['label'],
					'type'  => $type['type']

		        );


				if ($type['type'] == 'cpt') {
					// TODO hladat taxonomy fields a metafields dynamicky
			        $taxonomyFields = CsvCsvImportExportHelpers::getPostTaxonomyFields($type['slug']);

			        $itemConfig = CsvCsvImportExportHelpers::getRawConfig($type['slug']);
			        $itemFields = CsvCsvImportExportHelpers::getPostMetaFields($itemConfig);
			        $itemFields = $itemFields['newFields'];
			        $defaultPostFields = CsvCsvImportExportHelpers::getDefaultPostFields();
					$fields = $defaultPostFields + $taxonomyFields + $itemFields;
		        } else {
		        	$defaultTaxonomyFields = CsvCsvImportExportHelpers::getTaxonomyFields($type['slug']);
					$fields = array_merge($defaultTaxonomyFields, CsvCsvImportExportHelpers::getTaxonomyMetaFields($type['slug']));
		        }

				$this->postTypeTable = new CsvCsvImportExportAdminPostTypeTable();
				$this->postTypeTable->createPostTable($fields, $importData);

				?>
			</div>

		<?php endforeach; ?>



		<?php

	}

	public function checkPluginCompatibility($die = false)
	{
		if ( !in_array($this->currentTheme, $this->compatibleThemes) ) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php' );
			$pluginFile = $this->getPaths('dir')->pluginfile;
			deactivate_plugins(plugin_basename($pluginFile));
			if($die){
				wp_die('Current theme is not compatible with CSV import / export plugin :(', '',  array('back_link'=>true));
			} else {
				add_action( 'admin_notices', array($this, 'deactivateMessage') );
			}
		}
	}

	public function onSwitchTheme()
	{
		$this->checkPluginCompatibility();
	}

	public function deactivateMessage() {
			echo "<div class='error'><p>" . _x('Current theme is not compatible with CSV import / export plugin!', 'csv-csv-import-export') . "</p></div>";
	}

	public function exportAdminPage()
	{
		require_once dirname($this->pluginFile) . '/admin/CsvCsvImportExportHelpers.php';

        $cpts = CsvCsvImportExportHelpers::getSupportedCpts();
        $taxonomies = array();
        foreach ($cpts as $cpt => $cptData) {
            $taxonomies = array_merge($taxonomies, CsvCsvImportExportHelpers::getPostTaxonomyFields($cpt));
        }
        $types = $cpts + $taxonomies;
        $languages = Csvlangs::getLanguagesList();
        ?>
		<div class="csv-options-section">

        <form id="csv-csv-import" class="csv-opt-container" action="<?php echo plugin_dir_url( $this->pluginFile ) ?>/admin/export.php" method="post" enctype="multipart/form-data">
        	<div class='csv-opt-container csv-opt-select-main'>
				<div class='csv-opt-wrap'>
					<div class='csv-opt-label'>
						<div class='csv-label-wrapper'>
							<span class='csv-label'><?php _e('Select type of data', 'csv-csv-import-export') ?></span>
						</div>
					</div>
					<div class='csv-opt csv-opt-select'>
						<div class='csv-opt-wrapper chosen-wrapper'>
							<select data-placeholder="<?php _e('Choose&hellip;', 'csv-admin') ?>" class="chosen" name="post-type">
							<?php foreach($types as $slug => $type) : ?>
								<option value="<?php echo $slug ?>"><?php echo $type['label'] ?></option>
    						<?php endforeach; ?>


							</select>
						</div>
					</div>
				</div>
			</div>

			<div class='csv-opt-container csv-opt-select-main'>
				<div class='csv-opt-wrap'>
					<div class='csv-opt-label'>
						<div class='csv-label-wrapper'>
							<span class='csv-label'><?php _e('Language', 'csv-csv-import-export') ?></span>
						</div>
					</div>
					<div class='csv-opt csv-opt-select'>
						<div class='csv-opt-wrapper chosen-wrapper'>
							<select data-placeholder="<?php _e('Choose&hellip;', 'csv-admin') ?>" class="chosen" name="post-language">
							<?php foreach($languages as $language) : ?>
								<option value="<?php echo $language->slug ?>"><?php echo($language->name); ?></option>
    						<?php endforeach; ?>


							</select>
						</div>
					</div>
				</div>
			</div>
			<div class='csv-opt-container csv-opt-select-main'>
				<div class='csv-opt-wrap'>
					<div class='csv-opt-label'>
						<div class='csv-label-wrapper'>
						</div>
					</div>
					<div class="csv-backup-action">
		            	<input type="submit" value="<?php _e('Export CSV', 'csv-csv-import-export'); ?>" class="csv-button positive uppercase">
					</div>
				</div>
			</div>


        </form>
		</div>

        <?php
	}



	public static function getInstance()
	{
		if(!self::$instance){
			self::$instance = new self;
		}

		return self::$instance;
	}

	public static function getPaths($type)
	{
		if ($type == 'dir') {
			return self::$paths->dir;
		} else {
			return self::$paths->url;
		}
	}


}
