<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'PHP App') ?></title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header>
        <h1><?= htmlspecialchars($title ?? 'Главная') ?></h1>
    </header>
    <main>
        <?= $content ?? '' ?>
        <p>Всего записей: <?= $count ?? 0 ?></p>
    </main>
</body>
</html>