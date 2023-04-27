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
 */
interface ShopwareModelInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     */
    public function setId($id);

    /**
     * @return object
     */
    public function getRaw();

    /**
     * @param object $raw
     */
    public function setRaw($raw);

    /**
     * @return bool
     */
    public function isToken();

    /**
     * @param bool $token
     */
    public function setToken($token);
}
