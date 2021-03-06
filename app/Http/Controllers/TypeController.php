<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Type;
use App\Models\Category;
use App\Models\Item;
use App\Models\Service;
use Illuminate\Http\Request;
use Gate;
use App\Http\Resources\TypeResource;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TypeController extends Controller
{
    /**
     * @OA\Get(
     *      path="/type",
     *      operationId="getTypeList",
     *      tags={"Type"},
     *      summary="Get list of Type",
     *      description="Returns list of Type",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/TypeResource")
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function index()
    {
        try {
            $type = Type::where('status', '=', 'active')->orWhereNull('status')->get()
                ->each(function ($item, $key) {
                    $item->category;
                });

            return response()
                ->json(
                    HelperClass::responeObject(
                        $type,
                        true,
                        Response::HTTP_OK,
                        'Successfully fetched.',
                        "Types are fetched sucessfully.",
                        ""
                    ),
                    Response::HTTP_OK
                );
        } catch (ModelNotFoundException $ex) { // User not found
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'The model doesnt exist.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        } catch (Exception $ex) { // Anything that went wrong
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'Internal server error.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        }
    }


    /**
     * @OA\Post(
     *      path="/type",
     *      operationId="storeType",
     *      tags={"Type"},
     *      summary="Store new Type",
     *      description="Returns type data",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/Type")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Type")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'name' => ['required', 'max:30'],
                'category_id' => ['required', 'numeric']
            ]);
            if ($validatedData->fails()) {
                return response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_BAD_REQUEST, "Validation failed check JSON request", "", $validatedData->errors()),
                        Response::HTTP_BAD_REQUEST
                    );
            }
            $category = Category::where('id', $request->category_id)->first();
            if (!$category) {
                return response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_BAD_REQUEST, 'Category does not exist.', "",  "A category with this ID doesn't exist in the database.Please select the right category"),
                        Response::HTTP_BAD_REQUEST
                    ); 
            }
            $type = Type::where('name', Str::ucfirst($request->name))
                ->where('category_id', $request->category_id)->where('status', '!=', 'deleted')
                ->first();
            if (!$type) {
                $type = new Type($request->all());
                $type->status = "active";
                $type->name=Str::ucfirst($request->name);
                $type->used_for = $category->used_for; 
                if ($type->save()) {
                    $type->category;
                    return response()
                    ->json(
                        HelperClass::responeObject($type, true, Response::HTTP_CREATED, 'Type created.', "The type is created sucessfully.", ""),
                        Response::HTTP_CREATED
                    );
                } else {
                    return response()
                        ->json(
                            HelperClass::responeObject($category, false, Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal error', "",  "This type couldnt be saved."),
                            Response::HTTP_INTERNAL_SERVER_ERROR
                        );
                }
            } else {
                return response()
                    ->json(
                        HelperClass::responeObject($category, false, Response::HTTP_CONFLICT, 'Type already exist.', "",  "This type already exist in the database."),
                        Response::HTTP_CONFLICT
                    );
            }
        } catch (ModelNotFoundException $ex) { // User not found
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'The model doesnt exist.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        } catch (Exception $ex) { // Anything that went wrong
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'Internal error occured.', "", $ex->getMessage()),
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
        }
    }

    /**
     * @OA\Get(
     *      path="/type/{id}",
     *      operationId="getTypeById",
     *      tags={"Type"},
     *      summary="Get type information",
     *      description="Returns type data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Type id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Type")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function search(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'name' => ['max:50'],
                'category_id' => ['numeric'],
                'status' => ['max:50'],
                'used_for' => ['max:70']
            ]);
            if ($validatedData->fails()) {
                return response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_BAD_REQUEST, "Validation failed check JSON request", "", $validatedData->errors()),
                        Response::HTTP_BAD_REQUEST
                    );
            }
            $input = $request->all();
            $types = Type::all();
            if ($types->count() <= 0) {
                return response()
                    ->json(
                        HelperClass::responeObject($types, true, Response::HTTP_OK, 'List of types.', "There is no type by the search.", ""),
                        Response::HTTP_OK
                    );
            }
            $col = DB::getSchemaBuilder()->getColumnListing('types');
            $requestKeys = collect($request->all())->keys();
            foreach ($requestKeys as $key) { 
                if (in_array($key, $col)) {
                    if ($key == 'name') {
                        $input[$key] = Str::ucfirst($input[$key]);
                    }
                    $types = $types->where($key, $input[$key])->values();
                }
            }
            $types->each(function ($item, $key) {
                $item->category;
                $item->service;
                $item->item;
            });
            return response()
                    ->json(
                        HelperClass::responeObject($types, true, Response::HTTP_OK, 'List of types.', "List of types by this search.", ""),
                        Response::HTTP_OK
                    );
        } catch (ModelNotFoundException $ex) { // User not found
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'The model doesnt exist.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        } catch (Exception $ex) { // Anything that went wrong
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'Internal error occured.', "", $ex->getMessage()),
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
        }
    }

    /**
     * @OA\Put(
     *      path="/type/{id}",
     *      operationId="updateType",
     *      tags={"Type"},
     *      summary="Update existing type",
     *      description="Returns updated type data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Type id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/UpdateTypeRequest")
     *      ),
     *      @OA\Response(
     *          response=202,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Type")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {

            $validatedData = Validator::make($request->all(), [
                'name' => ['max:50'],
                'category_id' => ['numeric'],
                'status' => ['max:50'],
                'used_for' => ['max:70',Rule::in(['item', 'service'])]
            ]);
            if ($validatedData->fails()) {
                return response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_BAD_REQUEST, "Validation failed check JSON request", "", $validatedData->errors()),
                        Response::HTTP_BAD_REQUEST
                    );
            }
            $input = $request->all();
            $type_to_be_updated = Type::where('id', $id)->first();
            if (!$type_to_be_updated) {
                return response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_NOT_FOUND, 'Type doesnt exist.', "This type doesnt exist in the database.", ""),
                        Response::HTTP_OK
                    );
            }
            $item = Item::where('type_id', $id)->get()->count();
            if ($item > 0) {
                return response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_CONFLICT, "Type isn't updated.", "", "Couldn't update the type, items are already registered by this type."),
                        Response::HTTP_CONFLICT
                    );
            }
            $service = Service::where('type_id', $id)->get()->count();
            if ($service > 0) {
                return response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_CONFLICT, "Type isn't updated.", "", "Couldn't update the type, service are already registered by this type."),
                        Response::HTTP_CONFLICT
                    );
            }
            if ($request->name) {
                $type = Type::where('name', Str::ucfirst($request->name))->first();
                if ($type && $request->category_id) {
                    $category_is_same = strcmp($type->category_id, $request->category_id) == 0 ? true : false;
                    if ($category_is_same) {
                        return response()
                            ->json(
                                HelperClass::responeObject($type, false, Response::HTTP_OK, 'Type already exist.', "", "This type already exist in the database."),
                                Response::HTTP_OK
                            );
                    }
                    $input['name'] = Str::ucfirst($input['name']);
                }
            }
            if ($request->category_id || $request->category_id == 0) {
                $category = Category::where('id', $request->category_id)->where('status', 'active')->first();
                if ($category) {
                    $type_to_be_updated->used_for = $category->used_for;
                } else {
                    return response()
                            ->json(
                                HelperClass::responeObject(null, false, Response::HTTP_CONFLICT, 'Category doesnt exist.', "", "A category by this id doesn't exist in the database."),
                                Response::HTTP_CONFLICT
                            );
                }
            }
            if ($type_to_be_updated->fill($input)->save()) {
                $type_to_be_updated->type;
                return response()
                    ->json(
                        HelperClass::responeObject($type_to_be_updated, true, Response::HTTP_CREATED, 'Type updated.', "The type is updated sucessfully.", ""),
                        Response::HTTP_CREATED
                    );
            } else {
                return response()
                    ->json(
                        HelperClass::responeObject($type_to_be_updated, false, Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal error', "", "This type couldnt be updated."),
                        Response::HTTP_INTERNAL_SERVER_ERROR
                    );
            }
        } catch (ModelNotFoundException $ex) { // User not found
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'The model doesnt exist.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        } catch (Exception $ex) { // Anything that went wrong
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'Internal server error.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        }
    }

    /**
     * @OA\Delete(
     *      path="/type/{id}",
     *      operationId="deleteType",
     *      tags={"Type"},
     *      summary="Delete existing type",
     *      description="Deletes a record and returns no content",
     *      @OA\Parameter(
     *          name="id",
     *          description="Type id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            $type = Type::find($id);
            if (!$type) {
                response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_NOT_FOUND, "Resource Not Found", '', "Type by this id doesnt exist."),
                        Response::HTTP_NOT_FOUND
                    );
            }
            $type->status = 'deleted';
            $type->save();
            return response()
                ->json(
                    HelperClass::responeObject(null, true, Response::HTTP_OK, 'Successfully deleted.', "Type is deleted sucessfully.", ""),
                    Response::HTTP_OK
                );
        } catch (ModelNotFoundException $ex) {
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'The model doesnt exist.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        } catch (Exception $ex) { // Anything that went wrong
            return response()
                ->json(
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'Internal error occured.', "", $ex->getMessage()),
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
        }
    }
}
