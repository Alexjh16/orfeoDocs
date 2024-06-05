<?php
/**
 * En este frame se van cargado cada una de las funcionalidades del sistema
 *
 * Descripcion Larga
 *
 * @category
 * @package      SGD Orfeo
 * @subpackage   Main
 * @author       Community
 * @author       Skina Technologies SAS (http://www.skinatech.com)
 * @license      GNU/GPL <http://www.gnu.org/licenses/gpl-2.0.html>
 * @link         http://www.orfeolibre.org
 * @version      SVN: $Id$
 * @since
 */

        /*---------------------------------------------------------+
        |                     INCLUDES                             |
        +---------------------------------------------------------*/


        /*---------------------------------------------------------+
        |                    DEFINICIONES                          |
        +---------------------------------------------------------*/


        /*---------------------------------------------------------+
        |                       MAIN                               |
        +---------------------------------------------------------*/



   session_start();
   $ruta_raiz="..";

   require_once("$ruta_raiz/include/db/ConnectionHandler.php");

   $db = new ConnectionHandler("$ruta_raiz");

   //error_reporting(7);
   $db->conn->SetFetchMode(ADODB_FETCH_NUM);
   $db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

   // $db->conn->debug=true;

   // Parametro de entrada -> Codigo del suscriptor enviado desde el formulario de radicacion.
   $q = $_GET["subscriber"];

// 0: ciudadanos, 1: Terceros , 2: Empresas, 6:funcionarios



$isql1="SELECT ARE_ESP_SECUE AS TDID_CODI,IDENTIFICADOR_EMPRESA AS SGD_CIU_CODIGO,NOMBRE_DE_LA_EMPRESA as SGD_CIU_NOMBRE, DIRECCION as".
                       " SGD_CIU_DIRECCION, NOMBRE_REP_LEGAL  as SGD_CIU_APELL1, SIGLA_DE_LA_EMPRESA as SGD_CIU_APELL2, TELEFONO_1 AS SGD_CIU_TELEFONO,".
                       "EMAIL AS SGD_CIU_EMAIL,  CAST(CODIGO_DEL_MUNICIPIO as numeric(10,0))  as MUNI_CODI, CAST( CODIGO_DEL_DEPARTAMENTO as numeric(10,0))".
                       " as DPTO_CODI, NIT_DE_LA_EMPRESA AS SGD_CIU_CEDULA, ID_CONT,ID_PAIS, CODIGO_SUSCRIPTOR  from BODEGA_EMPRESAS WHERE 
                         replace ( replace (replace ( trim ( both ' 'from upper( CODIGO_SUSCRIPTOR )),' ',''),'.',''),'-','')  = '$q' and ACTIVA = 1  ";

   //Para conocer  si es ciudadano, empresa,entidad o funcionario.

   $rs1=$db->query($isql1);

   $nomb1=$rs1->fields["SGD_CIU_CEDULA"];

//Modificado skinatech
//Garantizamos que solo setea un tbusqueda
//Presentaba error cuando el mismo codigo estaba en varias tablas
//ej ciu 1 y oem 1, tomaba el ultimo y no el real
  $tbusqueda=0;   
  if($nomb1!=null) { $tbusqueda=0; $isql=$isql1; }
/*  elseif($nomb1!=null) { $tbusqueda=1; $isql=$isql1; }
  elseif($nomb2!=null) { $tbusqueda=2; $isql=$isql2; }
  elseif($nomb6!=null) { $tbusqueda=6; $isql=$isql6; }*/

//  $isql=$isql0 ." UNION ".$isql1." UNION ".$isql2." UNION ".$isql6;
//ECHO "TBUSQUEDA $tbusqueda nom $nomb0 1 $nomb1 2 $nomb2 6 $nomb6";
  $rs=$db->query($isql);

  $jsondata=array(); 
   
