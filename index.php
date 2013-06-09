<?php

/**
 * Скрипт для импорта в japancar
 */
/**
 * Данные для соединения с базой
 */
define('db_host', 'localhost');
define('db_name', 'miha');
define('db_login', 'root');
define('db_password', 'qweqweqwwe');

define('img_path', 'http://str-garage.com/uploads/goods/');
define('offer_path', 'http://str-garage.com/goods/');
/// если стоит 0 значит файл будет созранятся на сервер в папку со скриптом
/// иначе будет сразу скачиваться
define('download_now', 1);

/**
 * Данные о компании
 */
$company = array(
    'name' => 'companyname',
    'address' => 'root',
    'city' => 'nvkz',
    'tel' => '123123',
    'email' => 'ssssss',
    'web' => 'kalimru',
    'skype' => 'akomru2',
    'icq' => '1782517'
);

$db = mysql_connect(db_host, db_login, db_password);
mysql_select_db(db_name);
mysql_query('set CHARACTER SET utf8');

$dom = new domDocument('1.0', 'utf-8');
$root = $dom->createElement('japancarru_import_data');

// заполняем информацию о компании
$dealer = $dom->createElement('dealer');
$dealer->setAttribute('desc', 'Информация о комапании');
foreach ($company as $key => $value) {
    $temp = $dom->createElement($key, $value);
    $dealer->appendChild($temp);
}
$root->appendChild($dealer);

// заполняем инормацию о товарах
$offers = $dom->createElement('data_list');
$offers->setAttribute('desc', 'Список объявлений');

// получаем данные из БД
$result = mysql_query('
SELECT nc.id AS id, nc.title AS title, cost, ncc.name AS model,
    ncc.title AS mark, ncg.maker AS maker, ncc.parent_id AS parent_id, ncg.stock as nal
FROM ng_content AS nc
LEFT JOIN ng_content_goods AS ncg ON nc.id = ncg.content_id
LEFT JOIN ng_content_category AS ncc ON nc.category_id = ncc.id
WHERE nc.active =1');
while ($data = mysql_fetch_array($result)) {
    $temp = $dom->createElement('data');
    $temp->appendChild($dom->createElement('partsname', $data['title']));
    $temp->appendChild($dom->createElement('used',
                    ($data['parent_id'] == 187) ? '1' : '0'));
    $temp->appendChild($dom->createElement('firm', $data['mark']));
    $temp->appendChild($dom->createElement('model', $data['model']));
    $temp->appendChild($dom->createElement('kuzov'));
    $temp->appendChild($dom->createElement('engine'));
    $temp->appendChild($dom->createElement('modelnumber'));
    $temp->appendChild($dom->createElement('R_L'));
    $temp->appendChild($dom->createElement('U_D'));
    $temp->appendChild($dom->createElement('F_R'));
    $temp->appendChild($dom->createElement('oem_code'));
    $temp->appendChild($dom->createElement('producer', $data['maker']));
    $temp->appendChild($dom->createElement('producer_code'));
    $temp->appendChild($dom->createElement('price', $data['cost']));
    $temp->appendChild($dom->createElement('currency', 'руб.'));
    $temp->appendChild($dom->createElement('origcode', $data['id']));
    $temp->appendChild($dom->createElement('s_presence',
                    ($data['nal'] == 1) ? 'в наличии' : 'под заказ'));

    // заполняем поле фоток
    $temp2 = $dom->createElement('photos_list');
    $photoresult = mysql_query('select * from ng_goods_photo where goods_id=' . $data['id']);
    while ($photodata = mysql_fetch_array($photoresult)) {
        $temp2->appendChild($dom->createElement('photo_name',
                        img_path . $photodata['filename']));
    }
    $temp->appendChild($temp2);

    // ставим url
    $temp->appendChild($dom->createElement('link', offer_path.$data['id'].'/'));

    $offers->appendChild($temp);
}

$root->appendChild($offers);

$dom->appendChild($root);
if(download_now == 0)
    $dom->save("base.xml");
else {
    header('Content-type: "text/xml"; charset="utf8"');
    header('Content-disposition: attachment; filename="base.xml"');
    echo $dom->saveXML();
}
?>
