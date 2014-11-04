<?php
/**
 * Config of Calculator
 *
 * PHP Version 5.3 (min)
 *
 * @package DigiTisk
 * @subpackage Calculator
 * @author Zdeněk Vítek <zvitek@iwory.cz>
 * @license
 */

namespace App\Model\Calculator;

use Nette\Object;

class Config extends Object {

    /** db table constants */
    const
    /** Počet kusů */
    C_AMOUNT = 'calculator_amount',

    /** Cena kusů v dané službě */
    C_AMOUNT_HAS_SERVICE = 'calculator_amount_has_service',

    /** Zakončovací práce */
    C_DRESSING = 'calculator_dressing',

    /** Zakončovací práce pro danou službu */
    C_DRESSING_HAS_SERVICE = 'calculator_dressing_has_service',

    /** Formáty */
    C_FORMATS = 'calculator_formats',

    /** Formát pro danou službu */
    C_FORMAT_HAS_SERVICE = 'calculator_format_has_service',

    /** Počet formátu na arch */
    C_FORMAT_SCATTER = 'calculator_format_scatter',

    /** Materiály */
    C_MATERIALS = 'calculator_materials',

    /** Cena materiálu za gramy */
    C_MATERIALS_WEIGHT = 'calculator_materials_weight',

    /** Formáty připojené pod materiál */
    C_MATERIAL_HAS_FORMAT = 'calculator_material_has_format',

    /** Materiál připojený pod službu */
    C_MATERIAL_HAS_SERVICE = 'calculator_material_has_service',

    /** Služby */
    C_SERVICES = 'calculator_services';

}