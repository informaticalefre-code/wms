CONNECTION='Driver=ODBC Driver 17 for SQL Server;server=server03; Database=LEFRE_DV;uid=picking;pwd=p1ck1ng; Scrollable=SQLSRV_CURSOR_CLIENT_BUFFERED;MultipleActiveResultSets=1';

/* Este query es para determinar la falla cuando salga el siguiente error:
   Illegal mix of collations
*/
SELECT table_schema, table_name, column_name, character_set_name, collation_name
FROM information_schema.columns
WHERE collation_name = 'latin1_general_ci'
ORDER BY table_schema, table_name,ordinal_position; 


CREATE TABLE  TbPedidos1 (
  `numero_ped` varchar(15) NOT NULL,
  `fecha_ped` datetime DEFAULT NULL,
  `cliente_ped` varchar(10) DEFAULT NULL,
  `vendedor_ped` varchar(5) DEFAULT NULL,
  `status_ped` varchar(10) DEFAULT NULL,
  `monto_ped` decimal(18,3) DEFAULT NULL,
  `total_ped` decimal(18,3) DEFAULT NULL,
  `descto_ped` decimal(18,3) DEFAULT NULL,
  `montodto_ped` decimal(18,3) DEFAULT NULL,
  `observacion_ped` varchar(250) DEFAULT NULL,
  `creacion_ped` datetime DEFAULT NULL
)
ENGINE=CONNECT
DEFAULT CHARSET=latin1
TABLE_TYPE='ODBC'
CONNECTION='Driver=SQL Server Native Client 11.0;server=server02; Database=LEFRE_DV_DEV;uid=picking;pwd=p1ck1ng; Scrollable=SQLSRV_CURSOR_CLIENT_BUFFERED;MultipleActiveResultSets=1';


CREATE TABLE  TbPedidos2 (
  `numero_ped` varchar(15) NOT NULL,
  `producto_ped` varchar(20) DEFAULT NULL,
  `cantidad_ped` decimal(18,3) DEFAULT NULL,
  `descripcion_ped` varchar(50) DEFAULT NULL
)
ENGINE=CONNECT
DEFAULT CHARSET=latin1
TABLE_TYPE='ODBC'
CONNECTION='Driver=SQL Server Native Client 11.0;server=server02; Database=LEFRE_DV_DEV;uid=picking;pwd=p1ck1ng; Scrollable=SQLSRV_CURSOR_CLIENT_BUFFERED;MultipleActiveResultSets=1';


CREATE TABLE TbVendedores (
  `codigo_ven` varchar(10) DEFAULT NULL,
  `nombre_ven` varchar(50) DEFAULT NULL,
  `inactivo_ven` int(1) DEFAULT NULL
)
ENGINE=CONNECT
DEFAULT CHARSET=latin1
TABLE_TYPE='ODBC'
CONNECTION='Driver=SQL Server Native Client 11.0;server=server02; Database=LEFRE_DV_DEV;uid=picking;pwd=p1ck1ng; Scrollable=SQLSRV_CURSOR_CLIENT_BUFFERED;MultipleActiveResultSets=1';



CREATE TABLE TbMarcas (
  `CodMarca` varchar(10),
  `DesMarca` varchar(150)
)
ENGINE=CONNECT
DEFAULT CHARSET=latin1
TABLE_TYPE='ODBC'
CONNECTION='Driver=SQL Server Native Client 11.0;server=server02; Database=LEFRE_DV_DEV;uid=picking;pwd=p1ck1ng; Scrollable=SQLSRV_CURSOR_CLIENT_BUFFERED;MultipleActiveResultSets=1';


CREATE TABLE TbClientes (
  `codigo_cli` varchar(10) DEFAULT NULL,
  `nombre_cli` varchar(50) DEFAULT NULL,
  `descripcion_cli` varchar(150) DEFAULT NULL,
  `inactivo_cli` int(1) DEFAULT NULL,
  `vendedor_cli` varchar(5) DEFAULT NULL,
  `direccion_cli` varchar(150) DEFAULT NULL,
  `email_cli` varchar(100) DEFAULT NULL,
  `ciudad_cli` varchar(15) DEFAULT NULL,
  `estado_cli` varchar(15) DEFAULT NULL,
  `zona_cli` varchar(12) DEFAULT NULL
)
ENGINE=CONNECT
DEFAULT CHARSET=LATIN1
TABLE_TYPE='ODBC'
CONNECTION='Driver=SQL Server Native Client 11.0;server=server02; Database=LEFRE_DV_DEV;uid=picking;pwd=p1ck1ng; Scrollable=SQLSRV_CURSOR_CLIENT_BUFFERED;MultipleActiveResultSets=1';


CREATE TABLE  TbProductos (
	codigo_pro    varchar (20) NOT NULL,
	nombre_pro    varchar (50) NULL,
	unidad_pro    varchar (7) NULL,
	grupo_pro    varchar (10) NULL,
	tipo_pro    varchar (10) NULL,
	inactivo_pro   int(1)  NULL,
	existencia_pro   numeric (18, 2) NULL,
	ubicacion_pro    varchar (20) NULL,
	peso_pro   numeric (18, 2) NULL,
	codigobarra_pro    varchar (20) NULL,
	referencia_pro varchar (100) NULL,
	MARCA_PRO       varchar (10) NULL,
    BultoOriginal_Pro int(1) NULL, 
    EmpaqueOriginal_Pro int(1) NULL)
ENGINE=CONNECT
DEFAULT CHARSET=latin1
TABLE_TYPE='ODBC'
CONNECTION='Driver=SQL Server Native Client 11.0;server=server02; Database=LEFRE_DV_DEV;uid=picking;pwd=p1ck1ng; Scrollable=SQLSRV_CURSOR_CLIENT_BUFFERED;MultipleActiveResultSets=1';



