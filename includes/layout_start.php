<?php
if (!isset($page_title)) {
    $page_title = 'Portal MCM';
}

if (!isset($base_url)) {
    if (function_exists('appBaseUrl')) {
        $base_url = appBaseUrl();
    } else {
        $script_dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        $base_url = preg_replace('#/pages$#', '', $script_dir);
        if ($base_url === '/') {
            $base_url = '';
        }
    }
}

if (!isset($first_name)) {
    $first_name = 'Usuário';
}

if (!isset($custom_menus)) {
    $custom_menus = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Portal MCM</title>
    <link rel="icon" type="image/svg+xml" href="/basemcm/icon-mcm.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url); ?>/css/base.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url); ?>/css/layout.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url); ?>/css/components.css">

    <?php if (!empty($page_css)): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url . '/' . $page_css); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url); ?>/css/sidebar-text-fix.css">
</head>
<body>
    <div class="layout">
        <?php require __DIR__ . '/sidebar.php'; ?>

        <div class="main-area">
            <?php require __DIR__ . '/topbar.php'; ?>

            <main class="page-content">
