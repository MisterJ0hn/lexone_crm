-- =====================================================================
-- Servicio independiente de la línea de tiempo + Estado Procesal por causa
-- Ejecutar UNA vez, en este orden. Haga un respaldo de la base antes.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1) Tabla servicio (empresa + materia + nombre). Sin línea de tiempo.
-- ---------------------------------------------------------------------
CREATE TABLE servicio (
  id INT AUTO_INCREMENT NOT NULL,
  empresa_id INT NOT NULL,
  materia_id INT NOT NULL,
  nombre VARCHAR(255) NOT NULL,
  precio DOUBLE PRECISION DEFAULT NULL,
  estado TINYINT(1) NOT NULL,
  legacy_materia_estrategia_id INT DEFAULT NULL, -- temporal, se elimina al final
  INDEX IDX_servicio_empresa (empresa_id),
  INDEX IDX_servicio_materia (materia_id),
  PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE servicio ADD CONSTRAINT FK_servicio_empresa FOREIGN KEY (empresa_id) REFERENCES empresa (id);
ALTER TABLE servicio ADD CONSTRAINT FK_servicio_materia FOREIGN KEY (materia_id) REFERENCES materia (id);

-- Hasta ahora el catálogo de servicios era compartido por todas las empresas:
-- se copia una vez por cada empresa para conservar ese comportamiento (cada
-- empresa podrá luego editar el suyo sin afectar a las demás).
INSERT INTO servicio (empresa_id, materia_id, nombre, precio, estado, legacy_materia_estrategia_id)
SELECT e.id, me.materia_id, ej.nombre, ej.precio, me.estado, me.id
FROM materia_estrategia me
JOIN estrategia_juridica ej ON ej.id = me.estrategia_juridica_id
CROSS JOIN empresa e;

-- ---------------------------------------------------------------------
-- 2) causa: servicio_id reemplaza a materia_estrategia_id (se mapea por la
--    empresa de la agenda de la causa). Se elimina etapa_pendiente.
-- ---------------------------------------------------------------------
ALTER TABLE causa ADD servicio_id INT DEFAULT NULL;

UPDATE causa c
JOIN agenda a ON a.id = c.agenda_id
JOIN servicio s ON s.legacy_materia_estrategia_id = c.materia_estrategia_id AND s.empresa_id = a.empresa_id
SET c.servicio_id = s.id;

-- Verificación: debe devolver 0 (causas que tenían servicio y no se pudieron mapear)
-- SELECT COUNT(*) FROM causa WHERE materia_estrategia_id IS NOT NULL AND servicio_id IS NULL;

ALTER TABLE causa ADD CONSTRAINT FK_causa_servicio FOREIGN KEY (servicio_id) REFERENCES servicio (id);
CREATE INDEX IDX_causa_servicio ON causa (servicio_id);

-- Si en su base existe la FK causa.materia_estrategia_id -> materia_estrategia
-- (en la copia local no existe), elimínela antes: ALTER TABLE causa DROP FOREIGN KEY FK_F7B7BBA6516A508C;
ALTER TABLE causa DROP INDEX IDX_F7B7BBA6516A508C;
ALTER TABLE causa DROP COLUMN materia_estrategia_id;
ALTER TABLE causa DROP COLUMN etapa_pendiente;

ALTER TABLE servicio DROP COLUMN legacy_materia_estrategia_id;

-- ---------------------------------------------------------------------
-- 3) Estado procesal por causa (diferenciado por empresa)
-- ---------------------------------------------------------------------
CREATE TABLE estado_procesal (
  id INT AUTO_INCREMENT NOT NULL,
  empresa_id INT NOT NULL,
  causa_id INT NOT NULL,
  usuario_registro_id INT DEFAULT NULL,
  nombre VARCHAR(255) NOT NULL,
  observacion LONGTEXT DEFAULT NULL,
  completado TINYINT(1) NOT NULL,
  fecha DATETIME NOT NULL,
  fecha_completado DATETIME DEFAULT NULL,
  INDEX IDX_estado_procesal_empresa (empresa_id),
  INDEX IDX_estado_procesal_causa (causa_id),
  INDEX IDX_estado_procesal_usuario (usuario_registro_id),
  PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE estado_procesal ADD CONSTRAINT FK_estado_procesal_empresa FOREIGN KEY (empresa_id) REFERENCES empresa (id);
ALTER TABLE estado_procesal ADD CONSTRAINT FK_estado_procesal_causa FOREIGN KEY (causa_id) REFERENCES causa (id) ON DELETE CASCADE;
ALTER TABLE estado_procesal ADD CONSTRAINT FK_estado_procesal_usuario FOREIGN KEY (usuario_registro_id) REFERENCES usuario (id);

-- Las causas existentes parten con "Bienvenida" completado (verde).
INSERT INTO estado_procesal (empresa_id, causa_id, nombre, completado, fecha, fecha_completado)
SELECT a.empresa_id, c.id, 'Bienvenida', 1, NOW(), NOW()
FROM causa c
JOIN agenda a ON a.id = c.agenda_id;

-- ---------------------------------------------------------------------
-- 4) Línea de tiempo antigua: se conserva como respaldo (*_respaldo)
-- ---------------------------------------------------------------------
-- Se sueltan primero las FK que apuntan a estrategia_juridica / linea_tiempo.
ALTER TABLE contrato DROP FOREIGN KEY FK_6669652362144410;
ALTER TABLE contrato DROP COLUMN estrategia_juridica_id;

RENAME TABLE
  linea_tiempo_terminada TO linea_tiempo_terminada_respaldo,
  linea_tiempo_etapas    TO linea_tiempo_etapas_respaldo,
  linea_tiempo           TO linea_tiempo_respaldo,
  materia_estrategia     TO materia_estrategia_respaldo,
  estrategia_juridica    TO estrategia_juridica_respaldo;

-- ---------------------------------------------------------------------
-- 5) Historial de observaciones del contrato (con archivo opcional)
-- ---------------------------------------------------------------------
CREATE TABLE contrato_nota (
  id INT AUTO_INCREMENT NOT NULL,
  contrato_id INT NOT NULL,
  usuario_id INT NOT NULL,
  observacion LONGTEXT NOT NULL,
  archivo VARCHAR(64) DEFAULT NULL,
  archivo_nombre VARCHAR(255) DEFAULT NULL,
  fecha_registro DATETIME NOT NULL,
  INDEX IDX_contrato_nota_contrato (contrato_id),
  INDEX IDX_contrato_nota_usuario (usuario_id),
  PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE contrato_nota ADD CONSTRAINT FK_contrato_nota_contrato FOREIGN KEY (contrato_id) REFERENCES contrato (id) ON DELETE CASCADE;
ALTER TABLE contrato_nota ADD CONSTRAINT FK_contrato_nota_usuario FOREIGN KEY (usuario_id) REFERENCES usuario (id);
