<?php
/**
 * GUI for Acl
 *
 * @copyright  Copyright (c) 2012 Tomas Marcanik, www.TomasMarcanik.cz
 * @package    GUI for Acl
 */

namespace App\SystemModule\AclModule\Presenters;

/**
 * Blank Presenter, which redirects to UsersPresenter
 *
 */
class BasePresenter extends \App\SystemModule\BasePresenter
{
    public function startup()
    {
//        $this->redirect('Users:Default');
    }

}
