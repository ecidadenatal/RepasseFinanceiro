<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBSeller Servicos de Informatica
 *                            www.dbseller.com.br
 *                         e-cidade@dbseller.com.br
 *
 *  Este programa e software livre; voce pode redistribui-lo e/ou
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 *  publicada pela Free Software Foundation; tanto a versao 2 da
 *  Licenca como (a seu criterio) qualquer versao mais nova.
 *
 *  Este programa e distribuido na expectativa de ser util, mas SEM
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
 *  detalhes.
 *
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU
 *  junto com este programa; se nao, escreva para a Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *  02111-1307, USA.
 *
 *  Copia da licenca no diretorio licenca/licenca_en.txt
 *                                licenca/licenca_pt.txt
 */

class cl_solicitacaorepasseempnota extends DAOBasica {

  public function __construct() {
    parent::__construct("plugins.solicitacaorepasseempnota");
  }

  public function sql_query_autorizacao($sCampos = '*', $sWhere = null, $sOrder = null) {

    $sSql  = " select {$sCampos} from plugins.solicitacaorepasseempnota ";
    $sSql .= " inner join plugins.autorizacaorepasse on autorizacaorepasse.solicitacaorepasse = solicitacaorepasseempnota.solicitacaorepasse ";

    if (!empty($sWhere)) {
      $sSql .= " where {$sWhere} ";
    }

    if (!empty($sOrder)) {
      $sSql .= " order by {$sOrder} ";
    }

    return $sSql;
  }

  public function sql_query_notas($sCampos = '*', $sWhere = null, $sOrder = null) {

    $sSql  = "select {$sCampos} ";
    $sSql .= " from plugins.solicitacaorepasseempnota ";
    $sSql .= "     inner join empnota      on empnota.e69_codnota = solicitacaorepasseempnota.empnota ";
    $sSql .= "     inner join empnotaele   on empnota.e69_codnota = empnotaele.e70_codnota ";
    $sSql .= "     inner join empempenho   on e69_numemp = e60_numemp ";
    $sSql .= "     left  join pagordemnota on e71_codnota = e69_codnota ";
    $sSql .= "     left  join pagordem     on e71_codord  = e50_codord ";
    $sSql .= "     left  join pagordemele  on e53_codord  = e50_codord ";

    if (!empty($sWhere)) {
      $sSql .= " where {$sWhere} ";
    }

    if (!empty($sOrder)) {
      $sSql .= " order by {$sOrder} ";
    }

    return $sSql;
  }

}
