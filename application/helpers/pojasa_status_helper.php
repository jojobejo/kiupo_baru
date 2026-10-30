<?php
defined('BASEPATH') or exit('No direct script access allowed');

function pojasa_statuses()
{
    return array(
        'DRAFT',
        'MENUNGGU_PURCHASING_AWAL',
        'MENUNGGU_KONFIRMASI_PIC',
        'MENUNGGU_KADEP',
        'PENDING_KADEP',
        'REVISI_PIC',
        'REVISI_PURCHASING_KADEP',
        'MENUNGGU_PURCHASING',
        'MENUNGGU_DIRUT_OPS',
        'MENUNGGU_PURCHASING_DIROPS',
        'REVISI_PURCHASING_DIROPS',
        'MENUNGGU_DIREKTUR',
        'REVISI_PURCHASING_DIRUT',
        'MENUNGGU_PENERBITAN_PURCHASING',
        'SPK_TERBIT',
        'ON_PROGRESS',
        'SELESAI',
        'DITUTUP',
        'DITOLAK_KADEP',
        'DITOLAK_DIRUT_OPS',
        'DITOLAK_DIREKTUR',
    );
}

function pojasa_status_is_valid($status)
{
    return in_array(strtoupper(trim((string) $status)), pojasa_statuses(), true);
}

function pojasa_status_is_terminal($status)
{
    return in_array(strtoupper(trim((string) $status)), array(
        'DITUTUP',
        'DITOLAK_KADEP',
        'DITOLAK_DIRUT_OPS',
        'DITOLAK_DIREKTUR',
    ), true);
}

function pojasa_requires_dirut_ops($departemen, $nilaiJasa = null)
{
    $departemen = function_exists('pojasa_normalize_department')
        ? pojasa_normalize_department($departemen)
        : strtoupper(trim((string) $departemen));

    return $departemen === 'IT'
        || $departemen === 'GA'
        || ($nilaiJasa !== null && (float) $nilaiJasa <= 500000);
}

function pojasa_next_status($currentStatus, $action, $departemen, $dirutOpsApproved = false, $nilaiJasa = null)
{
    $currentStatus = strtoupper(trim((string) $currentStatus));
    $action = strtoupper(trim((string) $action));
    $transitions = array(
        'DRAFT' => array('SUBMIT' => 'MENUNGGU_PURCHASING_AWAL'),
        'MENUNGGU_PURCHASING_AWAL' => array(
            'OK' => 'MENUNGGU_KADEP',
            'REVISI' => 'REVISI_PIC',
            // Keep the legacy action for requests already waiting on the old PIC confirmation step.
            'UPDATE' => 'MENUNGGU_KONFIRMASI_PIC',
        ),
        'MENUNGGU_KONFIRMASI_PIC' => array(
            // Purchasing has completed its sole business review before the
            // PIC confirmation.  An accepted confirmation must therefore
            // go straight to the department head; returning it to Purchasing
            // here creates an approval loop and leaves KADEP unreachable.
            'ACC' => 'MENUNGGU_KADEP',
            'REVISI' => 'REVISI_PIC',
        ),
        'MENUNGGU_KADEP' => array(
            'ACC' => 'MENUNGGU_DIRUT_OPS',
            'PENDING' => 'PENDING_KADEP',
            'REVISI' => 'REVISI_PURCHASING_KADEP',
            'REJECT' => 'DITOLAK_KADEP',
        ),
        'PENDING_KADEP' => array(
            'ACC' => 'MENUNGGU_DIRUT_OPS',
            'REVISI' => 'REVISI_PURCHASING_KADEP',
            'REJECT' => 'DITOLAK_KADEP',
        ),
        'REVISI_PIC' => array('SUBMIT' => 'MENUNGGU_PURCHASING_AWAL'),
        'MENUNGGU_PURCHASING' => array('SUBMIT' => 'MENUNGGU_KADEP'),
        'MENUNGGU_DIRUT_OPS' => array(
            'ACC' => 'MENUNGGU_DIREKTUR',
            'REVISI' => 'REVISI_PURCHASING_DIROPS',
            'REJECT' => 'DITOLAK_DIRUT_OPS',
        ),
        'REVISI_PURCHASING_KADEP' => array('SUBMIT' => 'MENUNGGU_KADEP'),
        'REVISI_PURCHASING_DIROPS' => array('SUBMIT' => 'MENUNGGU_DIRUT_OPS'),
        'MENUNGGU_PURCHASING_DIROPS' => array('SUBMIT' => 'MENUNGGU_DIREKTUR'),
        'MENUNGGU_DIREKTUR' => array(
            'ACC' => 'MENUNGGU_PENERBITAN_PURCHASING',
            'REVISI' => 'REVISI_PURCHASING_DIRUT',
            'REJECT' => 'DITOLAK_DIREKTUR',
        ),
        'REVISI_PURCHASING_DIRUT' => array('SUBMIT' => 'MENUNGGU_DIREKTUR'),
        'MENUNGGU_PENERBITAN_PURCHASING' => array('TERBITKAN' => 'SPK_TERBIT'),
        'SPK_TERBIT' => array('PROGRESS' => 'ON_PROGRESS'),
        'ON_PROGRESS' => array('PROGRESS' => 'ON_PROGRESS', 'SELESAI' => 'SELESAI'),
        'SELESAI' => array('TUTUP' => 'DITUTUP'),
    );

    if (in_array($currentStatus, array('MENUNGGU_KADEP', 'PENDING_KADEP'), true) && $action === 'ACC') {
        return pojasa_requires_dirut_ops($departemen, $nilaiJasa) ? 'MENUNGGU_DIRUT_OPS' : 'MENUNGGU_DIREKTUR';
    }

    return isset($transitions[$currentStatus][$action]) ? $transitions[$currentStatus][$action] : false;
}

