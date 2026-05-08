<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\MerchantsRestApi;

use Spryker\Glue\Kernel\AbstractBundleConfig;

class MerchantsRestApiConfig extends AbstractBundleConfig
{
    /**
     * @api
     */
    public const string RESOURCE_MERCHANTS = 'merchants';

    /**
     * @api
     */
    public const string RESOURCE_MERCHANT_ADDRESSES = 'merchant-addresses';

    /**
     * @api
     */
    public const string RESPONSE_CODE_MERCHANT_NOT_FOUND = '3501';

    /**
     * @api
     */
    public const string RESPONSE_DETAIL_MERCHANT_NOT_FOUND = 'Merchant not found.';

    /**
     * @api
     */
    public const string RESPONSE_CODE_MERCHANT_IDENTIFIER_MISSING = '3502';

    /**
     * @api
     */
    public const string RESPONSE_DETAIL_MERCHANT_IDENTIFIER_MISSING = 'Merchant identifier is not specified.';

    /**
     * @api
     */
    public const string MERCHANT_REFERENCE = 'merchantReference';
}
