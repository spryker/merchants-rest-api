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

class MerchantsRelationshipResolver extends AbstractRelationshipResolver implements PerItemRelationshipResolverInterface
{
    public function __construct(
        protected MerchantReaderInterface $merchantReader,
    ) {
    }

    /**
     * @param array<object> $parentResources
     * @param array<string, mixed> $context
     *
     * @return array<string, array<object>>
     */
    public function resolvePerItem(array $parentResources, array $context): array
    {
        $this->parentResources = $parentResources;
        $this->context = $context;
        $locale = $this->getLocale()->getLocaleName() ?? '';

        $merchantReferencesIndexedByUuid = [];

        foreach ($parentResources as $parentResource) {
            $uuid = $parentResource->uuid ?? null;
            $merchantReference = $parentResource->merchantReference ?? null;

            if ($uuid === null || $merchantReference === null) {
                continue;
            }

            $merchantReferencesIndexedByUuid[$uuid] = $merchantReference;
        }

        if ($merchantReferencesIndexedByUuid === []) {
            return [];
        }

        $merchantResourcesIndexedByReference = $this->merchantReader->getMerchantResourcesIndexedByReference(
            array_values(array_unique($merchantReferencesIndexedByUuid)),
            $locale,
        );

        $merchantResourcesIndexedByUuid = [];

        foreach ($merchantReferencesIndexedByUuid as $uuid => $merchantReference) {
            $merchantResource = $merchantResourcesIndexedByReference[$merchantReference] ?? null;

            if ($merchantResource === null) {
                $merchantResourcesIndexedByUuid[$uuid] = [];

                continue;
            }

            $merchantResourcesIndexedByUuid[$uuid] = [$merchantResource];
        }

        return $merchantResourcesIndexedByUuid;
    }

    /**
     * @return array<object>
     */
    protected function resolveRelationship(): array
    {
        $allResources = [];

        foreach ($this->resolvePerItem($this->parentResources, $this->context) as $resources) {
            $allResources = array_merge($allResources, $resources);
        }

        return $allResources;
    }
}
