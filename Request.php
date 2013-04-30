<?

namespace pelish8\Requests;

/**
 * Request
 *
 * @package Request
 * @author  pelish8
 * @since   0.1
 */
class Request
{
    /**
     * request url
     *
     * @var string
     * @access public
     */
    public $url = null;

    /**
     * cUrl handle
     *
     * @access protected
     */
    protected $curl = null;

    /**
     * http status code
     *
     * @var int
     * @access protected
     */
    protected $code = 0;

    /**
     * cUrl options
     *
     * @var array
     * @access protected
     */
    protected $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_VERBOSE => true,
        CURLOPT_HEADER => true
    ];

    /**
     * headers in string before parsing
     *
     * @var string
     * @access protected
     */
    protected $headerString = null;

    /**
     * HTTP request response
     *
     * @var string
     * @access protected
     */
    protected $requestBody = null;

    /**
     * array of all headers
     *
     * @var array
     * @access protected
     */
    protected $headers = null;

    /**
     * array of all cookies
     *
     * @var array
     * @access protected
     */
    protected $cookies = null;

    /**
     * error mesage string
     *
     * @var string
     * @access protected
     */
    protected $error = null;

    /**
     * error message number
     *
     * @var int
     * @access protected
     */
    protected $errorNumber = null;

    /**
     * constructor
     *
     * @param string $url
     * @param array $auth authorization credentials key - user name value - password
     */
    public function __construct($url, array $auth = [])
    {
        $this->url = $url;
        $this->auth($auth);

        return $this;
    }

    /**
     * send request
     *
     * @return \pelish8\Requests\Request
     * @access public
     */
    public function send()
    {

        $this->initRequest();


        curl_setopt_array($this->curl, $this->options);

        $response = curl_exec($this->curl);
        var_dump($response);
        if ($response === false) {
            $this->error = curl_error($this->curl);
            $this->errorNumber = curl_errno($this->curl);
        } else {
            $header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);

            $this->headerString = substr($response, 0, $header_size); // get headers

            $this->requestBody = substr($response, $header_size); // body

            $this->code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE); // status code
        }

        curl_close($this->curl);

        return $this;
    }

    /**
     * init curl request
     *
     * @param array $auth authorization credentials key - user name value - password
     * @return \pelish8\Requests\Request
     * @access public
     */
    public function auth(array $auth)
    {

        if (!empty($auth)) {
            $key = key($auth);
            $this->setOption(CURLOPT_USERPWD, $key . ':' . $auth[$key]);
        }
        // error if parameter is messing
        return $this;
    }
    /**
     * init curl request
     *
     * @access protected
     */
    protected function initRequest()
    {
        $this->curl = curl_init();
        // set url
        curl_setopt($this->curl, CURLOPT_URL, $this->url);
    }

    /**
     * set curl options
     *
     * @param int $option cUrl option
     * @param mixed $value option value
     * @access protected
     */
    protected function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * return status code
     *
     * @return int
     * @access public
     */
    public function statusCode()
    {
        return $this->code;
    }

    /**
     * set post curl params
     *
     * @param array $params post request params
     * @return \pelish8\Requests\Request
     * @access public
     */
    public function post(array $params)
    {
        $paramString = $this->paramsToString($params); // postvar1=value1&postvar2=value2&postvar3=value3

        $this->setOption(CURLOPT_POST, true);

        $this->setOption(CURLOPT_POSTFIELDS, $paramString);
        return $this->send();
    }

    /**
     * set get curl prams
     *
     * @param array $params get request params
     * @return \pelish8\Requests\Request
     * @access public
     */
    public function get(array $params)
    {
        $this->setOption(CURLOPT_HTTPGET, true);
        $this->url .= '?' . $this->paramsToString($params);
        return $this->send();
    }

    /**
     * set request URL
     *
     * @param string $url
     * @access public
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * set set custom request method
     *
     * @param string $method set custom HTTP method
     * @access public
     */
    public function setMethod($method)
    {
        $this->setOption(CURLOPT_CUSTOMREQUEST, $method);
    }

    /**
     * convert params array to string
     *
     * @param mixed
     * @access protected
     * @return string
     */
    protected function paramsToString($params)
    {
        $paramString = '';
        $paramString .= http_build_query($params);

        return $paramString;
    }

    /**
     * return response body as text
     *
     * @access public
     * @return string
     */
    public function text()
    {
        return $this->requestBody;
    }

    /**
     * return array of all headers or just one if parameter is set or return false if header does not exists
     *
     * @param string $type header name
     * @access public
     * @return mixed
     */
    public function headers($type = null)
    {
        if ($this->headers === null) {
            $this->headers = $this->parseHeaders($this->headerString);
        }

        if ($type === null) {
            return $this->headers;
        }

        if (array_key_exists($type, $this->headers)) {
            return $this->headers[$type];
        }

        return null;
    }

    /**
     * setrequest headers
     *
     * @param array $headers array of all headers
     * @access public
     */
    public function setHeaders(array $headers)
    {
        $this->setOption(CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * parse header string in to array
     *
     * @param string $headerString header before parsing
     * @access protected
     * @return array
     */
    protected function parseHeaders($headerString)
    {
        if (function_exists('http_parse_headers')) {
            return http_parse_headers($headerString);
        }
        $headers = [];

        $headersArray = explode("\r\n", $headerString);

        foreach ($headersArray as $h) {
            if (empty($h)) {
                continue;
            }

            $header = explode(": ", $h);

            if (!isset($header[1])) {
                $headers['status'] = trim($header[0]);
                continue;
            }

            $key = trim($header[0]);

            if (!array_key_exists($key, $headers)) {
                $headers[$key] = trim($header[1]);
                continue;
            }

            if (is_string($headers[$key])) {
                $headers[$key] = [$headers[$key], trim($header[1])];
            } else {
                $headers[$key][] = trim($header[1]);
            }
        }
        return $headers;
    }

    /**
     * return array of all cookies or just one if parameter is set, return false if cookie does not exists
     *
     * @param string $name cookie name
     * @access public
     * @return mixed
     */
    public function cookies($name = null)
    {
        if ($this->cookies === null) {
            $this->cookies = $this->parseCookies($this->headers('Set-Cookie'));
        }

        if ($name === null) {
            return $this->cookies;
        }

        if (array_key_exists($name, $this->cookies)) {
            return $this->cookies[$name];
        }

        return null;
    }

    /**
     * parse cookies string in to array
     *
     * @param string $cookies cookie before parsing
     * @access protected
     * @return array
     */
    protected function parseCookies($cookies)
    {
        if ($cookies === false) {
            return [];
        }
        $cookieArray = [];
        $arrayName = [
            'domain',
            'expires',
            'path',
            'secure',
            'comment'
        ];

        if (is_string($cookies)) {
            $cookies = [$cookies]; // convert string to array
        }

        foreach ($cookies as $value) {
            echo $value . '<br>';

            $arr = explode(';', $value);

            $name = null;
            foreach ($arr as $val) {

                $d = explode('=', $val);
                if (count($d) !== 2) {
                    continue;
                }

                $key = trim($d[0]);
                $cookieValue = $d[1];

                if($key == 'expires') {

                    $cookieArray[$name][$key] = strtotime($cookieValue);
                } else if (in_array($key, $arrayName)) {

                    $cookieArray[$name][$key] = $cookieValue;
                } else {

                    $name = $key;
                    $cookieArray[$name]['value'] = $cookieValue;
                }
            }
        }
        return $cookieArray;
    }

    /**
     * set cookie for request
     *
     * @param array $cookies array of cookies to set
     * @access public
     */
    public function setCookies(array $cookies)
    {
        $cookieString = '';
        foreach ($cookies as $cookie) {
            $end = substr($cookie, -1);
                if ($end === ';') {
                    $cookieString .= $cookie . ' ';
                } else if ($end === ' ' && substr($cookie, -2) === ';') {
                    $cookieString .= $cookie;
                } else {
                    $cookieString .= $cookie . '; ';
                }
        }
        $this->setOption(CURLOPT_COOKIE, $cookieString);
    }

    public function hasError()
    {
        if ($this->error !== null && $this->error !== '' && $this->errorNumber !== null && $this->errorNumber !== 0) {
            return true;
        }

        return false;
    }

    /**
     * return error array
     *
     * @access public
     * @return array
     */
    public function error()
    {
        return ['number' => $this->errorNumber, 'message' => $this->error];
    }
}
