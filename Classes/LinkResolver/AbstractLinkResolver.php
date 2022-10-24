<?php
namespace Portrino\PxShopware\LinkResolver;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Sascha Nowak <sascha.nowak@netlogix.de>, netlogix GmbH & Co. KG
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

use Portrino\PxShopware\Domain\Model\ShopwareModelInterface;
use Portrino\PxShopware\Service\Shopware\AbstractShopwareApiClientInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;

class AbstractLinkResolver implements SingletonInterface
{

    /**
     * @var AbstractShopwareApiClientInterface
     */
    protected $client;

    /**
     * @param string $linkText
     * @param array $configuration
     * @param string $linkHandlerKeyword
     * @param string $linkHandlerValue
     * @param string $mixedLinkParameter
     * @return array
     */
    public function main($linkText, &$configuration, $linkHandlerKeyword, $linkHandlerValue, $mixedLinkParameter)
    {
        $linkParameterParts = GeneralUtility::makeInstance(TypoLinkCodecService::class)->decode($mixedLinkParameter);
        $configuration['extTarget'] = $linkParameterParts['target'];
        $result = [
            'href' => '',
            'target' => $linkParameterParts['target'],
            'class' => $linkParameterParts['class'],
            'title' => $linkParameterParts['title']
        ];

        /** @var ShopwareModelInterface $object */
        $object = $this->client->findById((int)$linkHandlerValue);
        if ($object === null) {
            // TODO: Log dead link
            return $result;
        }

        if (method_exists($object, 'getUri') && method_exists($object, 'getName')) {
            $result['href'] = (string)$object->getUri();
            if ($result['title'] === '') {
                $result['title'] = $object->getName();
            }
        }

        return $result;
    }

}