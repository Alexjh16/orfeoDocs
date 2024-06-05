<?PHP

/**
 * Clase donde gestionamos informacion referente a los tipos de radicados.
 *
 * @copyright Sistema de Gestion Documental ORFEO
 * @version 1.0
 * @author Ing. Hollman Ladino Paredes.
 * 
 * 19-feb-07  Agregado el metodo GetArrayIdTipRad. HLP.
 */
class TipRads {

    private $cnn; //Conexion a la BD.
    private $flag; //Bandera para usos varios.
    private $add; //Conexion al Adodb Data Dictionary.
    private $longitud_codigo_dependencia = 4;

    /**
     * Constructor de la classe.
     *
     * @param ConnectionHandler $db
     */
    function __construct($db) {
        $this->cnn = $db;
        $this->cnn->conn->SetFetchMode(ADODB_FETCH_ASSOC);
        $this->add = NewDataDictionary($this->cnn->conn);
    }

    /**
     * Agrega un nuevo tipo de radicado.
     *
     * @param array $datos  Vector asociativo con todos los campos y sus valores.
     * @return boolean $flag False on Error /
     */
    function SetInsDatosTipRad($datos) {
        //error_log(' @@@@@@@@@@@@@@@@@@@@ ' . print_r($datos, true));
        $this->cnn->conn->BeginTrans();
        
        $sgd_trad_codigo = $datos['SGD_TRAD_CODIGO'];
        $sgd_trad_descr = $datos['SGD_TRAD_DESCR'];
        $sgd_trad_genradsal = $datos['SGD_TRAD_GENRADSAL'];

        $sqlInsert = "insert into SGD_TRAD_TIPORAD (SGD_TRAD_CODIGO, SGD_TRAD_DESCR, SGD_TRAD_GENRADSAL) values ($sgd_trad_codigo,$sgd_trad_descr, $sgd_trad_genradsal) ";
        $ok1 = $this->cnn->conn->Execute($sqlInsert);

        if ($ok1) {
            $sqlDependencia = $this->add->AddColumnSQL('DEPENDENCIA', "DEPE_RAD_TP$datos[SGD_TRAD_CODIGO] C(5)");
            $ok2 = $this->cnn->conn->Execute($sqlDependencia[0]);

            $sqlTipoDoc = $this->add->AddColumnSQL('SGD_TPR_TPDCUMENTO', "SGD_TPR_TP$datos[SGD_TRAD_CODIGO] I1");
            $ok3 = $this->cnn->conn->Execute($sqlTipoDoc[0]);

            $sqlUsuario = $this->add->AddColumnSQL('USUARIO', "USUA_PRAD_TP$datos[SGD_TRAD_CODIGO] I1");
            $ok4 = $this->cnn->conn->Execute($sqlUsuario[0]);

            $sqlPerfiles = $this->add->AddColumnSQL('PERFILES', "USUA_PRAD_TP$datos[SGD_TRAD_CODIGO] I1");
            $ok5 = $this->cnn->conn->Execute($sqlPerfiles[0]);

            // $insertCarp = "insert into CARPETA (CARP_CODI,CARP_DESC) values (".$datos['SGD_TRAD_CODIGO'].", '".$datos['SGD_TRAD_DESCR']."')";
            // $ok6 = $this->cnn->conn->Execute($insertCarp[0]);

            $sqlTipoTecero = $this->add->AddColumnSQL('SGD_TIP3_TIPOTERCERO', "SGD_TPR_TP$datos[SGD_TRAD_CODIGO] I1 DEFAULT 1");
            $ok7 = $this->cnn->conn->Execute($sqlTipoTecero[0]);

            $ok8 = $this->cnn->conn->Replace('SGD_TIP3_TIPOTERCERO', array('SGD_TIP3_CODIGO' => 1, 'SGD_TPR_TP' . $datos['SGD_TRAD_CODIGO'] => 0), 'SGD_TIP3_CODIGO', true);

            // $insertSecu = "create sequence secr_tp".$datos['SGD_TRAD_CODIGO']."_998  start with 1  increment by 1  maxvalue 9999999999  minvalue 1 ";
            // $ok9 = $this->cnn->conn->Execute($insertSecu[0]);
        }

        if ($ok1 && $ok2 && $ok3 && $ok4 && $ok5 /*&& $ok6*/ && $ok7) {
            $this->cnn->conn->CommitTrans();
            $this->flag = 4;
        } else {
            $this->cnn->conn->RollbackTrans();
            $this->flag = 2;
        }
        return $this->flag;
    }

