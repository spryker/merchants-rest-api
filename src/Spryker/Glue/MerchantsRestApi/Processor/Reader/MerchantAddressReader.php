<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\MerchantsRestApi\Processor\Reader;

use Generated\Shared\Transfer\MerchantStorageCriteriaTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\MerchantsRestApi\Dependency\Client\MerchantsRestApiToMerchantStorageClientInterface;
use Spryker\Glue\MerchantsRestApi\MerchantsRestApiConfig;
use Spryker\Glue\MerchantsRestApi\Processor\RestResponseBuilder\MerchantAddressRestResponseBuilderInterface;

class MerchantAddressReader implements MerchantAddressReaderInterface
{
    /**
     * @var \Spryker\Glue\MerchantsRestApi\Dependency\Client\MerchantsRestApiToMerchantStorageClientInterface
     */
    protected $merchantStorageClient;

    /**
     * @var \Spryker\Glue\MerchantsRestApi\Processor\RestResponseBuilder\MerchantAddressRestResponseBuilderInterface
     */
    protected $merchantsAddressRestResponseBuilder;

    /**
     * @param \Spryker\Glue\MerchantsRestApi\Dependency\Client\MerchantsRestApiToMerchantStorageClientInterface $merchantStorageClient
     * @param \Spryker\Glue\MerchantsRestApi\Processor\RestResponseBuilder\MerchantAddressRestResponseBuilderInterface $merchantsAddressRestResponseBuilder
     */
    public function __construct(
        MerchantsRestApiToMerchantStorageClientInterface $merchantStorageClient,
        MerchantAddressRestResponseBuilderInterface $merchantsAddressRestResponseBuilder
    ) {
        $this->merchantStorageClient = $merchantStorageClient;
        $this->merchantsAddressRestResponseBuilder = $merchantsAddressRestResponseBuilder;
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function getMerchantAddresses(RestRequestInterface $restRequest): RestResponseInterface
    {
        $merchantResource = $restRequest->findParentResourceByType(MerchantsRestApiConfig::RESOURCE_MERCHANTS);

        if (!$merchantResource || !$merchantResource->getId()) {
            return $this->merchantsAddressRestResponseBuilder->createMerchantIdentifierMissingErrorResponse();
        }

        /**
         * @var string $merchantReference
         */
        $merchantReference = $merchantResource->getId();

        $merchantStorageTransfer = $this->merchantStorageClient->findOne(
            (new MerchantStorageCriteriaTransfer())->addMerchantReference($merchantReference),
        );

        if (!$merchantStorageTransfer) {
            return $this->merchantsAddressRestResponseBuilder->createMerchantNotFoundErrorResponse();
        }

        /**
         * @var \Generated\Shared\Transfer\MerchantStorageProfileTransfer $merchantStorageProfileTransfer
         */
        $merchantStorageProfileTransfer = $merchantStorageTransfer->getMerchantProfile();
        $merchantStorageProfileAddressTransfers = $merchantStorageProfileTransfer->getAddressCollection();

        return $this->merchantsAddressRestResponseBuilder->createMerchantAddressesRestResponse(
            $merchantStorageProfileAddressTransfers,
            $merchantReference,
        );
    }

    /**
     * @param array<string> $merchantReferences
     *
     * @return array<\Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface>
     */
    public function getMerchantAddressResources(array $merchantReferences): array
    {
        $merchantStorageTransfers = $this->merchantStorageClient->get(
            (new MerchantStorageCriteriaTransfer())->setMerchantReferences($merchantReferences),
        );

        $merchantStorageTransfers = $this->indexMerchantStorageTransfersByMerchantReference($merchantStorageTransfers);

        return $this->merchantsAddressRestResponseBuilder->createMerchantAddressesRestResources($merchantStorageTransfers);
    }

    /**
     * @param array<\Generated\Shared\Transfer\MerchantStorageTransfer> $merchantStorageTransfers
     *
     * @return array<\Generated\Shared\Transfer\MerchantStorageTransfer>
     */
    protected function indexMerchantStorageTransfersByMerchantReference(array $merchantStorageTransfers): array
    {
        $merchantStorageTransfersWithMerchantReferenceKey = [];
        foreach ($merchantStorageTransfers as $merchantStorageTransfer) {
            $merchantStorageTransfersWithMerchantReferenceKey[$merchantStorageTransfer->getMerchantReference()] = $merchantStorageTransfer;
        }

        return $merchantStorageTransfersWithMerchantReferenceKey;
    }
}
