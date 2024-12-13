-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.7.4-MariaDB-log - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando estructura para función wms.fdif_packing_pedidos
DELIMITER //
CREATE FUNCTION `fdif_packing_pedidos`(`vidpedido` VARCHAR(50)
) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE registros INT;
	SELECT count(*) 
	INTO registros
	FROM (
		SELECT b.producto_ped, b.cantidad_ped, a.picd_requerido, ifnull(CAST(b.cantidad_ped as integer),0) - ifnull(a.picd_requerido,0) as total
		FROM vpicking_tarea a INNER JOIN tbpedidos2 b ON b.numero_ped = a.pick_idpedido AND b.producto_ped = a.picd_idproducto 
		WHERE a.pick_idpedido = vidpedido
		UNION 
		SELECT a.picd_idproducto, NULL, a.picd_requerido, a.picd_requerido
		FROM vpicking_tarea a WHERE a.pick_idpedido = vidpedido
		AND NOT EXISTS (SELECT z.numero_ped FROM tbpedidos2 z WHERE z.numero_ped = a.pick_idpedido AND z.producto_ped = a.picd_idproducto)
		UNION 
		SELECT a.producto_ped, a.cantidad_ped, NULL, a.cantidad_ped
		FROM tbpedidos2 a WHERE a.numero_ped =vidpedido
		AND NOT EXISTS (SELECT z.pick_idpedido FROM vpicking_tarea z WHERE z.pick_idpedido = a.numero_ped AND z.picd_idproducto = a.producto_ped)
	) tabla1
	WHERE tabla1.total != 0;
RETURN (registros);
END//
DELIMITER ;

-- Volcando estructura para función wms.fdif_picking_pedidos
DELIMITER //
CREATE FUNCTION `fdif_picking_pedidos`(`idpedido` CHAR(15)
) RETURNS smallint(6)
    READS SQL DATA
    COMMENT 'Busca Diferencias de una tarea de picking determinada y el correspondiente pedido.'
BEGIN
	DECLARE registros INT;
	SELECT count(*)
	INTO registros
	FROM (
		SELECT b.producto_ped, b.cantidad_ped, a.picd_requerido, ifnull(CAST(b.cantidad_ped as integer),0) - ifnull(a.picd_requerido,0) as total
		FROM vpicking_tarea a INNER JOIN tbpedidos2 b ON b.numero_ped = a.pick_idpedido AND b.producto_ped = a.picd_idproducto 
		WHERE a.pick_idpedido = idpedido
		UNION 
		SELECT a.picd_idproducto, NULL, a.picd_requerido, a.picd_requerido
		FROM vpicking_tarea a WHERE a.pick_idpedido = idpedido
		AND NOT EXISTS (SELECT z.numero_ped FROM tbpedidos2 z WHERE z.numero_ped = a.pick_idpedido AND z.producto_ped = a.picd_idproducto)
		UNION
		SELECT a.producto_ped, a.cantidad_ped, NULL, a.cantidad_ped
		FROM tbpedidos2 a WHERE a.numero_ped =idpedido
		AND NOT EXISTS (SELECT z.pick_idpedido FROM vpicking_tarea z WHERE z.pick_idpedido = a.numero_ped AND z.picd_idproducto = a.producto_ped)
	) tabla1
	WHERE tabla1.total != 0;
RETURN (registros);
END//
DELIMITER ;

-- Volcando estructura para función wms.fnext_idpacking
DELIMITER //
CREATE FUNCTION `fnext_idpacking`() RETURNS int(3)
    READS SQL DATA
    COMMENT 'Busca el proximo primary key para la table de Packing'
BEGIN
DECLARE id_packing INT;
SELECT IFNULL(MAX(pack_idpacking),0) + 1
  INTO id_packing
  FROM tpacking;
RETURN (id_packing);
END//
DELIMITER ;

-- Volcando estructura para función wms.fnext_idpicking
DELIMITER //
CREATE FUNCTION `fnext_idpicking`() RETURNS int(3)
    READS SQL DATA
    COMMENT 'Busca el proximo primary key para la table de Picking'
BEGIN
DECLARE id_picking INT;
SELECT IFNULL(MAX(pick_idpicking),0) + 1
  INTO id_picking
  FROM tpicking;
RETURN (id_picking);
END//
DELIMITER ;

-- Volcando estructura para función wms.fpack_bultos_productos
DELIMITER //
CREATE FUNCTION `fpack_bultos_productos`(`idpacking` INT,
	`idbulto` TINYINT
) RETURNS int(11)
    READS SQL DATA
    COMMENT 'Cuenta el número de SKU que contiene un bulto de una tarea de packing'
BEGIN
DECLARE registros INT;
SELECT IFNULL(COUNT(pacp_idproducto),0)
  INTO registros
  FROM tpacking_productos
  where pacp_idpacking = idpacking and pacp_idbulto = idbulto;  
RETURN (registros);
END//
DELIMITER ;

-- Volcando estructura para función wms.fpack_bultos_status
DELIMITER //
CREATE FUNCTION `fpack_bultos_status`(`idpacking` INT
) RETURNS int(11)
    READS SQL DATA
    COMMENT 'Determina si hay bultos abiertos en una tarea de packing'
BEGIN
DECLARE abiertos INT;
SELECT COUNT(pack_idpacking) 
  INTO abiertos
FROM tpacking_bultos 
WHERE pack_idpacking = idpacking and pack_status = 0;
RETURN (abiertos);
END//
DELIMITER ;

-- Volcando estructura para función wms.fpicking_status
DELIMITER //
CREATE FUNCTION `fpicking_status`(`idpicking` INT(3)
) RETURNS int(11)
    READS SQL DATA
    COMMENT 'Retorna el Status de una tarea de picking'
BEGIN
	DECLARE lnstatus INT;
	SELECT pick_status
  	  INTO lnstatus
  	  FROM vpicking
     WHERE pick_idpicking = idpicking;
	RETURN (lnstatus);
END//
DELIMITER ;

-- Volcando estructura para función wms.fuser_empresa
DELIMITER //
CREATE FUNCTION `fuser_empresa`() RETURNS int(11)
    READS SQL DATA
    COMMENT 'Retorna el ID de Empresa asignado al usuario.'
BEGIN
   DECLARE lnid_empresa INT;
    SELECT id_empresa
      INTO lnid_empresa
      FROM tusuarios_bbdd
     WHERE username= USER();
   RETURN lnid_empresa;
END//
DELIMITER ;

-- Volcando estructura para función wms.fvalida_preparador
DELIMITER //
CREATE FUNCTION `fvalida_preparador`(`username` VARCHAR(20)) RETURNS tinyint(1)
    READS SQL DATA
    COMMENT 'Valida que un usuario tenga el rol de preparador.'
BEGIN
DECLARE registros INT;
SELECT IFNULL(COUNT(usrr_name),0)
  INTO registros
  FROM tusuarios_roles
  where usrr_name = username;
RETURN (registros);
END//
DELIMITER ;

