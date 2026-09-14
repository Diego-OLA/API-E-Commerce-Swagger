<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class OrderController extends Controller
{
    #[OA\Get(
        path: "/orders",
        summary: "Obtener órdenes del usuario autenticado",
        description: "Devuelve una lista paginada de las órdenes pertenecientes al usuario autenticado, incluyendo sus ítems y pagos.",
        operationId: "getUserOrders",
        tags: ["Órdenes"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista paginada de órdenes",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Order")
                        ),
                        new OA\Property(property: "current_page", type: "integer", example: 1),
                        new OA\Property(property: "per_page", type: "integer", example: 15),
                        new OA\Property(property: "total", type: "integer", example: 3)
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
    public function index()
    {
        return auth()->user()
            ->orders()
            ->with('items.product', 'payments')
            ->latest()
            ->paginate(15);
    }

    #[OA\Get(
        path: "/orders/{order}",
        summary: "Obtener detalle de una orden",
        description: "Muestra la información de una orden específica si pertenece al usuario autenticado.",
        operationId: "getOrderById",
        tags: ["Órdenes"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "order",
                in: "path",
                required: true,
                description: "ID de la orden",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalle de la orden",
                content: new OA\JsonContent(ref: "#/components/schemas/Order")
            ),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 403, description: "Acceso no autorizado a la orden"),
            new OA\Response(response: 404, description: "Orden no encontrada")
        ]
    )]
    public function show(Order $order): JsonResponse
    {
        abort_unless($order->user_id === auth()->id(), 403);

        return response()->json(
            $order->load('items.product', 'payments')
        );
    }

    #[OA\Post(
        path: "/orders",
        summary: "Crear una nueva orden",
        description: "Crea una orden con sus ítems, verifica el stock disponible de los productos y descuenta las existencias dentro de una transacción.",
        operationId: "storeOrder",
        tags: ["Órdenes"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Lista de productos y cantidades a pedir",
            content: new OA\JsonContent(
                required: ["items"],
                properties: [
                    new OA\Property(
                        property: "items",
                        type: "array",
                        items: new OA\Items(
                            required: ["product_id", "quantity"],
                            properties: [
                                new OA\Property(property: "product_id", type: "integer", example: 1),
                                new OA\Property(property: "quantity", type: "integer", example: 2)
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Orden creada exitosamente",
                content: new OA\JsonContent(ref: "#/components/schemas/Order")
            ),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(
                response: 422,
                description: "Error de validación (ej. Stock insuficiente o producto inactivo)",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Stock insuficiente para Camiseta Deportiva."),
                        new OA\Property(
                            property: "errors",
                            type: "object",
                            properties: [
                                new OA\Property(
                                    property: "items",
                                    type: "array",
                                    items: new OA\Items(type: "string", example: "Stock insuficiente para Camiseta Deportiva.")
                                )
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = DB::transaction(function () use ($request) {
            $subtotal = 0;
            $items = [];

            foreach ($request->validated('items') as $data) {
                $product = Product::lockForUpdate()->findOrFail($data['product_id']);

                if (!$product->is_active || $product->stock < $data['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$product->name}.",
                    ]);
                }

                $lineSubtotal = $product->price * $data['quantity'];
                $subtotal += $lineSubtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $data['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $lineSubtotal,
                ];

                $product->decrement('stock', $data['quantity']);
            }

            $order = auth()->user()->orders()->create([
                'status' => 'pending',
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'currency' => 'USD',
            ]);

            $order->items()->createMany($items);

            return $order;
        });

        return response()->json(
            $order->load('items'),
            201
        );
    }
}