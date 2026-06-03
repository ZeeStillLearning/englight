<?php
// ============================================================
//  includes/auth.php — Session, Cookie Timeout & Access Control
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ════════════════════════════════════════════════════════════
//  COOKIE ACTIVITY TIMEOUT
// ════════════════════════════════════════════════════════════

/**
 * Set/perbarui cookie waktu aktivitas terakhir.
 * Dipanggil setiap kali halaman yang butuh login dibuka.
 */
function refresh_activity_cookie(): void {
    setcookie(
        ACTIVITY_COOKIE_NAME,
        (string) time(),
        [
            'expires'  => time() + SESSION_TIMEOUT,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Strict',
        ]
    );
}

/**
 * Cek apakah user masih aktif berdasarkan cookie.
 * Return true  → masih aktif (belum timeout)
 * Return false → sudah timeout / cookie tidak ada
 */
function is_session_active(): bool {
    if (empty($_COOKIE[ACTIVITY_COOKIE_NAME])) {
        return false;
    }
    $last_active = (int) $_COOKIE[ACTIVITY_COOKIE_NAME];
    $elapsed     = time() - $last_active;
    return $elapsed < SESSION_TIMEOUT;
}

/**
 * Logout paksa karena timeout — hapus session & cookie.
 */
function force_logout_timeout(): void {
    // Hapus semua data session
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();

    // Hapus activity cookie
    setcookie(ACTIVITY_COOKIE_NAME, '', time() - 42000, '/');

    // Redirect ke login dengan pesan timeout
    header('Location: ' . APP_URL . '/login.php?timeout=1');
    exit;
}

// ════════════════════════════════════════════════════════════
//  ACCESS CONTROL
// ════════════════════════════════════════════════════════════

/**
 * Require any authenticated user.
 * Juga mengecek cookie timeout — jika tidak aktif, logout otomatis.
 */
function require_login(): void {
    // Belum login sama sekali
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }

    // Sudah login tapi cookie timeout (tidak aktif > 5 menit)
    if (!is_session_active()) {
        force_logout_timeout();
    }

    // Masih aktif — perbarui cookie (reset timer 5 menit)
    refresh_activity_cookie();
}

/**
 * Require admin role.
 */
function require_admin(): void {
    require_login(); // sudah include pengecekan timeout
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
 * Returns the current user data from session.
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

// ════════════════════════════════════════════════════════════
//  FLASH MESSAGES
// ════════════════════════════════════════════════════════════

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
