<?php
/**
 * ajaxFrontend - точка входа в приложение для ajax запросов
 *
 * @package   Core
 * @author    JoostinaTeam
 * @copyright (C) 2007-2012 Joostina Team
 * @license   MIT License http://www.opensource.org/licenses/mit-license.php
 * Информация об авторах и лицензиях стороннего кода в составе Joostina CMS: docs/copyrights
 */
// Установка флага родительского файла
define('_JOOS_CORE', 1);

require_once __DIR__ . '/core/joostina.php';

// заполняем некоторые полезные переменные
joosController::$controller = joosRequest::param('option');
joosController::$action = joosRequest::param('task', 'index');

try {

    // запускаем аяксовый контроллер
    joosController::ajax_run();
} catch (Exception $e) {

    echo $e;
}
