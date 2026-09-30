-- Rename aman untuk instalasi yang sudah memakai nama tabel LIFO lama.
-- Tidak melakukan apa pun pada instalasi baru, karena foundation akan membuat
-- tbpo_stock_lifo_batch_nk secara langsung.
SET @lifo_migbatch_lama_ada := (
  SELECT COUNT(*) FROM information_schema.tables
  WHERE table_schema = DATABASE() AND table_name = 'tbpo_stock_lifo_batch'
);
SET @lifo_batch_baru_ada := (
  SELECT COUNT(*) FROM information_schema.tables
  WHERE table_schema = DATABASE() AND table_name = 'tbpo_stock_lifo_batch_nk'
);
SET @lifo_batch_rename_sql := IF(
  @lifo_batch_lama_ada = 1 AND @lifo_batch_baru_ada = 0,
  'RENAME TABLE `tbpo_stock_lifo_batch` TO `tbpo_stock_lifo_batch_nk`',
  'SELECT 1'
);
PREPARE lifo_batch_rename_statement FROM @lifo_batch_rename_sql;
EXECUTE lifo_batch_rename_statement;
DEALLOCATE PREPARE lifo_batch_rename_statement;
