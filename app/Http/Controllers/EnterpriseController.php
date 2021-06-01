<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnterpriseController extends Controller
{

    /**
     * @OA\Get (
     *   path="/api/data",
     *   operationId="data",
     *   tags={"data"},
     *   security={ {"bearer": {} }},
     *   summary="data",
     *   description="data",
     *     @OA\Response(
     *         response="200",
     *         description="Returns data",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *             )
     *         }
     *     ),
     *)
     **/
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */

    public function getData(Request $request): JsonResponse
    {
//        dd($request['user_id']);
        return response()->json(['data'=>User::with('enterpriseData')->where('id',$request['user_id'])->first()]);
    }

    /**
     * @OA\Get (
     *   path="/api/enterprise/balance",
     *   operationId="balance",
     *   tags={"Enterprise"},
     *   security={ {"bearer": {} }},
     *   summary="balance",
     *   description="balance",
     *     @OA\Response(
     *         response="200",
     *         description="Returns data",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *             )
     *         }
     *     ),
     *)
     **/
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */

    public function getBalance(Request $request): JsonResponse
    {
        return response()->json(['balance'=>$request->user()->balance]);
    }

    /**
     * @OA\Get (
     *   path="/api/enterprise/income/history",
     *   operationId="payment/history",
     *   tags={"Enterprise"},
     *   security={ {"bearer": {} }},
     *   summary="payment/history",
     *   description="payment/history",
     *     @OA\Response(
     *         response="200",
     *         description="Returns data",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *             )
     *         }
     *     ),
     *)
     **/
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */

    public function getPaymentHistory(Request $request): JsonResponse
    {
        $history = DB::table('transactions')->where('recipient_id',$request['user_id'])->select('id','amount','date')->get();

        return response()->json(['history'=>$history]);
    }
}
