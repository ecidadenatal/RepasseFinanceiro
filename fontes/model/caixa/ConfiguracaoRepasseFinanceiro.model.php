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
   * Retorna o histórico padrão para o cadastro do Slip
   * @return integer
   */
  public static function getHistorico() {

    $aConfiguracao = self::getArquivoConfiguracao();
    return $aConfiguracao['historico_padrao_autorizacao'];
  }

  /**
   * Retorna o histórico padrão para o cadastro do Slip de Devolução
   * @return integer
   */
  public static function getHistoricoDevolucao() {

    $aConfiguracao = self::getArquivoConfiguracao();
    return trim($aConfiguracao['historico_padrao_devolucao']);
  }

  /**
   * Conta pagadora padrão para a autorização de repasse
   * @return string
   */
  public static function getContaPagadora() {

    $aConfiguracao = self::getArquivoConfiguracao();
    return $aConfiguracao['conta_pagadora_padrao'];
  }

  /**
   * Retorna os recursos que exigem que seja informada liquidação
   * @return string
   */
  public static function getRecursoLiquidacaoObrigatoria() {

    $aConfiguracao = self::getArquivoConfiguracao();
    return $aConfiguracao['exige_liquidacao_recurso'];
  }

  /**
   * Retorna os anexos que não exigem que seja informada liquidação
   * @return string
   */
  public static function getAnexoLiquidacaoNaoObrigatoria() {

    $aConfiguracao = self::getArquivoConfiguracao();
    return $aConfiguracao['nao_exige_liquidacao_anexo'];
  }

  /**
   * Retorna as unidades que não exigem que seja informada liquidação
   * @return string
   */
  public static function getUnidadeLiquidacaoNaoObrigatoria() {

    $aConfiguracao = self::getArquivoConfiguracao();
    return $aConfiguracao['nao_exige_liquidacao_orgao_unidade'];
  }

  /**
   * Retorna os Anexos que devem trazer liquidações de RP
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
   * Retorna verdadeiro se exige liquidação para o órgão, unidade, recurso e anexo informados.
   *
   * @param  integer $iOrgao   Órgão
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
     * Exige liquidação para os recursos configurados.
     */
    if (in_array($iRecurso, $aFiltrosRecurso)) {
      $lEmRecurso = true;
    }

    /**
     * Não exige liquidação para órgão e unidade configurados.
     */
    foreach ($aFiltrosOrgaoUnidadePlugin as $oFiltro) {

      if ($oFiltro->orgao == $iOrgao && $oFiltro->unidade == $iUnidade) {

        $lNaoEmOrgaoUnidade = false;
        break;
      }
    }

    /**
     * Não exige liquidação para os anexos configurados.
     */
    if (in_array($iAnexo, $aFiltrosAnexo)) {
      $lNaoEmAnexo = false;
    }

    /**
     * Só exige liquidação se todas as regras forem verdadeiras.
     */
    return ($lEmRecurso && $lNaoEmOrgaoUnidade && $lNaoEmAnexo);
  }

}
