<?php defined('_JOOS_CORE') or exit;

/**
 * Работа с текстом
 *
 * @version    1.0
 * @package    Core\Libraries
 * @subpackage Text
 * @category   Libraries
 * @author     Joostina Team <info@joostina.ru>
 * @copyright  (C) 2007-2012 Joostina Team
 * @license    MIT License http://www.opensource.org/licenses/mit-license.php
 * Информация об авторах и лицензиях стороннего кода в составе Joostina CMS: docs/copyrights
 *
 * */
class joosText
{
    /**
     * Символы русского алфавита
     *
     * @var array
     */
    public static $abc_ru = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Э', 'Ю', 'Я');

    /**
     * Символы английского алфавита
     *
     * @var array
     */
    public static $abc_en = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

    /**
     * Вывод численных результатов с учетом склонения слов
     * @access public
     *
     * @param integer $int
     * @param array   $expressions Например: array("ответ", "ответа", "ответов")
     */
    public static function declension($int, $expressions)
    {
        if (count($expressions) < 3) {
            $expressions[2] = $expressions[1];
        }

        settype($int, 'integer');
        $count = $int % 100;
        if ($count >= 5 && $count <= 20) {
            $result = $expressions['2'];
        } else {
            $count = $count % 10;
            if ($count == 1) {
                $result = $expressions['0'];
            } elseif ($count >= 2 && $count <= 4) {
                $result = $expressions['1'];
            } else {
                $result = $expressions['2'];
            }
        }

        return $result;
    }

    /**
     * Ограничение длины текста по числу слов
     *
     * @param string $str      исходная строка
     * @param int    $limit    число слов от начала строки, которое необходимо оставить
     * @param string $end_char строка которую необходимо добавить в конец обрезанного текста
     *
     * @return string обработанная строка
     */
    public static function word_limiter($str, $limit = 100, $end_char = '&#8230;')
    {
        if (joosString::trim($str) == '') {
            return $str;
        }

        preg_match('/^\s*+(?:\S++\s*+){1,' . (int) $limit . '}/u', $str, $matches);

        $end_char = (joosString::strlen($str) == joosString::strlen($matches[0])) ? '' : $end_char;

        return joosString::rtrim($matches[0]) . $end_char;
    }

    /**
     * Ограничение текста по числу символов
     *
     * @param string $str            исходная строка
     * @param int    $limit          число символов от начала строки, которо енеобходимо оставить
     * @param string $end_char       трока которую необходимо добавить в конец обрезанного текста
     * @param int    $max_word_lench максимальное число символов одного слова
     *
     * @return string обработанная строка
     */
    public static function character_limiter($str, $limit = 500, $end_char = '&#8230;', $max_word_lench = 500)
    {
        if (joosString::strlen($str) < $limit) {
            return $str;
        }

        $str = preg_replace("/\s+/u", ' ', str_replace(array("\r\n", "\r", "\n"), ' ', $str));

        if (joosString::strlen($str) <= $limit) {
            return $str;
        }

        $out = "";
        foreach (explode(' ', joosString::trim($str)) as $val) {
            if (joosString::strlen($val) > $max_word_lench) {
                $val = joosString::substr($val, 0, $max_word_lench) . $end_char;
            }
            $out .= $val . ' ';

            if (joosString::strlen($out) >= $limit) {
                $out = joosString::trim($out);

                return (joosString::strlen($out) == joosString::strlen($str)) ? $out : $out . $end_char;
            }
        }

        return joosString::substr($str, 0, $limit) . $end_char;
    }

    /**
     * Цензор текста, заменяет в тексте указанные слова
     *
     * @param string $str         исходная строка
     * @param array  $censored    массив слов для замены
     * @param string $replacement текст, который будет выводиться в качестве замены
     *
     * @return string обработанный текст
     */
    public static function text_censor($str, array $censored, $replacement = '')
    {
        $delim = '[-_\'\"`(){}<>\[\]|!?@#%&,.:;^~*+=\/ 0-9\n\r\t]';

        foreach ($censored as $badword) {
            if ($replacement != '') {
                $str = preg_replace("/({$delim})(" . str_replace('\*', '\w*?', preg_quote($badword, '/')) . ")({$delim})/iu", "\\1{$replacement}\\3", $str);
            } else {
                $str = preg_replace("/({$delim})(" . str_replace('\*', '\w*?', preg_quote($badword, '/')) . ")({$delim})/ieu", "'\\1'.str_repeat('#', strlen('\\2')).'\\3'", $str);
            }
        }

        return joosString::trim($str);
    }

