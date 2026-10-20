<?php

function systemsSidebarItems(mysqli $conn): array
{
    $result = $conn->query('SELECT id, title, url, icon, menu_type FROM custom_menus WHERE is_active = TRUE ORDER BY order_position ASC');

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function systemsList(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, name, description, system_url, logo_path
        FROM company_systems
        WHERE is_active = TRUE
        ORDER BY order_position ASC, name ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function systemsLogoUrl(string $base_url, ?string $path): ?string
{
    $path = str_replace('\\', '/', trim((string)$path));

    if (!preg_match('#^uploads/systems/[a-zA-Z0-9._-]+$#', $path)) {
        return null;
    }

    return rtrim($base_url, '/') . '/' . $path;
}
