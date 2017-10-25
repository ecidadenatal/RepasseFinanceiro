<?php

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("dbforms/db_funcoes.php"));
db_postmemory($_GET);

define('INCLUSAO', 1);
define('ALTERACAO', 2);
define('EXCLUSAO', 3);

$oGet = db_utils::postMemory($_GET);
parse_str($_SERVER["QUERY_STRING"]);
$oDaoSolicitacaoRepasse = new cl_solicitacaorepasse();
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <link href="estilos.css" rel="stylesheet" type="text/css">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/strings.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
</head>
<body style="background-color: #CCCCCC; margin-top:20px;">

<form name="form2" method="post" action="">
  <div class="container">
    <fieldset style="width: 630px">
      <legend class="bold">Filtros</legend>
      <?php
      db_input('funcao_js', 10, 0, true, 'hidden', 3);
      ?>
      <table style="width: 100%; border: 1px;">
        <tr>
          <td>
            <?php
            db_ancora('Órgão:', 'pesquisaOrgao(true)', 1);
            ?>
          </td>
          <td>
            <?php
            $Scodigo_orgao = "Órgão";
            db_input('codigo_orgao', 8, 1, true, 'text', 1, "onchange='pesquisaOrgao(false)'");
            db_input('descricao_orgao', 60, 1, true, 'text', 3);
            ?>
          </td>
        </tr>
        <tr>
          <td>
            <?php
            db_ancora('Unidade:', 'pesquisaUnidade(true)', 1);
            ?>
          </td>
          <td>
            <?php
            $Scodigo_unidade = "Unidade";
            db_input('codigo_unidade', 8, 1, true, 'text', 1, "onchange='pesquisaUnidade(false)'");
            db_input('descricao_unidade', 60, 1, true, 'text', 3);
            ?>
          </td>
        </tr>

        <tr>
          <td>
            <?php
            db_ancora('Recurso:', 'pesquisaRecurso(true)', 1);
            ?>
          </td>
          <td>
            <?php
            $Scodigo_recurso = "Recurso";
            db_input('codigo_recurso', 8, 1, true, 'text', 1, "onchange='pesquisaRecurso(false)'");
            db_input('descricao_recurso', 60, 1, true, 'text', 3);
            ?>
          </td>
        </tr>

        <tr>
          <td>
            <?php
            db_ancora('Anexo:', 'pesquisaAnexo(true)', 1);
            ?>
          </td>
          <td>
            <?php
            $Scodigo_anexo = "Anexo";
            db_input('codigo_anexo', 8, 1, true, 'text', 1, "onchange='pesquisaAnexo(false)'");
            db_input('descricao_anexo', 60, 1, true, 'text', 3);
            ?>
          </td>
        </tr>
      </table>
    </fieldset>
    <p>
      <input type="submit" id="btnPesquisar" value="Pesquisar" />
      <input type="button" id="btnFechar" value="Fechar" onclick="parent.db_iframe_solicitacaorepasse.hide()" />
    </p>
  </div>
</form>

