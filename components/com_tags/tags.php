<?php
/**
 * @package Joostina
 * @copyright Авторские права (C) 2008-2009 Joostina team. Все права защищены.
 * @license Лицензия http://www.gnu.org/licenses/gpl-2.0.htm GNU/GPL, или help/license.php
 * Joostina! - свободное программное обеспечение распространяемое по условиям лицензии GNU/GPL
 * Для получения информации о используемых расширениях и замечаний об авторском праве, смотрите файл help/copyright.php.
 */

// запрет прямого доступа
defined('_VALID_MOS') or die();

require_once ($mainframe->getPath('class'));
require_once ($mainframe->getPath('front_html'));

class actionsTags {

    /**
     * Вывод страниц тэгов
     * @param <type> $option
     * @param <type> $page
     * @param <type> $task
     * @param <type> $tag
     */
    public static function index($option, $page, $task, $tag ) {

        //$tag = (string) mosGetParam($_GET, 'tag', '');
        $tag = Jstring::clean( urldecode($tag) );

        mosMainFrame::getInstance()->setPageTitle( $tag );

        $tags = new Tags;
        $search_results_nodes_count = $tags->search_count($tag);

        // постраничная навигация
        mosMainFrame::addLib('paginator3000');
        $pager = new paginator3000( sefRelToAbs('index.php?option=com_tags&tag='.$tag, true) , $search_results_nodes_count, 10, 15 );
        $pager->paginate( $page );

        $tags_results = $tags->search($tag, '#__topics',$pager->offset, $pager->limit );

        tagsHTML::tag_search($tag, $tags_results, $pager);

        // считаем обращения к тэгу, но только на первой странице
        ($page == 0 OR $page==1) ?  Jhit::add('tags', 0, $tag ) : null;
    }

    /**
     * Формирование облака тэгов
     */
    public static function cloud() {

        $tags = new Tags;
        $tag_arr = $tags->load_all();

        $tags_cloud = new tagsCloud($tag_arr);
        $tags_cloud = $tags_cloud->get_cloud('', 400);

        tagsHTML::full_cloud($tags_cloud);
    }
}