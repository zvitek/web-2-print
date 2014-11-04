<?php
namespace zvitek;

use zvitek;

class Helper
{

	public static function verifyIC($ic)
	{
		$ic = preg_replace('#\s+#', '', $ic);

		if(!preg_match('#^\d{8}$#', $ic))
		{
			return FALSE;
		}

		$a = 0;
		for($i = 0; $i < 7; $i++)
		{
			$a += $ic[$i] * (8 - $i);
		}

		$a = $a % 11;

		if($a === 0) $c = 1;
		elseif($a === 10) $c = 1;
		elseif($a === 1) $c = 0;
		else $c = 11 - $a;

		return (int)$ic[7] === $c;
	}

	public static function verifyDIC($dic)
	{
		$first = substr($dic, 0, 2);
		$dic = str_replace($first, '', $dic);

		return self::verifyIC($dic);
	}

	public static function pick($string, $value = NULL)
	{
		return $string ? $string : $value;
	}

	public static function createMailTemplate($path)
	{
		$template = new \Nette\Templating\FileTemplate($path);
		$template->registerFilter(new \Latte\Engine);
		$template->registerHelperLoader('Nette\Templating\Helpers::loader');

		return $template;
	}

	public static function array_clear($array)
	{
		$data = array();

		foreach($array as $a)
		{
			$data[] = $a;
		}

		return $data;
	}

	public static function clearEmptyArray($data)
	{
		$sendBack = array();
		foreach($data as $key => $value)
		{
			if($value)
				$sendBack[$key] = $data[$key];
		}
		return $sendBack;
	}

	/**
	 * @param $path
	 * @param $template
	 * @param $type
	 * @return string
	 * @throws
	 */
	public static function control_template($path, $template, $type = '')
	{
		$path = $path . '/templates/';
		$extension = '.latte';
		$file = $template;
		$type = '-' . ucfirst($type);

		if(file_exists($path . $file . $type . $extension))
			return $path . $file . $type . $extension;

		if(!file_exists($path . $file . $extension))
			throw new \Nette\FileNotFoundException('Control layout not found:' . $path . $file . $extension);

		return $path . $file . $extension;
	}
} 