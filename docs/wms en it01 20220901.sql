-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.6.7-MariaDB-log - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             11.2.0.6213
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

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
		WHERE a.pick_idpedido = idpedido COLLATE utf8mb4_general_ci
		UNION 
		SELECT a.picd_idproducto, NULL, a.picd_requerido, a.picd_requerido
		FROM vpicking_tarea a WHERE a.pick_idpedido = idpedido COLLATE utf8mb4_general_ci
		AND NOT EXISTS (SELECT z.numero_ped FROM tbpedidos2 z WHERE z.numero_ped = a.pick_idpedido AND z.producto_ped = a.picd_idproducto)
		UNION 
		SELECT a.producto_ped, a.cantidad_ped, NULL, a.cantidad_ped
		FROM tbpedidos2 a WHERE a.numero_ped =idpedido COLLATE utf8mb4_general_ci
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
/*!40000 ALTER TABLE `tbackorder` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbackorder` ENABLE KEYS */;

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
/*!40000 ALTER TABLE `tempresas` DISABLE KEYS */;
INSERT INTO `tempresas` (`empr_idempresa`, `empr_nombre`, `empr_alias`, `empr_uuid`, `empr_logo`, `empr_logosimple`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 'Inversiones Lefre C.A.', 'Lefre', '85b466cbfb4911eaa49b4c72b92166c4', 'lefre_logo01.jpg', 'lefre_logo02.jpg', 'root', '2020-02-01 00:00:00', '', '2020-11-23 23:07:27'),
	(2, 'Inversiones S&M', 'S&M', '', 'lefre_logo01.jpg', 'lefre_logo02.jpg', 'root', '2020-02-13 22:37:59', NULL, '2020-05-23 20:35:17');
/*!40000 ALTER TABLE `tempresas` ENABLE KEYS */;

