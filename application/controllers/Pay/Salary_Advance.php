<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Salary_Advance extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!($this->session->userdata('login_user'))) {
            redirect(base_url() . "");
        }
        /*
         * Load Database model
         */
        $this->load->model('Db_model', '', TRUE);
    }

    /*
     * Index page
     */

    public function index()
    {

        $this->load->helper('url');
        $data['title'] = "Salary Advance Entry | HRM SYSTEM";
        $data['data_emp'] = $this->Db_model->getData('EmpNo,Emp_Full_Name', 'tbl_empmaster');
        $data['data_emp'] = $this->Db_model->getData('EmpNo,Emp_Full_Name', 'tbl_empmaster');
        $data['data_dep'] = $this->Db_model->getData('Dep_ID,Dep_Name', 'tbl_departments');
        $data['data_desig'] = $this->Db_model->getData('Des_ID,Desig_Name', 'tbl_designations');
        $data['data_cmp'] = $this->Db_model->getData('Cmp_ID,Company_Name', 'tbl_companyprofile');
        $data['data_group'] = $this->Db_model->getData('Grp_ID,EmpGroupName', 'tbl_emp_group');





        //        $data['data_loan'] = $this->Db_model->getData('id,loan_name', 'tbl_loan_types');
        $this->load->view('Payroll/Salary_Advance/index', $data);
    }

    public function dropdown()
    {

        $cat = $this->input->post('cmb_cat');

        if ($cat == "Employee") {
            $query = $this->Db_model->get_dropdown();
            echo '<option value="" default>-- Select --</option>';
            foreach ($query->result() as $row) {

                echo "<option value='" . $row->EmpNo . "'>" . $row->Emp_Full_Name . "</option>";
            }
        }

        if ($cat == "Department") {
            $query = $this->Db_model->get_dropdown_dep();
            echo '<option value="" default>-- Select --</option>';
            foreach ($query->result() as $row) {
                echo "<option value='" . $row->Dep_ID . "'>" . $row->Dep_Name . "</option>";
            }
        }
        if ($cat == "Designation") {
            $query = $this->Db_model->get_dropdown_des();
            echo '<option value="" default>-- Select --</option>';
            foreach ($query->result() as $row) {
                echo "<option value='" . $row->Des_ID . "'>" . $row->Desig_Name . "</option>";
            }
        }
        if ($cat == "Employee_Group") {
            $query = $this->Db_model->get_dropdown_group();
            echo '<option value="" default>-- Select --</option>';
            foreach ($query->result() as $row) {
                echo "<option value='" . $row->Grp_ID . "'>" . $row->EmpGroupName . "</option>";
            }
        }

        if ($cat == "Company") {
            $query = $this->Db_model->get_dropdown_comp();
            echo '<option value="" default>-- Select --</option>';
            foreach ($query->result() as $row) {
                echo "<option value='" . $row->Cmp_ID . "'>" . $row->Company_Name . "</option>";
            }
        }

        //        if ($cat == "Department") {
        //            $query = $this->Db_model->get_dropdown_dep();
        //            
        //            echo"<select class='form-control' id='Dep' name='Dep'>";
        //            foreach ($query->result() as $row) {
        //                echo "<option value='" . $row->ID . "'>" . $row->Dep_Name . "</option>";
        //            }
        //            echo"</select>";
        //        }
    }

    public function insert_data()
    {

        $currentUser = $this->session->userdata('login_user');
        $ApproveUser = $currentUser[0]->EmpNo;

        $cat = $this->input->post('cmb_cat');
        if ($cat == "Employee") {
            $cat2 = $this->input->post('txt_nic');
            $string = "SELECT EmpNo FROM tbl_empmaster WHERE EmpNo='$cat2'";
            $EmpData = $this->Db_model->getfilteredData($string);
        }

        if ($cat == "Department") {
            $cat2 = $this->input->post('cmb_cat2');
            $string = "SELECT EmpNo FROM tbl_empmaster WHERE Dep_ID='$cat2'";
            $EmpData = $this->Db_model->getfilteredData($string);
        }

        if ($cat == "Designation") {
            $cat2 = $this->input->post('cmb_cat2');
            $string = "SELECT EmpNo FROM tbl_empmaster WHERE Des_ID='$cat2'";
            $EmpData = $this->Db_model->getfilteredData($string);
        }
        if ($cat == "Employee_Group") {
            $cat2 = $this->input->post('cmb_cat2');
            $string = "SELECT EmpNo FROM tbl_empmaster WHERE Grp_ID='$cat2'";
            $EmpData = $this->Db_model->getfilteredData($string);
        }

        if ($cat == "Company") {
            $cat2 = $this->input->post('cmb_cat2');
            $string = "SELECT EmpNo FROM tbl_empmaster WHERE Cmp_ID='$cat2'";
            $EmpData = $this->Db_model->getfilteredData($string);
        }

        date_default_timezone_set('Asia/Colombo');
        $date = date_create();
        $timestamp = date_format($date, 'Y-m-d H:i:s');


        //        $Request_date = $this->input->post('txt_date');

        $advance = $this->input->post('txt_advance');
        $year = date("Y");
        //        $month = date("m");
        $month = $this->input->post('cmb_month');

        $Emp = $EmpData[0]->EmpNo;
        //        var_dump($Emp);die;

        $Count = count($EmpData);
        //        var_dump($Count);die;

        $SalPrecentage = $this->Db_model->getfilteredData("select (60/100)*(Basic_Salary+Incentive+Fixed_Allowance) as totsal from tbl_empmaster where EmpNo=$Emp");

        $HasRow = $this->Db_model->getfilteredData("select count(EmpNo) as HasRow from tbl_salary_advance where EmpNo=$Emp and Year=$year and month=$month");

        if ($advance > $SalPrecentage[0]->totsal) {
            $this->session->set_flashdata('error_message', 'Employee cannot apply more than salary precentage (60%)');
        } else {
            if ($HasRow[0]->HasRow > 0) {
                $this->session->set_flashdata('error_message', 'Employee already applied salary advance');
            } else {
                for ($i = 0; $i < $Count; $i++) {
                    $data = array(
                        array(
                            'EmpNo' => $Emp,
                            'Amount' => $advance,
                            'Year' => $year,
                            'Month' => $month,
                            'Is_pending' => 0,
                            'Approved_by' => $ApproveUser,
                            'Is_Approve' => 0,
                        )
                    );
                    $this->db->insert_batch('tbl_salary_advance', $data);
                    $this->session->set_flashdata('success_message', 'New Salary advance added successfully');
                }
            }
        }

        redirect('/Pay/Salary_Advance');
    }

    /*
     * Get Data
     */

    public function getSal_Advance()
    {

        $emp = $this->input->post("txt_emp");
        $emp_name = $this->input->post("txt_emp_name");
        $desig = $this->input->post("cmb_desig");
        $dept = $this->input->post("cmb_dep");
        $month = $this->input->post("cmb_months");
        $cmb_year = $this->input->post("cmb_years");
        $cmb_grp = $this->input->post("cmb_grp");


        // Filter Data by categories
        $filter = '';

        if (($this->input->post("cmb_years"))) {
            if ($filter == '') {
                $filter = " AND  sal_ad.Year ='$cmb_year'";
            } else {
                $filter .= " AND  sal_ad.Year ='$cmb_year'";
            }
        }

        if (($this->input->post("cmb_months"))) {
            if ($filter == '') {
                $filter = " AND  sal_ad.Month =$month";
            } else {
                $filter .= " AND  sal_ad.Month =$month";
            }
        }
        if (($this->input->post("txt_emp"))) {
            if ($filter == null) {
                $filter = " AND sal_ad.EmpNo =$emp";
            } else {
                $filter .= " AND sal_ad.EmpNo =$emp";
            }
        }

        if (($this->input->post("txt_emp_name"))) {
            if ($filter == null) {
                $filter = " AND Emp.Emp_Full_Name ='$emp_name'";
            } else {
                $filter .= " AND Emp.Emp_Full_Name ='$emp_name'";
            }
        }
        if (($this->input->post("cmb_desig"))) {
            if ($filter == null) {
                $filter = " AND dsg.Des_ID  ='$desig'";
            } else {
                $filter .= " AND dsg.Des_ID  ='$desig'";
            }
        }
        if (($this->input->post("cmb_dep"))) {
            if ($filter == null) {
                $filter = " AND dep.Dep_id  ='$dept'";
            } else {
                $filter .= " AND dep.Dep_id  ='$dept'";
            }
        }
        if (($this->input->post("cmb_grp"))) {
            if ($filter == null) {
                $filter = " AND grp.Grp_ID  ='$cmb_grp'";
            } else {
                $filter .= " AND grp.Grp_ID  ='$cmb_grp'";
            }
        }

        // echo $filter;


        $data['data_set'] = $this->Db_model->getfilteredData("SELECT 
                                                                    sal_ad.id,
                                                                    sal_ad.EmpNo,
                                                                    Emp.Emp_Full_Name,
                                                                    sal_ad.Amount,
                                                                    sal_ad.Year,
                                                                    sal_ad.Month,
                                                                    sal_ad.Request_Date,
                                                                    sal_ad.Is_pending,
                                                                    sal_ad.Is_Approve,
                                                                    sal_ad.Approved_by,
                                                                    sal_ad.Is_Cancel,
                                                                    sal_ad.Approved_Timestamp,
                                                                    dsg.Desig_Name,
                                                                    dep.Dep_Name
                                                                FROM
                                                                    tbl_salary_advance sal_ad
                                                                        INNER JOIN
                                                                    tbl_empmaster Emp ON Emp.EmpNo = sal_ad.EmpNo
                                                                        LEFT JOIN
                                                                    tbl_designations dsg ON dsg.Des_ID = Emp.Des_ID
                                                                        LEFT JOIN
                                                                    tbl_departments dep ON dep.Dep_id = Emp.Dep_id
                                                                        INNER JOIN
                                                                    tbl_emp_group grp ON grp.Grp_ID = Emp.Grp_ID
                                                                    where
                                                                    sal_ad.Is_pending = 0 and sal_ad.Is_Cancel=0
                                                                    {$filter} ");


        $this->load->view('Payroll/Salary_Advance/search_data', $data);
        // echo $filter;
    }

    /*
     * Approve salary advance request
     */

    public function approve($ID)
    {

        $currentUser = $this->session->userdata('login_user');
        $Emp = $currentUser[0]->EmpNo;

        $data = array(
            'Is_pending' => 1,
            'Is_Approve' => 1,
            'Approved_by' => $Emp,
        );

        $whereArr = array("id" => $ID);
        $result = $this->Db_model->updateData("tbl_salary_advance", $data, $whereArr);


        $this->session->set_flashdata('success_message', 'Salary Advance Approved successfully');
        redirect(base_url() . "Pay/Salary_Advance");
    }

    /*
     * Reject salary advance request
     */

    public function reject($ID)
    {


        $currentUser = $this->session->userdata('login_user');
        $Emp = $currentUser[0]->EmpNo;

        $data = array(
            'Is_pending' => 0,
            'Is_Approve' => 0,
            'Is_Cancel' => 1,
            'Approved_by' => $Emp,
        );

        $whereArr = array("id" => $ID);
        $result = $this->Db_model->updateData("tbl_salary_advance", $data, $whereArr);

        $this->session->set_flashdata('success_message', 'Salary Advance successfully');
        redirect(base_url() . "Payroll/Salary_Advance");
    }
    public function checkbasicsal()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        $empNo = isset($input['empNo']) ? $input['empNo'] : '';
        $Sal_input = intval(isset($input['sal_adv'])) ? $input['sal_adv'] : '';

        if (empty($empNo)) {
            echo "0";
        } else {
            $data_sal = $this->Db_model->getfilteredData("select (60/100)*(Basic_Salary+Incentive+Fixed_Allowance) as totsal from tbl_empmaster where EmpNo='$empNo'");


            $basicSal = intval($data_sal[0]->totsal);

            if ($basicSal < $Sal_input) {
                echo "1";
            } else {
                echo "2";
            }
        }
    }
    public function approveAll()
    {
        $ids = $this->input->post('ids');

        // echo json_encode($ids);
        $currentUser = $this->session->userdata('login_user');
        $Emp = $currentUser[0]->EmpNo;
        if (!empty($ids)) {
            foreach ($ids as $ID) {
                $data = array(
                    'Is_pending' => 1,
                    'Is_Approve' => 1,
                    'Approved_by' => $Emp,
                );

                $whereArr = array("id" => $ID);
                $result = $this->Db_model->updateData("tbl_salary_advance", $data, $whereArr);
            }
        }
        $this->session->set_flashdata('success_message', 'Salary Advance Approved successfully');
        redirect(base_url() . "Pay/Salary_Advance");
    }
}
