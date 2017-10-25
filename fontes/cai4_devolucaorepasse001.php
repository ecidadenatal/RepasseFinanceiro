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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("dbforms/db_funcoes.php"));
?>
<html xmlns="http://www.w3.org/1999/html">
<head>
  <title>DBSeller Inform&aacute;tica Ltda - P&aacute;gina Inicial</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <script type="text/javascript" src="scripts/scripts.js"></script>
  <script type="text/javascript" src="scripts/strings.js"></script>
  <script type="text/javascript" src="scripts/prototype.js"></script>
  <script type="text/javascript" src="scripts/widgets/DBLancador.widget.js"></script>
  <script type="text/javascript" src="scripts/AjaxRequest.js"></script>

  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body-default">
<div class="container">
  <form id="formCancelamentoAutorizacaoRepasse">
    <input type="hidden" id="saldo_conta" name="saldo_conta" value="" />
    <fieldset>
      <legend class="bold">Devolu��o de Repasse Financeiro</legend>
      <table>
        <tr>
          <td nowrap>
            <label for="codigo_solicitacao">
              <?php
              db_ancora("Solicita��o:", 'pesquisaSolicitacao(true);',  1);
              ?>
            </label>
          </td>
          <td>
            <?php
            $Scodigo_solicitacao = "Solicita��o";
            db_input('codigo_solicitacao', 10, 1, true, 'text', 1, "onchange='pesquisaSolicitacao(false);'");
            ?>
          </td>
        </tr>
        <tr>
          <td nowrap>
            <label class="bold" for="valor_solicitacao">Valor da Solicita��o:</label>
          </td>
          <td>
            <?php
            $Svalor_solicitacao = "Valor da Solicita��o";
            db_input("valor_solicitacao", 10, 4, true);
            ?>
          </td>
        </tr>
        <tr>
          <td nowrap>
            <label class="bold" for="valor_devolvido">Valor Devolvido:</label>
          </td>
          <td>
            <?php
            $Svalor_devolvido = "Valor Devolvido";
            db_input("valor_devolvido", 10, 4, true);
            ?>
          </td>
        </tr>
        <tr>
          <td nowrap>
            <label class="bold" for="valor_devolver">Valor a Devolver:</label>
          </td>
          <td>
            <?php
            $Svalor_devolver = "Valor a Devolver";
            $sScripValorDevolver  = ' onFocus="this.value = js_strToFloat(this.value)" ';
            $sScripValorDevolver .= ' onBlur="this.value = js_formatar(this.value, \'f\', 2)" ';
            db_input("valor_devolver", 10, 4, true, "text", 1, $sScripValorDevolver);
            ?>
          </td>
        </tr>
      </table>

      <fieldset>
        <legend class="bold">Notas Lan�adas</legend>
        <div id="ctnGridNotas" style="width: 500px">
        </div>
      </fieldset>
    </fieldset>
    <input type="button" value="Confirmar" id="btnConfirmar" onclick="devolver()"/>
  </form>
