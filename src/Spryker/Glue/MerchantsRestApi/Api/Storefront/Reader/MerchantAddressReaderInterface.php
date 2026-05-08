<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\MerchantsRestApi\Api\Storefront\Reader;

use Generated\Api\Storefront\MerchantAddressesStorefrontResource;
use Generated\Shared\Transfer\MerchantsStorefrontCriteriaTransfer;

interface MerchantAddressReaderInterface
{
    /**
     * Specification:
     * - Resolves the merchant identified by the criteria's `merchantReference`.
     * - Builds a `MerchantAddressesStorefrontResource` containing all addresses on the merchant profile (empty when none).
     * - Returns `null` when no merchant matches the reference.
     */
    public function findAddressesByMerchantReference(
        MerchantsStorefrontCriteriaTransfer $merchantsStorefrontCriteriaTransfer
    ): ?MerchantAddressesStorefrontResource;
}
