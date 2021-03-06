<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Membership;
use Illuminate\Http\Request;
use Gate;
use App\Http\Resources\MembershipResource;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MembershipController extends Controller
{
    /**
     * @OA\Get(
     *      path="/memberships",
     *      operationId="getMembershipesList",
     *      tags={"Membership"},
     *      summary="Get list of Membership",
     *      description="Returns list of Membership",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/MembershipResource")
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
            $membership = Membership::where('status', '=', 'active')->orWhereNull('status')->get();

            return response()
                ->json(
                    HelperClass::responeObject(
                        $membership,
                        true,
                        Response::HTTP_OK,
                        'Successfully fetched.',
                        "Membership are fetched sucessfully.",
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
     *      path="/membership",
     *      operationId="storeMembership",
     *      tags={"Membership"},
     *      summary="Store new Membership",
     *      description="Returns membership data",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/Membership")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Membership")
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
                'limit_of_post' => ['required', 'numeric', 'min:0', 'not_in:0'],
                'transaction_limit' => ['required', 'numeric', 'min:0', 'not_in:0']
            ]);
            if ($validatedData->fails()) {
                return response()
                ->json(
                    HelperClass::responeObject(null, false, Response::HTTP_BAD_REQUEST, "Validation failed check JSON request", "", $validatedData->errors()),
                    Response::HTTP_BAD_REQUEST
                );
            }
            $membership = Membership::where('name', Str::ucfirst($request->name))->where('status', '!=', 'deleted')->first();
            if (!$membership) {
                $input = $request->all();
                $input['name'] = Str::ucfirst($input['name']);
                $membership = new Membership($input);
                $membership->status = "active"; 
                $membership->name=Str::ucfirst($request->name);  
                if ($membership->save()) {
                    return response()
                    ->json(
                        HelperClass::responeObject($membership, true, Response::HTTP_CREATED, "Membership created.", "The membership is saved", ""),
                        Response::HTTP_CREATED
                    );
                } else {
                    return response()
                        ->json(
                            HelperClass::responeObject(null, false, Response::HTTP_INTERNAL_SERVER_ERROR, "Membership couldnt be saved.", "", "The membership couldn't be saved due to internal error"),
                            Response::HTTP_INTERNAL_SERVER_ERROR
                        );
                }
            } else {
                return response()
                    ->json(
                        HelperClass::responeObject($membership, false, Response::HTTP_CONFLICT, 'Membership already exist.', "",  "This memebrship already exist in the database."),
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
                    HelperClass::responeObject(null, false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY, 'Internal server error.', "", $ex->getMessage()),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        }
    }
    /**
     * @OA\Get(
     *      path="/memberships/{id}",
     *      operationId="getMembershipById",
     *      tags={"Memberships"},
     *      summary="Get membership information",
     *      description="Returns membership data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Membership id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Membership")
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
                'name' => ['max:30'],
                'status' => ['max:50'],
                'limit_of_post' => ['numeric', 'min:0', 'not_in:0'],
                'transaction_limit' => ['numeric', 'min:0', 'not_in:0']
            ]);
            if ($validatedData->fails()) {
                return response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_BAD_REQUEST, "Validation failed check JSON request", "", $validatedData->errors()),
                        Response::HTTP_BAD_REQUEST
                    );
            }
            $input = $request->all();
            $memberships = Membership::all();
            if ($memberships->count() <= 0) {
                return response()
                    ->json(
                        HelperClass::responeObject($memberships, true, Response::HTTP_OK, 'List of membership.', "There is no memebrship by this search.", ""),
                        Response::HTTP_OK
                    );
            }
            $col = DB::getSchemaBuilder()->getColumnListing('memberships');
            $requestKeys = collect($request->all())->keys();
            foreach ($requestKeys as $key) {
                if (in_array($key, $col)) {
                    if ($key == 'name') {
                        $input[$key] = Str::ucfirst($input[$key]);
                    }
                    $memberships = $memberships->where($key, $input[$key])->values();
                }
            }
            return response()->json($memberships, 200);
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
     *      path="/memberships/{id}",
     *      operationId="updateMembership",
     *      tags={"Memberships"},
     *      summary="Update existing membership",
     *      description="Returns updated membership data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Membership id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/UpdateMembershipRequest")
     *      ),
     *      @OA\Response(
     *          response=202,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Membership")
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
                
                'name' => ['max:30'],
                'status' => ['max:50'],
                'limit_of_post' => ['numeric', 'min:0', 'not_in:0'],
                'transaction_limit' => ['numeric', 'min:0', 'not_in:0']
            ]);
            if ($validatedData->fails()) {
                return response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_BAD_REQUEST, "Validation failed check JSON request", "", $validatedData->errors()),
                        Response::HTTP_BAD_REQUEST
                    );
            }
            $input = $request->all();
            $membership_to_be_updated = Membership::where('id', $id)->first();
            if (!$membership_to_be_updated) {
                return response()
                        ->json(
                            HelperClass::responeObject(null, false, Response::HTTP_NOT_FOUND, 'Membership doesnt exist.', "This membership doesnt exist in the database.", ""),
                            Response::HTTP_OK
                        );
            }
            if ($request->name) {
                $membership = Membership::where('name', Str::ucfirst($request->name))->first();
                if ($membership) {
                    if($membership->id!=$id){
                    return response()
                    ->json(
                        HelperClass::responeObject($membership, false, Response::HTTP_OK, 'Membership already exist.', "",  "This membership already exist in the database."),
                        Response::HTTP_OK
                    );
                }
                }
                $input['name'] = Str::ucfirst($input['name']);
            }

            if ($membership_to_be_updated->fill($input)->save()) {
                return response()
                    ->json(
                        HelperClass::responeObject($membership_to_be_updated, true, Response::HTTP_CREATED, "Membership updated.", "The membership is updated sucessfully.", ""),
                        Response::HTTP_CREATED
                    );
            } else {
                return response()
                        ->json(
                            HelperClass::responeObject(null, false, Response::HTTP_INTERNAL_SERVER_ERROR, "Membership couldnt be updated.", "", "The membership couldn't be updated due to internal error"),
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
     *      path="/memberships/{id}",
     *      operationId="deleteMembership",
     *      tags={"Membershipess"},
     *      summary="Delete existing membership",
     *      description="Deletes a record and returns no content",
     *      @OA\Parameter(
     *          name="id",
     *          description="Membership id",
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
            $membership = Membership::find($id);
            if (!$membership) {
                response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_NOT_FOUND, "Resource Not Found", '', "Membership by this id doesnt exist."),
                        Response::HTTP_NOT_FOUND
                    );
            }
            $membership->status = 'deleted';
            $membership->save();
            return response()
                ->json(
                    HelperClass::responeObject(null, true, Response::HTTP_OK, 'Successfully deleted.', "Membership is deleted sucessfully.", ""),
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
