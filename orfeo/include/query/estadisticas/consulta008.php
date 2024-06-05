<?php

/** Estadistica de permisos por rol
 * 
 * @autor Jenny Gamez
 * @version ORFEO 5.5
 * 
 */
$coltp3Esp = '"' . $tip3Nombre[3][2] . '"';
if (!$orno)
    $orno = 1;
$orderE = "	ORDER BY $orno $ascdesc ";

$desde = $fecha_ini . " " . "00:00:00";
$hasta = $fecha_fin . " " . "23:59:59";

/*$sWhereFec = " and (" . $db->conn->SQLDate('Y/m/d H:i:s', 'r.radi_fech_radi') . " >= '$desde'
                and " . $db->conn->SQLDate('Y/m/d H:i:s', 'r.radi_fech_radi') . " <= '$hasta')";*/

$sWhereFec = " AND ".$db->conn->SQLDate('Y/m/d', 'r.fech_vcmto') . " BETWEEN '$desde' AND '$hasta' ";

if ($dependencia_busq != 99999)
    $condicionE = "	AND r.RADI_DEPE_ACTU='$dependencia_busq' ";

switch ($db->driver) {
    case 'mssql': {
        $redondeo = "(DATEDIFF (d," . $db->conn->sysTimeStamp . ",b.RADI_FECH_RADI)+floor(dt.dias_termino * 7/5)+(select count(*) from sgd_noh_nohabiles where NOH_FECHA between b.RADI_FECH_RADI and " . $db->conn->sysTimeStamp . "))";
        $sWhere = " WHERE b.radi_nume_radi not in (select anex_radi_nume from anexos where anex_estado > 2)
                $whereTipoRadicado AND b.radi_usua_actu=us.usua_codi
                AND de.depe_codi=us.depe_codi AND b.radi_depe_actu=de.depe_codi $condicionE AND b.radi_depe_actu <> '0999' $sWhereFec ";

        $queryE = 'select de.DEPE_NOMB as "DEPENDENCIA_ACTUAL"'
                . ', b.RADI_NUME_RADI as "RADICADO"'
                . ', ' . $db->conn->SQLDate('Y/m/d H:i:s', 'b.radi_fech_radi') . ' as "FECHA_DE_RADICADO"'
                . ', c.SGD_TPR_DESCRIP as "TIPO_DOCUMENTO"'
                . ', b.RA_ASUN  as "ASUNTO"' . $colAgendado .
                ' ,us.USUA_NOMB as "USUARIO_ACTUAL"
                , d.SGD_DIR_NOMREMDES  as "REMITENTE/DESTINATARIO"
                ,' . $redondeo . ' as "DIAS_VENCIDOS"
                ,b.RADI_PATH AS "RUTA"            
            from radicado b 
                left outer join SGD_TPR_TPDCUMENTO c on b.tdoc_codi=c.sgd_tpr_codigo
                left outer join USUARIO us on b.radi_usua_actu=us.usua_codi
                left outer join DEPENDENCIA de on b.radi_depe_actu=de.depe_codi
                left outer join SGD_DIR_DRECCIONES d on b.radi_nume_radi=d.radi_nume_radi
                left outer join SGD_DT_RADICADO dt on b.radi_nume_radi=dt.radi_nume_radi
                ' . $sWhere . " AND DATEDIFF (d," . $db->conn->sysTimeStamp . ",b.RADI_FECH_RADI)+floor(dt.dias_termino * 7/5)+(select count(*) from sgd_noh_nohabiles where NOH_FECHA between b.RADI_FECH_RADI and " . $db->conn->sysTimeStamp . ") <= 0"
        . " ";
                
        }break;
    case 'postgres': {
        $redondeo = "date_part('days', r.radi_fech_radi-" . $db->conn->sysTimeStamp . ")+floor(dt.dias_termino * 7/5)+(select count(*) from sgd_noh_nohabiles where NOH_FECHA between r.radi_fech_radi and " . $db->conn->sysTimeStamp . ")";
        $sWhere = " WHERE r.RADI_NUME_DERI = '0'
                $whereTipoRadicado AND r.radi_usua_actu=us.usua_codi
                AND de.depe_codi=us.depe_codi AND r.radi_depe_actu=de.depe_codi $condicionE AND r.radi_depe_actu <> '0999' $sWhereFec ";

        $queryE = 'select de.DEPE_NOMB as "DEPENDENCIA_ACTUAL"'
                . ', r.RADI_NUME_RADI as "RADICADO"'
                . ', ' . $db->conn->SQLDate('Y/m/d H:i:s', 'r.radi_fech_radi') . ' as "FECHA_DE_RADICADO"'
                . ', ' . $db->conn->SQLDate('Y/m/d H:i:s', 'r.fech_vcmto') . ' as "FECHA_VENCIMIENTO"'
                . ', c.SGD_TPR_DESCRIP as "TIPO_DOCUMENTO"'
                . ', r.RA_ASUN  as "ASUNTO"' . $colAgendado .
                ' , us.USUA_NOMB as "USUARIO_ACTUAL"
                , d.SGD_DIR_NOMREMDES  as "REMITENTE/DESTINATARIO"
                ,' . $redondeo . ' as "DIAS_VENCIDOS"
                ,r.RADI_PATH AS "RUTA"            
            from radicado r 
                left outer join SGD_TPR_TPDCUMENTO c on r.tdoc_codi=c.sgd_tpr_codigo
                left outer join USUARIO us on r.radi_usua_actu=us.usua_codi
                left outer join DEPENDENCIA de on r.radi_depe_actu=de.depe_codi
                left outer join SGD_DIR_DRECCIONES d on r.radi_nume_radi=d.radi_nume_radi
                left outer join SGD_DT_RADICADO dt on r.radi_nume_radi=dt.radi_nume_radi
                ' . $sWhere . " AND date_part('days', r.radi_fech_radi-" . $db->conn->sysTimeStamp . ")+floor(dt.dias_termino * 7/5)+(select count(*) from sgd_noh_nohabiles where NOH_FECHA between r.radi_fech_radi and " . $db->conn->sysTimeStamp . ") <= 0"
        . $whereTipoRadicado ." ";
                
        }break;
        case 'oci8': {

        $redondeo = "TO_CHAR(TO_NUMBER(SUBSTR( (r.radi_fech_radi - ". $db->conn->sysTimeStamp ."),10 ,1 )) + floor(dt.dias_termino * 7/5)+(select count(*) from sgd_noh_nohabiles where NOH_FECHA between r.radi_fech_radi and ". $db->conn->sysTimeStamp .") )";
        
        $sWhere = " WHERE r.radi_nume_radi not in (select anex_radi_nume from anexos where anex_estado > 2)
                $whereTipoRadicado AND r.radi_usua_actu=us.usua_codi
                AND de.depe_codi=us.depe_codi AND r.radi_depe_actu=de.depe_codi $condicionE AND r.radi_depe_actu <> '999' $sWhereFec ";

        $queryE = 'select de.DEPE_NOMB as "DEPENDENCIA_ACTUAL"'
                . ', r.RADI_NUME_RADI as "RADICADO"'
                . ', ' . $db->conn->SQLDate('Y/m/d H:i:s', 'r.radi_fech_radi') . ' as "FECHA_DE_RADICADO"'
                . ', c.SGD_TPR_DESCRIP as "TIPO_DOCUMENTO"'
                . ', r.RA_ASUN  as "ASUNTO"' . $colAgendado .
                ' ,us.USUA_NOMB as "USUARIO_ACTUAL"
                , d.SGD_DIR_NOMREMDES  as "REMITENTE/DESTINATARIO"
                ,' . $redondeo . ' as "DIAS_VENCIDOS"
                ,r.RADI_PATH AS "RUTA"            
            from radicado r 
                left outer join SGD_TPR_TPDCUMENTO c on r.tdoc_codi=c.sgd_tpr_codigo
                left outer join USUARIO us on r.radi_usua_actu=us.usua_codi
                left outer join DEPENDENCIA de on r.radi_depe_actu=de.depe_codi
                left outer join SGD_DIR_DRECCIONES d on r.radi_nume_radi=d.radi_nume_radi
                left outer join SGD_DT_RADICADO dt on r.radi_nume_radi=dt.radi_nume_radi
                ' . $sWhere . " AND TO_NUMBER(SUBSTR( (r.radi_fech_radi - ". $db->conn->sysTimeStamp ."),10 ,1 )) + floor(dt.dias_termino * 7/5)+(select count(*) from sgd_noh_nohabiles where NOH_FECHA between r.radi_fech_radi and ". $db->conn->sysTimeStamp .") > 0"
        . $whereTipoRadicado ." ";
                
        }break;
}

