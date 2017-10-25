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

class cl_solicitacaorepasse extends DAOBasica {

  public function __construct() {
    parent::__construct("plugins.solicitacaorepasse");
  }

  public function sql_query_geral($sCampos = "*", $sWhere = null) {

    $sSqlBusca  = "  select {$sCampos} ";
    $sSqlBusca .= "    from plugins.solicitacaorepasse ";
    $sSqlBusca .= "          left join plugins.solicitacaorepasseempnota on solicitacaorepasseempnota.solicitacaorepasse = solicitacaorepasse.sequencial ";
    $sSqlBusca .= "          left join empnota                           on empnota.e69_codnota                          = solicitacaorepasseempnota.empnota ";
    $sSqlBusca .= "          left join empempenho                        on empempenho.e60_numemp                        = empnota.e69_numemp ";
    $sSqlBusca .= "         inner join orcunidade on orcunidade.o41_anousu       = ( case                                     ";    
    $sSqlBusca .= "                                                                    when empempenho.e60_anousu is not null ";
    $sSqlBusca .= "                                                                      then empempenho.e60_anousu           ";
    $sSqlBusca .= "                                                                    else solicitacaorepasse.unidade_anousu ";
    $sSqlBusca .= "                                                                  end )                                    ";
    $sSqlBusca .= "                              and orcunidade.o41_orgao   = solicitacaorepasse.unidade_orgao  ";
    $sSqlBusca .= "                              and orcunidade.o41_unidade = solicitacaorepasse.unidade_codigo ";
    $sSqlBusca .= "         inner join orcdotacao on orcdotacao.o58_anousu  = orcunidade.o41_anousu  ";
    $sSqlBusca .= "                              and orcdotacao.o58_orgao   = orcunidade.o41_orgao   ";
    $sSqlBusca .= "                              and orcdotacao.o58_unidade = orcunidade.o41_unidade ";
    $sSqlBusca .= "         inner join orctiporec on orctiporec.o15_codigo = solicitacaorepasse.recurso ";
    $sSqlBusca .= "         left join saltes     on saltes.k13_reduz      = solicitacaorepasse.conta ";
    $sSqlBusca .= "         left join conplanoreduz on conplanoreduz.c61_reduz  = saltes.k13_reduz ";
    $sSqlBusca .= "                                 and conplanoreduz.c61_anousu = solicitacaorepasse.unidade_anousu ";
    $sSqlBusca .= "         left join conplanocontabancaria on conplanocontabancaria.c56_codcon = conplanoreduz.c61_codcon ";
    $sSqlBusca .= "                                         and conplanocontabancaria.c56_anousu = conplanoreduz.c61_anousu ";
    $sSqlBusca .= "         left join contabancaria         on contabancaria.db83_sequencial = conplanocontabancaria.c56_contabancaria ";

    if (!empty($sWhere)) {
      $sSqlBusca .= " where {$sWhere} ";
    }
    return $sSqlBusca;
  }

  public function sql_query_cancelamento($sCampos = "*", $sWhere = null) {

    $sSqlBusca  = "  select {$sCampos} ";
    $sSqlBusca .= "    from plugins.solicitacaorepasse ";
    $sSqlBusca .= "         inner join orcunidade on orcunidade.o41_anousu  = solicitacaorepasse.unidade_anousu ";
    $sSqlBusca .= "                              and orcunidade.o41_orgao   = solicitacaorepasse.unidade_orgao  ";
    $sSqlBusca .= "                              and orcunidade.o41_unidade = solicitacaorepasse.unidade_codigo ";
    $sSqlBusca .= "         inner join orcdotacao on orcdotacao.o58_anousu  = orcunidade.o41_anousu  ";
    $sSqlBusca .= "                              and orcdotacao.o58_orgao   = orcunidade.o41_orgao   ";
    $sSqlBusca .= "                              and orcdotacao.o58_unidade = orcunidade.o41_unidade ";
    $sSqlBusca .= "         inner join orctiporec on orctiporec.o15_codigo = solicitacaorepasse.recurso ";
    $sSqlBusca .= "          left join saltes     on saltes.k13_reduz      = solicitacaorepasse.conta ";
    $sSqlBusca .= "          left join conplanoreduz on conplanoreduz.c61_reduz  = saltes.k13_reduz ";
    $sSqlBusca .= "                                 and conplanoreduz.c61_anousu = solicitacaorepasse.unidade_anousu ";
    $sSqlBusca .= "          left join conplanocontabancaria on conplanocontabancaria.c56_codcon = conplanoreduz.c61_codcon ";
    $sSqlBusca .= "                                         and conplanocontabancaria.c56_anousu = conplanoreduz.c61_anousu ";
    $sSqlBusca .= "          left join contabancaria         on contabancaria.db83_sequencial = conplanocontabancaria.c56_contabancaria ";
    $sSqlBusca .= "         inner join plugins.autorizacaorepasse on solicitacaorepasse.sequencial = autorizacaorepasse.solicitacaorepasse ";
    $sSqlBusca .= "         inner join slip                       on autorizacaorepasse.slip = k17_codigo ";
    $sSqlBusca .= "         left join empageslip                 on k17_codigo = e89_codigo ";
    $sSqlBusca .= "         left join empagemov                  on e89_codmov = e81_codmov ";
    $sSqlBusca .= "         left join empagemovslips             on k107_empagemov = e81_codmov ";
    $sSqlBusca .= "         left join empageconfgera             on e90_codmov = e81_codmov ";

    if (!empty($sWhere)) {
      $sSqlBusca .= " where {$sWhere} ";
    }
    return $sSqlBusca;
  }

  public function sql_query_autorizacao($sCampos = '*', $sWhere = null) {

    $sSql  = " select {$sCampos} ";
    $sSql .= " from plugins.solicitacaorepasse ";
    $sSql .= " inner join plugins.autorizacaorepasse on solicitacaorepasse.sequencial = autorizacaorepasse.solicitacaorepasse ";

    if (!empty($sWhere)) {
      $sSql .= " where {$sWhere} ";
    }

    return $sSql;
  }
}
