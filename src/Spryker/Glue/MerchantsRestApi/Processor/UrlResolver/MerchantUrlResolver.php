<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\MerchantsRestApi\Processor\UrlResolver;

use Generated\Shared\Transfer\RestUrlResolverAttributesTransfer;
use Generated\Shared\Transfer\UrlStorageTransfer;
use Spryker\Glue\MerchantsRestApi\Dependency\Client\MerchantsRestApiToMerchantStorageClientInterface;
use Spryker\Glue\MerchantsRestApi\MerchantsRestApiConfig;

class MerchantUrlResolver implements MerchantUrlResolverInterface
{
    /**
     * @var \Spryker\Glue\MerchantsRestApi\Dependency\Client\MerchantsRestApiToMerchantStorageClientInterface
     */
    protected $merchantStorageClient;

    /**
     * @param \Spryker\Glue\MerchantsRestApi\Dependency\Client\MerchantsRestApiToMerchantStorageClientInterface $merchantStorageClient
     */
    public function __construct(MerchantsRestApiToMerchantStorageClientInterface $merchantStorageClient)
    {
        $this->merchantStorageClient = $merchantStorageClient;
    }

    /**
     * @param \Generated\Shared\Transfer\UrlStorageTransfer $urlStorageTransfer
     *
     * @return \Generated\Shared\Transfer\RestUrlResolverAttributesTransfer|null
     */
    public function resolveMerchantUrl(UrlStorageTransfer $urlStorageTransfer): ?RestUrlResolverAttributesTransfer
    {
        $urlStorageTransfer->requireFkResourceMerchant();

        $merchantStorageTransfer = $this->merchantStorageClient->findOne($urlStorageTransfer->getFkResourceMerchant());

        if (!$merchantStorageTransfer) {
            return null;
        }

        return (new RestUrlResolverAttributesTransfer())
            ->setEntityId($merchantStorageTransfer->getMerchantReference())
            ->setEntityType(MerchantsRestApiConfig::RESOURCE_MERCHANTS);
    }
}
