-- Request PIC workflow: PIC -> KADEP -> Purchasing.
-- Apply before deploying the matching C_Reqpic/M_Reqpic code.
ALTER TABLE `tbpo_req_nk`
    ADD COLUMN `kadep_submitted_at` datetime DEFAULT NULL AFTER `status`,
    ADD COLUMN `pickup_approval_requested_at` datetime DEFAULT NULL AFTER `kadep_submitted_at`,
    ADD COLUMN `purchasing_opened_at` datetime DEFAULT NULL AFTER `pickup_approval_requested_at`,
    ADD KEY `idx_reqpic_kadep_timeout` (`status`, `kadep_submitted_at`),
    ADD KEY `idx_reqpic_pickup_timeout` (`status`, `pickup_approval_requested_at`);

-- Request lama yang belum pernah diproses Purchasing maupun disetujui KADEP
-- dimasukkan ke inbox KADEP agar tidak hilang dari alur baru.
UPDATE `tbpo_req_nk`
SET `status` = 'ON PROGRESS - BELUM ACC',
    `kadep_submitted_at` = COALESCE(`kadep_submitted_at`, `create_at`)
WHERE TRIM(`status`) = 'ON PROGRESS'
  AND COALESCE(TRIM(`acc_with`), '') = ''
  AND `purchasing_opened_at` IS NULL;
