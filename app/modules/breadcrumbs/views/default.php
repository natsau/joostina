<?php
/**
 * Breadcrumbs - модуль вывода "хлебных крошек"
 * Шаблон вывода
 *
 * @version    1.0
 * @package    Joostina CMS
 * @subpackage modelModules
 * @author     JoostinaTeam
 * @copyright  (C) 2007-2012 Joostina Team
 * @license    see license.txt
 *
 * */
//Запрет прямого доступа
defined( '_JOOS_CORE' ) or exit;

array_unshift( $items , '<a href="' . JPATH_SITE . '">Главная</a>' );
$last = count( $items ) - 1;
?>
<div class="path">
    <?php foreach ( $items as $key => $item ): ?>
    <?php echo $item; ?>
    <?php echo $key == $last ? '' : ' / ' ?>
    <?php endforeach; ?>
</div>
