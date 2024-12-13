USE [Lefre_DV_Dev]
GO

/****** Object:  View [dbo].[v_cuentas_x_cobrar]    Script Date: 11/7/2022 8:55:59 a. m. ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE VIEW [dbo].[v_cuentas_x_cobrar]
AS
SELECT        codigo_cxc, documento_cxc, SUM(debe_cxc) AS monto_fac, SUM(haber_cxc) AS pagado_fac, SUM(debe_cxc - haber_cxc) AS saldo_fac
FROM            dbo.TbCuentasCob
GROUP BY codigo_cxc, documento_cxc
HAVING        (SUM(debe_cxc - haber_cxc) >= 0.01)
GO


CREATE VIEW [dbo].[v_facturas_pagos]
AS
SELECT        codigo_cxc, documento_cxc, SUM(debe_cxc) AS monto_fac, SUM(haber_cxc) AS pagado_fac, SUM(debe_cxc - haber_cxc) AS saldo_fac
FROM            dbo.TbCuentasCob
GROUP BY codigo_cxc, documento_cxc
GO


CREATE VIEW [dbo].[v_pedidos_reservados]
AS
SELECT        numero_ped
FROM            dbo.TbPedidos1
WHERE        (status_ped IN ('', 'ASIGNADO', 'PENDIENTE', 'RETENIDO'))
GO
