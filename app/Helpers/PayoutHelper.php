 <?php



namespace App\Helpers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class PayoutHelper
{
    /*
    |--------------------------------------------------------------------------
    | HEADERS
    |--------------------------------------------------------------------------
    */

    public static function headers($signature)
    {
        return [
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: ".env('PAYOUT_AUTH'),
            "signature: ".$signature
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE SIGNATURE
    |--------------------------------------------------------------------------
    */

    public static function generateSignature($url, $payload = null)
    {
        $client = env('CLIENT_KEY');
        $salt = env('SALT_KEY');

        /*
        |--------------------------------------------------------------------------
        | POST API
        |--------------------------------------------------------------------------
        */

        if ($payload) {

            $base64 = base64_encode(
                json_encode($payload)
            );

            return hash(
                'sha256',
                $base64.$url.$client."####".$salt
            );
        }

        /*
        |--------------------------------------------------------------------------
        | GET API
        |--------------------------------------------------------------------------
        */

        return hash(
            'sha256',
            $url.$client."####".$salt
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CURL HIT
    |--------------------------------------------------------------------------
    */

    public static function hitApi(
        $method,
        $url,
        $payload = []
    )
    {
        $signature = self::generateSignature(
            $url,
            $payload
        );

        $curl = curl_init();

        curl_setopt_array($curl, [

            CURLOPT_URL => env('PAYOUT_BASE_URL').$url,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_CUSTOMREQUEST => $method,

            CURLOPT_POSTFIELDS => json_encode($payload),

            CURLOPT_HTTPHEADER => self::headers($signature),

        ]);

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {

            return [
                'status' => false,
                'message' => $err
            ];
        }

        return json_decode($response, true);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE CONTACT
    |--------------------------------------------------------------------------
    */

    public static function createContact($payload)
    {
        $url = "/v1/service/payout/contacts";

        return self::hitApi(
            "POST",
            $url,
            $payload
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET CONTACT
    |--------------------------------------------------------------------------
    */

    public static function getContact($contactId)
    {
        $url = "/v1/service/payout/contacts/".$contactId;

        $signature = self::generateSignature($url);

        $curl = curl_init();

        curl_setopt_array($curl, [

            CURLOPT_URL => env('PAYOUT_BASE_URL').$url,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_CUSTOMREQUEST => "GET",

            CURLOPT_HTTPHEADER => self::headers($signature),

        ]);

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {

            return [
                'status' => false,
                'message' => $err
            ];
        }

        return json_decode($response, true);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE ORDER
    |--------------------------------------------------------------------------
    */

    public static function createOrder($payload)
    {
        $url = "/v1/service/payout/orders";

        return self::hitApi(
            "POST",
            $url,
            $payload
        );
    }
}
