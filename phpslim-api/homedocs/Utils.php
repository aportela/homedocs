<?php

declare(strict_types=1);

namespace HomeDocs;

class Utils
{
    /**
     * @return array<mixed>
     */
    public static function getInitialState(\Psr\Container\ContainerInterface $container): array
    {
        $settings = $container->get('settings');
        return (
            [
                'allowSignUp' => $settings['common']['allowSignUp'],
                'defaultResultsPage' => $settings['common']['defaultResultsPage'],
                'environment' => $settings['environment'],
                'maxUploadFileSize' => self::getMaxUploadFileSize(),
                'session' => [
                    'logged' => \HomeDocs\UserSession::isLogged(),
                    'id' => \HomeDocs\UserSession::getUserId(),
                    'email' => \HomeDocs\UserSession::getEmail()
                ]
            ]
        );
    }

    /**
     * by svogal
     * https://www.php.net/manual/es/function.mime-content-type.php#87856
     */
    public static function getMimeType(string $filename): string
    {
        $mime_types = [
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
        ];
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
    private static function parseIniSize(string $iniSize): float
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $iniSize); // Remove the non-unit characters from the size.
        $size = intval(preg_replace('/[^0-9\.]/', '', $iniSize)); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round(floatval($size));
        }
    }

    /**
     * https://stackoverflow.com/a/25370978
     * Returns a file size limit in bytes based on the PHP upload_max_filesize and post_max_size
     */
    public static function getMaxUploadFileSize(): float
    {
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

    /**
     * https://stackoverflow.com/a/31460273
     *
     * Return a UUID (version 4) using random bytes
     * Note that version 4 follows the format:
     *     xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
     * where y is one of: [8, 9, A, B]
     *
     * We use (random_bytes(1) & 0x0F) | 0x40 to force
     * the first character of hex value to always be 4
     * in the appropriate position.
     *
     * For 4: http://3v4l.org/q2JN9
     * For Y: http://3v4l.org/EsGSU
     * For the whole shebang: https://3v4l.org/LNgJb
     *
     * @ref https://stackoverflow.com/a/31460273/2224584
     * @ref https://paragonie.com/b/JvICXzh_jhLyt4y3
     *
     * @return string
     */
    public static function uuidv4(): string
    {
        return implode('-', [
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            bin2hex(chr((ord(random_bytes(1)) & 0x0F) | 0x40)) . bin2hex(random_bytes(1)),
            bin2hex(chr((ord(random_bytes(1)) & 0x3F) | 0x80)) . bin2hex(random_bytes(1)),
            bin2hex(random_bytes(6))
        ]);
    }

    /**
     * return (if found) matched fragment of string
     */
    public static function getStringFragment(string $text, string $search, int $maxFragmentLength, bool $ignoreCase): ?string
    {
        $pos = $ignoreCase ? mb_stripos($text, $search) : mb_strpos($text, $search);
        if ($pos !== false) {
            $text_len = mb_strlen($text);
            if ($text_len <= $maxFragmentLength) {
                return ($text);
            } else {
                $len =  $text_len - $pos;
                return ($pos > 0 ? "..." : "") . trim(mb_substr($text, $pos, $maxFragmentLength > $len ? $len : $maxFragmentLength)) . "...";
            }
        } else {
            return null;
        }
    }
}
