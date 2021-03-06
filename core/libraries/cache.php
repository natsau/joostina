<?php defined('_JOOS_CORE') or exit;

/**
 * Библиотека кеширования
 * Базируется на оригинальном класса библиотеки Flourish   http://flourishlib.com/fCache
 *
 * @version    1.0
 * @package    Core\Libraries
 * @subpackage Cache
 * @category   Libraries
 * @author     Joostina Team <info@joostina.ru>
 * @copyright  (C) 2007-2012 Joostina Team
 * @license    MIT License http://www.opensource.org/licenses/mit-license.php
 * Информация об авторах и лицензиях стороннего кода в составе Joostina CMS: docs/copyrights
 *
 * @todo добавить проверки использую библиотеки работы с файлами и каталогами
 * @todo добавить проверку что кеше включен, сейчас при выключенном кешировании объекты и проверки всё равно выполняются
 * */
class joosCache
{
    protected $cache;
    protected $data_store;
    protected $state;
    protected $type;

    private static $instance = array();

    /**
     * Получение инстанции объекта кеша
     *
     * @static
     * @param  bool      $type
     * @return joosCache
     */
    public static function instance($type = false)
    {
        if (!isset(self::$instance[$type]) || self::$instance[$type] === null) {
            self::$instance[$type] = new self($type);
        }

        return self::$instance[$type];
    }

    private function __construct($type = false, $data_store = false)
    {
        $type = $type ? $type : joosConfig::get2('cache', 'handler');

        switch ($type) {
            case 'file':

                $data_store = $data_store ? $data_store : joosConfig::get2('cache', 'cachepath');

                $exists = file_exists($data_store);
                if (!$exists && !is_writable(dirname($data_store))) {
                    throw new joosCacheException(sprintf('Каталог кеширования %s недоступен для записи', $data_store));
                }
                if ($exists && !is_writable($data_store)) {
                    throw new joosCacheException(sprintf('Файл кеша %s недоступен для записи', $data_store));
                }
                $this->data_store = $data_store;
                if ($exists) {
                    $this->cache = unserialize(file_get_contents($data_store));
                } else {
                    $this->cache = array();
                }
                $this->state = 'clean';
                break;

            case 'apc':
            case 'xcache':
            case 'memcache':
                if (!extension_loaded($type)) {
                    throw new joosCacheException(sprintf('Расширение кеширования %s недоступно либо не установлено', $type));
                }
                if ($type == 'memcache') {

                    if ($data_store == false) {
                        $data_store = new Memcache();
                        $data_store->connect(joosConfig::get2('cache', 'memcache_host'), joosConfig::get2('cache', 'memcache_port'));
                    }

                    if (!$data_store instanceof Memcache) {
                        throw new joosCacheException('Объект кеширования не является допустимым объектом Memcache');
                    }
                    $this->data_store = $data_store;
                }
                break;

            default:
                throw new joosCacheException(sprintf('Кеширующая система не поддерживает %s, разрешено лишь %s', $type, join(', ', array('apc', 'file', 'memcache', 'xcache'))));
        }

        $this->type = $type;
    }

    public function __destruct()
    {
        $this->save();
    }

    public function add($key, $value, $ttl = 0)
    {
        switch ($this->type) {
            case 'apc':
                return apc_add($key, serialize($value), $ttl);

            case 'file':
                if (isset($this->cache[$key]) && $this->cache[$key]['expire'] && $this->cache[$key]['expire'] >= time()) {
                    return FALSE;
                }
                $this->cache[$key] = array('value' => $value, 'expire' => (!$ttl) ? 0 : time() + $ttl);
                $this->state = 'dirty';

                return TRUE;

            case 'memcache':
                if ($ttl > 2592000) {
                    $ttl = time() + 2592000;
                }

                return $this->data_store->add($key, $value, 0, $ttl);
        }
    }

    public function clear()
    {
        switch ($this->type) {
            case 'apc':
                apc_clear_cache('user');

                return;

            case 'file':
                $this->cache = array();
                $this->state = 'dirty';

                return;

            case 'memcache':
                $this->data_store->flush();

                return;
        }
    }

    public function delete($key)
    {
        switch ($this->type) {
            case 'apc':
                apc_delete($key);

                return;

            case 'file':
                if (isset($this->cache[$key])) {
                    unset($this->cache[$key]);
                    $this->state = 'dirty';
                }

                return;

            case 'memcache':
                $this->data_store->delete($key);

                return;
        }
    }

    public function get($key, $default = NULL)
    {
        switch ($this->type) {
            case 'apc':
                $value = apc_fetch($key);
                if ($value === FALSE) {
                    return $default;
                }

                return unserialize($value);

            case 'file':
                if (isset($this->cache[$key])) {
                    $expire = $this->cache[$key]['expire'];
                    if (!$expire || $expire >= time()) {
                        return $this->cache[$key]['value'];
                    } elseif ($expire) {
                        unset($this->cache[$key]);
                        $this->state = 'dirty';
                    }
                }

                return $default;

            case 'memcache':
                $value = $this->data_store->get($key);
                if ($value === FALSE) {
                    return $default;
                }

                return $value;
        }
    }

    public function save()
    {
        if ($this->type != 'file') {
            return;
        }

        // Randomly clean the cache out
        if (rand(0, 99) == 50) {
            $clear_before = time();

            foreach ($this->cache as $key => $value) {
                if ($value['expire'] && $value['expire'] < $clear_before) {
                    unset($this->cache[$key]);
                    $this->state = 'dirty';
                }
            }
        }

        if ($this->state == 'clean') {
            return;
        }

        file_put_contents($this->data_store, serialize($this->cache));
        $this->state = 'clean';
    }

    public function set($key, $value, $ttl = 0)
    {
        //если кэш запрещен
        if (!joosConfig::get2('cache', 'enable')) {
            return;
        }

        switch ($this->type) {
            case 'apc':
                apc_store($key, serialize($value), $ttl);

                return;

            case 'file':
                $this->cache[$key] = array('value' => $value, 'expire' => (!$ttl) ? 0 : time() + $ttl);
                $this->state = 'dirty';

                return;

            case 'memcache':
                if ($ttl > 2592000) {
                    $ttl = time() + 2592000;
                }
                $this->data_store->set($key, $value, 0, $ttl);

                return;
        }
    }

}

/**
 * Обработка исключений и ошибок кеширования
 */
class joosCacheException extends joosException
{
}