<div class="container">
  <fieldset>
    <legend class="bold">Registros Encontrados</legend>
    <?php

    $iAnoSessao = db_getsession("DB_anousu");
    $aCampos = array(
      'distinct solicitacaorepasse.sequencial as dl_Código',
      'orcunidade.o41_descr as dl_Unidade',
      'o15_descr as dl_Recurso',
      'valor',
      "db83_descricao||' - Ag: '||db83_bancoagencia||' - Conta: '||db83_conta as dl_Conta_de_Destino",
      'solicitacaorepasse.data',
      'motivo as dl_Motivo'
    );
    $sCampos = implode(',', $aCampos);
    $aWhere[] = "c61_anousu = ".db_getsession("DB_anousu");

    $aWhere = array();
    if (!empty($oGet->bloquearSolicitacaoVinculada) || (!empty($oGet->iAcao) && (in_array($oGet->iAcao, array(ALTERACAO, EXCLUSAO))))) {
      $aWhere[] = " not exists (select 1 from plugins.autorizacaorepasse as ar where ar.solicitacaorepasse = solicitacaorepasse.sequencial) ";
    }

    if (!empty($codigo_orgao)) {
      $aWhere[] = "orcdotacao.o58_orgao = {$codigo_orgao}";
      $aWhere[] = "orcdotacao.o58_anousu = {$iAnoSessao}";
    }

    if (!empty($codigo_unidade)) {
      $aWhere[] = "orcunidade.o41_unidade = {$codigo_unidade}";
      $aWhere[] = "orcunidade.o41_anousu = {$iAnoSessao}";
    }

    if (!empty($codigo_recurso)) {
      $aWhere[] = "orcdotacao.o58_codigo = {$codigo_recurso}";
      $aWhere[] = "orcdotacao.o58_anousu = {$iAnoSessao}";
    }

    if (!empty($codigo_anexo)) {
      $aWhere[] = "orcdotacao.o58_localizadorgastos = {$codigo_anexo}";
      $aWhere[] = "orcdotacao.o58_anousu = {$iAnoSessao}";
    }

    if(isset($lFiltrosCancelamento)) {
      // Tem autorização
      $aWhere[] = "autorizacaorepasse.sequencial is not null";
      // Não devolvido
      $sWhereNaoDevolvido  = "select 1 from devolucaosolicitacaorepasse ";
      $sWhereNaoDevolvido .= "where devolucaosolicitacaorepasse.solicitacaorepasse = solicitacaorepasse.sequencial";
      $aWhere[] = "not exists($sWhereNaoDevolvido)";
      // Somente slip não autenticado
      $aWhere[] = "(k17_autent is null or k17_autent = 0)";
      // Se o slip não está em arquivo
      $aWhere[] = "(empageconfgera.e90_codmov is null or empageconfgera.e90_cancelado = true)";
    }

    if(isset($lFiltroDevolucao)) {
      // Tem autorização
      $aWhere[] = "autorizacaorepasse.sequencial is not null";
      // Somente slip autenticado
      $aWhere[] = "(k17_autent is not null and k17_autent <> 0)";
      // Não estar completamente devolvida.
      $sValorDevolvido  = " solicitacaorepasse.valor > (select COALESCE(sum(valor), 0) ";
      $sValorDevolvido .= "                             from plugins.devolucaosolicitacaorepasse ";
      $sValorDevolvido .= "                             where solicitacaorepasse = solicitacaorepasse.sequencial) ";
      $aWhere[] = $sValorDevolvido;
    }

    $iTipo = isset($tipo) ? (int) $tipo : null;
    if (!empty($iTipo)) {
      $aWhere[] = " tipo = {$iTipo} " ;
    }


    $sWhereDotacoes = PermissaoUsuarioEmpenho::getDotacoesPorAnoDoUsuario(
      db_getsession('DB_id_usuario'),
      db_getsession('DB_anousu'),
      PermissaoUsuarioEmpenho::PERMISSAO_MANUTENCAO_CONSULTA
    );
    if (!empty($sWhereDotacoes)) {
      $aWhere[] = $sWhereDotacoes;
    }


    if(empty($pesquisa_chave)) {

      $sWhere = implode(' and ', $aWhere);
      if(isset($lFiltrosCancelamento) || isset($lFiltroDevolucao)) {
        $sql = $oDaoSolicitacaoRepasse->sql_query_cancelamento($sCampos, $sWhere);
      } else {
        $sql = $oDaoSolicitacaoRepasse->sql_query_geral($sCampos, $sWhere);
      }

      db_lovrot($sql,15,"()","",$funcao_js);

    } else {

      if (!empty($pesquisa_chave) ){

        $aWhere[] = " solicitacaorepasse.sequencial = {$pesquisa_chave}";
        if (isset($lFiltroDevolucao)) {
          $sSqlRepasse = $oDaoSolicitacaoRepasse->sql_query_cancelamento($sCampos, implode(' and ', $aWhere));
        } else {
          $sSqlRepasse = $oDaoSolicitacaoRepasse->sql_query_geral($sCampos, implode(' and ', $aWhere) );
        }
        $result = $oDaoSolicitacaoRepasse->sql_record($sSqlRepasse);
        if($oDaoSolicitacaoRepasse->numrows!=0){

          $oStdDados = db_utils::fieldsMemory($result, 0);
          echo "<script>".$funcao_js."('$oStdDados->dl_unidade',false);</script>";
        }else{
          echo "<script>".$funcao_js."('Chave(".$pesquisa_chave.") não Encontrado',true);</script>";
        }
      }else{
        echo "<script>".$funcao_js."('',false);</script>";
      }
    }
    ?>
  </fieldset>
</div>

</body>
</html>


