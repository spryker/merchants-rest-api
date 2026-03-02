<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\MerchantsRestApi\Processor\Reader;

use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

interface MerchantReaderInterface
{
    /**
     * @param array<string> $merchantReferences
     * @param string $localeName
     *
     * @return array<\Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface>
     */
    public function getMerchantsResources(array $merchantReferences, string $localeName): array;

    public function getMerchant(RestRequestInterface $restRequest): RestResponseInterface;

    public function getMerchants(RestRequestInterface $restRequest): RestResponseInterface;
}
