ALTER TABLE empresa ADD edapi_key VARCHAR(255) DEFAULT NULL, ADD edapi_cliente_guid VARCHAR(255) DEFAULT NULL;ALTER TABLE empresa ADD lexflow_habilitado TINYINT(1) DEFAULT 0 NOT NULL;
ALTER TABLE empresa ADD lexflow_solo_crm TINYINT(1) DEFAULT 0 NOT NULL;
ALTER TABLE empresa ADD pjud_client_key VARCHAR(255) DEFAULT NULL, ADD pjud_email VARCHAR(255) DEFAULT NULL, ADD pjud_password VARCHAR(512) DEFAULT NULL COMMENT '(DC2Type:encrypted_string)';
ALTER TABLE usuario ADD pjud_rut VARCHAR(20) DEFAULT NULL, ADD pjud_clave VARCHAR(512) DEFAULT NULL COMMENT '(DC2Type:encrypted_string)', ADD pjud_metodo_login SMALLINT DEFAULT 1 NOT NULL;

ALTER TABLE empresa ADD pjud_habilitado TINYINT(1) DEFAULT 0 NOT NULL;
