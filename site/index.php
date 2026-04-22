<?php
require_once 'modules/database.php';
require_once 'modules/page.php';
$config = require 'config.php';

$dbPath = $config['db_path'];
if (!is_dir(dirname($dbPath))) {
    mkdir(dirname($dbPath), 0755, true);
}

$db = new Database($dbPath);

$db->Execute("CREATE TABLE IF NOT EXISTS items (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");

if ($db->Count('items') === 0) {
    $db->Create('items', ['name' => 'Первая запись']);
}

$items = $db->Fetch("SELECT * FROM items");
$count = $db->Count('items');

$content = '<ul>';
foreach ($items as $item) {
    $content .= '<li>' . htmlspecialchars($item['name']) . ' [ID: ' . $item['id'] . ']</li>';
}
$content .= '</ul>';

$page = new Page('templates/index.tpl');
echo $page->Render([
    'title' => 'Список элементов',
    'content' => $content,
    'count' => $count
]);