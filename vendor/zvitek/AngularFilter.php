<?php
use Latte\Compiler;
/**
 * AngularFilter.php for options
 * @project    options
 * @date       29. 09. 2014
 * @author     Tomas Lauko <thommas2316@gmail.com>
 * @copyright  Don't Panic
 * @link       http://www.thommas2316.net
 */
class AngularFilter extends \Latte\Macros\MacroSet
{
	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('{', "echo '{{' . %node.word . '}}'");
	}
} 