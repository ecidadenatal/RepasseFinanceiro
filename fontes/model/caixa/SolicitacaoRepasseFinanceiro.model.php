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

class SolicitacaoRepasseFinanceiro {

  /**
   * @var integer
   */
  private $iCodigo;

  /**
   * @var Recurso
   */
  private $oUnidade;

  /**
   * @var Recurso
   */
  private $oRecurso;

  /**
   * @var
   */
  private $iAnexo;

  /**
   * @var
   */
  private $sAnexo;

  /**
   * @var float
   */
  private $nValor;

  /**
   * @var int
   */
  private $iConta;

  /**
   * @var DBDate
   */
  private $oData;

  /**
   * @var string
   */
  private $sMotivo;

  /**
   * @var array
   */
  private $aLiquidacoes = array();

  /**
   * @var boolean
   */
  private $lLiquidacoesCarregadas = false;

  /**
   * @var int
   */
  private $iTipo;

  /**
   * @var int
   */
  const TIPO_REPASSE = 1;

  /**
   * @var int
   */
  const TIPO_REGULARIZACAO = 2;

  /**
   * @return integer
   */
  public function getCodigo() {
    return $this->iCodigo;
  }

  /**
   * Sequencial do Recurso
   * @return Recurso
   */
  public function getRecurso() {
    return $this->oRecurso;
  }

  /**
   * Sequencial do Anexo
   * @return integer
   */
  public function getAnexo() {
    return $this->iAnexo;
  }

  /**
   * Descrição do Anexo
   * @return string
   */
  public function getAnexoDescricao() {
    return $this->sAnexo;
  }

  /**
   *
   * @return float
   */
  public function getValor() {
    return $this->nValor;
  }

  /**
   * Sequencial da conta
   * @return contaTesouraria
   */
  public function getConta() {
    return $this->iConta;
  }
  
  public function getDescricaoConta() {
  	 
  	$sDescricao = "";
  	$iReduz = $this->getConta();
  	 
  	$iAno = $this->getData()->getAno();
  	if (empty($iAno)) {
  		$iAno = db_getsession("DB_anousu");
  	}
  	 
  	$sSqlDescricaoConta = "select c60_descr
  	                         from conplano
  	                              inner join conplanoreduz on conplano.c60_codcon = conplanoreduz.c61_codcon
  	                                                      and conplano.c60_anousu = conplanoreduz.c61_anousu
  	                        where conplanoreduz.c61_anousu = {$iAno}
  	                          and conplanoreduz.c61_reduz = {$iReduz}";
  	$rsDescricaoConta = db_query($sSqlDescricaoConta);
  	if (pg_num_rows($rsDescricaoConta) > 0) {
  		$sDescricao = db_utils::fieldsMemory($rsDescricaoConta, 0)->c60_descr;
  	}
  	 
  	return $sDescricao;
  	 
  }  

  /**
   *
   * @return DBDate
   */
  public function getData() {
    return $this->oData;
  }

  /**
   *
   * @return string
   */
  public function getMotivo() {
    return $this->sMotivo;
  }

  /**
   *
   * @param integer $iCodigo
   */
  public function setCodigo($iCodigo) {
    $this->iCodigo = $iCodigo;
  }

  /**
   *
   * @param Recurso $oRecurso
   */
  public function setRecurso(Recurso $oRecurso) {
    $this->oRecurso = $oRecurso;
  }

  /**
   *
   * @param integer $iAnexo
   */
  public function setAnexo($iAnexo) {
    $this->iAnexo = $iAnexo;
  }

  /**
   *
   * @param float $nValor
   */
  public function setValor($nValor) {
    $this->nValor = $nValor;
  }

  /**
   *
   * @param $iConta
   */
  public function setConta($iConta) {
    $this->iConta = $iConta;
  }

  /**
   *
   * @param DBDate $oData
   */
  public function setData(DBDate $oData) {
    $this->oData = $oData;
  }

  /**
   *
   * @param string $sMotivo
   */
  public function setMotivo($sMotivo) {
    $this->sMotivo = $sMotivo;
  }

  /**
   *
   * @param Unidade $oUnidade
   */
  public function setUnidade(Unidade $oUnidade) {
    $this->oUnidade = $oUnidade;
  }

  /**
   *
   * @return Unidade
   */
  public function getUnidade() {
    return $this->oUnidade;
  }