    /**
     * Modifica datos a un tipo de radicado.
     *
     * @param array $datos  Vector asociativo con todos los campos y sus valores.
     * @return boolean $flag False on Error /
     */
    function SetModDatosTipRad($datos) {
        $this->cnn->conn->BeginTrans();
        $flag = $this->cnn->conn->Replace('CARPETA', array('CARP_CODI' => $datos['SGD_TRAD_CODIGO'],
            'CARP_DESC' => $datos['SGD_TRAD_DESCR']), 'CARP_CODI');
        if ($flag)
            $flag = $this->cnn->conn->Replace('SGD_TRAD_TIPORAD', $datos, 'SGD_TRAD_CODIGO');
        if ($flag) {
            $this->cnn->conn->CommitTrans();
            $this->flag = 3;
        } else {
            $this->cnn->conn->RollbackTrans();
            $this->flag = 2;
        }
        return $this->flag;
    }

    /**
     * Elimina un tipo de radicado.
     *
     * @param  int $dato  Id del T.R. a eliminar.
     * @return boolean $flag 5 on Error /
     */
    function SetDelDatosTipRad($dato) {
        $this->cnn->conn->BeginTrans();
        $sql = "SELECT count(RADI_NUME_RADI) as CNT FROM RADICADO WHERE " . $this->cnn->conn->substr . "(RADI_NUME_RADI,15,1) like '%" . $dato . "'";
        $rs = $this->cnn->conn->Execute($sql);

        //$rs = $this->cnn->conn->CacheExecute(15, $sql);
        $resultados = $rs->FetchRow();
        //if ($resultados['CNT'] == $dato || $rs->RecordCount() > 0) {
        if ($resultados['CNT'] > 0) {
            $this->flag = 5;
        } else {
            $ok1 = $this->cnn->conn->Execute("DELETE FROM SGD_TRAD_TIPORAD WHERE SGD_TRAD_CODIGO=" . $dato);
            $sql = $this->add->DropColumnSQL('DEPENDENCIA', 'DEPE_RAD_TP' . $dato);
            $ok2 = $this->cnn->conn->Execute($sql[0]);
            $sql = $this->add->DropColumnSQL('SGD_TPR_TPDCUMENTO', 'SGD_TPR_TP' . $dato);
            $ok3 = $this->cnn->conn->Execute($sql[0]);
            $sql = $this->add->DropColumnSQL('USUARIO', 'USUA_PRAD_TP' . $dato);
            $ok4 = $this->cnn->conn->Execute($sql[0]);
            $ok5 = $this->cnn->conn->Execute("DELETE FROM CARPETA WHERE CARP_CODI=" . $dato);
            $sql = $this->add->DropColumnSQL('SGD_TIP3_TIPOTERCERO', 'SGD_TPR_TP' . $dato);
            $ok6 = $this->cnn->conn->Execute($sql[0]);
            $sql = $this->add->DropColumnSQL('PERFILES', 'USUA_PRAD_TP' . $dato);
            $ok7 = $this->cnn->conn->Execute($sql[0]);
            $deleteSecu = "drop sequence secr_tp".$dato."_998";
            $ok9 = $this->cnn->conn->Execute($deleteSecu[0]);
            //$ok7 = $this->add->DropTableSQL($tabname);	//Eliminacion de las tablas secuenciales.
            //$sql = "SELECT DEPE_RAD_TP".$dato." FROM DEPENDENCIA GROUP BY DEPE_RAD_TP".$dato;
        }

        if ($ok1 && $ok2 && $ok3 && $ok4 && $ok5 && $ok6) {
            $this->cnn->conn->CommitTrans();
            $this->flag = 0;
        } else {
            $this->cnn->conn->RollbackTrans();
            $this->flag = 5;
        }
        return $this->flag . $sql;
    }

    /**
     * Retorna un vector todos los tipos de radicados.
     *
     * @return array $vector False on Error.
     */
    function GetArrayIdTipRad() {
        $sql = "SELECT SGD_TRAD_CODIGO as ID FROM SGD_TRAD_TIPORAD ORDER BY 1";
        $rs = $this->cnn->conn->Execute($sql);
        if (!$rs)
            $this->flag = 5;
        else {
            $this->flag = $this->cnn->conn->GetAll($sql);
        }
        return $this->flag;
    }

}

?>