function pojasa_workflow_stage($status)
{
    $status = strtoupper(trim((string) $status));
    if (in_array($status, array('MENUNGGU_KADEP', 'PENDING_KADEP'), true)) {
        return 'KADEP';
    }
    if (in_array($status, array(
        'MENUNGGU_PURCHASING_AWAL',
        'MENUNGGU_PURCHASING',
        'REVISI_PURCHASING_KADEP',
        'REVISI_PURCHASING_DIROPS',
        'REVISI_PURCHASING_DIRUT',
        'MENUNGGU_PENERBITAN_PURCHASING',
    ), true)) {
        return 'PURCHASING';
    }
    if ($status === 'MENUNGGU_DIRUT_OPS') {
        return 'DIREKTUR_OPERASIONAL';
    }
    if ($status === 'MENUNGGU_KONFIRMASI_PIC') {
        return 'PIC';
    }
    if ($status === 'MENUNGGU_DIREKTUR') {
        return 'DIREKTUR';
    }

    return null;
}

function pojasa_workflow_actions($status)
{
    $status = strtoupper(trim((string) $status));
    if ($status === 'MENUNGGU_KADEP') {
        return array('ACC', 'REVISI', 'PENDING', 'REJECT');
    }
    if ($status === 'PENDING_KADEP') {
        return array('ACC', 'REVISI', 'REJECT');
    }
    if ($status === 'MENUNGGU_PURCHASING_AWAL') {
        return array('OK', 'REVISI');
    }
    if ($status === 'MENUNGGU_PURCHASING') {
        return array('SUBMIT');
    }
    if ($status === 'MENUNGGU_PENERBITAN_PURCHASING') {
        return array('TERBITKAN');
    }
    if (in_array($status, array('REVISI_PURCHASING_KADEP', 'REVISI_PURCHASING_DIROPS', 'REVISI_PURCHASING_DIRUT'), true)) {
        return array('SUBMIT');
    }
    if ($status === 'MENUNGGU_KONFIRMASI_PIC') {
        return array('ACC', 'REVISI');
    }
    if (in_array($status, array('MENUNGGU_DIRUT_OPS', 'MENUNGGU_DIREKTUR'), true)) {
        return array('ACC', 'REVISI', 'REJECT');
    }

    return array();
}
