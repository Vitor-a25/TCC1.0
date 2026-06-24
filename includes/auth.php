<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['usuario_id']);
}

function getTipo(): string {
    return $_SESSION['tipo'] ?? '';
}

function requireLogin(string $tipo = ''): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    if ($tipo && getTipo() !== $tipo) {
        header('Location: login.php?erro=acesso_negado');
        exit;
    }

    if (function_exists('fazerBackupAutomatico')) {
        fazerBackupAutomatico();
    }
}


function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function flash(string $key, string $msg): void {
    $_SESSION['flash'][$key] = $msg;
}

function getFlash(string $key): string {
    $msg = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function formatStars(int $nota): string {
    return str_repeat('★', $nota) . str_repeat('☆', 5 - $nota);
}