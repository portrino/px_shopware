<?php
/**
 * Created by PhpStorm.
 * User: aw
 * Date: 08.04.2016
 * Time: 17:08
 */
namespace Portrino\PxShopware\Domain\Model;


/**
 * Class AbstractShopwareModel
 *
 * @package Portrino\PxShopware\Domain\Model
 */
interface ShopwareModelInterface {

    /**
     * @return string
     */
    public function getId();

    /**
     * @param string $id
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getRaw();

    /**
     * @param string $raw
     */
    public function setRaw($raw);

    /**
     * @return boolean
     */
    public function isToken();

    /**
     * @param boolean $token
     */
    public function setToken($token);

}