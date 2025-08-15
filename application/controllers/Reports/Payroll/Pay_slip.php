<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Pay_slip extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!($this->session->userdata('login_user'))) {
            redirect(base_url() . "");
        }

        /*
         * Load Database model
         */
        $this->load->library("pdf_library");
        $this->load->model('Db_model');
    }

    /*
     * Index page in Departmrnt
     */

    public function index() {

        $data['title'] = "Pay Slip | HRM System";
        $data['data_dep'] = $this->Db_model->getData('Dep_ID,Dep_Name', 'tbl_departments');
        $data['data_desig'] = $this->Db_model->getData('Des_ID,Desig_Name', 'tbl_designations');
        $data['data_cmp'] = $this->Db_model->getData('Cmp_ID,Company_Name', 'tbl_companyprofile');
        $data['data_branch'] = $this->Db_model->getData('B_id,B_name', 'tbl_branches');
        $this->load->view('Reports/Payroll/Pay_slip_report', $data);
    }

    /*
     * Insert Departmrnt
     */

    public function Report_department() {

        $Data['data_set'] = $this->Db_model->getData('id,Dep_Name', 'tbl_departments');

        $this->load->view('Reports/Master/rpt_Departments', $Data);
    }

    public function Pay_slip_Report_By_Cat() {

        $data['data_cmp'] = $this->Db_model->getData('Cmp_ID,Company_Name', 'tbl_companyprofile');

        date_default_timezone_set('Asia/Colombo');
        $year = $this->input->post("cmb_year") ?: date("Y");
        $Month = $this->input->post("cmb_month");
        $emp = $this->input->post("txt_emp");
        $emp_name = $this->input->post("txt_emp_name");
        $desig = $this->input->post("cmb_desig");
        $dept = $this->input->post("cmb_dep");
        $branch = $this->input->post("cmb_group");

        // Build filter
        $filter = '';

        if ($Month && $year) {
            $filter = "WHERE s.Month = '$Month' AND s.Year = '$year' AND e.Status = '1' AND e.EmpNo != '9000'";
        }
        if ($emp) {
            $filter .= ($filter == '' ? " WHERE" : " AND") . " e.EmpNo = '$emp'";
        }
        if ($emp_name) {
            $filter .= ($filter == '' ? " WHERE" : " AND") . " e.Emp_Full_Name = '$emp_name'";
        }
        if ($desig) {
            $filter .= ($filter == '' ? " WHERE" : " AND") . " e.Des_ID = '$desig'";
        }
        if ($dept) {
            $filter .= ($filter == '' ? " WHERE" : " AND") . " e.Dep_ID = '$dept'";
        }
        if ($branch) {
            $filter .= ($filter == '' ? " WHERE" : " AND") . " e.B_id = '$branch'";
        }

        // Main salary query
        $data['data_set'] = $this->Db_model->getfilteredData("
            SELECT 
                s.id,
                s.EmpNo,
                e.Emp_Full_Name,
                b.B_name,
                d.Dep_Name,
                s.Month,
                s.Year,
                s.Basic_sal,
                s.Br_pay,
                s.Allowance_1,
                s.Salary_advance,
                s.EPF_Worker_Amount,
                s.EPF_Employee_Amount,
                s.ETF_Amount,
                s.Payee_amount,
                s.Stamp_duty,
                s.Gross_pay,
                s.no_pay_deduction,
                s.Late_deduction,
                s.Loan_Instalment,
                s.Ed_deduction,
                s.Normal_OT_Pay,
                s.Incentive,
                s.tot_deduction,
                s.Net_salary
            FROM tbl_salary s
            INNER JOIN tbl_empmaster e ON e.EmpNo = s.EmpNo
            INNER JOIN tbl_departments d ON d.Dep_ID = e.Dep_ID
            INNER JOIN tbl_branches b ON b.B_id = e.B_id
            $filter
            ORDER BY s.EmpNo
        ");

        // Fetch allowances and deductions for each employee
        $data['allowances'] = [];
        $data['deductions'] = [];
        foreach ($data['data_set'] as $row) {
            $EmpNo = $row->EmpNo;
            $data['allowances'][$EmpNo] = [
                'fixed' => $this->Db_model->getfilteredData("
                    SELECT a.Allowance_name, b.Amount 
                    FROM tbl_allowance_has_tbl_salary c
                    INNER JOIN tbl_fixed_allowance b ON b.ID = c.tbl_varialble_allowance_ID
                    INNER JOIN tbl_allowance_type a ON b.Alw_ID = a.Alw_ID
                    WHERE c.EmpNo = '$EmpNo' AND c.Allowance_Status = 'fixed_allowance'
                    GROUP BY c.tbl_varialble_allowance_ID
                "),
                'variable' => $this->Db_model->getfilteredData("
                    SELECT a.Allowance_name, b.Amount
                    FROM tbl_allowance_has_tbl_salary c
                    INNER JOIN tbl_varialble_allowance b ON b.ID = c.tbl_varialble_allowance_ID
                    INNER JOIN tbl_allowance_type a ON a.Alw_ID = b.Alw_ID
                    WHERE c.EmpNo = '$EmpNo' 
                    AND c.Allowance_Status = 'varialble_allowance'
                    AND b.Month = '$Month' AND b.Year = '$year'
                    GROUP BY c.tbl_varialble_allowance_ID
                ")
            ];
            $data['deductions'][$EmpNo] = [
                'fixed' => $this->Db_model->getfilteredData("
                    SELECT a.Deduction_name, c.Amount 
                    FROM tbl_deduction_has_tbl_salary b 
                    INNER JOIN tbl_variable_deduction c ON b.tbl_varialble_deduction_ID = c.ID
                    INNER JOIN tbl_deduction_types a ON c.Ded_ID = a.Ded_ID
                    WHERE b.Deduction_Status = 'fixed_deduction' 
                    AND b.EmpNo = '$EmpNo'
                    GROUP BY b.tbl_varialble_deduction_ID
                "),
                'variable' => $this->Db_model->getfilteredData("
                    SELECT a.Deduction_name, c.Amount 
                    FROM tbl_deduction_has_tbl_salary b 
                    INNER JOIN tbl_variable_deduction c ON b.tbl_varialble_deduction_ID = c.ID
                    INNER JOIN tbl_deduction_types a ON c.Ded_ID = a.Ded_ID
                    WHERE b.Deduction_Status = 'varialble_deduction' 
                    AND b.EmpNo = '$EmpNo' 
                    AND c.Month = '$Month' AND c.Year = '$year'
                    GROUP BY b.tbl_varialble_deduction_ID
                ")
            ];
        }
        // echo json_encode($data['allowances']);
        // var_dump($data['data_set']);
        // var_dump($data['allowances']);
        // var_dump($data['deductions']);
        // die;

        $data['data_month'] = $Month;
        $data['data_year'] = $year;

        $this->load->view('Reports/Payroll/rpt_pay_slip', $data);
    }

    function get_auto_emp_name() {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->Db_model->get_auto_emp_name($q);
        }
    }

    function get_auto_emp_no() {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->Db_model->get_auto_emp_no($q);
        }
    }

}
