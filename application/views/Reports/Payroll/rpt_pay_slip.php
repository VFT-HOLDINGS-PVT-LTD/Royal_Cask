<?php
// Ensure no output is sent before this point and handle errors properly
ob_start(); // Start output buffering

// Set error reporting for development (remove in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Set timezone to prevent date warnings
date_default_timezone_set('Asia/Colombo'); // Adjust to your timezone

$date = date("Y/m/d");

// Your existing data preparation code here...
// Assume $data_set, $data_cmp, $data_month, $data_year variables are prepared

try {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(5, 5, 5, true); // Reduced margins
    $pdf->SetAutoPageBreak(FALSE); // Disable auto page break for custom layout
    $pdf->SetFont('helvetica', '', 10); // Smaller font size

    $payslips_per_page = 4;
    $current_payslip = 0;
    
    foreach ($data_set as $emp) {
        // Add new page every 4 payslips
        if ($current_payslip % $payslips_per_page == 0) {
            $pdf->AddPage('P', 'A4');
        }
        
        $EmpNo = $emp->EmpNo;
        $emp_allowances = isset($allowances[$EmpNo]) ? $allowances[$EmpNo] : ['fixed'=>[], 'variable'=>[]];
        $emp_deductions = isset($deductions[$EmpNo]) ? $deductions[$EmpNo] : ['fixed'=>[], 'variable'=>[]];

        $total_allowances = 0;
        $total_deductions = 0;
        $earnings_html = '';
        foreach (['fixed', 'variable'] as $type) {
            if (!empty($emp_allowances[$type])) {
                foreach ($emp_allowances[$type] as $alw) {
                    if (isset($alw->Allowance_name) && isset($alw->Amount)) {
                        $earnings_html .= '<tr>
                            <td>' . htmlspecialchars($alw->Allowance_name) . '</td>
                            <td class="amount">' . number_format((float)$alw->Amount, 2) . '</td>
                        </tr>';
                        $total_allowances += (float)$alw->Amount;
                    }
                }
            }
        }
        $deductions_html = '';
        foreach (['fixed', 'variable'] as $type) {
            if (!empty($emp_deductions[$type])) {
                foreach ($emp_deductions[$type] as $ded) {
                    if (isset($ded->Deduction_name) && isset($ded->Amount)) {
                        $deductions_html .= '<tr>
                            <td>' . htmlspecialchars($ded->Deduction_name) . '</td>
                            <td class="amount">' . number_format((float)$ded->Amount, 2) . '</td>
                        </tr>';
                        $total_deductions += (float)$ded->Amount;
                    }
                }
            }
        }

        $basic_salary = isset($emp->Basic_sal) ? (float)$emp->Basic_sal : 0;
        $Normal_OT_Pay = isset($emp->Normal_OT_Pay) ? (float)$emp->Normal_OT_Pay : 0;
        $Allowance_1 = isset($emp->Allowance_1) ? (float)$emp->Allowance_1 : 0;
        $Incentive = isset($emp->Incentive) ? (float)$emp->Incentive : 0;
        $Br_pay_Data = isset($emp->Br_pay) ? (float)$emp->Br_pay : 0;
        $Gross_pay_Data = isset($emp->Gross_pay) ? (float)$emp->Gross_pay : 0;
        $salary_advance = isset($emp->Salary_advance) ? (float)$emp->Salary_advance : 0;
        $no_pay_deduction = isset($emp->no_pay_deduction) ? (float)$emp->no_pay_deduction : 0;
        $Late_deduction = isset($emp->Late_deduction) ? (float)$emp->Late_deduction : 0;
        $Ed_deduction = isset($emp->Ed_deduction) ? (float)$emp->Ed_deduction : 0;
        $payee_amount = isset($emp->Payee_amount) ? (float)$emp->Payee_amount : 0;
        $epf_worker = isset($emp->EPF_Worker_Amount) ? (float)$emp->EPF_Worker_Amount : 0;
        $epf_employer = isset($emp->EPF_Employee_Amount) ? (float)$emp->EPF_Employee_Amount : 0;
        $etf_amount = isset($emp->ETF_Amount) ? (float)$emp->ETF_Amount : 0;
        $stamp_duty = isset($emp->Stamp_duty) ? (float)$emp->Stamp_duty : 0;
        $total_all_deductions = isset($emp->tot_deduction) ? (float)$emp->tot_deduction :
            ($total_deductions + $salary_advance + $no_pay_deduction + $Late_deduction + $Ed_deduction + $payee_amount + $epf_worker + $stamp_duty);
        $net_salary = isset($emp->Net_salary) ? (float)$emp->Net_salary :
            (($basic_salary + $Normal_OT_Pay + $Incentive + $total_allowances) - $total_all_deductions);
        $pay_period = date('F Y', mktime(0, 0, 0, $data_month, 1, $data_year));

        // Calculate position for each payslip (2x2 grid)
        $position_in_page = $current_payslip % $payslips_per_page;
        $col = $position_in_page % 2; // 0 or 1
        $row = intval($position_in_page / 2); // 0 or 1
        
        // Calculate X and Y positions
        $x = 5 + ($col * 100); // 5mm margin + 100mm width for each column
        $y = 5 + ($row * 140); // 5mm margin + 140mm height for each row

        $html = '
        <style>
        /* Compact styles for 4 payslips per page */
        body {
            font-family: "Helvetica", sans-serif;
            font-size: 9pt;
            line-height: 1.2;
            color: #333;
        }
        
        .payslip-container {
            width: 95mm;
            height: 135mm;
            border: 1px solid #ccc;
            padding: 2mm;
            margin: 0;
        }
        
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 3px;
            text-align: center;
            margin-bottom: 3px;
            border-radius: 2px;
        }
        .header h1 {
            font-size: 8px;
            margin: 1px 0;
            font-weight: bold;
        }
        .header p {
            font-size: 6px;
            margin: 1px 0;
        }
        
        .pay-period {
            background-color: #3498db;
            color: white;
            padding: 2px 3px;
            font-weight: bold;
            text-align: center;
            border-radius: 2px;
            margin-bottom: 3px;
            font-size: 6px;
        }
        
        .employee-info {
            background-color: #f8f9fa;
            padding: 3px;
            border-radius: 2px;
            margin-bottom: 3px;
            font-size: 5px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
            font-size: 7px;
        }
        .table th {
            background-color: #f1f1f1;
            padding: 1px;
            text-align: left;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            font-size: 7px;
        }
        .table td {
            padding: 1px;
            border-bottom: 1px solid #eee;
            font-size: 7px;
        }
        .amount {
            text-align: right;
            font-family: helvetica, helvetica;
            white-space: nowrap;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        
        .net-salary {
            background-color: #2c3e50;
            color: white;
            padding: 2px;
            text-align: center;
            font-size: 7px;
            font-weight: bold;
            border-radius: 2px;
            margin: 2px 0;
        }
        
        .divider {
            border-top: 1px dashed #ddd;
            margin: 2px 0;
        }
        
        .summary-box {
            border: 1px solid #ddd;
            border-radius: 2px;
            padding: 2px;
            margin-top: 2px;
            background-color: #f9f9f9;
            font-size: 5px;
        }
        
        .footer {
            font-size: 4px;
            text-align: center;
            color: #777;
            margin-top: 2px;
            padding-top: 1px;
            border-top: 1px solid #eee;
        }
        </style>
        <div class="payslip-container">
            <div class="header">
                <h1>' . htmlspecialchars($data_cmp[0]->Company_Name) . '</h1>
                <p>Employee Payslip</p>
            </div>
            <div class="pay-period">
                Pay Period: ' . htmlspecialchars($pay_period) . '
            </div>
            <div class="employee-info">
                <table width="100%">
                    <tr>
                        <td width="50%"><strong>Employee Name:</strong> ' . htmlspecialchars($emp->Emp_Full_Name) . '</td>
                        <td width="50%"><strong>Employee ID:</strong> ' . htmlspecialchars($emp->EmpNo) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Department:</strong> ' . htmlspecialchars($emp->Dep_Name) . '</td>
                        <td><strong>Branch:</strong> ' . htmlspecialchars($emp->B_name) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Position:</strong> ' . (isset($emp->Position) ? htmlspecialchars($emp->Position) : 'N/A') . '</td>
                        <td><strong>Generated on:</strong> ' . htmlspecialchars(date('d M Y, h:i A')) . '</td>
                    </tr>
                </table>
            </div>
            
            <table class="table">
                <tr>
                    <th width="60%">Earnings</th>
                    <th class="amount" width="40%">Amount (Rs.)</th>
                </tr>
                <tr>
                    <td>Basic Salary</td>
                    <td class="amount">' . number_format($basic_salary, 2) . '</td>
                </tr>
                <tr>
                    <td>BR</td>
                    <td class="amount">' . number_format($Br_pay_Data, 2) . '</td>
                </tr>
                <tr>
                    <td>Incentive</td>
                    <td class="amount">' . number_format($Incentive, 2) . '</td>
                </tr>
                
                <tr>
                    <td>Bata</td>
                    <td class="amount">' . number_format($Allowance_1, 2) . '</td>
                </tr>
                <tr>
                    <td>OT</td>
                    <td class="amount">' . number_format($Normal_OT_Pay, 2) . '</td>
                </tr>
                ' . $earnings_html . '
                <tr class="total-row">
                    <td><strong>Total Earnings</strong></td>
                    <td class="amount"><strong>' . number_format($Gross_pay_Data, 2) . '</strong></td>
                </tr>
            </table>
            
            <div class="divider"></div>
            
            <table class="table">
                <tr>
                    <th width="60%">Deductions</th>
                    <th class="amount" width="40%">Amount (Rs.)</th>
                </tr>
                ' . $deductions_html . '
                <tr>
                    <td>Salary Advance</td>
                    <td class="amount">' . number_format($salary_advance, 2) . '</td>
                </tr>
                <tr>
                    <td>Late Deduction</td>
                    <td class="amount">' . number_format($Late_deduction, 2) . '</td>
                </tr>
                <tr>
                    <td>ED Deduction</td>
                    <td class="amount">' . number_format($Ed_deduction, 2) . '</td>
                </tr>
                <tr>
                    <td>NoPay Deduction</td>
                    <td class="amount">' . number_format($no_pay_deduction, 2) . '</td>
                </tr>
                <tr>
                    <td>PAYE Tax</td>
                    <td class="amount">' . number_format($payee_amount, 2) . '</td>
                </tr>
                <tr>
                    <td>EPF (8%)</td>
                    <td class="amount">' . number_format($epf_worker, 2) . '</td>
                </tr>
                <tr>
                    <td>Stamp Duty</td>
                    <td class="amount">' . number_format($stamp_duty, 2) . '</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Total Deductions</strong></td>
                    <td class="amount"><strong>' . number_format($total_all_deductions, 2) . '</strong></td>
                </tr>
            </table>
            
            <div class="divider"></div>
            
            <table class="table">
                <tr>
                    <th width="60%">Description</th>
                    <th class="amount" width="40%">Amount (Rs.)</th>
                </tr>
                <tr>
                    <td>EPF (12%)</td>
                    <td class="amount">' . number_format($epf_employer, 2) . '</td>
                </tr>
                <tr>
                    <td>ETF (3%)</td>
                    <td class="amount">' . number_format($etf_amount, 2) . '</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Total Employer Contributions</strong></td>
                    <td class="amount"><strong>' . number_format($epf_employer + $etf_amount, 2) . '</strong></td>
                </tr>
            </table>
           
            <table class="table" width="100%">
                <tr>
                    <td width="60%"><strong>Gross Earnings:</strong></td>
                    <td class="amount" width="40%"><strong>Rs. ' . number_format($Gross_pay_Data, 2) . '</strong></td>
                </tr>
                <tr>
                    <td><strong>Total Deductions:</strong></td>
                    <td class="amount"><strong>Rs. ' . number_format($total_all_deductions, 2) . '</strong></td>
                </tr>
            </table>

            <div class="net-salary">
                Net Salary: Rs. ' . number_format($net_salary, 2) . '
            </div>
            
            
        </div>';

        // Write HTML at the calculated position
        $pdf->writeHTMLCell(95, 135, $x, $y, $html, 0, 0, false, true, '', true);
        
        $current_payslip++;
    }

    ob_end_clean();
    $pdf->Output('Payslips_' . date('F_Y', mktime(0, 0, 0, $data_month, 1, $data_year)) . '_4per_page.pdf', 'I');
} catch (Exception $e) {
    ob_end_clean();
    echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Error</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 50px; }
                .error-box { border: 1px solid #f5c6cb; background-color: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; }
                h2 { margin-top: 0; }
                .back-btn { margin-top: 20px; }
                .back-btn a { background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="error-box">
                <h2>Error Generating Payslip</h2>
                <p>We\'re sorry, but there was an error generating your payslip. Please try again later or contact technical support.</p>
                <p>Error reference: ' . time() . '</p>
                <div class="back-btn">
                    <a href="javascript:history.back()">Go Back</a>
                </div>
            </div>
        </body>
        </html>';
    exit;
}
?>