  /**
   *
   * @param int $iTipo
   */
  public function setTipo($iTipo) {
    $this->iTipo = $iTipo;
  }

  /**
   * @return int
   */
  public function getTipo() {
    return $this->iTipo;
  }

  public function getNotasLiquidacao() {

    if (!$this->lLiquidacoesCarregadas && $this->iCodigo !== null) {

      /**
       * Carrega as liquidações associadas
       */
      $oDaoSolicitacaoLiquidacao = new cl_solicitacaorepasseempnota();
      $sWhere          = " solicitacaorepasse = {$this->iCodigo} ";
      $sSqlLiquidacoes = $oDaoSolicitacaoLiquidacao->sql_query(null, null, null, $sWhere);
      $rsLiquidacoes   = $oDaoSolicitacaoLiquidacao->sql_record($sSqlLiquidacoes);
      $iNumrows        = $oDaoSolicitacaoLiquidacao->numrows;

      if ($iNumrows > 0) {

        for ($iIndice = 0; $iIndice < $iNumrows; $iIndice++) {

          $oNotaAssociada   = db_utils::fieldsMemory($rsLiquidacoes, $iIndice);
          $oNotaSolicitacao = new NotaSolicitacaoRepasseFinanceiro($this);
          $oNotaSolicitacao->setCodigoNotaLiquidacao($oNotaAssociada->empnota);

          $this->aLiquidacoes[] = $oNotaSolicitacao;
        }
      }

      $this->lLiquidacoesCarregadas = true;
    }

    return $this->aLiquidacoes;
  }

  /**
   * @param array $aLiquidacoes
   */
  public function removerNotasLiquidacao() {

    $this->lLiquidacoesCarregadas = true;
    $this->aLiquidacoes = array();
  }

  public function adicionarNotaLiquidacao(NotaSolicitacaoRepasseFinanceiro $oNotaSolicitacao) {
    $this->aLiquidacoes[] = $oNotaSolicitacao;
  }

  public function __construct($iCodigo = null) {

    if ($iCodigo !== null) {

      $oDaoSolicitacao = new cl_solicitacaorepasse;
      $sSql            = $oDaoSolicitacao->sql_query($iCodigo);
      $rsSolicitacao   = $oDaoSolicitacao->sql_record($sSql);

      if ($oDaoSolicitacao->numrows > 0) {
        $oSolicitacao = db_utils::fieldsMemory($rsSolicitacao, 0);
      } else {
        throw new Exception("Solicitação de Repasse Financeiro não encontrada.");
      }

      /**
       * Carrega os dados da solicitação
       */
      $this->iCodigo  = $oSolicitacao->sequencial;
      $this->iTipo    = $oSolicitacao->tipo;
      $this->oUnidade = new Unidade($oSolicitacao->unidade_anousu, $oSolicitacao->unidade_orgao, $oSolicitacao->unidade_codigo);
      $this->oRecurso = new Recurso($oSolicitacao->recurso);
      $this->iAnexo   = $oSolicitacao->anexo;
      $this->nValor   = $oSolicitacao->valor;
      $this->iConta      = $oSolicitacao->conta;
      $this->oData    = new DBDate($oSolicitacao->data);
      $this->sMotivo  = $oSolicitacao->motivo;

      $oDaoAnexo = new cl_ppasubtitulolocalizadorgasto();
      $rsAnexo = $oDaoAnexo->sql_record($oDaoAnexo->sql_query($this->iAnexo));

      if ($oDaoAnexo->numrows > 0) {
        $oAnexo = db_utils::fieldsMemory($rsAnexo, 0);
        $this->sAnexo = $oAnexo->o11_descricao;
      }
    }
  }

  /**
   * Remove todas as liquidações associadas
   */
  private function desassociarLiquidacoes() {

    /**
     * Carrega as notas de liquidação no objeto antes de apagar do banco de dados
     */
    $this->getNotasLiquidacao();

    $oDaoSolicitacaoLiquidacao = new cl_solicitacaorepasseempnota;
    $oDaoSolicitacaoLiquidacao->excluir(null, " solicitacaorepasse = {$this->iCodigo} ");

    if ($oDaoSolicitacaoLiquidacao->erro_status == "0") {

      $sMensagemErro  = "Não foi possível desassociar as Liquidações da Solicitação de Repasse Financeiro.\n";
      $sMensagemErro .= str_replace("\\n", "\n", $oDaoSolicitacaoLiquidacao->erro_msg);
      throw new Exception($sMensagemErro);
    }
  }

