<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\MerchantsRestApi\Api\Storefront\Exception;

use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\MerchantsRestApi\MerchantsRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class MerchantsExceptionFactory
{
    public function createMerchantNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            MerchantsRestApiConfig::RESPONSE_CODE_MERCHANT_NOT_FOUND,
            MerchantsRestApiConfig::RESPONSE_DETAIL_MERCHANT_NOT_FOUND,
        );
    }

    public function createMerchantIdentifierMissingException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            MerchantsRestApiConfig::RESPONSE_CODE_MERCHANT_IDENTIFIER_MISSING,
            MerchantsRestApiConfig::RESPONSE_DETAIL_MERCHANT_IDENTIFIER_MISSING,
        );
    }
}
