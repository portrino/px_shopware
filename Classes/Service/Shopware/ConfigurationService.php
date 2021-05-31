<?php
namespace Portrino\PxShopware\Service\Shopware;

use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientConfigurationException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class ConfigurationService implements SingletonInterface
{

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $settings = [];

    public function initializeObject()
    {
        $settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'PxShopware');
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('px_shopware');

        if ($settings['api']['url'] === '' && isset($extConf['api']['url'])) {
            $settings['api']['url'] = $extConf['api']['url'];
        }
        if ($settings['api']['username'] === '' && isset($extConf['api']['username'])) {
            $settings['api']['username'] = $extConf['api']['username'];
        }
        if ($settings['api']['key'] === '' && isset($extConf['api']['key'])) {
            $settings['api']['key'] = $extConf['api']['key'];
        }

        if (!isset($settings['caching']['disable']) && isset($extConf['caching']['disable'])) {
            $settings['caching']['disable'] = $extConf['caching']['disable'];
        }
        if (!isset($settings['caching']['lifetime']) && isset($extConf['caching']['lifetime'])) {
            $settings['caching']['lifetime'] = (int)$extConf['caching']['lifetime'];
        }

        if (!isset($settings['logging']['disable']) && isset($extConf['logging']['disable'])) {
            $settings['logging']['disable'] = $extConf['logging']['disable'];
        }

        $this->settings = $settings;
    }

    /**
     * @return string
     * @throws ShopwareApiClientConfigurationException
     */
    public function getApiUrl()
    {
        if ($this->settings['api']['url'] === false) {
            throw new ShopwareApiClientConfigurationException('No apiUrl given to connect to shopware REST-Service! Please add it to your extension configuration, TS or flexform.',
                1458807513);
        }

        if (filter_var($this->settings['api']['url'], FILTER_VALIDATE_URL) === false) {
            throw new ShopwareApiClientConfigurationException('apiUrl is not valid. Please enter a valid url in your extension configuration, TS or flexform.',
                1459492118);
        }
        return rtrim($this->settings['api']['url'], '/') . '/';
    }

    /**
     * @return string
     * @throws ShopwareApiClientConfigurationException
     */
    public function getApiUsername()
    {
        if ($this->settings['api']['username'] === false) {
            throw new ShopwareApiClientConfigurationException('No username given to connect to shopware REST-Service! Please add it to your extension configuration, TS or Flexform.',
                1458807514);
        }
        return $this->settings['api']['username'];
    }

    /**
     * @return string
     * @throws ShopwareApiClientConfigurationException
     */
    public function getApiKey()
    {
        if ($this->settings['api']['key'] === false) {
            throw new ShopwareApiClientConfigurationException('No apiKey given to connect to shopware REST-Service! Please add it to your extension configuration, TS or Flexform.',
                1458807515);
        }
        return $this->settings['api']['key'];
    }

    /**
     * @return boolean
     */
    public function isCachingEnabled()
    {
        return (boolean)$this->settings['caching']['disable'] !== true;
    }

    /**
     * @return integer
     */
    public function getCacheLifeTime()
    {
        return intval($this->settings['caching']['lifetime'] ?: 0);
    }

    /**
     * @return boolean
     */
    public function isLoggingEnabled()
    {
        return (boolean)$this->settings['logging']['disable'] !== true;
    }

}
