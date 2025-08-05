<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payroll_Process extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('login_user')) {
            redirect(base_url());
        }
        $this->load->model('Db_model', 'Db_model', true);
        $this->load->helper('url');
    }

    public function index()
    {
        $data['title'] = "Payroll Process | HRM SYSTEM";
        $data['data_emp'] = $this->Db_model->getData('EmpNo, Emp_Full_Name', 'tbl_empmaster');
        $this->load->view('Payroll/Payroll_process/index', $data);
    }

    public function emp_payroll_process()
    {
        date_default_timezone_set('Asia/Colombo');

        $month = (int) $this->input->post('cmb_month');
        $year = (int) $this->input->post('cmb_year');

        $employees = $this->Db_model->getfilteredData("
            SELECT EmpNo, Dep_ID,Des_ID,Grp_ID, Basic_Salary, BR1, BR2, is_nopay_calc, Is_EPF, Emp_Full_Name,Incentive
            FROM tbl_empmaster 
            WHERE Status = 1 AND Active_process = 1
        ");

        $payee_slabs = $this->Db_model->getfilteredData("SELECT * FROM tbl_payee ORDER BY id ASC");

        $finalPayroll = [];


        foreach ($employees as $emp) {

            $EmpNo = (int) $emp->EmpNo;
            $Dep_ID = (int) $emp->Dep_ID;
            $Des_ID = (int) $emp->Des_ID;
            $Grp_ID = (int) $emp->Grp_ID;

            $HasmsRow_MS = $this->Db_model->getfilteredData("select COUNT(tbl_individual_roster.EmpNo) AS misspunch from tbl_individual_roster where EmpNo='$EmpNo' 
                and EXTRACT(MONTH FROM FDate)='$month' and EXTRACT(YEAR FROM FDate)='$year' AND DayStatus = 'MS'");
            if ($HasmsRow_MS[0]->misspunch == 0) {
                // echo $EmpNo;
                // Get Department Name
                $dep = $this->Db_model->getfilteredData("SELECT Dep_Name FROM tbl_departments WHERE Dep_ID = $Dep_ID");
                $DepName = isset($dep[0]->Dep_Name) ? $dep[0]->Dep_Name : 'Unknown';

                $setting = $this->Db_model->getfilteredData("SELECT * FROM tbl_payroll_setting INNER JOIN tbl_emp_group ON tbl_payroll_setting.Group_Id = tbl_emp_group.Grp_ID WHERE tbl_emp_group.Grp_ID = '$Grp_ID'");
                $daily_rate = $setting[0]->Daily_Rate;

                // Get NoPay
                $nopay = $this->Db_model->getfilteredData("
                SELECT SUM(nopay) AS nopay 
                FROM tbl_individual_roster 
                WHERE EmpNo = $EmpNo 
                  AND EXTRACT(MONTH FROM FDate) = $month 
                  AND EXTRACT(YEAR FROM FDate) = $year 
                  AND ShType = 'DU'
            ");
                $NopayDays = isset($nopay[0]->nopay) ? (float) $nopay[0]->nopay : 0;

                // Get Salary Advance
                $advance = $this->Db_model->getfilteredData("
                SELECT Amount 
                FROM tbl_salary_advance 
                WHERE Is_Approve = 1 AND EmpNo = $EmpNo 
                  AND month = $month AND year = $year
            ");
                $AdvanceAmount = isset($advance[0]->Amount) ? (float) $advance[0]->Amount : 0;

                //Get Variable Allowances
                // $variable_allowances = $this->Db_model->getfilteredData("
                //     SELECT *
                //     FROM tbl_varialble_allowance 
                //     WHERE EmpNo = $EmpNo AND Month = $month AND Year = $year
                // ");
                // $VariableAllowancesAmount = isset($variable_allowances[0]->Amount) ? (float) $variable_allowances[0]->Amount : 0;

                $variable_allowances = $this->Db_model->getfilteredData("
                SELECT a.*, b.Allowance_name
                FROM tbl_varialble_allowance a
                JOIN tbl_allowance_type b
                ON a.Alw_ID = b.Alw_ID
                WHERE a.EmpNo = $EmpNo AND a.Month = $month AND a.Year = $year
            ");

                $VariableAllowancesAmount = 0;
                if (!empty($variable_allowances)) {
                    foreach ($variable_allowances as $allowance) {
                        $VariableAllowancesAmount += (float) $allowance->Amount;
                    }
                }

                // Get Fixed Allowances
                $fixed_allowances = $this->Db_model->getfilteredData("
                SELECT a.*, b.Allowance_name
                FROM tbl_fixed_allowance a
                JOIN tbl_allowance_type b
                ON a.Alw_ID = b.Alw_ID
                WHERE a.EmpNo = $EmpNo
            ");
                $FixedAllowancesAmount = 0;
                if (!empty($fixed_allowances)) {
                    foreach ($fixed_allowances as $allowance) {
                        $FixedAllowancesAmount += (float) $allowance->Amount;
                    }
                }

                // Get Variable Deductions
                $variable_deductions = $this->Db_model->getfilteredData("
                SELECT a.*, b.Deduction_name
                FROM tbl_variable_deduction a
                JOIN tbl_deduction_types b
                ON a.Ded_ID = b.Ded_ID
                WHERE a.EmpNo = $EmpNo AND a.Month = $month AND a.Year = $year
            ");
                $VariableDeductionAmount = 0;
                if (!empty($variable_deductions)) {
                    foreach ($variable_deductions as $deduction) {
                        $VariableDeductionAmount += (float) $deduction->Amount;
                    }
                }

                // Get Fixed Deductions
                $fixed_deductions = $this->Db_model->getfilteredData("
                SELECT a.*, b.Deduction_name
                FROM tbl_fixed_deduction a
                JOIN tbl_deduction_types b
                ON a.Deduction_ID = b.Ded_ID
                WHERE a.EmpNo = $EmpNo
            ");
                $FixedDeductionAmount = 0;
                if (!empty($fixed_deductions)) {
                    foreach ($fixed_deductions as $deduction) {
                        $FixedDeductionAmount += (float) $deduction->Amount;
                    }
                }

                // Get Overtime details (Day Overtime)
                $Overtime_DB = $this->Db_model->getfilteredData("
                SELECT SUM(DOT) AS D_OT 
                FROM tbl_individual_roster 
                WHERE EmpNo = '$EmpNo' 
                AND EXTRACT(MONTH FROM FDate) = $month 
                AND EXTRACT(YEAR FROM FDate) = $year
            ");
                // Total Day Overtime hours
                $D_OT_Hours = $Overtime_DB[0]->D_OT ?? 0;

                // Get Overtime details (Night Overtime)
                $Overtime = $this->Db_model->getfilteredData("
                SELECT SUM(AfterExH) AS N_OT 
                FROM tbl_individual_roster 
                WHERE EmpNo = '$EmpNo' 
                AND EXTRACT(MONTH FROM FDate) = $month 
                AND EXTRACT(YEAR FROM FDate) = $year
            ");
                // Total Night Overtime hours
                $N_OT_Hours = $Overtime[0]->N_OT ?? 0;

                // Get Overtime unites
                $Overtime_unites = $this->Db_model->getfilteredData("
                SELECT SUM(MODExtra) AS N_OT_units 
                FROM tbl_individual_roster 
                WHERE EmpNo = '$EmpNo' 
                AND EXTRACT(MONTH FROM FDate) = $month 
                AND EXTRACT(YEAR FROM FDate) = $year
            ");
                // Total Night Overtime hours
                $N_OT_Unites = $Overtime_unites[0]->N_OT_units ?? 0;

                // Get Late Minutes
                $Late_Min = $this->Db_model->getfilteredData("
                SELECT SUM(LateM) AS LateMin 
                FROM tbl_individual_roster 
                WHERE EmpNo = $EmpNo 
                AND EXTRACT(MONTH FROM FDate) = $month 
                AND RYear = $year 
                AND ShType = 'DU'
            ");
                // Total Late Minutes
                $Late_Minutes = $Late_Min[0]->LateMin ?? 0;

                // Get Early Departure Minutes
                $earlyDeparture = $this->Db_model->getfilteredData("
                SELECT SUM(EarlyDepMin) AS ed 
                FROM tbl_individual_roster 
                WHERE EmpNo = $EmpNo 
                AND EXTRACT(MONTH FROM FDate) = $month 
                AND RYear = $year 
                AND ShType = 'DU'
            ");
                // Total Early Departure Minutes
                $Early_Dep_Minutes = $earlyDeparture[0]->ed ?? 0;

                $OT_Results = $this->calculateOvertime($emp->Basic_Salary, $FixedAllowancesAmount, $N_OT_Hours, $D_OT_Hours, $setting);
                $OT_Results_unites = $this->calculateOvertime_unites($emp->Basic_Salary, $FixedAllowancesAmount, $N_OT_Unites, $setting);
                $Late_Amount = $this->calculateLateDeduction($emp->Basic_Salary, $Late_Minutes, $setting);
                $EarlyDep_Amount = $this->calculateEarlyDepartureDeduction($emp->Basic_Salary, $Early_Dep_Minutes, $setting);
                $Festival_Adavance = $this->processFestivalAdvance($EmpNo, $month, $year);
                $Loan_Amount = $this->processLoanInstallments($EmpNo, $month, $year);
                $halfday_day_amount = $this->processHalfdayDeduction($emp->Basic_Salary, $EmpNo, $month, $year);
                $BataAllowance = $this->processBata($EmpNo, $Des_ID, $month, $year);
                // die($Late_Amount . ' - ' . $EarlyDep_Amount . ' - ' . $N_OT_Hours . ' - ' . $D_OT_Hours);

                // Perform Payroll Calculations
                $salaryData = $this->calculate_salary(
                    (float) $emp->Basic_Salary,
                    (float) $emp->BR1,
                    (float) $emp->BR2,
                    (float) $emp->Incentive,
                    $emp->is_nopay_calc,
                    $NopayDays,
                    $AdvanceAmount,
                    $payee_slabs,
                    $VariableAllowancesAmount,
                    $FixedAllowancesAmount,
                    $VariableDeductionAmount,
                    $FixedDeductionAmount,
                    $OT_Results,
                    $Late_Amount,
                    $EarlyDep_Amount,
                    $Festival_Adavance,
                    $Loan_Amount,
                    $halfday_day_amount,
                    $BataAllowance,
                    $OT_Results_unites,
                    $setting

                );

                $salaryData['EmpNo'] = $EmpNo;
                $salaryData['month'] = $month;
                $salaryData['year'] = $year;

                // Remove VariableAllowances and FixedAllowances from the data to be inserted/updated
                unset($salaryData['Allowances'], $salaryData['Deductions']);

                // Check if already exists
                $HasRow = $this->Db_model->getfilteredData("
                SELECT COUNT(*) AS HasRow 
                FROM tbl_salary 
                WHERE EmpNo = $EmpNo AND month = $month AND year = $year
            ");

                if (!empty($HasRow[0]->HasRow)) {
                    // $this->Db_model->updateData('tbl_salary', $salaryData, [
                    //     'EmpNo' => $EmpNo,
                    //     'month' => $month,
                    //     'year' => $year
                    // ]);
                    $this->Db_model->getfilteredDelete("DELETE FROM tbl_salary WHERE Month =  $month and Year =$year  and EmpNo= $EmpNo");
                    $this->Db_model->getfilteredDelete("DELETE FROM tbl_allowance_has_tbl_salary WHERE Month =  $month and Year =$year  and EmpNo= $EmpNo");
                    $this->Db_model->getfilteredDelete("DELETE FROM tbl_deduction_has_tbl_salary WHERE Month =  $month and Year =$year  and EmpNo= $EmpNo");

                    $this->Db_model->insertData('tbl_salary', $salaryData);
                } else {
                    try {
                        $this->Db_model->insertData('tbl_salary', $salaryData);
                    } catch (Exception $e) {
                        log_message('error', 'Payroll insert failed: ' . $e->getMessage());
                    }
                }

                // Handle Variable Allowances
                $this->handle_variable_allowances($EmpNo, $month, $year, $variable_allowances);

                // Handle Fixed Allowances
                $this->handle_fixed_allowances($EmpNo, $month, $year, $fixed_allowances);

                // Handle Variable Deductions
                $this->handle_variable_deductions($EmpNo, $month, $year, $variable_deductions);

                // Handle Fixed Deductions
                $this->handle_fixed_deductions($EmpNo, $month, $year, $fixed_deductions);

                // Push into final array
                $finalPayroll[] = [
                    'EMP_NO' => $EmpNo,
                    'DEP_NAME' => $DepName,
                    'NAME' => $emp->Emp_Full_Name,
                    'BASIC_SALARY' => number_format($salaryData['Basic_sal'], 2),
                    'BR' => number_format($salaryData['Br_pay'], 2),
                    'TOTAL_FOR_EPF' => number_format($salaryData['Total_F_Epf'], 2),
                    'NORMAL_OT_HRS' => number_format($salaryData['Normal_OT_Hrs'], 2),
                    'NORMAL_OT_PAY' => number_format($salaryData['Normal_OT_Pay'], 2),
                    'GROSS_PAY' => number_format($salaryData['Gross_pay'], 2),
                    'NET_SALARY' => number_format($salaryData['Net_salary'], 2),
                    'LATE_DED' => number_format($salaryData['Late_deduction'], 2),
                    'LATE_MIN' => number_format($salaryData['Late_min'], 2),
                    'ED_DED' => number_format($salaryData['Ed_deduction'], 2),
                    'ED_MIN' => number_format($salaryData['Ed_min'], 2),
                    'LOAN_INSTALMENT' => number_format($salaryData['Loan_Instalment'], 2),
                    'FESTIVAL_ADVANCE' => number_format($salaryData['Festivel_Advance'], 2),
                    'TOT_DEDUCTION' => number_format($salaryData['tot_deduction'], 2),
                    'EPF_8' => number_format($salaryData['EPF_Worker_Amount'], 2),
                    'EPF_12' => number_format($salaryData['EPF_Employee_Amount'], 2),
                    'ETF_3' => number_format($salaryData['ETF_Amount'], 2),
                    'ADVANCE_PAID' => number_format($salaryData['Salary_advance'], 2),
                    'PAYE' => number_format($salaryData['Payee_amount'], 2),
                    'NO_PAY' => number_format($salaryData['no_pay_deduction'], 2),
                    'STAMP_D' => number_format($salaryData['Stamp_duty'], 2),
                    'BALANCE' => number_format($salaryData['D_Salary'], 2),
                    'DEDUCT_1' => number_format($salaryData['Deduct_1'], 2),
                    'Other_OT' => number_format($salaryData['Other_OT'], 2),
                ];
            }
        }




        // Set flash data for success message
        $this->session->set_flashdata('success_message', 'Payroll Process successfully');

        // Redirect to the same page
        redirect('Pay/Payroll_Process');

        // $filteredPayroll = array_map(function($payroll) {
        //     return [
        //     'EMP_NO' => $payroll['EMP_NO'],
        //     'DEP_NAME' => $payroll['DEP_NAME'],
        //     'NORMAL_OT_HRS' => $payroll['NORMAL_OT_HRS'],
        //     'NORMAL_OT_PAY' => $payroll['NORMAL_OT_PAY']
        //     ];

        // }, $finalPayroll);

        // echo json_encode($filteredPayroll);
    }

    // Function to calculate salary
    private function calculate_salary(
        $BasicSal,
        $BR1,
        $BR2,
        $Incentive,
        $is_no_pay_calc,
        $NopayDays,
        $AdvanceAmount,
        $payee_slabs,
        $VariableAllowancesAmount,
        $FixedAllowancesAmount,
        $VariableDeductionAmount,
        $FixedDeductionAmount,
        $OT_Results,
        $Late_Amount,
        $EarlyDep_Amount,
        $Festival_Adavance,
        $Loan_Amount,
        $halfday_day_amount,
        $BataAllowance,
        $OT_Results_unites,
        $Setting

    ) {

        // Calculate TotalForEPF
        $TotalForEPF = $BasicSal + $BR1 + $BR2 + $Incentive;

        $GrossSal = $TotalForEPF + $VariableAllowancesAmount + $FixedAllowancesAmount + $OT_Results_unites['ot_unites_amount'] + $OT_Results['double'] + $BataAllowance;

        //calculate nopay deduction
        $NopayRate = $BasicSal / 26; // Assuming 30 days in a month
        $NopayDeduction = ($is_no_pay_calc == 1) ? 0 : ($NopayDays * $NopayRate);

        //payee tax calculation is on below function
        $payeeTax = $this->calculate_payee_tax($GrossSal, $payee_slabs, $Setting);

        // Stamp duty calculation, stamp duty is rs.25 of the gross salary if gross salary is above 25000
        $StampDeduction = ($TotalForEPF > 25000) ? 25 : 0;

        // EPF and ETF calculation
        $EPF_8 = 0.08 * $TotalForEPF;
        $EPF_12 = 0.12 * $TotalForEPF;
        $ETF_3 = 0.03 * $TotalForEPF;

        if ($Setting[0]->Epf_Limit == 1) {
            $TotalDeduction = $AdvanceAmount + $payeeTax + $Festival_Adavance + $halfday_day_amount + $Loan_Amount + $NopayDeduction + $StampDeduction + $VariableDeductionAmount + $FixedDeductionAmount + $Late_Amount['Late_Amount'] + $EarlyDep_Amount['ED_Amount'];
        } else {
            $TotalDeduction = $AdvanceAmount + $payeeTax + $Festival_Adavance + $halfday_day_amount + $Loan_Amount + $NopayDeduction + $StampDeduction + $EPF_8 + $VariableDeductionAmount + $FixedDeductionAmount + $Late_Amount['Late_Amount'] + $EarlyDep_Amount['ED_Amount'];
        }

        $NetSalary = $GrossSal - $TotalDeduction;

        $Balance = $NetSalary;

        return [
            'Basic_sal' => $BasicSal,
            'Br_pay' => $BR1 + $BR2,
            'Incentive' => $Incentive,
            'Allowances' => $VariableAllowancesAmount + $FixedAllowancesAmount,
            'Total_F_Epf' => $TotalForEPF,
            'Normal_OT_Hrs' => $OT_Results['normal_hours'],
            'Normal_OT_Pay' => $OT_Results['normal'],
            'Gross_pay' => $GrossSal,
            'Net_salary' => $NetSalary,
            'Deductions' => $VariableDeductionAmount + $FixedDeductionAmount,
            'Late_deduction' => $Late_Amount['Late_Amount'],
            'Late_min' => $Late_Amount['Late_Minutes'],
            'Ed_deduction' => $EarlyDep_Amount['ED_Amount'],
            'Ed_min' => $EarlyDep_Amount['ED_Minutes'],
            'tot_deduction' => $TotalDeduction,
            'EPF_Worker_Amount' => $EPF_8,
            'EPF_Employee_Amount' => $EPF_12,
            'ETF_Amount' => $ETF_3,
            'Salary_advance' => $AdvanceAmount,
            'Payee_amount' => $payeeTax,
            'no_pay_deduction' => $NopayDeduction,
            'Stamp_duty' => $StampDeduction,
            'D_Salary' => $NetSalary,
            'Loan_Instalment' => $Loan_Amount,
            'Festivel_Advance' => $Festival_Adavance,
            'Deduct_1' => $halfday_day_amount,
            'Other_OT' => $OT_Results_unites['ot_unites_amount'],
            'Allowance_1' => $BataAllowance
        ];
        // echo "basic salary " . $BasicSal;
        // echo "<br/>";
        // echo "total_f_epf " . $TotalForEPF;
        // echo "<br/>";
        // echo "ot amount " . $OT_Results['normal'] + $OT_Results['double'];
        // echo "<br/>";
        // echo "Late min = " . $Late_Amount['Late_Minutes'];
        // echo "<br/>";
        // echo "Late = " . $Late_Amount['Late_Amount'];
        // echo "<br/>";
        // echo "Ed min = " . $EarlyDep_Amount['ED_Minutes'];
        // echo "<br/>";
        // echo "Ed" . $EarlyDep_Amount['ED_Amount'];
        // echo "<br/>";
        // echo "epf  " . $EPF_8;
        // echo "<br/>";
        // echo "variable allowance" . $VariableAllowancesAmount;
        // echo "<br/>";
        // echo "fixed allowance" . $FixedAllowancesAmount;
        // echo "<br/>";
        // echo "variable deduction " . $VariableDeductionAmount;
        // echo "<br/>";
        // echo "fixed deduction " . $FixedDeductionAmount;
        // echo "<br/>";
        // echo " deduction " . $VariableDeductionAmount + $FixedDeductionAmount;
        // echo "<br/>";
        // echo "payee" . $payeeTax;
        // echo "<br/>";
        // echo "sal adv" . $AdvanceAmount;
        // echo "<br/>";
        // echo "loan" . $Loan_Amount;
        // echo "<br/>";
        // echo "no_pay_days" . $NopayDays;
        // echo "<br/>";
        // echo "no_pay_deduction" . $NopayDeduction;
        // echo "<br/>";
        // echo "stamp d" . $StampDeduction;
        // echo "<br/>";
        // echo "gross" . $GrossSal;
        // echo "<br/>";
        // echo "net  " . $NetSalary;
        // echo "<br/>";
        // echo "ot unites price " . $OT_Results_unites['ot_unites_amount'];
        // echo "<br/>";
        // // // echo "workhours1-" . $workhours1;
        // // // echo "<br/>";
        // // // echo "workhours2-" . $workhours2;
        // // // echo "<br/>";
        // // // echo "workhours3-" . $workhours3;
        // // // echo "<br/>";
        // // // echo "workhours3-" . $workhours;
        // // // echo "<br/>";
        // // // echo "dot1-" . $DOT1;
        // // // echo "<br/>";
        // // // echo "dot2-" . $DOT2;
        // // // echo "<br/>";
        // // // echo "dot3-" . $DOT3;
        // // // echo "<br/>";
        // // // echo "dot-" . $DOT;
        // // // echo "<br/>";
        // // // echo "out" . $OutTime;
        // // // echo "<br/>";
        // // // echo "outd-" . $OutDate;
        // echo "<br/>";
        // echo "<br/>";
        // echo "<br/>";
        // echo "<br/>";
    }

    //calculate payee tax
    private function calculate_payee_tax($Gross_sal, $payee, $Setting)
    {
        if ($Gross_sal > 140000) {
            $gross_for_payee = 140000;
        } else {
            $gross_for_payee = $Gross_sal;
        }

        $st_gross_Pay = $gross_for_payee * 12;

        $free_rate = 100000;
        $anual_freee_rate = $free_rate * 12;
        $payee_now_amount = 0;

        $calculate_gross_pay = $st_gross_Pay - $anual_freee_rate;

        if ($calculate_gross_pay > 0) {
            foreach ($payee as $slab) {
                if ($calculate_gross_pay <= 0)
                    break;

                $slab_limit = 500000;
                $taxable_amount = min($calculate_gross_pay, $slab_limit);
                $payee_now_amount += ($taxable_amount / 12) * ($slab->Tax_rate / 100);
                $calculate_gross_pay -= $taxable_amount;
            }
        }

        // print_r( $Gross_sal. '-' . $payee_now_amount . '<br/>');
        return $payee_now_amount;
    }


    // Function to handle variable allowances
    private function handle_variable_allowances($EmpNo, $month, $year, $VariableAllowancesAmount)
    {
        if (empty($VariableAllowancesAmount) || !is_array($VariableAllowancesAmount)) return;

        $salaryData = $this->Db_model->getfilteredData("
            SELECT ID FROM tbl_salary 
            WHERE EmpNo = $EmpNo AND Month = $month AND Year = $year
        ");

        $salaryID = isset($salaryData[0]->ID) ? $salaryData[0]->ID : 0;
        if ($salaryID == 0) {
            log_message('error', "No tbl_salary record found for EmpNo: $EmpNo, Month: $month, Year: $year");
            return;
        }

        $processedIDs = [];

        foreach ($VariableAllowancesAmount as $allowance) {
            $allowanceID = $allowance->ID ?? 0;
            $amount = $allowance->Amount;
            $allowanceName = $allowance->Allowance_name;
            if ($allowanceID == 0 || in_array($allowanceID, $processedIDs)) continue;

            $processedIDs[] = $allowanceID;

            $dataArray = [
                'tbl_varialble_allowance_ID' => $allowanceID,
                'tbl_salary_ID' => $salaryID,
                'Year' => $year,
                'Month' => $month,
                'EmpNo' => $EmpNo,
                'Allowance_Status' => 'varialble_allowance',
                'Alw_Name' => $allowanceName,
                'Alw_Amount' => $amount

            ];

            $existingData = $this->Db_model->getfilteredData("
                SELECT ID FROM tbl_allowance_has_tbl_salary 
                WHERE EmpNo = $EmpNo AND Month = $month AND Year = $year 
                  AND tbl_varialble_allowance_ID = $allowanceID 
                  AND Allowance_Status = 'varialble_allowance'
            ");

            if (empty($existingData)) {
                $this->Db_model->insertData("tbl_allowance_has_tbl_salary", $dataArray);
            } else {
                $this->Db_model->updateData("tbl_allowance_has_tbl_salary", $dataArray, [
                    'EmpNo' => $EmpNo,
                    'Month' => $month,
                    'Year' => $year,
                    'tbl_varialble_allowance_ID' => $allowanceID,
                    'Allowance_Status' => 'varialble_allowance',
                    'Alw_Name' => $allowanceName,
                    'Alw_Amount' => $amount
                ]);
            }
        }
    }

    // Function to handle fixed allowances
    private function handle_fixed_allowances($EmpNo, $month, $year, $FixedAllowancesAmount)
    {
        if (empty($FixedAllowancesAmount) || !is_array($FixedAllowancesAmount)) return;

        $salaryData = $this->Db_model->getfilteredData("
        SELECT ID FROM tbl_salary 
        WHERE EmpNo = $EmpNo AND Month = $month AND Year = $year
    ");

        $salaryID = isset($salaryData[0]->ID) ? $salaryData[0]->ID : 0;
        if ($salaryID == 0) {
            log_message('error', "No tbl_salary record found for EmpNo: $EmpNo, Month: $month, Year: $year");
            return;
        }

        $processedIDs = [];

        foreach ($FixedAllowancesAmount as $allowance) {
            $allowanceID = $allowance->ID ?? 0;
            $amount = $allowance->Amount;
            $allowanceName = $allowance->Allowance_name;
            if ($allowanceID == 0 || in_array($allowanceID, $processedIDs)) continue;

            $processedIDs[] = $allowanceID;

            $dataArray = [
                'tbl_varialble_allowance_ID' => $allowanceID,
                'tbl_salary_ID' => $salaryID,
                'Year' => $year,
                'Month' => $month,
                'EmpNo' => $EmpNo,
                'Allowance_Status' => 'fixed_allowance',
                'Alw_Name' => $allowanceName,
                'Alw_Amount' => $amount
            ];

            $existingData = $this->Db_model->getfilteredData("
            SELECT ID FROM tbl_allowance_has_tbl_salary 
            WHERE EmpNo = $EmpNo AND Month = $month AND Year = $year 
              AND tbl_varialble_allowance_ID = $allowanceID 
              AND Allowance_Status = 'fixed_allowance'
        ");

            if (empty($existingData)) {
                $this->Db_model->insertData("tbl_allowance_has_tbl_salary", $dataArray);
            } else {
                $this->Db_model->updateData("tbl_allowance_has_tbl_salary", $dataArray, [
                    'EmpNo' => $EmpNo,
                    'Month' => $month,
                    'Year' => $year,
                    'tbl_varialble_allowance_ID' => $allowanceID,
                    'Allowance_Status' => 'fixed_allowance',
                    'Alw_Name' => $allowanceName,
                    'Alw_Amount' => $amount
                ]);
            }
        }
    }


    // Function to handle variable deductions
    private function handle_variable_deductions($EmpNo, $month, $year, $VariableDeductionAmount)
    {
        if (empty($VariableDeductionAmount) || !is_array($VariableDeductionAmount)) return;

        $salaryData = $this->Db_model->getfilteredData("
            SELECT ID FROM tbl_salary 
            WHERE EmpNo = $EmpNo AND Month = $month AND Year = $year
        ");

        $salaryID = isset($salaryData[0]->ID) ? $salaryData[0]->ID : 0;
        if ($salaryID == 0) {
            log_message('error', "No tbl_salary record found for EmpNo: $EmpNo, Month: $month, Year: $year");
            return;
        }

        $processedIDs = [];

        foreach ($VariableDeductionAmount as $deduction) {
            $deductionID = $deduction->ID ?? 0;
            $amount = $deduction->Amount;
            $deductionName = $deduction->Deduction_name;
            if ($deductionID == 0 || in_array($deductionID, $processedIDs)) continue;

            $processedIDs[] = $deductionID;

            $dataArray = [
                'tbl_varialble_deduction_ID' => $deductionID,
                'tbl_salary_ID' => $salaryID,
                'Year' => $year,
                'Month' => $month,
                'EmpNo' => $EmpNo,
                'Deduction_Status' => 'varialble_deduction',
                'Ded_Name' => $deductionName,
                'Ded_Amount' => $amount
            ];

            $existingData = $this->Db_model->getfilteredData("
                SELECT ID FROM tbl_deduction_has_tbl_salary 
                WHERE EmpNo = $EmpNo AND Month = $month AND Year = $year 
                  AND tbl_varialble_deduction_ID = $deductionID 
                  AND Deduction_Status = 'varialble_deduction'
            ");

            if (empty($existingData)) {
                $this->Db_model->insertData("tbl_deduction_has_tbl_salary", $dataArray);
            } else {
                $this->Db_model->updateData("tbl_deduction_has_tbl_salary", $dataArray, [
                    'EmpNo' => $EmpNo,
                    'Month' => $month,
                    'Year' => $year,
                    'tbl_varialble_deduction_ID' => $deductionID,
                    'Deduction_Status' => 'varialble_deduction',
                    'Ded_Name' => $deductionName,
                    'Ded_Amount' => $amount
                ]);
            }
        }
    }

    // Function to handle fixed deductions
    private function handle_fixed_deductions($EmpNo, $month, $year, $FixedDeductionAmount)
    {
        if (empty($FixedDeductionAmount) || !is_array($FixedDeductionAmount)) return;

        $salaryData = $this->Db_model->getfilteredData("
            SELECT ID FROM tbl_salary 
            WHERE EmpNo = $EmpNo AND Month = $month AND Year = $year
        ");

        $salaryID = isset($salaryData[0]->ID) ? $salaryData[0]->ID : 0;
        if ($salaryID == 0) {
            log_message('error', "No tbl_salary record found for EmpNo: $EmpNo, Month: $month, Year: $year");
            return;
        }

        $processedIDs = [];

        foreach ($FixedDeductionAmount as $deduction) {
            $deductionID = $deduction->ID ?? 0;
            $amount = $deduction->Amount;
            $deductionName = $deduction->Deduction_name;
            if ($deductionID == 0 || in_array($deductionID, $processedIDs)) continue;

            $processedIDs[] = $deductionID;

            $dataArray = [
                'tbl_varialble_deduction_ID' => $deductionID,
                'tbl_salary_ID' => $salaryID,
                'Year' => $year,
                'Month' => $month,
                'EmpNo' => $EmpNo,
                'Deduction_Status' => 'fixed_deduction',
                'Ded_Name' => $deductionName,
                'Ded_Amount' => $amount
            ];

            $existingData = $this->Db_model->getfilteredData("
                SELECT ID FROM tbl_deduction_has_tbl_salary 
                WHERE EmpNo = $EmpNo AND Month = $month AND Year = $year 
                  AND tbl_varialble_deduction_ID = $deductionID 
                  AND Deduction_Status = 'fixed_deduction'
            ");

            if (empty($existingData)) {
                $this->Db_model->insertData("tbl_deduction_has_tbl_salary", $dataArray);
            } else {
                $this->Db_model->updateData("tbl_deduction_has_tbl_salary", $dataArray, [
                    'EmpNo' => $EmpNo,
                    'Month' => $month,
                    'Year' => $year,
                    'tbl_varialble_deduction_ID' => $deductionID,
                    'Deduction_Status' => 'fixed_deduction',
                    'Ded_Name' => $deductionName,
                    'Ded_Amount' => $amount
                ]);
            }
        }
    }

    // Function to calculate overtime
    private function calculateOvertime($BasicSal, $Fixed_Allowance, $N_OT_Hours, $D_OT_Hours, $Setting)
    {
        if ($Setting[0]->Is_Ot_Fixed_Amount_Rate == 1) {
            // echo "ot munites " . $N_OT_Hours;
            // echo "<br/>";

            $OT_Rate = $Setting[0]->Ot_Rate;
            $N_OT_Amount = $OT_Rate * ($N_OT_Hours / 60);
            return [
                'normal' => $N_OT_Amount,
                'double' => 0,
                'normal_hours' => $N_OT_Hours,
                'double_hours' => $D_OT_Hours
            ];
        } else {
            $OT_Rate = (($BasicSal) / 240) * 1.5;
            $OT_Rate_2 = (($BasicSal) / 240) * 2;

            $N_OT_Amount = $OT_Rate * ($N_OT_Hours / 60);
            $D_OT_Amount = $OT_Rate_2 * ($D_OT_Hours / 60);
            // echo "ot munites " . $N_OT_Hours;
            // echo "<br/>";
            return [
                'normal' => $N_OT_Amount,
                'double' => $D_OT_Amount,
                'normal_hours' => $N_OT_Hours,
                'double_hours' => $D_OT_Hours
            ];
        }
    }

    private function calculateOvertime_unites($BasicSal, $Fixed_Allowance, $N_OT_unites, $Setting)
    {
        if ($Setting[0]->Is_Ot_Fixed_Amount_Rate == 1) {
            echo "ot munites " . $N_OT_unites;
            echo "<br/>";

            $OT_Rate = $Setting[0]->Ot_Rate;
            $N_OT_Amount = ($OT_Rate / 2) * $N_OT_unites;
            return [
                'ot_unites_amount' => $N_OT_Amount
            ];
        } else {
            $OT_Rate = (($BasicSal) / 240) * 1.5;
            $OT_Rate_2 = (($BasicSal) / 240) * 2;

            $N_OT_Amount = ($OT_Rate / 2) * $N_OT_unites;
            // $D_OT_Amount = $OT_Rate_2 * ($D_OT_Hours / 60);
            echo "ot munites " . $N_OT_unites;
            echo "<br/>";
            return [
                'ot_unites_amount' => $N_OT_Amount
            ];
        }
    }

    // Function to calculate late deduction
    private function calculateLateDeduction($BasicSal, $Late_Min, $Setting)
    {

        if ($Setting[0]->Is_Ot_Fixed_Amount_Rate == 1) {
            // echo "late munites " . $Late_Min;
            // echo "<br/>";

            $Late_rate = $Setting[0]->Ot_Rate;
            $Late_Amount = $Late_rate * ($Late_Min / 60);
            return [
                'Late_Minutes' => $Late_Min,
                'Late_Amount' => $Late_Amount
            ];
        } else {
            $Late_rate = ($BasicSal / 240) / 60;
            $Late_Amount = $Late_rate * $Late_Min;
            // echo "late munites " . $Late_Min;
            // echo "<br/>";
            return [
                'Late_Minutes' => $Late_Min,
                'Late_Amount' => $Late_Amount
            ];
        }
    }

    // Function to calculate early departure deduction
    private function calculateEarlyDepartureDeduction($BasicSal, $ed_min, $Setting)
    {

        if ($Setting[0]->Is_Ot_Fixed_Amount_Rate == 1) {
            // echo "ed munites " . $ed_min;
            // echo "<br/>";

            $ed_rate = $Setting[0]->Ot_Rate;
            $ed_amount = $ed_rate * ($ed_min / 60);
            return [
                'ED_Minutes' => $ed_min,
                'ED_Amount' => $ed_amount
            ];
        } else {
            $ed_rate = ($BasicSal / 240) / 60;
            $ed_amount = $ed_rate * $ed_min;
            // echo "ed munites " . $ed_min;
            // echo "<br/>";
            return [
                'ED_Minutes' => $ed_min,
                'ED_Amount' => $ed_amount
            ];
        }
    }

    private function processFestivalAdvance($EmpNo, $month, $year)
    {
        $Fest_Advance = $this->Db_model->getfilteredData("SELECT * FROM tbl_festival_advance WHERE EmpNo = $EmpNo AND Month = $month AND Year = $year");

        $totalFestivalAdvance = 0;

        foreach ($Fest_Advance as $data) {
            $Festival_id = $data->Fest_ID;
            $installment = $data->Month_Installment;

            $hasRow = $this->Db_model->getfilteredData("SELECT COUNT(EmpNo) as HasRow FROM tbl_festival_trans WHERE EmpNo = $EmpNo AND Month = $month AND Year = $year AND Festival_ID = $Festival_id");

            if (!$hasRow[0]->HasRow) {
                $PaidAmount_to = $data->Paid_Amount + $installment;
                $BalanceAmount = $data->FullAmount - $PaidAmount_to;
                $Is_Settled = ($BalanceAmount <= 0) ? 1 : 0;

                // Insert transaction
                $this->Db_model->insertData("tbl_festival_trans", [
                    'Year' => $year,
                    'EmpNo' => $EmpNo,
                    'Month' => $month,
                    'Amount_month' => $installment,
                    'Festival_ID' => $Festival_id,
                ]);

                // Update advance table
                $this->Db_model->updateData("tbl_festival_advance", [
                    'Paid_Amount' => $PaidAmount_to,
                    'Balance_amount' => $BalanceAmount,
                    'Is_Settled' => $Is_Settled,
                ], ['EmpNo' => $EmpNo, 'Fest_ID' => $Festival_id]);

                // Add to total

            }
            $totalFestivalAdvance += $installment;
        }

        return $totalFestivalAdvance;
    }

    private function processLoanInstallments($EmpNo, $month, $year)
    {
        $Loans = $this->Db_model->getfilteredData("SELECT Loan_ID, Month_Installment, FullAmount, Paid_Amount FROM tbl_loans WHERE Is_Settled = 0 AND EmpNo = $EmpNo");

        $totalLoanAmount = 0;

        foreach ($Loans as $loan) {
            $LoanID = $loan->Loan_ID;
            $installment = $loan->Month_Installment;

            $hasRow = $this->Db_model->getfilteredData("SELECT COUNT(EmpNo) as HasRow FROM tbl_loan_trans WHERE EmpNo = $EmpNo AND Month = $month AND Year = $year AND Loan_ID = $LoanID");

            if (!$hasRow[0]->HasRow) {
                $PaidAmount_to = $loan->Paid_Amount + $installment;
                $BalanceAmount = $loan->FullAmount - $PaidAmount_to;
                $Is_Settled = ($BalanceAmount <= 0) ? 1 : 0;

                $this->Db_model->insertData("tbl_loan_trans", [
                    'Year' => $year,
                    'EmpNo' => $EmpNo,
                    'Month' => $month,
                    'Amount_month' => $installment,
                    'Loan_ID' => $LoanID,
                ]);

                $this->Db_model->updateData("tbl_loans", [
                    'Paid_Amount' => $PaidAmount_to,
                    'Balance_amount' => $BalanceAmount,
                    'Is_Settled' => $Is_Settled,
                ], ['EmpNo' => $EmpNo, 'Loan_ID' => $LoanID]);
            }
            $totalLoanAmount += $installment;
        }
        return $totalLoanAmount;
    }

    private function processHalfdayDeduction($BasicSal, $EmpNo, $month, $year)
    {
        $halfday_deduct = $this->Db_model->getfilteredData("select sum(Day_Type) as misspunch FROM 
                tbl_individual_roster where EmpNo=$EmpNo AND DayStatus = 'MS' AND EXTRACT(MONTH FROM FDate)=$month AND EXTRACT(YEAR FROM FDate)=$year AND tbl_individual_roster.InTime = '00:00:00'");

        $halfday_days = $halfday_deduct[0]->misspunch;

        $halfday_day_rate = $BasicSal / 26;
        $halfday_day_amount = $halfday_days * $halfday_day_rate;

        return 0;
    }
    private function processBata($EmpNo, $Des_ID, $month, $year)
    {
        $bata_amount = 0;
        if ($Des_ID = 6 || $Des_ID = 7 || $Des_ID = 8) {
            $bata_days = $this->Db_model->getfilteredData("select sum(Day_Type) as batadates FROM 
                tbl_individual_roster where EmpNo=$EmpNo AND EXTRACT(MONTH FROM FDate)=$month AND EXTRACT(YEAR FROM FDate)=$year AND (DayStatus = 'PR' OR DayStatus = 'HFD' OR DayStatus = 'SL')");
            if (!empty($bata_days[0]->batadates)) {
                $bata_days = $bata_days[0]->batadates;
                $batarate = 300;
                $bata_amount = $bata_days * $batarate;
            }
        }
        return 0;
    }
}
