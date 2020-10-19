<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\MerchantsRestApi;

use Spryker\Glue\Kernel\AbstractBundleDependencyProvider;
use Spryker\Glue\Kernel\Container;
use Spryker\Glue\MerchantsRestApi\Dependency\Client\MerchantsRestApiToGlossaryStorageClientBridge;
use Spryker\Glue\MerchantsRestApi\Dependency\Client\MerchantsRestApiToMerchantStorageClientBridge;

/**
 * @method \Spryker\Glue\MerchantsRestApi\MerchantsRestApiConfig getConfig()
 */
class MerchantsRestApiDependencyProvider extends AbstractBundleDependencyProvider
{
    public const CLIENT_MERCHANT_STORAGE = 'CLIENT_MERCHANT_STORAGE';
    public const CLIENT_GLOSSARY_STORAGE = 'CLIENT_GLOSSARY_STORAGE';

    public const PLUGINS_REST_MERCHANT_ATTRIBUTES_MAPPER = 'PLUGINS_REST_MERCHANT_ATTRIBUTES_MAPPER';

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);
        $container = $this->addMerchantStorageClient($container);
        $container = $this->addGlossaryStorageClient($container);

        $container = $this->addRestMerchantAttributesMapperPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addMerchantStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_MERCHANT_STORAGE, function (Container $container) {
            return new MerchantsRestApiToMerchantStorageClientBridge(
                $container->getLocator()->merchantStorage()->client()
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addGlossaryStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_GLOSSARY_STORAGE, function (Container $container) {
            return new MerchantsRestApiToGlossaryStorageClientBridge(
                $container->getLocator()->glossaryStorage()->client()
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addRestMerchantAttributesMapperPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_REST_MERCHANT_ATTRIBUTES_MAPPER, function (Container $container) {
            return $this->getRestMerchantAttributesMapperPlugins();
        });

        return $container;
    }

    /**
     * @return \Spryker\Glue\MerchantsRestApiExtension\Dependency\Plugin\RestMerchantAttributesMapperPluginInterface[]
     */
    public function getRestMerchantAttributesMapperPlugins(): array
    {
        return [];
    }
}
