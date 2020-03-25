<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\MerchantsRestApi\Processor\Mapper;

use Generated\Shared\Transfer\MerchantStorageTransfer;
use Generated\Shared\Transfer\RestMerchantsAttributesTransfer;

interface MerchantMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\MerchantStorageTransfer $merchantStorageTransfer
     * @param \Generated\Shared\Transfer\RestMerchantsAttributesTransfer $restMerchantsAttributesTransfer
     *
     * @return \Generated\Shared\Transfer\RestMerchantsAttributesTransfer
     */
    public function mapMerchantStorageTransferToRestMerchantAttributesTransfer(
        MerchantStorageTransfer $merchantStorageTransfer,
        RestMerchantsAttributesTransfer $restMerchantsAttributesTransfer
    ): RestMerchantsAttributesTransfer;
}
