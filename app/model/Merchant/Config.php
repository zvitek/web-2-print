<?php
/**
 * Config of Merchant
 *
 * PHP Version 5.3 (min)
 *
 * @package DigiTisk
 * @subpackage Merchant
 * @author Zdeněk Vítek <zvitek@iwory.cz>
 * @license
 */

namespace App\Model\Merchant;

use Nette\Object;

class Config extends Object {

    /** db table constants */
    const
    C_MERCHANT = 'merchants',
    C_MERCHANT_ROLE = 'acl_merchants_roles';
}