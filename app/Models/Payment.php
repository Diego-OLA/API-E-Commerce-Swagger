<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Payment",
    title: "Payment",
    description: "Modelo de Pago procesado",
    required: ["order_id", "provider", "status", "amount", "currency"],
    properties: [
        new OA\Property(property: "id", type: "integer", example: 10),
        new OA\Property(property: "order_id", type: "integer", example: 1),
        new OA\Property(property: "provider", type: "string", example: "stripe"),
        new OA\Property(property: "status", type: "string", example: "succeeded"),
        new OA\Property(property: "amount", type: "integer", example: 2500, description: "Monto en centavos"),
        new OA\Property(property: "currency", type: "string", example: "usd"),
        new OA\Property(property: "stripe_payment_intent_id", type: "string", example: "pi_3MtwBwLkd901xA0Z12345678", nullable: true),
        new OA\Property(property: "stripe_charge_id", type: "string", example: "ch_3MtwBwLkd901xA0Z12345678", nullable: true),
        new OA\Property(property: "metadata", type: "object", nullable: true),
        new OA\Property(property: "paid_at", type: "string", format: "date-time", example: "2026-09-13T20:00:00.000000Z", nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-09-13T20:00:00.000000Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2026-09-13T20:00:00.000000Z")
    ]
)]
class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'provider',
        'status',
        'amount',
        'currency',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'metadata',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'metadata' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}