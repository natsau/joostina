<?php

/**
 * @package Joostina
 * @copyright Авторские права (C) 2007-2010 Joostina team. Все права защищены.
 * @license Лицензия http://www.gnu.org/licenses/gpl-2.0.htm GNU/GPL, или help/license.php
 * Joostina! - свободное программное обеспечение распространяемое по условиям лицензии GNU/GPL
 * Для получения информации о используемых расширениях и замечаний об авторском праве, смотрите файл help/copyright.php.
 */
// запрет прямого доступа
defined('_JOOS_CORE') or die();

echo sprintf('<div class="page"><h1>%s</h1></div>', $page->title);
echo sprintf('<div class="pc">%s</div>', $page->text);

joosLoader::model('tags');
$tags = new Tags;
echo $tags->show_tags($page);

joosLoader::model('comments');
$comments = new Comments;
echo '<div class="comments">' . $comments->load_comments_tree($page) . '</div>';