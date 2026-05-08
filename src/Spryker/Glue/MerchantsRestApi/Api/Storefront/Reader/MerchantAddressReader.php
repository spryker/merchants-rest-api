<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\MerchantsRestApi\Api\Storefront\Reader;

use Generated\Api\Storefront\MerchantAddressesStorefrontResource;
use Generated\Shared\Transfer\MerchantsStorefrontConditionsTransfer;
use Generated\Shared\Transfer\MerchantsStorefrontCriteriaTransfer;
use Generated\Shared\Transfer\MerchantStorageCriteriaTransfer;
use Generated\Shared\Transfer\MerchantStorageProfileAddressTransfer;
use Spryker\Client\MerchantStorage\MerchantStorageClientInterface;

class MerchantAddressReader implements MerchantAddressReaderInterface
{
    public function __construct(
        protected MerchantStorageClientInterface $merchantStorageClient,
    ) {
    }

    public function findAddressesByMerchantReference(
        MerchantsStorefrontCriteriaTransfer $merchantsStorefrontCriteriaTransfer
    ): ?MerchantAddressesStorefrontResource {
        $conditions = $merchantsStorefrontCriteriaTransfer->getMerchantsStorefrontConditions() ?? new MerchantsStorefrontConditionsTransfer();
        $merchantReference = (string)$conditions->getMerchantReference();

        $merchantStorageTransfers = $this->merchantStorageClient->get(
            (new MerchantStorageCriteriaTransfer())->addMerchantReference($merchantReference),
        );

        if ($merchantStorageTransfers === []) {
            return null;
        }

        $merchantStorageTransfer = reset($merchantStorageTransfers);
        $merchantProfile = $merchantStorageTransfer->getMerchantProfile();

        $addresses = [];

        if ($merchantProfile !== null) {
            foreach ($merchantProfile->getAddressCollection() as $addressTransfer) {
                $addresses[] = $this->prepareAddressData($addressTransfer);
            }
        }

        $resource = new MerchantAddressesStorefrontResource();
        $resource->merchantAddressId = $merchantReference;
        $resource->addresses = $addresses;

        return $resource;
    }

    /**
     * @return array<string, mixed>
     */
    protected function prepareAddressData(MerchantStorageProfileAddressTransfer $merchantStorageProfileAddressTransfer): array
    {
        return [
            'countryName' => $merchantStorageProfileAddressTransfer->getCountryName(),
            'address1' => $merchantStorageProfileAddressTransfer->getAddress1(),
            'address2' => $merchantStorageProfileAddressTransfer->getAddress2(),
            'address3' => $merchantStorageProfileAddressTransfer->getAddress3(),
            'city' => $merchantStorageProfileAddressTransfer->getCity(),
            'zipCode' => $merchantStorageProfileAddressTransfer->getZipCode(),
            'email' => $merchantStorageProfileAddressTransfer->getEmail(),
            'latitude' => $merchantStorageProfileAddressTransfer->getLatitude(),
            'longitude' => $merchantStorageProfileAddressTransfer->getLongitude(),
        ];
    }
}
