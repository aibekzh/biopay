<?php


namespace App\Repository;


use App\Helpers\HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class FaceTecRepository
{
    private $client;

    public function __construct()
    {
        $this->client = new HttpClient();
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSessionToken(Request $request): array
    {
        try {
            $headers = [
                "X-Device-Key"=>"dLVPXB1kl5m7QGVyCDHiKRdideKJXCA6",
            ];
            $response = $this->client->get("api/v3/biometrics/session-token",[],$headers);
            $data =  json_decode(
                $response->getBody()
                         ->getContents(), true);
            return ['sessionToken'=>$data['sessionToken']];
        }catch (GuzzleException $exception){
            return ['sessionToken'=>null];
        }

    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function enroll(Request $request)
    {
        try {
            $headers  = [
                "X-User-Agent"   => "facetec|sdk|android|com.facetec.sampleapp|INSERT_DEVICE_KEY_HERE|A0BE5209AD702E90|Pixel 4|9.0.13|en_US|en|7e4f518d-f4ae-447a-a6e4-d4143135e7b1",
                "X-Device-Key"   => "dLVPXB1kl5m7QGVyCDHiKRdideKJXCA6",
                "X-FT-IPAddress" => "199.73.53.77",
            ];
            $params   = [
                "externalDatabaseRefID"     => strval($request['user_id']),
                "faceScan"                  => $request['faceScan'],
                "auditTrailImage"           => $request['auditTrailImage'],
                "lowQualityAuditTrailImage" => $request['lowQualityAuditTrailImage'],
            ];
            $response = $this->client->post("api/v3/biometrics/enrollment-3d", $params, $headers);

            $data = json_decode(
                $response->getBody()
                         ->getContents(), true
            );
//            dd($data);

            return ['success' => $data['success']];
        } catch (GuzzleException $exception) {
//            return $exception->getMessage();
//            dd($exception);
            return ['success' => false];
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param                          $userId
     *
     * @return mixed
     */
    public function match(Request $request,$userId): bool
    {
//        return true;
        try{
            $headers = [
                "X-User-Agent"=>"facetec|sdk|android|com.facetec.sampleapp|INSERT_DEVICE_KEY_HERE|A0BE5209AD702E90|Pixel 4|9.0.13|en_US|en|7e4f518d-f4ae-447a-a6e4-d4143135e7b1",
                "X-Device-Key"=>"dLVPXB1kl5m7QGVyCDHiKRdideKJXCA6",
                "X-FT-IPAddress"=>"199.73.53.77",
            ];
            $params = [
                "externalDatabaseRefID"=>strval($userId),
                "faceScan"=>$request['faceScan'],
                "auditTrailImage"=>$request['auditTrailImage'],
                "lowQualityAuditTrailImage"=>$request['lowQualityAuditTrailImage'],
            ];
            $response = $this->client->post("api/v3/biometrics/match-3d-3d",$params,$headers);

            $data = json_decode(
                $response->getBody()
                         ->getContents(), true);

            return  $data['success'];
        }catch (GuzzleException $exception){

            return false;
        }
    }
}
