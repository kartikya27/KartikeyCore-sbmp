<?php

namespace Kartikey\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Stegback\Product\Repository\ProductRepository;
use Stegback\Api\Helpers\ApiResponseHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stegback\Category\Services\TagServices;

class ProcessProductDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    protected $sku;
    protected $productRepository;
    protected $tagService;
    protected $apiResponseHelper;


    public function __construct($sku)
    {
        $this->sku = $sku;
    }

    /**
     * Execute the job.
     * todo add try catch because if product has any error in price or in variation then product is already created.
     */

    public function handle(ProductRepository $productRepository, ApiResponseHelper $apiResponseHelper,TagServices $tagService): void
    {
        try {
            $this->productRepository = $productRepository;
            $this->apiResponseHelper = $apiResponseHelper;
            $this->tagService = $tagService;

            $buffer = \Kartikey\Core\Models\UnifiedBuffer::where('type', 'product_data')
                ->where('status', 0)
                ->first();

            if (!$buffer) {
                return;
            }

            $data = $buffer->data;

            // Check if the product with the given SKU already exists
            if ($this->productRepository->findWhere(['sku' => $data['item']['sku']])->first()) {
                Log::info('Product already exists, skipping processing', ['sku' => $data['item']['sku']]);
                return;
            }

            // Start database transaction
            DB::beginTransaction();

            $productData = $this->productRepository->prepareProduct($data);

            $product = $this->productRepository->create($productData);
            $productId = $product->id;

            // Process and save images
            $productImagesData = $this->productRepository->prepareProductImageTableData($data, $productId);
            $this->productRepository->createProductImage($productImagesData);

            $productCategoryData = $this->productRepository->prepareProductCategoryData($data, $productId);

            $this->productRepository->createProductCategory($productCategoryData);

            $productTagsData = $this->productRepository->prepareProductTagsData($data, $product);
            // $this->productRepository->createProductTag($productTagsData);


            $sellerCategoryData = $this->productRepository->prepareSellerCategoryData($data);
            $this->productRepository->createSellerCategory($sellerCategoryData);

            $sellerBrandData = $this->productRepository->prepareSellerBrandData($data);
            $this->productRepository->createSellerBrand($sellerBrandData);

            // Process and save descriptions
            $productDescriptionsData = $this->productRepository->prepareProductDescriptionTableData($data, $productId);
            $this->productRepository->createProductDescription($productDescriptionsData);

            // Process and save pricing
            $productPriceData = $this->productRepository->prepareProductPriceData($data, $productId);
            $this->productRepository->createProductPrice($productPriceData);

            $tags = $this->tagService->generateTags($product->title, $product->description, $request->attributes ?? []);
            $this->tagService->attachTagsToProduct($product, $tags);

            $productTaxData = $this->productRepository->prepareProductTaxData($data, $productId);
            $this->productRepository->createProductTax($productTaxData);

            if ($product->type == 'composite') {
                $productAttribute = $this->productRepository->prepareProductAttributeData($data, $productId);
                $this->productRepository->createProductAttribute($productAttribute);

                $productVariation = $this->productRepository->prepareProductVariationData($data, $productId);
                $this->productRepository->createProductVariation($productVariation);
            }

            // Mark the buffer as processed
            $buffer->update(['status' => 1]);
            $buffer->delete();
            Log::info('Buffer updated to processed', ['bufferId' => $buffer->id]);

            // Commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            Log::error('Error processing product', [
                'errorMessage' => $e->getMessage(),
                'stackTrace' => $e->getTraceAsString(),
                'data' => $data ?? null
            ]);
            throw $e; // Re-throw the exception if needed
        }
    }

}
