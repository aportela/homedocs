<?php

    declare(strict_types=1);

    namespace HomeDocs\Test;

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    class BaseTest extends \PHPUnit\Framework\TestCase {
        static public $app = null;
        static public $container = null;
        static public $dbh = null;

        /**
         * Called once just like normal constructor
         */
        public static function setUpBeforeClass () {
            self::$app = (new \HomeDocs\App())->get();
            self::$container = self::$app->getContainer();
            self::$dbh = new \HomeDocs\Database\DB(self::$container);
        }

        /**
         * Initialize the test case
         * Called for every defined test
         */
        public function setUp() {
            self::$dbh->beginTransaction();
        }

        /**
         * Clean up the test case, called for every defined test
         */
        public function tearDown() {
            self::$dbh->rollBack();
        }

        /**
         * Clean up the whole test class
         */
        public static function tearDownAfterClass() {
            self::$dbh = null;
            self::$container = null;
            self::$app = null;
        }

        public function test(): void {
            $this->assertTrue(true);
        }

    }

?>