-- Volcando estructura para tabla wms.tmenu_opciones
CREATE TABLE IF NOT EXISTS `tmenu_opciones` (
  `menu_id` tinyint(1) NOT NULL,
  `menu_nombre` varchar(50) NOT NULL DEFAULT '',
  `menu_order` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabla de Menu de Opciones';

-- Volcando datos para la tabla wms.tmenu_opciones: ~6 rows (aproximadamente)
/*!40000 ALTER TABLE `tmenu_opciones` DISABLE KEYS */;
INSERT INTO `tmenu_opciones` (`menu_id`, `menu_nombre`, `menu_order`) VALUES
	(1, 'PEDIDOS', 1),
	(2, 'PICKING', 2),
	(3, 'PACKING', 3),
	(4, 'ALMACEN', 4),
	(5, 'SUPERVISOR', 5),
	(6, 'USUARIO', 6);
/*!40000 ALTER TABLE `tmenu_opciones` ENABLE KEYS */;

-- Volcando estructura para tabla wms.tmenu_roles
CREATE TABLE IF NOT EXISTS `tmenu_roles` (
  `menu_id` tinyint(4) NOT NULL,
  `menu_role` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Indica que Roles tienen acceso a cada opción del menú principal. Solo aplica a opciones principales o al nombre de los grupos de opciones';

-- Volcando datos para la tabla wms.tmenu_roles: ~20 rows (aproximadamente)
/*!40000 ALTER TABLE `tmenu_roles` DISABLE KEYS */;
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
	(6, 'ADMIN');
/*!40000 ALTER TABLE `tmenu_roles` ENABLE KEYS */;

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
  `pack_prioridad` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Normal, 1=Urgente',
  `pack_fecierre` datetime DEFAULT NULL COMMENT 'Fecha de cierre de la tarea.',
  `pack_pista` tinyint(1) DEFAULT 0 COMMENT 'Indica la pista dónde se coloca el pedido una vez consolidado, para pasar al picking.',
  `pack_observacion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_crea` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fec_crea` datetime NOT NULL DEFAULT current_timestamp(),
  `user_mod` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL COMMENT 'Fecha ultima modificación o cierre de tarea.',
  PRIMARY KEY (`pack_idempresa`,`pack_idpacking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.tpacking: ~3 rows (aproximadamente)
/*!40000 ALTER TABLE `tpacking` DISABLE KEYS */;
INSERT INTO `tpacking` (`pack_idempresa`, `pack_idpacking`, `pack_fecha`, `pack_idpedido`, `pack_idpicking`, `pack_fecinicio`, `pack_embalador`, `pack_status`, `pack_prioridad`, `pack_fecierre`, `pack_pista`, `pack_observacion`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 1, '2022-07-13 11:26:47', 'M51003161', 1, '2022-07-13 11:26:55', 'usuario1', 1, 0, NULL, 0, NULL, 'usuario1', '2022-07-13 11:26:47', 'usuario1', '2022-07-13 11:26:55'),
	(1, 2, '2022-07-13 15:47:17', 'M17001885', 8, '2022-07-13 15:48:17', 'jcfreites', 5, 0, '2022-07-13 16:17:44', 0, '', 'jcfreites', '2022-07-13 15:47:17', 'jcfreites', '2022-07-13 16:17:44'),
	(1, 3, '2022-07-14 15:13:27', 'M11500181', 10, '2022-07-14 15:13:34', 'jcfreites', 1, 0, NULL, 0, NULL, 'jcfreites', '2022-07-14 15:13:27', 'jcfreites', '2022-07-14 15:13:34');
/*!40000 ALTER TABLE `tpacking` ENABLE KEYS */;

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

-- Volcando datos para la tabla wms.tpacking_bultos: ~8 rows (aproximadamente)
/*!40000 ALTER TABLE `tpacking_bultos` DISABLE KEYS */;
INSERT INTO `tpacking_bultos` (`pack_idempresa`, `pack_idpacking`, `pack_idbulto`, `pack_peso`, `pack_unidadpeso`, `pack_status`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 1, 1, 0.00, 'Kg', b'1', 'usuario1', '2022-07-13 11:26:56', 'usuario1', '2022-07-13 14:20:49'),
	(1, 2, 1, 0.00, 'Kg', b'1', 'jcfreites', '2022-07-13 15:48:18', 'jcfreites', '2022-07-13 16:01:17'),
	(1, 2, 2, 20.00, 'Kg', b'1', 'jcfreites', '2022-07-13 16:07:44', 'jcfreites', '2022-07-13 16:13:29'),
	(1, 2, 3, 19.00, 'Kg', b'1', 'jcfreites', '2022-07-13 16:13:44', 'jcfreites', '2022-07-13 16:14:26'),
	(1, 3, 1, 0.00, 'Kg', b'1', 'jcfreites', '2022-07-14 15:13:35', 'jcfreites', '2022-07-22 15:23:14'),
	(1, 3, 2, 0.00, 'Kg', b'1', 'jcfreites', '2022-07-26 11:36:18', 'jcfreites', '2022-07-26 11:38:24'),
	(1, 3, 3, 0.00, 'Kg', b'1', 'jcfreites', '2022-07-26 11:38:28', 'jcfreites', '2022-07-26 11:38:43'),
	(1, 3, 4, 0.00, 'Kg', b'1', 'jcfreites', '2022-07-26 11:38:51', 'jcfreites', '2022-07-26 11:39:05');
/*!40000 ALTER TABLE `tpacking_bultos` ENABLE KEYS */;

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

-- Volcando datos para la tabla wms.tpacking_detalle: ~19 rows (aproximadamente)
/*!40000 ALTER TABLE `tpacking_detalle` DISABLE KEYS */;
INSERT INTO `tpacking_detalle` (`pacd_idempresa`, `pacd_idpacking`, `pacd_idproducto`, `pacd_unidad`, `pacd_requerido`, `pacd_cantidad`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 1, '5040-0100', 'CJA', 4, 4, 'usuario1', '2022-07-13 11:26:47', 'usuario1', '2022-07-13 14:20:45'),
	(1, 1, '5040-0112', 'CJA', 4, NULL, 'usuario1', '2022-07-13 11:26:47', NULL, NULL),
	(1, 1, '5040-0126', 'CJA', 4, NULL, 'usuario1', '2022-07-13 11:26:47', NULL, NULL),
	(1, 2, '0020-0201', 'KIT', 3, 3, 'jcfreites', '2022-07-13 15:47:17', 'jcfreites', '2022-07-13 16:00:26'),
	(1, 2, '0040-0102', 'PZA', 12, 12, 'jcfreites', '2022-07-13 15:47:17', 'jcfreites', '2022-07-13 16:11:28'),
	(1, 2, '6300-0020', 'PQT', 12, 12, 'jcfreites', '2022-07-13 15:47:17', 'jcfreites', '2022-07-13 16:11:39'),
	(1, 2, '7060-0212', 'PZA', 30, 30, 'jcfreites', '2022-07-13 15:47:17', 'jcfreites', '2022-07-13 16:13:50'),
	(1, 3, '2391-2141', '1/4', 4, 4, 'jcfreites', '2022-07-14 15:13:27', 'jcfreites', '2022-07-14 15:13:40'),
	(1, 3, '2391-6000', '1/4', 4, 4, 'jcfreites', '2022-07-14 15:13:27', 'jcfreites', '2022-07-14 15:13:53'),
	(1, 3, '2391-6004', '1/4', 3, 3, 'jcfreites', '2022-07-14 15:13:27', 'jcfreites', '2022-07-14 15:13:49'),
	(1, 3, '2391-6008', 'GLN', 2, 2, 'jcfreites', '2022-07-14 15:13:27', 'jcfreites', '2022-07-26 11:36:26'),
	(1, 3, '2391-6009', '1/4', 4, 4, 'jcfreites', '2022-07-14 15:13:27', 'jcfreites', '2022-07-26 11:36:31'),
	(1, 3, '2391-6010', '1/4 ', 2, 2, 'jcfreites', '2022-07-14 15:13:27', 'jcfreites', '2022-07-26 11:36:36'),
	(1, 3, '2391-6011', 'GLN', 2, 2, 'jcfreites', '2022-07-14 15:13:27', 'jcfreites', '2022-07-26 11:38:32'),
	(1, 3, '2391-6052', '1/4 ', 4, 4, 'jcfreites', '2022-07-14 15:13:27', 'jcfreites', '2022-07-26 11:38:35'),
	(1, 3, '2391-6054', '1/4 ', 2, 2, 'jcfreites', '2022-07-14 15:13:27', 'jcfreites', '2022-07-26 11:38:39'),
	(1, 3, '2391-6081', '1/4 ', 4, 4, 'jcfreites', '2022-07-14 15:13:27', 'jcfreites', '2022-07-26 11:38:54'),
	(1, 3, '2391-6083', '1/4', 2, 2, 'jcfreites', '2022-07-14 15:13:27', 'jcfreites', '2022-07-26 11:38:58'),
	(1, 3, '2391-6084', 'GLN', 2, 2, 'jcfreites', '2022-07-14 15:13:27', 'jcfreites', '2022-07-26 11:39:01');
/*!40000 ALTER TABLE `tpacking_detalle` ENABLE KEYS */;

-- Volcando estructura para tabla wms.tpacking_productos
CREATE TABLE IF NOT EXISTS `tpacking_productos` (
  `pacp_idempresa` tinyint(1) NOT NULL,
  `pacp_idpacking` int(11) NOT NULL,
  `pacp_idbulto` tinyint(2) NOT NULL,
  `pacp_idproducto` varchar(20) NOT NULL,
  `pacp_cantidad` smallint(1) NOT NULL,
  `user_crea` varchar(20) NOT NULL,
  `fec_crea` datetime NOT NULL,
  `user_mod` varchar(20) DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL,
  PRIMARY KEY (`pacp_idempresa`,`pacp_idpacking`,`pacp_idbulto`,`pacp_idproducto`),
  CONSTRAINT `tpacking_productos` FOREIGN KEY (`pacp_idempresa`, `pacp_idpacking`, `pacp_idbulto`) REFERENCES `tpacking_bultos` (`pack_idempresa`, `pack_idpacking`, `pack_idbulto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Volcando datos para la tabla wms.tpacking_productos: ~18 rows (aproximadamente)
/*!40000 ALTER TABLE `tpacking_productos` DISABLE KEYS */;
INSERT INTO `tpacking_productos` (`pacp_idempresa`, `pacp_idpacking`, `pacp_idbulto`, `pacp_idproducto`, `pacp_cantidad`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 1, 1, '5040-0100', 4, 'usuario1', '2022-07-13 14:20:45', NULL, NULL),
	(1, 2, 1, '0020-0201', 3, 'jcfreites', '2022-07-13 16:00:26', NULL, NULL),
	(1, 2, 1, '0040-0102', 6, 'jcfreites', '2022-07-13 16:01:01', NULL, NULL),
	(1, 2, 2, '0040-0102', 6, 'jcfreites', '2022-07-13 16:11:28', NULL, NULL),
	(1, 2, 2, '6300-0020', 12, 'jcfreites', '2022-07-13 16:11:39', NULL, NULL),
	(1, 2, 3, '7060-0212', 30, 'jcfreites', '2022-07-13 16:13:50', NULL, NULL),
	(1, 3, 1, '2391-2141', 4, 'jcfreites', '2022-07-14 15:13:40', NULL, NULL),
	(1, 3, 1, '2391-6000', 4, 'jcfreites', '2022-07-14 15:13:53', NULL, NULL),
	(1, 3, 1, '2391-6004', 3, 'jcfreites', '2022-07-14 15:13:49', NULL, NULL),
	(1, 3, 2, '2391-6008', 2, 'jcfreites', '2022-07-26 11:36:26', NULL, NULL),
	(1, 3, 2, '2391-6009', 4, 'jcfreites', '2022-07-26 11:36:31', NULL, NULL),
	(1, 3, 2, '2391-6010', 2, 'jcfreites', '2022-07-26 11:36:36', NULL, NULL),
	(1, 3, 3, '2391-6011', 2, 'jcfreites', '2022-07-26 11:38:32', NULL, NULL),
	(1, 3, 3, '2391-6052', 4, 'jcfreites', '2022-07-26 11:38:35', NULL, NULL),
	(1, 3, 3, '2391-6054', 2, 'jcfreites', '2022-07-26 11:38:39', NULL, NULL),
	(1, 3, 4, '2391-6081', 4, 'jcfreites', '2022-07-26 11:38:54', NULL, NULL),
	(1, 3, 4, '2391-6083', 2, 'jcfreites', '2022-07-26 11:38:58', NULL, NULL),
	(1, 3, 4, '2391-6084', 2, 'jcfreites', '2022-07-26 11:39:01', NULL, NULL);
/*!40000 ALTER TABLE `tpacking_productos` ENABLE KEYS */;

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
/*!40000 ALTER TABLE `tpedidos_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `tpedidos_status` ENABLE KEYS */;

-- Volcando estructura para tabla wms.tpicking
CREATE TABLE IF NOT EXISTS `tpicking` (
  `pick_idempresa` tinyint(4) NOT NULL,
  `pick_idpicking` int(11) NOT NULL,
  `pick_fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `pick_idpedido` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pick_preparador` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Es el username del preparador asignado.',
  `pick_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=Anulado, 1=En Proceso, 2=Pausado, 3=Consolidado,\r\n5=Cerrada',
  `pick_fecierre` datetime DEFAULT NULL COMMENT 'Fecha en la que el preparador termina de anclar todos los productos a la tarea de picking.',
  `pick_pista` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Indica la pista dónde se coloca el pedido una vez consolidado, para pasar al picking.',
  `pick_observacion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pick_userverif` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario verificador',
  `pick_fecverif` datetime DEFAULT NULL COMMENT 'Fecha en que fue verificada y cerrada la tarea de picking.',
  `user_crea` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fec_crea` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de inicio de la tarea.',
  `user_mod` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL,
  PRIMARY KEY (`pick_idempresa`,`pick_idpicking`),
  UNIQUE KEY `tpicking_idx01` (`pick_idpedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.tpicking: ~10 rows (aproximadamente)
/*!40000 ALTER TABLE `tpicking` DISABLE KEYS */;
INSERT INTO `tpicking` (`pick_idempresa`, `pick_idpicking`, `pick_fecha`, `pick_idpedido`, `pick_preparador`, `pick_status`, `pick_fecierre`, `pick_pista`, `pick_observacion`, `pick_userverif`, `pick_fecverif`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 1, '2022-07-13 11:24:39', 'M51003161', 'usuario1', 5, '2022-07-13 11:26:15', 7, '', 'usuario1', '2022-07-13 11:26:47', 'usuario1', '2022-07-13 11:24:39', 'usuario1', '2022-07-13 11:26:47'),
	(1, 2, '2022-07-13 11:24:51', 'M16002600', 'usuario1', 1, NULL, 0, NULL, NULL, NULL, 'usuario1', '2022-07-13 11:24:51', NULL, NULL),
	(1, 3, '2022-07-13 11:25:40', 'M17001887', 'usuario1', 1, NULL, 0, NULL, NULL, NULL, 'usuario1', '2022-07-13 11:25:40', NULL, NULL),
	(1, 4, '2022-07-13 11:28:35', 'M64000636', 'usuario1', 1, NULL, 0, NULL, NULL, NULL, 'usuario1', '2022-07-13 11:28:35', NULL, NULL),
	(1, 5, '2022-07-13 11:30:54', 'M52000649', 'jcfreites', 3, '2022-07-13 15:43:42', 1, '', NULL, NULL, 'usuario1', '2022-07-13 11:30:54', 'jcfreites', '2022-07-13 15:43:42'),
	(1, 6, '2022-07-13 15:13:02', 'M20101113', 'usuario1', 1, NULL, 0, NULL, NULL, NULL, 'jcfreites', '2022-07-13 15:13:02', NULL, NULL),
	(1, 7, '2022-07-13 15:13:20', 'M68000305', 'jcfreites', 1, NULL, 0, NULL, NULL, NULL, 'jcfreites', '2022-07-13 15:13:20', NULL, NULL),
	(1, 8, '2022-07-13 15:13:56', 'M17001885', 'jcfreites', 5, '2022-07-13 15:29:49', 1, '', 'jcfreites', '2022-07-13 15:47:17', 'jcfreites', '2022-07-13 15:13:56', 'jcfreites', '2022-07-13 15:47:17'),
	(1, 9, '2022-07-13 15:16:17', 'M57001682', 'jcfreites', 1, NULL, 0, NULL, NULL, NULL, 'jcfreites', '2022-07-13 15:16:17', NULL, NULL),
	(1, 10, '2022-07-14 15:10:17', 'M11500181', 'jcfreites', 5, '2022-07-14 15:11:55', 3, '', 'jcfreites', '2022-07-14 15:13:27', 'jcfreites', '2022-07-14 15:10:17', 'jcfreites', '2022-07-14 15:13:27');
/*!40000 ALTER TABLE `tpicking` ENABLE KEYS */;

-- Volcando estructura para tabla wms.tpicking_bins
CREATE TABLE IF NOT EXISTS `tpicking_bins` (
  `picc_idempresa` tinyint(4) NOT NULL,
  `picc_idpicking` int(11) NOT NULL,
  `picc_bin` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Bandeja o palet dónde sera ubicado el pedido consolidado para pasar al packing.',
  `user_crea` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fec_crea` datetime NOT NULL,
  `user_mod` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fec_mod` datetime DEFAULT NULL,
  PRIMARY KEY (`picc_idempresa`,`picc_idpicking`,`picc_bin`),
  CONSTRAINT `tpicking_bins` FOREIGN KEY (`picc_idempresa`, `picc_idpicking`) REFERENCES `tpicking` (`pick_idempresa`, `pick_idpicking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Indica los palets,cajones,recipientes donde se consolidan tareas de picking';

-- Volcando datos para la tabla wms.tpicking_bins: ~3 rows (aproximadamente)
/*!40000 ALTER TABLE `tpicking_bins` DISABLE KEYS */;
INSERT INTO `tpicking_bins` (`picc_idempresa`, `picc_idpicking`, `picc_bin`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 1, 'PALET-7', 'usuario1', '2022-07-13 11:26:15', NULL, NULL),
	(1, 5, 'PALET-1', 'jcfreites', '2022-07-13 15:43:42', NULL, NULL),
	(1, 8, 'PALET-1', 'jcfreites', '2022-07-13 15:29:49', NULL, NULL),
	(1, 10, 'PALET-3', 'jcfreites', '2022-07-14 15:11:55', NULL, NULL);
/*!40000 ALTER TABLE `tpicking_bins` ENABLE KEYS */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.tpicking_detalle: ~38 rows (aproximadamente)
/*!40000 ALTER TABLE `tpicking_detalle` DISABLE KEYS */;
INSERT INTO `tpicking_detalle` (`picd_idempresa`, `picd_idpicking`, `picd_idproducto`, `picd_unidad`, `picd_idalmacen`, `picd_ubicacion`, `picd_requerido`, `picd_cantidad`, `picd_cantverif`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 1, '5040-0100', 'CJA', '1', '04-08-B', 4, 4, 4, 'usuario1', '2022-07-13 11:24:39', 'usuario1', '2022-07-13 11:26:28'),
	(1, 1, '5040-0112', 'CJA', '1', '04-08-B', 4, 4, 4, 'usuario1', '2022-07-13 11:24:39', 'usuario1', '2022-07-13 11:26:32'),
	(1, 1, '5040-0126', 'CJA', '1', '04-08-C', 4, 4, 4, 'usuario1', '2022-07-13 11:24:39', 'usuario1', '2022-07-13 11:26:36'),
	(1, 2, '1400-0021', 'PZA', '1', '5-12-B', 2, 2, NULL, 'usuario1', '2022-07-13 11:24:51', 'usuario1', '2022-07-13 11:29:06'),
	(1, 2, '5019-0035', 'CJA', '1', '1-3-B', 1, 1, NULL, 'usuario1', '2022-07-13 11:24:51', 'usuario1', '2022-07-13 11:29:10'),
	(1, 2, '9110-0010', 'CJA', '1', 'MZZ-A', 1, 1, NULL, 'usuario1', '2022-07-13 11:24:51', 'usuario1', '2022-07-13 11:29:13'),
	(1, 3, '5040-0100', 'CJA', '1', '04-08-B', 3, 3, NULL, 'usuario1', '2022-07-13 11:25:40', 'usuario1', '2022-07-13 11:29:41'),
	(1, 3, '5040-0102', 'CJA', '1', '04-08-B', 3, 3, NULL, 'usuario1', '2022-07-13 11:25:40', 'usuario1', '2022-07-13 11:29:45'),
	(1, 4, '9041-0213', 'PZA', '1', 'REC-03-A', 20, 20, NULL, 'usuario1', '2022-07-13 11:28:35', 'usuario1', '2022-07-13 11:28:50'),
	(1, 5, '2391-6054', '1/4 ', '1', '6-5-B', 11, 11, NULL, 'usuario1', '2022-07-13 11:30:54', 'jcfreites', '2022-07-13 15:43:15'),
	(1, 6, '2002-0122', 'PQT', '1', '04-08-B', 1, NULL, NULL, 'jcfreites', '2022-07-13 15:13:02', NULL, NULL),
	(1, 6, '2010-0462', 'PZA', '1', '04-09-C', 1, NULL, NULL, 'jcfreites', '2022-07-13 15:13:02', NULL, NULL),
	(1, 6, '2010-0463', 'PZA', '1', '04-09-C', 1, NULL, NULL, 'jcfreites', '2022-07-13 15:13:02', NULL, NULL),
	(1, 6, '2010-0469', 'PZA', '1', '04-09-C', 1, NULL, NULL, 'jcfreites', '2022-07-13 15:13:02', NULL, NULL),
	(1, 6, '2310-0367', 'PZA', '1', '04-11-C', 1, NULL, NULL, 'jcfreites', '2022-07-13 15:13:02', NULL, NULL),
	(1, 6, '2310-0386', 'PZA', '1', '04-11-C', 2, NULL, NULL, 'jcfreites', '2022-07-13 15:13:02', NULL, NULL),
	(1, 6, '2352-0122', 'PZA', '1', '04-11-A', 1, NULL, NULL, 'jcfreites', '2022-07-13 15:13:02', NULL, NULL),
	(1, 7, '2300-0021', 'PZA', '1', '01-05-C', 20, 20, NULL, 'jcfreites', '2022-07-13 15:13:20', 'jcfreites', '2022-07-13 15:24:55'),
	(1, 8, '0020-0201', 'KIT', '1', 'MZZ-B', 6, 3, 3, 'jcfreites', '2022-07-13 15:13:56', 'jcfreites', '2022-07-13 15:46:35'),
	(1, 8, '0040-0102', 'PZA', '1', '2-10-A', 25, 12, 12, 'jcfreites', '2022-07-13 15:13:56', 'jcfreites', '2022-07-13 15:45:30'),
	(1, 8, '6300-0020', 'PQT', '1', '1-1-A', 25, 12, 12, 'jcfreites', '2022-07-13 15:13:56', 'jcfreites', '2022-07-13 15:45:34'),
	(1, 8, '7060-0212', 'PZA', '1', '03-06-B', 30, 30, 30, 'jcfreites', '2022-07-13 15:13:56', 'jcfreites', '2022-07-13 15:45:40'),
	(1, 9, '7121-0091', 'PZA', '1', '03-01-B', 10, NULL, NULL, 'jcfreites', '2022-07-13 15:16:17', NULL, NULL),
	(1, 9, '7121-0092A', 'PZA', '1', '03-01-B', 10, NULL, NULL, 'jcfreites', '2022-07-13 15:16:17', NULL, NULL),
	(1, 9, '7121-0094', 'PZA', '1', '03-01-C', 10, NULL, NULL, 'jcfreites', '2022-07-13 15:16:17', NULL, NULL),
	(1, 9, '7121-0095', 'PZA', '1', '03-01-B', 10, NULL, NULL, 'jcfreites', '2022-07-13 15:16:17', NULL, NULL),
	(1, 9, '7121-0096', 'PZA', '1', '03-01-C', 10, NULL, NULL, 'jcfreites', '2022-07-13 15:16:17', NULL, NULL),
	(1, 9, '7121-0097', 'PZA', '1', '03-01-C', 2, NULL, NULL, 'jcfreites', '2022-07-13 15:16:17', NULL, NULL),
	(1, 9, '7121-0098', 'PZA', '1', '03-01-C', 4, NULL, NULL, 'jcfreites', '2022-07-13 15:16:17', NULL, NULL),
	(1, 9, '7121-0201', 'PZA', '1', '03-04-D', 3, NULL, NULL, 'jcfreites', '2022-07-13 15:16:17', NULL, NULL),
	(1, 10, '2391-2141', '1/4', '1', '6-2-B', 4, 4, 4, 'jcfreites', '2022-07-14 15:10:17', 'jcfreites', '2022-07-14 15:12:05'),
	(1, 10, '2391-6000', '1/4', '1', '6-6-B', 4, 4, 4, 'jcfreites', '2022-07-14 15:10:17', 'jcfreites', '2022-07-14 15:12:09'),
	(1, 10, '2391-6004', '1/4', '1', '6-5-B', 3, 3, 3, 'jcfreites', '2022-07-14 15:10:17', 'jcfreites', '2022-07-14 15:12:13'),
	(1, 10, '2391-6008', 'GLN', '1', '6-6-A', 2, 2, 2, 'jcfreites', '2022-07-14 15:10:17', 'jcfreites', '2022-07-14 15:12:17'),
	(1, 10, '2391-6009', '1/4', '1', '6-6-B', 4, 4, 4, 'jcfreites', '2022-07-14 15:10:17', 'jcfreites', '2022-07-14 15:12:22'),
	(1, 10, '2391-6010', '1/4 ', '1', '6-6-B', 2, 2, 2, 'jcfreites', '2022-07-14 15:10:17', 'jcfreites', '2022-07-14 15:12:33'),
	(1, 10, '2391-6011', 'GLN', '1', '6-6-A', 2, 2, 2, 'jcfreites', '2022-07-14 15:10:17', 'jcfreites', '2022-07-14 15:12:29'),
	(1, 10, '2391-6052', '1/4 ', '1', '6-4-B', 4, 4, 4, 'jcfreites', '2022-07-14 15:10:17', 'jcfreites', '2022-07-14 15:12:37'),
	(1, 10, '2391-6054', '1/4 ', '1', '6-5-B', 2, 2, 2, 'jcfreites', '2022-07-14 15:10:17', 'jcfreites', '2022-07-14 15:12:42'),
	(1, 10, '2391-6081', '1/4 ', '1', '6-5-B', 4, 4, 4, 'jcfreites', '2022-07-14 15:10:17', 'jcfreites', '2022-07-14 15:12:47'),
	(1, 10, '2391-6083', '1/4', '1', '6-4-B', 4, 2, 2, 'jcfreites', '2022-07-14 15:10:17', 'jcfreites', '2022-07-14 15:13:10'),
	(1, 10, '2391-6084', 'GLN', '1', '6-3-A', 2, 2, 2, 'jcfreites', '2022-07-14 15:10:17', 'jcfreites', '2022-07-14 15:13:14');
/*!40000 ALTER TABLE `tpicking_detalle` ENABLE KEYS */;

-- Volcando estructura para tabla wms.tpicking_pistas
CREATE TABLE IF NOT EXISTS `tpicking_pistas` (
  `picp_idvendedor` varchar(10) CHARACTER SET latin1 NOT NULL COMMENT 'Codigo del Vendedor. Viene del Xenx.',
  `picp_pista` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.tpicking_pistas: ~83 rows (aproximadamente)
/*!40000 ALTER TABLE `tpicking_pistas` DISABLE KEYS */;
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
/*!40000 ALTER TABLE `tpicking_pistas` ENABLE KEYS */;

-- Volcando estructura para tabla wms.tprinter_roles
CREATE TABLE IF NOT EXISTS `tprinter_roles` (
  `device_name` varchar(50) DEFAULT NULL,
  `device_uid` varchar(100) DEFAULT NULL,
  `device_descripcion` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Contiene información de las impresoras en la cuáles se imprimen tickets y su nombre en la red a través del zebra printer browser';

-- Volcando datos para la tabla wms.tprinter_roles: ~2 rows (aproximadamente)
/*!40000 ALTER TABLE `tprinter_roles` DISABLE KEYS */;
INSERT INTO `tprinter_roles` (`device_name`, `device_uid`, `device_descripcion`) VALUES
	('GC420t_01', '\\\\\\\\192.168.1.52\\\\ZDesigner GC420t (EPL) (Copiar 1)', 'Impresora compartida en 192.168.1.52 '),
	('GK420t_02', '\\\\\\\\192.168.1.100\\\\ZDesigner GK420t (EPL)', 'Impresora compartida en 192.168.1.100');
/*!40000 ALTER TABLE `tprinter_roles` ENABLE KEYS */;

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

-- Volcando datos para la tabla wms.tproductos_ubica: ~3 rows (aproximadamente)
/*!40000 ALTER TABLE `tproductos_ubica` DISABLE KEYS */;
INSERT INTO `tproductos_ubica` (`prou_idproducto`, `prou_almacen`, `prou_ubicacion`, `prou_cantidad`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	('0010-0010', '01', '123', 1, 'jcfreites', '2022-07-28 13:01:14', NULL, NULL),
	('0010-0010', '01', '1234', 2, 'jcfreites', '2022-07-28 13:01:14', NULL, NULL),
	('0010-0010', '01', '12345', 3, 'jcfreites', '2022-07-28 13:01:14', NULL, NULL);
/*!40000 ALTER TABLE `tproductos_ubica` ENABLE KEYS */;

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
/*!40000 ALTER TABLE `treserva_productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `treserva_productos` ENABLE KEYS */;

-- Volcando estructura para tabla wms.troles
CREATE TABLE IF NOT EXISTS `troles` (
  `rol_id` tinyint(1) NOT NULL,
  `rol_nombre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.troles: ~6 rows (aproximadamente)
/*!40000 ALTER TABLE `troles` DISABLE KEYS */;
INSERT INTO `troles` (`rol_id`, `rol_nombre`) VALUES
	(1, 'PREPARADOR'),
	(2, 'EMBALADOR'),
	(3, 'SUPERVISOR_ALMACEN'),
	(4, 'SOPORTE TECNICO'),
	(5, 'ESTADISTICO'),
	(6, 'TRANSPORTE');
/*!40000 ALTER TABLE `troles` ENABLE KEYS */;

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

-- Volcando datos para la tabla wms.tusuarios: ~3 rows (aproximadamente)
/*!40000 ALTER TABLE `tusuarios` DISABLE KEYS */;
INSERT INTO `tusuarios` (`user_id`, `user_uuid`, `user_name`, `user_nombre`, `user_apellido`, `user_email`, `user_salt`, `user_password`, `user_token`, `user_tokenexp`, `user_activo`, `user_tipo`, `user_perfil`, `user_admin`, `user_idempresa`, `user_cedula`, `user_lastlogin`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	(1, 'e03ecbf6-9277-11ec-9738-4c72b92166c4', 'jcfreites', 'Julio', 'Cesar', 'jcfreites@hotmail.com', NULL, '$argon2i$v=19$m=4096,t=10,p=20$c0Z1dTR6S3FEaUQzZGp3Uw$9rQa6ZOVsIY5Y04VuuMm9xnH6OOFj4hj8cNJiZ8yvlU', '291bf4a75f1966dc42e3fc367c4327ca', '2022-06-13 22:46:51', 1, '2', 'WEB', 0, 1, NULL, NULL, 'web anonimo', '2020-09-20 14:36:24', 'root@localhost', '2020-09-25 17:57:24'),
	(3, '3a7ce267-f037-11ec-91b9-4c72b92166c4', 'usuario2', 'embalador 1 prueba', 'embalador 1 prueba', 'embalador1@gmail.com', NULL, '$argon2i$v=19$m=4096,t=10,p=20$Zm56L3NreGNMcHdES3d1Vg$ngZV8nM7VK99GJOTYcVKui1+pmUAaV5ZFfAnhGM0YWU', 'cacf09357b7ab91ddb1efe62b44ff624', '2022-06-20 21:20:53', 1, '2', 'WEB', 0, 1, NULL, NULL, 'jcfreites', '2022-06-19 21:20:53', 'jcfreites', '2022-07-21 15:09:37'),
	(4, '6040de32-f037-11ec-91b9-4c72b92166c4', 'usuario3', 'embalador 2 prueba', 'embalador 2 prueba', 'embalador2@gmail.com', NULL, '$argon2i$v=19$m=4096,t=10,p=20$b3JQMUFOYWVmMjRDU0JxYw$C7UkD5tKsnsJFUKHJWgRfblavcHtVryFyq2DN4WriIo', '3fa337d36b43ec57ef7ece993a55b8ff', '2022-06-20 21:21:56', 1, '2', 'WEB', 0, 1, NULL, NULL, 'jcfreites', '2022-06-19 21:21:57', 'jcfreites', '2022-07-21 15:09:39'),
	(5, '738c35ca-092f-11ed-8678-a0d3c1262574', 'usuario1', 'Usuario Prueba', 'Pruebas', 'jcfreitesbacalao@gmail.com', NULL, '$argon2i$v=19$m=4096,t=10,p=20$QmdpcTQvaWpTOXFhMzVFNQ$SmaTKXv7tB9oz2CoG81oJmGBSCx3JmWekF0tV5nwdtc', 'fad11d2f472ea3d564cfde43adc87af5', '2022-07-22 15:58:12', 0, '2', 'WEB', 0, 1, NULL, NULL, 'jcfreites', '2022-07-21 15:58:12', NULL, NULL);
/*!40000 ALTER TABLE `tusuarios` ENABLE KEYS */;

-- Volcando estructura para tabla wms.tusuarios_roles
CREATE TABLE IF NOT EXISTS `tusuarios_roles` (
  `usrr_name` varchar(20) NOT NULL DEFAULT '',
  `usrr_role` varchar(15) NOT NULL,
  `user_crea` varchar(20) NOT NULL,
  `fec_crea` date NOT NULL,
  `user_mod` varchar(20) DEFAULT NULL,
  `fec_mod` date DEFAULT NULL,
  PRIMARY KEY (`usrr_name`,`usrr_role`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla wms.tusuarios_roles: ~4 rows (aproximadamente)
/*!40000 ALTER TABLE `tusuarios_roles` DISABLE KEYS */;
INSERT INTO `tusuarios_roles` (`usrr_name`, `usrr_role`, `user_crea`, `fec_crea`, `user_mod`, `fec_mod`) VALUES
	('jcfreites', 'ADMIN', 'jcfreites', '2022-06-19', 'jcfreites', '2022-07-26'),
	('usuario1', 'PREPARADOR', 'jcfreites', '2022-06-19', 'embalador1', '2022-06-24'),
	('usuario2', 'PREPARADOR', 'jcfreites', '2022-06-19', 'jcfreites', '2022-06-24'),
	('usuario3', 'PREPARADOR', 'jcfreites', '2022-06-19', 'jcfreites', '2022-06-24');
/*!40000 ALTER TABLE `tusuarios_roles` ENABLE KEYS */;

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

-- Volcando estructura para vista wms.vpicking
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vpicking` (
	`pick_idempresa` TINYINT(4) NOT NULL,
	`pick_idpicking` INT(11) NOT NULL,
	`pick_fecha` DATETIME NOT NULL,
	`pick_idpedido` VARCHAR(15) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`pick_preparador` VARCHAR(20) NOT NULL COMMENT 'Es el username del preparador asignado.' COLLATE 'utf8mb4_unicode_ci',
	`pick_status` TINYINT(1) NOT NULL COMMENT '0=Anulado, 1=En Proceso, 2=Pausado, 3=Consolidado,\r\n5=Cerrada',
	`pick_fecierre` DATETIME NULL COMMENT 'Fecha en la que el preparador termina de anclar todos los productos a la tarea de picking.',
	`pick_pista` TINYINT(1) NOT NULL COMMENT 'Indica la pista dónde se coloca el pedido una vez consolidado, para pasar al picking.',
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
CREATE TABLE `vstats_preparador2` 
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
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vpacking` AS SELECT `tpacking`.`pack_idempresa` AS `pack_idempresa`, `tpacking`.`pack_idpacking` AS `pack_idpacking`, `tpacking`.`pack_fecha` AS `pack_fecha`, `tpacking`.`pack_idpedido` AS `pack_idpedido`, `tpacking`.`pack_idpicking` AS `pack_idpicking`, `tpacking`.`pack_fecinicio` AS `pack_fecinicio`, `tpacking`.`pack_embalador` AS `pack_embalador`, `tpacking`.`pack_status` AS `pack_status`, `tpacking`.`pack_fecierre` AS `pack_fecierre`, `tpacking`.`pack_pista` AS `pack_pista`, `tpacking`.`pack_observacion` AS `pack_observacion`, `tpacking`.`user_crea` AS `user_crea`, `tpacking`.`fec_crea` AS `fec_crea`, `tpacking`.`user_mod` AS `user_mod`, `tpacking`.`fec_mod` AS `fec_mod` FROM `tpacking` ;

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

-- Volcando estructura para vista wms.vpicking
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vpicking`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vpicking` AS SELECT `tpicking`.`pick_idempresa` AS `pick_idempresa`, `tpicking`.`pick_idpicking` AS `pick_idpicking`, `tpicking`.`pick_fecha` AS `pick_fecha`, `tpicking`.`pick_idpedido` AS `pick_idpedido`, `tpicking`.`pick_preparador` AS `pick_preparador`, `tpicking`.`pick_status` AS `pick_status`, `tpicking`.`pick_fecierre` AS `pick_fecierre`, `tpicking`.`pick_pista` AS `pick_pista`, `tpicking`.`pick_observacion` AS `pick_observacion`, `tpicking`.`pick_userverif` AS `pick_userverif`, `tpicking`.`pick_fecverif` AS `pick_fecverif`, `tpicking`.`user_crea` AS `user_crea`, `tpicking`.`fec_crea` AS `fec_crea`, `tpicking`.`user_mod` AS `user_mod`, `tpicking`.`fec_mod` AS `fec_mod` FROM `tpicking` ;

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
WHERE a.user_activo = 1 AND EXISTS (SELECT z.usrr_rol FROM tusuarios_roles z WHERE z.usrr_rol = 'PREPARADOR' AND z.usrr_name = a.user_name) ;

-- Volcando estructura para vista wms.vusuarios
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vusuarios`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vusuarios` AS SELECT `tusuarios`.`user_id` AS `user_id`, `tusuarios`.`user_uuid` AS `user_uuid`, `tusuarios`.`user_name` AS `user_name`, `tusuarios`.`user_nombre` AS `user_nombre`, `tusuarios`.`user_apellido` AS `user_apellido`, `tusuarios`.`user_email` AS `user_email`, `tusuarios`.`user_salt` AS `user_salt`, `tusuarios`.`user_password` AS `user_password`, `tusuarios`.`user_token` AS `user_token`, `tusuarios`.`user_tokenexp` AS `user_tokenexp`, `tusuarios`.`user_activo` AS `user_activo`, `tusuarios`.`user_tipo` AS `user_tipo`, `tusuarios`.`user_perfil` AS `user_perfil`, `tusuarios`.`user_admin` AS `user_admin`, `tusuarios`.`user_idempresa` AS `user_idempresa`, `tusuarios`.`user_cedula` AS `user_cedula`, `tusuarios`.`user_lastlogin` AS `user_lastlogin`, `tusuarios`.`user_crea` AS `user_crea`, `tusuarios`.`fec_crea` AS `fec_crea`, `tusuarios`.`user_mod` AS `user_mod`, `tusuarios`.`fec_mod` AS `fec_mod` FROM `tusuarios` ;

-- Volcando estructura para vista wms.v_picking_prod_anclados
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_picking_prod_anclados`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_picking_prod_anclados` AS SELECT `a`.`picd_idproducto` AS `picd_idproducto`, sum(`a`.`picd_cantidad`) AS `anclado` FROM `tpicking_detalle` AS `a` WHERE exists(select `z`.`pick_idpicking` from `tpicking` `z` where `z`.`pick_status` in (1,2,3) AND `z`.`pick_idempresa` = `a`.`picd_idempresa` AND `z`.`pick_idpicking` = `a`.`picd_idpicking` limit 1) GROUP BY `a`.`picd_idproducto` ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
