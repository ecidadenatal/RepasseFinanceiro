<?php
/**
 * Class ConfiguracaoRepasseFinanceiro
 */
class ConfiguracaoRepasseFinanceiro {

  private function __construct(){}
  private function __clone(){}

  /**
   * @type null|array
   */
  private static $aConfiguracao = null;

  /**
   * @return array|null
   */
  private static function getArquivoConfiguracao() {

    if (empty(self::$aConfiguracao)) {

      $oPlugin = new Plugin(null, 'RepasseFinanceiro');
      $aConfiguracao = PluginService::getPluginConfig($oPlugin);
      self::$aConfiguracao = $aConfiguracao;
    }
    return self::$aConfiguracao;
  }

  /**
   * Retorna o hist�rico padr�o para o cadastro do Slip
   * @return integer
   */
  public static function getHistorico() {

    $aConfiguracao = self::getArquivoConfiguracao();
    return $aConfiguracao['historico_padrao_autorizacao'];
  }

  /**
   * Retorna o hist�rico padr�o para o cadastro do Slip de Devolu��o
   * @return integer
   */
  public static function getHistoricoDevolucao() {

    $aConfiguracao = self::getArquivoConfiguracao();
    return trim($aConfiguracao['historico_padrao_devolucao']);
  }

  /**
   * Conta pagadora padr�o para a autoriza��o de repasse
   * @return string
   */
  public static function getContaPagadora() {

    $aConfiguracao = self::getArquivoConfiguracao();
    return $aConfiguracao['conta_pagadora_padrao'];
  }

  /**
   * Retorna os recursos que exigem que seja informada liquida��o
   * @return string
   */
  public static function getRecursoLiquidacaoObrigatoria() {

    $aConfiguracao = self::getArquivoConfiguracao();
    return $aConfiguracao['exige_liquidacao_recurso'];
  }

  /**
   * Retorna os anexos que n�o exigem que seja informada liquida��o
   * @return string
   */
  public static function getAnexoLiquidacaoNaoObrigatoria() {

    $aConfiguracao = self::getArquivoConfiguracao();
    return $aConfiguracao['nao_exige_liquidacao_anexo'];
  }

  /**
   * Retorna as unidades que n�o exigem que seja informada liquida��o
   * @return string
   */
  public static function getUnidadeLiquidacaoNaoObrigatoria() {

    $aConfiguracao = self::getArquivoConfiguracao();
    return $aConfiguracao['nao_exige_liquidacao_orgao_unidade'];
  }

  /**
   * Retorna os Anexos que devem trazer liquida��es de RP
   * @return string
   */
  public static function getAnexoParaRP() {

    $aConfiguracao = self::getArquivoConfiguracao();
    return $aConfiguracao['anexo_restos_a_pagar'];
  }

  /**
   * @return stdClass[]
   */
  public static function getFiltrosOrgaoUnidade() {

    $aWhere = array();
    $aConfiguracaoOrgaoUnidade = explode(',', self::getUnidadeLiquidacaoNaoObrigatoria());
    foreach ($aConfiguracaoOrgaoUnidade as $sOrgaoUnidade) {

      if (strlen($sOrgaoUnidade) < 3) {
        continue;
      }

      $oStdOrgaoUnidade          = new stdClass();
      $oStdOrgaoUnidade->orgao   = substr($sOrgaoUnidade, 0, 2);
      $oStdOrgaoUnidade->unidade = substr($sOrgaoUnidade, 2, 2);
      $aWhere[] = $oStdOrgaoUnidade;
    }
    return $aWhere;
  }

  /**
   * Retorna verdadeiro se exige liquida��o para o �rg�o, unidade, recurso e anexo informados.
   *
   * @param  integer $iOrgao   �rg�o
   * @param  integer $iUnidade Unidade
   * @param  integer $iRecurso Recurso
   * @param  integer $iAnexo   Anexo
   * @return boolean
   */
  public static function exigeLiquidacao($iOrgao, $iUnidade, $iRecurso, $iAnexo) {

    $aFiltrosOrgaoUnidadePlugin = self::getFiltrosOrgaoUnidade();
    $aFiltrosRecurso            = explode(',', self::getRecursoLiquidacaoObrigatoria());
    $aFiltrosAnexo              = explode(',', self::getAnexoLiquidacaoNaoObrigatoria());

    $lEmRecurso         = false;
    $lNaoEmOrgaoUnidade = true;
    $lNaoEmAnexo        = true;

    /**
     * Exige liquida��o para os recursos configurados.
     */
    if (in_array($iRecurso, $aFiltrosRecurso)) {
      $lEmRecurso = true;
    }

    /**
     * N�o exige liquida��o para �rg�o e unidade configurados.
     */
    foreach ($aFiltrosOrgaoUnidadePlugin as $oFiltro) {

      if ($oFiltro->orgao == $iOrgao && $oFiltro->unidade == $iUnidade) {

        $lNaoEmOrgaoUnidade = false;
        break;
      }
    }

    /**
     * N�o exige liquida��o para os anexos configurados.
     */
    if (in_array($iAnexo, $aFiltrosAnexo)) {
      $lNaoEmAnexo = false;
    }

    /**
     * S� exige liquida��o se todas as regras forem verdadeiras.
     */
    return ($lEmRecurso && $lNaoEmOrgaoUnidade && $lNaoEmAnexo);
  }

}
