<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\MerchantsRestApi\Api\Storefront\Reader;

use Generated\Api\Storefront\MerchantsStorefrontResource;
use Generated\Shared\Transfer\MerchantsStorefrontCriteriaTransfer;

interface MerchantReaderInterface
{
    /**
     * Specification:
     * - Resolves the merchant identified by the criteria's `merchantReference`.
     * - Translates glossary keys on the merchant profile using the criteria's `localeName`.
     * - Maps the merchant to a `MerchantsStorefrontResource` and runs registered expander plugins.
     * - Returns `null` when no merchant matches the reference.
     */
    public function findMerchantByReference(MerchantsStorefrontCriteriaTransfer $merchantsStorefrontCriteriaTransfer): ?MerchantsStorefrontResource;

    /**
     * Specification:
     * - Searches merchants using `pagination` (limit/offset) from the criteria, falling back to module defaults.
     * - Loads matching merchant storage data, translates glossary keys, maps to resources and runs expander plugins.
     * - Returns the resources together with the search engine's total result count.
     *
     * @return array{resources: array<int, \Generated\Api\Storefront\MerchantsStorefrontResource>, totalCount: int}
     */
    public function findMerchants(MerchantsStorefrontCriteriaTransfer $merchantsStorefrontCriteriaTransfer): array;
}
