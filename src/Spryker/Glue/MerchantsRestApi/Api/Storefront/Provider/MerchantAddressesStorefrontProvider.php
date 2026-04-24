<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\MerchantsRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\MerchantAddressesStorefrontResource;
use Generated\Shared\Transfer\MerchantStorageCriteriaTransfer;
use Generated\Shared\Transfer\MerchantStorageProfileAddressTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\MerchantStorage\MerchantStorageClientInterface;
use Symfony\Component\HttpFoundation\Response;

class MerchantAddressesStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string URI_VAR_MERCHANT_REFERENCE = 'merchantReference';

    protected const string ERROR_CODE_MERCHANT_NOT_FOUND = '3501';

    protected const string ERROR_MESSAGE_MERCHANT_NOT_FOUND = 'Merchant not found.';

    protected const string ERROR_CODE_MERCHANT_REFERENCE_NOT_SPECIFIED = '3502';

    protected const string ERROR_MESSAGE_MERCHANT_REFERENCE_NOT_SPECIFIED = 'Merchant identifier is not specified.';

    public function __construct(
        protected MerchantStorageClientInterface $merchantStorageClient,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     *
     * @return array<\Generated\Api\Storefront\MerchantAddressesStorefrontResource>
     */
    protected function provideCollection(): array
    {
        $merchantReference = $this->resolveMerchantReference();

        $merchantStorageTransfers = $this->merchantStorageClient->get(
            (new MerchantStorageCriteriaTransfer())->addMerchantReference($merchantReference),
        );

        if ($merchantStorageTransfers === []) {
            throw new GlueApiException(
                Response::HTTP_NOT_FOUND,
                static::ERROR_CODE_MERCHANT_NOT_FOUND,
                static::ERROR_MESSAGE_MERCHANT_NOT_FOUND,
            );
        }

        $merchantStorageTransfer = reset($merchantStorageTransfers);
        $merchantProfile = $merchantStorageTransfer->getMerchantProfile();

        if ($merchantProfile === null || $merchantProfile->getAddressCollection()->count() === 0) {
            return [];
        }

        $addresses = [];

        foreach ($merchantProfile->getAddressCollection() as $addressTransfer) {
            $addresses[] = $this->prepareAddressData($addressTransfer);
        }

        $resource = new MerchantAddressesStorefrontResource();
        $resource->merchantAddressId = $merchantReference;
        $resource->addresses = $addresses;

        return [$resource];
    }

    protected function resolveMerchantReference(): string
    {
        if (!$this->hasUriVariable(static::URI_VAR_MERCHANT_REFERENCE)) {
            $this->throwMissingMerchantReference();
        }

        $merchantReference = (string)$this->getUriVariable(static::URI_VAR_MERCHANT_REFERENCE);

        if ($merchantReference === '') {
            $this->throwMissingMerchantReference();
        }

        return $merchantReference;
    }

    protected function throwMissingMerchantReference(): never
    {
        throw new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            static::ERROR_CODE_MERCHANT_REFERENCE_NOT_SPECIFIED,
            static::ERROR_MESSAGE_MERCHANT_REFERENCE_NOT_SPECIFIED,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function prepareAddressData(MerchantStorageProfileAddressTransfer $addressTransfer): array
    {
        return [
            'countryName' => $addressTransfer->getCountryName(),
            'address1' => $addressTransfer->getAddress1(),
            'address2' => $addressTransfer->getAddress2(),
            'address3' => $addressTransfer->getAddress3(),
            'city' => $addressTransfer->getCity(),
            'zipCode' => $addressTransfer->getZipCode(),
            'email' => $addressTransfer->getEmail(),
            'latitude' => $addressTransfer->getLatitude(),
            'longitude' => $addressTransfer->getLongitude(),
        ];
    }
}
