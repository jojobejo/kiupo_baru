-- SSO subject mapping for KarismaERP Portal identities to pre-provisioned KIU PO users.
-- Apply after confirming tbpo_user.id_user exists and uses InnoDB.
CREATE TABLE IF NOT EXISTS `tbpo_sso_identity` (
  `id_user` int(11) NOT NULL,
  `external_sub` varchar(191) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_tbpo_sso_identity_sub` (`external_sub`),
  CONSTRAINT `fk_tbpo_sso_identity_user`
    FOREIGN KEY (`id_user`) REFERENCES `tbpo_user` (`id_user`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
