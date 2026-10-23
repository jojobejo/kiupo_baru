<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 *
 */

class Auth extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Auth/M_Auth');
        $this->load->model('Auth/M_Portal_sso');
        $this->config->load('portal_sso', TRUE);
    }

    function index()
    {
        if ($this->session->userdata('status') === 'is_login' && $this->session->userdata('id')) {
            redirect('dashboard');
            return;
        }

        $data['portal_sso_enabled'] = $this->portalSsoConfigured();
        $showLocalLogin = !$data['portal_sso_enabled']
            || (string) $this->input->get('local', TRUE) === '1'
            || (bool) $this->session->flashdata('sso_failure')
            || $this->session->userdata('portal_sso_auto_disabled') === TRUE;

        if ($data['portal_sso_enabled'] && !$showLocalLogin) {
            redirect('sso/start');
            return;
        }

        $data['show_local_login'] = $showLocalLogin;
        $this->load->view('partial/Auth/head');
        $this->load->view('content/Auth/body', $data);
        $this->load->view('partial/Auth/footer');
    }

    /** Start the portal authorization-code flow. */
    function sso()
    {
        if (!$this->isHttpsRequest()) {
            show_error('Login SSO hanya tersedia melalui HTTPS.', 400, 'Permintaan Tidak Valid');
            return;
        }

        if (!$this->portalSsoConfigured()) {
            $this->ssoFailure('Integrasi SSO belum dikonfigurasi.');
            return;
        }

        if ($this->session->userdata('status') === 'is_login' && $this->session->userdata('id')) {
            redirect('dashboard');
            return;
        }

        $this->session->unset_userdata('portal_sso_auto_disabled');

        $this->load->library('Portal_sso', NULL, 'portal_sso');
        $request = $this->portal_sso->createAuthorizationRequest(
            $this->config->item('portal_sso')
        );

        if ($request === FALSE) {
            $this->ssoFailure('Tidak dapat memulai login SSO. Silakan coba kembali.');
            return;
        }

        $this->session->set_userdata('portal_sso_pending', array(
            'state' => $request['state'],
            'code_verifier' => $request['code_verifier'],
            'created_at' => time(),
        ));

        redirect($request['authorize_url']);
    }

    /** Validate callback state, exchange the one-time code, and create a local session. */
    function sso_callback()
    {
        if (!$this->isHttpsRequest()) {
            show_error('Callback SSO hanya tersedia melalui HTTPS.', 400, 'Permintaan Tidak Valid');
            return;
        }

        $pending = $this->session->userdata('portal_sso_pending');
        $state = $this->input->get('state', TRUE);
        $code = $this->input->get('code', TRUE);

        // Consume state before making the network request to prevent callback replay.
        $this->session->unset_userdata('portal_sso_pending');

        if (!is_array($pending)
            || empty($pending['state'])
            || empty($pending['code_verifier'])
            || empty($pending['created_at'])
            || (time() - (int) $pending['created_at']) > 600
            || !is_string($state)
            || !hash_equals((string) $pending['state'], $state)
        ) {
            $this->ssoFailure('Respons login SSO tidak valid atau sudah kedaluwarsa.');
            return;
        }

        if ($this->input->get('error', TRUE)) {
            $this->ssoFailure('Login SSO dibatalkan atau ditolak oleh portal.');
            return;
        }

        if (!is_string($code) || $code === '' || strlen($code) > 2048) {
            $this->ssoFailure('Portal tidak mengirim authorization code yang valid.');
            return;
        }

        if (!$this->portalSsoConfigured()) {
            $this->ssoFailure('Integrasi SSO belum dikonfigurasi.');
            return;
        }

        $this->load->library('Portal_sso', NULL, 'portal_sso');
        $identity = $this->portal_sso->exchangeCode(
            $this->config->item('portal_sso'),
            $code,
            $pending['code_verifier'],
            $state
        );

        if ($identity === FALSE) {
            $this->ssoFailure('Portal gagal memverifikasi login. Silakan coba kembali.');
            return;
        }

        $user = $this->M_Portal_sso->resolveLocalUser($identity);
        if (!$user) {
            $this->ssoFailure('Akun portal belum dipetakan ke akun KIU PO. Hubungi administrator.');
            return;
        }

        $this->session->sess_regenerate(TRUE);
        $this->session->unset_userdata('portal_sso_auto_disabled');
        $this->session->set_userdata(array(
            'id' => $user->id_user,
            'kode' => $user->kode_user,
            'username' => $user->username,
            'nama_user' => $user->nama_user,
            'departemen' => strtoupper($user->departement),
            'lv' => $user->aksess_lv,
            'is_super_admin' => ((string) $user->aksess_lv === '1' || strtoupper($user->departement) === 'ADMIN' || strtolower($user->username) === 'admin') ? 1 : 0,
            'status' => 'is_login',
            'auth_method' => 'karismaerp_sso',
            'portal_sub' => $identity['sub'],
        ));

        redirect('dashboard');
    }

    private function portalSsoConfigured()
    {
        $settings = $this->config->item('portal_sso');
        return is_array($settings)
            && !empty($settings['client_id'])
            && !empty($settings['client_secret'])
            && !empty($settings['portal_base_url'])
            && !empty($settings['redirect_uri'])
            && extension_loaded('curl');
    }

    private function isHttpsRequest()
    {
        $https = strtolower((string) $this->input->server('HTTPS'));
        return ($https !== '' && $https !== 'off')
            || (string) $this->input->server('SERVER_PORT') === '443';
    }

    private function ssoFailure($message)
    {
        $this->session->set_flashdata('sso_failure', TRUE);
        $this->session->set_flashdata('gagal', $message);
        redirect('Auth');
    }

    function process()
    {
        $username = $this->input->post('user_isi');
        $password = $this->input->post('pass_isi');

        $check_username = $this->M_Auth->cek_username($username)->num_rows();

        if ($check_username > 0) {

            $check_password = $this->M_Auth->cek_password($username);

            foreach ($check_password as $key) {
                if ($key->username == $username && password_verify($password, $key->password)) {
                    $this->session->sess_regenerate(TRUE);
                    $this->session->unset_userdata('portal_sso_auto_disabled');
                    $data_session = array(
                        'id'            => $key->id_user,
                        'kode'          => $key->kode_user,
                        'username'      => $key->username,
                        'nama_user'     => $key->nama_user,
                        'departemen'    => strtoupper($key->departement),
                        'lv'            => $key->aksess_lv,
                        'is_super_admin'=> ((string) $key->aksess_lv === '1' || strtoupper($key->departement) === 'ADMIN' || strtolower($key->username) === 'admin') ? 1 : 0,
                        'status'        => "is_login"
                    );
                    $this->session->set_userdata($data_session);
                    redirect('dashboard');
                } else {
                    $this->session->set_flashdata('sso_failure', TRUE);
                    $this->session->set_flashdata("gagal", "password salah !");
                    redirect('Auth');
                }
            }
        } else {
            $this->session->set_flashdata('sso_failure', TRUE);
            $this->session->set_flashdata("gagal", "username salah");
            redirect('Auth');
        }
    }

    function logout()
    {
        // End only the local KIU PO session. The portal has no cross-application logout endpoint.
        $_SESSION = array();
        $this->session->sess_regenerate(TRUE);
        $this->session->set_userdata('portal_sso_auto_disabled', TRUE);
        $this->session->set_flashdata("logout", "Berhasil Log Out");
        redirect('Auth');
    }
}
