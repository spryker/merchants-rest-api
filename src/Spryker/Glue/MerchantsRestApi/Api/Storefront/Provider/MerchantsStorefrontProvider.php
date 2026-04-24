<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\MerchantsRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\MerchantsStorefrontResource;
use Generated\Shared\Transfer\MerchantStorageCriteriaTransfer;
use Generated\Shared\Transfer\MerchantStorageProfileTransfer;
use Generated\Shared\Transfer\MerchantStorageTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface;
use Spryker\Client\MerchantStorage\MerchantStorageClientInterface;

class MerchantsStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string URI_VAR_ID = 'merchantReference';

    public function __construct(
        protected MerchantStorageClientInterface $merchantStorageClient,
        protected GlossaryStorageClientInterface $glossaryStorageClient,
    ) {
    }

    protected function provideItem(): ?object
    {
        if (!$this->hasUriVariable(static::URI_VAR_ID)) {
            return null;
        }

        $merchantReference = (string)$this->getUriVariable(static::URI_VAR_ID);

        if ($merchantReference === '') {
            return null;
        }

        $merchantStorageTransfer = $this->merchantStorageClient->findOne(
            (new MerchantStorageCriteriaTransfer())->addMerchantReference($merchantReference),
        );

        if ($merchantStorageTransfer === null) {
            return null;
        }

        $localeName = $this->findLocaleName() ?? $this->getRequest()->getLocale();
        $this->translateProfileGlossaryKeys($merchantStorageTransfer, $localeName);

        return $this->mapMerchantToResource($merchantStorageTransfer, $localeName);
    }

    protected function mapMerchantToResource(MerchantStorageTransfer $merchant, string $localeName): MerchantsStorefrontResource
    {
        $profile = $merchant->getMerchantProfile() ?? new MerchantStorageProfileTransfer();

        $resource = new MerchantsStorefrontResource();
        $resource->merchantReference = $merchant->getMerchantReference();
        $resource->merchantName = $merchant->getName();
        $resource->merchantUrl = $this->findMerchantUrlByLocale($merchant, $localeName);
        $resource->contactPersonRole = $profile->getContactPersonRole();
        $resource->contactPersonTitle = $profile->getContactPersonTitle();
        $resource->contactPersonFirstName = $profile->getContactPersonFirstName();
        $resource->contactPersonLastName = $profile->getContactPersonLastName();
        $resource->contactPersonPhone = $profile->getContactPersonPhone();
        $resource->logoUrl = $profile->getLogoUrl();
        $resource->publicEmail = $profile->getPublicEmail();
        $resource->publicPhone = $profile->getPublicPhone();
        $resource->description = $profile->getDescription();
        $resource->bannerUrl = $profile->getBannerUrl();
        $resource->deliveryTime = $profile->getDeliveryTime();
        $resource->faxNumber = $profile->getFaxNumber();
        $resource->legalInformation = [
            'terms' => $profile->getTermsConditions(),
            'cancellationPolicy' => $profile->getCancellationPolicy(),
            'imprint' => $profile->getImprint(),
            'dataPrivacy' => $profile->getDataPrivacy(),
        ];

        return $resource;
    }

    protected function translateProfileGlossaryKeys(MerchantStorageTransfer $merchant, string $localeName): void
    {
        $profile = $merchant->getMerchantProfile();

        if ($profile === null) {
            return;
        }

        $glossaryKeys = array_filter([
            $profile->getBannerUrlGlossaryKey(),
            $profile->getCancellationPolicyGlossaryKey(),
            $profile->getDataPrivacyGlossaryKey(),
            $profile->getDeliveryTimeGlossaryKey(),
            $profile->getDescriptionGlossaryKey(),
            $profile->getImprintGlossaryKey(),
            $profile->getTermsConditionsGlossaryKey(),
        ]);

        if ($glossaryKeys === []) {
            return;
        }

        $translations = $this->glossaryStorageClient->translateBulk($glossaryKeys, $localeName);

        $profile
            ->setBannerUrl($translations[$profile->getBannerUrlGlossaryKey()] ?? $profile->getBannerUrl())
            ->setCancellationPolicy($translations[$profile->getCancellationPolicyGlossaryKey()] ?? $profile->getCancellationPolicy())
            ->setDataPrivacy($translations[$profile->getDataPrivacyGlossaryKey()] ?? $profile->getDataPrivacy())
            ->setDeliveryTime($translations[$profile->getDeliveryTimeGlossaryKey()] ?? $profile->getDeliveryTime())
            ->setDescription($translations[$profile->getDescriptionGlossaryKey()] ?? $profile->getDescription())
            ->setImprint($translations[$profile->getImprintGlossaryKey()] ?? $profile->getImprint())
            ->setTermsConditions($translations[$profile->getTermsConditionsGlossaryKey()] ?? $profile->getTermsConditions());
    }

    protected function findMerchantUrlByLocale(MerchantStorageTransfer $merchant, string $localeName): ?string
    {
        foreach ($merchant->getUrlCollection() as $urlTransfer) {
            if ($urlTransfer->getLocaleName() === $localeName) {
                return $urlTransfer->getUrl();
            }
        }

        return $merchant->getMerchantUrl();
    }
}
