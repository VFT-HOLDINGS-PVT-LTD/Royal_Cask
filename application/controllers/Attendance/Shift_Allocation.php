<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Shift_Allocation extends CI_Controller
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

        $data['title'] = "Shift Allocation | HRM System";
        $data['data_set'] = $this->Db_model->getData('EmpNo,Emp_Full_Name', 'tbl_empmaster');
        $data['data_shift'] = $this->Db_model->getData('ShiftCode,ShiftName', 'tbl_shifts');
        $data['data_roster'] = $this->Db_model->getData('RosterCode,RosterName', 'tbl_rosterpatternweeklyhd');
        $this->load->view('Attendance/Shift_Allocation/index', $data);
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
    }

    /*
     * Insert Data
     */

    public function shift_allocation()
    {
        $cat = $this->input->post('cmb_cat');
        $cat2 = $this->input->post('cmb_cat2');
        if ($cat == 'Employee') {
            $cat2 = $this->input->post('txt_nic');
        }
        $filterColumn = [
            "Employee" => "EmpNo",
            "Department" => "Dep_ID",
            "Designation" => "Des_ID",
            "Employee_Group" => "Grp_ID",
            "Company" => "Cmp_ID",
        ][$cat] ?? null;

        if (!$filterColumn) {
            $this->session->set_flashdata('error_message', 'Invalid Category');
            redirect('/Attendance/Shift_Allocation');
            return;
        }

        $EmpData = $this->Db_model->getfilteredData(
            "SELECT EmpNo FROM tbl_empmaster WHERE {$filterColumn} = '$cat2' AND Status = 1 AND Active_process = 1"
        );

        $roster = $this->input->post('cmb_roster');
        $from_date = $this->input->post('txt_from_date');
        $to_date = $this->input->post('txt_to_date');

        $d1 = new DateTime($from_date);
        $d2 = new DateTime($to_date);
        $interval = $d2->diff($d1)->days;

        for ($x = 0; $x <= $interval; $x++) {
            $dayNameMap = ['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday'];
            $dayOfWeek = date('N', strtotime($from_date));
            $Current_date = $dayNameMap[$dayOfWeek] ?? '';

            $from_date_fmt = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));

            $Holiday = $this->Db_model->getfilteredData("SELECT COUNT(Hdate) AS HasRow FROM tbl_holidays WHERE Hdate = '$from_date_fmt'");
            $year = date("Y");

            $ros = $this->Db_model->getfilteredData("SELECT ts.ShiftCode, tr.DayName, tr.ShiftType, ts.FromTime, ts.ToTime, ts.DayType, ts.ShiftGap, ts.NextDay, ts.FHDSessionEndTime, ts.SHDSessionStartTime FROM tbl_rosterpatternweeklydtl tr INNER JOIN tbl_shifts ts ON ts.ShiftCode = tr.ShiftCode WHERE tr.RosterCode = '$roster' AND tr.DayName = '$Current_date'");
            $ros = $ros[0];

            $DayStatus = $ros->ShiftType === 'EX' ? 'EX' : ($Holiday[0]->HasRow == 1 ? 'HD' : 'AB');
            $ShiftType = $Holiday[0]->HasRow == 1 ? 'HD' : $ros->ShiftType;
            $NoPay = ($ShiftType === 'EX') ? 0 : ($Holiday[0]->HasRow == 1 ? 0 : 1);
            $to_date_sh = $ros->NextDay == 1 ? date('Y-m-d H:i:s', strtotime($from_date . ' +1 day')) : $from_date;

            foreach ($EmpData as $emp) {
                $EmpNo = $emp->EmpNo;
                $GroupID = $this->Db_model->getfilteredData("SELECT Grp_ID FROM tbl_empmaster WHERE EmpNo = $EmpNo")[0]->Grp_ID;
                $GracePeriod = $this->Db_model->getfilteredData("SELECT GracePeriod FROM tbl_emp_group WHERE Grp_ID = $GroupID")[0]->GracePeriod;

                $dataArray = [
                    'RYear' => $year,
                    'EmpNo' => $EmpNo,
                    'ShiftCode' => $ros->ShiftCode,
                    'ShiftDay' => $ros->DayName,
                    'Day_Type' => $ros->DayType,
                    'ShiftIndex' => 1,
                    'FDate' => $from_date,
                    'FTime' => $ros->FromTime,
                    'TDate' => $to_date_sh,
                    'TTime' => $ros->ToTime,
                    'ShType' => $ShiftType,
                    'HDSession' => $ros->FHDSessionEndTime,
                    'HDESession' => $ros->SHDSessionStartTime,
                    'DayStatus' => $DayStatus,
                    'GapHrs' => $ros->ShiftGap,
                    'GracePrd' => $GracePeriod,
                    'nopay' => $NoPay,
                ];

                $HasR = $this->Db_model->getfilteredData("SELECT COUNT(EmpNo) AS HasRow FROM tbl_individual_roster WHERE EmpNo = '$EmpNo' AND FDate = '$from_date'")[0]->HasRow;

                if ($HasR == 1) {
                    $this->session->set_flashdata('error_message', 'Already Shift Allocated');
                } else {
                    $this->Db_model->insertData("tbl_individual_roster", $dataArray);
                    $this->session->set_flashdata('success_message', 'Shift Allocation Processed successfully');
                }
            }

            $from_date = date("Y-m-d", strtotime("+1 day", strtotime($from_date)));
        }

        redirect('/Attendance/Shift_Allocation');
    }
}
