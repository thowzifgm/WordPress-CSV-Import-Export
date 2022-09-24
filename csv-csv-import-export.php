<?php
/*
Plugin Name: CSV Import / Export
Version: 1.0.0
Description: You can import items from CSV or export items to CSV file in your directory-based theme

Author: CsvThemes.Club
Author URI: http://thowzif.com

*/

define("CSV_IMPORT_PLUGIN_ENABLED",true);define("CSV_IMPORT_PLUGIN_URL",plugins_url().'/csv-toolkit/cpts/');define("CSV_IMPORTEXPORT_PLUGIN_PATH",dirname(__FILE__));require_once
CSV_IMPORTEXPORT_PLUGIN_PATH.'/admin/CsvCsvImportExportAdmin.php';require_once
CSV_IMPORTEXPORT_PLUGIN_PATH.'/admin/CsvCsvImportExportAdminPostTypeTable.php';$csvPluginsList[]='csv-csv-import-export';if(!isset($csvPluginsInitialized)){add_action('init',function(){$domain=explode('/',preg_replace('|https?://|','',get_option('siteurl')))[0];if(defined('CSV_THEME_PACKAGE')&&!in_array($domain,['localhost','127.0.0.1'])){if(wp_doing_ajax()){add_action('csv-save-options',function($data){if(isset($data['_CSV_api_key_plugin'])){update_option('_CSV_api_key_plugin',$data['_CSV_api_key_plugin']);}delete_transient('check_CSV_subscription_plugin');});}else{if(CSV_THEME_PACKAGE==='themeforest'){$key=get_option('_CSV_api_key_plugin');if(is_super_admin()){add_filter('csv-get-full-config',function($config,$type)use($domain){if($type==='theme'&&isset($config['licensing'])){$config['licensingPlugin']=$config['licensing'];$config['licensingPlugin']['@title']=__('API Key','csv-admin');$config['licensingPlugin']['@options'][0]['domain']['callback']=function()use($domain){?>
                                    <div class="csv-opt-label">
                                        <div class="csv-label-wrapper">
                                            <label class="csv-label" for="csv-plugin-api-domain"><?php esc_html_e('Domain')?></label>
                                        </div>
                                    </div>
                                    <div class="csv-opt csv-opt-text">
                                        <div class="csv-opt-wrapper">
                                            <input type="text" id="csv-plugin-api-domain" value="<?php echo
esc_attr($domain)?>" onclick="this.focus();this.select()" readonly style="cursor: copy;">
                                        </div>
                                        <div class="csv-help"><?php echo
wp_kses_post(__('You will need this when you will be generating API Key','csv'))?></div>
                                    </div>
                                    <?php
};$config['licensingPlugin']['@options'][0]['key']['callback']=function(){?>
                                    <div class="csv-opt-label">
                                        <div class="csv-label-wrapper">
                                            <label class="csv-label" for="csv-plugin-api-key"><?php _e('API Key','csv')?></label>
                                        </div>
                                    </div>
                                    <div class="csv-opt csv-opt-text">
                                        <div class="csv-opt-wrapper">
                                            <input type="text" id="csv-plugin-api-key" name="_CSV_api_key_plugin" value="<?php echo
esc_attr(get_option('_CSV_api_key_plugin'))?>">
                                        </div>
                                        <div class="csv-help">
                                            <?php echo
wp_kses_post(sprintf(__('You can generate API Key for the domain in your %sCsvThemes account%s.','csv'),'<a href="https://system.csv-themes.club/account/api" target="_blank">','</a>'))?>
                                        </div>
                                    </div>
                                    <?php
};}return$config;},10,2);}}else{$o=get_option('_CSV_updater_options');$key=!empty($o['api_key'])?$o['api_key']:'';}$response=get_transient('check_CSV_subscription_plugin');if(!is_object($response)||!isset($response->last)||((24*HOUR_IN_SECONDS)<(time()-$response->last))){$responseRaw=wp_remote_post('https://system.csv-themes.club/api/5.0/subscriptions/check',['timeout'=>3,'body'=>['domain'=>$domain,'key'=>$key,'package'=>'plugins','theme'=>CSV_THEME_CODENAME,'plugins'=>json_encode($GLOBALS['csvPluginsList'])]]);if(is_wp_error($responseRaw)){error_log($responseRaw->get_error_message());}$response=new
stdClass;$response->last=time();$response->body=json_decode(wp_remote_retrieve_body($responseRaw),true);$response->code=wp_remote_retrieve_response_code($responseRaw);set_transient('check_CSV_subscription_plugin',$response);}switch($response->code){case
401:if(is_super_admin()){add_action('admin_notices',function(){printf('<div class="notice notice-warning notice-large"><p><strong class="notice-title">%1$s</strong><br>%2$s</p></div>',esc_html(__('Invalid API Key for this domain','csv')),wp_kses_post(sprintf(__('Please enter a valid API key for this domain. You can configure it in %sTheme Admin &rarr; Theme Options &rarr; API Key%s.','csv'),'<a href="'.admin_url('admin.php?page=csv-theme-options#csv-theme-options-licensing'.(CSV_THEME_PACKAGE==='themeforest'?'plugin':'').'-panel').'">','</a>')));});}if(!is_admin()){add_filter('template_include',function($template){wp_die(__('Please enter a valid API key for this domain. You can configure it in Theme Options.','csv'),__('Invalid API Key for this domain','csv'));return$template;});}break;case
500:if(is_super_admin()){add_action('admin_notices',function(){printf('<div class="notice notice-warning notice-large"><p><strong class="notice-title">%1$s</strong><br>%2$s</p></div>',esc_html(__('Error checking API Key','csv')),wp_kses_post(__('There was and error while checking API Key.','csv')));});}break;}}}});$csvPluginsInitialized=true;}CsvCsvImportExportAdmin::run(__FILE__);