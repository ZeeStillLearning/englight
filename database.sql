-- ============================================================
--  EngLight — Database Schema
--  Engine  : InnoDB | Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS englight_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE englight_db;

-- ----------------------------------------------------------------
-- 1. USERS
-- ----------------------------------------------------------------
CREATE TABLE users (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(150)                          NOT NULL,
    email        VARCHAR(191)                          NOT NULL UNIQUE,
    password     VARCHAR(255)                          NOT NULL,
    role         ENUM('user','admin')                  NOT NULL DEFAULT 'user',
    plan         ENUM('free','premium','pro')          NOT NULL DEFAULT 'free',
    xp           INT UNSIGNED                          NOT NULL DEFAULT 0,
    avatar_url   VARCHAR(500)                          NULL,
    is_active    TINYINT(1)                            NOT NULL DEFAULT 1,
    created_at   DATETIME                              NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME                              NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 2. MEMBERSHIP / SUBSCRIPTIONS
-- ----------------------------------------------------------------
CREATE TABLE memberships (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED                          NOT NULL,
    plan         ENUM('free','premium','pro')          NOT NULL,
    started_at   DATETIME                              NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at   DATETIME                              NULL,
    payment_ref  VARCHAR(100)                          NULL,
    amount       DECIMAL(12,2)                         NOT NULL DEFAULT 0.00,
    status       ENUM('active','expired','cancelled')  NOT NULL DEFAULT 'active',
    created_at   DATETIME                              NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_membership_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 3. MATERI (LEARNING MATERIAL)
-- ----------------------------------------------------------------
CREATE TABLE materi (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255)                          NOT NULL,
    description  TEXT                                  NULL,
    category     ENUM('listening','structure','reading','grammar','vocabulary','speaking') NOT NULL,
    type         ENUM('video','pdf','text')            NOT NULL DEFAULT 'video',
    file_path    VARCHAR(500)                          NULL,
    is_premium   TINYINT(1)                            NOT NULL DEFAULT 0,
    sort_order   SMALLINT UNSIGNED                     NOT NULL DEFAULT 0,
    created_by   INT UNSIGNED                          NOT NULL,
    created_at   DATETIME                              NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME                              NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_materi_user
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 4. USER MATERI PROGRESS
-- ----------------------------------------------------------------
CREATE TABLE user_materi_progress (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    materi_id    INT UNSIGNED NOT NULL,
    is_completed TINYINT(1)   NOT NULL DEFAULT 0,
    completed_at DATETIME     NULL,
    UNIQUE KEY uq_user_materi (user_id, materi_id),
    CONSTRAINT fk_ump_user   FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ump_materi FOREIGN KEY (materi_id) REFERENCES materi(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 5. BANK SOAL (QUESTION BANK)
-- ----------------------------------------------------------------
CREATE TABLE questions (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_text  TEXT                                                  NOT NULL,
    option_a       VARCHAR(500)                                          NOT NULL,
    option_b       VARCHAR(500)                                          NOT NULL,
    option_c       VARCHAR(500)                                          NOT NULL,
    option_d       VARCHAR(500)                                          NOT NULL,
    correct_answer ENUM('a','b','c','d')                                 NOT NULL,
    explanation    TEXT                                                  NULL,
    category       ENUM('listening','structure','reading')               NOT NULL,
    difficulty     ENUM('easy','medium','hard')                          NOT NULL DEFAULT 'medium',
    created_by     INT UNSIGNED                                          NOT NULL,
    created_at     DATETIME                                              NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME                                              NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_question_user
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 6. LATIHAN SOAL SESSIONS
-- ----------------------------------------------------------------
CREATE TABLE latihan_sessions (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    category      VARCHAR(50)  NULL,
    total_soal    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    correct_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    score         TINYINT UNSIGNED  NOT NULL DEFAULT 0,
    started_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    finished_at   DATETIME     NULL,
    CONSTRAINT fk_ls_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 7. TRYOUT
-- ----------------------------------------------------------------
CREATE TABLE tryouts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(255)  NOT NULL,
    description     TEXT          NULL,
    duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    total_questions SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    passing_score   TINYINT UNSIGNED  NOT NULL DEFAULT 60,
    is_premium      TINYINT(1)        NOT NULL DEFAULT 0,
    created_by      INT UNSIGNED      NOT NULL,
    created_at      DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_tryout_user
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 8. TRYOUT ↔ QUESTIONS (pivot)
-- ----------------------------------------------------------------
CREATE TABLE tryout_questions (
    tryout_id   INT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (tryout_id, question_id),
    CONSTRAINT fk_tq_tryout   FOREIGN KEY (tryout_id)   REFERENCES tryouts(id)   ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_tq_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 9. TRYOUT SESSIONS (user attempts)
-- ----------------------------------------------------------------
CREATE TABLE tryout_sessions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    tryout_id   INT UNSIGNED NOT NULL,
    score       TINYINT UNSIGNED  NOT NULL DEFAULT 0,
    is_passed   TINYINT(1)        NOT NULL DEFAULT 0,
    started_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    finished_at DATETIME     NULL,
    CONSTRAINT fk_ts_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ts_tryout FOREIGN KEY (tryout_id) REFERENCES tryouts(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 10. EBOOKS
-- ----------------------------------------------------------------
CREATE TABLE ebooks (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(255) NOT NULL,
    description     TEXT         NULL,
    category        VARCHAR(100) NULL,
    file_path       VARCHAR(500) NULL,
    cover_color     VARCHAR(100) NULL,
    cover_color_hex VARCHAR(7)   NULL,
    pages           SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_premium      TINYINT(1)        NOT NULL DEFAULT 0,
    created_by      INT UNSIGNED      NOT NULL,
    created_at      DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ebook_user
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 11. FORUM POSTS
-- ----------------------------------------------------------------
CREATE TABLE forum_posts (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    title      VARCHAR(255) NOT NULL,
    content    TEXT         NOT NULL,
    category   VARCHAR(100) NULL,
    upvotes    INT UNSIGNED NOT NULL DEFAULT 0,
    is_pinned  TINYINT(1)   NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_forum_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 12. FORUM REPLIES
-- ----------------------------------------------------------------
CREATE TABLE forum_replies (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id   INT UNSIGNED NOT NULL,
    user_id   INT UNSIGNED NOT NULL,
    content   TEXT         NOT NULL,
    upvotes   INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reply_post FOREIGN KEY (post_id) REFERENCES forum_posts(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reply_user FOREIGN KEY (user_id) REFERENCES users(id)       ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 13. SPEAKING SESSIONS
-- ----------------------------------------------------------------
CREATE TABLE speaking_sessions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    topic       VARCHAR(255) NULL,
    audio_path  VARCHAR(500) NULL,
    score       TINYINT UNSIGNED  NULL,
    feedback    TEXT              NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_speak_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 14. ADMIN ACTIVITY LOGS
-- ----------------------------------------------------------------
CREATE TABLE admin_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id    INT UNSIGNED NOT NULL,
    action      VARCHAR(100) NOT NULL,
    target_type VARCHAR(100) NULL,
    target_id   INT UNSIGNED NULL,
    detail      TEXT         NULL,
    ip_address  VARCHAR(45)  NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_admin FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================================
-- SEED DATA
-- ================================================================

-- Default admin account (password: Admin@12345)
INSERT INTO users (name, email, password, role, plan, xp) VALUES
('Admin EngLight', 'admin@englight.id',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uFutrnIdC',
 'admin', 'pro', 9999);

-- Default demo user (password: User@12345)
INSERT INTO users (name, email, password, role, plan, xp) VALUES
('Budi Santoso', 'budi@student.com',
 '$2y$12$Lj6C/bfQJm.L0VBW97yb7OB2A78lBYOiAqNAFm3e0g1.V.a2xCe2W',
 'user', 'free', 850);
