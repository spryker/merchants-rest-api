<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\MerchantsRestApi\Api\Storefront\Reader;

use Generated\Api\Storefront\MerchantsStorefrontResource;
use Generated\Shared\Transfer\MerchantSearchCollectionTransfer;
use Generated\Shared\Transfer\MerchantSearchRequestTransfer;
use Generated\Shared\Transfer\MerchantsStorefrontConditionsTransfer;
use Generated\Shared\Transfer\MerchantsStorefrontCriteriaTransfer;
use Generated\Shared\Transfer\MerchantStorageCriteriaTransfer;
use Generated\Shared\Transfer\MerchantStorageProfileTransfer;
use Generated\Shared\Transfer\MerchantStorageTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\RestLegalInformationTransfer;
use Generated\Shared\Transfer\RestMerchantsAttributesTransfer;
use Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface;
use Spryker\Client\MerchantSearch\MerchantSearchClientInterface;
use Spryker\Client\MerchantStorage\MerchantStorageClientInterface;
use Spryker\Service\Container\Attributes\Plugins;
use Spryker\Service\Serializer\SerializerServiceInterface;

class MerchantReader implements MerchantReaderInterface
{
    protected const string KEY_MERCHANT_SEARCH_COLLECTION = 'MerchantSearchCollection';

    /**
     * @param array<\Spryker\Glue\MerchantsRestApiExtension\Dependency\Plugin\MerchantRestAttributesMapperPluginInterface> $merchantRestAttributesMapperPlugins
     */
    public function __construct(
        protected MerchantStorageClientInterface $merchantStorageClient,
        protected MerchantSearchClientInterface $merchantSearchClient,
        protected GlossaryStorageClientInterface $glossaryStorageClient,
        protected SerializerServiceInterface $serializer,
        #[Plugins(dependencyProviderMethod: 'getMerchantRestAttributesMapperPlugins')]
        protected array $merchantRestAttributesMapperPlugins = [],
    ) {
    }

    public function findMerchantByReference(MerchantsStorefrontCriteriaTransfer $merchantsStorefrontCriteriaTransfer): ?MerchantsStorefrontResource
    {
        $conditions = $merchantsStorefrontCriteriaTransfer->getMerchantsStorefrontConditions() ?? new MerchantsStorefrontConditionsTransfer();
        $merchantReference = (string)$conditions->getMerchantReference();
        $localeName = (string)$conditions->getLocaleName();

        $merchantStorageTransfer = $this->merchantStorageClient->findOne(
            (new MerchantStorageCriteriaTransfer())->addMerchantReference($merchantReference),
        );

        if ($merchantStorageTransfer === null) {
            return null;
        }

        $this->translateProfileGlossaryKeys($merchantStorageTransfer, $localeName);

        return $this->buildResource($merchantStorageTransfer, $localeName);
    }

    /**
     * {@inheritDoc}
     *
     * @return array{resources: array<int, \Generated\Api\Storefront\MerchantsStorefrontResource>, totalCount: int}
     */
    public function findMerchants(MerchantsStorefrontCriteriaTransfer $merchantsStorefrontCriteriaTransfer): array
    {
        $conditions = $merchantsStorefrontCriteriaTransfer->getMerchantsStorefrontConditions() ?? new MerchantsStorefrontConditionsTransfer();
        $localeName = (string)$conditions->getLocaleName();

        $paginationTransfer = $merchantsStorefrontCriteriaTransfer->getPagination() ?? new PaginationTransfer();

        $searchResult = $this->merchantSearchClient->search(
            (new MerchantSearchRequestTransfer())
                ->setRequestParameters($this->buildMerchantSearchRequestParameters($paginationTransfer)),
        );

        $merchantSearchCollectionTransfer = $searchResult[static::KEY_MERCHANT_SEARCH_COLLECTION] ?? null;

        if (!$merchantSearchCollectionTransfer instanceof MerchantSearchCollectionTransfer) {
            return ['resources' => [], 'totalCount' => 0];
        }

        $merchantStorageTransfers = $this->fetchMerchantsByIds($this->extractMerchantIds($merchantSearchCollectionTransfer));

        if ($merchantStorageTransfers === []) {
            return ['resources' => [], 'totalCount' => $merchantSearchCollectionTransfer->getNbResults() ?? 0];
        }

        $resources = [];

        foreach ($merchantStorageTransfers as $merchantStorageTransfer) {
            $this->translateProfileGlossaryKeys($merchantStorageTransfer, $localeName);
            $resources[] = $this->buildResource($merchantStorageTransfer, $localeName);
        }

        return [
            'resources' => $resources,
            'totalCount' => $merchantSearchCollectionTransfer->getNbResults() ?? 0,
        ];
    }

