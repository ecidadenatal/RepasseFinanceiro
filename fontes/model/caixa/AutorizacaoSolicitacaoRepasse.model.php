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
class AutorizacaoSolicitacaoRepasse {

  /**
   * @type integer
   */
  private $iCodigo;

  /**
   * @type SolicitacaoRepasseFinanceiro
   */
  private $oSolicitacaoRepasse = null;


  /**
   * @type Transferencia
   */
  private $oTransferencia = null;

  /**
   * @type DBDate
   */
  private $dtAutorizacao;

  /**
   * @type contaTesouraria
   */
  private $oContaPagadora;

  /**
   * @type Instituicao
   */
  private $oInstituicao;

  /**
   * @param $iCodigo
   */
  public function setCodigo($iCodigo) {
    $this->iCodigo = $iCodigo;
  }

  /**
   * @return int
   */
  public function getCodigo() {
    return $this->iCodigo;
  }

  public function setSolicitacao(SolicitacaoRepasseFinanceiro $oSolicitacao) {
    $this->oSolicitacaoRepasse = $oSolicitacao;
  }

  public function getSolicitacao() {
    return $this->oSolicitacaoRepasse;
  }

  /**
   * @param Transferencia $oTransferencia
   */
  public function setTransferencia(Transferencia $oTransferencia) {
    $this->oTransferencia = $oTransferencia;
  }

  /**
   * @return Transferencia
   */
  public function getTransferencia() {
    return $this->oTransferencia;
  }

  /**
   * @param Instituicao $oInstituicao
   */
  public function setInstituicao(Instituicao $oInstituicao) {
    $this->oInstituicao = $oInstituicao;
  }

  /**
   * @param contaTesouraria $oContaPagadora
   */
  public function setContaPagadora(contaTesouraria $oContaPagadora) {
    $this->oContaPagadora = $oContaPagadora;
  }

  /**
   * @return contaTesouraria
   */
  public function getContaPagadora() {
    return $this->oContaPagadora;
  }

  /**
   * @param DBDate $oData
   */
  public function setData(DBDate $oData) {
    $this->dtAutorizacao = $oData;
  }

  /**
   * @throws Exception
   */
  public function autorizar() {

    if ($this->oSolicitacaoRepasse->getConta()->getCodigoConta() == $this->oContaPagadora->getCodigoConta()) {
      throw new Exception("A solicita��o {$this->oSolicitacaoRepasse->getCodigo()} possui Conta Pagadora igual a Conta Destino do repasse. N�o � poss�vel incluir uma Autoriza��o com contas iguais.");
    }

    if ($this->getSolicitacao()->getTipo() == SolicitacaoRepasseFinanceiro::TIPO_REPASSE) {
      $this->criarTransferencia();
    }

    $oDaoAutorizar = new cl_autorizacaorepasse();
    $oDaoAutorizar->sequencial         = null;
    $oDaoAutorizar->slip               = is_null($this->oTransferencia) ? null : $this->oTransferencia->getCodigoSlip();
    $oDaoAutorizar->data               = $this->dtAutorizacao->getDate(DBDate::DATA_EN);
    $oDaoAutorizar->solicitacaorepasse = $this->oSolicitacaoRepasse->getCodigo();
    $oDaoAutorizar->incluir(null);
    if ($oDaoAutorizar->erro_status == "0") {
      throw new Exception("N�o foi poss�vel gerar autoriza��o para a solicita��o de repasse {$this->oSolicitacaoRepasse->getCodigo()}.");
    }
    $this->iCodigo = $oDaoAutorizar->sequencial;
  }

  /**
   * Realiza o cancelamento da Autoriza��o de Repasse Financeiro cancelando, tamb�m, o slip v�nculado.
   * @throws Exception
   */
  public function cancelar() {

    if(!db_utils::inTransaction()) {

      $sMensagem = "O Cancelamento de Autoriza��o de Solicita��o de Repasse deve ser efetuado dentro de uma transa��o.";
      throw new Exception($sMensagem);
    }

    if (empty($this->iCodigo)) {

      $sMensagem  = "O c�digo da Autoriza��o de Solicita��o de Repasse n�o foi informado. ";
      $sMensagem .= "O cancelamento foi abortado.";
      throw new Exception($sMensagem);
    }

    $oDaoCancelar             = new cl_autorizacaorepasse();
    $oDaoCancelar->sequencial = $this->iCodigo;

    if(!$oDaoCancelar->excluir("{$this->iCodigo}")) {

      $sMensagem  = "A Solicita��o de Repasse Financeiro n�o pode ser cancelada.";
      throw new Exception($sMensagem);
    }

    if ($this->getSolicitacao()->getTipo() == SolicitacaoRepasseFinanceiro::TIPO_REPASSE) {

      if ($this->oTransferencia == null) {

        $sMensagem  = "O slip v�nculado a Autoriza��o de Repasse Financeiro n�o foi carregado. ";
        $sMensagem .= "O cancelamento foi abortado. ";
        throw new Exception($sMensagem);
      }

      if (!$this->getTransferencia()->excluir()) {

        $sMensagem  = "A Autoriza��o de Repasse Financeiro n�o pode ser cancelada. ";
        $sMensagem .= "Houve um problema na exclus�o do slip.";
        throw new Exception($sMensagem);
      }
    }
  }