  /**
   * Associa as liquidações a solicitação
   */
  private function associarLiquidacoes() {

    if (count($this->getNotasLiquidacao()) === 0) {
      return;
    }

    $oDaoSolicitacaoLiquidacao = new cl_solicitacaorepasseempnota;

    foreach ($this->getNotasLiquidacao() as $oNotaSolicitacao) {

      $iNota = $oNotaSolicitacao->getNotaLiquidacao()->getCodigoNota();

      if (self::notaTemSolicitacao($iNota)) {
        throw new BusinessException("A Nota de Liquidação {$iNota} já está associada a uma Solicitação de Repasse Financeiro.");
      }

      $oDaoSolicitacaoLiquidacao->sequencial         = null;
      $oDaoSolicitacaoLiquidacao->solicitacaorepasse = $this->iCodigo;
      $oDaoSolicitacaoLiquidacao->empnota            = $iNota;
      $oDaoSolicitacaoLiquidacao->estornado          = $oNotaSolicitacao->getEstornado() ? 'true' : 'false';
      $oDaoSolicitacaoLiquidacao->incluir();

      if ($oDaoSolicitacaoLiquidacao->erro_status == "0") {

        $sMensagemErro  = "Não foi possível associar as Liquidações a Solicitação de Repasse Financeiro.\n";
        $sMensagemErro .= str_replace("\\n", "\n", $oDaoSolicitacaoLiquidacao->erro_msg);
        throw new Exception($sMensagemErro);
      }
    }
  }

  /**
   * Retorna verdadeiro se a Nota de Liquidação estiver associada
   * a uma Solicitação de Repasse Financeiro.
   *
   * @param  integer $iNota Código da Nota de Liquidação
   * @return boolean
   */
  public static function notaTemSolicitacao($iNota) {

    $oDaoSolicitacaoLiquidacao = new cl_solicitacaorepasseempnota;
    $sWhere  = " solicitacaorepasseempnota.empnota = {$iNota} ";
    $sWhere .= " and solicitacaorepasseempnota.estornado = false ";
    $sSqlNotaAssociada = $oDaoSolicitacaoLiquidacao->sql_query(null, 'count(*) as count', null, $sWhere);
    $rsNotaAssociada   = $oDaoSolicitacaoLiquidacao->sql_record($sSqlNotaAssociada);

    if (db_utils::fieldsMemory($rsNotaAssociada, 0)->count > 0) {
      return true;
    }

    return false;
  }

  /**
   * Retorna verdadeiro se a Solicitação de Repasse estiver
   * vinculada a uma Autorização.
   *
   * @param  integer $iSolicitacao Código da Solicitação de Repasse
   * @return boolean
   */
  public static function solicitacaoTemAutorizacao($iSolicitacao) {

    $oDaoSolicitacao = new cl_solicitacaorepasse;
    $sWhere = " solicitacaorepasse.sequencial = {$iSolicitacao}";
    $sSql   = $oDaoSolicitacao->sql_query_autorizacao('count(*) as count', $sWhere);
    $rsSolicitacaoAutorizacao = $oDaoSolicitacao->sql_record($sSql);

    if (db_utils::fieldsMemory($rsSolicitacaoAutorizacao, 0)->count > 0) {
      return true;
    }

    return false;
  }

  /**
   * Verifica se existe alguma dotação para a combinação de Órgão / Unidade / Recurso / Anexo
   *
   * @return boolean
   */
  private function temDotacao() {

    $aWhere = array(
      "o58_orgao = {$this->oUnidade->getOrgao()->getCodigoOrgao()}",
      "o58_unidade = {$this->oUnidade->getCodigoUnidade()}",
      "o58_codigo = {$this->oRecurso->getCodigo()}",
      "o58_localizadorgastos = {$this->iAnexo}",
      "o58_anousu = {$this->oData->getAno()}",
    );

    $sWhere      = implode(' and ', $aWhere);
    $oDaoDotacao = new cl_orcdotacao;
    $sSql        = $oDaoDotacao->sql_query(null, null, 'count(*) as total', null, $sWhere);
    $rsResultado = db_query($sSql);

    if (!$rsResultado) {
      throw new DBException("Não foi possível verificar as dotações.");
    }

    return db_utils::fieldsMemory($rsResultado, 0)->total;
  }

