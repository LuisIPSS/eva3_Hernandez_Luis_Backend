<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmpresaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/empresas",
     *     operationId="getEmpresas",
     *     tags={"Empresas"},
     *     summary="Listar empresas activas",
     *     @OA\Parameter(name="tipo_empresa", in="query", required=false,
     *         @OA\Schema(type="string", enum={"contratacion-directa","est","outsourcing"})),
     *     @OA\Parameter(name="page", in="query", required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1), description="Número de página"),
     *     @OA\Parameter(name="per_page", in="query", required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=50, default=15), description="Registros por página"),
     *     @OA\Response(response=200, description="Listado paginado exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Empresa")),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=12),
     *                 @OA\Property(property="last_page", type="integer", example=1)
     *             )
     *         ))
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Empresa::where('activo', true);
        if ($request->has('tipo_empresa')) {
            $query->where('tipo_empresa', $request->input('tipo_empresa'));
        }

        return $this->successResponse($query->paginate($this->perPage($request)));
    }

    /**
     * @OA\Post(
     *     path="/empresas",
     *     operationId="createEmpresa",
     *     tags={"Empresas"},
     *     summary="Registrar nueva empresa",
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/EmpresaInput")),
     *     @OA\Response(response=201, description="Empresa creada",
     *         @OA\JsonContent(ref="#/components/schemas/Empresa")),
     *     @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre_empresa'    => 'required|string|max:255',
            'rut_empresa'       => 'required|string|max:20|unique:empresas,rut_empresa',
            'email'             => 'required|email|unique:empresas,email',
            'logo_url'          => 'nullable|url',
            'rubro'             => 'nullable|string|max:100',
            'tipo_empresa'      => 'required|in:contratacion-directa,est,outsourcing',
            'presentacion'      => 'nullable|string',
            'beneficios'        => 'nullable|array',
            'beneficios.*'      => 'string',
            'contacto_nombre'   => 'required|string|max:100',
            'contacto_email'    => 'required|email',
            'contacto_telefono' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son válidos.', 422, $validator->errors()->toArray());
        }

        return $this->successResponse(Empresa::create($validator->validated()), 201);
    }

    /**
     * @OA\Get(
     *     path="/empresas/{id}",
     *     operationId="getEmpresa",
     *     tags={"Empresas"},
     *     summary="Obtener empresa por ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Empresa encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/Empresa")),
     *     @OA\Response(response=404, description="No encontrada")
     * )
     */
    public function show(string $empresa): JsonResponse
    {
        $model = Empresa::find($empresa);
        if (!$model) {
            return $this->errorResponse('Empresa no encontrada.', 404);
        }
        return $this->successResponse($model);
    }

    /**
     * @OA\Put(
     *     path="/empresas/{id}",
     *     operationId="updateEmpresa",
     *     tags={"Empresas"},
     *     summary="Actualizar empresa",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/EmpresaInput")),
     *     @OA\Response(response=200, description="Empresa actualizada"),
     *     @OA\Response(response=404, description="No encontrada")
     * )
     */
    public function update(Request $request, string $empresa): JsonResponse
    {
        $model = Empresa::find($empresa);
        if (!$model) {
            return $this->errorResponse('Empresa no encontrada.', 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_empresa'    => 'sometimes|string|max:255',
            'rut_empresa'       => 'sometimes|string|max:20|unique:empresas,rut_empresa,' . $model->id,
            'email'             => 'sometimes|email|unique:empresas,email,' . $model->id,
            'logo_url'          => 'nullable|url',
            'rubro'             => 'nullable|string|max:100',
            'tipo_empresa'      => 'sometimes|in:contratacion-directa,est,outsourcing',
            'presentacion'      => 'nullable|string',
            'beneficios'        => 'nullable|array',
            'beneficios.*'      => 'string',
            'contacto_nombre'   => 'sometimes|string|max:100',
            'contacto_email'    => 'sometimes|email',
            'contacto_telefono' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son válidos.', 422, $validator->errors()->toArray());
        }

        $model->update($validator->validated());
        return $this->successResponse($model->fresh());
    }

    /**
     * @OA\Patch(
     *     path="/empresas/{id}/validar",
     *     operationId="validarEmpresa",
     *     tags={"Empresas"},
     *     summary="Validar empresa (solo administración)",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Empresa validada"),
     *     @OA\Response(response=404, description="No encontrada")
     * )
     */
    public function validar(string $empresa): JsonResponse
    {
        $model = Empresa::find($empresa);
        if (!$model) {
            return $this->errorResponse('Empresa no encontrada.', 404);
        }
        $model->update(['validado' => true]);
        return $this->successResponse(['message' => 'Empresa validada exitosamente.', 'data' => $model->fresh()]);
    }

    /**
     * @OA\Delete(
     *     path="/empresas/{id}",
     *     operationId="deleteEmpresa",
     *     tags={"Empresas"},
     *     summary="Desactivar empresa",
     *     description="Desactiva el perfil sin eliminarlo de la base de datos.",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Empresa desactivada"),
     *     @OA\Response(response=404, description="No encontrada")
     * )
     */
    public function destroy(string $empresa): JsonResponse
    {
        $model = Empresa::find($empresa);
        if (!$model) {
            return $this->errorResponse('Empresa no encontrada.', 404);
        }
        $model->update(['activo' => false]);
        return $this->successResponse(['message' => 'Empresa desactivada exitosamente.']);
    }
}
