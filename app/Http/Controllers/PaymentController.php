<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentController extends Controller
{
    #[OA\Post(
        path: "/orders/{order}/payments",
        summary: "Procesar un pago con Stripe para una orden",
        description: "Crea un PaymentIntent en Stripe, ejecuta el cobro y registra la transacción localmente.",
        operationId: "storePayment",
        tags: ["Pagos"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "order",
                in: "path",
                required: true,
                description: "ID de la orden a pagar",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Datos requeridos para procesar el pago",
            content: new OA\JsonContent(
                required: ["payment_method_id"],
                properties: [
                    new OA\Property(
                        property: "payment_method_id",
                        type: "string",
                        example: "pm_card_visa",
                        description: "ID del método de pago generado por Stripe (ej. pm_card_visa en testing)"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Pago procesado exitosamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "payment",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 10),
                                new OA\Property(property: "order_id", type: "integer", example: 1),
                                new OA\Property(property: "provider", type: "string", example: "stripe"),
                                new OA\Property(property: "status", type: "string", example: "succeeded"),
                                new OA\Property(property: "amount", type: "integer", example: 2000),
                                new OA\Property(property: "currency", type: "string", example: "usd"),
                                new OA\Property(property: "stripe_payment_intent_id", type: "string", example: "pi_3MtwBwLkd901xA0Z12345678"),
                                new OA\Property(property: "paid_at", type: "string", format: "date-time", example: "2026-09-13T20:00:00.000000Z")
                            ]
                        ),
                        new OA\Property(property: "client_secret", type: "string", example: "pi_3MtwBwLkd901xA0Z12345678_secret_ABC123")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 403, description: "No autorizado (La orden no pertenece al usuario autenticado)"),
            new OA\Response(response: 422, description: "Error de validación en la solicitud (Falta payment_method_id)")
        ]
    )]
    public function store(
        CreatePaymentRequest $request,
        Order $order
    ): JsonResponse {
        abort_unless($order->user_id === auth()->id(), 403);

        Stripe::setApiKey(config('services.stripe.secret'));

        $intent = PaymentIntent::create([
            'amount' => (int) round($order->total * 100),
            'currency' => strtolower($order->currency),
            'payment_method' => $request->validated('payment_method_id'),
            'confirm' => true,
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'stripe',
            'status' => $intent->status,
            'amount' => $intent->amount,
            'currency' => $intent->currency,
            'stripe_payment_intent_id' => $intent->id,
            'metadata' => $intent->metadata?->toArray(),
            'paid_at' => $intent->status === 'succeeded' ? now() : null,
        ]);

        if ($intent->status === 'succeeded') {
            $order->update(['status' => 'paid']);
        }

        return response()->json([
            'payment' => $payment,
            'client_secret' => $intent->client_secret,
        ], 201);
    }
}