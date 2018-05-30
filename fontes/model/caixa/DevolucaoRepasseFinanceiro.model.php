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
class DevolucaoRepasseFinanceiro {

  /**
   * @var integer
   */
  private $iCodigo;

  /**
   * @var SolicitacaoRepasseFinanceiro
   */
  private $oSolicitacaoRepasse = null;

  /**
   * @var AutorizacaoSolicitacaoRepasse
   */
  private $oAutorizacaoRepasse;

  /**
   * @var Transferencia
   */
  private $oTransferencia = null;

  /**
   * @var double
   */
  private $nValorDevolucao;

  /**
   * @var integer
   */
  private $nValorDevolvido;

  /**
   * @var array
   */
  private $aNotasLiquidacao;

  public function __construct($oSolicitacao, $nValorDevolucao) {

    $this->oSolicitacaoRepasse = $oSolicitacao;
    $this->nValorDevolucao     = $nValorDevolucao;
  }

  /**
   * @return int
   */
  public function getCodigo() {
    return $this->iCodigo;
  }

  /**
   * @param int $iCodigo
   */
  public function setCodigo($iCodigo) {
    $this->iCodigo = $iCodigo;
  }

  /**
   * @return SolicitacaoRepasseFinanceiro
   */
  public function getSolicitacaoRepasse() {
    return $this->oSolicitacaoRepasse;
  }

  /**
   * @param SolicitacaoRepasseFinanceiro $oSolicitacaoRepasse
   */
  public function setSolicitacaoRepasse($oSolicitacaoRepasse) {
    $this->oSolicitacaoRepasse = $oSolicitacaoRepasse;
  }

  /**
   * @return Transferencia
   */
  public function getTransferencia() {

    return $this->oTransferencia;
  }

  /**
   * @param Transferencia $oTransferencia
   */
  public function setTransferencia($oTransferencia) {
    $this->oTransferencia = $oTransferencia;
  }

  /**
   * @return float
   */
  public function getValor() {
    return $this->nValorDevolucao;
  }

  /**
   * @param float $nValor
   */
  public function setValor($nValor) {
    $this->nValorDevolucao = $nValor;
  }

  /**
   * @return array
   */
  public function getNotasLiquidacao() {
    return $this->aNotasLiquidacao;
  }

  /**
   * @param array $aNotasLiquidacao
   */
  public function setNotasLiquidacao($aNotasLiquidacao) {
    $this->aNotasLiquidacao = $aNotasLiquidacao;
  }

  /**
   * Realiza a devolu��o da solicita��o e retorna o Slip gerado.
   * @return integer C�digo do slip gerado.
   * @throws BusinessException
   * @throws Exception
   */
  public function devolver() {

    if (empty($this->oSolicitacaoRepasse) || $this->oSolicitacaoRepasse->getCodigo() == '') {
      throw new Exception("A Solicita��o de Repasse Financeiro n�o foi informada.");
    }

    // Busca o valor total j� devolvido da solicita��o.
    $sCampos = " COALESCE(sum(devolucaosolicitacaorepasse.valor), 0) as total ";
    $sWhere  = " devolucaosolicitacaorepasse.solicitacaorepasse = {$this->oSolicitacaoRepasse->getCodigo()} ";

    $oDaoDevolucoes = new cl_devolucaosolicitacaorepasse();
    $sSqlDevolucoes = $oDaoDevolucoes->sql_query(null, $sCampos, null, $sWhere);
    $rsDevolucoes   = $oDaoDevolucoes->sql_record($sSqlDevolucoes);

    if ($oDaoDevolucoes->numrows != 1) {
      throw new Exception("N�o foi poss�vel verificar o total devolvido da solicita��o selecionada.");
    }

    $oDevolucoes           = db_utils::fieldsMemory($rsDevolucoes, 0);
    $this->nValorDevolvido = $oDevolucoes->total;

    $this->carregarAutorizacao();
    $this->validar();
    $this->criarTransferencia();
    $this->salvar();

    return $this->oTransferencia->getCodigoSlip();
  }

