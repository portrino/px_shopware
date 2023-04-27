<?php

namespace Portrino\PxShopware\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Andre Wuttig <wuttig@portrino.de>, portrino GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class Media
 */
class Media extends AbstractShopwareModel
{
    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var array
     */
    protected $thumbnails = [];

    /**
     * @param object $raw
     * @param bool $token
     */
    public function initialize($raw, $token)
    {
        parent::initialize($raw, $token);

        if (isset($this->raw->path)) {
            $this->setUrl($this->raw->path);
        }

        if (isset($this->raw->thumbnails)) {
            $this->setThumbnails((array)$this->raw->thumbnails);
        }
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function getThumbnails(): array
    {
        return $this->thumbnails;
    }

    /**
     * @param array $thumbnails
     */
    public function setThumbnails(array $thumbnails): void
    {
        $this->thumbnails = $thumbnails;
    }
}
