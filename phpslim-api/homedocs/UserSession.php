<?php

    declare(strict_types=1);

    namespace HomeDocs;

    class UserSession {

        public static function set($userId = "", string $email = "") {
            $_SESSION["userId"] = $userId;
            $_SESSION["email"] = $email;
        }

        public static function clear() {
            $_SESSION = array();
            if (ini_get("session.use_cookies")) {
                if (PHP_SAPI != 'cli') {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
                }
            }
            if (session_status() != PHP_SESSION_NONE) {
                session_destroy();
            }
        }

        public static function isLogged() {
            return(isset($_SESSION["userId"]));
        }

        public static function getUserId() {
            return(isset($_SESSION["userId"]) ? $_SESSION["userId"]: null);
        }

        public static function getEmail() {
            return(isset($_SESSION["email"]) ? $_SESSION["email"]: null);
        }

    }

?>