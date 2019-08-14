<?php

    declare(strict_types=1);

    namespace HomeDocs;

    class Utils {
        public static function getInitialState(\Slim\Container $container) {
            return(
                array(
                    'allowSignUp' => $container->get('settings')['common']['allowSignUp'],
                    'defaultResultsPage' => $container->get('settings')['common']['defaultResultsPage'],
                    'productionEnvironment' => $container->get('settings')['twigParams']['production'],
                    'maxUploadFileSize' => self::getMaxUploadFileSize(),
                    'session' => array(
                        'logged' => \HomeDocs\UserSession::isLogged(),
                        'id' => \HomeDocs\UserSession::getUserId(),
                        'email' => \HomeDocs\UserSession::getEmail()
                    )
                )
            );
        }

        /**
         * by svogal
         * https://www.php.net/manual/es/function.mime-content-type.php#87856
         */
        public static function getMimeType($filename) {
            $mime_types = array(
                'txt' => 'text/plain',
                'htm' => 'text/html',
                'html' => 'text/html',
                'php' => 'text/html',
                'css' => 'text/css',
                'js' => 'application/javascript',
                'json' => 'application/json',
                'xml' => 'application/xml',
                'swf' => 'application/x-shockwave-flash',
                'flv' => 'video/x-flv',
                // images
                'png' => 'image/png',
                'jpe' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'ico' => 'image/vnd.microsoft.icon',
                'tiff' => 'image/tiff',
                'tif' => 'image/tiff',
                'svg' => 'image/svg+xml',
                'svgz' => 'image/svg+xml',
                // archives
                'zip' => 'application/zip',
                'rar' => 'application/x-rar-compressed',
                'exe' => 'application/x-msdownload',
                'msi' => 'application/x-msdownload',
                'cab' => 'application/vnd.ms-cab-compressed',
                // audio/video
                'mp3' => 'audio/mpeg',
                'qt' => 'video/quicktime',
                'mov' => 'video/quicktime',
                // adobe
                'pdf' => 'application/pdf',
                'psd' => 'image/vnd.adobe.photoshop',
                'ai' => 'application/postscript',
                'eps' => 'application/postscript',
                'ps' => 'application/postscript',
                // ms office
                'doc' => 'application/msword',
                'rtf' => 'application/rtf',
                'xls' => 'application/vnd.ms-excel',
                'ppt' => 'application/vnd.ms-powerpoint',
                // open office
                'odt' => 'application/vnd.oasis.opendocument.text',
                'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            );
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (array_key_exists($ext, $mime_types)) {
                return ($mime_types[$ext]);
            } else {
                return ('application/octet-stream');
            }
        }

        /**
         * https://stackoverflow.com/a/25370978
         */
        private static function parseIniSize($size) {
            $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
            $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
            if ($unit) {
                // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
                return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
            } else {
                return round($size);
            }
        }

        /**
         * https://stackoverflow.com/a/25370978
         * Returns a file size limit in bytes based on the PHP upload_max_filesize and post_max_size
         */
        public static function getMaxUploadFileSize() {
            static $max_size = -1;
            if ($max_size < 0) {
                // Start with post_max_size.
                $post_max_size = self::parseIniSize(ini_get('post_max_size'));
                if ($post_max_size > 0) {
                    $max_size = $post_max_size;
                }

                // If upload_max_size is less, then reduce. Except if upload_max_size is
                // zero, which indicates no limit.
                $upload_max = self::parseIniSize(ini_get('upload_max_filesize'));
                if ($upload_max > 0 && $upload_max < $max_size) {
                    $max_size = $upload_max;
                }
            }
            return $max_size;
        }

    }
?>