    /**
     * Базовая очистка текста от тэгов создаваемых редактором MS Word
     *
     * @param string $text исходная строка
     *
     * @return string очищенная от тэгов строка
     */
    public static function text_msword_clean($text)
    {
        $text = str_replace("&nbsp;", "", $text);
        $text = str_replace("</html>", "", $text);
        $text = preg_replace("/FONT-SIZE: [0-9]+pt;/miu", "", $text);

        return preg_replace("/([ \f\r\t\n\'\"])on[a-z]+=[^>]+/iu", "\\1", $text);
    }

    /**
     * Семантическая замена тэгов на более правильные аналоги
     *
     * @param string $text исходная строка
     *
     * @return string строка с исправленными тэгами
     */
    public static function semantic_replacer($text)
    {
        $text = preg_replace("!<b>(.*?)</b>!si", "<strong>\\1</strong>", $text);
        $text = preg_replace("!<i>(.*?)</i>!si", "<em>\\1</em>", $text);
        $text = preg_replace("!<u>(.*?)</u>!si", "<strike>\\1</strike>", $text);

        return str_replace("<br>", "<br />", $text);
    }

    /**
     * Базовая очистка текста от тэгов
     *
     * @param string $text исходная строка текста для очистки
     *
     * @return string очищенная строка
     */
    public static function simple_clean($text)
    {
        $text = html_entity_decode($text, ENT_QUOTES, 'utf-8');

        return self::text_clean($text);
    }

    /**
     * Очистка текста от HTML тэгов
     *
     * @param string $text исходная строка для очистки
     *
     * @return string очищенная от тэгов строка
     */
    public static function text_clean($text)
    {
        $text = preg_replace("'<script[^>]*>.*?</script>'si", '', $text);
        $text = preg_replace('/<!--.+?-->/', '', $text);
        $text = preg_replace('/{.+?}/', '', $text);
        $text = preg_replace('/&nbsp;/', ' ', $text);
        $text = preg_replace('/&amp;/', ' ', $text);
        $text = preg_replace('/&quot;/', ' ', $text);
        $text = strip_tags($text);

        return htmlspecialchars($text, null, 'UTF-8');
    }

    /**
     * Функция работы с внешними ссылками.
     * Через функцию надо пропустить обрабатываемые текст, и все ссылки в нём заменятся на внутренние с редиректом на оригинальные.
     * Базирутеся на примерах описанных в http://www.ewgenij.net/php-outlinks.html
     * Функция заменятет внешние ссылки в тексте на "внутренние"
     * Автор: Гринкевич Евгений Вадимович
     * http://www.ewgenij.net/
     *
     * @param string $text исходный текст для обработки
     *
     * @return string текст,  в котором все внешние ссылки заменены на редирект через внутренние
     */
    public static function outlink_parse($text)
    {
        $host = str_replace(array('http://', 'www.'), '', JPATH_SITE);
        $host = str_replace('.', '\.', $host);

        // TODO сюда можно добавить base64 кодирование внешней ссылки, а out.php - дэкодирование, тогда пользователь не будет знать куда попадёт и в тексте не будет фигурировать сама ссылка
        return preg_replace('/href="?(http:\/\/(?!(www\.|)' . $host . ')([^">\s]*))/ie', "'href=\"" . JPATH_SITE . "/out.php?url=' . urlencode('\$1') . '\"'", $text);
    }

