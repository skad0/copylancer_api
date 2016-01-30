<?php
/**
 * Class CopylancerBasic
 * To see API method reference: http://doc.copylancer.ru/
 */
class CopylancerBasic
{
    const BASE_URL = 'http://api.copylancer.ru/core/api/{NAMESPACE}.{METHOD}';
    /* @todo think about privacy */
    const NAME = 'your login';
    const PASSWORD = 'your password';

    protected $allowedRequestMethods = ['POST', 'GET'];
    protected $cookies = '';
    /**
     * CopylancerBasic constructor.
     * @param string $name
     * @param string $password
     */
    public function __construct($name = self::NAME, $password = self::PASSWORD)
    {
        $this->auth($name, $password);
    }

    /**
     * Call auth method to use
     * @param $name
     * @param $password
     */
    private function auth($name, $password)
    {
        $this->invokeMethod('Users', 'auth', 'GET', ['name' => $name, 'password' => $password]);
    }

    /**
     * Check is request method allowed
     * @param $method
     * @return bool
     */
    private function isMethodAllowed($method)
    {
        return in_array($method, $this->allowedRequestMethods);
    }

    /**
     * Accept namespace and method to url
     * @param $namespace
     * @param $method
     * @return mixed
     */
    private function makeUrl($namespace, $method)
    {
        return str_replace(['{NAMESPACE}', '{METHOD}'], [$namespace, $method], self::BASE_URL);
    }

    /**
     * Return parameters string
     * @param array $params
     * @return string
     */
    private function getParametersString($params = [])
    {
        if (empty($params)) {
            return '';
        }
        return '?' . http_build_query($params);
    }

    /**
     * Filter only cookies from headers
     * @param $headers
     * @return string
     */
    private function getCookiesFromHeader($headers)
    {
        if (empty($headers)) return '';
        $filtered = array_filter($headers, function ($item) {
            return preg_match('/Set-Cookie:/i', $item);
        });
        $result = "";
        foreach ($filtered as $item) {
            $result .= str_replace('Set-Cookie', 'Cookie', $item) . "\r\n";
        }
        return $result;
    }

    /**
     * Protect from cloning
     */
    protected function __clone(){}

    /**
     * Set instance cookie variable
     * @param $headers
     */
    protected function setLocalCookies($headers)
    {
        $this->cookies = $this->getCookiesFromHeader($headers);
    }

    /**
     * Send request and return json decoded response
     * @param $url
     * @param $params
     * @param $type
     * @return array|mixed
     */
    protected function getResponse($url, $params, $type)
    {
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\nAccess-Control-Allow-Credentials: true\r\n" . $this->cookies,
                'method'  => $type,
                'content' => http_build_query($params),
            ],
        ];
        $context = stream_context_create($options);
        $handle = fopen($url, 'r', false, $context);
        $result = stream_get_contents($handle);
        $this->setLocalCookies(stream_get_meta_data($handle)['wrapper_data']);
        fclose($handle);
        if ($result === FALSE)
            return ['status' => 'Request failed'];
        return json_decode($result);
    }

    /**
     * Invoke method and get response
     * @param string $namespace
     * @param string $method
     * @param string $type
     * @param array $params
     * @return object|bool
     */
    public function invokeMethod($namespace, $method, $type = 'GET', $params = [])
    {
        if (!$this->isMethodAllowed($type))
            $type = 'GET';
        $url = $this->makeUrl($namespace, $method) . $this->getParametersString($params);
        dump($url);
        $result = $this->getResponse($url, $params, $type);
        dump($result);
        if ($result->status !== 'ok')
            return false;
        return $result->response;
    }
}