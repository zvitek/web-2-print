<?php

namespace App;

use Nette;
use App\Model;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var null|int  */
    protected $merchantID = NULL;

    /**
     * @inject
     * @var Model\Merchant\MerchantService
     */
    public $merchantService;

    /**
     * @inject
     * @var Model\Calculator\CalculatorService
     */
    public $calculatorService;

	/**
	 * @inject
	 * @var Model\User\UserService
	 */
	public $userService;

    /** @var array */
    public $config;

    public function injectConfig()
    {
        $this->config = Nette\Environment::getConfig();
    }

    public function startup()
    {
        parent::startup();
        $this->property_merchantID();
    }

    /**
     * Set merchant ID property
     * @property $merchantID
     */
    private function property_merchantID()
    {
        $httpRequest = $this->context->getByType('Nette\Http\Request');
        $url = new Nette\Http\Url($httpRequest->getUrl());
        $merchantID = $this->merchantService->findMerchant_id_byUrl($url->host);

        if(!is_null($merchantID))
            $this->merchantID = $merchantID;
    }

	/**
	 * Create template with default variables
	 * @param null $class
	 * @return \Nette\Application\UI\ITemplate
	 */
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$template->rootUrl = $this->config['url']['rootUrl'];
		
		$template->isLoggedIn = $this->user->isLoggedIn();
		return $template;
	}
}