  /**
   * Carrega a AutorizacaoSolicitacaoRepasse para a SolicitacaoRepasseFinanceiro
   * @throws Exception
   */
  private function carregarAutorizacao() {

    $sCampos         = " autorizacaorepasse.sequencial as sequencial ";
    $sWhere          = "solicitacaorepasse.sequencial = {$this->oSolicitacaoRepasse->getCodigo()} ";
    $oDaoAutorizacao = new cl_solicitacaorepasse();
    $sSqlAutorizacao = $oDaoAutorizacao->sql_query_autorizacao($sCampos, $sWhere);
    $rsAutorizacoes  = $oDaoAutorizacao->sql_record($sSqlAutorizacao);

    if ($oDaoAutorizacao->numrows != 1) {
      throw new Exception("A Devolu��o n�o pode ser efetuada pois a Solicita��o de Repasse informada n�o est� autorizada.");
    }

    $oAutorizacao              = db_utils::fieldsMemory($rsAutorizacoes, 0);
    $this->oAutorizacaoRepasse = AutorizacaoSolicitacaoRepasse::getInstanciaPorCodigo($oAutorizacao->sequencial);
  }

  /**
   * Valida as informa��es referentes as notas de liquida��es da devolu��o.
   * @throws Exception
   */
  private function validarNotasLiquidacao() {

    if (count($this->aNotasLiquidacao) < 1) {
      return;
    }

    $sCamposNotas  = " solicitacaorepasseempnota.empnota, empnotaele.e70_vlrliq as valor_liquidado, ";
    $sCamposNotas .= " solicitacaorepasseempnota.estornado, pagordemele.e53_vlrpag as valor_pago";
    $sWhereNotas   = " solicitacaorepasseempnota.solicitacaorepasse = {$this->oSolicitacaoRepasse->getCodigo()} ";
    $sWhereNotas  .= " and solicitacaorepasseempnota.empnota in (" . implode(",", $this->aNotasLiquidacao) . ") ";

    $oDaoNotas = new cl_solicitacaorepasseempnota();
    $sSqlNotas = $oDaoNotas->sql_query_notas($sCamposNotas, $sWhereNotas);
    $rsNotas   = $oDaoNotas->sql_record($sSqlNotas);

    if ($oDaoNotas->numrows < 1) {
      throw new Exception("A Devolu��o n�o pode ser efetuada pois as notas de liquida��o n�o foram encontradas.");
    }

    $aNotas           = db_utils::getCollectionByRecord($rsNotas);
    $nTotalValorNotas = 0;

    foreach ($aNotas as $oNota) {

      if ($oNota->estornado == 't') {
        throw new Exception("A Devolu��o n�o pode ser efetuada pois a nota de liquida��o {$oNota->empnota} j� foi estornada.");
      }

      if ($oNota->valor_pago > 0) {
        throw new Exception("A Devolu��o n�o pode ser efetuada pois a nota de liquida��o {$oNota->empnota} j� foi paga.");
      }

      $nTotalValorNotas += $oNota->valor_liquidado;
    }

    if ($nTotalValorNotas != $this->nValorDevolucao) {
      throw new Exception("A Devolu��o n�o pode ser efetuada pois o valor � devolver � diferente do valor total das notas de liquida��es selecionadas.");
    }
  }

  /**
   * Realiza as valida��es para a devolu��o de repasse financeiro.
   * @throws Exception
   */
  private function validar() {

    if ($this->nValorDevolucao <= 0) {
      throw new Exception("A Devolu��o n�o pode ser efetuada pois o Valor � Devolver informado deve ser maior que zero.");
    }

    // Verificar se o slip est� autenticado
    if (!$this->oAutorizacaoRepasse->getTransferencia()->possuiAutenticacao()) {

      $sMensagem  = "A solicita��o possui slip que n�o est� autenticado. N�o � poss�vel fazer uma Devolu��o ";
      $sMensagem .= "de Repasse para solicita��o onde o slip vinculado n�o est� autenticado.";
      throw new Exception($sMensagem);
    }

    $this->validarNotasLiquidacao();

    $nValorDisponivel = round($this->oSolicitacaoRepasse->getValor() - $this->nValorDevolvido, 2);
    if ($this->nValorDevolucao > $nValorDisponivel) {

      $nValorDisponivel = trim(db_formatar($nValorDisponivel, 'f'));
      $sMensagem  = "A Devolu��o n�o pode ser efetuada pois o Valor � Devolver informado � superior ao dispon�vel.";
      $sMensagem .= "\nO saldo � devolver dispon�vel � de {$nValorDisponivel}.";
      throw new Exception($sMensagem);
    }
  }

