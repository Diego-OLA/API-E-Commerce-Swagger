<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    #[OA\Get(
        path: "/products",
        summary: "Obtener lista de productos activos",
        description: "Retorna una lista paginada de todos los productos cuyo estado es activo.",
        operationId: "index",
        tags: ["Productos"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista paginada de productos",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Product")
                        ),
                        new OA\Property(property: "current_page", type: "integer", example: 1),
                        new OA\Property(property: "per_page", type: "integer", example: 15),
                        new OA\Property(property: "total", type: "integer", example: 45)
                    ]
                )
            )
        ]
    )]
    public function index()
    {
        return Product::where('is_active', true)->paginate(15);
    }

    #[OA\Get(
        path: "/products/{product}",
        summary: "Obtener detalles de un producto",
        description: "Retorna la información detallada de un producto específico por su ID.",
        operationId: "getProductById",
        tags: ["Productos"],
        parameters: [
            new OA\Parameter(
                name: "product",
                in: "path",
                required: true,
                description: "ID del producto",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalles del producto",
                content: new OA\JsonContent(ref: "#/components/schemas/Product")
            ),
            new OA\Response(
                response: 404,
                description: "Producto no encontrado"
            )
        ]
    )]
    public function show(Product $product)
    {
        return $product;
    }

    #[OA\Post(
        path: "/products",
        summary: "Crear un nuevo producto",
        description: "Almacena un nuevo registro de producto en la base de datos.",
        operationId: "storeProduct",
        tags: ["Productos"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Datos requeridos para crear el producto",
            content: new OA\JsonContent(
                required: ["name", "price"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Camiseta Deportiva"),
                    new OA\Property(property: "price", type: "number", format: "float", example: 25.99),
                    new OA\Property(property: "stock", type: "integer", example: 50),
                    new OA\Property(property: "is_active", type: "boolean", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Producto creado exitosamente",
                content: new OA\JsonContent(ref: "#/components/schemas/Product")
            ),
            new OA\Response(
                response: 401,
                description: "No autenticado"
            ),
            new OA\Response(
                response: 422,
                description: "Error de validación de datos"
            )
        ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        return response()->json(
            Product::create($request->validated()),
            201
        );
    }

    #[OA\Put(
        path: "/products/{product}",
        summary: "Actualizar un producto existente",
        description: "Actualiza los campos de un producto específico en la base de datos.",
        operationId: "updateProduct",
        tags: ["Productos"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "product",
                in: "path",
                required: true,
                description: "ID del producto a actualizar",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Datos del producto a actualizar",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Camiseta Deportiva Pro"),
                    new OA\Property(property: "price", type: "number", format: "float", example: 29.99),
                    new OA\Property(property: "stock", type: "integer", example: 40),
                    new OA\Property(property: "is_active", type: "boolean", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Producto actualizado exitosamente",
                content: new OA\JsonContent(ref: "#/components/schemas/Product")
            ),
            new OA\Response(
                response: 401,
                description: "No autenticado"
            ),
            new OA\Response(
                response: 404,
                description: "Producto no encontrado"
            ),
            new OA\Response(
                response: 422,
                description: "Error de validación de datos"
            )
        ]
    )]
    public function update(
        UpdateProductRequest $request,
        Product $product
    ): JsonResponse {
        $product->update($request->validated());

        return response()->json($product);
    }

    #[OA\Delete(
        path: "/products/{product}",
        summary: "Eliminar un producto",
        description: "Elimina físicamente o deshabilita un producto por su ID.",
        operationId: "destroyProduct",
        tags: ["Productos"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "product",
                in: "path",
                required: true,
                description: "ID del producto a eliminar",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Producto eliminado exitosamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Producto eliminado")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "No autenticado"
            ),
            new OA\Response(
                response: 404,
                description: "Producto no encontrado"
            )
        ]
    )]
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Producto eliminado']);
    }
}