  /**
   * Cria um SLIP do tipo DDO (Dep�sito de Diversar Origens)
   * @return bool
   * @throws Exception
   */
  private function criarTransferencia() {

    $sCNPJ = $this->oSolicitacaoRepasse->getUnidade()->getCnpj();
    $sCNPJ = trim($sCNPJ);
    if (empty($sCNPJ)) {

      $iSolicitacao = $this->getSolicitacao()->getCodigo();
      $sMsg         = "A solicita��o {$iSolicitacao} possui Unidade com CNPJ sem CGM cadastrado.\n";
      $sMsg        .= "� necess�rio cadastrar CGM com o mesmo CNPJ da Unidade para realizar autoriza��o de repasse.";
      throw new Exception($sMsg);
    }

    $oCGM = CgmFactory::getInstanceByCnpjCpf($sCNPJ);
    if (!$oCGM) {
      throw new Exception("� necess�rio cadastrar CGM com o mesmo CNPJ da Unidade para realizar autoriza��o de repasse.");
    }

    $iCodigoHistorico = ConfiguracaoRepasseFinanceiro::getHistorico();
    $iCodigoHistorico = trim($iCodigoHistorico);
    if (empty($iCodigoHistorico)) {

      $sMsg  = "N�o � poss�vel incluir autoriza��o de repasse financeiro, pois n�o h� Hist�rico configurado.\n";
      $sMsg .= "� necess�rio informar na configura��o do plugin o c�digo do Hist�rico.";
      throw new Exception($sMsg);
    }

    $oTransferencia = TransferenciaFactory::getInstance(13);
    $oTransferencia->setSituacao(1);
    $oTransferencia->setCaracteristicaPeculiarCredito('000');
    $oTransferencia->setCaracteristicaPeculiarDebito('000');
    $oTransferencia->setData($this->dtAutorizacao->getDate());
    $oTransferencia->setContaCredito($this->oContaPagadora->getCodigoReduzido());
    $oTransferencia->setContaDebito($this->oSolicitacaoRepasse->getConta()->getCodigoReduzido());
    $oTransferencia->setHistorico($iCodigoHistorico);
    $oTransferencia->setValor($this->oSolicitacaoRepasse->getValor());
    $oTransferencia->setObservacao("Gerado automaticamente para a autoriza��o de repasse referente a solicita��o {$this->oSolicitacaoRepasse->getCodigo()}.");
    $oTransferencia->setInstituicao($this->oInstituicao->getCodigo());
    $oTransferencia->setCodigoCgm($oCGM->getCodigo());
    $oTransferencia->salvar();

    $this->oTransferencia = $oTransferencia;
    return true;
  }

  /**
   * @param $iCodigo
   * @return AutorizacaoSolicitacaoRepasse
   */
  public static function getInstanciaPorCodigo($iCodigo) {

    $oDaoAutorizacao      = new cl_autorizacaorepasse();
    $sSqlBuscaSolicitacao = $oDaoAutorizacao->sql_query_file($iCodigo);
    $rsBuscaSolicitacao   = db_query($sSqlBuscaSolicitacao);

    if (pg_num_rows($rsBuscaSolicitacao) == 0) {
      return new AutorizacaoSolicitacaoRepasse();
    }

    $oStdDados = db_utils::fieldsMemory($rsBuscaSolicitacao, 0);
    $oAutorizacaoRepasse = new AutorizacaoSolicitacaoRepasse();
    $oAutorizacaoRepasse->setCodigo($oStdDados->sequencial);
    $oAutorizacaoRepasse->setSolicitacao(new SolicitacaoRepasseFinanceiro($oStdDados->solicitacaorepasse));

    if ($oAutorizacaoRepasse->getSolicitacao()->getTipo() == SolicitacaoRepasseFinanceiro::TIPO_REPASSE) {

      $oTransferencia = TransferenciaFactory::getInstance(null, $oStdDados->slip);
      $oAutorizacaoRepasse->setTransferencia($oTransferencia);
      $oAutorizacaoRepasse->setContaPagadora(new contaTesouraria($oTransferencia->getContaCredito()));
    }

    return $oAutorizacaoRepasse;
  }
}
