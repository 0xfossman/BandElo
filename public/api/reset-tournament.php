<?php

require __DIR__ . '/../../config/bootstrap.php';

use BandElo\Http\Response;
use BandElo\Http\Security;

try {
    Security::requireUser();
    Security::requireCsrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    unset($_SESSION['tournament']);
    Response::json(['success' => true]);
} catch (Throwable $e) {
    Response::json(['success' => false, 'error' => $e->getMessage()], 400);
}
