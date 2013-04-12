<?php

class Text {

	protected static $accents = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç',
		'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ',
		'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å',
		'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô',
		'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą',
		'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē',
		'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ',
		'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į',
		'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ',
		'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō',
		'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś',
		'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ',
		'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ',
		'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ',
		'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ',
		'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');

	protected static $without_accent = array('A', 'A', 'A', 'A', 'A', 'A',
		'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O',
		'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a',
		'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n',
		'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a',
		'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd',
		'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g',
		'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i',
		'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l',
		'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n',
		'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R',
		'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T',
		't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W',
		'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o',
		'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u',
		'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');

	public static function purge($txt, $tolower = true) {
		$txt = str_replace(self::$accents, self::$without_accent, $txt);
		$txt = preg_replace(
			array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'),
			array('', '-', ''),
			$txt
		);
		if ($tolower) { $txt = strtolower($txt); }
		return $txt;
	}

	public static function randomKey($length = 8) {
		return substr(
			sha1(uniqid('', true).'_'.mt_rand().SALT),
			mt_rand(0, 40-$length),
			$length
		);
	}

	public static function check_language($language) {
		$languages = explode(',', LANGUAGES);
		return in_array($language, $languages);
	}

	public static function getHash($password) {
		global $config;
		return sha1($password.$config['salt']);
	}

	public static function timeDiff($time1, $time2) {
		$period = array(
			Trad::W_SECONDE,
			Trad::W_MINUTE,
			Trad::W_HOUR,
			Trad::W_DAY,
			Trad::W_WEEK,
			Trad::W_MONTH,
			Trad::W_YEAR,
			Trad::W_DECADE
		);
		$periods = array(
			Trad::W_SECONDE_P,
			Trad::W_MINUTE_P,
			Trad::W_HOUR_P,
			Trad::W_DAY_P,
			Trad::W_WEEK_P,
			Trad::W_MONTH_P,
			Trad::W_YEAR_P,
			Trad::W_DECADE_P
		);
		$lengths = array("60", "60", "24", "7", "4.35", "12", "10");
		$difference = abs($time1 - $time2);
		for ($j=0; $difference>=$lengths[$j] && $j<count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}
		$difference = round($difference);
		if ($difference != 1) {
			return array($difference, $periods[$j]);
		}
		return array($difference, $period[$j]);
	}
	public static function ago($time) {
		return str_replace(
			array('%duration%', '%pediod%'),
			self::timeDiff(time(), $time),
			Trad::S_AGO
		);
	}

	public static function intro($text, $length, $quote = true, $find = NULL) {
		$text = ' '.strip_tags($text).' ';
		$text = Text::remove_blanks($text, ' ');
		$text = str_replace('  ', ' ', $text);
		if ($find) {
			$start = strpos($text, $find);
			if ($start === false || $start-$length/2 < 0) { $start = 0; }
			else { $start = $start-$length/2; }
		}
		else {
			$start = 0;
		}
		$cuttext = substr($text, $start);
		if ($cuttext != $text && ($nb = strpos($cuttext, ' ')) !== false) {
			$subtext = Trad::W_SUSPENSION.ltrim(substr_replace(
				$cuttext,
				'',
				0,
				$nb+1
			));
		}
		else {
			$subtext = ltrim($cuttext);
		}
		$cuttext = substr($subtext, 0, $length);
		if ($cuttext != $subtext && ($nb = strrpos($cuttext, ' ')) !== false) {
			$subtext = rtrim(substr_replace(
				$cuttext,
				'',
				$nb
			)).Trad::W_SUSPENSION;
		}
		else {
			$subtext = rtrim($cuttext);
		}
		if ($quote) {
			return str_replace('%text%', $subtext, Trad::W_EXTRACT);
		}
		else { return $subtext; }
	}

	public static function options($arr, $sel) {
		$ret = '';
		foreach ($arr as $k => $v) {
			$data = '';
			if (preg_match('/%([a-z]+)%/', $v, $matches)) {
				$data = ' data-match="'.$matches[1].'"';
			}
			$v = preg_replace('/%[a-z]+%/', Trad::W_SUSPENSION, $v);
			$ret .= '<option value="'.$k.'"'.$data;
			if ($k == $sel) { $ret .= ' selected'; }
			$ret .= '>'.$v.'</option>';
		}
		return $ret;
	}

	public static function dir($name) {
		return preg_replace('#//$#', '/', dirname($name).'/');
	}

	public static function hash($object) {
		return PHPPREFIX.base64_encode(gzdeflate(serialize($object))).PHPSUFFIX;
	}
	public static function unhash($text) {
		return unserialize(gzinflate(base64_decode(substr(
			$text,
			strlen(PHPPREFIX),
			-strlen(PHPSUFFIX)
		))));
	}

	public static function remove_blanks($text, $replace = '') {
		return str_replace(array("\n", "\t"), $replace, $text);
	}

	public static function chars($string) {
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}

	public static function js_str($string) {
		return str_replace("'", "\\'", $string);
	}

	public static function click($fun, $params = array()) {
		$t = str_replace('"', '&quot;', json_encode($params));
		return 'class="a-'.$fun.'" '
			.'onclick="onclick_'.$fun.'(this, '.$t.');return false;"';
	}

}

?>