  public function salvar() {

    if (!db_utils::inTransaction()) {
      throw new DBException("Sem transação com banco de dados");
    }

    if (!$this->temDotacao() && $this->iAnexo != 5) {

      $sMensagem  = "Não é possível cadastrar Solicitação de Repasse, ";
      $sMensagem .= "pois não existe Dotação cadastrada para o Órgão, Unidade, Recurso e Anexo informados.";
      throw new BusinessException($sMensagem);
    }

    $oDaoSolicitacao = new cl_solicitacaorepasse;

    $oDaoSolicitacao->sequencial     = $this->iCodigo;
    $oDaoSolicitacao->tipo           = $this->iTipo;
    $oDaoSolicitacao->unidade_anousu = $this->oUnidade->getAno();
    $oDaoSolicitacao->unidade_orgao  = $this->oUnidade->getOrgao()->getCodigoOrgao();
    $oDaoSolicitacao->unidade_codigo = $this->oUnidade->getCodigoUnidade();
    $oDaoSolicitacao->recurso        = $this->oRecurso->getCodigo();
    $oDaoSolicitacao->anexo          = $this->iAnexo;
    $oDaoSolicitacao->valor          = $this->nValor;
    $oDaoSolicitacao->conta          = $this->iConta;
    $oDaoSolicitacao->data           = $this->oData->getDate();
    $oDaoSolicitacao->motivo         = $this->sMotivo;

    if($this->iCodigo === null) {

      $oDaoSolicitacao->incluir();
      $this->iCodigo = $oDaoSolicitacao->sequencial;
    } else {

      if (self::solicitacaoTemAutorizacao($this->iCodigo)) {
        throw new BusinessException("A Solicitação de Repasse já está autorizada e não pode ser alterada.");
      }

      $oDaoSolicitacao->alterar();
      $this->desassociarLiquidacoes();
    }

    if ($oDaoSolicitacao->erro_status == "0") {

      $sMensagemErro  = "Não foi possível salvar a Solicitação de Repasse Financeiro.\n";
      $sMensagemErro .= str_replace("\\n", "\n", $oDaoSolicitacao->erro_msg);
      throw new Exception($sMensagemErro);
    }

    $this->associarLiquidacoes();

    return true;
  }

  public function excluir() {

    if (!db_utils::inTransaction()) {
      throw new DBException("Sem transação com banco de dados");
    }

    if ($this->iCodigo === null) {
      throw new Exception("Solicitação de Repasse Financeiro não carregada. Não foi possível excluir o registro.");
    }

    if (self::solicitacaoTemAutorizacao($this->iCodigo)) {
      throw new BusinessException("A Solicitação de Repasse já está autorizada e não pode ser excluída.");
    }

    $this->desassociarLiquidacoes();
    $oDaoSolicitacao = new cl_solicitacaorepasse;
    $oDaoSolicitacao->excluir($this->iCodigo);
    $this->iCodigo = null;
  }

  /**
   * Busca a Autorização de Repasse desta Solicitação, se houver.
   * @return AutorizacaoSolicitacaoRepasse
   * @throws Exception
   */
  public function buscarAutorizacao() {

    if (empty($this->iCodigo)) {
      throw new Exception("O Código da Solicitação de Repasse Financeiro não foi informado.");
    }

    /**
     * Busca a Autorização, desde que não tenha nenhuma Devolução
     */
    $sCampos = " autorizacaorepasse.sequencial as sequencial ";
    $sNotExists  = "select 1 from plugins.devolucaosolicitacaorepasse ";
    $sNotExists .= "where devolucaosolicitacaorepasse.solicitacaorepasse = solicitacaorepasse.sequencial";
    $sWhere = "solicitacaorepasse.sequencial = {$this->iCodigo} and not exists({$sNotExists})";
    $oDaoAutorizacao = new cl_solicitacaorepasse;
    $sSqlAutorizacao = $oDaoAutorizacao->sql_query_autorizacao($sCampos, $sWhere);
    $rsAutorizacoes  = $oDaoAutorizacao->sql_record($sSqlAutorizacao);

    if ($oDaoAutorizacao->numrows < 1) {
      throw new Exception("Não foi encontrada Autorização para a Solicitação de Repasse informada.");
    }

    $oStdDados    = db_utils::fieldsMemory($rsAutorizacoes, 0);
    $oAutorizacao = AutorizacaoSolicitacaoRepasse::getInstanciaPorCodigo($oStdDados->sequencial);

    return $oAutorizacao;
  }

}
