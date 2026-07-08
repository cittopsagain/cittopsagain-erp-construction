<?php

namespace Applications\Projects\Modules\Quotations\Controllers;

use Core\Controller;

/**
 * Export Controller for Quotations
 * Handles PDF generation and other export-related tasks.
 */
class Export extends Controller
{
    /**
     * Export quotation to PDF.
     */
    public function pdf()
    {
        $header_id = $_GET['id'] ?? null;
        if (!$header_id) {
            die("ID is required.");
        }

        $model = $this->model('Projects', 'Quotations', 'Quotations');
        $header = $model->getHeader($header_id);
        if (!$header) {
            die("Quotation not found.");
        }

        $details = $model->getDetails($header_id);
        $terms = $model->getTerms($header_id);
        $currentUserFromSession = $model->getCurrentUserFromSession();

        $valid_days = 15;
        foreach ($terms as $term) {
            if (preg_match('/(\d+)\s*days\s*upon\s*receipt\s*of\s*quotation/i', $term['description'], $matches)) {
                $valid_days = (int)$matches[1];
                break;
            }
        }

        // Create new PDF document using the legacy TCPDF (found in library/tcpdf)
        require_once 'library/tcpdf/tcpdf.php';

        $orientation = defined('PDF_PAGE_ORIENTATION') ? PDF_PAGE_ORIENTATION : 'P';
        $unit = defined('PDF_UNIT') ? PDF_UNIT : 'mm';
        $format = defined('PDF_PAGE_FORMAT') ? PDF_PAGE_FORMAT : 'A4';

        $pdf = new \TCPDF($orientation, $unit, $format, true, 'UTF-8', false);

        // Set default font
        $pdf->SetFont('helvetica', '', 9);

        // Set document information
        $pdf->setCreator(APP_NAME);
        $pdf->setAuthor(APP_NAME);
        $pdf->setTitle('Quotation ' . $header['quot_ctrl_no']);

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set default monospaced font
        if (defined('PDF_FONT_MONOSPACED')) {
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        }

        // Set margins
        $pdf->setMargins(15, 15, 15);

        // Set auto page breaks
        $pdf->setAutoPageBreak(TRUE, 20);

        // Add a page
        $pdf->AddPage();

        // Colors
        $primaryColor = '#2c3e50';
        $secondaryColor = '#34495e';
        $accentColor = '#FFC000';
        $textColor = '#333333';
        $lightGray = '#f8f9fa';
        $borderGray = '#dee2e6';

        // Header Section
        $logo = 'public/assets/images/logo_no_alpha.png';

        $html = '
        <style>
            table { border-collapse: collapse; }
            .header-table td { vertical-align: middle; }
            .company-name { font-size: 16pt; font-weight: bold; color: ' . $primaryColor . '; }
            .company-info { font-size: 8pt; color: #555; }
            .quotation-title { font-size: 20pt; font-weight: bold; color: ' . $primaryColor . '; text-align: right; }
            .info-box { border: 1px solid ' . $borderGray . '; background-color: ' . $lightGray . '; }
            .label { font-weight: bold; color: ' . $secondaryColor . '; font-size: 8pt; }
            .value { font-size: 9pt; }
            .section-title { font-size: 10pt; font-weight: bold; color: #fff; background-color: ' . $primaryColor . '; padding: 5px; }
            .boq-table th { background-color: ' . $accentColor . '; font-weight: bold; text-align: center; font-size: 8pt; border: 1px solid #000; }
            .boq-table td { border: 0.1pt solid ' . $borderGray . '; font-size: 8pt; vertical-align: middle; }
            .component-row { background-color: #eee; font-weight: bold; }
            .total-row td { font-weight: bold; font-size: 9pt; }
            .grand-total-row td { font-weight: bold; font-size: 11pt; background-color: ' . $accentColor . '; }
            .signature-box { text-align: center; }
            .signature-line { border-top: 1px solid #000; width: 80%; margin: 10px auto 0; }
        </style>

        <table class="header-table" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td width="15%">
                    <img src="' . $logo . '" width="80"/>
                </td>
                <td width="45%">
                    <span class="company-name">' . APP_NAME . '</span><br/>
                    <span class="company-info">' . COMPANY_SERVICES_OFFERED . '</span><br/>
                    <span class="company-info">' . COMPANY_ADDRESS . '</span><br/>
                    <span class="company-info">Tel: ' . COMPANY_CONTACT . ' | Email: ' . COMPANY_EMAIL . '</span>
                </td>
                <td width="40%" align="right">
                    <span class="quotation-title">QUOTATION</span><br/>
                    <table cellpadding="4" align="right">
                        <tr>
                            <td align="right" class="label">DATE:</td>
                            <td align="left" class="value">' . date('M d, Y', strtotime($header['date_created'])) . '</td>
                        </tr>
                        <tr>
                            <td align="right" class="label">QUOTATION NO:</td>
                            <td align="left" class="value"><b>' . $header['quot_ctrl_no'] . '</b></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div style="line-height: 0.5;">&nbsp;</div>

        <table width="100%" cellpadding="5">
            <tr>
                <td width="60%">
                    <table width="100%" cellpadding="3" class="info-box">
                        <tr><td class="section-title">CLIENT INFORMATION</td></tr>
                        <tr>
                            <td>
                                <span class="label">CLIENT:</span> <span class="value">' . $header['client_name'] . '</span><br/>
                                <span class="label">ATTENTION:</span> <span class="value">' . ($header['contact_person'] ?? 'Valued Client') . '</span><br/>
                                <span class="label">ADDRESS:</span> <span class="value">' . ($header['add1'] ?: $header['add2'] ?: 'As specified') . '</span>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="40%">
                    <table width="100%" cellpadding="3" class="info-box">
                        <tr><td class="section-title">PROJECT DETAILS</td></tr>
                        <tr>
                            <td>
                                <span class="label">PROJECT:</span> <span class="value">' . ($header['project_name'] ?? 'N/A') . '</span><br/>
                                <span class="label">REF NO:</span> <span class="value">' . ($header['project_type_desc'] ?? 'General Construction') . '</span><br/>
                                <span class="label">VALID UNTIL:</span> <span class="value">' . date('M d, Y', strtotime($header['date_created'] . ' + ' . $valid_days . ' days')) . '</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div style="line-height: 1;">&nbsp;</div>
        <p style="font-size: 9pt;">Dear ' . ($header['contact_person'] ?? 'Valued Client') . ',<br/><br/>
        We are pleased to submit our quotation for the <b>' . ($header['project_name'] ?? 'above mentioned project') . '</b> as per requested:</p>

        <table class="boq-table" cellpadding="4" width="100%">
            <thead>
                <tr>
                    <th width="5%">ITEM</th>
                    <th width="50%">DESCRIPTION</th>
                    <th width="10%">QTY</th>
                    <th width="10%">UNIT</th>
                    <th width="12%">UNIT PRICE</th>
                    <th width="13%">AMOUNT</th>
                </tr>
            </thead>
            <tbody>';

        // Group items by type and component
        $grouped_items = [];
        foreach ($details as $item) {
            $type = $item['detail_type'] ?? 'BOQ';
            $component_name = $item['component_description'] ?: 'OTHERS';
            $grouped_items[$type][$component_name][] = $item;
        }

        $subtotal = 0;
        $boq_items = $grouped_items['BOQ'] ?? [];

        $comp_index = 'A';
        foreach ($boq_items as $component_name => $items) {
            // Component Header Row
            $html .= '<tr class="component-row">
                <td align="center"><b>' . $comp_index . '</b></td>
                <td colspan="5"><b>' . strtoupper($component_name) . '</b></td>
            </tr>';

            foreach ($items as $index => $item) {
                $markup = $item['markup_percent'] ?? 0;
                $unit_selling_price = $item['price'] * (1 + $markup / 100);
                $amount = $item['qty'] * $unit_selling_price;
                $subtotal += $amount;

                $html .= '<tr>
                    <td align="center">' . ($index + 1) . '</td>
                    <td align="left">' . ($item['item_desc'] ?: 'Item') . '</td>
                    <td align="center">' . $item['qty'] . '</td>
                    <td align="center">' . ($item['unit_description'] ?: $item['unit_code']) . '</td>
                    <td align="right">' . number_format($unit_selling_price, 2) . '</td>
                    <td align="right">' . number_format($amount, 2) . '</td>
                </tr>';
            }
            $comp_index++;
        }

        // Add Materials, Labor, Overhead to subtotal if they exist
        foreach (['MATERIAL', 'LABOR', 'OVERHEAD'] as $type) {
            if (isset($grouped_items[$type])) {
                foreach ($grouped_items[$type] as $comp => $items) {
                    foreach ($items as $item) {
                        if ($type === 'LABOR') {
                            $subtotal += ($item['no_of_men'] * $item['days'] * $item['hours'] * $item['price']) +
                                ($item['no_of_men'] * $item['ot_hrs'] * $item['ot_rate']);
                        } else {
                            $subtotal += ($item['qty'] * $item['price']);
                        }
                    }
                }
            }
        }

        $discount_amount = ($header['discount'] / 100) * $subtotal;
        $net_total = $subtotal - $discount_amount;
        $vat = $net_total * 0.12;
        $grand_total = $net_total + $vat;

        $html .= '
            <tr class="total-row">
                <td colspan="4" rowspan="4" style="border: none;"></td>
                <td align="right">SUBTOTAL</td>
                <td align="right">' . number_format($subtotal, 2) . '</td>
            </tr>';

        if ($header['discount'] > 0) {
            $html .= '<tr class="total-row">
                <td align="right">DISCOUNT (' . $header['discount'] . '%)</td>
                <td align="right">-' . number_format($discount_amount, 2) . '</td>
            </tr>';
        } else {
            $html .= '<tr class="total-row">
                <td align="right" style="color: #fff; background-color: #fff;">-</td>
                <td align="right" style="color: #fff; background-color: #fff;">-</td>
            </tr>';
        }

        $html .= '
            <tr class="total-row">
                <td align="right">VAT 12%</td>
                <td align="right">' . number_format($vat, 2) . '</td>
            </tr>
            <tr class="grand-total-row">
                <td align="right">TOTAL PHP</td>
                <td align="right">' . number_format($grand_total, 2) . '</td>
            </tr>
        </tbody>
        </table>
        
        <div style="line-height: 1;">&nbsp;</div>';

        // Terms and Conditions
        if (!empty($terms)) {
            $html .= '<table cellpadding="3" width="100%">
                <tr><td class="section-title">TERMS & CONDITIONS</td></tr>
                <tr>
                    <td style="border: 1px solid ' . $borderGray . '; font-size: 8pt;">';
            foreach ($terms as $term) {
                $html .= '<b>&bull; ' . $term['section'] . ':</b> ' . $term['description'] . '<br>';
            }
            $html .= '</td></tr></table><div style="line-height: 1;">&nbsp;</div>';
        }

        // Signatures
        $html .= '
        <table cellpadding="5" width="100%" style="font-size: 9pt;">
            <tr>
                <td width="33%" class="signature-box">
                    <span>Prepared by:</span><br/><br/><br/>
                    <div class="signature-line"></div>
                    <b>' . strtoupper($header['prepared_by']) . '</b><br/>
                    <span style="font-size: 8pt;">Operations Manager</span>
                </td>
                <td width="33%" class="signature-box">
                    <span>Approved by:</span><br/><br/><br/>
                    <div class="signature-line"></div>
                    <b>ENGR. JERALD OBENITA</b><br/>
                    <span style="font-size: 8pt;">General Manager</span>
                </td>
                <td width="33%" class="signature-box">
                    <span>Conformed by:</span><br/><br/><br/>
                    <div class="signature-line"></div>
                    <br/>
                    <span style="font-size: 8pt;">Client Representative</span>
                </td>
            </tr>
        </table>';

        $pdf->writeHTML($html);

        // Close and output PDF document
        $pdf->Output('Quotation_' . $header['quot_ctrl_no'] . '.pdf', 'I');
        exit;
    }
}
