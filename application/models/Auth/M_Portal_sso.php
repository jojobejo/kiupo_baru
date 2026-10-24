<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Links a verified portal subject to an existing local KIU PO account. */
class M_Portal_sso extends CI_Model
{
    public function resolveLocalUser($identity)
    {
        if (!is_array($identity) || empty($identity['sub']) || empty($identity['username'])) {
            return FALSE;
        }

        $mapped = $this->db->get_where('tbpo_sso_identity', array('external_sub' => $identity['sub']))->row();
        if ($mapped) {
            $user = $this->localUserById($mapped->id_user);
            return $this->hasSupportedAccessLevel($user) ? $user : FALSE;
        }

        // Accounts are pre-provisioned locally; role and department never come from the portal.
        $this->db->where('username', $identity['username']);
        $users = $this->db->get('tbpo_user')->result();
        if (count($users) !== 1) {
            return FALSE;
        }

        $user = $users[0];
        if (!$this->hasSupportedAccessLevel($user)) {
            return FALSE;
        }

        $this->db->trans_begin();
        $inserted = $this->db->insert('tbpo_sso_identity', array(
            'id_user' => $user->id_user,
            'external_sub' => $identity['sub'],
        ));

        if ($inserted && $this->db->trans_status()) {
            $this->db->trans_commit();
            return $user;
        }

        $this->db->trans_rollback();
        // A concurrent first login can win the unique mapping race. Accept only that same subject.
        $mapped = $this->db->get_where('tbpo_sso_identity', array('external_sub' => $identity['sub']))->row();
        if ($mapped && (int) $mapped->id_user === (int) $user->id_user) {
            $user = $this->localUserById($mapped->id_user);
            return $this->hasSupportedAccessLevel($user) ? $user : FALSE;
        }

        return FALSE;
    }

    /** Dashboard and its menu routes are defined for levels 1 through 6 only. */
    private function hasSupportedAccessLevel($user)
    {
        return is_object($user)
            && isset($user->id_user, $user->username, $user->kode_user, $user->aksess_lv)
            && (int) $user->id_user > 0
            && (string) $user->username !== ''
            && (string) $user->kode_user !== ''
            && in_array((int) $user->aksess_lv, array(1, 2, 3, 4, 5, 6), TRUE);
    }

    private function localUserById($idUser)
    {
        return $this->db->get_where('tbpo_user', array('id_user' => $idUser))->row();
    }
}
