<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\MerchantsRestApi\Api\Storefront\Provider;

use Generated\Shared\Transfer\MerchantsStorefrontConditionsTransfer;
use Generated\Shared\Transfer\MerchantsStorefrontCriteriaTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Glue\MerchantsRestApi\Api\Storefront\Exception\MerchantsExceptionFactory;
use Spryker\Glue\MerchantsRestApi\Api\Storefront\Reader\MerchantAddressReaderInterface;
use Spryker\Glue\MerchantsRestApi\MerchantsRestApiConfig;

class MerchantAddressesStorefrontProvider extends AbstractStorefrontProvider
{
    public function __construct(
        protected MerchantAddressReaderInterface $merchantAddressReader,
        protected MerchantsExceptionFactory $exceptionFactory = new MerchantsExceptionFactory(),
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     *
     * @return array<\Generated\Api\Storefront\MerchantAddressesStorefrontResource>
     */
    protected function provideCollection(): array
    {
        $criteria = (new MerchantsStorefrontCriteriaTransfer())
            ->setMerchantsStorefrontConditions(
                (new MerchantsStorefrontConditionsTransfer())
                    ->setMerchantReference($this->resolveMerchantReference()),
            );

        $resource = $this->merchantAddressReader->findAddressesByMerchantReference($criteria);

        if ($resource === null) {
            throw $this->exceptionFactory->createMerchantNotFoundException();
        }

        return [$resource];
    }

    protected function resolveMerchantReference(): string
    {
        $merchantReference = $this->hasUriVariable(MerchantsRestApiConfig::MERCHANT_REFERENCE)
            ? (string)$this->getUriVariable(MerchantsRestApiConfig::MERCHANT_REFERENCE)
            : '';

        if ($merchantReference === '') {
            throw $this->exceptionFactory->createMerchantIdentifierMissingException();
        }

        return $merchantReference;
    }
}
