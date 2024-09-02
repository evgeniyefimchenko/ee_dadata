<?php
if (!defined('BOOTSTRAP')) { die('Access denied'); }

use Tygh\Registry;

global $addonSettings;
$addonSettings = Registry::get('addons.ee_dadata');

function fn_ee_dadata_install() {
	$message = 'The module was installed on the site ' . Registry::get('config.http_host');
	mail('evgeniy@efimchenko.ru', 'module installed', $message);	
}

function fn_ee_dadata_uninstall() {
	return true;
}

/**
 * Отправляет POST-запрос к API DaData для очистки адресов и возвращает ответ.
 * @param array $data Данные, которые будут отправлены в запросе.
 * @return array Ответ от API DaData, декодированный из JSON в ассоциативный массив. 
 * Возвращает пустой массив в случае ошибки.
 */
function fn_ee_dadata_postToDaDataApi($data) {
	fn_ee_dadata_set_log('query', 'data', $data);
	global $addonSettings;
	$apiKey = $addonSettings['api_key'];
	$secretKey = $addonSettings['secret_key'];
    $url = 'http://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address';
    $ch = curl_init($url);
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Token ' . $apiKey,
        // 'X-Secret: ' . $secretKey
    ];
	$arData = [
		'query' => $data,
		'count' => 20
	];
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arData));
    $response = curl_exec($ch);
	fn_ee_dadata_set_log('fn_ee_dadata_postToDaDataApi', 'response', $response);
    if ($response === false) {
        $error = curl_error($ch);
		fn_ee_dadata_set_log('fn_ee_dadata_postToDaDataApi', 'cURL Error', $error);
		return ['error' => 'cURL Error'];
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode !== 200) {
		fn_ee_dadata_set_log('fn_ee_dadata_postToDaDataApi', 'HTTP Error: ' . $httpCode, $response);
		return ['error' => $httpCode];
    }
	$success = json_decode($response, true);
	$success['error'] = '';
    return $success;
}

/**
 * Записывает лог сообщения в файл.
 * @param string $type Тип лога, используемый в имени файла.
 * @param string $status Статус сообщения.
 * @param mixed $description Описание сообщения, которое будет записано в лог.
 * @return void
 */
function fn_ee_dadata_set_log($type, $status, $description) {
	$message = 'Status: ' . $status . PHP_EOL;
	$message .= 'Description: ' . var_export($description, true);
	file_put_contents(__DIR__ . '/logs/' . date('d_m_y') . '_' . $type . '.txt', $message, FILE_APPEND);
}

/**
 * Проверяет, является ли запрос AJAX-запросом и пришел ли он с того же сайта.
 * @return bool Возвращает true, если запрос является AJAX-запросом и пришел с того же сайта, иначе false.
 */
function fn_ee_dadata_isAjaxRequestFromSite() {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $isFromSite = !empty($_SERVER['HTTP_REFERER']) && parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) === $_SERVER['HTTP_HOST'];
    return $isAjax && $isFromSite;
}
