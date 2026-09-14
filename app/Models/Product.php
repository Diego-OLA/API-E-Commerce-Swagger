<?php

namespace App\Models;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Product",
    title: "Product",
    description: "Modelo de Producto",
    required: ["name", "price", "sku"],
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "iPhone 15 Pro"),
        new OA\Property(property: "description", type: "string", example: "Teléfono inteligente de 128GB", nullable: true),
        new OA\Property(property: "sku", type: "string", example: "PROD-12345"),
        new OA\Property(property: "price", type: "number", format: "float", example: 999.99),
        new OA\Property(property: "stock", type: "integer", example: 15),
        new OA\Property(property: "is_active", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-09-13T20:00:00.000000Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2026-09-13T20:00:00.000000Z")
    ]
)]

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'sku',
        'price',
        'stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}