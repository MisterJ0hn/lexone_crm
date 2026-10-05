-- Representante legal del cliente (Convenio/Empresa)
ALTER TABLE cliente ADD rep_legal_rut VARCHAR(512) DEFAULT NULL COMMENT '(DC2Type:encrypted_string)', ADD rep_legal_nombre VARCHAR(255) DEFAULT NULL, ADD rep_legal_profesion VARCHAR(255) DEFAULT NULL, ADD rep_legal_estado_civil_id INT DEFAULT NULL;
ALTER TABLE cliente ADD CONSTRAINT FK_F41C9B25D8C71359 FOREIGN KEY (rep_legal_estado_civil_id) REFERENCES estado_civil (id);
CREATE INDEX IDX_F41C9B25D8C71359 ON cliente (rep_legal_estado_civil_id);

-- Correo de bienvenida por empresa y tipo de cliente
CREATE TABLE correo_bienvenida (id INT AUTO_INCREMENT NOT NULL, empresa_id INT NOT NULL, tipo_cliente_id INT NOT NULL, remitente_nombre VARCHAR(150) DEFAULT NULL, remitente_correo VARCHAR(255) NOT NULL, asunto VARCHAR(255) NOT NULL, contenido LONGTEXT NOT NULL, activo TINYINT(1) NOT NULL, fecha_modificacion DATETIME DEFAULT NULL, INDEX IDX_149E5B4F521E1991 (empresa_id), INDEX IDX_149E5B4F4FF54C79 (tipo_cliente_id), UNIQUE INDEX uniq_correo_bienvenida_empresa_tipo (empresa_id, tipo_cliente_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE correo_bienvenida ADD CONSTRAINT FK_149E5B4F521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (id);
ALTER TABLE correo_bienvenida ADD CONSTRAINT FK_149E5B4F4FF54C79 FOREIGN KEY (tipo_cliente_id) REFERENCES tipo_cliente (id);

-- Imagen incrustada del correo de bienvenida
ALTER TABLE correo_bienvenida ADD imagen VARCHAR(255) DEFAULT NULL;
