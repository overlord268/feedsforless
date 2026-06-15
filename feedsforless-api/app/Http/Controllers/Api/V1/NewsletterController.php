<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Catalog\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SubscribeNewsletterRequest;
use App\Mail\MarketInsightsWelcomeMail;
use App\Models\NewsletterSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Exception;

class NewsletterController extends Controller
{
    /**
     * @unauthenticated
     */
    public function subscribeMarketInsights(SubscribeNewsletterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = strtolower(trim($validated['email']));

        $subscription = NewsletterSubscription::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => trim($validated['name']),
                'source' => 'market_insights',
                'subscribed_at' => now(),
            ]
        );

        try {
            $base = rtrim(config('app.frontend_url', config('app.url')), '/');
            $welcomeQuery = [
                'from' => 'newsletter',
                'email' => $email,
            ];
            $registerUrl = $base . '/register?' . http_build_query($welcomeQuery);
            $catalogUrl = $base . '/catalog?' . http_build_query($welcomeQuery);

            $product = Product::query()
                ->where('status', 'published')
                ->whereNotNull('slug')
                ->inRandomOrder()
                ->first();

            $suggestedName = $product?->name;
            $suggestedUrl = $product
                ? $base . '/products/' . $product->slug . '?' . http_build_query($welcomeQuery)
                : $catalogUrl;

            Mail::to($email)->send(new MarketInsightsWelcomeMail(
                subscription: $subscription,
                suggestedProductName: $suggestedName,
                suggestedProductUrl: $suggestedUrl,
                registerUrl: $registerUrl,
                catalogUrl: $catalogUrl,
            ));
        } catch (Exception $e) {
            report($e);

            return response()->json([
                'message' => 'Subscription saved but we could not send the confirmation email. Please try again later.',
            ], 500);
        }

        return response()->json([
            'message' => 'Subscribed successfully. Check your inbox for confirmation.',
            'data' => [
                'email' => $subscription->email,
            ],
        ], 201);
    }
}
