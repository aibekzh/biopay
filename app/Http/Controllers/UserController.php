<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repository\FaceTecRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    /**
     * @OA\Get (
     *   path="/api/user/balance",
     *   operationId="balance",
     *   tags={"User"},
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
        return response()->json(['balance' => $request->user()->balance]);
    }

    /**
     * @OA\Post(
     *   path="/api/user/balance/top-up",
     *   operationId="top-up",
     *   tags={"User"},
     *   security={ {"bearer": {} }},
     *   summary="top-up",
     *   description="top-up",
     * @OA\RequestBody(
     *    required=true,
     *    description="top-up",
     *    @OA\JsonContent(
     *       required={"amount"},
     *       @OA\Property(property="amount", type="integer"),
     *    ),
     * ),
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
    public function topUp(Request $request): JsonResponse
    {
        $user = $request->user();
        DB::table('top_ups')
          ->insert(['user_id' => $user->id, 'amount' => $request['amount'], 'date' => Carbon::now()])
        ;
        $user->balance += $request['amount'];
        $user->save();

        return response()->json(['success' => true]);
    }

    /**
     * @OA\Get (
     *   path="/api/user/top-up/history",
     *   operationId="top-up/history",
     *   tags={"User"},
     *   security={ {"bearer": {} }},
     *   summary="top-up/history",
     *   description="top-up/history",
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

    public function getTopUpHistory(Request $request): JsonResponse
    {
        $history = DB::table('top_ups')
                     ->where('user_id', $request['user_id'])
                     ->select('id', 'amount', 'date')
                     ->get()
        ;

        return response()->json(['history' => $history]);
    }

    /**
     * @OA\Get (
     *   path="/api/user/payment/history",
     *   operationId="payment/history",
     *   tags={"User"},
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
        $history = DB::table('transactions')
                     ->leftJoin('users', 'recipient_id', 'users.id')
                     ->leftJoin('enterprise_data', 'recipient_id', 'enterprise_data.user_id')
                     ->where('sender_id', $request['user_id'])
                     ->select('transactions.id', 'amount', 'date','users.name as enterprise_name','bank_account_number','biin','bik','address')
                     ->get()
        ;

        return response()->json(['history' => $history]);
    }
}
