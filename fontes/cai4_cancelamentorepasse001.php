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
require_once modification("libs/db_conecta_plugin.php");
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("dbforms/db_funcoes.php"));

$oGet   = db_utils::postMemory($_GET);
?>
<html xmlns="http://www.w3.org/1999/html">
<head>
  <title>DBSeller Inform&aacute;tica Ltda - P&aacute;gina Inicial</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <script type="text/javascript" src="scripts/scripts.js"></script>
  <script type="text/javascript" src="scripts/strings.js"></script>
  <script type="text/javascript" src="scripts/prototype.js"></script>
  <script type="text/javascript" src="scripts/AjaxRequest.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body-default">
<div class="container">
  <form id="formSolicitacaoRepasse">
    <fieldset>

      <legend><strong>Cancelamento de Autorização de Repasse Financeiro</strong></legend>
      <table>

        <tr>
          <td class="bold">
            <label for="codigo_repasse">
              <?php db_ancora('Solicitação de Repasse:', 'buscarSolicitacao(true)', 2, null, 'solicitacao_codigo_ancora'); ?>
            </label>
          </td>
          <td>
            <?php
            $Scodigo_repasse = "Solicitação de Repasse";
            db_input('codigo_repasse', 10, false, true, 'text', 3, null, null, null, '');
            ?>
          </td>
        </tr>

      </table>
    </fieldset>

    <input type="button" id="btnSalvarSolicitacao" onClick="cancelarSolicitacao();" value="Cancelar Autorização" />
  </form>
</div>
<script>
  function cancelarSolicitacao() {

    if ($F('codigo_repasse') == "") {

      alert('Campo Solicitação de Repasse é de preenchimento obrigatório.');
      return false;
    }

    var oParametros = {
      iSolicitacao : $F('codigo_repasse'),
      exec         : 'cancelarAutorizacaoRepasseFinanceiro'
    };
    var fnRetorno = retornoCancelar;

    new AjaxRequest(
      "emp4_gerarslipRPC.php",
      oParametros,
      fnRetorno).setMessage('Aguarde, cancelando autorização de repasse...').execute();
  }

  function retornoCancelar(oRetorno, lErro) {

    var iSolicitacao = $F('codigo_repasse');

    if (lErro) {

      alert(oRetorno.mensagem.urlDecode());
      return;
    }

    $('codigo_repasse').value = '';
    alert('Autorização referente a Solicitação de Repasse ' + iSolicitacao + ' cancelada com sucesso.');
  }

  /**
   * Busca uma solicitação de repasse.
   */
  function buscarSolicitacao(lMostrar) {

    var sQuerySring = 'funcao_js=parent.retornoSolicitacao|0&lFiltrosCancelamento=true';
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
    $('codigo_repasse').value = iCodigoRepasse;
  }
</script>
<?php db_menu(); ?>
</body>
</html>
