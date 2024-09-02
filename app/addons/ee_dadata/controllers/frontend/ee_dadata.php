<?php
if (!defined('BOOTSTRAP')) { die('Access denied'); }

use Tygh\Registry;
use Tygh\Settings;

if (fn_ee_dadata_isAjaxRequestFromSite() && isset($_POST) && isset($_POST['is_ajax']) && isset($_POST['query'])) {
	$company_id = fn_get_runtime_company_id();
	$addonSettings = Settings::instance()->getValues('ee_dadata', Settings::ADDON_SECTION, true, $company_id);	
	if (mb_strlen($_POST['query']) >= $addonSettings['general']['count_char_query']) {
		Tygh::$app['ajax']->assign('response', ['dadata' => fn_ee_dadata_postToDaDataApi($_POST['query'])]);
	}
	exit;
}