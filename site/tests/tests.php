<?php

require_once __DIR__ . '/testframework.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/database.php';
require_once __DIR__ . '/../modules/page.php';

$tests = new TestFramework();

// Вспомогательная функция для получения тестовой БД
function getTestDb(): Database {
    $config = require __DIR__ . '/../config.php';
    $testPath = sys_get_temp_dir() . '/test_' . uniqid() . '.db';
    $db = new Database($testPath);
    $db->Execute("CREATE TABLE IF NOT EXISTS test_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT, 
        name TEXT, 
        value INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    return $db;
}

// test 1: check database connection
function testDbConnection() {
    $config = require __DIR__ . '/../config.php';
    $db = getTestDb();
    $result = $db->Execute("SELECT 1");
    unlink((new ReflectionProperty($db, 'pdo'))->getValue($db)->getAttribute(PDO::ATTR_CONNECTION_STATUS) ? '/dev/null' : '/dev/null'); // заглушка, реальная проверка ниже
    return assertExpression($result !== false, 'DB connection OK', 'DB connection failed');
}

// test 2: test count method
function testDbCount() {
    $db = getTestDb();
    $db->Execute("DELETE FROM test_items");
    $db->Create('test_items', ['name' => 'item1', 'value' => 10]);
    $db->Create('test_items', ['name' => 'item2', 'value' => 20]);
    $count = $db->Count('test_items');
    return assertExpression($count === 2, "Count = 2, got {$count}", "Count failed, expected 2, got {$count}");
}

// test 3: test create method
function testDbCreate() {
    $db = getTestDb();
    $id = $db->Create('test_items', ['name' => 'new_item', 'value' => 42]);
    $row = $db->Read('test_items', $id);
    return assertExpression(
        $id > 0 && $row['name'] === 'new_item' && $row['value'] == 42,
        'Create OK, ID=' . $id,
        'Create failed'
    );
}

// test 4: test read method
function testDbRead() {
    $db = getTestDb();
    $id = $db->Create('test_items', ['name' => 'read_test', 'value' => 100]);
    $row = $db->Read('test_items', $id);
    return assertExpression(
        !empty($row) && $row['id'] == $id && $row['name'] === 'read_test',
        'Read OK',
        'Read failed or returned empty'
    );
}

// test 5: test update method
function testDbUpdate() {
    $db = getTestDb();
    $id = $db->Create('test_items', ['name' => 'old_name', 'value' => 1]);
    $result = $db->Update('test_items', $id, ['name' => 'updated_name', 'value' => 999]);
    $row = $db->Read('test_items', $id);
    return assertExpression(
        $result && $row['name'] === 'updated_name' && $row['value'] == 999,
        'Update OK',
        'Update failed'
    );
}

// test 6: test delete method
function testDbDelete() {
    $db = getTestDb();
    $id = $db->Create('test_items', ['name' => 'to_delete', 'value' => 5]);
    $result = $db->Delete('test_items', $id);
    $row = $db->Read('test_items', $id);
    return assertExpression(
        $result && empty($row),
        'Delete OK',
        'Delete failed or row still exists'
    );
}

// test 7: test fetch method
function testDbFetch() {
    $db = getTestDb();
    $db->Execute("DELETE FROM test_items");
    $db->Create('test_items', ['name' => 'f1', 'value' => 1]);
    $db->Create('test_items', ['name' => 'f2', 'value' => 2]);
    $rows = $db->Fetch("SELECT * FROM test_items ORDER BY value");
    return assertExpression(
        count($rows) === 2 && $rows[0]['name'] === 'f1' && $rows[1]['name'] === 'f2',
        'Fetch OK, 2 rows returned',
        'Fetch failed or wrong order'
    );
}

// test 8: test execute method (generic)
function testDbExecute() {
    $db = getTestDb();
    $result1 = $db->Execute("INSERT INTO test_items (name, value) VALUES ('exec_test', 777)");
    $result2 = $db->Execute("DELETE FROM test_items WHERE name = 'exec_test'");
    return assertExpression(
        $result1 && $result2,
        'Execute OK for INSERT and DELETE',
        'Execute failed'
    );
}

// test 9: test Page constructor
function testPageConstructor() {
    $templatePath = __DIR__ . '/../templates/index.tpl';
    $page = new Page($templatePath);
    return assertExpression(
        $page instanceof Page,
        'Page constructor OK',
        'Page constructor failed'
    );
}

// test 10: test Page render method
function testPageRender() {
    $templatePath = __DIR__ . '/../templates/index.tpl';
    $page = new Page($templatePath);
    $output = $page->Render([
        'title' => 'Test Title',
        'content' => '<p>Test Content</p>',
        'count' => 42
    ]);
    $hasTitle = strpos($output, 'Test Title') !== false;
    $hasContent = strpos($output, '<p>Test Content</p>') !== false;
    $hasCount = strpos($output, '42') !== false;
    return assertExpression(
        $hasTitle && $hasContent && $hasCount,
        'Render OK, all variables substituted',
        'Render failed, missing variables in output'
    );
}

// add tests
$tests->add('Database connection', 'testDbConnection');
$tests->add('table count', 'testDbCount');
$tests->add('data create', 'testDbCreate');
$tests->add('data read', 'testDbRead');
$tests->add('data update', 'testDbUpdate');
$tests->add('data delete', 'testDbDelete');
$tests->add('fetch results', 'testDbFetch');
$tests->add('execute generic', 'testDbExecute');
$tests->add('Page constructor', 'testPageConstructor');
$tests->add('Page render', 'testPageRender');

// run tests
$tests->run();

echo PHP_EOL . '=== Results: ' . $tests->getResult() . ' ===' . PHP_EOL;