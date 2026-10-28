<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\MerchantsRestApi\Api\Storefront\Relationship;

use Spryker\ApiPlatform\Relationship\AbstractRelationshipResolver;
use Spryker\ApiPlatform\Relationship\PerItemRelationshipResolverInterface;
use Spryker\Glue\MerchantsRestApi\Api\Storefront\Reader\MerchantReaderInterface;

class OrderMerchantsRelationshipResolver extends AbstractRelationshipResolver implements PerItemRelationshipResolverInterface
{
    public function __construct(
        protected MerchantReaderInterface $merchantReader,
    ) {
    }

    /**
     * @param array<object> $parentResources
     * @param array<string, mixed> $context
     *
     * @return array<string, array<\Generated\Api\Storefront\MerchantsStorefrontResource>>
     */
    public function resolvePerItem(array $parentResources, array $context): array
    {
        $this->parentResources = $parentResources;
        $this->context = $context;

        $merchantReferencesIndexedByOrderReference = $this->getMerchantReferencesIndexedByOrderReference($parentResources);
        $merchantReferences = array_values(array_unique(array_merge([], ...array_values($merchantReferencesIndexedByOrderReference))));

        if ($merchantReferences === []) {
            return [];
        }

        $merchantResourcesIndexedByReference = $this->merchantReader->getMerchantResourcesIndexedByReference(
            $merchantReferences,
            $this->getLocale()->getLocaleName() ?? '',
        );

        $merchantResourcesIndexedByOrderReference = [];

        foreach ($merchantReferencesIndexedByOrderReference as $orderReference => $orderMerchantReferences) {
            $merchantResourcesIndexedByOrderReference[$orderReference] = array_values(array_intersect_key(
                $merchantResourcesIndexedByReference,
                array_flip($orderMerchantReferences),
            ));
        }

        return $merchantResourcesIndexedByOrderReference;
    }

    /**
     * @return array<\Generated\Api\Storefront\MerchantsStorefrontResource>
     */
    protected function resolveRelationship(): array
    {
        $merchantResources = [];

        foreach (array_merge([], ...array_values($this->resolvePerItem($this->parentResources, $this->context))) as $merchantResource) {
            $merchantResources[$merchantResource->merchantReference] = $merchantResource;
        }

        return array_values($merchantResources);
    }

    /**
     * @param array<object> $parentResources
     *
     * @return array<string, array<string>>
     */
    protected function getMerchantReferencesIndexedByOrderReference(array $parentResources): array
    {
        $merchantReferencesIndexedByOrderReference = [];

        foreach ($parentResources as $parentResource) {
            $orderReference = $parentResource->orderReference ?? null;

            if ($orderReference === null) {
                continue;
            }

            $merchantReferencesIndexedByOrderReference[$orderReference] = array_values(array_filter(
                (array)($parentResource->merchantReferences ?? []),
                is_string(...),
            ));
        }

        return $merchantReferencesIndexedByOrderReference;
    }
}