</div>
<?php
db_menu();
?>
</body>
</html>
<script>

  var oCodigoSolicitacao = $('codigo_solicitacao');
  var oValorSolicitacao  = $('valor_solicitacao');
  var oValorDevolvido    = $('valor_devolvido');
  var oValorDevolver     = $('valor_devolver');
  var oFormulario        = $('formCancelamentoAutorizacaoRepasse');

  /**
   * Prepara, abre e faz a busca na lookup.
   * @param {boolean} lMostrar Se deve mostrar a lookup ou fazer a busca autom�tica.
   */
  function pesquisaSolicitacao(lMostrar) {

    var sPathSolicitacao = "func_solicitacaorepasse.php?lFiltroDevolucao=true&funcao_js=parent.retornoSolicitacao|0";
    if (!lMostrar) {

      if (empty(oCodigoSolicitacao.value)) {

        limparTela();
        return;
      }

      sPathSolicitacao  = "func_solicitacaorepasse.php?funcao_js=parent.retornoSolicitacaoChave&lFiltroDevolucao=true";
      sPathSolicitacao += "&pesquisa_chave=" + oCodigoSolicitacao.value;
    }

    js_OpenJanelaIframe('', 'db_iframe_solicitacaorepasse', sPathSolicitacao, "Pesquisa Solicita��o de Repasse Financeiro", lMostrar);
  }

  /**
   * Trata o retorno a busca na lookup manualmente.
   * @param {int}     iCodigo C�digo da solicita��o selecionada.
   * @param {boolean} lErro   Se houve erro na busca.
   */
  function retornoSolicitacao(iCodigo, lErro) {

    oCodigoSolicitacao.value = iCodigo;
    db_iframe_solicitacaorepasse.hide();

    if (!lErro) {
      buscarSolicitacao();
      return;
    }

    limparTela();
  }

  /**
   * Trata o retorno da lookup de solicita��o por digita��o de chave.
   * @param {string}  sDescricao Descri��o retornada da lookup, n�o � utilizada neste caso.
   * @param {boolean} lErro      Informa se houve falha na busca.
   */
  function retornoSolicitacaoChave(sDescricao, lErro) {

    if (lErro) {

      retornoSolicitacao('', lErro);
      return false;
    }
    db_iframe_solicitacaorepasse.hide();
    buscarSolicitacao();
  }

  /**
   * Realiza a busca de uma solicita��o de repasse com base no campo de c�digo codigo_solicitacao.
   */
  function buscarSolicitacao() {

    var iSolicitacao = oCodigoSolicitacao.value;
    var sArquivo     = "cai4_solicitacaorepasse.RPC.php";
    var oParametros  = {
      exec:         "getDadosSolicitacaoDevolucao",
      iSolicitacao: iSolicitacao
    };
    var fnFuncao = preencheSolicitacao;
    new AjaxRequest(sArquivo, oParametros, fnFuncao)
      .setMessage("Carregando solicita��o de repasse financeiro, aguarde...")
      .execute();
  }

  /**
   * Preenche os campos da tela com as informa��es da solicita��o buscadas.
   * @param {object}  oRetorno Objeto retornado da consulta ajax.
   * @param {boolean} lErro    Informa se houve erro na solicita��o.
   */
  function preencheSolicitacao(oRetorno, lErro) {

    if (lErro) {

      alert("Houve uma falha ao carregar a solicita��o.");
      return false;
    }

    if (oRetorno.erro) {

      alert(oRetorno.message.urlDecode());
      return false;
    }

    var nValorSolicitacao  = oRetorno.nValor;
    var nValorDevolvido    = oRetorno.nValorDevolvido;
    var lLinhaDesabilitada = false;
    var aLinha             = [];
    var aLiquidacoes       = oRetorno.aLiquidacoes;

    oValorSolicitacao.value = js_formatar(nValorSolicitacao, 'f');
    oValorDevolvido.value   = js_formatar(nValorDevolvido, 'f');
    oValorDevolver.value    = js_formatar(0.0, 'f');

    oValorDevolver.readOnly = false;
    oValorDevolver.style    = "";

    oGridNotas.clearAll(true);
    if (aLiquidacoes.length > 0) {

      oValorDevolver.readOnly = true;
      oValorDevolver.style    = "background-color:#DEB887;";
      for (var iIndice = 0; iIndice < aLiquidacoes.length; iIndice++) {

        aLinha = [];
        aLinha[0] = aLiquidacoes[iIndice].iEmpenho;
        aLinha[1] = aLiquidacoes[iIndice].iNota;
        aLinha[2] = js_formatar(aLiquidacoes[iIndice].nValor, 'f');

        lLinhaDesabilitada = aLiquidacoes[iIndice].lEstornado == "t" || aLiquidacoes[iIndice].nValorPago > 0;

        oGridNotas.addRow(aLinha, false, lLinhaDesabilitada);
        oGridNotas.aRows[iIndice].aCells[0].sEvents='onClick="totalizaValorDevolver()"';
      }
    }
    oGridNotas.renderRows();
  }

  /**
   * Ao selecionar ou deselecionar uma nota, retotaliza o valor a ser devolvido com base nas liquida��es selecionadas.
   */
  function totalizaValorDevolver() {

    var nValor = 0;
    var aNotas = oGridNotas.getSelection("object");

    for (var iIndice = 0; iIndice < aNotas.length; iIndice++) {
      nValor += aNotas[iIndice].aCells[3].getValue().getNumber();
    }

    oValorDevolver.value = js_formatar(nValor, 'f');
  }

  /**
   * Valida e faz a requisi��o para efetuar a devolu��o da solicita��o de repasse.
   */
  function devolver() {

    if (oCodigoSolicitacao.value == "") {

      alert("Campo Solicita��o � de preenchimento obrigat�rio.");
      return false;
    }

    if (oValorDevolver.value == "") {

      alert("O Valor a Devolver � de preenchimento obrigat�rio.");
      return false;
    }

    var nValorDevolver    = oValorDevolver.value.getNumber();
    var nValorDevolvido   = oValorDevolvido.value.getNumber();
    var nValorSolicitacao = oValorSolicitacao.value.getNumber();
    var nValorDisponivel  = (new Number(nValorSolicitacao - nValorDevolvido)).toFixed(2);

    if (nValorDevolver <= 0) {

      if (oGridNotas.getRows().length) {
        alert("� necess�rio informar ao menos uma Nota para devolu��o.");
      } else {
        alert("O campo Valor a Devolver deve possuir valor maior que zero.");
      }

      return false;
    }

    if (nValorDevolver > nValorDisponivel) {

      var sMensagem = "O valor a devolver ultrapassa o saldo dispon�vel para devolu��o.";
      sMensagem    += "\nO saldo a devolver dispon�vel � de " + js_formatar(nValorDisponivel, 'f') + ".";
      alert(sMensagem);
      return false;
    }

    var nValorLiquidacoes = 0 ;
    var aLiquidacoes      = [];
    var aNotasLiquidacao  = oGridNotas.getSelection("object");

    for (var iIndice = 0; iIndice < aNotasLiquidacao.length; iIndice++) {

      aLiquidacoes[iIndice] = aNotasLiquidacao[iIndice].aCells[2].getValue();
      nValorLiquidacoes    += aNotasLiquidacao[iIndice].aCells[3].getValue().getNumber();
    }

    if (aNotasLiquidacao.length > 0 && nValorLiquidacoes != nValorDevolver) {

      alert("O Valor a Devolver deve ser igual a soma dos valores das Notas Selecionadas.");
      return false;
    }

    //Se n�o tiver liquida��es, deve validar o saldo da conta antes de continuar.
    if (oGridNotas.aRows.length == 0) {

      validaSaldoConta();
      return false;
    }

    realizaDevolucao(nValorDevolver, aLiquidacoes);
  }

  /**
   * Faz a chamada para validar o saldo da conta antes de realizar a devolu��o.
   */
  function validaSaldoConta() {

    var sArquivo    = "cai4_solicitacaorepasse.RPC.php";
    var oParametros = {
      exec:          "verificaSaldoConta",
      iSolicitacao:  oCodigoSolicitacao.value
    };
    var fnRetorno   = retornoValidacaoConta;

    new AjaxRequest(sArquivo, oParametros, fnRetorno).setMessage("Verificando saldo da conta, aguarde...").execute();
  }

  /**
   * Faz a valida��o do saldo da conta e chama a fun��o respons�vel pela devolu��o.
   */
  function retornoValidacaoConta(oRetorno, lErro) {

    if (lErro) {

      alert("N�o foi poss�vel verificar o saldo da conta.");
      return false;
    }

    if (oRetorno.erro) {

      alert(oRetorno.message.urlDecode());
      return false;
    }

    var nValor      = oValorDevolver.value.getNumber();
    var nSaldoConta = oRetorno.nSaldoConta;

    if (nValor > nSaldoConta) {

      var sMensagem = "A conta cr�dito " + oRetorno.iConta + " do slip a ser gerado para a devolu��o de repasse n�o possui saldo suficiente.\n"
                    + "Deseja gerar o slip ainda que n�o possua saldo para a conta?";

      if (!confirm(sMensagem)) {
        return false;
      }
    }
    realizaDevolucao(nValor, []);
  }

  /**
   * Faz a requisi��o para realizar a devolu��o da solicita��o de repasse.
   * @param {number} nValorDevolver Valor a ser devolvido.
   * @param {Array}  aLiquidacoes   Liquida��es relacionadas a devolu��o.
   */
  function realizaDevolucao(nValorDevolver, aLiquidacoes) {

    var sArquivo    = "emp4_gerarslipRPC.php";
    var oParametros = {
      exec:          "salvarDevolucaoRepasse",
      iSolicitacao:  oCodigoSolicitacao.value,
      nValor:        nValorDevolver,
      aLiquidacoes:  aLiquidacoes
    };
    var fnRetorno   = retornoDevolucao;

    new AjaxRequest(sArquivo, oParametros, fnRetorno).setMessage("Realizando devolu��o, aguarde...").execute();
  }

  /**
   * Fun��o realizada ap�s executar a devolu��o de uma solicita��o.
   * @param {object}  oRetorno Objeto de retorno da requisi��o de devolu��o da solicita��o.
   * @param {boolean} lErro    Indica se houve erro na solicita��o.
   */
  function retornoDevolucao(oRetorno, lErro) {

    if (lErro) {

      alert(oRetorno.mensagem.urlDecode());
      return false;
    }

    if (confirm("Devolu��o de Repasse efetuada com sucesso.\nO slip " + oRetorno.slip + " foi gerado. Deseja emitir o documento?")) {
      var sQuery  = "?numslip=" + oRetorno.slip;
      var iHeight = (screen.availHeight - 40);
      var iWidth  = (screen.availWidth - 5);
      var sOpcoes = 'width=' + iWidth + ',height=' + iHeight + ',scrollbars=1,location=1';
      var oJanela = window.open("cai1_slip003.php" + sQuery, '', sOpcoes);

      oJanela.moveTo(0, 0);
    }

    pesquisaSolicitacao(true);
  }

  /**
   * Cria a grid para as notas de liquida��o.
   */
  function criaGrid() {

    oGridNotas              = new DBGrid("oGridNotas");
    oGridNotas.nameInstance = "oGridNotas";
    oGridNotas.setCheckbox(0);
    oGridNotas.setCellAlign(["right", "right", "right"]);
    oGridNotas.setHeader(["N�mero do Empenho", "Nota", "Valor"]);
    oGridNotas.setCellWidth(["48%", "22%", "30%"]);

    oGridNotas.setCallBackSelectAll(function() {
      totalizaValorDevolver();
    });

    oGridNotas.show($('ctnGridNotas'));
  }

  /**
   * Limpra os campos do formul�rio e a Grid.
   */
  function limparTela() {

    oGridNotas.clearAll(true);
    oFormulario.reset();
  }

  criaGrid();
  limparTela();
  pesquisaSolicitacao(true);
</script>