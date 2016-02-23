<?php
/**
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

/**
 * Representa a ligação entre a Nota de Liquidação
 * e a Solicitação de Repasse Financeiro
 */
class NotaSolicitacaoRepasseFinanceiro {

  /**
   * @var SolicitacaoRepasseFinanceiro
   */
  private $oSolicitacaoRepasseFinanceiro;

  /**
   * @var NotaLiquidacao
   */
  private $oNotaLiquidacao;

  /**
   *
   * @var boolean
   */
  private $lEstornado = false;

  /**
   * Código da nota de liquidação
   * @var integer
   */
  private $iCodigoNotaLiquidacao;

  public function __construct(SolicitacaoRepasseFinanceiro $oSolicitacaoRepasse) {
    $this->oSolicitacaoRepasseFinanceiro = $oSolicitacaoRepasse;
  }

  /**
   * @return SolicitacaoRepasseFinanceiro
   */
  public function getSolicitacaoRepasseFinanceiro() {
    return $this->oSolicitacaoRepasseFinanceiro;
  }

  /**
   * @return NotaLiquidacao
   */
  public function getNotaLiquidacao() {

    if (empty($this->oNotaLiquidacao) && $this->getCodigoNotaLiquidacao()) {
      $this->oNotaLiquidacao = new NotaLiquidacao($this->getCodigoNotaLiquidacao());
    }

    return $this->oNotaLiquidacao;
  }

  /**
   * @param NotaLiquidacao $oNotaLiquidacao
   */
  public function setNotaLiquidacao(NotaLiquidacao $oNotaLiquidacao) {

    $this->oNotaLiquidacao = $oNotaLiquidacao;
    $this->setCodigoNotaLiquidacao($oNotaLiquidacao->getCodigoNota());
  }

  /**
   * @return integer
   */
  public function getCodigoNotaLiquidacao() {
    return $this->iCodigoNotaLiquidacao;
  }

  /**
   * @param integer $iCodigoNotaLiquidacao
   */
  public function setCodigoNotaLiquidacao($iCodigoNotaLiquidacao) {

    $this->iCodigoNotaLiquidacao = $iCodigoNotaLiquidacao;
    $this->oNotaLiquidacao = null;
  }

  /**
   * @return boolean
   */
  public function getEstornado()
  {
    return $this->lEstornado;
  }

  /**
   * @param boolean $lEstornado
   */
  public function setEstornado($lEstornado)
  {
    $this->lEstornado = $lEstornado;
  }

  public function getMesCompetencia() {
  	
    $oDaoLiquidacaoCompetencia = new cl_liquidacaocompetencia();
    $oDaoPagordemNota          = new cl_pagordemnota();
    
    $sSqlPagordemNota   = $oDaoPagordemNota->sql_query(null, $this->iCodigoNotaLiquidacao, "*", null, "");
    $rsPagordemNota     = $oDaoPagordemNota->sql_record($sSqlPagordemNota);
    $oPagordemNota      = db_utils::fieldsMemory($rsPagordemNota, 0);
    
    $sWhereMesCompetencia = " pagordem = {$oPagordemNota->e71_codord} ";
    $sSqlMesCompetencia   = $oDaoLiquidacaoCompetencia->sql_query(null, null, null, $sWhereMesCompetencia);
    $rsMesCompetencia     = $oDaoLiquidacaoCompetencia->sql_record($sSqlMesCompetencia);
    $oMesCompetencia = db_utils::fieldsMemory($rsMesCompetencia, 0);
    
    return $oMesCompetencia->mes.'/'.$oMesCompetencia->ano;
  }
}
