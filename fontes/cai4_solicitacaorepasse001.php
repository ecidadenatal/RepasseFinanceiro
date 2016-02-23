<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBseller Servicos de Informatica
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

require_once ("libs/db_stdlib.php");
require_once "libs/db_conecta_plugin.php";
require_once ("libs/db_sessoes.php");
require_once ("libs/db_utils.php");
require_once ("libs/db_app.utils.php");
require_once ("dbforms/db_funcoes.php");

$oGet   = db_utils::postMemory($_GET);
$iOpcao = !empty($oGet->acao) ? $oGet->acao : 1;

?>
<html xmlns="http://www.w3.org/1999/html">
<head>
  <title>DBSeller Inform&aacute;tica Ltda - P&aacute;gina Inicial</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

  <?php
  // Includes padrão
  db_app::load("scripts.js, prototype.js, strings.js, datagrid.widget.js, AjaxRequest.js");
  db_app::load("estilos.css, grid.style.css");
  ?>

  <script type="text/javascript" src="scripts/widgets/DBAbas.widget.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body-default">
<div class="container">
  <form id="formSolicitacaoRepasse">
    <input type="hidden" id="iOpcao" name="iOpcao" value="<?=$iOpcao?>" />
    <input type="hidden" id="orgao_unidade_exercicio" name="orgao_unidade_exercicio" />
    <fieldset>

      <legend><strong>Solicitação de Repasse</strong></legend>
      <table>

        <tr>
          <td class="bold">
            <label for="codigo_repasse">
              <?php db_ancora('Código:', 'buscarSolicitacao(true)', $iOpcao == 1 ? 3 : 2, null, 'solicitacao_codigo_ancora'); ?>
            </label>
          </td>
          <td>
            <?php
            db_input('codigo_repasse', 10, false, true, 'text', 3, null, null, null, '');
            ?>
          </td>
        </tr>

        <tr>
          <td class="bold">
            <label for="tipo_repasse">Tipo</label>
          </td>
          <td>
            <?php
            db_select("tipo_repasse", array(SolicitacaoRepasseFinanceiro::TIPO_REPASSE => 'Repasse', SolicitacaoRepasseFinanceiro::TIPO_REGULARIZACAO => 'Regularização'), true, 1, "style='width: 95px;'");
            ?>
          </td>
        </tr>

        <tr>
          <td class="bold">
            <label for="orgao_numero"><?php db_ancora('Órgão:', 'buscarOrgao(true)', $iOpcao, null, 'orgao_numero_ancora'); ?></label>
          </td>
          <td>
            <?php
            $Sorgao_numero = 'Órgão';
            db_input('orgao_numero', 10, 1, true, 'text', $iOpcao, 'onChange="buscarOrgao(false)"');
            db_input('orgao_descricao', 44, 0, true, 'text', 3);
            ?>
          </td>
        </tr>

        <tr>
          <td class="bold">
            <label for="unidade_numero>">
              <?php db_ancora('Unidade:', 'buscarUnidade(true)', $iOpcao, null, 'unidade_numero_ancora'); ?>
            </label>
          </td>
          <td>
            <?php
            $Sunidade_numero = 'Unidade';
            db_input('unidade_numero', 10, 1, true, 'text', $iOpcao, 'onChange="buscarUnidade(false)"');
            db_input('unidade_descricao', 44, 0, true, 'text', 3);
            ?>
          </td>
        </tr>

        <tr>
          <td class="bold">
            <label for="recurso_numero">
              <?php db_ancora('Recurso:', 'buscarRecurso(true)', $iOpcao, null, 'recurso_numero_ancora'); ?>
            </label>
          </td>
          <td>
            <?php
            $Srecurso_numero = 'Recurso';
            db_input('recurso_numero', 10, 1, true, 'text', $iOpcao, 'onChange="buscarRecurso(false)"');
            db_input('recurso_descricao', 44, 0, true, 'text', 3);
            ?>
          </td>
        </tr>

        <tr>
          <td class="bold">
            <label for="anexo_numero">
              <?php db_ancora('Anexo:', 'buscarAnexo(true)', $iOpcao, null, 'anexo_numero_ancora'); ?>
            </label>
          </td>
          <td>
            <?php
            $Sanexo_numero = 'Anexo';
            db_input('anexo_numero', 10, 1, true, 'text', $iOpcao, 'onChange="buscarAnexo(false)"');
            db_input('anexo_descricao', 44, 0, true, 'text', 3);
            ?>
          </td>
        </tr>

        <tr>
          <td class="bold">
            <label for="conta_destino_numero">
              <?php db_ancora('Conta de Destino:', 'buscarContaDestino(true)', $iOpcao, null, 'conta_destino_numero_ancora'); ?>
            </label>
          </td>
          <td>
            <?php
            $Sconta_destino_numero = "Conta de Destino";
            db_input('conta_destino_numero', 10, 1, true, 'text', $iOpcao, 'onChange="buscarContaDestino(false)"');
            db_input('conta_destino_descricao', 44, 0, true, 'text', 3);
            ?>
          </td>
        </tr>

        <tr>
          <td class="bold">
            <label for="data_repasse">Data:</label>
          </td>
          <td>
            <?php
            $Sdata_repasse = 'Data';
            db_inputdata('data_repasse', null, null, null, true, 'text', $iOpcao);
            ?>
          </td>
        </tr>

        <tr>
          <td class="bold">
            <label for="valor_repasse">Valor:</label>
          </td>
          <td>
            <?php

            $Svalor_repasse = "Valor";
            $sScripValorRepasse  = ' onFocus="this.value = js_strToFloat(this.value)" ';
            $sScripValorRepasse .= ' onBlur="this.value = js_formatar(this.value, \'f\', 2)" ';
            db_input('valor_repasse', 10, 4, true, 'text', $iOpcao, $sScripValorRepasse);
            ?>
          </td>
        </tr>

        <tr>
          <td colspan='2'>
            <fieldset>
              <legend><label for="motivo">Motivo</label></legend>
              <?php
              $Smotivo = "Motivo";
              db_textarea('motivo', 3, 40, false, true, 'text', $iOpcao, ' class="field-size-max" ');
              ?>
            </fieldset>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <fieldset>
              <legend>Liquidações</legend>
              <div id="lancadorLiquidacao" style="padding-bottom: 5px;">
                <label for="liquidacao_numero">
                  <?php db_ancora('Liquidação:', 'buscarLiquidacao(true)', $iOpcao, null, 'liquidacao_ancora'); ?>
                </label>
                <?php
                $Sliquidacao_numero = 'Liquidação';
                /* [Inicio plugin LiquidacaoCompetencia - parte1] */
                /* [Fim plugin LiquidacaoCompetencia - parte1] */
                db_input('liquidacao_numero', 10, 1, true, 'text', $iOpcao, 'onChange="buscarLiquidacao(false)"');
                db_input('empenho_codigo', 10, 0, true, 'hidden', 3);
                db_input('liquidacao_descricao', 40, 0, true, 'text', 3);
                db_input('liquidacao_valor', 10, 0, true, 'hidden', 3);
                ?>
                <input type="button" id="btnLancar" value="Lançar" onClick="lancarLiquidacao();" />
              </div>
              <div id='ctnGrid'></div>
            </fieldset>
          </td>
        </tr>
      </table>
    </fieldset>

    <?php if ($iOpcao == 3) : ?>
      <input type="button" id="btnExcluirSolicitacao" onClick="excluirSolicitacao();" value="Excluir" />
    <?php else : ?>
      <input type="button" id="btnSalvarSolicitacao" onClick="salvarSolicitacao();" value="Salvar" />
    <?php endif; ?>

    <?php if ($iOpcao != 1) : ?>
      <input type="button" onClick="buscarSolicitacao();" value="Pesquisar" />
    <?php endif; ?>
  </form>
