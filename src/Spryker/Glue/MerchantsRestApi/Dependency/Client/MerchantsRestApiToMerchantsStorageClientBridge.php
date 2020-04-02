<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\MerchantsRestApi\Dependency\Client;

use Generated\Shared\Transfer\MerchantStorageProfileTransfer;
use Generated\Shared\Transfer\MerchantStorageTransfer;

class MerchantsRestApiToMerchantsStorageClientBridge implements MerchantsRestApiToMerchantsStorageClientInterface
{
    /**
     * @param string[] $merchantReferences
     *
     * @return \Generated\Shared\Transfer\MerchantStorageTransfer[]
     */
    public function findByMerchantReference(array $merchantReferences): array
    {
        $merchantStorageProfile = (new MerchantStorageProfileTransfer())
            ->setMerchantUrl('url-test')
            ->setPublicEmail('email-test')
            ->setTermsConditionsGlossaryKey('TermsConditionsGlossaryKey');

        return [
            (new MerchantStorageTransfer())
                ->setIdMerchant(1)
                ->setMerchantReference('MER000006')
                ->setName('Kudu Merchant')
                ->setMerchantStorageProfile($merchantStorageProfile),
        ];
    }

    /**
     * @param string $merchantReference
     *
     * @return \Generated\Shared\Transfer\MerchantStorageTransfer|null
     */
    public function findOneByMerchantReference(string $merchantReference): ?MerchantStorageTransfer
    {
        $merchantStorageProfile = (new MerchantStorageProfileTransfer())
            ->setMerchantUrl('url-test')
            ->setPublicEmail('email-test')
            ->setDescriptionGlossaryKey('merchant.description_glossary_key.1');

        return (new MerchantStorageTransfer())
            ->setIdMerchant(1)
            ->setMerchantReference('MER000006')
            ->setName('Kudu Merchant')
            ->setMerchantStorageProfile($merchantStorageProfile);
    }
}
