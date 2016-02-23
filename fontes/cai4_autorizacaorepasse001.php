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
require_once ("libs/db_conecta_plugin.php");
require_once ("libs/db_sessoes.php");
require_once ("libs/db_utils.php");
require_once ("libs/db_app.utils.php");
require_once ("dbforms/db_funcoes.php");

require_once('model/caixa/ConfiguracaoRepasseFinanceiro.model.php');



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
<body>
<div class="container">

  <fieldset style="width: 600px;">
    <legend class="bold">Autorização de Repasse Financeiro</legend>
    <table style="width:98%;margin-bottom:7px">
      <tr style="display: table-row">
        <td nowrap class="field-size1" style="width: 100px;">
          <label class="bold" for="tipo">Tipo:</label>
        </td>
        <td>
          <?php
          $aTipos = array(
            SolicitacaoRepasseFinanceiro::TIPO_REPASSE       => "Repasse",
            SolicitacaoRepasseFinanceiro::TIPO_REGULARIZACAO => "Regularização"
          );
          db_select("tipo", $aTipos, true, 1, 'onchange="trocarTipo();"');
          ?>
        </td>
      </tr>
      <tr id="linha_conta_pagadora">
        <td nowrap>
          <label for="codigo_conta">
            <?php
            db_ancora("Conta Pagadora:", 'pesquisaContaPagadora(true, false);', 1);
            ?>
          </label>
        </td>
        <td>
          <?php
          $Scodigo_conta = "Conta Pagadora";
          $codigo_conta = ConfiguracaoRepasseFinanceiro::getContaPagadora();
          db_input('codigo_conta', 10, 1, true, 'text', 1, "onchange='pesquisaContaPagadora(false, false);'");
          db_input('descricao_conta', 50, 1, true, 'text', 3);
          ?>
        </td>
      </tr>
    </table>
    <br />
    <div id="ctnLancadorSolicitacao"></div>
  </fieldset>
  <br />

  <input type="button" value="Autorizar" id="btnAutorizar" onclick="salvar()"/>

</div>
<?php
db_menu(
  db_getsession("DB_id_usuario"),
  db_getsession("DB_modulo"),
  db_getsession("DB_anousu"),
  db_getsession("DB_instit")
);
?>
</body>
</html>

<script>

  var sParametroPadrao = "bloquearSolicitacaoVinculada=true";

  var oLancador = new DBLancador('ctnLancadorSolicitacao');
  oLancador.setNomeInstancia('oLancador');
  oLancador.setLabelAncora('Solicitação:');
  oLancador.setTextoFieldset('Solicitações de Repasse Financeiro');
  oLancador.setParametrosPesquisa('func_solicitacaorepasse.php', ['0','1'], sParametroPadrao);
  oLancador.setTituloJanela("Pesquisa de Solicitação de Repasse Financeiro");
  oLancador.setCallbackAncora(atualizaLookUp);
  oLancador.show($('ctnLancadorSolicitacao'));

  var oCodigoConta    = $('codigo_conta');
  var oDescricaoConta = $('descricao_conta');

  var iContaPagadoraPadrao = oCodigoConta.value;

  function atualizaLookUp() {
    oLancador.oDadosPesquisa.sStringAdicional = sParametroPadrao + "&tipo=" + $F('tipo');
  }

  function trocarTipo() {

    $('linha_conta_pagadora').style.display = ($F('tipo') == 2 ? "none" : "table-row");
    $('txtCodigooLancador').value    = "";
    $('txtDescricaooLancador').value = "";
    oLancador.clearAll();
    limparCampos();

    if ($F('tipo') == 1) {

      oCodigoConta.value = iContaPagadoraPadrao;
      pesquisaContaPagadora(false, true);
    }

  }

  function salvar() {

    if (oCodigoConta.value == "" && $F('tipo') == 1) {
      alert("Campo Conta Pagadora é de preenchimento obrigatório.");
      return false;
    }

    var aRegistrosLancador = new Array();
    oLancador.getRegistros().each(function(oRegistro, iIndice) {
      aRegistrosLancador[iIndice] = oRegistro.sCodigo;
    });
    if (aRegistrosLancador.length == 0) {
      alert("É necessário informar ao menos uma Solicitação de Repasse.");
      return false;
    }

    var oParametros = {
      'exec': 'autorizarRepasseFinanceiro',
      'conta_pagadora': oCodigoConta.value,
      'solicitacoes'  : aRegistrosLancador,
      'tipo'          : $('tipo').value
    };

    new AjaxRequest(
      'emp4_gerarslipRPC.php',
      oParametros,
      function (oRetorno, lErro) {


        if (lErro) {
          alert(oRetorno.mensagem.urlDecode());
          return false;
        }

        oLancador.clearAll();
        limparCampos();

        var sMensagem        = "Solicitações de repasse autorizadas com sucesso.";
        var sMensagemRepasse = sMensagem + "\n\nDeseja visualizar os slips gerados?";

        if ($F('tipo') == 1 && confirm(sMensagemRepasse)) {

          var oJanela = window.open(
            'cai3_autorizacaorepasse002.php?autorizacao='+oRetorno.autorizacoes,
            '',
            'width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');
          oJanela.moveTo(0,0);

          return true;
        }

        alert(sMensagem);
        return true;
      }
    ).setMessage('Aguarde, salvando informações...').execute();
  }

  function limparCampos() {

    oCodigoConta.value    = '';
    oDescricaoConta.value = '';
  }

  function pesquisaContaPagadora(lMostrar, lPesquisaContaPadrao) {

    var sPathConta = "func_saltes.php?funcao_js=parent.preencheContaPagadora|0|2";
    if (!lMostrar) {

      if (oCodigoConta.value == "") {
        limparCampos();
      }

      var sFuncaoRetorno = "completaContaPagadora";
      if (lPesquisaContaPadrao) {
        sFuncaoRetorno = "completaContaPagadoraPadrao";
      }
      sPathConta = "func_saltes.php?funcao_js=parent." + sFuncaoRetorno +"&pesquisa_chave="+oCodigoConta.value;
    }

    js_OpenJanelaIframe('', 'db_iframe_saltes', sPathConta, "Pesquisa de Conta Pagadora", lMostrar);
  }

  function preencheContaPagadora(iCodigoConta, sDescricao) {

    oCodigoConta.value    = iCodigoConta;
    oDescricaoConta.value = sDescricao;
    db_iframe_saltes.hide();
  }

  function completaContaPagadora(sDescricao, lErro) {

    oDescricaoConta.value = sDescricao;
    if (lErro) {
      oCodigoConta.value = '';
    }
  }

  function completaContaPagadoraPadrao(sDescricao, lErro) {

    if (lErro) {
      sDescricao = '';
    }
    completaContaPagadora(sDescricao, lErro);
  }

  trocarTipo();
</script>

