<?php
/**
 * Classe para gerenciar sessões e autenticação
 */

class Session {
    
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            $cookie_params = [
                'lifetime' => 0,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ];

            $host = $_SERVER['HTTP_HOST'] ?? '';
            if (preg_match('/(^|\.)mcmtocantins\.com\.br$/', explode(':', $host)[0]) === 1) {
                $cookie_params['domain'] = '.mcmtocantins.com.br';
            }

            session_set_cookie_params($cookie_params);

            // Usa o mesmo identificador de sessao entre modulos do portal.
            session_name('PORTALMCM_SESSION');

            ini_set('session.gc_maxlifetime', 86400);
            session_start();
        }
    }
    
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    public static function remove($key) {
        self::start();
        unset($_SESSION[$key]);
    }
    
    public static function destroy() {
        self::start();
        session_destroy();
        $_SESSION = [];
    }
    
    public static function isAuthenticated() {
        return self::has('user_id') && self::has('username');
    }
    
    public static function getUserId() {
        return self::get('user_id');
    }
    
    public static function getUsername() {
        return self::get('username');
    }
    
    public static function getEmail() {
        return self::get('email');
    }
    
    public static function getAccessLevel() {
        return self::get('access_level', 'viewer');
    }

    public static function getIdFuncao() {
        return self::get('id_funcao');
    }

    public static function getIdFilial() {
        return self::get('id_filial');
    }
}
?>
