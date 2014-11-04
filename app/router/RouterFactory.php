<?php
namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\SimpleRouter;

class RouterFactory
{
	private $config;

	public function __construct()
	{
		$this->config = Nette\Environment::getContext()->parameters;
	}

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();

		$router[] = $calculatorRouter = new RouteList('User');
		$calculatorRouter[] = new Route('zakaznik/aktivace-profilu/<token [a-zA-Z0-9]{35}>', array('presenter' => 'User', 'action' => 'activateAccount'));
		$calculatorRouter[] = new Route('obnova-hesla/<token [a-zA-Z0-9]{35}>', array('presenter' => 'User', 'action' => 'revokePassword'));
		$calculatorRouter[] = new Route('login', array('presenter' => 'User', 'action' => 'login'));
		$calculatorRouter[] = new Route('logout', array('presenter' => 'User', 'action' => 'logout'));
		$calculatorRouter[] = new Route('zakaznik/<presenter>/<action>', array('presenter' => 'Page', 'action' => 'default'));

		$router[] = $calculatorRouter = new RouteList('System:Acl');
		$calculatorRouter[] = new Route('admin/<presenter>/<action>', array('presenter' => 'Users', 'action' => 'default'));

		$router[] = $calculatorRouter = new RouteList('Merchant');
		$calculatorRouter[] = new Route('obchodnik/<presenter>/<action>', array('presenter' => 'Page', 'action' => 'default'));

		$router[] = $calculatorRouter = new RouteList('Calculator');
		$calculatorRouter[] = new Route('<presenter>/<action>', array('presenter' => 'Page', 'action' => 'default'));

		return $router;
	}
}
