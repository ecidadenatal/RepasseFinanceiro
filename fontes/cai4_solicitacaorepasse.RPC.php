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

require_once "libs/db_stdlib.php";
require_once "libs/db_conecta_plugin.php";
require_once "libs/db_sessoes.php";
require_once "dbforms/db_funcoes.php";

$oParam            = json_decode(str_replace("\\","",$_POST["json"]));
$oRetorno          = new stdClass();
$oRetorno->erro    = false;
$oRetorno->message = '';

try {

  db_inicio_transacao();

  switch ($oParam->exec) {

    case "salvarSolicitacao":

      $lPossuiNotasLancadas = count($oParam->aNotas) > 0;

      $oParam->iTipo = intval($oParam->iTipo);
      if (ConfiguracaoRepasseFinanceiro::exigeLiquidacao($oParam->iUnidadeOrgao, $oParam->iUnidade, $oParam->iRecurso, $oParam->iAnexo) && !$lPossuiNotasLancadas && $oParam->iTipo == SolicitacaoRepasseFinanceiro::TIPO_REPASSE) {

        $sMensagem = "Para o Órgão {$oParam->iUnidadeOrgao}, Unidade {$oParam->iUnidade}, Recurso {$oParam->iRecurso} e Anexo {$oParam->iAnexo} é necessário informar uma nota de liquidação.";
        throw new Exception($sMensagem);
      }

      $oUnidade = new Unidade($oParam->iUnidadeExercicio, $oParam->iUnidadeOrgao, $oParam->iUnidade);
      $oRecurso = new Recurso($oParam->iRecurso);
      $oContaTesouraria = new contaTesouraria($oParam->iContaDestino);
      $oData = new DBDate($oParam->sData);

      $iSolicitacao = !empty($oParam->iSolicitacao) ? $oParam->iSolicitacao : null;
      $oSolicitacaoRepasse = new SolicitacaoRepasseFinanceiro($iSolicitacao);
      $oSolicitacaoRepasse->setUnidade($oUnidade);
      $oSolicitacaoRepasse->setTipo($oParam->iTipo);
      $oSolicitacaoRepasse->setRecurso($oRecurso);
      $oSolicitacaoRepasse->setConta($oContaTesouraria);
      $oSolicitacaoRepasse->setAnexo($oParam->iAnexo);
      $oSolicitacaoRepasse->setValor($oParam->nValor);
      $oSolicitacaoRepasse->setMotivo(addslashes(db_stdClass::normalizeStringJson($oParam->sMotivo)));
      $oSolicitacaoRepasse->setData($oData);

      $oSolicitacaoRepasse->removerNotasLiquidacao();

      foreach ($oParam->aNotas as $iNotaLiquidacao) {

        $oSolicitacaoNota = new NotaSolicitacaoRepasseFinanceiro($oSolicitacaoRepasse);
        $oSolicitacaoNota->setCodigoNotaLiquidacao($iNotaLiquidacao);

        $oSolicitacaoRepasse->adicionarNotaLiquidacao($oSolicitacaoNota);
      }

      $oSolicitacaoRepasse->salvar();

      $oRetorno->iSolicitacao = $oSolicitacaoRepasse->getCodigo();

      break;

    case "excluirSolicitacao":

      if (empty($oParam->iSolicitacao)) {
        throw new Exception("Solicitação de Repasse não informada.");
      }

      $oSolicitacaoRepasse = new SolicitacaoRepasseFinanceiro($oParam->iSolicitacao);
      $oSolicitacaoRepasse->excluir();

      break;

    case "getDadosSolicitacao":

      if (empty($oParam->iSolicitacao)) {
        throw new Exception("Solicitação de Repasse não informada.");
      }

      $oSolicitacaoRepasse = new SolicitacaoRepasseFinanceiro($oParam->iSolicitacao);

      $oDados = new stdClass();
      $oDados->iOrgao   = $oSolicitacaoRepasse->getUnidade()->getOrgao()->getCodigoOrgao();
      $oDados->sOrgao   = urlencode($oSolicitacaoRepasse->getUnidade()->getOrgao()->getDescricao());
      $oDados->iUnidade = $oSolicitacaoRepasse->getUnidade()->getCodigoUnidade();
      $oDados->sUnidade = urlencode($oSolicitacaoRepasse->getUnidade()->getDescricao());
      $oDados->iTipo    = $oSolicitacaoRepasse->getTipo();
      $oDados->iUnidadeExercicio = $oSolicitacaoRepasse->getUnidade()->getAno();
      $oDados->iRecurso = $oSolicitacaoRepasse->getRecurso()->getCodigo();
      $oDados->sRecurso = urlencode($oSolicitacaoRepasse->getRecurso()->getDescricao());
      $oDados->iAnexo = $oSolicitacaoRepasse->getAnexo();
      $oDados->sAnexo = urlencode($oSolicitacaoRepasse->getAnexoDescricao());
      $oDados->iContaDestino = $oSolicitacaoRepasse->getConta()->getCodigoConta();
      $oDados->sContaDestino = urlencode($oSolicitacaoRepasse->getConta()->getDescricao());
      $oDados->sData = $oSolicitacaoRepasse->getData()->getDate(DBDate::DATA_PTBR);
      $oDados->nValor = $oSolicitacaoRepasse->getValor();
      $oDados->sMotivo = urlencode($oSolicitacaoRepasse->getMotivo());

      $aLiquidacoes = array();

      foreach ($oSolicitacaoRepasse->getNotasLiquidacao() as $oNotaLiquidacao) {

        $aLiquidacoes[] = array(
          'iEmpenho' => $oNotaLiquidacao->getNotaliquidacao()->getEmpenho()->getCodigo() . '/' .
                        $oNotaLiquidacao->getNotaliquidacao()->getEmpenho()->getAnoUso(),
          'iNota'      => $oNotaLiquidacao->getCodigoNotaLiquidacao(),
          'sCredor'    => urlencode($oNotaLiquidacao->getNotaliquidacao()->getEmpenho()->getFornecedor()->getNome()),
          'nValor'     => $oNotaLiquidacao->getNotaliquidacao()->getValorNota()
        );
      }

      $oDados->aLiquidacoes = $aLiquidacoes;
      $oRetorno->dados = $oDados;

      break;

    case 'getDadosSolicitacaoDevolucao':


      if (empty($oParam->iSolicitacao)) {
        throw new Exception("A Solicitação de Repasse não foi informada.");
      }

      //Pega a solicitação e valor desta.
      $oSolicitacaoRepasse = new SolicitacaoRepasseFinanceiro($oParam->iSolicitacao);
      $oRetorno->nValor    = $oSolicitacaoRepasse->getValor();

      // Busca o valor total já devolvido da solicitação.
      $sCampos = " COALESCE(sum(devolucaosolicitacaorepasse.valor), 0) as valor_devolvido ";
      $sWhere  = " devolucaosolicitacaorepasse.solicitacaorepasse = {$oParam->iSolicitacao} ";

      $oDaoDevolucoes = new cl_devolucaosolicitacaorepasse();
      $sSqlDevolucoes = $oDaoDevolucoes->sql_query(null, $sCampos, null, $sWhere);
      $rsDevolucoes   = $oDaoDevolucoes->sql_record($sSqlDevolucoes);

      if ($oDaoDevolucoes->numrows != 1) {
        throw new Exception("Não foi possível verificar o total devolvido da Solicitação de Repasse selecionada.");
      }

      $oDevolucoes               = db_utils::fieldsMemory($rsDevolucoes, 0);
      $oRetorno->nValorDevolvido = $oDevolucoes->valor_devolvido;


      // Busca as informações das notas de liquidação da solicitação.
      $sCampos  = " e60_codemp||'/'||e60_anousu as empenho, e69_codnota as nota, e70_vlrliq as valor_liquidado, ";
      $sCampos .= " solicitacaorepasseempnota.estornado as estornado, e53_vlrpag as valor_pago";
      $sWhere   = "solicitacaorepasseempnota.solicitacaorepasse = {$oParam->iSolicitacao} ";

      $oDaoNotas = new cl_solicitacaorepasseempnota();
      $sSqlNotas = $oDaoNotas->sql_query_notas($sCampos, $sWhere);
      $rsNotas   = $oDaoNotas->sql_record($sSqlNotas);

      $aNotas = array();
      if ($oDaoNotas->numrows > 0) {

        $aNotasBuscadas = db_utils::getCollectionByRecord($rsNotas);

        foreach ($aNotasBuscadas as $oNotaBuscada) {

          $oNota = new stdClass();
          $oNota->iEmpenho   = $oNotaBuscada->empenho;
          $oNota->iNota      = $oNotaBuscada->nota;
          $oNota->nValor     = $oNotaBuscada->valor_liquidado;
          $oNota->lEstornado = $oNotaBuscada->estornado;
          $oNota->nValorPago = $oNotaBuscada->valor_pago;
          $aNotas[]          = $oNota;
        }
      }
      $oRetorno->aLiquidacoes = $aNotas;

      break;

    case 'verificaSaldoConta':

      $oSolicitacaoRepasse = new SolicitacaoRepasseFinanceiro($oParam->iSolicitacao);

      $iInstituicao = db_getsession('DB_instit');
      $iDataUsu     = db_getsession('DB_datausu');
      $iConta       = $oSolicitacaoRepasse->getConta()->getCodigoReduzido();
      $oData        = new DBDate(date('Y-m-d', $iDataUsu));

      $oSaldo                = contaTesouraria::getSaldoTesouraria($iConta, $oData, $oData, $iInstituicao);
      $oRetorno->iConta      = $iConta;
      $oRetorno->nSaldoConta = $oSaldo->nSaldoFinal;
      break;

    default:
      throw new Exception("Método inválido.");
      break;
  }

  db_fim_transacao(false);

} catch (Exception $eErro) {

  db_fim_transacao (true);
  $oRetorno->erro  = true;
  $oRetorno->message = urlencode($eErro->getMessage());
}

echo json_encode($oRetorno);
