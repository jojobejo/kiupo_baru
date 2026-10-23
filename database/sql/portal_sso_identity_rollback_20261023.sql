-- Rollback removes SSO identity links. Export the table first if the mapping must be retained.
DROP TABLE IF EXISTS `tbpo_sso_identity`;
