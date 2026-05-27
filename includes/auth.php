<?php
// ============================================================
//  includes/auth.php — Session & access control helpers
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require any authenticated user; redirect to login if not.
 */
function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

/**
 * Require admin role; redirect to dashboard if non-admin.
 */
function require_admin(): void {
    require_login();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: ' . APP_URL . '/dashboard.php');
        exit;
    }
}

/**
 * Redirect already-logged-in users away from login/register pages.
 */
function redirect_if_logged_in(): void {
    if (!empty($_SESSION['user_id'])) {
        if ($_SESSION['user_role'] === 'admin') {
            header('Location: ' . APP_URL . '/admin/admin-dashboard.php');
        } else {
            header('Location: ' . APP_URL . '/dashboard.php');
        }
        exit;
    }
}

/**
 * Returns the current user's full row from the DB (cached in session).
 */
function current_user(): array {
    if (empty($_SESSION['user_id'])) {
        return [];
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role'  => $_SESSION['user_role'],
        'plan'  => $_SESSION['user_plan'],
        'xp'    => $_SESSION['user_xp'],
    ];
}

/**
 * Insert a row into admin_logs.
 */
function log_admin_action(string $action, ?string $target_type = null, ?int $target_id = null, ?string $detail = null): void {
    if (empty($_SESSION['user_id'])) return;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    db_run(
        'INSERT INTO admin_logs (admin_id, action, target_type, target_id, detail, ip_address)
         VALUES (?, ?, ?, ?, ?, ?)',
        [$_SESSION['user_id'], $action, $target_type, $target_id, $detail, $ip]
    );
}

/**
 * Flash message helpers.
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
}

function get_flash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function render_flash(): void {
    $f = get_flash();
    if (!$f) return;
    $color = $f['type'] === 'success' ? 'green' : ($f['type'] === 'error' ? 'red' : 'blue');
    echo '<div class="mb-4 px-4 py-3 rounded-xl text-sm font-semibold bg-' . $color . '-100 text-' . $color . '-700 border border-' . $color . '-200">'
       . e($f['msg'])
       . '</div>';
}
