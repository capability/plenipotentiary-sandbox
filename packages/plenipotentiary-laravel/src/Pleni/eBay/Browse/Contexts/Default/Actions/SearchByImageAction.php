<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions;

use Plenipotentiary\Laravel\Contracts\Gateway\RestGatewayContract;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Requests\SearchByImageRequest;
use Plenipotentiary\Laravel\Support\Result;

/**
 * Action for visual search using images.
 * 
 * This action enables "search by image" functionality where users
 * can upload a photo to find similar items on eBay.
 */
final class SearchByImageAction
{
    public function __construct(
        private readonly RestGatewayContract $gateway,
    ) {}

    /**
     * Search for items using an image.
     * 
     * @param string $base64Image Base64-encoded image string
     * @param array $options Additional search options:
     *                       - categoryIds: Limit search to specific categories
     *                       - filter: Filter string
     *                       - limit: Items per page
     *                       - sort: Sort order
     */
    public function execute(string $base64Image, array $options = []): Result
    {
        $request = new SearchByImageRequest(
            image: $base64Image,
            categoryIds: $options['categoryIds'] ?? null,
            filter: $options['filter'] ?? null,
            limit: $options['limit'] ?? 50,
            offset: $options['offset'] ?? 0,
            sort: $options['sort'] ?? null,
            aspectFilter: $options['aspectFilter'] ?? null,
            fieldgroups: $options['fieldgroups'] ?? null,
        );

        return $this->gateway->execute($request);
    }

    /**
     * Search by image file path.
     * 
     * Convenience method that reads a file and encodes it.
     */
    public function executeFromFile(string $filePath, array $options = []): Result
    {
        if (!file_exists($filePath)) {
            return Result::err([
                'code' => 'FILE_NOT_FOUND',
                'message' => "Image file not found: {$filePath}",
            ]);
        }

        $imageData = file_get_contents($filePath);
        $base64Image = base64_encode($imageData);

        return $this->execute($base64Image, $options);
    }
}
