<?php
/**
 * Merchant Model
 *
 * PHP Version 5.3 (min)
 *
 * @package DigiTisk
 * @subpackage Calculator
 * @author Zdeněk Vítek <zvitek@iwory.cz>
 * @license
 */

namespace App\Model\Merchant;

use Nette\Utils\Strings;

class MerchantService extends \Nette\Object {

    /** @var \Nette\Database\Context */
    private $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**************************************************************************************************************z*v*/
    /********** DATA FCN **********/

    /**
     * Získání ID merchanta na základě systemové url
     * Pracuje s tabulkami: merchants
     * @param string $url
     * @return null|int
     */
    public function findMerchant_id_byUrl($url)
    {
        $merchant = $this->table_merchant()->where('system_url', $url)->fetch();

        if($merchant)
            return $merchant['id'];

        return NULL;
    }

    /**************************************************************************************************************z*v*/
    /********** DATA FCN **********/

    /**
     * Přidá nebo aktualizuje záznam o obochodníkovi
     * @param array|object $data
     * @param null|int $id
     * @return bool
     */
    public function i_control($data, $id = NULL)
    {
        try
        {
            if(is_null($id))
            {
                $row = $this->table_merchant()->insert($data);
                return $row->id;
            }
            else
            {
                $this->table_merchant()->where('id', $id)->update($data);
                return TRUE;
            }
        }
        catch(PDOException $e)
        {
            return FALSE;
        }
    }

    /**************************************************************************************************************z*v*/
    /********** ACL FCN **********/

    /**
     * Vloží roli pro obchoníka
     * @param int $merchantID
     * @param int $roleID
     * @return bool
     */
    public function i_merchantRoles($merchantID, $roleID)
    {
        try
        {
            $data = array('merchants_id' => $merchantID, 'acl_roles_id' => $roleID);
            $this->table_merchantRole()->where($data)->delete();

            $this->table_merchantRole()->insert($data);
            return TRUE;
        }
        catch(PDOException $e)
        {
            return FALSE;
        }
    }

    /**************************************************************************************************************z*v*/
    /********** VERIFY FCN **********/

    /**
     * Zjistí zda je e-mail již registrovaný
     * @param string $email
     * @return bool
     */
    public function verify_freeEmail($email)
    {
        return $this->table_merchant()->where('email', $email)->fetch() ? TRUE : FALSE;
    }

    /**
     * Ověří existenci systémové jména a případně vygeneruje nové
     * @param string $name
     * @return string
     */
    public function verify_systemName($name)
    {
        $name = Strings::webalize($name);
        $isName = $this->table_merchant()->where('system_name', $name)->fetch();

        if($isName)
            $name = $isName . rand(1000, 9999);

        return $name;
    }

    /**************************************************************************************************************z*v*/
    /********** INIT TABLES **********/

    public function table_merchant()
    {
        return $this->database->table(Config::C_MERCHANT);
    }

    public function table_merchantRole()
    {
        return $this->database->table(Config::C_MERCHANT_ROLE);
    }
}