$titulos = array("#", "1#DEPENDENCIA ACTUAL", "2#RADICADO", "3#FECHA DE RADICADO", "4#TIPO DOCUMENTO", "5#ASUNTO", "6#USUARIO ACTUAL", "7#REMITENTE/DESTINATARIO", "8#FECHA VENCIMIENTO", "9#D&Iacute;AS VENCIDOS");

function pintarEstadisticaDetalle($fila, $indice, $numColumna) {
    global $ruta_raiz, $encabezado, $krd, $ambiente;
    if (isset($fila['RADICADO'])) {
        $depeActual = $fila['DEPENDENCIA_ACTUAL'];
        $numRadicado = $fila['RADICADO'];
        $fechaRadicado = $fila['FECHA_DE_RADICADO'];
        $tipoDocumento = $fila['TIPO_DOCUMENTO'];
        $asuntoRadicado = $fila['ASUNTO'];
        $usaurioActu = $fila['USUARIO_ACTUAL'];
        $remitenteDest = $fila['REMITENTE/DESTINATARIO'];
        $diasVencidos = $fila['DIAS_VENCIDOS'];
        $ruta = $fila['RUTA'];
        $fechaVencimiento = $fila['FECHA_VENCIMIENTO'];
    } else {
        $depeActual = $fila[0];
        $numRadicado = $fila[1];
        $fechaRadicado = $fila[2];
        $tipoDocumento = $fila[4];
        $asuntoRadicado = $fila[5];
        $usaurioActu = $fila[6];
        $remitenteDest = $fila[7];
        $diasVencidos = $fila[8];
        $ruta = $fila[9];
        $fechaVencimiento = $fila[3];
    }

    switch ($numColumna) {
        case 0:
            $salida = $indice;
            break;
        case 1:
            $salida = "<center class=\"leidos\">" . $depeActual . "</center>";
            break;
        case 2:
            if ($ruta && $verImg)
                $salida = "<center><a href=\"$url_raiz/$ambiente/bodega/" . $ruta . "\">" . $numRadicado . "</a></center>";
            else
                $salida = "<center class=\"leidos\">{$numRadicado}</center>";
            break;
        case 3:
            $salida = "<center class=\"leidos\">" . $fechaRadicado . "</center>";
            break;
        case 4:
            $salida = "<center class=\"leidos\">" . $tipoDocumento . "</center>";
            break;
        case 5:
            $salida = "<center class=\"leidos\">" . $asuntoRadicado . "</center>";
            break;
        case 6:
            $salida = "<center class=\"leidos\">" . $usaurioActu . "</center>";
            break;
        case 7:
            $salida = "<center class=\"leidos\">" . $remitenteDest . "</center>";
            break;
        case 8:
            $salida = "<center class=\"leidos\">" . $fechaVencimiento . "</center>";
            break;
        case 9:
            $salida = "<center class=\"leidos\">" . $diasVencidos . "</center>";
                break;    
    }
    return $salida;
}

?>
