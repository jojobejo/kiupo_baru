<?php
defined('BASEPATH') or exit('No direct script access allowed');

$portalSsoEnv = function ($name, $default = '') {
    $value = getenv($name);
    return $value === FALSE ? $default : trim((string) $value);
};

$config['portal_sso'] = array(
    // This value must match the app_code registered in KarismaERP Portal.
    'client_id' => $portalSsoEnv('KARISMAERP_SSO_CLIENT_ID', 'kiu_po'),
    // Keep the secret in the web-server environment or a secret manager. Never commit it.
    'client_secret' => $portalSsoEnv('KARISMAERP_SSO_CLIENT_SECRET'),
    'portal_base_url' => rtrim($portalSsoEnv('KARISMAERP_SSO_PORTAL_BASE_URL', 'https://localhost/karismaerp/'), '/') . '/',
    // Must be the exact HTTPS callback registered for this environment.
    'redirect_uri' => $portalSsoEnv('KARISMAERP_SSO_REDIRECT_URI', 'https://10.10.10.26/kiupobaru/sso/callback'),
    // Optional CA bundle path for environments using a private certificate authority.
    'ca_bundle' => $portalSsoEnv('KARISMAERP_SSO_CA_BUNDLE'),
);

// Server-local override is ignored by Git; use this only when environment-backed secret storage is unavailable.
$portalSsoLocalPath = APPPATH . 'config/portal_sso.local.php';
if (is_file($portalSsoLocalPath)) {
    $portalSsoLocal = include $portalSsoLocalPath;
    if (is_array($portalSsoLocal)) {
        $config['portal_sso'] = array_merge($config['portal_sso'], $portalSsoLocal);
    }
}

unset($portalSsoEnv);
unset($portalSsoLocalPath, $portalSsoLocal);