</div>
<?php db_menu(); ?>
</body>
</html>
<script type="text/javascript">

  var aLiquidacoes = [];

  /**
   * Faz a validação dos campos de preenchimento obrigatório.
   */
  function validarCamposObrigatorios() {

    var iOrgao        = $F('orgao_numero');
    var iUnidade      = $F('unidade_numero');
    var iRecurso      = $F('recurso_numero');
    var iAnexo        = $F('anexo_numero');
    var iContaDestino = $F('conta_destino_numero');
    var sDataRepasse  = $F('data_repasse');
    var nValor        = $F('valor_repasse');
    var sMotivo       = $F('motivo');

    if (iOrgao == '') {

      alert("O campo Órgão é de preenchimento obrigatório.");
      return false;
    }

    if (iUnidade == '') {

      alert("O campo Unidade é de preenchimento obrigatório.");
      return false;
    }

    if (iRecurso == '') {

      alert("O campo Recurso é de preenchimento obrigatório.");
      return false;
    }

    if (iAnexo == '') {

      alert("O campo Anexo é de preenchimento obrigatório.");
      return false;
    }

    if (iContaDestino == '') {

      alert("O campo Conta de Destino é de preenchimento obrigatório.");
      return false;
    }

    if (sDataRepasse == '') {

      alert("O campo Data é de preenchimento obrigatório.");
      return false;
    }

    if (nValor == '' || nValor == "0,00") {

      alert("O campo Valor é de preenchimento obrigatório.");
      return false;
    }

    if (sMotivo == '') {

      alert("O campo Motivo é de preenchimento obrigatório.");
      return false;
    }

    return true;
  }

  /**
   * Função executada após exclusão de uma solicitação de repasse.
   * @param {object}  oRetorno Objeto com as informações de retorno da requisição.
   * @param {boolean} lErro    Se houve erro na exclusão.
   */
  function retornoExcluir(oRetorno, lErro) {

    if (lErro) {

      alert(oRetorno.message.urlDecode());
      return false;
    }

    alert("Solicitação de repasse excluída com sucesso.");
    limparTela();
    buscarSolicitacao(true);
  }

  /**
   * Limpa todos os campos da tela, inclusive as liquidações.
   */
  function limparTela() {

    $('codigo_repasse').value          = '';
    $('unidade_numero').value          = '';
    $('unidade_descricao').value       = '';
    $('orgao_numero').value            = '';
    $('orgao_descricao').value         = '';
    $('orgao_unidade_exercicio').value = '';
    $('recurso_numero').value          = '';
    $('recurso_descricao').value       = '';
    $('anexo_numero').value            = '';
    $('anexo_descricao').value         = '';
    $('conta_destino_numero').value    = '';
    $('conta_destino_descricao').value = '';
    $('data_repasse').value            = '';
    $('valor_repasse').value           = js_formatar('0,00', 'f');
    $('motivo').value                  = '';
    removerTodasLiquidacoes();
  }

  /**
   * Realiza a exclusão da solicitação de repasse.
   */
  function excluirSolicitacao() {

    iSolicitacaoRepasse = $F('codigo_repasse');

    var sRPC        = 'cai4_solicitacaorepasse.RPC.php';
    var fnRetorno   = retornoExcluir;
    var oParameters = {
      exec:         'excluirSolicitacao',
      iSolicitacao: iSolicitacaoRepasse
    };

    new AjaxRequest(sRPC, oParameters, fnRetorno).setMessage('Aguarde, carregando a solicitação de repasse...').execute();
  }

  /**
   * Método chamado após salvar uma solicitação de repasse.
   * @param {object}  oRetorno Objeto de resposta da requisição.
   * @param {boolean} lErro    Se ocorreu algum erro.
   */
  function retornoSalvar(oRetorno, lErro) {

    if (lErro) {

      alert(oRetorno.message.urlDecode());
      return false;
    }

    var sOperacao = "alterada";
    if ($F('iOpcao') == '1') {

      sOperacao                 = 'incluída';
      $('codigo_repasse').value = oRetorno.iSolicitacao;
      limparTela();
    }
    alert("Solicitação de repasse " +sOperacao + " com sucesso.");
  }

  /**
   * Salva a solicitação de repasse.
   */
  function salvarSolicitacao() {

    if (!validarCamposObrigatorios()) {
      return false;
    }

    //Prepara um array com os ids das notas de liquidação
    var aNotas = [];
    for (var iIndice = 0; iIndice < aLiquidacoes.length; iIndice++) {
      aNotas[iIndice] = aLiquidacoes[iIndice][1];
    }

    var iSolicitacao = '';
    if ($F('iOpcao') != '1' && $F('codigo_repasse') != '') {
      iSolicitacao = $F('codigo_repasse');
    }

    var sRPC        = 'cai4_solicitacaorepasse.RPC.php';
    var fnRetorno   = retornoSalvar;
    var oParameters = {
      exec:              'salvarSolicitacao',
      iSolicitacao:       iSolicitacao,
      iUnidade:          $F('unidade_numero'),
      iTipo:             $F('tipo_repasse'),
      iUnidadeOrgao:     $F('orgao_numero'),
      iUnidadeExercicio: $F('orgao_unidade_exercicio'),
      iRecurso:          $F('recurso_numero'),
      iAnexo:            $F('anexo_numero'),
      iContaDestino:     $F('conta_destino_numero'),
      sData:             $F('data_repasse'),
      nValor:            $F('valor_repasse').getNumber(),
      sMotivo:           encodeURIComponent(tagString($F('motivo'))),
      aNotas:            aNotas
    };

    new AjaxRequest(sRPC, oParameters, fnRetorno).setMessage('Aguarde, salvando solicitação de repasse...').execute();
  }

  /**
   * Busca uma solicitação de repasse.
   */
  function buscarSolicitacao(lMostrar) {

    var sQuerySring = 'funcao_js=parent.retornoSolicitacao|0';
    sQuerySring    += "&lRotinaManutencao=true&iAcao="+$F('iOpcao');
    var sArquivo    = 'func_solicitacaorepasse.php';
    var sTituloTela = 'Pesquisar Solicitação de Repasse Financeiro';

    js_OpenJanelaIframe('', 'db_iframe_solicitacaorepasse', sArquivo + '?' + sQuerySring, sTituloTela, lMostrar);
  }

  /**
   *
   * @param {int} iCodigoRepasse Sequencial da Solicitação de Repasse
   */
  function retornoSolicitacao(iCodigoRepasse) {

    db_iframe_solicitacaorepasse.hide();
    limparTela();
    $('codigo_repasse').value = iCodigoRepasse;

    if ($F('iOpcao') == '1') {
      $('iOpcao').value = '2';
    }

    carregarRepasse(iCodigoRepasse);
  }

  /**
   * Faz a busca por orgão.
   * @param {boolean} lMostrar Se deve mostrar a janela para busca ou fazer busca pela chave.
   */
  function buscarOrgao(lMostrar) {

    var sQuerySring = 'funcao_js=parent.retornoOrgao|0|2';
    var sArquivo    = 'func_orcorgao.php';
    var sTituloTela = 'Pesquisar Órgão';

    if (!lMostrar) {
      sQuerySring = 'pesquisa_chave=' + $F('orgao_numero') + '&funcao_js=parent.retornoOrgaoChave';
    }

    js_OpenJanelaIframe('', 'db_iframe_orcorgao', sArquivo +'?' +sQuerySring, sTituloTela, lMostrar);
  }

  /**
   * Faz a busca por unidade.
   * @param {boolean} lMostrar Se deve mostrar a janela para busca ou fazer busca pela chave.
   */
  function buscarUnidade(lMostrar) {

    var iOrgao = $F('orgao_numero');

    if (iOrgao == '') {

      alert("Para selecionar uma unidade, você deve primeiro informar o Órgão.");
      return false;
    }

    var sQuerySring = 'orgao=' + iOrgao + '&funcao_js=parent.retornoUnidade|2|4|0';
    var sArquivo    = 'func_orcunidade.php';
    var sTituloTela = 'Pesquisar Unidade';

    if (!lMostrar) {
      sQuerySring = 'pesquisa_chave=' + $F('unidade_numero') + '&orgao=' + iOrgao + '&funcao_js=parent.retornoUnidadeChave';
    }

    js_OpenJanelaIframe('', 'db_iframe_orcunidade', sArquivo +'?' +sQuerySring, sTituloTela, lMostrar);
  }

  /**
   * Faz a busca por recurso.
   * @param {boolean} lMostrar Se deve mostrar a janela para busca ou fazer busca pela chave.
   */
  function buscarRecurso(lMostrar) {

    var sQuerySring = 'funcao_js=parent.retornoRecurso|0|1';
    var sArquivo    = 'func_orctiporec.php';
    var sTituloTela = 'Pesquisa de Recurso';

    if (!lMostrar) {
      sQuerySring = 'pesquisa_chave=' + $F('recurso_numero') + '&funcao_js=parent.retornoRecursoChave';
    }

    js_OpenJanelaIframe('', 'db_iframe_orctiporec', sArquivo +'?' +sQuerySring, sTituloTela, lMostrar);
  }

  /**
   * Faz a busca por anexo.
   * @param {boolean} lMostrar Se deve mostrar a janela para busca ou fazer busca pela chave.
   */
  function buscarAnexo(lMostrar) {

    var sQuerySring = 'funcao_js=parent.retornoAnexo|0|2';
    var sArquivo    = 'func_ppasubtitulolocalizadorgasto.php';
    var sTituloTela = 'Pesquisa de Anexo';

    if (!lMostrar) {
      sQuerySring = 'pesquisa_chave=' + $F('anexo_numero') + '&funcao_js=parent.retornoAnexoChave';
    }

    js_OpenJanelaIframe('', 'db_iframe_ppasubtitulolocalizadorgasto', sArquivo +'?' +sQuerySring, sTituloTela, lMostrar);
  }

  /**
   * Faz a busca por conta de destino.
   * @param {boolean} lMostrar Se deve mostrar a janela para busca ou fazer busca pela chave.
   */
  function buscarContaDestino(lMostrar) {

    var sQuerySring = 'lIgnorarFiltroDespesa=true&funcao_js=parent.retornoContaDestino|0|2';
    var sArquivo    = 'func_saltes.php';
    var sTituloTela = 'Pesquisa Conta de Destino';
    if (!lMostrar) {
      sQuerySring = 'pesquisa_chave=' + $F('conta_destino_numero') + '&lIgnorarFiltroDespesa=true&funcao_js=parent.retornoContaDestinoChave';
    }

    js_OpenJanelaIframe('', 'db_iframe_saltes', sArquivo +'?' +sQuerySring, sTituloTela, lMostrar);
  }

  /**
   * Faz a busca por liquidações.
   * @param {boolean} lMostrar Se deve mostrar a janela para busca ou fazer busca pela chave.
   */
  function buscarLiquidacao(lMostrar) {

    //Precisa da unidade, recurso e anexo para realizar a busca das liquidações.
    var iUnidade = $F('unidade_numero');
    var iRecurso = $F('recurso_numero');
    var iAnexo   = $F('anexo_numero');

    if (iUnidade == '' || iRecurso == '' || iAnexo == '') {

      alert("Para selecionar uma liquidação, você deve primeiro informar a unidade, recurso e anexo.");
      return false;
    }

    var iUnidadeOrgao     = $F('orgao_numero');
    var iUnidadeExercicio = $F('orgao_unidade_exercicio');

    var sQuerySringAdicional  = 'iUnidade=' + iUnidade + '&iRecurso=' + iRecurso + '&iAnexo=' + iAnexo +
      '&iUnidadeOrgao=' + iUnidadeOrgao + '&iUnidadeExercicio=' + iUnidadeExercicio;

    /* [Inicio plugin LiquidacaoCompetencia - parte2] */
    var sQuerySring           = sQuerySringAdicional + '&funcao_js=parent.retornoLiquidacao|0|3|2|6';
    /* [Fim plugin LiquidacaoCompetencia - parte2] */
    var sArquivo              = 'func_empnotarepasse.php';
    var sTituloTela           = 'Pesquisar Liquidação';
    if (!lMostrar) {
      sQuerySring = 'pesquisa_chave=' + $F('liquidacao_numero') + '&' + sQuerySringAdicional +
      '&funcao_js=parent.retornoLiquidacao';
    }
    js_OpenJanelaIframe('', 'db_iframe_repasse', sArquivo + '?' + sQuerySring, sTituloTela, lMostrar);
  }

  /**
   * Retorno da busca por órgão usando a chave.
   * @param {string}  sDescricao
   * @param {boolean} lErro
   */
  function retornoOrgaoChave(sDescricao, lErro) {

    $('unidade_numero').value    = '';
    $('unidade_descricao').value = '';
    removerTodasLiquidacoes();
    retornoOrgao($F('orgao_numero'), sDescricao, lErro);
  }

  /**
   * Retorno da busca por unidade usando a chave.
   * @param {string}  sDescricao
   * @param {boolean} lErro
   * @param {string}  sNomeInstituicao
   * @param {int}     iInstituicao
   * @param {int}     iCodigoOrgao
   * @param {int}     iExercicio
   */
  function retornoUnidadeChave(sDescricao, lErro, sNomeInstituicao, iInstituicao, iCodigoOrgao, iExercicio) {

    if (lErro) {
      iExercicio   = '';
    }
    retornoUnidade($F('unidade_numero'), sDescricao, iExercicio, lErro);
  }

  /**
   * Retorno da busca por recurso usando a chave.
   * @param {string}  sDescricao
   * @param {boolean} lErro
   */
  function retornoRecursoChave(sDescricao, lErro) {
    retornoRecurso($F('recurso_numero'), sDescricao, lErro);
  }

  /**
   * Retorno da busca por anexo usando a chave.
   * @param {string}  sDescricao
   * @param {boolean} lErro
   */
  function retornoAnexoChave(sDescricao, lErro) {
    retornoAnexo($F('anexo_numero'), sDescricao, lErro);
  }

  /**
   * Retorno da busca por conta destino usando a chave.
   * @param {string}  sDescricao
   * @param {boolean} lErro
   */
  function retornoContaDestinoChave(sDescricao, lErro) {
    retornoContaDestino($F('conta_destino_numero'), sDescricao, lErro);
  }

  /**
   * Retorno da busca por órgão.
   * @param {int}     iCodigo
   * @param {string}  sDescricao
   * @param {boolean} lErro
   */
  function retornoOrgao(iCodigo, sDescricao, lErro) {

    //Se o valor selecionado for diferente do atual, limpa a grid de liquidações e a unidade.
    if ($('orgao_numero').value != iCodigo) {

      $('unidade_numero').value    = '';
      $('unidade_descricao').value = '';
      removerTodasLiquidacoes();
    }
    db_iframe_orcorgao.hide();
    retorno('orgao', iCodigo, sDescricao, lErro, false);
  }

  /**
   * Retorno da busca por unidade.
   * @param {int}     iCodigo
   * @param {string}  sDescricao
   * @param {int}     iCodigoOrgao
   * @param {int}     iExercicio
   * @param {boolean} lErro
   */
  function retornoUnidade(iCodigo, sDescricao, iExercicio, lErro) {

    //Se o valor selecionado for diferente do atual, limpa a grid de liquidações.
    if ($('unidade_numero').value != iCodigo || $('orgao_unidade_exercicio').value != iExercicio) {
      removerTodasLiquidacoes();
    }

    if (lErro) {
      iExercicio = '';
    }
    db_iframe_orcunidade.hide();
    retorno('unidade', iCodigo, sDescricao, lErro, false);
    $('orgao_unidade_exercicio').value = iExercicio;
  }

  /**
   * Retorno da busca por recurso.
   * @param {int}     iCodigo
   * @param {string}  sDescricao
   * @param {boolean} lErro
   */
  function retornoRecurso(iCodigo, sDescricao, lErro) {

    db_iframe_orctiporec.hide();
    retorno('recurso', iCodigo, sDescricao, lErro, true);
  }

  /**
   * Retorno da busca por anexo.
   * @param {int}     iCodigo
   * @param {string}  sDescricao
   * @param {boolean} lErro
   */
  function retornoAnexo(iCodigo, sDescricao, lErro) {

    db_iframe_ppasubtitulolocalizadorgasto.hide();
    retorno('anexo', iCodigo, sDescricao, lErro, true);
  }

  /**
   * Retorno da busca por conta de destino.
   * @param {int}     iCodigo
   * @param {string}  sDescricao
   * @param {boolean} lErro
   */
  function retornoContaDestino(iCodigo, sDescricao, lErro) {

    db_iframe_saltes.hide();
    retorno('conta_destino', iCodigo, sDescricao, lErro, false);
  }

  /**
   * Retorno da busca por liquidação.
   * @param {int}     iCodigo
   * @param {int}     iCodigoEmpenho
   * @param {string}  sDescricao
   * @param {number}  nValor
   * @param {boolean} lErro
   */
  /* [Inicio plugin LiquidacaoCompetencia - parte3] */
  function retornoLiquidacao(iCodigo, iCodigoEmpenho, sDescricao, nValor, lErro) {
  /* [Fim plugin LiquidacaoCompetencia - parte3] */
    db_iframe_repasse.hide();
    retorno('liquidacao', iCodigo, sDescricao, lErro, false);
    $('empenho_codigo').value   = iCodigoEmpenho;
    $('liquidacao_valor').value = js_formatar(nValor, 'f');
    /* [Inicio plugin LiquidacaoCompetencia - parte4] */
    /* [Fim plugin LiquidacaoCompetencia - parte4] */
  }

  /**
   * Fecha a lookup e preenche as informações de retorno.
   * @param {string}  sCampo            Nome dos campos da âncora.
   * @param {int}     iCodigo           Código referente à âncora.
   * @param {string}  sDescricao        Descrição referente à âncora.
   * @param {boolean} lErro             Se ocorreu erro.
   * @param {boolean} lLimpaLiquidacoes Se deve limpar as liquidações.
   */
  function retorno(sCampo, iCodigo, sDescricao, lErro, lLimpaLiquidacoes) {

    //Verifica se deve remover todas as liquidações.
    if (lLimpaLiquidacoes && $(sCampo + '_numero').value != iCodigo) {
      removerTodasLiquidacoes();
    }

    $(sCampo+'_numero').value = iCodigo;
    if (lErro) {
      $(sCampo+'_numero').value = '';
    }
    $(sCampo+'_descricao').value = sDescricao;
  }

  /**
   * Cria uma grid para as liquidações.
   */
  function criaGrid() {

    oGridLiquidacoes              = new DBGrid("oGridLiquidacoes");
    oGridLiquidacoes.nameInstance = 'oGridLiquidacoes';
    /* [Inicio plugin LiquidacaoCompetencia - parte5] */
    oGridLiquidacoes.setCellAlign(['center', 'center', 'left', 'right', 'center']);
    oGridLiquidacoes.setHeader(["Empenho", "Nota", 'Credor', 'Valor', 'Ações']);
    /* [Fim plugin LiquidacaoCompetencia - parte5] */
    oGridLiquidacoes.setHeight(150);
    oGridLiquidacoes.show($('ctnGrid'));
    oGridLiquidacoes.clearAll(true);
    oGridLiquidacoes.renderRows();
  }

  /**
   * Retorna a string do botão.
   * @param iIndice {int} Indice da linha para o botão.
   */
  function montaBotaoRemover(iIndice) {

    var sDisabled = "";
    if ($F('iOpcao') == '3') {
      sDisabled = "disabled='disabled'";
    }

    return "<input type='button' " + sDisabled + " value='Remover' onClick='removerLiquidacao(" + iIndice + ")' />"
  }

  /**
   * Atualiza o campo valor_repasse com o valor passado, mudando também seu estado.
   * @param {number}  nValor      Valor para ser setado no campo.
   * @param {boolean} lDesabilita Se deve desabilitar ou liberar o campo.
   */
  function atualizaValorRepasse(nValor, lDesabilita) {

    var oValorRepasse      = $('valor_repasse');
    oValorRepasse.readOnly = false;
    oValorRepasse.value    = js_formatar(nValor, 'f');
    oValorRepasse.readOnly = lDesabilita;
    oValorRepasse.disabled = lDesabilita;

  }

  /**
   * Remove todas as liquidações da grid.
   */
  function removerTodasLiquidacoes() {

    aLiquidacoes = [];
    oGridLiquidacoes.clearAll(true);
    oGridLiquidacoes.renderRows();
    atualizaValorRepasse("0,00", false);
  }

  /**
   * Remove uma liquidação da grid pelo seu indice e remonta o array e a grid.
   * @param {int} iIndice Indice da linha a ser removida.
   */
  function removerLiquidacao(iIndice) {

    var iContador       = 0;
    var nValorLinha     = 0;
    var nTotalRepasse   = 0;
    var aLiquidacaoNova = [];

    /**
     * Monta um novo array pulando o item removido, retotaliza os valores e reimprime a grid.
     */
    oGridLiquidacoes.clearAll(true);
    for (var iLinha = 0; iLinha < aLiquidacoes.length; iLinha++) {

      if (iIndice == iLinha) {
        continue;
      }

      nValorLinha                   = js_strToFloat(aLiquidacoes[iLinha][3]);
      nTotalRepasse                += nValorLinha;
      aLiquidacaoNova[iContador]    = aLiquidacoes[iLinha];
      aLiquidacaoNova[iContador][4] = montaBotaoRemover(iContador);

      oGridLiquidacoes.addRow(aLiquidacoes[iLinha], false);
      iContador++;
    }
    oGridLiquidacoes.renderRows();
    aLiquidacoes = aLiquidacaoNova;

    //Verifica se tem liquidações na grid para saber se libera/bloqueia o campo com o valor do repasse.
    atualizaValorRepasse(nTotalRepasse, (aLiquidacoes.length > 0));
  }

  /**
   * Adiciona uma liquidação na grid.
   */
  function lancarLiquidacao() {

    var iUnidade = $F('unidade_numero');
    var iRecurso = $F('recurso_numero');
    var iAnexo   = $F('anexo_numero');

    if (iUnidade == '' || iRecurso == '' || iAnexo == '') {

      alert("Para lançar uma liquidação, você deve primeiro informar a unidade, recurso e anexo.");
      return false;
    }

    var iCodigoEmpenho = $F('empenho_codigo');
    var iCodigoNota    = $F('liquidacao_numero');
    var sNomeCredor    = $F('liquidacao_descricao');
    var nValor         = $F('liquidacao_valor');
    /* [Inicio plugin LiquidacaoCompetencia - parte6] */
    /* [Fim plugin LiquidacaoCompetencia - parte6] */
    var nTotalRepasse  = 0;
    var nValorLinha    = 0;

    //Verifica se a liquidação foi selecionada.
    if (iCodigoNota == '') {

      alert("Selecione uma liquidação para ser lançada.");
      return false;
    }

    if (sNomeCredor == "") {
      alert("Aguarde carregar as informações.");
      return false;
    }

    //Verifica se a liquidação selecionada já está na grid.
    for (var iLinha = 0; iLinha < aLiquidacoes.length; iLinha++) {

      if (aLiquidacoes[iLinha][1] == iCodigoNota) {

        alert("Essa liquidação já foi lançada.");
        return false;
      }
    }

    //Monta a linha e coloca no array.
    /* [Inicio plugin LiquidacaoCompetencia - parte7] */
    var aLinha                        = [iCodigoEmpenho,
      iCodigoNota,
      sNomeCredor,
      js_formatar(nValor, 'f'),
      montaBotaoRemover(aLiquidacoes.length)];
    /* [Fim plugin LiquidacaoCompetencia - parte7] */
    aLiquidacoes[aLiquidacoes.length] = aLinha;

    /**
     * Após limpar a grid, reinsere cada linha e totaliza os valores do valor total.
     */
    oGridLiquidacoes.clearAll(true);
    for (var iLinhas = 0; iLinhas < aLiquidacoes.length; iLinhas++) {

      nValorLinha    = js_strToFloat(aLiquidacoes[iLinhas][3]);
      nTotalRepasse += nValorLinha;
      oGridLiquidacoes.addRow(aLiquidacoes[iLinhas], false);
    }
    oGridLiquidacoes.renderRows();

    $('empenho_codigo').value       = '';
    $('liquidacao_numero').value    = '';
    $('liquidacao_descricao').value = '';
    $('liquidacao_valor').value     = '';
    /* [Inicio plugin LiquidacaoCompetencia - parte8] */
    /* [Fim plugin LiquidacaoCompetencia - parte8] */

    atualizaValorRepasse(nTotalRepasse, aLiquidacoes.length > 0);
  }

  /**
   * Preenche a tela com as informações da solicitação de repasse.
   * @param {object}  oRetorno Objeto com as informações retornadas.
   * @paral {boolean} lErro    Se houve erro.
   */
  function preencherSolicitacao(oRetorno, lErro) {

    if (lErro) {

      $('codigo_repasse').value = '';
      alert("Não foi possível carregar as informações da solicitação de repasse informada.");
      return;
    }

    oDados            = oRetorno.dados;
    aDadosLiquidacoes = oDados.aLiquidacoes;

    $('unidade_numero').value          = oDados.iUnidade;
    $('unidade_descricao').value       = oDados.sUnidade.urlDecode();
    $('tipo_repasse').value            = oDados.iTipo;
    $('orgao_numero').value            = oDados.iOrgao;
    $('orgao_descricao').value         = oDados.sOrgao.urlDecode();
    $('orgao_unidade_exercicio').value = oDados.iUnidadeExercicio;
    $('recurso_numero').value          = oDados.iRecurso;
    $('recurso_descricao').value       = oDados.sRecurso.urlDecode();
    $('anexo_numero').value            = oDados.iAnexo;
    $('anexo_descricao').value         = oDados.sAnexo.urlDecode();
    $('conta_destino_numero').value    = oDados.iContaDestino;
    $('conta_destino_descricao').value = oDados.sContaDestino.urlDecode();
    $('data_repasse').value            = oDados.sData;
    $('valor_repasse').value           = js_formatar(oDados.nValor, 'f');
    $('motivo').value                  = oDados.sMotivo.urlDecode();

    //Preenche grid de liquidações se houver informações.
    if (aDadosLiquidacoes.length > 0) {

      oGridLiquidacoes.clearAll(true);
      for (var iLiquidacoes = 0; iLiquidacoes < aDadosLiquidacoes.length; iLiquidacoes++) {

        var aLinha = [
          aDadosLiquidacoes[iLiquidacoes].iEmpenho,
          aDadosLiquidacoes[iLiquidacoes].iNota,
          aDadosLiquidacoes[iLiquidacoes].sCredor.urlDecode(),
          js_formatar(aDadosLiquidacoes[iLiquidacoes].nValor, 'f'),
          montaBotaoRemover(iLiquidacoes)
        ];
        aLiquidacoes[iLiquidacoes] = aLinha;
        oGridLiquidacoes.addRow(aLiquidacoes[iLiquidacoes], false);
      }
      oGridLiquidacoes.renderRows();
      atualizaValorRepasse(oDados.nValor, true);
    }
  }

  /**
   * Carrega uma solicitação de repasse a partir do seu sequencial.
   * @param {int} iSolicitacaoRepasse Código da solicitação de repasse.
   */
  function carregarRepasse(iSolicitacaoRepasse) {

    var sRPC        = 'cai4_solicitacaorepasse.RPC.php';
    var fnRetorno   = preencherSolicitacao;
    var oParameters = {
      exec:         'getDadosSolicitacao',
      iSolicitacao: iSolicitacaoRepasse
    };

    new AjaxRequest(sRPC, oParameters, fnRetorno).setMessage('Aguarde, carregando a solicitação de repasse...').execute();
  }

  //Chama o método para criação da grid.
  criaGrid();

  /**
   * Chama a busca caso não seja inclusão.
   */
  if ($F('iOpcao') != '1') {
    buscarSolicitacao(true);
  }

  if ($F('iOpcao') == '3') {
    $('tipo_repasse').disabled = true;
    $('btnLancar').disabled = true;
  }
</script>
