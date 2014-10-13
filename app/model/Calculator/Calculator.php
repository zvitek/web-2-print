<?php
/**
 * Calculator Model
 *
 * PHP Version 5.3 (min)
 *
 * @package DigiTisk
 * @subpackage Calculator
 * @author Zdeněk Vítek <zvitek@iwory.cz>
 * @license
 */

namespace App\Model\Calculator;

class CalculatorService extends \Nette\Object {

    /** @var \Nette\Database\Context */
    private $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**************************************************************************************************************z*v*/
    /********** INIT TABLES **********/

    public function table_services()
    {
        return $this->database->table(Config::C_SERVICES);
    }
}