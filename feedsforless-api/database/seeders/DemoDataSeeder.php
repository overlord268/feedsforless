<?php

namespace Database\Seeders;

use App\Domains\B2B\Models\Address;
use App\Domains\B2B\Models\User;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\HandlingSpec;
use App\Domains\Catalog\Models\MeasureUnit;
use App\Domains\Catalog\Models\NutritionalAnalysis;
use App\Domains\Catalog\Models\NutritionalParameter;
use App\Domains\Catalog\Models\PackagingType;
use App\Domains\Catalog\Models\Parameter;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductPackaging;
use App\Domains\Catalog\Models\RelatedProduct;
use App\Domains\Catalog\Models\Specification;
use App\Domains\Catalog\Models\TestMethod;
use App\Domains\Catalog\Models\TypicalApplication;
use App\Domains\Catalog\Models\VolumePricingTier;
use App\Domains\Conversations\Models\Conversation;
use App\Domains\Conversations\Models\ConversationMessage;
use App\Domains\Quotes\Models\QuoteRequest;
use App\Domains\Quotes\Models\QuoteRequestItem;
use App\Services\Catalog\FflSkuGenerator;
use App\Services\ConversationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', env('SEED_ADMIN_EMAIL', 'admin@feedsforless.com'))->firstOrFail();
        $customer = User::where('email', env('SEED_CLIENT_EMAIL', 'cliente@empresa.com'))->firstOrFail();

        $customer->company?->update([
            'tax_registration_number' => '12-3456789',
            'tax_classification' => 'LLC',
        ]);
        $customer->update(['job_title' => 'Procurement Manager']);

        $address = Address::create([
            'user_id' => $customer->id,
            'address_line_1' => '1200 Industrial Blvd',
            'city' => 'Indianapolis',
            'state' => 'IN',
            'zip_code' => '46204',
        ]);

        $categories = $this->seedCategories();

        $phosphates = $categories['phosphates'];

        $relatedProduct = $this->createCatalogProduct([
            'name' => 'Monocalcium Feed Phosphate',
            'slug' => 'monocalcium-feed-phosphate',
            'grade' => '22.7%',
            'description' => 'High-quality monocalcium phosphate for livestock and poultry mineral programs.',
            'category' => $phosphates,
            'base_price_ref' => 420.00,
            'stock_status' => 'in_stock',
        ]);

        $showcase = $this->createShowcaseProduct($phosphates);

        $this->seedAdditionalCatalogProducts($categories);

        RelatedProduct::create([
            'node_id' => $showcase->id,
            'link_id' => $relatedProduct->id,
            'label' => 'Alternative phosphate source',
        ]);

        $bulkBag = PackagingType::where('slug', 'bulk-bag')->firstOrFail();
        $superSack = PackagingType::where('slug', 'super-sack')->firstOrFail();

        $bulkPackaging = ProductPackaging::create([
            'product_id' => $showcase->id,
            'packaging_type_id' => $bulkBag->id,
            'quantity_per_pallet' => 40,
            'base_price_per_unit' => 385.50,
        ]);

        ProductPackaging::create([
            'product_id' => $showcase->id,
            'packaging_type_id' => $superSack->id,
            'quantity_per_pallet' => 20,
            'base_price_per_unit' => 372.00,
        ]);

        VolumePricingTier::create([
            'product_packaging_id' => $bulkPackaging->id,
            'tier_name' => 'Standard',
            'min_quantity' => 1,
            'max_quantity' => 9,
            'pricing_mode' => 'margin',
            'profit_margin_percent' => 15,
        ]);

        VolumePricingTier::create([
            'product_packaging_id' => $bulkPackaging->id,
            'tier_name' => 'Volume',
            'min_quantity' => 10,
            'max_quantity' => 49,
            'pricing_mode' => 'discount',
            'discount_percentage' => 5,
            'profit_margin_percent' => 12,
        ]);

        VolumePricingTier::create([
            'product_packaging_id' => $bulkPackaging->id,
            'tier_name' => 'Truckload',
            'min_quantity' => 50,
            'max_quantity' => null,
            'pricing_mode' => 'fixed',
            'fixed_price' => 350.00,
            'profit_margin_percent' => 10,
        ]);

        $this->attachSpecsAndNutrition($showcase);

        $pendingQuote = $this->createQuote($customer, $address, $showcase, $bulkBag, [
            'status' => 'pending',
            'qty' => 12,
            'requires_liftgate' => true,
            'requires_appointment' => false,
            'admin_note' => 'Demo RFQ — awaiting pricing from sales.',
        ]);

        $quotedQuote = $this->createQuote($customer, $address, $showcase, $superSack, [
            'status' => 'quoted',
            'qty' => 24,
            'requires_liftgate' => false,
            'requires_appointment' => true,
            'product_cost' => 372.00,
            'freight_cost' => 18.50,
            'admin_note' => 'Demo RFQ — quoted and ready for customer review.',
        ]);

        $acceptedQuote = $this->createQuote($customer, $address, $showcase, $bulkBag, [
            'status' => 'accepted',
            'qty' => 6,
            'requires_liftgate' => true,
            'requires_appointment' => true,
            'product_cost' => 385.50,
            'freight_cost' => 22.00,
            'admin_note' => 'Demo RFQ — accepted by customer.',
        ]);

        $this->seedQuoteChat($pendingQuote, $customer, $admin);
        $this->seedGuestQuote($showcase, $bulkBag);
        $this->seedGeneralChat($customer, $admin);

        $this->command?->info('Demo catalog product, RFQs, and conversations seeded.');
        $this->command?->info("  Categories: {$this->categoryCount()} | Products: {$this->productCount()}");
        $this->command?->info("  Showcase product: {$showcase->name} (#{$showcase->id})");
        $this->command?->info("  Customer RFQs: pending #{$pendingQuote->id}, quoted #{$quotedQuote->id}, accepted #{$acceptedQuote->id}");
    }

    /** @return array<string, Category> */
    private function seedCategories(): array
    {
        $defs = [
            'phosphates' => 'Phosphates',
            'magnesium-oxide' => 'Magnesium Oxide',
            'prilled-urea' => 'Prilled Urea',
            'feed-additives' => 'Feed Additives',
            'fertilizers' => 'Fertilizers',
            'trace-minerals' => 'Trace Minerals',
        ];

        $categories = [];
        foreach ($defs as $slug => $label) {
            $categories[$slug] = Category::create(compact('label', 'slug'));
        }

        return $categories;
    }

    /** @param  array<string, Category>  $categories */
    private function seedAdditionalCatalogProducts(array $categories): void
    {
        $bulkBag = PackagingType::where('slug', 'bulk-bag')->firstOrFail();
        $pallet = PackagingType::where('slug', 'pallet')->firstOrFail();

        $products = [
            [
                'name' => 'Magnesium Oxide',
                'slug' => 'magnesium-oxide-54',
                'grade' => '54% MgO (0.8–3.0 mm)',
                'description' => 'Feed-grade magnesium oxide for ruminant diets and mineral premixes.',
                'category' => $categories['magnesium-oxide'],
                'base_price_ref' => 295.00,
                'stock_status' => 'in_stock',
                'availability' => 'Immediate',
            ],
            [
                'name' => 'Urea',
                'slug' => 'urea-feed-grade',
                'grade' => 'Feed Grade',
                'description' => 'Non-protein nitrogen source for ruminant feed formulations.',
                'category' => $categories['prilled-urea'],
                'base_price_ref' => 510.00,
                'stock_status' => 'call',
                'availability' => 'Lead time 1–2 weeks',
            ],
            [
                'name' => 'Calcium Carbonate',
                'slug' => 'calcium-carbonate',
                'grade' => 'Feed Grade',
                'description' => 'Ground limestone supplement for poultry and swine calcium requirements.',
                'category' => $categories['feed-additives'],
                'base_price_ref' => 185.00,
                'stock_status' => 'in_stock',
                'availability' => 'Immediate',
            ],
            [
                'name' => 'NPK Blend 10-20-10',
                'slug' => 'npk-blend-10-20-10',
                'grade' => 'Standard',
                'description' => 'Balanced crop nutrition blend for row-crop and pasture applications.',
                'category' => $categories['fertilizers'],
                'base_price_ref' => 340.00,
                'stock_status' => 'in_stock',
                'availability' => 'Regional terminals',
            ],
            [
                'name' => 'Trace Mineral Premix',
                'slug' => 'trace-mineral-premix',
                'grade' => 'Standard Grade',
                'description' => 'Zinc, copper, manganese, and selenium premix for complete feed mills.',
                'category' => $categories['trace-minerals'],
                'base_price_ref' => 890.00,
                'stock_status' => 'call',
                'availability' => 'Made to order',
            ],
        ];

        foreach ($products as $index => $row) {
            $product = $this->createCatalogProduct($row);

            if ($index % 2 === 0) {
                ProductPackaging::create([
                    'product_id' => $product->id,
                    'packaging_type_id' => $bulkBag->id,
                    'quantity_per_pallet' => 40,
                    'base_price_per_unit' => $row['base_price_ref'],
                ]);
            } else {
                ProductPackaging::create([
                    'product_id' => $product->id,
                    'packaging_type_id' => $pallet->id,
                    'quantity_per_pallet' => 48,
                    'base_price_per_unit' => $row['base_price_ref'],
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function createCatalogProduct(array $row): Product
    {
        $sku = app(FflSkuGenerator::class)->assignUniqueSkuFromCategories(
            [$row['category']->id],
            $row['name'],
            $row['grade'] ?? null,
        );

        $product = Product::create([
            'sku' => $sku,
            'name' => $row['name'],
            'slug' => $row['slug'] ?? Str::slug($row['name']),
            'grade' => $row['grade'],
            'description' => $row['description'],
            'origin_address' => $row['origin_address'] ?? 'Houston, TX',
            'stock_status' => $row['stock_status'] ?? 'in_stock',
            'availability' => $row['availability'] ?? 'Immediate',
            'status' => 'published',
            'base_price_ref' => $row['base_price_ref'] ?? null,
            'profit_margin_percent' => $row['profit_margin_percent'] ?? 12,
        ]);

        $product->categories()->attach($row['category']->id);

        return $product;
    }

    private function categoryCount(): int
    {
        return Category::count();
    }

    private function productCount(): int
    {
        return Product::count();
    }

    private function createShowcaseProduct(Category $category): Product
    {
        $sdsPath = 'product-documents/sds/demo-dicalcium-phosphate-sds.pdf';
        $tdsPath = 'product-documents/tds/demo-dicalcium-phosphate-tds.pdf';

        Storage::disk('local')->put($sdsPath, "Demo SDS placeholder for Dicalcium Feed Phosphate.\n");
        Storage::disk('local')->put($tdsPath, "Demo TDS placeholder for Dicalcium Feed Phosphate.\n");

        $name = 'Dicalcium Feed Phosphate';
        $grade = '18.5%';
        $sku = app(FflSkuGenerator::class)->assignUniqueSkuFromCategories(
            [$category->id],
            $name,
            $grade,
        );

        $product = Product::create([
            'sku' => $sku,
            'name' => $name,
            'slug' => 'dicalcium-feed-phosphate',
            'grade' => $grade,
            'description' => 'Showcase demo product with full catalog data: specs, nutrition, packaging tiers, handling, and applications. Suitable for end-to-end RFQ and admin pricing tests.',
            'origin_address' => 'Houston, TX — Gulf Coast terminal',
            'stock_status' => 'in_stock',
            'availability' => 'Immediate / 2-week lead on truckloads',
            'lead_time' => now()->addDays(7)->toDateString(),
            'max_lead_time' => now()->addDays(21)->toDateString(),
            'shelf_life_template' => '24 months in original packaging',
            'status' => 'published',
            'base_price_ref' => 385.50,
            'profit_margin_percent' => 15,
            'market_trends_link' => 'https://example.com/market-insights/phosphates',
            'sds_document_path' => $sdsPath,
            'tds_document_path' => $tdsPath,
        ]);

        $product->categories()->attach($category->id);

        $handling = HandlingSpec::whereIn('slug', ['store-in-dry-area', 'avoid-direct-sunlight'])->pluck('id');
        $product->handlingSpecs()->sync($handling);

        $apps = TypicalApplication::whereIn('slug', ['swine-feed', 'poultry-feed', 'ruminant-feed'])->pluck('id');
        $product->typicalApplications()->sync($apps);

        return $product;
    }

    private function attachSpecsAndNutrition(Product $product): void
    {
        $percent = MeasureUnit::where('slug', 'percent')->firstOrFail();
        $phosphorus = Parameter::where('slug', 'phosphorus-p')->firstOrFail();
        $calcium = Parameter::where('slug', 'calcium-ca')->firstOrFail();
        $moisture = Parameter::where('slug', 'moisture')->firstOrFail();
        $aoac = TestMethod::where('slug', 'aoac-official-method')->firstOrFail();
        $nir = TestMethod::where('slug', 'nir-analysis')->firstOrFail();

        Specification::create([
            'product_id' => $product->id,
            'parameter_id' => $phosphorus->id,
            'test_method_id' => $aoac->id,
            'measure_unit_id' => $percent->id,
            'specification' => 'Min. 18.5%',
        ]);

        Specification::create([
            'product_id' => $product->id,
            'parameter_id' => $calcium->id,
            'test_method_id' => $aoac->id,
            'measure_unit_id' => $percent->id,
            'specification' => '20–24%',
        ]);

        Specification::create([
            'product_id' => $product->id,
            'parameter_id' => $moisture->id,
            'test_method_id' => $nir->id,
            'measure_unit_id' => $percent->id,
            'specification' => 'Max. 2.0%',
        ]);

        $pParam = NutritionalParameter::where('slug', 'phosphorus')->firstOrFail();
        $caParam = NutritionalParameter::where('slug', 'calcium')->firstOrFail();

        NutritionalAnalysis::create([
            'product_id' => $product->id,
            'nutritional_parameter_id' => $pParam->id,
            'measure_unit_id' => $percent->id,
            'value' => '18.5',
        ]);

        NutritionalAnalysis::create([
            'product_id' => $product->id,
            'nutritional_parameter_id' => $caParam->id,
            'measure_unit_id' => $percent->id,
            'value' => '22.0',
        ]);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function createQuote(
        User $customer,
        Address $address,
        Product $product,
        PackagingType $packaging,
        array $options,
    ): QuoteRequest {
        $qty = (int) ($options['qty'] ?? 1);
        $productCost = (float) ($options['product_cost'] ?? 0);
        $freightCost = (float) ($options['freight_cost'] ?? 0);
        $lineTotal = ($productCost + $freightCost) * $qty;

        $quote = QuoteRequest::create([
            'request_by_id' => $customer->id,
            'target_address_id' => $address->id,
            'delivery_zip' => $address->zip_code,
            'requires_liftgate' => (bool) ($options['requires_liftgate'] ?? false),
            'requires_appointment' => (bool) ($options['requires_appointment'] ?? false),
            'status' => $options['status'],
            'admin_note' => $options['admin_note'] ?? null,
            'total_estimated_cost' => $lineTotal > 0 ? $lineTotal : null,
        ]);

        QuoteRequestItem::create([
            'quote_request_id' => $quote->id,
            'product_id' => $product->id,
            'packaging_type_id' => $packaging->id,
            'qty' => $qty,
            'estimated_product_cost' => $productCost,
            'estimated_freight_cost' => $freightCost,
            'line_total_cost' => $lineTotal > 0 ? $lineTotal : null,
        ]);

        return $quote;
    }

    private function seedQuoteChat(QuoteRequest $quote, User $customer, User $admin): void
    {
        $conversation = app(ConversationService::class)->findOrCreateForQuote($quote);

        $customerMsg = $conversation->messages()->create([
            'sender_type' => ConversationMessage::SENDER_CUSTOMER,
            'sender_user_id' => $customer->id,
            'message_type' => ConversationMessage::TYPE_TEXT,
            'body' => 'Hi, can you confirm lead time for 12 bulk bags to Indianapolis?',
            'read_by_customer_at' => now(),
        ]);

        $conversation->messages()->create([
            'sender_type' => ConversationMessage::SENDER_ADMIN,
            'sender_user_id' => $admin->id,
            'message_type' => ConversationMessage::TYPE_TEXT,
            'body' => 'Thanks for your RFQ — we are preparing freight options and will update pricing shortly.',
            'read_by_admin_at' => now(),
            'read_by_customer_at' => now(),
        ]);

        $conversation->update(['last_message_at' => $customerMsg->created_at]);
    }

    private function seedGuestQuote(Product $product, PackagingType $packaging): void
    {
        $guestQuote = QuoteRequest::create([
            'request_by_id' => null,
            'guest_email' => 'guest.demo@example.com',
            'guest_company_name' => 'Guest Feed Mill LLC',
            'guest_contact_name' => 'Jane Guest',
            'guest_first_name' => 'Jane',
            'guest_last_name' => 'Guest',
            'guest_phone' => '555-0100',
            'guest_destination_address' => '800 Warehouse Rd, Columbus, OH',
            'guest_tax_id' => '98-7654321',
            'delivery_zip' => '43215',
            'requires_liftgate' => true,
            'requires_appointment' => false,
            'status' => 'pending',
        ]);

        $guestQuote->items()->create([
            'product_id' => $product->id,
            'packaging_type_id' => $packaging->id,
            'qty' => 8,
        ]);
    }

    private function seedGeneralChat(User $customer, User $admin): void
    {
        $conversation = Conversation::create([
            'user_id' => $customer->id,
            'guest_email' => $customer->email,
            'guest_name' => trim("{$customer->first_name} {$customer->last_name}"),
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $conversation->messages()->create([
            'sender_type' => ConversationMessage::SENDER_CUSTOMER,
            'sender_user_id' => $customer->id,
            'message_type' => ConversationMessage::TYPE_TEXT,
            'body' => 'Do you have updated phosphate pricing for Q3?',
            'read_by_customer_at' => now(),
        ]);

        $conversation->messages()->create([
            'sender_type' => ConversationMessage::SENDER_ADMIN,
            'sender_user_id' => $admin->id,
            'message_type' => ConversationMessage::TYPE_TEXT,
            'body' => 'Yes — submit an RFQ from the catalog or check your pending quotes in the portal.',
            'read_by_admin_at' => now(),
            'read_by_customer_at' => now(),
        ]);
    }
}
