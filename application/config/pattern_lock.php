<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pattern Lock Configuration
 * Конфигурация системы паттерн-аутентификации
 */

// ==================== Основные настройки ====================

// Размер сетки (по умолчанию 3x3, максимум 20x20)
$config['pattern_grid_size'] = 3;

// Минимальное количество точек в паттерне
$config['pattern_min_dots'] = 4;

// Максимальное количество неудачных попыток перед блокировкой
$config['pattern_max_attempts'] = 5;

// Время блокировки в минутах
$config['pattern_lockout_time'] = 15;

// ==================== Безопасность ====================

// Алгоритм шифрования (sha256, sha512)
$config['pattern_encryption'] = 'sha256';

// Соль для шифрования (ОБЯЗАТЕЛЬНО ИЗМЕНИТЕ!)
$config['pattern_salt'] = 'your_unique_salt_key_change_this_2024';

// Включить тотальную блокировку системы
$config['pattern_total_lockdown'] = FALSE;

// Кастомный URL для входа
$config['pattern_custom_login_url'] = '';

// Разрешить fallback на обычный пароль
$config['pattern_allow_fallback'] = TRUE;

// ==================== Логирование ====================

// Включить логирование
$config['pattern_logging_enabled'] = TRUE;

// Уровень логирования: 'basic', 'detailed', 'debug'
$config['pattern_log_level'] = 'detailed';

// Время жизни логов в днях
$config['pattern_log_retention_days'] = 30;

// Логировать успешные входы
$config['pattern_log_success'] = TRUE;

// Логировать неудачные попытки
$config['pattern_log_failures'] = TRUE;

// Путь к файлу логов
$config['pattern_log_file'] = 'pattern_auth.log';

// ==================== reCAPTCHA ====================

// Включить reCAPTCHA
$config['pattern_captcha_enabled'] = TRUE;

// Показывать капчу после N неудачных попыток (0 = всегда)
$config['pattern_captcha_threshold'] = 3;

// Версия reCAPTCHA: 'v2', 'v3', 'invisible'
$config['pattern_captcha_version'] = 'v2';

// Минимальный score для reCAPTCHA v3 (0.0 - 1.0)
$config['pattern_captcha_v3_threshold'] = 0.5;

// ==================== Уведомления ====================

// Отправлять email при блокировке
$config['pattern_notify_on_lockout'] = TRUE;

// Email администратора
$config['pattern_admin_email'] = 'admin@example.com';

// ==================== Сессия ====================

// Имя сессионной переменной
$config['pattern_session_key'] = 'pattern_auth';

// Время жизни сессии в минутах
$config['pattern_session_lifetime'] = 120;