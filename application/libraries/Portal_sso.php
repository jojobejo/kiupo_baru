<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** KarismaERP Portal authorization-code and PKCE client for KIU PO. */
class Portal_sso
{
    private $lastErrorCode = '';

    /** Mengembalikan kategori kegagalan tanpa membocorkan detail respons atau kredensial. */
    public function getLastErrorCode()
    {
        return $this->lastErrorCode;
    }

    public function createAuthorizationRequest($settings)
    {
        if (!is_array($settings)
            || empty($settings['client_id'])
            || !$this->isHttpsUrl(isset($settings['portal_base_url']) ? $settings['portal_base_url'] : '')
            || !$this->isHttpsUrl(isset($settings['redirect_uri']) ? $settings['redirect_uri'] : '')
        ) {
            return FALSE;
        }

        try {
            $state = $this->base64UrlEncode(random_bytes(32));
            $verifier = $this->base64UrlEncode(random_bytes(32));
        } catch (Exception $exception) {
            return FALSE;
        }

        $challenge = $this->base64UrlEncode(hash('sha256', $verifier, TRUE));
        $authorizeUrl = rtrim($settings['portal_base_url'], '/') . '/sso/authorize?'
            . http_build_query(array(
                'client_id' => $settings['client_id'],
                'redirect_uri' => $settings['redirect_uri'],
                'state' => $state,
                'code_challenge' => $challenge,
                'code_challenge_method' => 'S256',
            ), '', '&', PHP_QUERY_RFC3986);

        return array(
            'state' => $state,
            'code_verifier' => $verifier,
            'authorize_url' => $authorizeUrl,
        );
    }

    public function exchangeCode($settings, $code, $verifier, $expectedState)
    {
        $this->lastErrorCode = '';
        if (!extension_loaded('curl')
            || !is_array($settings)
            || empty($settings['client_id'])
            || empty($settings['client_secret'])
            || !$this->isHttpsUrl(isset($settings['portal_base_url']) ? $settings['portal_base_url'] : '')
            || !$this->isHttpsUrl(isset($settings['redirect_uri']) ? $settings['redirect_uri'] : '')
        ) {
            return FALSE;
        }

        $fields = array(
            'client_id' => $settings['client_id'],
            'client_secret' => $settings['client_secret'],
            'code' => $code,
            'redirect_uri' => $settings['redirect_uri'],
            'code_verifier' => $verifier,
        );

        $handle = curl_init(rtrim($settings['portal_base_url'], '/') . '/api/sso/token');
        if ($handle === FALSE) {
            return FALSE;
        }

        curl_setopt_array($handle, array(
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => http_build_query($fields, '', '&', PHP_QUERY_RFC3986),
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'),
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => FALSE,
            CURLOPT_SSL_VERIFYPEER => TRUE,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
        ));

        if (!empty($settings['ca_bundle'])) {
            curl_setopt($handle, CURLOPT_CAINFO, $settings['ca_bundle']);
        }

        $responseBody = curl_exec($handle);
        $httpCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $curlError = curl_errno($handle);
        curl_close($handle);

        if ($responseBody === FALSE || $curlError !== 0 || $httpCode < 200 || $httpCode >= 300) {
            if (in_array($curlError, array(35, 51, 58, 59, 60, 64, 66, 80, 82, 83, 90, 91), TRUE)) {
                $this->lastErrorCode = 'tls';
            } elseif ($curlError !== 0) {
                $this->lastErrorCode = 'network';
            } else {
                $this->lastErrorCode = 'portal_response';
            }
            return FALSE;
        }

        $response = json_decode($responseBody, TRUE);
        if (!is_array($response)
            || empty($response['success'])
            || empty($response['data'])
            || !is_array($response['data'])
            || !isset($response['data']['app_code'])
            || !hash_equals((string) $settings['client_id'], (string) $response['data']['app_code'])
            || !isset($response['data']['state'])
            || !is_string($response['data']['state'])
            || !hash_equals((string) $expectedState, $response['data']['state'])
            || empty($response['data']['user'])
            || !is_array($response['data']['user'])
        ) {
            $this->lastErrorCode = 'portal_response';
            return FALSE;
        }

        $user = $response['data']['user'];
        if (empty($user['sub'])
            || !is_string($user['sub'])
            || strlen($user['sub']) > 191
            || empty($user['username'])
            || !is_string($user['username'])
            || strlen($user['username']) > 255
            || empty($user['auth_source'])
            || !is_string($user['auth_source'])
            || !isset($user['user_id'])
            || !is_numeric($user['user_id'])
            || (int) $user['user_id'] < 1
            || $user['sub'] !== $user['auth_source'] . ':' . (int) $user['user_id']
        ) {
            $this->lastErrorCode = 'portal_response';
            return FALSE;
        }

        return array(
            'sub' => $user['sub'],
            'username' => $user['username'],
            'name' => isset($user['name']) && is_string($user['name']) ? $user['name'] : '',
        );
    }

    private function isHttpsUrl($url)
    {
        if (!is_string($url) || $url === '') {
            return FALSE;
        }

        $parts = parse_url($url);
        return is_array($parts)
            && isset($parts['scheme'], $parts['host'])
            && strtolower($parts['scheme']) === 'https'
            && !isset($parts['user'])
            && !isset($parts['pass'])
            && !isset($parts['fragment']);
    }

    private function base64UrlEncode($value)
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
