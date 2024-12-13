USE [master]
GO
CREATE LOGIN [picking] WITH PASSWORD=N'p1ck1ng', DEFAULT_DATABASE=[LEFRE_DV_Dev], CHECK_EXPIRATION=OFF, CHECK_POLICY=OFF
GO

USE [LEFRE_DV_Dev]
GO
CREATE USER [picking_user] FOR LOGIN [picking]
GO

use [LEFRE_DV_Dev]
GO
GRANT SELECT ON [dbo].[TbPedidos1] TO [picking_user]
GRANT SELECT ON [dbo].[TbPedidos2] TO [picking_user]
GO
use [LEFRE_DV_Dev]
GO
GRANT UPDATE ON [dbo].[TbPedidos1] ([status_ped],[aprobador_ped],[aprofecha_ped]) TO [picking_user]
GRANT UPDATE ON [dbo].[TbPedidos2] ([aprobado_ped]) TO [picking_user]
GO
use [LEFRE_DV_Dev]
GO
GRANT SELECT ON [dbo].[TbMarcas] TO [picking_user]
GO
use [LEFRE_DV_Dev]
GO
GRANT SELECT ON [dbo].[TbPedidos2] TO [picking_user]
use [LEFRE_DV_Dev]
GO
GRANT SELECT ON [dbo].[TbFacturacion3] TO [picking_user]
GRANT SELECT ON [dbo].[TbFacturacion4] TO [picking_user]
GRANT SELECT ON [dbo].[TbCuentasCob] TO [picking_user]
GRANT SELECT ON [dbo].[TbClientes] TO [picking_user]
GRANT SELECT ON [dbo].[TbProductos] TO [picking_user]
DENY UPDATE ON [dbo].[TbProductos] ([dolar_pro]) TO [picking_user]
DENY UPDATE ON [dbo].[TbProductos] ([costo_pro]) TO [picking_user]
DENY UPDATE ON [dbo].[TbProductos] ([excento_pro]) TO [picking_user]
DENY UPDATE ON [dbo].[TbProductos] ([nombre_pro]) TO [picking_user]
DENY UPDATE ON [dbo].[TbProductos] ([codigo_pro]) TO [picking_user]
DENY UPDATE ON [dbo].[TbProductos] ([descripcion_pro]) TO [picking_user]
DENY UPDATE ON [dbo].[TbProductos] ([inactivo_pro]) TO [picking_user]
DENY UPDATE ON [dbo].[TbProductos] ([existencia_pro]) TO [picking_user]
DENY UPDATE ON [dbo].[TbProductos] ([EMPAQUE_PRO]) TO [picking_user]
DENY UPDATE ON [dbo].[TbProductos] ([precio1_Pro]) TO [picking_user]
DENY UPDATE ON [dbo].[TbProductos] ([precio2_Pro]) TO [picking_user]
DENY UPDATE ON [dbo].[TbProductos] ([precio3_Pro]) TO [picking_user]
GRANT UPDATE ON [dbo].[TbProductos] ([codigobarra_pro]) TO [picking_user]
GRANT UPDATE ON [dbo].[TbProductos] ([ubicacion_pro]) TO [picking_user]
GRANT SELECT ON [dbo].[TbVendedores] TO [picking_user]
GRANT SELECT ON [dbo].[TbCobranza1] TO [picking_user]
GRANT SELECT ON [dbo].[TbCobranza2] TO [picking_user]
GRANT SELECT ON [dbo].[TbRecibeDoc2] TO [picking_user]
GRANT SELECT ON [dbo].[v_productos_reservados] TO [picking_user]
GRANT SELECT ON [dbo].[v_pedidos_reservados] TO [picking_user]
GRANT SELECT ON [dbo].[v_cuentas_x_cobrar] TO [picking_user]
GRANT SELECT ON [dbo].[v_facturas_pagos] TO [picking_user]
GO


-- ALTER USER [picking_user] WITH LOGIN=[picking]

--You can use the following query in the context of your database to check for orphans:

/*select
    dp.name [user_name]
    ,dp.type_desc [user_type]
    ,isnull(sp.name,'Orhphaned!') [login_name]
    ,sp.type_desc [login_type]
from   
    sys.database_principals dp
    left join sys.server_principals sp on (dp.sid = sp.sid)
where
    dp.type in ('S','U','G')
    and dp.principal_id >4
order by sp.name
*/