-- Volcando estructura para tabla wms.tbackorder
CREATE TABLE IF NOT EXISTS `tbackorder` (
  `back_idempresa` tinyint(4) NOT NULL,
  `back_idorder` int(11) NOT NULL,
  `back_fecha` datetime NOT NULL,
  `back_idpedido` varchar(15) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `back_idpicking` int(11) DEFAULT NULL,
  `back_idpacking` int(11) DEFAULT NULL,
  `back_requerido` int(11) NOT NULL COMMENT 'Campo a tabla hija',
  `back_despachado` int(11) NOT NULL COMMENT 'Mover campo a tabla hija',
  `user_crea` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `fec_crea` datetime NOT NULL DEFAULT current_timestamp(),
  `user_mod` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.tbackorder: ~0 rows (aproximadamente)

-- Volcando estructura para tabla wms.tempresas
CREATE TABLE IF NOT EXISTS `tempresas` (
  `empr_idempresa` tinyint(4) NOT NULL COMMENT 'ID. de empresa. No debe ser utilizado como parametro o variable GLOBAL dentro de cualquier sistema. Si se requiere mandar como parametro GET u otro valor publico usar el valor del campo UUID.',
  `empr_nombre` varchar(200) CHARACTER SET utf8mb3 NOT NULL,
  `empr_alias` varchar(50) CHARACTER SET utf8mb3 NOT NULL,
  `empr_uuid` varchar(36) CHARACTER SET utf8mb3 NOT NULL COMMENT 'Este es un valor ID Unico para utilizarlo como parametro o variable publica en las llamadas GET que requieran el ID de la empresa. Se genera con la función UUID() de Mariadb y se le quitan los guiones para hacerlo menos identificable',
  `empr_logo` varchar(100) CHARACTER SET utf8mb3 NOT NULL,
  `empr_logosimple` varchar(200) CHARACTER SET utf8mb3 NOT NULL,
  `user_crea` varchar(50) CHARACTER SET utf8mb3 NOT NULL,
  `fec_crea` datetime NOT NULL DEFAULT current_timestamp(),
  `user_mod` varchar(50) CHARACTER SET utf8mb3 DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.tempresas: ~2 rows (aproximadamente)
INSERT INTO `tempresas` (`empr_idempresa`, `empr_nombre`, `empr_alias`, `empr_uuid`, `empr_logo`, `empr_logosimple`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 'Inversiones Lefre C.A.', 'Lefre', '85b466cbfb4911eaa49b4c72b92166c4', 'lefre_logo01.jpg', 'lefre_logo02.jpg', 'root', '2020-02-01 00:00:00', '', '2020-11-23 23:07:27'),
	(2, 'Inversiones S&M', 'S&M', '', 'lefre_logo01.jpg', 'lefre_logo02.jpg', 'root', '2020-02-13 22:37:59', NULL, '2020-05-23 20:35:17');

-- Volcando estructura para tabla wms.tmenu_roles
CREATE TABLE IF NOT EXISTS `tmenu_roles` (
  `menu_id` tinyint(4) NOT NULL,
  `menu_role` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Indica que Roles tienen acceso a cada opción del menú principal. Solo aplica a opciones principales o al nombre de los grupos de opciones';

-- Volcando datos para la tabla wms.tmenu_roles: ~21 rows (aproximadamente)
INSERT INTO `tmenu_roles` (`menu_id`, `menu_role`) VALUES
	(1, 'PEDIDOS'),
	(2, 'PREPARADOR'),
	(3, 'EMBALADOR'),
	(4, 'ALMACEN'),
	(1, 'SOPORTE_TECNICO'),
	(2, 'SOPORTE_TECNICO'),
	(3, 'SOPORTE_TECNICO'),
	(4, 'SOPORTE_TECNICO'),
	(1, 'SUPERVISOR_ALMACEN'),
	(2, 'SUPERVISOR_ALMACEN'),
	(3, 'SUPERVISOR_ALMACEN'),
	(5, 'SUPERVISOR_ALMACEN'),
	(5, 'SOPORTE_TECNICO'),
	(1, 'ADMIN'),
	(2, 'ADMIN'),
	(3, 'ADMIN'),
	(4, 'ADMIN'),
	(5, 'ADMIN'),
	(6, 'SUPERVISOR_ALMACEN'),
	(6, 'ADMIN'),
	(5, 'RECEPCION_CONTROL');

-- Volcando estructura para tabla wms.toperadores
CREATE TABLE IF NOT EXISTS `toperadores` (
  `oper_idempresa` tinyint(4) NOT NULL,
  `oper_idoperador` smallint(5) unsigned NOT NULL COMMENT 'Id del Operador',
  `oper_nombre` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `oper_cargo` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `oper_foto` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `oper_fotopath` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `user_crea` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `fec_crea` datetime NOT NULL DEFAULT current_timestamp(),
  `user_mod` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.toperadores: ~2 rows (aproximadamente)
INSERT INTO `toperadores` (`oper_idempresa`, `oper_idoperador`, `oper_nombre`, `oper_cargo`, `oper_foto`, `oper_fotopath`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 1, 'Julio Csar', '1', NULL, NULL, 'root', '2022-01-03 22:44:02', NULL, NULL),
	(2, 2, 'test2', '1', NULL, NULL, 'root', '2022-01-03 22:44:33', NULL, NULL);

-- Volcando estructura para tabla wms.tpacking
CREATE TABLE IF NOT EXISTS `tpacking` (
  `pack_idempresa` tinyint(4) NOT NULL,
  `pack_idpacking` int(11) NOT NULL COMMENT 'Id. Tarea de Packing',
  `pack_fecha` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha en que pasó a Packing. Distinto a la fecha en que se inicia. ',
  `pack_idpedido` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Id del Pedido.',
  `pack_idpicking` int(11) NOT NULL COMMENT 'Id. del Picking. En caso que venga del picking.',
  `pack_fecinicio` datetime DEFAULT NULL COMMENT 'Inicio de tarea de Packing por parte del embalador.',
  `pack_embalador` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Es el username del preparador asignado. ',
  `pack_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=Anulado, 1=En Proceso, 2=Pausado, 5=Culminado',
  `pack_prioridad` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=Normal, 1=Urgente',
  `pack_fecierre` datetime DEFAULT NULL COMMENT 'Fecha de cierre de la tarea.',
  `pack_pista` tinyint(1) DEFAULT 0 COMMENT 'Indica la pista dónde se coloca el pedido una vez consolidado, para pasar al picking.',
  `pack_observacion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_crea` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fec_crea` datetime NOT NULL DEFAULT current_timestamp(),
  `user_mod` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL COMMENT 'Fecha ultima modificación o cierre de tarea.',
  PRIMARY KEY (`pack_idempresa`,`pack_idpacking`),
  UNIQUE KEY `tpacking_idx01` (`pack_idpedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.tpacking: ~3 rows (aproximadamente)
INSERT INTO `tpacking` (`pack_idempresa`, `pack_idpacking`, `pack_fecha`, `pack_idpedido`, `pack_idpicking`, `pack_fecinicio`, `pack_embalador`, `pack_status`, `pack_prioridad`, `pack_fecierre`, `pack_pista`, `pack_observacion`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 1, '2022-10-27 17:04:41', 'M98000293', 2, '2022-10-27 17:08:09', 'JCFREITES', 5, 0, '2022-10-27 17:26:32', 0, '', 'jcfreites', '2022-10-27 17:04:41', 'JCFREITES', '2022-10-27 17:26:32'),
	(1, 2, '2022-10-29 12:49:36', 'M26002345', 4, '2022-10-29 13:29:02', 'jcfreites', 5, 0, '2022-10-30 13:38:50', 0, '', 'jcfreites', '2022-10-29 12:49:36', 'jcfreites', '2022-10-30 13:38:50'),
	(1, 3, '2022-10-29 12:51:59', 'M26002346', 3, '2022-10-29 13:55:19', 'jcfreites', 5, 0, '2022-10-29 14:03:36', 0, '', 'jcfreites', '2022-10-29 12:51:59', 'jcfreites', '2022-10-29 14:03:36');

-- Volcando estructura para tabla wms.tpacking_bultos
CREATE TABLE IF NOT EXISTS `tpacking_bultos` (
  `pack_idempresa` tinyint(4) NOT NULL,
  `pack_idpacking` int(11) NOT NULL COMMENT 'Id. Tarea de Packing',
  `pack_idbulto` tinyint(2) NOT NULL,
  `pack_peso` decimal(7,2) NOT NULL DEFAULT 0.00,
  `pack_unidadpeso` varchar(3) CHARACTER SET utf8mb3 NOT NULL DEFAULT 'Kg' COMMENT 'gr = gramos, Kg=Kilos,',
  `pack_status` bit(1) NOT NULL DEFAULT b'0' COMMENT '0=Abierto, 1=Cerrado',
  `user_crea` varchar(50) CHARACTER SET utf8mb3 NOT NULL,
  `fec_crea` datetime NOT NULL DEFAULT current_timestamp(),
  `user_mod` varchar(50) CHARACTER SET utf8mb3 DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL,
  PRIMARY KEY (`pack_idempresa`,`pack_idpacking`,`pack_idbulto`) USING BTREE,
  CONSTRAINT `tpacking_bultos_fk01` FOREIGN KEY (`pack_idempresa`, `pack_idpacking`) REFERENCES `tpacking` (`pack_idempresa`, `pack_idpacking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.tpacking_bultos: ~9 rows (aproximadamente)
INSERT INTO `tpacking_bultos` (`pack_idempresa`, `pack_idpacking`, `pack_idbulto`, `pack_peso`, `pack_unidadpeso`, `pack_status`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 1, 1, 0.00, 'Kg', b'1', 'JCFREITES', '2022-10-27 17:10:13', NULL, NULL),
	(1, 1, 2, 0.00, 'Kg', b'1', 'JCFREITES', '2022-10-27 17:10:13', NULL, NULL),
	(1, 1, 3, 10.00, 'gr', b'1', 'JCFREITES', '2022-10-27 17:16:49', 'JCFREITES', '2022-10-27 17:19:30'),
	(1, 2, 1, 1.10, 'Kg', b'1', 'jcfreites', '2022-10-30 11:47:36', 'jcfreites', '2022-10-30 13:44:46'),
	(1, 2, 2, 10.00, 'Kg', b'1', 'jcfreites', '2022-10-30 13:36:18', 'jcfreites', '2022-10-30 13:44:51'),
	(1, 2, 3, 1.15, 'Kg', b'1', 'jcfreites', '2022-10-30 13:36:30', 'jcfreites', '2022-10-30 13:45:11'),
	(1, 3, 1, 19.60, 'Kg', b'1', 'jcfreites', '2022-10-29 13:55:19', 'jcfreites', '2022-10-29 13:59:16'),
	(1, 3, 2, 10.65, 'Kg', b'1', 'jcfreites', '2022-10-29 13:59:47', 'jcfreites', '2022-10-29 14:01:09'),
	(1, 3, 3, 0.00, 'Kg', b'1', 'jcfreites', '2022-10-29 14:02:37', 'jcfreites', '2022-10-29 14:03:23');

-- Volcando estructura para tabla wms.tpacking_detalle
CREATE TABLE IF NOT EXISTS `tpacking_detalle` (
  `pacd_idempresa` tinyint(4) NOT NULL,
  `pacd_idpacking` int(11) NOT NULL,
  `pacd_idproducto` varchar(20) CHARACTER SET utf8mb3 NOT NULL,
  `pacd_unidad` varchar(7) DEFAULT NULL,
  `pacd_requerido` smallint(1) NOT NULL COMMENT 'Cantidad requerida a embalar',
  `pacd_cantidad` smallint(1) DEFAULT NULL COMMENT 'Cantidad  embalada del producto indicado',
  `user_crea` varchar(50) CHARACTER SET utf8mb3 NOT NULL,
  `fec_crea` datetime NOT NULL DEFAULT current_timestamp(),
  `user_mod` varchar(50) CHARACTER SET utf8mb3 DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL,
  PRIMARY KEY (`pacd_idempresa`,`pacd_idpacking`,`pacd_idproducto`),
  CONSTRAINT `tpacking_detalle_fk01` FOREIGN KEY (`pacd_idempresa`, `pacd_idpacking`) REFERENCES `tpacking` (`pack_idempresa`, `pack_idpacking`),
  CONSTRAINT `tpacking_detalle_c01` CHECK (`pacd_cantidad` >= 0 and `pacd_cantidad` <= `pacd_requerido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.tpacking_detalle: ~13 rows (aproximadamente)
INSERT INTO `tpacking_detalle` (`pacd_idempresa`, `pacd_idpacking`, `pacd_idproducto`, `pacd_unidad`, `pacd_requerido`, `pacd_cantidad`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 1, '2391-0006', 'PZA', 12, 12, 'jcfreites', '2022-10-27 17:04:41', 'JCFREITES', '2022-10-27 17:10:13'),
	(1, 1, '2391-0008', 'PZA', 12, 12, 'jcfreites', '2022-10-27 17:04:41', 'JCFREITES', '2022-10-27 17:10:13'),
	(1, 1, '2391-0012', 'PZA', 6, 6, 'jcfreites', '2022-10-27 17:04:41', 'JCFREITES', '2022-10-27 17:16:58'),
	(1, 1, '2391-0022', 'PZA', 6, 6, 'jcfreites', '2022-10-27 17:04:41', 'JCFREITES', '2022-10-27 17:17:38'),
	(1, 2, '5020-0161', 'CJA', 1, 1, 'jcfreites', '2022-10-29 12:49:36', 'jcfreites', '2022-10-30 13:36:04'),
	(1, 2, '5020-0227', 'CJA', 1, 1, 'jcfreites', '2022-10-29 12:49:36', 'jcfreites', '2022-10-30 13:36:25'),
	(1, 2, '6300-0022', 'PQT', 1, 1, 'jcfreites', '2022-10-29 12:49:36', 'jcfreites', '2022-10-30 13:36:10'),
	(1, 2, '7007-0104', 'PZA', 30, 30, 'jcfreites', '2022-10-29 12:49:36', 'jcfreites', '2022-10-30 13:38:34'),
	(1, 3, '2391-0112', 'GLN', 1, 1, 'jcfreites', '2022-10-29 12:51:59', 'jcfreites', '2022-10-29 14:00:06'),
	(1, 3, '2391-0139', 'GLN', 3, 3, 'jcfreites', '2022-10-29 12:51:59', 'jcfreites', '2022-10-29 13:57:43'),
	(1, 3, '2391-2108', 'GLN', 1, 1, 'jcfreites', '2022-10-29 12:51:59', 'jcfreites', '2022-10-29 13:56:12'),
	(1, 3, '2392-0378', 'GLN', 1, 1, 'jcfreites', '2022-10-29 12:51:59', 'jcfreites', '2022-10-29 14:00:26'),
	(1, 3, '2392-0404', 'CÑT', 1, 1, 'jcfreites', '2022-10-29 12:51:59', 'jcfreites', '2022-10-29 14:02:43');

-- Volcando estructura para tabla wms.tpacking_productos
CREATE TABLE IF NOT EXISTS `tpacking_productos` (
  `pacp_idempresa` tinyint(1) NOT NULL,
  `pacp_idpacking` int(11) NOT NULL,
  `pacp_idbulto` tinyint(2) NOT NULL,
  `pacp_idproducto` varchar(20) CHARACTER SET utf8mb3 NOT NULL,
  `pacp_cantidad` smallint(1) NOT NULL,
  `user_crea` varchar(20) CHARACTER SET utf8mb3 NOT NULL,
  `fec_crea` datetime NOT NULL,
  `user_mod` varchar(20) CHARACTER SET utf8mb3 DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL,
  PRIMARY KEY (`pacp_idempresa`,`pacp_idpacking`,`pacp_idbulto`,`pacp_idproducto`),
  CONSTRAINT `tpacking_productos` FOREIGN KEY (`pacp_idempresa`, `pacp_idpacking`, `pacp_idbulto`) REFERENCES `tpacking_bultos` (`pack_idempresa`, `pack_idpacking`, `pack_idbulto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.tpacking_productos: ~12 rows (aproximadamente)
INSERT INTO `tpacking_productos` (`pacp_idempresa`, `pacp_idpacking`, `pacp_idbulto`, `pacp_idproducto`, `pacp_cantidad`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 1, 1, '2391-0006', 12, 'JCFREITES', '2022-10-27 17:10:13', NULL, NULL),
	(1, 1, 2, '2391-0008', 12, 'JCFREITES', '2022-10-27 17:10:13', NULL, NULL),
	(1, 1, 3, '2391-0012', 6, 'JCFREITES', '2022-10-27 17:16:58', NULL, NULL),
	(1, 1, 3, '2391-0022', 6, 'JCFREITES', '2022-10-27 17:17:38', NULL, NULL),
	(1, 2, 1, '5020-0161', 1, 'jcfreites', '2022-10-30 13:36:04', NULL, NULL),
	(1, 2, 1, '6300-0022', 1, 'jcfreites', '2022-10-30 13:36:10', NULL, NULL),
	(1, 2, 2, '5020-0227', 1, 'jcfreites', '2022-10-30 13:36:25', NULL, NULL),
	(1, 2, 3, '7007-0104', 30, 'jcfreites', '2022-10-30 13:38:34', NULL, NULL),
	(1, 3, 1, '2391-0139', 3, 'jcfreites', '2022-10-29 13:57:43', NULL, NULL),
	(1, 3, 1, '2391-2108', 1, 'jcfreites', '2022-10-29 13:56:12', NULL, NULL),
	(1, 3, 2, '2391-0112', 1, 'jcfreites', '2022-10-29 14:00:06', NULL, NULL),
	(1, 3, 2, '2392-0378', 1, 'jcfreites', '2022-10-29 14:00:26', NULL, NULL),
	(1, 3, 3, '2392-0404', 1, 'jcfreites', '2022-10-29 14:02:43', NULL, NULL);

-- Volcando estructura para tabla wms.tpedidos_status
CREATE TABLE IF NOT EXISTS `tpedidos_status` (
  `pedi_idempresa` tinyint(4) NOT NULL,
  `pedi_idpedido` varchar(15) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `user_crea` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `fec_crea` datetime(2) NOT NULL,
  `user_mod` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `fec_mod` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.tpedidos_status: ~0 rows (aproximadamente)

-- Volcando estructura para tabla wms.tpicking
CREATE TABLE IF NOT EXISTS `tpicking` (
  `pick_idempresa` tinyint(4) NOT NULL,
  `pick_idpicking` int(11) NOT NULL,
  `pick_fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `pick_idpedido` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pick_preparador` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Es el username del preparador asignado.',
  `pick_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=Anulado, 1=En Proceso, 2=Pausado, 3=Consolidado,\r\n5=Cerrada',
  `pick_prioridad` tinyint(1) DEFAULT 0 COMMENT '0=Normal, 1=Urgente',
  `pick_fecierre` datetime DEFAULT NULL COMMENT 'Fecha en la que el preparador termina de anclar todos los productos a la tarea de picking.',
  `pick_pista` tinyint(1) DEFAULT 0 COMMENT 'Indica la pista dónde se coloca el pedido una vez consolidado, para pasar al picking.',
  `pick_observacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pick_userverif` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario verificador',
  `pick_fecverif` datetime DEFAULT NULL COMMENT 'Fecha en que fue verificada y cerrada la tarea de picking.',
  `user_crea` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fec_crea` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de inicio de la tarea.',
  `user_mod` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL,
  PRIMARY KEY (`pick_idempresa`,`pick_idpicking`),
  UNIQUE KEY `tpicking_idx01` (`pick_idpedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla wms.tpicking: ~4 rows (aproximadamente)
INSERT INTO `tpicking` (`pick_idempresa`, `pick_idpicking`, `pick_fecha`, `pick_idpedido`, `pick_preparador`, `pick_status`, `pick_prioridad`, `pick_fecierre`, `pick_pista`, `pick_observacion`, `pick_userverif`, `pick_fecverif`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 1, '2022-10-26 09:23:01', 'M40002773', 'jcfreites', 1, 1, NULL, 0, NULL, NULL, NULL, 'JCFREITES', '2022-10-26 09:23:01', NULL, NULL),
	(1, 2, '2022-10-27 16:40:40', 'M98000293', 'jcfreites', 5, 0, '2022-10-27 16:54:32', 2, '', 'jcfreites', '2022-10-27 17:04:41', 'jcfreites', '2022-10-27 16:40:40', 'jcfreites', '2022-10-27 17:04:41'),
	(1, 3, '2022-10-28 15:52:01', 'M26002346', 'jcfreites', 5, 0, '2022-10-29 11:32:18', 4, '', 'jcfreites', '2022-10-29 12:51:59', 'jcfreites', '2022-10-28 15:52:01', 'jcfreites', '2022-10-29 12:51:59'),
	(1, 4, '2022-10-28 15:52:10', 'M26002345', 'jcfreites', 5, 0, '2022-10-29 12:47:29', 4, '', 'jcfreites', '2022-10-29 12:49:36', 'jcfreites', '2022-10-28 15:52:10', 'jcfreites', '2022-10-29 12:49:36');

-- Volcando estructura para tabla wms.tpicking_bins
CREATE TABLE IF NOT EXISTS `tpicking_bins` (
  `picc_idempresa` tinyint(4) NOT NULL,
  `picc_idpicking` int(11) NOT NULL,
  `picc_bin` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Bandeja o palet dónde sera ubicado el pedido consolidado para pasar al packing.',
  `user_crea` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fec_crea` datetime NOT NULL,
  `user_mod` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL,
  PRIMARY KEY (`picc_idempresa`,`picc_idpicking`,`picc_bin`),
  CONSTRAINT `tpicking_bins` FOREIGN KEY (`picc_idempresa`, `picc_idpicking`) REFERENCES `tpicking` (`pick_idempresa`, `pick_idpicking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Indica los palets,cajones,recipientes donde se consolidan tareas de picking';

-- Volcando datos para la tabla wms.tpicking_bins: ~3 rows (aproximadamente)
INSERT INTO `tpicking_bins` (`picc_idempresa`, `picc_idpicking`, `picc_bin`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 2, 'PALET-2', 'jcfreites', '2022-10-27 16:54:32', NULL, NULL),
	(1, 3, 'PALET-4', 'jcfreites', '2022-10-29 11:32:18', NULL, NULL),
	(1, 4, 'PALET-4', 'jcfreites', '2022-10-29 11:32:37', NULL, NULL);

-- Volcando estructura para tabla wms.tpicking_detalle
CREATE TABLE IF NOT EXISTS `tpicking_detalle` (
  `picd_idempresa` tinyint(4) NOT NULL,
  `picd_idpicking` int(11) NOT NULL,
  `picd_idproducto` varchar(20) CHARACTER SET utf8mb3 NOT NULL,
  `picd_unidad` varchar(7) CHARACTER SET utf8mb3 DEFAULT NULL,
  `picd_idalmacen` varchar(10) CHARACTER SET utf8mb3 NOT NULL,
  `picd_ubicacion` varchar(20) CHARACTER SET utf8mb3 DEFAULT NULL,
  `picd_requerido` smallint(1) NOT NULL COMMENT 'Cantidad requerida en el pedido.',
  `picd_cantidad` smallint(1) DEFAULT NULL COMMENT 'Cantidad anclada al picking del producto indicado. NULL= el preparador no ha hecho nada con el producto.',
  `picd_cantverif` smallint(1) DEFAULT NULL COMMENT 'Cantidad verificada',
  `user_crea` varchar(50) CHARACTER SET utf8mb3 NOT NULL,
  `fec_crea` datetime NOT NULL DEFAULT current_timestamp(),
  `user_mod` varchar(50) CHARACTER SET utf8mb3 DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL,
  PRIMARY KEY (`picd_idempresa`,`picd_idpicking`,`picd_idproducto`),
  CONSTRAINT `tpicking_detalle_fk01` FOREIGN KEY (`picd_idempresa`, `picd_idpicking`) REFERENCES `tpicking` (`pick_idempresa`, `pick_idpicking`),
  CONSTRAINT `tpicking_detalle_c02` CHECK (`picd_cantverif` >= 0 and `picd_cantverif` <= `picd_cantidad`),
  CONSTRAINT `tipcking_detalle_c01` CHECK (`picd_cantidad` >= 0 and `picd_cantidad` <= `picd_requerido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla wms.tpicking_detalle: ~15 rows (aproximadamente)
INSERT INTO `tpicking_detalle` (`picd_idempresa`, `picd_idpicking`, `picd_idproducto`, `picd_unidad`, `picd_idalmacen`, `picd_ubicacion`, `picd_requerido`, `picd_cantidad`, `picd_cantverif`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 1, '0020-0259', 'PZA', '1', '04-01-B', 12, NULL, NULL, 'JCFREITES', '2022-10-26 09:23:01', NULL, NULL),
	(1, 1, '6030-0255', 'PZA', '1', 'JAULA', 1, NULL, NULL, 'JCFREITES', '2022-10-26 09:23:01', NULL, NULL),
	(1, 2, '2391-0006', 'PZA', '1', '04-02-B', 12, 12, 12, 'jcfreites', '2022-10-27 16:40:40', 'jcfreites', '2022-10-27 17:01:53'),
	(1, 2, '2391-0008', 'PZA', '1', '04-02-B', 12, 12, 12, 'jcfreites', '2022-10-27 16:40:40', 'jcfreites', '2022-10-27 17:04:32'),
	(1, 2, '2391-0012', 'PZA', '1', '04-02-B', 6, 6, 6, 'jcfreites', '2022-10-27 16:40:40', 'jcfreites', '2022-10-27 17:00:13'),
	(1, 2, '2391-0022', 'PZA', '1', '04-02-B', 6, 6, 6, 'jcfreites', '2022-10-27 16:40:40', 'jcfreites', '2022-10-27 16:59:52'),
	(1, 3, '2391-0112', 'GLN', '1', '06-12-A', 1, 1, 1, 'jcfreites', '2022-10-28 15:52:01', 'jcfreites', '2022-10-29 12:51:52'),
	(1, 3, '2391-0139', 'GLN', '1', '06-11-B', 3, 3, 3, 'jcfreites', '2022-10-28 15:52:01', 'jcfreites', '2022-10-29 12:51:14'),
	(1, 3, '2391-2108', 'GLN', '1', '06-11-A', 1, 1, 1, 'jcfreites', '2022-10-28 15:52:01', 'jcfreites', '2022-10-29 12:50:02'),
	(1, 3, '2392-0378', 'GLN', '1', '06-02-A', 1, 1, 1, 'jcfreites', '2022-10-28 15:52:01', 'jcfreites', '2022-10-29 12:50:23'),
	(1, 3, '2392-0404', 'CÑT', '1', 'PAS-6', 1, 1, 1, 'jcfreites', '2022-10-28 15:52:01', 'jcfreites', '2022-10-29 12:51:37'),
	(1, 4, '5020-0161', 'CJA', '1', '01-01-A', 1, 1, 1, 'jcfreites', '2022-10-28 15:52:10', 'jcfreites', '2022-10-29 12:48:29'),
	(1, 4, '5020-0227', 'CJA', '1', 'MZZ-A', 1, 1, 1, 'jcfreites', '2022-10-28 15:52:10', 'jcfreites', '2022-10-29 12:49:10'),
	(1, 4, '6300-0022', 'PQT', '1', '03-10-B', 1, 1, 1, 'jcfreites', '2022-10-28 15:52:10', 'jcfreites', '2022-10-29 12:48:41'),
	(1, 4, '7007-0104', 'PZA', '1', 'MZZ-B', 30, 30, 30, 'jcfreites', '2022-10-28 15:52:10', 'jcfreites', '2022-10-29 12:49:25');

-- Volcando estructura para tabla wms.tpicking_pistas
CREATE TABLE IF NOT EXISTS `tpicking_pistas` (
  `picp_idvendedor` varchar(10) CHARACTER SET latin1 NOT NULL COMMENT 'Codigo del Vendedor. Viene del Xenx.',
  `picp_pista` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.tpicking_pistas: ~83 rows (aproximadamente)
INSERT INTO `tpicking_pistas` (`picp_idvendedor`, `picp_pista`) VALUES
	('0011', 4),
	('0038', 7),
	('01', 0),
	('02', 0),
	('03', 0),
	('04', 1),
	('05', 0),
	('06', 4),
	('09', 0),
	('10', 0),
	('102', 1),
	('108', 4),
	('113', 1),
	('115', 3),
	('116', 3),
	('12', 5),
	('13', 7),
	('14', 1),
	('15', 1),
	('16', 6),
	('17', 1),
	('18', 3),
	('20', 2),
	('200', 0),
	('201', 0),
	('22', 1),
	('23', 1),
	('24', 3),
	('25', 1),
	('26', 4),
	('27', 4),
	('28', 3),
	('29', 3),
	('30', 5),
	('31', 1),
	('33', 2),
	('34', 2),
	('35', 3),
	('36', 1),
	('37', 1),
	('39', 2),
	('40', 1),
	('41', 3),
	('42', 2),
	('43', 6),
	('44', 1),
	('45', 2),
	('46', 3),
	('47', 3),
	('48', 5),
	('49', 3),
	('50', 4),
	('51', 7),
	('52', 3),
	('54', 3),
	('55', 1),
	('56', 3),
	('57', 6),
	('58', 7),
	('59', 2),
	('60', 6),
	('61', 2),
	('62', 2),
	('64', 2),
	('65', 6),
	('66', 5),
	('67', 5),
	('68', 5),
	('69', 5),
	('71', 1),
	('72', 6),
	('73', 4),
	('74', 3),
	('75', 1),
	('76', 1),
	('77', 1),
	('78', 1),
	('79', 1),
	('80', 0),
	('87', 0),
	('92', 4),
	('94', 5),
	('98', 2);

-- Volcando estructura para tabla wms.tproductos_ubica
CREATE TABLE IF NOT EXISTS `tproductos_ubica` (
  `prou_idproducto` varchar(20) NOT NULL,
  `prou_almacen` varchar(20) NOT NULL COMMENT 'Almacén donde está ubicado',
  `prou_ubicacion` varchar(20) NOT NULL COMMENT 'Ubicación en formato Pasillo-Rack-Nivel. Ejemplo: 01-01-A',
  `prou_cantidad` int(1) NOT NULL,
  `user_crea` varchar(50) NOT NULL,
  `fec_crea` datetime NOT NULL,
  `user_mod` varchar(50) DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Cuando un producto está ubicado en varios sitios dentro del almacén, esas ubicaciones se almacenan aquí.  La ubicación principal está en la tabla de Productos y las alternas y/o aéreas aquí. ';

-- Volcando datos para la tabla wms.tproductos_ubica: ~56 rows (aproximadamente)
INSERT INTO `tproductos_ubica` (`prou_idproducto`, `prou_almacen`, `prou_ubicacion`, `prou_cantidad`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	('1300-0009', '01', '04-01-D', 640, 'jcfreites', '2022-08-01 10:12:47', NULL, NULL),
	('0040-0100', '01', '02-10-F', 240, 'arcilal', '2022-08-01 11:22:10', NULL, NULL),
	('2352-0179', '01', '02-10-F', 1400, 'arcilal', '2022-08-01 11:24:07', NULL, NULL),
	('2352-0180', '01', '02-10-F', 780, 'arcilal', '2022-08-01 11:25:09', NULL, NULL),
	('2352-0166', '01', '02-10-F', 200, 'arcilal', '2022-08-01 11:25:50', NULL, NULL),
	('0020-0172', '01', '02-09-F', 1440, 'arcilal', '2022-08-01 11:27:06', NULL, NULL),
	('8170-0257', '01', '02-09-F', 1100, 'arcilal', '2022-08-01 11:31:44', NULL, NULL),
	('0040-0102', '01', '02-10-F', 500, 'arcilal', '2022-08-01 11:34:55', NULL, NULL),
	('0020-0220', '01', '02-01-F', 1800, 'arcilal', '2022-08-01 11:35:50', NULL, NULL),
	('8170-0068', '01', '02-02-F', 1400, 'arcilal', '2022-08-01 11:37:51', NULL, NULL),
	('8059-0108', '01', '02-02-F', 500, 'arcilal', '2022-08-01 11:43:32', NULL, NULL),
	('8120-0119', '01', '02-02-F', 125, 'arcilal', '2022-08-01 13:42:31', NULL, NULL),
	('8160-0115', '01', '02-04-F', 350, 'arcilal', '2022-08-01 14:10:08', NULL, NULL),
	('8059-0122', '01', '02-04-F', 450, 'arcilal', '2022-08-01 14:12:13', NULL, NULL),
	('8180-0009', '01', '02-04-F', 750, 'arcilal', '2022-08-01 14:18:35', NULL, NULL),
	('8180-0004', '01', '02-04-F', 400, 'arcilal', '2022-08-01 14:19:30', NULL, NULL),
	('8059-0114', '01', '02-04-F', 600, 'arcilal', '2022-08-01 14:20:27', NULL, NULL),
	('4120-0250', '01', '02-03-F', 450, 'arcilal', '2022-08-01 14:25:32', NULL, NULL),
	('8180-0023', '01', '02-03-F', 250, 'arcilal', '2022-08-01 14:26:51', NULL, NULL),
	('8170-1023', '01', '02-04-E', 144, 'arcilal', '2022-08-01 14:30:14', NULL, NULL),
	('8170-1017', '01', '02-04-E', 288, 'arcilal', '2022-08-01 14:31:59', NULL, NULL),
	('8170-1018', '01', '02-04-E', 90, 'arcilal', '2022-08-01 14:35:39', NULL, NULL),
	('0020-0165', '01', '02-09-F', 384, 'arcilal', '2022-08-01 14:41:57', NULL, NULL),
	('2394-0110', '01', '02-09-F', 1848, 'arcilal', '2022-08-01 14:45:45', NULL, NULL),
	('2351-0187', '01', '02-07-F', 400, 'arcilal', '2022-08-01 15:03:17', NULL, NULL),
	('8059-0113', '01', '02-07-F', 225, 'arcilal', '2022-08-01 15:08:06', NULL, NULL),
	('0023-0150', '01', '02-06-F', 420, 'arcilal', '2022-08-01 15:37:20', NULL, NULL),
	('2352-0244', '01', '05-02-D', 1584, 'sANESJ', '2022-08-02 11:52:33', NULL, NULL),
	('7060-0218', '01', '03-06-E', 600, 'arcilal', '2022-08-08 08:49:18', NULL, NULL),
	('7008-0158', '01', '03-06-D', 330, 'arcilal', '2022-08-08 08:59:46', NULL, NULL),
	('3400-0000', '01', '03-06-E', 45, 'arcilal', '2022-08-08 09:00:54', NULL, NULL),
	('7008-0148', '01', '03-05-E', 1080, 'arcilal', '2022-08-08 09:18:25', NULL, NULL),
	('7121-0102', '01', '03-05-E', 60, 'arcilal', '2022-08-08 09:19:40', NULL, NULL),
	('7008-0159', '01', '03-05-E', 70, 'arcilal', '2022-08-08 09:20:41', NULL, NULL),
	('7198-0107', 'Selecciona...', '03-04-E', 400, 'arcilal', '2022-08-08 09:26:26', NULL, NULL),
	('7060-0020', '01', '03-04-E', 750, 'arcilal', '2022-08-08 09:27:34', NULL, NULL),
	('2320-0171', '01', '02-07-E', 1500, 'GOMEZD', '2022-08-24 09:39:06', NULL, NULL),
	('2320-0175', '01', '05-09-D', 1500, 'GOMEZD', '2022-08-24 09:43:50', NULL, NULL),
	('2320-0176', '01', '05-09-D', 2400, 'GOMEZD', '2022-08-24 09:44:17', NULL, NULL),
	('9042-0048', '01', '05-04-E', 4100, 'GOMEZD', '2022-08-24 11:29:04', NULL, NULL),
	('9042-0121', '01', '05-10-B', 1500, 'GOMEZD', '2022-08-24 11:30:11', NULL, NULL),
	('9042-0122', '01', '05-10-B', 600, 'GOMEZD', '2022-08-24 11:31:21', NULL, NULL),
	('9042-0044', '01', '05-10-B', 1000, 'GOMEZD', '2022-08-24 11:32:49', NULL, NULL),
	('9042-0046', '01', '05-10-B', 1000, 'GOMEZD', '2022-08-24 11:33:16', NULL, NULL),
	('2352-0502', '01', '02-03-F', 264, 'GOMEZD', '2022-08-24 11:46:07', NULL, NULL),
	('2352-0504', '01', '02-03-F', 720, 'GOMEZD', '2022-08-24 11:46:31', NULL, NULL),
	('2352-0505', '01', '02-03-F', 1440, 'GOMEZD', '2022-08-24 11:47:01', NULL, NULL),
	('2352-0515', '01', '02-03-F', 720, 'GOMEZD', '2022-08-24 11:47:27', NULL, NULL),
	('2352-0522', '01', '02-03-F', 768, 'GOMEZD', '2022-08-24 11:47:52', NULL, NULL),
	('2352-0523', '01', '02-03-F', 1104, 'GOMEZD', '2022-08-24 11:48:12', NULL, NULL),
	('2352-0524', '01', '02-03-F', 384, 'GOMEZD', '2022-08-24 11:48:34', NULL, NULL),
	('0800-0012', '01', '05-03-E', 384, 'GOMEZD', '2022-08-24 17:51:56', NULL, NULL),
	('0800-0012', '02', '05-08-D', 320, 'GOMEZD', '2022-08-24 17:52:47', NULL, NULL),
	('2391-2103', '01', '5-2-A', 36, 'jcfreites', '2022-08-29 14:58:10', NULL, NULL),
	('7008-0124', '01', '07-02-C', 128, 'jcfreites', '2022-09-15 10:17:48', NULL, NULL),
	('0023-0409', '01', 'RECEPCION DEVOLUCION', 166, 'sanesj', '2022-09-30 08:08:26', NULL, NULL);

-- Volcando estructura para tabla wms.treserva_productos
CREATE TABLE IF NOT EXISTS `treserva_productos` (
  `reser_idempresa` tinyint(4) NOT NULL,
  `reser_idpedido` varchar(15) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `reser_idproducto` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `reser_cantidad` decimal(18,3) NOT NULL,
  `user_crea` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `fec_crea` datetime NOT NULL,
  `user_mod` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.treserva_productos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla wms.troles
CREATE TABLE IF NOT EXISTS `troles` (
  `rol_id` tinyint(1) NOT NULL,
  `rol_nombre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.troles: ~8 rows (aproximadamente)
INSERT INTO `troles` (`rol_id`, `rol_nombre`) VALUES
	(1, 'PREPARADOR'),
	(2, 'EMBALADOR'),
	(3, 'SUPERVISOR_ALMACEN'),
	(4, 'SOPORTE_TECNICO'),
	(5, 'ESTADISTICO'),
	(6, 'TRANSPORTE'),
	(7, 'RECEPCION_CONTROL'),
	(8, 'ADMIN');

-- Volcando estructura para tabla wms.tusuarios
CREATE TABLE IF NOT EXISTS `tusuarios` (
  `user_id` int(11) NOT NULL COMMENT 'ID usuario. Unico',
  `user_uuid` char(36) CHARACTER SET utf8mb3 NOT NULL DEFAULT 'UUID()' COMMENT 'Este es un valor ID Unico para utilizarlo como parametro o variable publica en las llamadas GET o APIS que requieran el ID del usuario. Se genera con la función UUID() de Mariadb y se le quitan los guiones para hacerlo menos identificable ',
  `user_name` varchar(20) CHARACTER SET utf8mb3 NOT NULL COMMENT 'Nombre o alias de usuario. Valor unico indistintamente de la empresa.',
  `user_nombre` varchar(50) CHARACTER SET utf8mb3 NOT NULL,
  `user_apellido` varchar(50) CHARACTER SET utf8mb3 NOT NULL,
  `user_email` varchar(100) CHARACTER SET utf8mb3 NOT NULL,
  `user_salt` varchar(20) CHARACTER SET utf8mb3 DEFAULT NULL COMMENT 'Salt para generación del password',
  `user_password` char(200) CHARACTER SET utf8mb3 DEFAULT NULL,
  `user_token` varchar(100) CHARACTER SET utf8mb3 DEFAULT NULL COMMENT 'Token para validar registro y cambios de contraseña',
  `user_tokenexp` datetime DEFAULT NULL COMMENT 'Fecha de expiración del Token',
  `user_activo` int(1) NOT NULL DEFAULT 0 COMMENT '1=Activo, 0=No activo, anulado o desactivado',
  `user_tipo` varchar(1) CHARACTER SET utf8mb3 NOT NULL DEFAULT '2' COMMENT 'I=Usuario interno o empleado, 2=Usuario Externo web',
  `user_perfil` varchar(20) CHARACTER SET utf8mb3 DEFAULT 'WEB' COMMENT 'Perfil de Uusario. Determina a que tendrá acceso',
  `user_admin` int(11) NOT NULL DEFAULT 0 COMMENT '0=Normal,1=Administrador',
  `user_idempresa` int(11) DEFAULT NULL COMMENT 'Id. de empresa por defecto. Empresa que carga primero al loguearse.',
  `user_cedula` int(10) DEFAULT NULL COMMENT 'Nro. de cedula',
  `user_lastlogin` datetime DEFAULT NULL COMMENT 'Fecha de ultimo login',
  `user_crea` varchar(40) CHARACTER SET utf8mb3 DEFAULT NULL COMMENT 'usuario que crea registro',
  `fec_crea` datetime DEFAULT NULL COMMENT 'fecha de creación del registro',
  `user_mod` varchar(40) CHARACTER SET utf8mb3 DEFAULT NULL COMMENT 'usuario que modifica el registro',
  `fec_mod` datetime DEFAULT NULL COMMENT 'Fecha modificacion registro',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `tusuarios_idx03` (`user_email`),
  UNIQUE KEY `tusuarios_idx02` (`user_name`),
  UNIQUE KEY `tusuarios_idx04` (`user_uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.tusuarios: ~11 rows (aproximadamente)
INSERT INTO `tusuarios` (`user_id`, `user_uuid`, `user_name`, `user_nombre`, `user_apellido`, `user_email`, `user_salt`, `user_password`, `user_token`, `user_tokenexp`, `user_activo`, `user_tipo`, `user_perfil`, `user_admin`, `user_idempresa`, `user_cedula`, `user_lastlogin`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 'e03ecbf6-9277-11ec-9738-4c72b92166c4', 'jcfreites', 'Julio', 'Cesar', 'jcfreitesbacalao@gmail.com', NULL, '$argon2i$v=19$m=4096,t=10,p=20$TG5iT3JBUmp2emMyUTQ0Vw$w/hkMCwOhDeMuy3E9daP3ZPMQMEcULIyzaBFPcHV29Y', '87cac76ce3323d54be8567ce8322e104', '2022-10-29 09:56:39', 1, '2', 'WEB', 0, 1, NULL, NULL, 'web anonimo', '2020-09-20 14:36:24', 'jcfreites', '2022-10-29 09:56:39'),
	(3, 'ec391490-f7de-11ec-ad8c-a0d3c1262574', 'jimenezm', 'Melitza', 'Jimenez', 'melijimenez01@hotmail.com', NULL, '$argon2i$v=19$m=4096,t=10,p=20$VU5SZ1FSSUh2U2VvTURzZA$DOEJBoDF80kaqwiuqzZn9kv4hHceFXX1geksGHsHhkI', 'de628c24fd383bb3a7b3e82d78f52c95', '2022-09-08 15:14:08', 1, '2', 'WEB', 0, 1, NULL, NULL, 'jcfreites', '2022-06-29 15:13:22', 'jimenezm', '2022-09-08 15:14:08'),
	(5, '043c99c5-f7eb-11ec-8bf8-509a4c5266e1', 'distolam', 'Michel', 'Distola', 'mdistolalefre@gmail.com', NULL, '$argon2i$v=19$m=4096,t=10,p=20$TlBHbWJxeEhhZmVDNkFBQg$xEd0Yla/EIF/0qY2ArHjcenFRf7ubqzHuiilK4CIeEc', '5ad3aa231fb6c11754c455936120f3cc', '2022-06-30 16:35:30', 1, '2', 'WEB', 0, 1, NULL, NULL, 'jcfreites', '2022-06-29 16:35:30', 'jcfreites', '2022-07-21 16:19:24'),
	(7, '4dcdc741-fe20-11ec-9427-509a4c5266e1', 'marrugoe', 'Estefanía', 'Marrugo', 'lefrecobranzas5@gmail.com', NULL, '$argon2i$v=19$m=4096,t=10,p=20$dXA4Rkk0N2FmQnEuanE2bQ$gOV7Ykpn+SLBD3mD2vCWhik2hhxiugre+5dcT2lXAQY', '92ecf9aee6e4ffb531c6c3ddb4056f03', '2022-07-08 14:11:52', 1, '2', 'WEB', 0, 1, NULL, NULL, 'jcfreites', '2022-07-07 14:11:52', 'jcfreites', '2022-07-21 16:02:43'),
	(8, '8b822ff6-fe25-11ec-9427-509a4c5266e1', 'nrodriguez', 'Nilemar', 'Rodriguez', 'lefrecobranzas3@gmail.com', NULL, '$argon2i$v=19$m=4096,t=10,p=20$WjFYNFliaE9iM0FHM3Ztbw$bA3TQpM7CzXJ05zYtOz+YRbUSODyzy+rF9ZjP6FxO4s', '6f2ce619cc68ed4cb502d047a89464b1', '2022-07-08 14:49:23', 1, '2', 'WEB', 0, 1, NULL, NULL, 'jcfreites', '2022-07-07 14:49:23', 'jcfreites', '2022-07-21 16:02:46'),
	(9, '8a7ba094-fe28-11ec-9427-509a4c5266e1', 'rangell', 'Leidy', 'Rangel', 'leidy.compraslefre@gmail.com', NULL, '$argon2i$v=19$m=4096,t=10,p=20$bkVLOXl1ZmxsNmNPZ2RSeA$QNti8YnU13ENC8VY/mZjey2FkFqPIpB2bRU1OzeyoLc', '8cf149ed6bdf9ad65cf4c258033e05fb', '2022-10-27 16:26:42', 1, '2', 'WEB', 0, 1, NULL, NULL, 'jcfreites', '2022-07-07 15:10:50', 'rangell', '2022-10-27 16:26:42'),
	(10, 'af142130-fe2a-11ec-9427-509a4c5266e1', 'kobece', 'Elivy', 'Kobec', 'coordinacioncxclefre@gmail.com', NULL, '$argon2i$v=19$m=4096,t=10,p=20$dkJOSUJmYmIuM0xka280dA$TtuEx9YanH8JdpIChI6AZCtMHXjsDlearAdNu8vYuDU', 'a159b3062bf01bc2cff621eb7742631f', '2022-07-08 15:26:10', 1, '2', 'WEB', 0, 1, NULL, NULL, 'jcfreites', '2022-07-07 15:26:10', 'jcfreites', '2022-07-21 16:02:48'),
	(11, 'eb9df8e6-0930-11ed-8c17-509a4c5266e1', 'sanesj', 'Juan', 'Sanes', 'jcsamve@gmail.com', NULL, '$argon2i$v=19$m=4096,t=10,p=20$cUFQN0dYazhqNDFMYzNrSQ$riEV5mBJ367U57qsYtdlTtgt6a3b5vIpQTz01i2Uo94', 'c8f9523612cb97bd65d12fb98f58d708', '2022-07-22 16:08:41', 1, '2', 'WEB', 0, 1, NULL, NULL, 'jcfreites', '2022-07-21 16:08:41', 'jcfreites', '2022-07-21 16:19:02'),
	(12, '0aec1577-0931-11ed-8c17-509a4c5266e1', 'gomezd', 'Daniel', 'Gomez', 'danielgotorres@gmail.com', NULL, '$argon2i$v=19$m=4096,t=10,p=20$WHl1OUxSWGtRVlZnVVZYLw$GtDoodvXwFQcJY9TtAxivntYja6vFBC8i9r5krCLKEU', '24b6f147ec913cea04e4d89eaa4348f2', '2022-07-22 16:09:34', 1, '2', 'WEB', 0, 1, NULL, NULL, 'jcfreites', '2022-07-21 16:09:34', 'jcfreites', '2022-07-21 16:19:13'),
	(13, 'bbd67a9a-0c2c-11ed-914c-509a4c5266e1', 'arcilal', 'Lisset', 'Arcila', 'arcilalisset83@gmail.com', NULL, '$argon2i$v=19$m=4096,t=10,p=20$b1oyZ1d2S05acjdjS2RZeg$WVx8exqJhrvoVDCArHQv29cIXhb6QrVmeQ0CVNNvlPw', 'd15ebaedb3cbe465c77775def552c4b4', '2022-07-26 11:16:18', 1, '2', 'WEB', 0, 1, NULL, NULL, 'jcfreites', '2022-07-25 11:16:18', 'jcfreites', '2022-07-25 11:17:53'),
	(14, '82054cef-57ae-11ed-97a5-509a4c5266e1', 'sanchezj', 'Julio', 'Sanchez', 'julioalexandersanchezveiza@gmail.com', NULL, '$argon2i$v=19$m=4096,t=10,p=20$bHE5aEZFR3ZITlZpVlpjdw$ISwRTGNHjrzq+dXACecuglwJhNNdztzMUXULRILSoDY', 'aa6c7c32cfc752c8d35d3f76963f651c', '2022-10-30 13:24:20', 0, '2', 'WEB', 0, 1, NULL, NULL, 'jcfreites', '2022-10-29 13:24:21', NULL, NULL);

-- Volcando estructura para tabla wms.tusuarios_roles
CREATE TABLE IF NOT EXISTS `tusuarios_roles` (
  `usrr_name` varchar(20) NOT NULL DEFAULT '',
  `usrr_role` varchar(20) NOT NULL,
  `user_crea` varchar(20) NOT NULL,
  `fec_crea` date NOT NULL,
  `user_mod` varchar(20) DEFAULT NULL,
  `fec_mod` date DEFAULT NULL,
  PRIMARY KEY (`usrr_name`,`usrr_role`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.tusuarios_roles: ~10 rows (aproximadamente)
INSERT INTO `tusuarios_roles` (`usrr_name`, `usrr_role`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	('arcilal', 'PREPARADOR', 'jcfreites', '2022-09-12', NULL, NULL),
	('arcilal', 'RECEPCION_CONTROL', 'jcfreites', '2022-08-29', 'jcfreites', '2022-08-29'),
	('gomezd', 'RECEPCION_CONTROL', 'jcfreites', '2022-08-30', NULL, NULL),
	('jcfreites', 'ADMIN', 'jcfreites', '2022-06-19', 'jcfreites', '2022-07-26'),
	('jcfreites', 'EMBALADOR', 'jcfreites', '2022-10-30', NULL, NULL),
	('jcfreites', 'PREPARADOR', 'jcfreites', '2022-09-12', NULL, NULL),
	('jcfreites', 'SOPORTE_TECNICO', 'jcfreites', '2022-08-16', 'jcfreites', '2022-08-29'),
	('jimenezm', 'ADMIN', 'jcfreites', '2022-09-08', NULL, NULL),
	('nrodriguez', 'PEDIDOS', 'jcfreites', '2022-10-28', NULL, NULL),
	('rangell', 'ADMIN', 'jcfreites', '2022-09-12', NULL, NULL),
	('sanchezj', 'EMBALADOR', 'jcfreites', '2022-10-30', NULL, NULL),
	('sanesj', 'RECEPCION_CONTROL', 'jcfreites', '2022-08-30', NULL, NULL);

-- Volcando estructura para vista wms.vpacking
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vpacking` (
	`pack_idempresa` TINYINT(4) NOT NULL,
	`pack_idpacking` INT(11) NOT NULL COMMENT 'Id. Tarea de Packing',
	`pack_fecha` DATETIME NOT NULL COMMENT 'Fecha en que pasó a Packing. Distinto a la fecha en que se inicia. ',
	`pack_idpedido` VARCHAR(15) NOT NULL COMMENT 'Id del Pedido.' COLLATE 'utf8mb4_unicode_ci',
	`pack_idpicking` INT(11) NOT NULL COMMENT 'Id. del Picking. En caso que venga del picking.',
	`pack_fecinicio` DATETIME NULL COMMENT 'Inicio de tarea de Packing por parte del embalador.',
	`pack_embalador` VARCHAR(20) NULL COMMENT 'Es el username del preparador asignado. ' COLLATE 'utf8mb4_unicode_ci',
	`pack_status` TINYINT(1) NOT NULL COMMENT '0=Anulado, 1=En Proceso, 2=Pausado, 5=Culminado',
	`pack_prioridad` TINYINT(1) NOT NULL COMMENT '0=Normal, 1=Urgente',
	`pack_fecierre` DATETIME NULL COMMENT 'Fecha de cierre de la tarea.',
	`pack_pista` TINYINT(1) NULL COMMENT 'Indica la pista dónde se coloca el pedido una vez consolidado, para pasar al picking.',
	`pack_observacion` VARCHAR(100) NULL COLLATE 'utf8mb4_unicode_ci',
	`user_crea` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`fec_crea` DATETIME NOT NULL,
	`user_mod` VARCHAR(50) NULL COLLATE 'utf8mb4_unicode_ci',
	`fec_mod` DATETIME NULL COMMENT 'Fecha ultima modificación o cierre de tarea.'
) ENGINE=MyISAM;

-- Volcando estructura para vista wms.vpacking_bultos
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vpacking_bultos` (
	`pack_idempresa` TINYINT(4) NOT NULL,
	`pack_idpacking` INT(11) NOT NULL COMMENT 'Id. Tarea de Packing',
	`pack_idbulto` TINYINT(2) NOT NULL,
	`pack_peso` DECIMAL(7,2) NOT NULL,
	`pack_unidadpeso` VARCHAR(3) NOT NULL COMMENT 'gr = gramos, Kg=Kilos,' COLLATE 'utf8mb3_general_ci',
	`pack_status` BIT(1) NOT NULL COMMENT '0=Abierto, 1=Cerrado',
	`user_crea` VARCHAR(50) NOT NULL COLLATE 'utf8mb3_general_ci',
	`fec_crea` DATETIME NOT NULL,
	`user_mod` VARCHAR(50) NULL COLLATE 'utf8mb3_general_ci',
	`fec_mod` DATETIME NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista wms.vpacking_detalle
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vpacking_detalle` (
	`pacd_idempresa` TINYINT(4) NOT NULL,
	`pacd_idpacking` INT(11) NOT NULL,
	`pacd_idproducto` VARCHAR(20) NOT NULL COLLATE 'utf8mb3_general_ci',
	`pacd_unidad` VARCHAR(7) NULL COLLATE 'utf8mb4_general_ci',
	`pacd_requerido` SMALLINT(1) NOT NULL COMMENT 'Cantidad requerida a embalar',
	`pacd_cantidad` SMALLINT(1) NULL COMMENT 'Cantidad  embalada del producto indicado',
	`user_crea` VARCHAR(50) NOT NULL COLLATE 'utf8mb3_general_ci',
	`fec_crea` DATETIME NOT NULL,
	`user_mod` VARCHAR(50) NULL COLLATE 'utf8mb3_general_ci',
	`fec_mod` DATETIME NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista wms.vpacking_productos
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vpacking_productos` (
	`pacp_idempresa` TINYINT(1) NOT NULL,
	`pacp_idpacking` INT(11) NOT NULL,
	`pacp_idbulto` TINYINT(2) NOT NULL,
	`pacp_idproducto` VARCHAR(20) NOT NULL COLLATE 'utf8mb3_general_ci',
	`pacp_cantidad` SMALLINT(1) NOT NULL,
	`user_crea` VARCHAR(20) NOT NULL COLLATE 'utf8mb3_general_ci',
	`fec_crea` DATETIME NOT NULL,
	`user_mod` VARCHAR(20) NULL COLLATE 'utf8mb3_general_ci',
	`fec_mod` DATETIME NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista wms.vpacking_tarea
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vpacking_tarea` (
	`pack_idpacking` INT(11) NOT NULL COMMENT 'Id. Tarea de Packing',
	`pack_idpedido` VARCHAR(15) NOT NULL COMMENT 'Id del Pedido.' COLLATE 'utf8mb4_unicode_ci',
	`pacd_idproducto` VARCHAR(20) NOT NULL COLLATE 'utf8mb3_general_ci',
	`pacd_requerido` SMALLINT(1) NOT NULL COMMENT 'Cantidad requerida a embalar'
) ENGINE=MyISAM;

-- Volcando estructura para vista wms.vpicking
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vpicking` (
	`pick_idempresa` TINYINT(4) NOT NULL,
	`pick_idpicking` INT(11) NOT NULL,
	`pick_fecha` DATETIME NOT NULL,
	`pick_idpedido` VARCHAR(15) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`pick_preparador` VARCHAR(20) NOT NULL COMMENT 'Es el username del preparador asignado.' COLLATE 'utf8mb4_unicode_ci',
	`pick_status` TINYINT(1) NOT NULL COMMENT '0=Anulado, 1=En Proceso, 2=Pausado, 3=Consolidado,\r\n5=Cerrada',
	`pick_prioridad` TINYINT(1) NULL COMMENT '0=Normal, 1=Urgente',
	`pick_fecierre` DATETIME NULL COMMENT 'Fecha en la que el preparador termina de anclar todos los productos a la tarea de picking.',
	`pick_pista` TINYINT(1) NULL COMMENT 'Indica la pista dónde se coloca el pedido una vez consolidado, para pasar al picking.',
	`pick_observacion` VARCHAR(100) NULL COLLATE 'utf8mb4_unicode_ci',
	`pick_userverif` VARCHAR(20) NULL COMMENT 'Usuario verificador' COLLATE 'utf8mb4_unicode_ci',
	`pick_fecverif` DATETIME NULL COMMENT 'Fecha en que fue verificada y cerrada la tarea de picking.',
	`user_crea` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`fec_crea` DATETIME NOT NULL COMMENT 'Fecha de inicio de la tarea.',
	`user_mod` VARCHAR(50) NULL COLLATE 'utf8mb4_unicode_ci',
	`fec_mod` DATETIME NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista wms.vpicking_bins
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vpicking_bins` (
	`picc_idempresa` TINYINT(4) NOT NULL,
	`picc_idpicking` INT(11) NOT NULL,
	`picc_bin` VARCHAR(10) NOT NULL COMMENT 'Bandeja o palet dónde sera ubicado el pedido consolidado para pasar al packing.' COLLATE 'utf8mb4_unicode_ci',
	`user_crea` VARCHAR(20) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`fec_crea` DATETIME NOT NULL,
	`user_mod` VARCHAR(20) NULL COLLATE 'utf8mb4_unicode_ci',
	`fec_mod` DATETIME NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista wms.vpicking_detalle
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vpicking_detalle` (
	`picd_idempresa` TINYINT(4) NOT NULL,
	`picd_idpicking` INT(11) NOT NULL,
	`picd_idproducto` VARCHAR(20) NOT NULL COLLATE 'utf8mb3_general_ci',
	`picd_unidad` VARCHAR(7) NULL COLLATE 'utf8mb3_general_ci',
	`picd_idalmacen` VARCHAR(10) NOT NULL COLLATE 'utf8mb3_general_ci',
	`picd_ubicacion` VARCHAR(20) NULL COLLATE 'utf8mb3_general_ci',
	`picd_requerido` SMALLINT(1) NOT NULL COMMENT 'Cantidad requerida en el pedido.',
	`picd_cantidad` SMALLINT(1) NULL COMMENT 'Cantidad anclada al picking del producto indicado. NULL= el preparador no ha hecho nada con el producto.',
	`picd_cantverif` SMALLINT(1) NULL COMMENT 'Cantidad verificada',
	`user_crea` VARCHAR(50) NOT NULL COLLATE 'utf8mb3_general_ci',
	`fec_crea` DATETIME NOT NULL,
	`user_mod` VARCHAR(50) NULL COLLATE 'utf8mb3_general_ci',
	`fec_mod` DATETIME NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista wms.vpicking_pistas
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vpicking_pistas` (
	`picp_idvendedor` VARCHAR(10) NOT NULL COMMENT 'Codigo del Vendedor. Viene del Xenx.' COLLATE 'latin1_swedish_ci',
	`picp_pista` TINYINT(1) NOT NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista wms.vpicking_tarea
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vpicking_tarea` (
	`pick_idpedido` VARCHAR(15) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`picd_idproducto` VARCHAR(20) NOT NULL COLLATE 'utf8mb3_general_ci',
	`picd_requerido` SMALLINT(1) NOT NULL COMMENT 'Cantidad requerida en el pedido.'
) ENGINE=MyISAM;

-- Volcando estructura para vista wms.vproductos_ubica
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vproductos_ubica` (
	`prou_idproducto` VARCHAR(20) NOT NULL COLLATE 'utf8mb4_general_ci',
	`prou_almacen` VARCHAR(20) NOT NULL COMMENT 'Almacén donde está ubicado' COLLATE 'utf8mb4_general_ci',
	`prou_ubicacion` VARCHAR(20) NOT NULL COMMENT 'Ubicación en formato Pasillo-Rack-Nivel. Ejemplo: 01-01-A' COLLATE 'utf8mb4_general_ci',
	`prou_cantidad` INT(1) NOT NULL,
	`user_crea` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
	`fec_crea` DATETIME NOT NULL,
	`user_mod` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
	`fec_mod` DATETIME NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista wms.vstats_preparador
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vstats_preparador` (
	`pick_preparador` VARCHAR(20) NOT NULL COMMENT 'Es el username del preparador asignado.' COLLATE 'utf8mb4_unicode_ci',
	`tareas` BIGINT(21) NOT NULL,
	`cant_sku` DECIMAL(42,0) NULL,
	`sku_culminados` DECIMAL(44,0) NULL,
	`cant_productos` DECIMAL(49,0) NULL,
	`cant_anclados` DECIMAL(49,0) NULL,
	`porcentaje` DECIMAL(47,0) NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista wms.vstats_preparador2
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vstats_preparador2` (
	`user_name` VARCHAR(20) NOT NULL COMMENT 'Nombre o alias de usuario. Valor unico indistintamente de la empresa.' COLLATE 'utf8mb3_general_ci',
	`user_nombre` VARCHAR(50) NOT NULL COLLATE 'utf8mb3_general_ci',
	`user_apellido` VARCHAR(50) NOT NULL COLLATE 'utf8mb3_general_ci',
	`tareas` BIGINT(21) NOT NULL,
	`cant_sku` DECIMAL(42,0) NOT NULL,
	`sku_culminados` DECIMAL(44,0) NOT NULL,
	`cant_productos` DECIMAL(49,0) NOT NULL,
	`cant_anclados` DECIMAL(49,0) NOT NULL,
	`porcentaje` DECIMAL(47,0) NOT NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista wms.vusuarios
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vusuarios` (
	`user_id` INT(11) NOT NULL COMMENT 'ID usuario. Unico',
	`user_uuid` CHAR(36) NOT NULL COMMENT 'Este es un valor ID Unico para utilizarlo como parametro o variable publica en las llamadas GET o APIS que requieran el ID del usuario. Se genera con la función UUID() de Mariadb y se le quitan los guiones para hacerlo menos identificable ' COLLATE 'utf8mb3_general_ci',
	`user_name` VARCHAR(20) NOT NULL COMMENT 'Nombre o alias de usuario. Valor unico indistintamente de la empresa.' COLLATE 'utf8mb3_general_ci',
	`user_nombre` VARCHAR(50) NOT NULL COLLATE 'utf8mb3_general_ci',
	`user_apellido` VARCHAR(50) NOT NULL COLLATE 'utf8mb3_general_ci',
	`user_email` VARCHAR(100) NOT NULL COLLATE 'utf8mb3_general_ci',
	`user_salt` VARCHAR(20) NULL COMMENT 'Salt para generación del password' COLLATE 'utf8mb3_general_ci',
	`user_password` CHAR(200) NULL COLLATE 'utf8mb3_general_ci',
	`user_token` VARCHAR(100) NULL COMMENT 'Token para validar registro y cambios de contraseña' COLLATE 'utf8mb3_general_ci',
	`user_tokenexp` DATETIME NULL COMMENT 'Fecha de expiración del Token',
	`user_activo` INT(1) NOT NULL COMMENT '1=Activo, 0=No activo, anulado o desactivado',
	`user_tipo` VARCHAR(1) NOT NULL COMMENT 'I=Usuario interno o empleado, 2=Usuario Externo web' COLLATE 'utf8mb3_general_ci',
	`user_perfil` VARCHAR(20) NULL COMMENT 'Perfil de Uusario. Determina a que tendrá acceso' COLLATE 'utf8mb3_general_ci',
	`user_admin` INT(11) NOT NULL COMMENT '0=Normal,1=Administrador',
	`user_idempresa` INT(11) NULL COMMENT 'Id. de empresa por defecto. Empresa que carga primero al loguearse.',
	`user_cedula` INT(10) NULL COMMENT 'Nro. de cedula',
	`user_lastlogin` DATETIME NULL COMMENT 'Fecha de ultimo login',
	`user_crea` VARCHAR(40) NULL COMMENT 'usuario que crea registro' COLLATE 'utf8mb3_general_ci',
	`fec_crea` DATETIME NULL COMMENT 'fecha de creación del registro',
	`user_mod` VARCHAR(40) NULL COMMENT 'usuario que modifica el registro' COLLATE 'utf8mb3_general_ci',
	`fec_mod` DATETIME NULL COMMENT 'Fecha modificacion registro'
) ENGINE=MyISAM;

-- Volcando estructura para vista wms.v_picking_prod_anclados
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_picking_prod_anclados` (
	`picd_idproducto` VARCHAR(20) NOT NULL COLLATE 'utf8mb3_general_ci',
	`anclado` DECIMAL(27,0) NULL
) ENGINE=MyISAM;

-- Volcando estructura para disparador wms.tempresas_tr01
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tempresas_tr01` BEFORE INSERT ON `tempresas` FOR EACH ROW begin
SET new.fec_crea = CURRENT_TIMESTAMP;
SET new.empr_idempresa= fuser_empresa();
IF new.user_crea iS NULL or LENGTH(new.user_crea) = 0 THEN
	SET new.user_crea = CURRENT_USER;
END IF;   
end//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tempresas_tr02
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tempresas_tr02` BEFORE UPDATE ON `tempresas` FOR EACH ROW SET new.fec_mod=CURRENT_TIMESTAMP//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpacking_bultos_tr01
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpacking_bultos_tr01` BEFORE INSERT ON `tpacking_bultos` FOR EACH ROW BEGIN
SET new.fec_crea = CURRENT_TIMESTAMP;
IF new.user_crea iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de creación';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpacking_bultos_tr02
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpacking_bultos_tr02` BEFORE UPDATE ON `tpacking_bultos` FOR EACH ROW BEGIN
SET new.fec_mod = CURRENT_TIMESTAMP;
IF new.user_mod iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de update';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpacking_bultos_tr03
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpacking_bultos_tr03` BEFORE INSERT ON `tpacking_bultos` FOR EACH ROW BEGIN
IF fpack_bultos_status(new.pack_idpacking) > 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede crear caja nueva si existe alguna abierta';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpacking_bultos_tr04
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpacking_bultos_tr04` BEFORE UPDATE ON `tpacking_bultos` FOR EACH ROW BEGIN
if old.pack_status=0 and new.pack_status=1 then
	IF fpack_bultos_productos(new.pack_idpacking,new.pack_idbulto) = 0 THEN
    	SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No puede cerrar una caja sin productos';
	END IF;
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpacking_detalle_tr01
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpacking_detalle_tr01` BEFORE INSERT ON `tpacking_detalle` FOR EACH ROW BEGIN
SET new.fec_crea = CURRENT_TIMESTAMP;
IF new.user_crea iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de creación';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpacking_detalle_tr02
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpacking_detalle_tr02` BEFORE UPDATE ON `tpacking_detalle` FOR EACH ROW BEGIN
SET new.fec_mod = CURRENT_TIMESTAMP;
IF new.user_mod iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de update';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpacking_productos_tr01
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpacking_productos_tr01` BEFORE INSERT ON `tpacking_productos` FOR EACH ROW BEGIN
SET new.fec_crea = CURRENT_TIMESTAMP;
IF new.user_crea iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de creación';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpacking_productos_tr02
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpacking_productos_tr02` BEFORE UPDATE ON `tpacking_productos` FOR EACH ROW BEGIN
SET new.fec_mod = CURRENT_TIMESTAMP;
IF new.user_mod iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de update';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpacking_productos_tr03
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpacking_productos_tr03` AFTER DELETE ON `tpacking_productos` FOR EACH ROW BEGIN
/* Devuelve la cantidad apartada a la tabla tpacking_detalle */
UPDATE tpacking_detalle SET pacd_cantidad = pacd_cantidad - old.pacp_cantidad WHERE pacd_idempresa = old.pacp_idempresa AND pacd_idpacking = old.pacp_idpacking AND pacd_idproducto = old.pacp_idproducto;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpacking_tr01
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpacking_tr01` BEFORE INSERT ON `tpacking` FOR EACH ROW BEGIN
SET new.fec_crea = CURRENT_TIMESTAMP;
IF new.user_crea iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de creación';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpacking_tr02
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tpacking_tr02` BEFORE UPDATE ON `tpacking` FOR EACH ROW BEGIN
IF old.pack_status = 5 AND new.pack_status = 5  THEN
    SIGNAL SQLSTATE '45101' SET MESSAGE_TEXT = 'No puede modificar datos de una tarea cerrada';
END IF;
SET new.fec_mod = CURRENT_TIMESTAMP;
IF new.user_mod iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de update';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpacking_tr03
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpacking_tr03` BEFORE UPDATE ON `tpacking` FOR EACH ROW BEGIN
if old.pack_status<>5 and new.pack_status=5 then
	SET new.pack_fecierre = CURRENT_TIMESTAMP;
	IF fpack_bultos_status(new.pack_idpacking) > 0 THEN  
    	SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No puede cerrar la tarea de packing si hay bultos abiertos';
	END IF;
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpacking_tr04
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpacking_tr04` BEFORE UPDATE ON `tpacking` FOR EACH ROW BEGIN
	/* Aquí verificamos que todos los productos fueron procesados para poder cerrar la tarea de picking */
    DECLARE lnregistros INT;
	IF old.pack_status <> 5 AND new.pack_status = 5 THEN
	        SELECT count(*)
        	INTO lnregistros
		FROM tpacking_detalle
        	WHERE pacd_idempresa = new.pack_idempresa
	        AND pacd_idpacking = new.pack_idpacking
        	AND pacd_cantidad is null;
	    	IF lnregistros > 0 THEN  
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede culminar una tarea con productos sin procesar';
		END IF;
	END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpedidos_status_tr01
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpedidos_status_tr01` BEFORE INSERT ON `tpedidos_status` FOR EACH ROW BEGIN
SET new.fec_crea = CURRENT_TIMESTAMP;
IF new.user_crea iS NULL THEN
    SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'No hau usuario de creación';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpedidos_status_tr02
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpedidos_status_tr02` BEFORE UPDATE ON `tpedidos_status` FOR EACH ROW BEGIN
SET new.fec_mod = CURRENT_TIMESTAMP;
IF new.user_mod iS NULL THEN
    SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'No hau usuario de update';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpicking_container_tr01
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpicking_container_tr01` BEFORE INSERT ON `tpicking_bins` FOR EACH ROW BEGIN
SET new.fec_crea = CURRENT_TIMESTAMP;
IF new.user_crea iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de creación';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpicking_container_tr02
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpicking_container_tr02` BEFORE UPDATE ON `tpicking_bins` FOR EACH ROW BEGIN
SET new.fec_mod = CURRENT_TIMESTAMP;
IF new.user_mod iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de update';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpicking_detalle_tr01
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpicking_detalle_tr01` BEFORE INSERT ON `tpicking_detalle` FOR EACH ROW BEGIN
SET new.fec_crea = CURRENT_TIMESTAMP;
IF new.user_crea iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de creación';
END IF;
IF new.picd_cantidad > new.picd_requerido THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cantidad no puede superar lo requerido';
END IF;
IF new.picd_cantidad < 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cantidad no puede ser menor a cero';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpicking_detalle_tr02
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tpicking_detalle_tr02` BEFORE UPDATE ON `tpicking_detalle` FOR EACH ROW BEGIN
SET new.fec_mod = CURRENT_TIMESTAMP;
IF new.user_mod iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de update';
END IF;
IF new.picd_cantidad > new.picd_requerido THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cantidad no puede superar lo requerido';
END IF;
IF new.picd_cantverif > new.picd_cantidad THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cantidad a verificar no puede superar lo anclado';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpicking_detalle_tr03
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpicking_detalle_tr03` BEFORE INSERT ON `tpicking_detalle` FOR EACH ROW BEGIN
	DECLARE lnstatus INT;
	SELECT fpicking_status(new.picd_idpicking)
  	  INTO lnstatus;
    IF lnstatus = 3 THEN  
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No puede modificar una tarea de picking cerrada';
	END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpicking_detalle_tr04
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpicking_detalle_tr04` BEFORE UPDATE ON `tpicking_detalle` FOR EACH ROW BEGIN
	DECLARE lnstatus INT;
	SELECT fpicking_status(new.picd_idpicking)
  	  INTO lnstatus;
    IF lnstatus = 5 THEN  
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No puede modificar una tarea de picking cerrada';
	END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpicking_detalle_tr05
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpicking_detalle_tr05` BEFORE DELETE ON `tpicking_detalle` FOR EACH ROW BEGIN
	DECLARE lnstatus INT;
	SELECT fpicking_status(old.picd_idpicking)
  	  INTO lnstatus;
    IF lnstatus = 5 THEN  
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No puede modificar una tarea de picking cerrada';
	END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpicking_tr01
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tpicking_tr01` BEFORE INSERT ON `tpicking` FOR EACH ROW BEGIN
SET new.fec_crea = CURRENT_TIMESTAMP;
IF new.user_crea iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de creación';
END IF;
IF fvalida_preparador(NEW.pick_preparador)=0 THEN
    SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO=30001, MESSAGE_TEXT = 'preparador no valido';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpicking_tr02
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpicking_tr02` BEFORE UPDATE ON `tpicking` FOR EACH ROW BEGIN
IF old.pick_status = 5 AND new.pick_status = 5 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No puede modificar datos de una tarea cerrada';
END IF;
SET new.fec_mod = CURRENT_TIMESTAMP;
IF new.user_mod iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de update';
END IF;
IF new.pick_status = 3 THEN
    SET new.pick_fecierre = CURRENT_TIMESTAMP;
END IF;
IF new.pick_status = 5 THEN
    SET new.pick_fecverif = CURRENT_TIMESTAMP;
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tpicking_tr04
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `tpicking_tr04` BEFORE UPDATE ON `tpicking` FOR EACH ROW BEGIN
	/* Aquí verificamos que todos los productos fueron procesados para poder cerrar la tarea de picking */
    DECLARE lnregistros INT;
	IF old.pick_status <> 3 AND new.pick_status = 3 THEN
	        SELECT count(*)
        	INTO lnregistros
		FROM tpicking_detalle
        	WHERE picd_idempresa = new.pick_idempresa
	        AND picd_idpicking = new.pick_idpicking
        	AND picd_cantidad is null;
	    	IF lnregistros > 0 THEN  
			SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede culminar una tarea con productos sin procesar';
		END IF;
	END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tproductos_ubica_tr01
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tproductos_ubica_tr01` BEFORE INSERT ON `tproductos_ubica` FOR EACH ROW BEGIN
SET new.fec_crea = CURRENT_TIMESTAMP;
IF new.user_crea iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de creación';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tproductos_ubica_tr02
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tproductos_ubica_tr02` BEFORE UPDATE ON `tproductos_ubica` FOR EACH ROW BEGIN
SET new.fec_mod = CURRENT_TIMESTAMP;
IF new.user_mod iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de update';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tusuarios_roles_tr01
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tusuarios_roles_tr01` BEFORE INSERT ON `tusuarios_roles` FOR EACH ROW BEGIN
SET new.fec_crea = CURRENT_TIMESTAMP;
IF new.user_crea iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de creación';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tusuarios_roles_tr02
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tusuarios_roles_tr02` BEFORE UPDATE ON `tusuarios_roles` FOR EACH ROW BEGIN
SET new.fec_mod = CURRENT_TIMESTAMP;
IF new.user_mod iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de update';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tusuarios_tr01
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tusuarios_tr01` BEFORE INSERT ON `tusuarios` FOR EACH ROW BEGIN
SET new.fec_crea = CURRENT_TIMESTAMP;
IF new.user_crea iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de creación';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador wms.tusuarios_tr02
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tusuarios_tr02` BEFORE UPDATE ON `tusuarios` FOR EACH ROW BEGIN
SET new.fec_mod = CURRENT_TIMESTAMP;
IF new.user_mod iS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay usuario de update';
END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para vista wms.vpacking
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vpacking`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vpacking` AS SELECT * FROM `tpacking` ;

-- Volcando estructura para vista wms.vpacking_bultos
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vpacking_bultos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vpacking_bultos` AS SELECT `tpacking_bultos`.`pack_idempresa` AS `pack_idempresa`, `tpacking_bultos`.`pack_idpacking` AS `pack_idpacking`, `tpacking_bultos`.`pack_idbulto` AS `pack_idbulto`, `tpacking_bultos`.`pack_peso` AS `pack_peso`, `tpacking_bultos`.`pack_unidadpeso` AS `pack_unidadpeso`, `tpacking_bultos`.`pack_status` AS `pack_status`, `tpacking_bultos`.`user_crea` AS `user_crea`, `tpacking_bultos`.`fec_crea` AS `fec_crea`, `tpacking_bultos`.`user_mod` AS `user_mod`, `tpacking_bultos`.`fec_mod` AS `fec_mod` FROM `tpacking_bultos` ;

-- Volcando estructura para vista wms.vpacking_detalle
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vpacking_detalle`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vpacking_detalle` AS SELECT `tpacking_detalle`.`pacd_idempresa` AS `pacd_idempresa`, `tpacking_detalle`.`pacd_idpacking` AS `pacd_idpacking`, `tpacking_detalle`.`pacd_idproducto` AS `pacd_idproducto`, `tpacking_detalle`.`pacd_unidad` AS `pacd_unidad`, `tpacking_detalle`.`pacd_requerido` AS `pacd_requerido`, `tpacking_detalle`.`pacd_cantidad` AS `pacd_cantidad`, `tpacking_detalle`.`user_crea` AS `user_crea`, `tpacking_detalle`.`fec_crea` AS `fec_crea`, `tpacking_detalle`.`user_mod` AS `user_mod`, `tpacking_detalle`.`fec_mod` AS `fec_mod` FROM `tpacking_detalle` ;

-- Volcando estructura para vista wms.vpacking_productos
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vpacking_productos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vpacking_productos` AS SELECT `tpacking_productos`.`pacp_idempresa` AS `pacp_idempresa`, `tpacking_productos`.`pacp_idpacking` AS `pacp_idpacking`, `tpacking_productos`.`pacp_idbulto` AS `pacp_idbulto`, `tpacking_productos`.`pacp_idproducto` AS `pacp_idproducto`, `tpacking_productos`.`pacp_cantidad` AS `pacp_cantidad`, `tpacking_productos`.`user_crea` AS `user_crea`, `tpacking_productos`.`fec_crea` AS `fec_crea`, `tpacking_productos`.`user_mod` AS `user_mod`, `tpacking_productos`.`fec_mod` AS `fec_mod` FROM `tpacking_productos` ;

-- Volcando estructura para vista wms.vpacking_tarea
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vpacking_tarea`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vpacking_tarea` AS SELECT a.pack_idpacking, a.pack_idpedido, b.pacd_idproducto, b.pacd_requerido
FROM vpacking a 
JOIN vpacking_detalle b ON a.pack_idempresa = b.pacd_idempresa AND a.pack_idpacking = b.pacd_idpacking ;

-- Volcando estructura para vista wms.vpicking
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vpicking`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vpicking` AS SELECT * FROM `tpicking` ;

-- Volcando estructura para vista wms.vpicking_bins
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vpicking_bins`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vpicking_bins` AS SELECT `tpicking_bins`.`picc_idempresa` AS `picc_idempresa`, `tpicking_bins`.`picc_idpicking` AS `picc_idpicking`, `tpicking_bins`.`picc_bin` AS `picc_bin`, `tpicking_bins`.`user_crea` AS `user_crea`, `tpicking_bins`.`fec_crea` AS `fec_crea`, `tpicking_bins`.`user_mod` AS `user_mod`, `tpicking_bins`.`fec_mod` AS `fec_mod` FROM `tpicking_bins` ;

-- Volcando estructura para vista wms.vpicking_detalle
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vpicking_detalle`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vpicking_detalle` AS SELECT `tpicking_detalle`.`picd_idempresa` AS `picd_idempresa`, `tpicking_detalle`.`picd_idpicking` AS `picd_idpicking`, `tpicking_detalle`.`picd_idproducto` AS `picd_idproducto`, `tpicking_detalle`.`picd_unidad` AS `picd_unidad`, `tpicking_detalle`.`picd_idalmacen` AS `picd_idalmacen`, `tpicking_detalle`.`picd_ubicacion` AS `picd_ubicacion`, `tpicking_detalle`.`picd_requerido` AS `picd_requerido`, `tpicking_detalle`.`picd_cantidad` AS `picd_cantidad`, `tpicking_detalle`.`picd_cantverif` AS `picd_cantverif`, `tpicking_detalle`.`user_crea` AS `user_crea`, `tpicking_detalle`.`fec_crea` AS `fec_crea`, `tpicking_detalle`.`user_mod` AS `user_mod`, `tpicking_detalle`.`fec_mod` AS `fec_mod` FROM `tpicking_detalle` ;

-- Volcando estructura para vista wms.vpicking_pistas
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vpicking_pistas`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vpicking_pistas` AS SELECT `tpicking_pistas`.`picp_idvendedor` AS `picp_idvendedor`, `tpicking_pistas`.`picp_pista` AS `picp_pista` FROM `tpicking_pistas` ;

-- Volcando estructura para vista wms.vpicking_tarea
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vpicking_tarea`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vpicking_tarea` AS SELECT a.pick_idpedido AS pick_idpedido, b.picd_idproducto AS picd_idproducto, b.picd_requerido AS picd_requerido 
FROM vpicking a 
join vpicking_detalle b ON a.pick_idempresa = b.picd_idempresa AND a.pick_idpicking = b.picd_idpicking ;

-- Volcando estructura para vista wms.vproductos_ubica
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vproductos_ubica`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vproductos_ubica` AS SELECT * FROM tproductos_ubica ;

-- Volcando estructura para vista wms.vstats_preparador
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vstats_preparador`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vstats_preparador` AS select a.pick_preparador,count(a.pick_idpicking) as tareas, SUM(tabla1.cant_sku) AS cant_sku, SUM(tabla1.sku_culminados) AS sku_culminados, SUM(tabla1.cant_productos) AS cant_productos, SUM(tabla1.cant_anclados) AS cant_anclados,
truncate(SUM(tabla1.sku_culminados) / SUM(tabla1.cant_sku) * 100,0) AS `porcentaje` 
FROM vpicking a, (select b.picd_idpicking, count(`b`.`picd_idproducto`) AS `cant_sku`,sum(case when `b`.`picd_cantidad` is null then 0 else 1 end) AS `sku_culminados`,sum(`b`.`picd_requerido`) AS `cant_productos`,ifnull(sum(`b`.`picd_cantidad`),0) AS `cant_anclados`
from vpicking_detalle b
WHERE exists (SELECT z.pick_idpicking FROM vpicking z WHERE z.pick_idempresa = b.picd_idempresa AND z.pick_idpicking = b.picd_idpicking AND z.pick_status = 1)
group by b.picd_idpicking) tabla1
where a.pick_status = 1 
AND a.pick_idpicking = tabla1.picd_idpicking
group by a.pick_preparador ;

-- Volcando estructura para vista wms.vstats_preparador2
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vstats_preparador2`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vstats_preparador2` AS SELECT a.user_name, a.user_nombre, a.user_apellido, 
IFNULL(b.tareas,0) AS tareas,
IFNULL(b.cant_sku,0) AS cant_sku,
IFNULL(b.sku_culminados,0) AS sku_culminados,
IFNULL(b.cant_productos,0) AS cant_productos,
IFNULL(b.cant_anclados,0) AS cant_anclados,
IFNULL(b.porcentaje,0) AS porcentaje
FROM tusuarios a 
LEFT JOIN vstats_preparador b ON b.pick_preparador = a.user_name
WHERE a.user_activo = 1 AND EXISTS (SELECT z.usrr_role FROM tusuarios_roles z WHERE z.usrr_role = 'PREPARADOR' AND z.usrr_name = a.user_name) ;

-- Volcando estructura para vista wms.vusuarios
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vusuarios`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vusuarios` AS SELECT `tusuarios`.`user_id` AS `user_id`, `tusuarios`.`user_uuid` AS `user_uuid`, `tusuarios`.`user_name` AS `user_name`, `tusuarios`.`user_nombre` AS `user_nombre`, `tusuarios`.`user_apellido` AS `user_apellido`, `tusuarios`.`user_email` AS `user_email`, `tusuarios`.`user_salt` AS `user_salt`, `tusuarios`.`user_password` AS `user_password`, `tusuarios`.`user_token` AS `user_token`, `tusuarios`.`user_tokenexp` AS `user_tokenexp`, `tusuarios`.`user_activo` AS `user_activo`, `tusuarios`.`user_tipo` AS `user_tipo`, `tusuarios`.`user_perfil` AS `user_perfil`, `tusuarios`.`user_admin` AS `user_admin`, `tusuarios`.`user_idempresa` AS `user_idempresa`, `tusuarios`.`user_cedula` AS `user_cedula`, `tusuarios`.`user_lastlogin` AS `user_lastlogin`, `tusuarios`.`user_crea` AS `user_crea`, `tusuarios`.`fec_crea` AS `fec_crea`, `tusuarios`.`user_mod` AS `user_mod`, `tusuarios`.`fec_mod` AS `fec_mod` FROM `tusuarios` ;

-- Volcando estructura para vista wms.v_picking_prod_anclados
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_picking_prod_anclados`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_picking_prod_anclados` AS SELECT `a`.`picd_idproducto` AS `picd_idproducto`, sum(`a`.`picd_cantidad`) AS `anclado` FROM `tpicking_detalle` AS `a` WHERE exists(select `z`.`pick_idpicking` from `tpicking` `z` where `z`.`pick_status` in (1,2,3) AND `z`.`pick_idempresa` = `a`.`picd_idempresa` AND `z`.`pick_idpicking` = `a`.`picd_idpicking` limit 1) GROUP BY `a`.`picd_idproducto` ;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
