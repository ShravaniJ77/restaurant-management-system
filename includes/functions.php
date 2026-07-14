<?php
function getPageTitle(string $title, string $default = 'Restaurant Admin'): string
{
    return empty($title) ? $default : $title;
}

// Basic logging utility for errors and security events.
function logError(string $message): void
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/app.log';
    $entry = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}

// CSRF token helpers: generate and validate tokens stored in session.
function generateCsrfToken(string $form = 'default'): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $key = 'csrf_' . $form;
    if (empty($_SESSION[$key])) {
        $_SESSION[$key] = bin2hex(random_bytes(32));
    }

    return $_SESSION[$key];
}

function verifyCsrfToken(string $token, string $form = 'default'): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $key = 'csrf_' . $form;
    if (empty($token) || empty($_SESSION[$key])) {
        return false;
    }

    $valid = hash_equals($_SESSION[$key], $token);
    // Regenerate token after successful check to mitigate replay.
    if ($valid) {
        unset($_SESSION[$key]);
    }

    return $valid;
}

function csrfInputField(string $form = 'default'): string
{
    $token = generateCsrfToken($form);
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

// Simple sanitizers and helpers
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sanitizeString($value): string
{
    return trim(filter_var((string) $value, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
}

function sanitizeInt($value): int
{
    return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
}

function getPost(string $key, $default = null)
{
    return $_POST[$key] ?? $default;
}


function setFlashMessage(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function getFlashMessage(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : [];
}

function displayFlashMessages(): string
{
    $flash = getFlashMessage();

    if (empty($flash['message'])) {
        return '';
    }

    $typeClass = 'info';
    if (($flash['type'] ?? '') === 'success') {
        $typeClass = 'success';
    } elseif (($flash['type'] ?? '') === 'error') {
        $typeClass = 'danger';
    }

    return '<div class="alert alert-' . $typeClass . ' rounded-3" role="alert">' . htmlspecialchars($flash['message']) . '</div>';
}

function uploadMenuImage(array $file, string $uploadDirectory): array
{
    // Centralized image upload with extra validation and logging.
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        logError('uploadMenuImage: no file provided from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        return ['success' => false, 'message' => 'Please select an image file.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        logError('uploadMenuImage: upload error code ' . intval($file['error']) . ' from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        return ['success' => false, 'message' => 'Image upload failed. Please try again.'];
    }

    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        logError('uploadMenuImage: disallowed mime ' . $mimeType . ' from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        return ['success' => false, 'message' => 'Only JPG, PNG, WEBP, and GIF images are allowed.'];
    }

    // Double-check image content using getimagesize
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false || !in_array($imageInfo['mime'] ?? '', $allowedMimeTypes, true)) {
        logError('uploadMenuImage: file is not a valid image from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        return ['success' => false, 'message' => 'Uploaded file is not a valid image.'];
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        logError('uploadMenuImage: file too large (' . $file['size'] . ') from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        return ['success' => false, 'message' => 'Image size must be 2MB or less.'];
    }

    // Map MIME types to safe extensions
    $mimeToExt = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    $extension = $mimeToExt[$mimeType] ?? strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileName = uniqid('menu_', true) . '.' . $extension;
    $destination = rtrim($uploadDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

    if (!is_dir(dirname($destination))) {
        mkdir(dirname($destination), 0777, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        logError('uploadMenuImage: could not move uploaded file to ' . $destination . ' from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        return ['success' => false, 'message' => 'Could not save the uploaded image.'];
    }

    return ['success' => true, 'file_name' => $fileName, 'message' => ''];
}

function renderEmptyState(string $title, string $message, string $icon = 'bi bi-info-circle'): string
{
    return '<div class="empty-state text-center py-5">
        <div class="mb-3 text-muted" style="font-size: 2rem;"><i class="' . $icon . '"></i></div>
        <h5 class="fw-semibold">' . htmlspecialchars($title) . '</h5>
        <p class="text-muted mb-0">' . htmlspecialchars($message) . '</p>
    </div>';
}
?>