    /**
     * Транслитерация для русского текста
     *  на основе http://htmlweb.ru/php/example/translit.php
     *
     * @param string $string исходная строка
     *
     * @return string строка, обработанная по правилам транслитерации
     */
    public static function russian_transliterate($string)
    {
        $converter = array('а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ь' => '\'', 'ы' => 'y', 'ъ' => '\'', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ь' => '\'', 'Ы' => 'Y', 'Ъ' => '\'', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya');

        return strtr($string, $converter);
    }

    /**
     * Преобразование строки в URL-безопасный вариант
     *
     * @tutorial joosText::text_to_url( 'Compellingly strategize B2B portals after empowered.' ); => compellingly-strategize-b2b-portals-after-empowered
     * @tutorial joosText::text_to_url( 'Гендер теоретически возможен!' ); => gender-teoreticheski-vozmozhen
     * @tutorial joosText::text_to_url( 'Разного рода символы !%#$&^&*^%*&)()(*_)*--  <> -' ); => raznogo-roda-simvoly
     *
     * @param  string $str исходная строка для обработки
     * @return string обработанная и готовая для формирования ссылки строка
     */
    public static function text_to_url($str)
    {
        // убираем непроизносимые
        $str = str_ireplace(array('ь', 'ъ'), '', $str);
        // переводим в транслит
        $str = self::russian_transliterate($str);
        // в нижний регистр
        $str = strtolower($str);
        // заменям все ненужное нам на " "
        $str = str_replace(array("'", '-', '"', '`'), ' ', $str);
        $str = preg_replace('/[^a-z0-9\-]+/', '-', $str);

        return trim($str, '-');
    }

    /**
     * Обрезание длиииинных слоооооооооооооооооов через мягкие переносы
     *
     * @param string $test       строка для обрезки
     * @param int    $max_length максимальная длина слова
     *
     * @return string обрезанная строка
     */
    public static function text_wrap($text, $max_length = 30)
    {
        $counter = 0;
        $newText = array();
        $array = array();

        $textLength = joosString::strlen($text);

        for ($i = 0; $i <= $textLength; $i++) {
            $array[] = joosString::substr($text, $i, 1);
        }

        $textLength = count($array);

        for ($x = 0; $x < $textLength; $x++) {
            if (preg_match("/[[:space:]]/u", $array[$x])) {
                $counter = 0;
            } else {
                $counter++;
            }

            $newText[] = $array[$x];

            if ($counter >= $max_length) {
                $newText[] = '<wbr style="display: inline-block"/>&shy;';
                $counter = 0;
            }
        }

        return implode('', $newText);
    }

    /**
     * Преобразование текстовой строки к каноничному виду
     *
     * @param string $text исходная строка
     *
     * @return string
     */
    public static function to_canonical($text)
    {
        // приводим к единому нижнему регистру
        $text = joosString:: strtolower($text);

        // убираем спецсимволы
        $to_del = array('~', '@', '#', '$', '%', '^', '&amp;', '*', '(', ')', '-', '_', '+', '=', '|', '?', ',', '.', '/', ';', ':', '"', "'", '№', ' ', '&nbsp;');
        $text = str_replace($to_del, '', $text);

        // приводим одинаковое начертание к единому тексту
        $a = array('о', 'o', 'l', 'L', '|', '!', 'i', 'х', 's', 'а', 'р', 'с', 'в', 'к', 'е', 'й', 'ё', 'ш', 'з', 'у', 'т', 'д', 'd', 'ф', 'в', 'м', 'н', 'и', 'э', 'ь', 'ъ', 'ю');
        $b = array('0', '0', '1', '1', '1', '1', '1', 'x', '$', '0', 'p', '$', 'b', 'k', 'e', 'и', 'е', 'щ', '$', 'y', 't', 't', 't', 'b', 'b', 'm', 'h', 'e', 'e', '', '', 'u');
        $text = str_replace($a, $b, $text);

        // убираем дуУубли символов
        $return = $o = '';
        $_l = joosString::strlen($text);
        for ($i = 0; $i < $_l; $i++) {
            $c = joosString::substr($text, $i, 1);
            if ($c != $o) {
                $return .= $c;
                $o = $c;
            }
        }

        return $return;
    }

    /**
     * Кодировкищик, позволяющий хранить 8 492 487 570 записей всего в 6 символах.
     *
     * @param type $string
     *
     * @todo тут можно/лучше использовать base_convert
     * @return type
     */
    public function id_decode($string)
    {
        $chars = '23456789abcdeghkmnpqsuvxyzABCDEGHKLMNPQSUVXYZ'; // Используем непохожие друг на друга символы
        $length = 45; //strlen($chars); // если изменяем набор символов, то число нужно изменить
        $size = strlen($string) - 1;
        $array = str_split($string);
        $id = strpos($chars, array_pop($array));
        foreach ($array as $i => $char) {
            $id += strpos($chars, $char) * pow($length, $size - $i);
        }

        return $id;
    }

    /**
     * Вывод нуждной формы слова, в зависимости от пола
     *
     *
     * @tutorial joosText::sexerate( 1, array('умник','умница','умницо') );
     * @tutorial joosText::sexerate( 'm', array('делал','делала','делало') )
     *
     * @param  string $sex   - пол, принимает варианты 1/2, м/ж, m/f. Если вариан  отсутствует - то используется 2й элементы неопределённого пола
     * @param  array  $texts - 3х элементый массив слов для каждого пола 0-мужской, 1-женский, 2
     * @return string
     */
    public static function sexerate($sex, array $texts)
    {
        $sex = joosString::strtolower($sex);
        $sex = strtr($sex, array('м' => 0, 'ж' => 1, 'm' => 0, 'f' => 1, 'муж' => 0, 'жен' => 1, 'male' => 0, 'female' => 1, 'мужчина' => 0, 'женщина' => 1,));

        return isset($texts[$sex]) ? $texts[$sex] : $texts[2];
    }

    /**
     * Конвертер в JSON с подержкой прямого вывода русских символов
     *
     * @tutorial  joosText::json_encode( array(1=>'Один',2=>'Два') ); => {"1":"Один","2":"Два"}
     *
     * @param  mixed              $value любой тип переменной
     * @return string|json_string
     */
    public static function json_encode($value)
    {
        $arr_replace_utf = array('\u0410', '\u0430', '\u0411', '\u0431', '\u0412', '\u0432', '\u0413', '\u0433', '\u0414', '\u0434', '\u0415', '\u0435', '\u0401', '\u0451', '\u0416', '\u0436', '\u0417', '\u0437', '\u0418', '\u0438', '\u0419', '\u0439', '\u041a', '\u043a', '\u041b', '\u043b', '\u041c', '\u043c', '\u041d', '\u043d', '\u041e', '\u043e', '\u041f', '\u043f', '\u0420', '\u0440', '\u0421', '\u0441', '\u0422', '\u0442', '\u0423', '\u0443', '\u0424', '\u0444', '\u0425', '\u0445', '\u0426', '\u0446', '\u0427', '\u0447', '\u0428', '\u0448', '\u0429', '\u0449', '\u042a', '\u044a', '\u042b', '\u044b', '\u042c', '\u044c', '\u042d', '\u044d', '\u042e', '\u044e', '\u042f', '\u044f');
        $arr_replace_cyr = array('А', 'а', 'Б', 'б', 'В', 'в', 'Г', 'г', 'Д', 'д', 'Е', 'е', 'Ё', 'ё', 'Ж', 'ж', 'З', 'з', 'И', 'и', 'Й', 'й', 'К', 'к', 'Л', 'л', 'М', 'м', 'Н', 'н', 'О', 'о', 'П', 'п', 'Р', 'р', 'С', 'с', 'Т', 'т', 'У', 'у', 'Ф', 'ф', 'Х', 'х', 'Ц', 'ц', 'Ч', 'ч', 'Ш', 'ш', 'Щ', 'щ', 'Ъ', 'ъ', 'Ы', 'ы', 'Ь', 'ь', 'Э', 'э', 'Ю', 'ю', 'Я', 'я');
        $str1 = json_encode($value);
        $str2 = str_replace($arr_replace_utf, $arr_replace_cyr, $str1);

        return $str2;
    }

}
