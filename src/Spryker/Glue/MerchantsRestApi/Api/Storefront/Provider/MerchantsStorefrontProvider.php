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
use Spryker\Glue\MerchantsRestApi\Api\Storefront\Reader\MerchantReaderInterface;
use Spryker\Glue\MerchantsRestApi\MerchantsRestApiConfig;

class MerchantsStorefrontProvider extends AbstractStorefrontProvider
{
    public function __construct(
        protected MerchantReaderInterface $merchantReader,
        protected MerchantsExceptionFactory $exceptionFactory = new MerchantsExceptionFactory(),
    ) {
    }

    protected function provideItem(): ?object
    {
        $criteria = (new MerchantsStorefrontCriteriaTransfer())
            ->setMerchantsStorefrontConditions(
                (new MerchantsStorefrontConditionsTransfer())
                    ->setMerchantReference($this->resolveMerchantReference())
                    ->setLocaleName((string)$this->getLocale()->getLocaleName()),
            );

        $resource = $this->merchantReader->findMerchantByReference($criteria);

        if ($resource === null) {
            throw $this->exceptionFactory->createMerchantNotFoundException();
        }

        return $resource;
    }

    /**
     * @return array<\Generated\Api\Storefront\MerchantsStorefrontResource>
     */
    protected function provideCollection(): array
    {
        // When invoked as a relationship resolver from a parent that exposes `merchantReference`
        // (e.g. ProductOffers), the framework passes that value as a URI variable. Honoring it
        // here keeps the provider's `GetCollection` semantics correct: return only the parent's
        // own merchant instead of the unfiltered `/merchants` collection.
        if ($this->hasUriVariable(MerchantsRestApiConfig::MERCHANT_REFERENCE)) {
            return $this->provideMerchantsByReference();
        }

        $paginationTransfer = $this->buildPaginationTransfer();

        $criteria = (new MerchantsStorefrontCriteriaTransfer())
            ->setMerchantsStorefrontConditions(
                (new MerchantsStorefrontConditionsTransfer())
                    ->setLocaleName((string)$this->getLocale()->getLocaleName()),
            )
            ->setPagination($paginationTransfer);

        ['resources' => $resources, 'totalCount' => $totalCount] = $this->merchantReader->findMerchants($criteria);

        if ($resources !== []) {
            $limit = $paginationTransfer->getLimitOrFail();
            $offset = $paginationTransfer->getOffsetOrFail();

            // Read by Spryker\ApiPlatform\EventSubscriber\PaginationLinksResponseSubscriber from data[0].attributes.pagination
            // to emit JSON:API top-level pagination links (first, last, prev, next).
            $resources[0]->pagination = $this->calculatePagination($offset, $limit, $totalCount);
        }

        return $resources;
    }

    /**
     * @return array<\Generated\Api\Storefront\MerchantsStorefrontResource>
     */
    protected function provideMerchantsByReference(): array
    {
        $merchantReference = (string)$this->getUriVariable(MerchantsRestApiConfig::MERCHANT_REFERENCE);

        if ($merchantReference === '') {
            return [];
        }

        $criteria = (new MerchantsStorefrontCriteriaTransfer())
            ->setMerchantsStorefrontConditions(
                (new MerchantsStorefrontConditionsTransfer())
                    ->setMerchantReference($merchantReference)
                    ->setLocaleName((string)$this->getLocale()->getLocaleName()),
            );

        $resource = $this->merchantReader->findMerchantByReference($criteria);

        return $resource !== null ? [$resource] : [];
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