<script>

  var oCodigoOrgao      = $('codigo_orgao');
  var oDescricaoOrgao   = $('descricao_orgao');
  var oCodigoUnidade    = $('codigo_unidade');
  var oDescricaoUnidade = $('descricao_unidade');
  var oCodigoRecurso    = $('codigo_recurso');
  var oDescricaoRecurso = $('descricao_recurso');
  var oCodigoAnexo      = $('codigo_anexo');
  var oDescricaoAnexo   = $('descricao_anexo');


  function pesquisaOrgao(lMostrar) {

    var sPath = "func_orcorgao.php?funcao_js=parent.preencheOrgao|0|2";
    if (!lMostrar) {
      if (oCodigoOrgao.value == "") {

        oDescricaoOrgao.value = "";
        limparUnidade();

        return false;
      }
      sPath = "func_orcorgao.php?funcao_js=parent.completaOrgao&pesquisa_chave="+oCodigoOrgao.value;
    }
    js_OpenJanelaIframe('', 'db_iframe_orcorgao', sPath, "Pesquisa de Orgãos", lMostrar);
  }

  function preencheOrgao(iCodigo, sDescricao) {
    oCodigoOrgao.value    = iCodigo;
    oDescricaoOrgao.value = sDescricao;
    db_iframe_orcorgao.hide();
  }

  function completaOrgao(sDescricao, lErro) {
    oDescricaoOrgao.value = sDescricao;
    if (lErro) {
      oCodigoOrgao.value = '';
    }
  }


  function pesquisaUnidade(lMostrar) {

    if (oCodigoOrgao.value == '') {

      alert("Para selecionar uma unidade, você deve primeiro informar o Órgão.");
      limparUnidade();
      return false;
    }

    var sOrgao = "&orgao="+oCodigoOrgao.value;
    var sPath  = "func_orcunidade.php?funcao_js=parent.preencheUnidade|2|4"+sOrgao;
    if (!lMostrar) {
      if (oCodigoUnidade.value == "") {
        oDescricaoUnidade.value = "";
        limparAnexo();
        return false;
      }
      sPath = "func_orcunidade.php?funcao_js=parent.completaUnidade&pesquisa_chave="+oCodigoUnidade.value+""+sOrgao;
    }
    js_OpenJanelaIframe('', 'db_iframe_orcunidade', sPath, "Pesquisa de Unidade", lMostrar);
  }

  function preencheUnidade(iCodigo, sDescricao) {
    oCodigoUnidade.value    = iCodigo;
    oDescricaoUnidade.value = sDescricao;
    db_iframe_orcunidade.hide();
  }

  function completaUnidade(sDescricao, lErro) {
    oDescricaoUnidade.value = sDescricao;
    if (lErro) {
      oCodigoUnidade.value = '';
    }
  }

  function pesquisaRecurso(lMostrar) {

    var sPath = "func_orctiporec.php?funcao_js=parent.preencheRecurso|0|1";
    if (!lMostrar) {
      if (oCodigoRecurso.value == "") {
        oDescricaoRecurso.value = "";
        return false;
      }
      sPath = "func_orctiporec.php?funcao_js=parent.completaRecurso&pesquisa_chave="+oCodigoRecurso.value;
    }
    js_OpenJanelaIframe('', 'db_iframe_orctiporec', sPath, "Pesquisa de Recurso", lMostrar);
  }

  function preencheRecurso(iCodigo, sDescricao) {
    oCodigoRecurso.value    = iCodigo;
    oDescricaoRecurso.value = sDescricao;
    db_iframe_orctiporec.hide();
  }

  function completaRecurso(sDescricao, lErro) {
    oDescricaoRecurso.value = sDescricao;
    if (lErro) {
      oCodigoRecurso.value = '';
    }
  }

  function pesquisaAnexo(lMostrar) {

    var sOrgao = "";
    if (oCodigoOrgao.value != "") {
      sOrgao = "&orgao="+oCodigoOrgao.value;
    }

    var sPath = "func_ppasubtitulolocalizadorgasto.php?funcao_js=parent.preencheAnexo|0|2"+sOrgao;
    if (!lMostrar) {
      if (oCodigoAnexo.value == "") {
        oDescricaoAnexo.value = "";
        return false;
      }
      sPath = "func_ppasubtitulolocalizadorgasto.php?funcao_js=parent.completaAnexo&pesquisa_chave="+oCodigoAnexo.value+""+sOrgao;
    }
    js_OpenJanelaIframe('', 'db_iframe_ppasubtitulolocalizadorgasto', sPath, "Pesquisa de Anexo", lMostrar);
  }

  function preencheAnexo(iCodigo, sDescricao) {
    oCodigoAnexo.value    = iCodigo;
    oDescricaoAnexo.value = sDescricao;
    db_iframe_ppasubtitulolocalizadorgasto.hide();
  }

  function completaAnexo(sDescricao, lErro) {
    oDescricaoAnexo.value = sDescricao;
    if (lErro) {
      oCodigoAnexo.value = '';
    }
  }

  function limparAnexo() {
    oCodigoAnexo.value    = '';
    oDescricaoAnexo.value = '';
  }

  function limparUnidade() {
    oCodigoUnidade.value    = '';
    oDescricaoUnidade.value = '';
  }
</script>

<script type="text/javascript">
(function() {
  var query = frameElement.getAttribute('name').replace('IF', ''), input = document.querySelector('input[value="Fechar"]');
  input.onclick = parent[query] ? parent[query].hide.bind(parent[query]) : input.onclick;
})();
</script>