//   $jsondata['DOCUMENTO']    =$rs->fields["SGD_CIU_CODIGO"];

   $jsondata['NOM']          = str_replace('"',' ',$rs->fields["SGD_CIU_NOMBRE"]) . " ";
   $jsondata['APELL1']       = str_replace('"',' ',$rs->fields["SGD_CIU_APELL1"]) . " ";
   $jsondata['APELL2']       = str_replace('"',' ',$rs->fields["SGD_CIU_APELL2"]) . " ";
   $jsondata['TELEFONO']     = str_replace('"',' ',$rs->fields["SGD_CIU_TELEFONO"]) . " ";
   $jsondata['DIRECCION']    = str_replace('"',' ',$rs->fields["SGD_CIU_DIRECCION"]) . " ";
   $jsondata['DOCUMENTO']    = trim($rs->fields["SGD_CIU_CODIGO"]);
   $jsondata['MAIL']         = str_replace('"',' ',$rs->fields["SGD_CIU_EMAIL"]) . " ";
   $jsondata['TIPO_EMPRESA'] = 1;
   $jsondata['CC_DOCUMENTO'] = trim($rs->fields["SGD_CIU_CEDULA"]) ;
   $jsondata['CONT']         = $rs->fields["ID_CONT"];
   $jsondata['PAIS']         = $rs->fields["ID_PAIS"];
   $jsondata['DPTO']         = $jsondata['PAIS']."-".$rs->fields["DPTO_CODI"];
   $jsondata['MUNI']         = $jsondata['DPTO']."-".$rs->fields["MUNI_CODI"];


  echo json_encode($jsondata);

 /*  // Se guardaron los datos de suscriptor en la tabla de terceros, se realiza mapeo de datos.

   $isql1="SELECT ARE_ESP_SECUE AS TDID_CODI,IDENTIFICADOR_EMPRESA AS SGD_CIU_CODIGO,NOMBRE_DE_LA_EMPRESA as SGD_CIU_NOMBRE, DIRECCION as".
                       " SGD_CIU_DIRECCION, NOMBRE_REP_LEGAL  as SGD_CIU_APELL1, SIGLA_DE_LA_EMPRESA as SGD_CIU_APELL2, TELEFONO_1 AS SGD_CIU_TELEFONO,".
                       "EMAIL AS SGD_CIU_EMAIL,  CAST(CODIGO_DEL_MUNICIPIO as numeric(10,0))  as MUNI_CODI, CAST( CODIGO_DEL_DEPARTAMENTO as numeric(10,0))".
                       " as DPTO_CODI, NIT_DE_LA_EMPRESA AS SGD_CIU_CEDULA, ID_CONT,ID_PAIS  from BODEGA_EMPRESAS WHERE replace ( replace (replace ( trim ( both ' 'from upper( NIT_DE_LA_EMPRESA )),' ',''),'.',''),'-','') = '$q' and ACTIVA = 1  ";

   
   // Para identificar como empresa
   $tbusqueda=1;   

   $rs=$db->query($isql1);

   $jsondata=array(); 
   
   $jsondata['NOM']          = str_replace('"',' ',$rs->fields["SGD_CIU_NOMBRE"]) . " ";
   $jsondata['APELL1']       = str_replace('"',' ',$rs->fields["SGD_CIU_APELL1"]) . " ";
   $jsondata['APELL2']       = str_replace('"',' ',$rs->fields["SGD_CIU_APELL2"]) . " ";
   $jsondata['TELEFONO']     = str_replace('"',' ',$rs->fields["SGD_CIU_TELEFONO"]) . " ";
   $jsondata['DIRECCION']    = str_replace('"',' ',$rs->fields["SGD_CIU_DIRECCION"]) . " ";
   $jsondata['DOCUMENTO']    = trim($rs->fields["SGD_CIU_CODIGO"]);
   $jsondata['MAIL']         = str_replace('"',' ',$rs->fields["SGD_CIU_EMAIL"]) . " ";
   $jsondata['TIPO_EMPRESA'] = $tbusqueda;
   $jsondata['CC_DOCUMENTO'] = trim($rs->fields["SGD_CIU_CEDULA"]) ;
   $jsondata['CONT']         = $rs->fields["ID_CONT"];
   $jsondata['PAIS']         = $rs->fields["ID_PAIS"];
   $jsondata['DPTO']         = $jsondata['PAIS']."-".$rs->fields["DPTO_CODI"];
   $jsondata['MUNI']         = $jsondata['DPTO']."-".$rs->fields["MUNI_CODI"];

   echo json_encode($jsondata);*/
  
?>
