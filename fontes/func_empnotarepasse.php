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

require("libs/db_stdlib.php");
require("libs/db_conecta_plugin.php");
include("libs/db_sessoes.php");
include("dbforms/db_funcoes.php");
include("classes/db_empnota_classe.php");
db_postmemory($_POST);
db_postmemory($_GET);
parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);
$clempnota = new cl_empnota;
$clempnota->rotulo->label("e69_numero");
$clempnota->rotulo->label("e69_dtnota");
$rotulo = new rotulocampo;
$rotulo->label("z01_nome");
$rotulo->label("e60_codemp");
$rotulo->label("e60_numemp");
$iAnoSessao = db_getsession('DB_anousu');
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <link href="estilos.css" rel="stylesheet" type="text/css">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <script>
    function js_mascara(evt){
      var evt = (evt) ? evt : (window.event) ? window.event : "";

      if ((evt.charCode > 46 && evt.charCode < 58) || evt.charCode == 0) {//8:backspace|46:delete|190:.
        return true;
      } else {
        return false;
      }
    }
  </script>
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table height="100%" border="0"  align="center" cellspacing="0" bgcolor="#CCCCCC">
  <tr>
    <td height="63" align="center" valign="top">
      <table width="35%" border="0" align="center" cellspacing="0">
        <form name="form2" method="post" action="" >
          <tr>
            <td width="4%" align="right" nowrap title="<?=$Te60_numemp?>"><?=$Le60_codemp?></td>
            <td width="96%" align="left" nowrap>

              <input name="chave_e60_codemp" size="10" type='text'  onKeyPress="return js_mascara(event);" >
              <?=$Le60_numemp?>
              <? db_input("e60_numemp",10,$Ie60_numemp,true,"text",4,"","chave_e60_numemp"); ?>
            </td>
          </tr>
          <tr>
            <td width="4%" align="right" nowrap title="<?=$Te69_numero?>"><?=$Le69_numero?></td>
            <td width="96%" align="left" nowrap>
              <?=db_input("e69_numero",10,$Ie69_numero,true,"text",4,"","chave_e69_numero"); ?>
              <?=$Le69_dtnota?>
              <?=db_inputdata('e69_dtnota','','','',false,'text','','','chave_e69_dtnota');?>
            </td>
          </tr>
          <tr>
            <td width="4%" align="right" nowrap title="<?=$Tz01_nome?>"><?=$Lz01_nome?></td>
            <td width="96%" align="left" nowrap>
              <? db_input("z01_nome",45,"",true,"text",4,"","chave_z01_nome"); ?>
            </td>
          </tr>
          <tr>
            <td colspan="2" align="center">
              <input name="pesquisar" type="submit" id="pesquisar2" value="Pesquisar">
              <input name="limpar" type="reset" id="limpar" value="Limpar" >
              <input name="Fechar" type="button" id="fechar" value="Fechar" onClick="parent.db_iframe_repasse.hide();">
            </td>
          </tr>
        </form>
      </table>
    </td>
  </tr>
  <tr>
    <td align="center" valign="top">
      <?php
      $sCampos  = " distinct empnota.e69_codnota, empnota.e69_numero, ";
      $sCampos .= " substr(z01_nome, 1, 30) as z01_nome, (e60_codemp||'/'||e60_anousu)::varchar as dl_Código_empenho, ";
      $sCampos .= " empnota.e69_dtrecebe, empnotaele.e70_valor, empnotaele.e70_vlrliq, empnotaele.e70_vlranu ";
      $sCampos .= ", (select (mes || '/' || ano) as mescompetencia      
                        from plugins.liquidacaocompetencia                
                             inner join pagordemnota on e71_codord = pagordem
                       where e71_codnota = empnota.e69_codnota) as dl_Mês_competência";
      
      $aWhere   = array();

      //Aplica o filtro da despesa.
      $sDotacoes = PermissaoUsuarioEmpenho::getDotacoesPorAnoDoUsuario(
        db_getsession('DB_id_usuario'),
        db_getsession('DB_anousu'),
        PermissaoUsuarioEmpenho::PERMISSAO_MANUTENCAO
      );
      if (!empty($sDotacoes)) {
        $aWhere[] = " {$sDotacoes} ";
      }

      //Somente liquidações aprovadas no controle interno.
      $sWhereControleInterno  = " (exists(select 1 from plugins.empenhonotacontroleinterno ";
      $sWhereControleInterno .= "               where nota = e71_codnota ";
      $sWhereControleInterno .= "               and situacao in (" . ControleInterno::SITUACAO_APROVADA . ", " . ControleInterno::SITUACAO_LIBERADO_AUTOMATICO . "))";
      $sWhereControleInterno .= " )";
      $aWhere[]               = $sWhereControleInterno;

      //Aplica filtros por unidade, anexo e recurso.
      if (isset($iUnidadeExercicio) && isset($iUnidadeOrgao) && isset($iUnidade) && isset($iAnexo) && isset($iRecurso)) {

        $aWhere[] = " o58_anousu = e60_anousu and o58_orgao = {$iUnidadeOrgao} ";
        
        $aWhere[] = " o58_unidade = {$iUnidade} and o58_codigo = {$iRecurso} ";
      }

      $aWhere[]  = "e69_codnota not in (select empnota from plugins.solicitacaorepasseempnota where estornado = false)";
      $aWhere[]  = "(e70_valor - e70_vlranu) = e70_vlrliq";
      $aWhere[]  = "(e70_valor - e70_vlranu) > 0";
      $aWhere[]  = "pagordemele.e53_valor <> pagordemele.e53_vlrpag";

      //Busca para quando a func é chamada e exida.
      if (!isset($pesquisa_chave)) {

        //Filtra pelo número da NF.
        if (isset($chave_e69_numero) && (trim($chave_e69_numero) != "")) {
          $aWhere[] = " e69_numero = '".trim($chave_e69_numero)."' ";
        }

        //Filtra pela data da NF.
        if (isset($chave_e69_dtnota) && (trim($chave_e69_dtnota) != "") ) {

          $e69_dtnota     =  explode("/", $chave_e69_dtnota);
          $e69_dtnota_ano =  $e69_dtnota[2];
          $e69_dtnota_mes =  $e69_dtnota[1];
          $e69_dtnota_dia =  $e69_dtnota[0];
          $aWhere[]       = " e69_dtnota = '".$e69_dtnota_ano."-".$e69_dtnota_mes."-".$e69_dtnota_dia."' ";
        }

        //Filtra pelo sequência do empenho.
        if (isset($chave_e60_numemp) && (trim($chave_e60_numemp) != "")) {
          $aWhere[] = " e60_numemp  = '".trim($chave_e60_numemp)."' ";
        }

        //Filtra pelo número do empenho
        if (isset($chave_e60_codemp) && (trim($chave_e60_codemp) != "")) {

          $arr = split("/", $chave_e60_codemp);
          if (count($arr) == 2  && isset($arr[1]) && $arr[1] != '') {
            $dbwhere_ano = " and e60_anousu = " . $arr[1];
          } else {
            $dbwhere_ano = " and e60_anousu =" . db_getsession("DB_anousu");
          }
          $aWhere[] = " e60_codemp ='{$arr[0]}' {$dbwhere_ano} ";
        }

        //Filtra pelo nome do credor.
        if (isset($chave_z01_nome) && (trim($chave_z01_nome) != "")) {
          $aWhere[] = " z01_nome like '$chave_z01_nome%' ";
        }

        if (!in_array($iAnexo, explode(',', ConfiguracaoRepasseFinanceiro::getAnexoParaRP()))) {
          $aWhere[] = "o58_localizadorgastos = '{$iAnexo}'";             
        }

        //Monta SQL e faz a busca,
        $sql = $clempnota->sql_query_notas("", $sCampos, "", implode(" and ", $aWhere)  . " and e60_anousu >= {$iAnoSessao} ");

        if (in_array($iAnexo, explode(',', ConfiguracaoRepasseFinanceiro::getAnexoParaRP()))) {

          $aWhere[] = "exists (select 1 from empresto where empresto.e91_numemp = empempenho.e60_numemp and empresto.e91_anousu = {$iAnoSessao})";
          $aWhere[] = "c53_tipo in (20)";
 
          $sSqlNotasRP = $clempnota->sql_query_notas("", $sCampos, "", implode(" and ", $aWhere) );
          $sql = "{$sSqlNotasRP} ";
        }

        $sql = "select * from ({$sql}) as x order by x.e69_codnota desc";

        db_lovrot($sql, 100, "()", "", $funcao_js, "", "NoMe", array(), false);



        //Busca quando a func não é exibida e usa a pesquisa_chave.
      } else {

        if ($pesquisa_chave != null && $pesquisa_chave != "") {

          $aWhere[] = "e69_codnota = {$pesquisa_chave}";
          $aWhere[]  = "e69_codnota not in (select empnota from plugins.solicitacaorepasseempnota where estornado = false)";
          $sql = $clempnota->sql_query_notas("", $sCampos, "", implode(' and ', $aWhere) . " and e60_emiss >= '{$iAnoSessao}-01-01' ");
          
          if (!in_array($iAnexo, explode(',', ConfiguracaoRepasseFinanceiro::getAnexoParaRP()))) {
            $aWhere[] = "o58_localizadorgastos = '{$iAnexo}'";             
          }
          if (in_array($iAnexo, explode(',', ConfiguracaoRepasseFinanceiro::getAnexoParaRP()))) {

            $aWhere[] = "exists (select 1 from empresto where empresto.e91_numemp = empempenho.e60_numemp and empresto.e91_anousu = {$iAnoSessao})";
            $aWhere[] = "c53_tipo  in (20) ";
            $sSqlNotasRP = $clempnota->sql_query_notas("", $sCampos, "", implode(" and ", $aWhere) );
            $sql = "{$sSqlNotasRP} ";
          }

          $sql = "select * from ({$sql}) as x order by x.e69_codnota desc";

          $rsResultado = $clempnota->sql_record($sql);

          if ($clempnota->numrows != 0) {

            $oLiquidacao = db_utils::fieldsMemory($rsResultado, 0);
            echo "<script>" . $funcao_js . "('$oLiquidacao->e69_codnota', '$oLiquidacao->dl_código_empenho', '$oLiquidacao->z01_nome', '$oLiquidacao->e70_vlrliq', '$oLiquidacao->mescompetencia', false);</script>";
          } else {
            echo "<script>" . $funcao_js . "('$pesquisa_chave', '', 'Chave(".$pesquisa_chave.") não Encontrado', '', true);</script>";
          }
        } else {
          echo "<script>" . $funcao_js . "('', '', '', '', false);</script>";
        }
      }
      ?>
    </td>
  </tr>
</table>
</body>
</html>