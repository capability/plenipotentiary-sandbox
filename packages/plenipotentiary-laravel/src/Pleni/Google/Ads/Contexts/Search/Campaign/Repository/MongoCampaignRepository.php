<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository;

use Illuminate\Support\Collection;
use MongoDB\Client as MongoClient;
use MongoDB\Collection as MongoCollection;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;

/**
 * MongoDB repository implementation of CampaignRepositoryContract.
 *
 * ⚠️ Intended for production/staging when using MongoDB as a document store.
 */
final class MongoCampaignRepository implements CampaignRepositoryContract
{
    private MongoCollection $collection;

    public function __construct(MongoClient $client)
    {
        $dbName = env('MONGO_DB', 'pleni');
        $this->collection = $client->selectCollection($dbName, 'campaigns');
    }

    public function all(): Collection
    {
        $cursor = $this->collection->find();
        $items = [];
        foreach ($cursor as $doc) {
            $items[] = CampaignCanonicalDTO::fromArray((array) $doc);
        }

        return collect($items);
    }

    public function findById(string $id): ?CampaignCanonicalDTO
    {
        $doc = $this->collection->findOne(['_id' => $id]);

        return $doc ? CampaignCanonicalDTO::fromArray((array) $doc) : null;
    }

    public function findByExternalReference(string $externalRef): ?CampaignCanonicalDTO
    {
        $doc = $this->collection->findOne(['externalId' => $externalRef]);

        return $doc ? CampaignCanonicalDTO::fromArray((array) $doc) : null;
    }

    public function save(CampaignCanonicalDTO $campaign): CampaignCanonicalDTO
    {
        $doc = $campaign->toArray();

        if ($campaign->externalId) {
            $this->collection->updateOne(
                ['externalId' => $campaign->externalId],
                ['$set' => $doc],
                ['upsert' => true]
            );
        } else {
            $campaign->externalId = (string) new \MongoDB\BSON\ObjectId();
            $doc['externalId'] = $campaign->externalId;
            $this->collection->insertOne($doc);
        }

        return $campaign;
    }

    public function delete(string $id): bool
    {
        $result = $this->collection->deleteOne(['externalId' => $id]);

        return $result->getDeletedCount() > 0;
    }
}
