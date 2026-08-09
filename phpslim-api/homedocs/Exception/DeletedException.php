<?php

declare(strict_types=1);

namespace HomeDocs\Exception;

/**
 * "deleted element" custom exception (operation fails due element has been deleted and can not be accessed)
 */
class DeletedException extends \Exception {}