    protected function buildResource(MerchantStorageTransfer $merchantStorageTransfer, string $localeName): MerchantsStorefrontResource
    {
        $restMerchantsAttributesTransfer = $this->mapMerchantStorageToRestMerchantsAttributesTransfer(
            $merchantStorageTransfer,
            new RestMerchantsAttributesTransfer(),
            $localeName,
        );

        foreach ($this->merchantRestAttributesMapperPlugins as $merchantRestAttributesMapperPlugin) {
            $restMerchantsAttributesTransfer = $merchantRestAttributesMapperPlugin->mapMerchantStorageTransferToRestMerchantsAttributesTransfer(
                $merchantStorageTransfer,
                $restMerchantsAttributesTransfer,
                $localeName,
            );
        }

        $merchantsStorefrontResource = $this->serializer->denormalize(
            $restMerchantsAttributesTransfer->toArray(true, true),
            MerchantsStorefrontResource::class,
        );

        $merchantsStorefrontResource->merchantReference = $merchantStorageTransfer->getMerchantReference();

        return $merchantsStorefrontResource;
    }

    protected function mapMerchantStorageToRestMerchantsAttributesTransfer(
        MerchantStorageTransfer $merchantStorageTransfer,
        RestMerchantsAttributesTransfer $restMerchantsAttributesTransfer,
        string $localeName,
    ): RestMerchantsAttributesTransfer {
        $merchantStorageProfileTransfer = $merchantStorageTransfer->getMerchantProfile() ?? new MerchantStorageProfileTransfer();

        $restLegalInformationTransfer = (new RestLegalInformationTransfer())
            ->setCancellationPolicy($merchantStorageProfileTransfer->getCancellationPolicy())
            ->setDataPrivacy($merchantStorageProfileTransfer->getDataPrivacy())
            ->setImprint($merchantStorageProfileTransfer->getImprint())
            ->setTerms($merchantStorageProfileTransfer->getTermsConditions());

        return $restMerchantsAttributesTransfer
            ->fromArray($merchantStorageTransfer->toArray(), true)
            ->fromArray($merchantStorageProfileTransfer->toArray(), true)
            ->setMerchantName($merchantStorageTransfer->getName())
            ->setLegalInformation($restLegalInformationTransfer)
            ->setBannerUrl($merchantStorageProfileTransfer->getBannerUrl())
            ->setDescription($merchantStorageProfileTransfer->getDescription())
            ->setDeliveryTime($merchantStorageProfileTransfer->getDeliveryTime())
            ->setMerchantUrl($this->findMerchantUrlByLocale($merchantStorageTransfer, $localeName));
    }

    /**
     * @param array<int, int> $merchantIds
     *
     * @return array<int, \Generated\Shared\Transfer\MerchantStorageTransfer>
     */
    protected function fetchMerchantsByIds(array $merchantIds): array
    {
        if ($merchantIds === []) {
            return [];
        }

        $merchantStorageTransfers = $this->merchantStorageClient->get(
            (new MerchantStorageCriteriaTransfer())->setMerchantIds($merchantIds),
        );

        $byId = [];

        foreach ($merchantStorageTransfers as $merchantStorageTransfer) {
            $byId[$merchantStorageTransfer->getIdMerchant()] = $merchantStorageTransfer;
        }

        $ordered = [];

        foreach ($merchantIds as $idMerchant) {
            if (isset($byId[$idMerchant])) {
                $ordered[] = $byId[$idMerchant];
            }
        }

        return $ordered;
    }

    /**
     * @return array<int, int>
     */
    protected function extractMerchantIds(MerchantSearchCollectionTransfer $merchantSearchCollectionTransfer): array
    {
        $merchantIds = [];

        foreach ($merchantSearchCollectionTransfer->getMerchants() as $merchantSearchTransfer) {
            $idMerchant = $merchantSearchTransfer->getIdMerchant();

            if ($idMerchant === null) {
                continue;
            }

            $merchantIds[] = $idMerchant;
        }

        return $merchantIds;
    }

    /**
     * @return array<string, int>
     */
    protected function buildMerchantSearchRequestParameters(PaginationTransfer $paginationTransfer): array
    {
        return [
            PaginationTransfer::LIMIT => $paginationTransfer->getLimitOrFail(),
            PaginationTransfer::OFFSET => $paginationTransfer->getOffsetOrFail(),
        ];
    }

    protected function translateProfileGlossaryKeys(MerchantStorageTransfer $merchantStorageTransfer, string $localeName): void
    {
        $profile = $merchantStorageTransfer->getMerchantProfile();

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

    protected function findMerchantUrlByLocale(MerchantStorageTransfer $merchantStorageTransfer, string $localeName): ?string
    {
        foreach ($merchantStorageTransfer->getUrlCollection() as $urlTransfer) {
            if ($urlTransfer->getLocaleName() === $localeName) {
                return $urlTransfer->getUrl();
            }
        }

        return $merchantStorageTransfer->getMerchantUrl();
    }
}
