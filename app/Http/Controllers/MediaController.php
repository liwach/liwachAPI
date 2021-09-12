<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\MediaResource;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    { 
            try{
                $medias=Media::all();
                return response()
                ->json(HelperClass::responeObject(
                                    $medias,true, Response::HTTP_OK,'Successfully fetched.',"Medias are fetched sucessfully.","")
                                    , Response::HTTP_OK);
            } catch (ModelNotFoundException $ex) { // User not found
                return response()
                ->json( HelperClass::responeObject(null,false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY,'The model doesnt exist.',"",$ex->getMessage())
                  , Response::HTTP_UNPROCESSABLE_ENTITY);
            } catch (Exception $ex) { // Anything that went wrong
                return response()
                ->json( HelperClass::responeObject(null,false, RESPONSE::HTTP_UNPROCESSABLE_ENTITY,'Internal server error.',"",$ex->getMessage())
                , Response::HTTP_UNPROCESSABLE_ENTITY);
                   
            }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $serviceMedia = $request->url;
        foreach ($serviceMedia as $m) {
            //check if the sent type id is in there 
            $media = new Media();
            $media->type = $request->type;
            $media->url = $m;
            $media->item_id = $request->item_id;
            if (!$media->save()) {
                return response()
                    ->json("The media $media resource couldn't be saved due to internal error", Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        return (new MediaResource(Media::where('item_id', $request->item_id)->get()))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
    public function search(Request $request)
    {
        $input = $request->all();
        $medias = Media::all();
        $col = DB::getSchemaBuilder()->getColumnListing('medias');
        $requestKeys = collect($request->all())->keys();
        foreach ($requestKeys as $key) {
            if (empty($medias)) {
                return response()->json($medias, 200);
            }
            if (in_array($key, $col)) {
                $medias = $medias->where($key, $input[$key])->values();
            }
        }
        $medias->each(function ($flag, $key) {
            $flag->reason;
            $flag->flagged_by;
            $flag->flagged_item;
        });
        return response()->json($medias, 200);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Media  $media
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'item_id' => ['numeric'],
                'type' => ['max:10']
            ]);
            if ($validatedData->fails()) {
                return response()
                ->json( HelperClass::responeObject(null,false, Response::HTTP_BAD_REQUEST,"Validation failed check JSON request","",$validatedData->errors())
                , Response::HTTP_BAD_REQUEST);
            }
        $input = $request->all();
        $media = Media::where('id', $id)->first();
        if ($media->fill($input)->save()) {

            return (new MediaResource($media))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } else {
            return response()
                ->json("This resource couldn't be saved due to internal error", Response::HTTP_INTERNAL_SERVER_ERROR);
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Media  $media
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $media = Media::find($id);
            if (!$media) {
                response()
                    ->json(
                        HelperClass::responeObject(null, false, Response::HTTP_NOT_FOUND, "Resource Not Found", '', "Media by this id doesnt exist."),
                        Response::HTTP_NOT_FOUND
                    );
            }
            $media->delete();
            return response()
                ->json(
                    HelperClass::responeObject(null, true, Response::HTTP_NO_CONTENT, 'Successfully deleted.', "Media is deleted sucessfully.", ""),
                    Response::HTTP_NO_CONTENT
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
