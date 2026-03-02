<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\MerchantsRestApi\Dependency\Client;

use Generated\Shared\Transfer\MerchantStorageCriteriaTransfer;
use Generated\Shared\Transfer\MerchantStorageTransfer;

interface MerchantsRestApiToMerchantStorageClientInterface
{
    /**
     * @param \Generated\Shared\Transfer\MerchantStorageCriteriaTransfer $merchantStorageCriteriaTransfer
     *
     * @return array<\Generated\Shared\Transfer\MerchantStorageTransfer>
     */
    public function get(MerchantStorageCriteriaTransfer $merchantStorageCriteriaTransfer): array;

    public function findOne(MerchantStorageCriteriaTransfer $merchantStorageCriteriaTransfer): ?MerchantStorageTransfer;
}
