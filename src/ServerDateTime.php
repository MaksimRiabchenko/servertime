<?php

namespace Maksimriabchenko\Servertime;

use DateTimeImmutable;
use Exception;

abstract class ServerDateTimeErrors {

    /**
     * JSON empty
     */
    const ERROR_JSON_DECODE = 'Error: result empty';

    /**
     * JSON parse error
     */
    const ERROR_PARSE = 'Error: parse result';

    /**
     * Other errors (unknown)
     */
    const ERROR_UNKNOWN = 'Error: unknown';

    /**
     * Error Bad Request
     */
    const ERROR_400 = 'Error: Bad Request';

    /**
     * Error Forbidden
     */
    const ERROR_403 = 'Error: Forbidden';

    /**
     * Error Not Found
     */
    const ERROR_404 = 'Error: Not Found';

    /**
     * Error Request Timeout
     */
    const ERROR_408 = 'Error: Request Timeout';

    /**
     * Error Request Entity Too Large
     */
    const ERROR_413 = 'Error: Request Entity Too Large';

    /**
     * Error Request-URI Too Long
     */
    const ERROR_414 = 'Error: Request-URI Too Long';

    /**
     * Error Internal Server Error
     */
    const ERROR_500 = 'Error: Internal Server Error';

    /**
     * Error Bad Gateway
     */
    const ERROR_502 = 'Error: Bad Gateway';

    /**
     * Error Service Unavailable
     */
    const ERROR_503 = 'Error: Service Unavailable';

    /**
     * Error Gateway Timeout
     */
    const ERROR_504 = 'Error: Gateway Timeout';

    /**
     * Good (no errors))
     */
    const GOOD = TRUE;

    /**
     * Bad (errors))
     */
    const BAD = FALSE;
}

class ServerDateTime extends ServerDateTimeErrors {
    /**
     * API URL
     * @var string
     */
    public $api_url = "https://worldtimeapi.org/api/ip/";

    /**
     * Format that expected in reply from web api
     * @var string
     */
    public $date_time_format = "Y-m-d\TH:i:s.uP";

    /**
     * get DateTime
     * @param string $ip (optional)
     * @return array [ good/bad, DateTime or error ]
     */
    public function getDateTime ($ip = '')
    {
        if ($ip == '') $ip = $_SERVER['SERVER_ADDR'];

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $this->api_url . $ip . '.txt');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

            $data = curl_exec($ch);

            if (!curl_errno($ch)) {
                if (empty($data)) return [self::BAD, self::ERROR_JSON_DECODE];

                switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                    case 200:  # OK
                        $result = explode("\n", $data);
                        $allDataArray = [];
                        foreach ($result as $item) {
                            $tmp = explode(": ", $item);
                            $allDataArray[$tmp[0]] = $tmp[1];
                        }
                        if (empty($allDataArray['datetime'])) return [self::BAD, self::ERROR_JSON_DECODE];

                        $datetime = DateTimeImmutable::createFromFormat($this->date_time_format, $allDataArray['datetime']);
                        if ($datetime === false) return [self::BAD, self::ERROR_PARSE];
                        return [self::GOOD, $datetime];
                    case 400:
                        return [self::BAD, self::ERROR_400];
                    case 403:
                        return [self::BAD, self::ERROR_403];
                    case 404:
                        return [self::BAD, self::ERROR_404];
                    case 408:
                        return [self::BAD, self::ERROR_408];
                    case 413:
                        return [self::BAD, self::ERROR_413];
                    case 414:
                        return [self::BAD, self::ERROR_414];
                    case 500:
                        return [self::BAD, self::ERROR_500];
                    case 502:
                        return [self::BAD, self::ERROR_502];
                    case 503:
                        return [self::BAD, self::ERROR_503];
                    case 504:
                        return [self::BAD, self::ERROR_504];
                    default:
                        return [self::BAD, self::ERROR_UNKNOWN];
                }
            }
            else {
                return [self::BAD, "Error: ".curl_error($ch)."\n"];
            }

            curl_close($ch);
        } catch (Throwable $e) {
            return [self::BAD, 'Error:' . $e->getMessage()];
        } catch (Exception $e) {
            return [self::BAD, 'Error:' . $e->getMessage()];
        }
    }
}