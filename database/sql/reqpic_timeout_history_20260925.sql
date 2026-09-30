-- Penanda khusus untuk request yang diteruskan Purchasing karena KADEP
-- tidak memberi keputusan dalam lima menit. Jalankan setelah migration
-- reqpic_kadep_timeout_workflow_20260923.sql.
ALTER TABLE `tbpo_req_nk`
    ADD COLUMN `purchasing_timeout_opened_at` datetime DEFAULT NULL AFTER `purchasing_opened_at`,
    ADD KEY `idx_reqpic_timeout_history` (`purchasing_timeout_opened_at`);
