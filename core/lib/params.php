<?php

/**
 * Class joosParams
 * @package    joosParams
 * @subpackage    Joostina CMS
 * @created    2011-02-03 21:37:41
 */
class joosParams extends joosDBModel {

	/**
	 * @var int(11)
	 */
	public $id;
	/**
	 * @var varchar(255)
	 */
	public $group;
	/**
	 * @var varchar(255)
	 */
	public $subgroup;
	/**
	 * @var varchar(20)
	 */
	public $object;
	/**
	 * @var text
	 */
	public $data;
	private static $_params;

	/*
	 * Constructor
	 */
	function __construct() {
		$this->joosDBModel('#__params', 'id');
	}

	public function check() {
		$this->filter();
		return true;
	}

	public function before_insert() {
		return true;
	}

	public function after_insert() {
		return true;
	}

	public function before_update() {
		return true;
	}

	public function after_update() {
		return true;
	}

	public function before_store() {
		return true;
	}

	public function after_store() {
		return true;
	}

	public function before_delete() {
		return true;
	}

	public function get_fieldinfo() {
		return array(
			'id' => array(
				'name' => 'id',
				'editable' => true,
				'in_admintable' => true,
				'html_table_element' => 'value',
				'html_table_element_param' => array(),
				'html_edit_element' => 'hidden',
				'html_edit_element_param' => array(),
			),
			'group' => array(
				'name' => 'group',
				'editable' => true,
				'in_admintable' => true,
				'html_table_element' => 'value',
				'html_table_element_param' => array(),
				'html_edit_element' => 'hidden',
				'html_edit_element_param' => array(),
			),
			'subgroup' => array(
				'name' => 'subgroup',
				'editable' => true,
				'in_admintable' => true,
				'html_table_element' => 'value',
				'html_table_element_param' => array(),
				'html_edit_element' => 'hidden',
				'html_edit_element_param' => array(),
			),
			'object' => array(
				'name' => 'object',
				'editable' => true,
				'in_admintable' => true,
				'html_table_element' => 'value',
				'html_table_element_param' => array(),
				'html_edit_element' => 'hidden',
				'html_edit_element_param' => array(),
			),
			'data' => array(
				'name' => 'data',
				'editable' => true,
				'in_admintable' => true,
				'html_table_element' => 'value',
				'html_table_element_param' => array(),
				'html_edit_element' => 'json',
				'html_edit_element_param' => array(
					'call_from' => 'joosParams::get_defaults'
				),
			),
		);
	}

	public function get_tableinfo() {
		return array(
			'header_list' => 'Параметры',
			'header_new' => 'Создание параметров',
			'header_edit' => 'Редактирование параметров'
		);
	}

	public function get_extrainfo() {
		return array(
			'search' => array(),
			'filter' => array(),
			'extrafilter' => array()
		);
	}

	public static function get_defaults($item) {

		$file = JPATH_BASE . DS . 'app' . DS . 'components' . DS . $item->group . DS . 'params.' . $item->group . '.php';

		$model = 'params' . ucfirst($item->group);


		if (is_file($file)) {
			require_once($file);
			return $model::get_params_scheme('default');
		} else {
			return false;
		}
	}

	public static function get_scheme($item) {

		$group = isset($item->params_group) ? $item->params_group : joosRequest::request('option');

		$file = 'app' . DS . 'components' . DS . $group . DS . 'params.' . $group . '.php';
		$file = JPATH_BASE . DS . $file;

		$model = 'params' . ucfirst($group);

		if (is_file($file)) {
			require_once($file);

			$params = array(
				'notdefault' => array(
					'name' => '–°–њ–µ—Ж–Є–∞–ї—М–љ—Л–µ –љ–∞—Б—В—А–Њ–є–Ї–Є',
					'editable' => true,
					'html_edit_element' => 'checkbox',
					'html_edit_element_param' => array(
						'text' => '–°–њ–µ—Ж–Є–∞–ї—М–љ—Л–µ –љ–∞—Б—В—А–Њ–є–Ї–Є',
					)
				)
			);

			$add_params = $model::get_params_scheme($item->params['subgroup']);
			if ($add_params) {
				$params += $model::get_params_scheme($item->params['subgroup']);
				return $params;
			}

			return false;
		} else {
			return false;
		}
	}

	public static function get_params($group, $subgroup, $object = '') {

		$params = new self;

		$params->group = self::$_params['group'] = $group;
		$params->subgroup = self::$_params['subgroup'] = $subgroup;
		$params->object = self::$_params['object'] = $object;


		if ($params->find()) {
			self::$_params['notdefault'] = 1;
			self::$_params += json_decode($params->data, true);
		}

		return self::$_params;
	}

	public function load_params($group, $subgroup, $object = '') {

		$this->group = self::$_params['group'] = $group;
		$this->subgroup = self::$_params['subgroup'] = $subgroup;
		$this->object = self::$_params['object'] = $object;

		if ($this->find()) {
			self::$_params['notdefault'] = 1;
			self::$_params += json_decode($this->data, true);
		}
	}

	public function save_params(array $params, $group, $subgroup, $object = '') {

		$this->group = $group;
		$this->subgroup = $subgroup;
		$this->object = $object;

		$this->find();

		if ($_POST['params']['notdefault'] == 1) {
			array_shift($params);
			$this->data = json_encode($params);
			$this->store();
		} else {
			$this->delete($this->id);
		}
	}

	public function get($key, $default = false) {
		return isset(self::$_params[$key]) ? self::$_params[$key] : $default;
	}

}
