<?php
require_once(modification("fpdf151/PDFDocument.php"));
require_once(modification("fpdf151/PDFTable.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_utils.php"));

$oGet = db_utils::postMemory($_GET);

$aAutorizacoes = explode(',', $oGet->autorizacao);

$oPDFTable = new PDFTable(PDFDocument::PRINT_PORTRAIT);
$oPDFTable->setHeaders(array("Solicitação de Repasse", "Slip"));
$oPDFTable->setColumnsWidth(array('50%', '50%'));
$oPDFTable->setPercentWidth(true);
$oPDFTable->setColumnsAlign(array(PDFDocument::ALIGN_CENTER, PDFDocument::ALIGN_CENTER));
$oPDFTable->addHeaderDescription("SLIPS DE SOLICITAÇÃO DE REPASSE FINANCEIRO");

foreach ($aAutorizacoes as $iCodigoAutorizacao) {

  $oAutorizacao = AutorizacaoSolicitacaoRepasse::getInstanciaPorCodigo($iCodigoAutorizacao);
  $oPDFTable->addLineInformation(
    array(
      $oAutorizacao->getSolicitacao()->getCodigo(),
      $oAutorizacao->getTransferencia()->getCodigoSlip()
    )
  );
}

$oPdf = new PDFDocument(PDFDocument::PRINT_PORTRAIT);
$oPdf->disableFooterDefault();
$oPdf->SetFontSize(8);
$oPdf->SetFillColor(235);
$oPdf->open();
$oPDFTable->printOut($oPdf);
