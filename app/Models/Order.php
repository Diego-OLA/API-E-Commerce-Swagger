<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Payment;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Order",
    title: "Order",
    description: "Modelo de Orden de compra",
    required: ["user_id", "status", "subtotal", "total", "currency"],
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "user_id", type: "integer", example: 5),
        new OA\Property(property: "status", type: "string", example: "pending", enum: ["pending", "paid", "cancelled", "completed"]),
        new OA\Property(property: "subtotal", type: "number", format: "float", example: 49.98),
        new OA\Property(property: "total", type: "number", format: "float", example: 49.98),
        new OA\Property(property: "currency", type: "string", example: "USD"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-09-13T20:00:00.000000Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2026-09-13T20:00:00.000000Z")
    ]
)]

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'subtotal',
        'total',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}