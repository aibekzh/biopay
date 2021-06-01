<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repository\FaceTecRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaceMapController extends Controller
{
    protected $repository;
    public function __construct(FaceTecRepository $faceTecRepository)
    {
        $this->repository = $faceTecRepository;
    }

    /**
     * @OA\Post(
     *   path="/api/biometrics/enrollment",
     *   operationId="enrollment",
     *   tags={"FaceMap"},
     *   security={ {"bearer": {} }},
     *   summary="FaceMap",
     *   description="enrollment",
     * @OA\RequestBody(
     *    required=true,
     *    description="enrollment",
     *    @OA\JsonContent(
     *       required={"faceScan","auditTrailImage","lowQualityAuditTrailImage"},
     *       @OA\Property(property="faceScan", type="string"),
     *       @OA\Property(property="auditTrailImage", type="string"),
     *       @OA\Property(property="lowQualityAuditTrailImage", type="string"),
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

    public function enroll(Request $request): JsonResponse
    {
//        return response()->json($this->repository->enroll($request));
        $response = $this->repository->enroll($request);
        if ($response['success']) {
            User::query()
                ->where('id', $request['user_id'])
                ->update(['has_face_map' => true])
            ;

            return response()->json(['success'=>true]);
        } else {

            return response()->json(['success'=>false]);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/biometrics/session",
     *   operationId="session",
     *   tags={"FaceMap"},
     *   security={ {"bearer": {} }},
     *   summary="FaceMap",
     *   description="session",
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
    public function session(Request $request): JsonResponse
    {
        return response()->json($this->repository->getSessionToken($request));
    }

    /**
     * @OA\Post(
     *   path="/api/biometrics/match",
     *   operationId="match",
     *   tags={"FaceMap"},
     *   security={ {"bearer": {} }},
     *   summary="FaceMap",
     *   description="match",
     * @OA\RequestBody(
     *    required=true,
     *    description="match",
     *    @OA\JsonContent(
     *       required={"faceScan","auditTrailImage","lowQualityAuditTrailImage","token"},
     *       @OA\Property(property="faceScan", type="string"),
     *       @OA\Property(property="auditTrailImage", type="string"),
     *       @OA\Property(property="lowQualityAuditTrailImage", type="string"),
     *       @OA\Property(property="token", type="integer"),
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

    public function match(Request $request): JsonResponse
    {
        $attempt = DB::table('pay_attempts')->where('id',$request['token'])->first();
        $senderId = $attempt->user_id;
        $success = $this->repository->match($request,$senderId);
        if ($success){
            $recipientId = $request['user_id'];
            $sender = User::query()->where('id',$senderId)->first();
            $recipient = User::query()->where('id',$recipientId)->first();

            if ($sender->balance >= $attempt->summa){
                    $sender->balance -= $attempt->summa;
                    $recipient->balance +=$attempt->summa;
                    DB::table('transactions')->insert(['sender_id'=>$senderId,'recipient_id'=>$recipientId,'amount'=>$attempt->summa,'date'=>Carbon::now()]);
                    $sender->save();
                    $recipient->save();

                    return response()->json(['success'=>true]);
            }else{
                return response()->json(['success'=>false,'message'=>'Недостаточно баланса'],400);
            }
        }else{
            return response()->json(['success'=>false],401);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/pay",
     *   operationId="pay",
     *   tags={"Payment"},
     *   security={ {"bearer": {} }},
     *   summary="pay",
     *   description="pay",
     * @OA\RequestBody(
     *    required=true,
     *    description="match",
     *    @OA\JsonContent(
     *       required={"phone_number","summa"},
     *       @OA\Property(property="phone_number", type="string"),
     *       @OA\Property(property="summa", type="integer"),
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
     * @throws \Exception
     */

    public function pay(Request $request): JsonResponse
    {
        $user = User::query()->where('email',$request['phone_number'])->first();
        if ($user->has_face_map){
            $id = DB::table('pay_attempts')->insertGetId(['user_id'=>$user->id,'summa'=>$request['summa']]);

            return response()->json(['success'=>true,'token'=>$id]);
        }else{

            return response()->json(['success'=>false,'token'=>null],400);
        }
    }
}