  /**
   * Atualiza a rela��o solicita��o x nota para devolvida e persiste a devolu��o.
   */
  private function salvar() {

    if (!empty($this->aNotasLiquidacao)) {

      $sSql = "update plugins.solicitacaorepasseempnota ";
      $sSql .= "set estornado = true ";
      $sSql .= "where empnota in(" . implode(', ', $this->aNotasLiquidacao)
               . ") and estornado is false and solicitacaorepasse = {$this->oSolicitacaoRepasse->getCodigo()} ";

      $rsUpdate = db_query($sSql);

      if ($rsUpdate == false) {
        throw new Exception("Houve um erro ao tentar realizar a devolu��o das notas de liquida��o.\nA Devolu��o n�o foi efetuada.");
      }
    }

    $oDaoDevolucao = new cl_devolucaosolicitacaorepasse();
    $oDaoDevolucao->slip               = $this->oTransferencia->getCodigoSlip();
    $oDaoDevolucao->valor              = $this->nValorDevolucao;
    $oDaoDevolucao->solicitacaorepasse = $this->oSolicitacaoRepasse->getCodigo();

    if (!$oDaoDevolucao->incluir()) {
      throw new Exception("Houve um erro ao tentar efetuar a devolu��o.\n" . $oDaoDevolucao->erro_msg);
    }
  }

  /**
   * Cria a transfer�ncia para a devolu��o.
   * @return bool true
   * @throws BusinessException
   */
  private function criarTransferencia() {

    $iCodigoHistorico = ConfiguracaoRepasseFinanceiro::getHistoricoDevolucao();
    $oPrefeitura      = InstituicaoRepository::getInstituicaoPrefeitura()->getDadosPrefeitura();
    $iCgmPrefeitura   = $oPrefeitura->getNumeroCgm();
    $oData            = new DBDate(date('Y-m-d', db_getsession('DB_datausu')));

    if (empty($iCodigoHistorico)) {

      $sMsg  = "N�o � poss�vel incluir a devolu��o de repasse financeiro, pois n�o h� Hist�rico configurado.\n";
      $sMsg .= "� necess�rio informar na configura��o do plugin o c�digo do Hist�rico para devolu��o.";
      throw new BusinessException($sMsg);
    }

    if (empty($iCgmPrefeitura)) {
      throw new BusinessException("A institui��o {$oPrefeitura->getCodigo()} n�o possui CGM vinculado. � necess�rio vincular um CGM para realizar a devolu��o de repasse.");
    }

    $oTransferencia = TransferenciaFactory::getInstance(11);
    $oTransferencia->setSituacao(1);
    $oTransferencia->setCaracteristicaPeculiarCredito('000');
    $oTransferencia->setCaracteristicaPeculiarDebito('000');
    $oTransferencia->setData($oData->getDate());
    $oTransferencia->setContaCredito($this->oSolicitacaoRepasse->getConta());
    $oTransferencia->setContaDebito($this->oAutorizacaoRepasse->getContaPagadora()->getCodigoReduzido());
    $oTransferencia->setHistorico($iCodigoHistorico);
    $oTransferencia->setValor($this->nValorDevolucao);
    $oTransferencia->setObservacao("Gerado automaticamente para a devolu��o de repasse referente a solicita��o {$this->oSolicitacaoRepasse->getCodigo()}.");
    $oTransferencia->setInstituicao($oPrefeitura->getCodigo());
    $oTransferencia->setCodigoCgm($iCgmPrefeitura);
    $oTransferencia->salvar();

    $this->oTransferencia = $oTransferencia;
    return true;
  }
}