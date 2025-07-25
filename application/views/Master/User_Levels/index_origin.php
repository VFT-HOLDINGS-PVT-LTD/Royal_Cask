<!DOCTYPE html>


<!--Description of dashboard page

@author Ashan Rathsara-->


<html lang="en">


    <head>
        <!-- Styles -->
        <?php $this->load->view('template/css.php'); ?>

    </head>

    <body class="infobar-offcanvas">

        <!--header-->

        <?php $this->load->view('template/header.php'); ?>

        <!--end header-->

        <div id="wrapper">
            <div id="layout-static">

                <!--dashboard side-->

                <?php $this->load->view('template/dashboard_side.php'); ?>

                <!--dashboard side end-->

                <div class="static-content-wrapper">
                    <div class="static-content">
                        <div class="page-content">
                            <ol class="breadcrumb">

                                <li class=""><a href="<?php echo base_url(); ?>Dashboard/">HOME</a></li>
                                <li class="active"><a href="<?php echo base_url(); ?>Master/User_Levels/">USER LEVELS</a></li>

                            </ol>


                            <div class="page-tabs">
                                <ul class="nav nav-tabs">

                                    <li class="active"><a data-toggle="tab" href="#tab1">USER LEVELS</a></li>
                                    <li><a data-toggle="tab" href="#tab2">VIEW USER LEVELS</a></li>
                                    <li><a data-toggle="tab" href="#tab3">VIEW PERMISSIONS</a></li>


                                </ul>
                            </div>
                            <div class="container-fluid">


                                <div class="tab-content">
                                    <div class="tab-pane active" id="tab1">

                                        <div class="row">
                                            <div class="col-xs-12">


                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="panel panel-info">
                                                            <div class="panel-heading"><h2>ADD USER LEVELS</h2></div>
                                                            <div class="panel-body">
                                                                <form class="form-horizontal" id="frm_user_level" name="frm_user_level" action="<?php echo base_url(); ?>Master/User_Levels/insert_data" method="POST">

                                                                    <div class="form-group col-sm-12">
                                                                        <div class="col-sm-8">
                                                                            <img class="imagecss" src="<?php echo base_url(); ?>assets/images/userlevel.png" >
                                                                        </div>
                                                                    </div>


                                                                    <div class="form-group col-sm-6">
                                                                        <label for="focusedinput" class="col-sm-4 control-label">User Level Name</label>
                                                                        <div class="col-sm-8">
                                                                            <input type="text" class="form-control" required="" id="txt_user_level" name="txt_user_level" placeholder="Ex: Admin">
                                                                        </div>

                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-sm-8 col-sm-offset-2">
                                                                            <button type="submit" id="submit"  class="btn-primary btn fa fa-check">&nbsp;&nbsp;Submit</button>
                                                                            <button type="button" id="Cancel" name="Cancel" class="btn btn-danger-alt fa fa-times-circle">&nbsp;&nbsp;Cancel</button>
                                                                        </div>
                                                                    </div>


                                                                </form>
                                                                <hr>

                                                            </div>

                                                            <!-- <div id="divmessage" class="">
                                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                                                <div id="spnmessage"> </div>
                                                            </div> -->

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>
                                        </div>

                                    </div>


                                    <!--***************************-->
                                    <!--User Level View Grid-->

                                    <div class="tab-pane" id="tab2">

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="panel panel-primary">
                                                    <div class="col-md-12">
                                                        <div class="panel panel-default">
                                                            <div class="panel-heading">
                                                                <h2>USER LEVEL DETAILS</h2>
                                                                <div class="panel-ctrls">
                                                                </div>
                                                            </div>
                                                            <div class="panel-body panel-no-padding">
                                                                <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>ID</th>
                                                                            <th>USER LEVEL</th>
                                                                            <th>EDIT</th>
                                                                            <th>DELETE</th>

                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                        foreach ($data_set as $data) {


                                                                            echo "<tr class='odd gradeX'>";


                                                                            echo "<td width='100'>" . $data->user_level_id . "</td>";
                                                                            echo "<td width='100'>" . $data->user_level_name . "</td>";

                                                                            echo "<td width='15'>";
                                                                            echo "<button class='get_data btn btn-green'  data-toggle='modal' data-target='#myModal' title='EDIT' data-id='$data->user_level_id' href='" . base_url() . "index.php/Master/Department/get_details" . $data->user_level_id . "'><i class='fa fa-edit'></i></button>";
                                                                            echo "</td>";

                                                                            echo "<td width='15'>";

                                                                            echo "<button  class='action_comp btn btn-danger' data-toggle='modal' href='javascript:void()' title='DELETE' onclick='delete_id($data->user_level_id)'><i class='fa fa-times-circle'></i></a>";


                                                                            echo "</td>";

                                                                            echo "</tr>";
                                                                        }
                                                                        ?>
                                                                    </tbody>
                                                                </table>
                                                                <div class="panel-footer"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>


                                    <!--End View Grid-->

                                    <!--VIEW USER LEVEL PERMISIONS-->
                                    <div class="tab-pane" id="tab3">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="panel panel-primary">
                                                            <div class="panel panel-default">
                                                                <div class="panel-heading">
                                                                    <h2>USER LEVEL PERMISSIONS</h2>
                                                                </div>
                                                                <div class="panel-body panel-no-padding">
                                                                    <form class="form-horizontal" id="frm_user_level_view" name="frm_user_level_view"
                                                                        action="<?php echo base_url(); ?>Master/User_Levels/update_permission_data"
                                                                        method="POST" style="margin-top: 20px;">
                                                                        <div class="form-group col-sm-12" style="display: flex; align-items: center;">
                                                                          <label for="user_level_select" class="col-sm-4 control-label" style="text-align: right;">Select User Level</label>
                                                                          <div class="col-sm-6 input-group" style="margin-left: 10px;">
                                                                            <select class="form-control" id="user_level_select" name="user_level_select" required>
                                                                                <option value="">-- Select User Level --</option>
                                                                                <?php foreach ($data_set as $data): ?>
                                                                                  <option value="<?php echo $data->user_level_id; ?>"><?php echo $data->user_level_name; ?></option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                          </div>
                                                                        </div>

                                                                        <input type="hidden" name="user_level_id" id="user_level_id_input">

                                                                        <div class="form-group col-sm-10">
                                                                          <label for="permissions_checklist" style="font-weight: bold; margin-left: 10px;">Permissions Checklist</label>
                                                                          <div class="col-sm-12">
                                                                            <div class="row">
                                                                                <?php
                                                                                $permissions = [
                                                                                  "Dashboard", "master_data", "designation", "department",
                                                                                  "holiday_types", "holidays", "shifts", "weekly_roster",
                                                                                  "user_level", "leave_types", "banks", "bank_accounts",
                                                                                  "payees", "loan_types", "allowance_types", "deduction_types",
                                                                                  "branches", "employee_groups", "Employee_mgt", "add_employee",
                                                                                  "add_employee_branch", "view_employee", "Attendance", "shift_allocation",
                                                                                  "attendance_collection", "attendance_row_data", "manual_attendance", "manual_att_request",
                                                                                  "Is_manual_Sup", "Is_manual_Admin", "attendance_process", "Leave_Transaction",
                                                                                  "view_lv_balance", "leave_allocation", "leave_approve", "leave_app_sup",
                                                                                  "leave_entry", "leave_request", "leave_adj", "Payroll",
                                                                                  "allowance", "deduction", "loan_entry", "salary_increment",
                                                                                  "salary_advance", "request_advance", "approve_advance", "payroll_process",
                                                                                  "Cheque", "write_cheque", "view_cheque", "Messages",
                                                                                  "send_message", "view_message", "company", "company_profile",
                                                                                  "Reports", "Master_Reports", "employee_report", "designation_report",
                                                                                  "department_report", "holidays_report", "employee_birthdays", "Attendance_Report",
                                                                                  "in_out_report", "overtime_report", "absence_report", "leave_report",
                                                                                  "late_report", "monthly_summery_report", "leave_summery_report", "ot_summery_report",
                                                                                  "Payroll_reports", "allowance_report", "deduction_report", "salary_advance_report",
                                                                                  "paysheet_report", "payslip_report", "Analysis_Report", "month_absence_rpt",
                                                                                  "attendance_rpt", "leave_rpt", "Tools", "System_backup",
                                                                                  "dsh_report", "dsh_ch_1", "dsh_ch_2", "dsh_ch_3",
                                                                                  "dsh_rpt_in_out", "dsh_rpt_paysheet", "dsh_rpt_leave", "dsh_rpt_emp_master",
                                                                                  "dsh_rpt_sal_advance", "dsh_rpt__absence", "header_lv_nt"
                                                                                ];

                                                                                $columns = 4; // Number of columns for checkboxes
                                                                                $chunked_permissions = array_chunk($permissions, ceil(count($permissions) / $columns));

                                                                                foreach ($chunked_permissions as $column) {
                                                                                  echo '<div class="col-sm-12 col-md-3">'; // Adjusted for 4 columns
                                                                                  foreach ($column as $permission) {
                                                                                    $checkbox_id = 'perm_' . $permission;
                                                                                    $is_checked = isset($permission_data[$permission]) && $permission_data[$permission] == 1 ? 'checked' : '';
                                                                                    $is_disabled = $permission === 'user_level' ? 'disabled' : ''; // Disable 'user_level' checkbox
                                                                                    $readonly_attribute = $is_disabled ? 'onclick="return false;"' : ''; // Prevent changes for disabled checkboxes
                                                                                    echo '<label><input type="checkbox" id="' . $checkbox_id . '" name="permissions[]" value="' . $permission . '" ' . $is_checked . ' ' . $is_disabled . ' ' . $readonly_attribute . '> ' . ucfirst(str_replace('_', ' ', $permission)) . '</label><br>';
                                                                                  }
                                                                                  echo '</div>';
                                                                                }
                                                                                ?>
                                                                            </div>
                                                                            <div style="text-align: right;">
                                                                                <button type="submit" class="btn btn-success">
                                                                                  <i class="fa fa-check"></i> Update Permissions
                                                                                </button>
                                                                            </div>
                                                                          </div>
                                                                        </div>
                                                                    </form>

                                                                    <script>
                                                                        // Set hidden input when dropdown changes
                                                                        document.getElementById("user_level_select").addEventListener("change", function () {
                                                                          document.getElementById("user_level_id_input").value = this.value;
                                                                        });
                                                                    </script>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                </div>
                                                            </div>
                                                    </div>
                                                </div>
                                            </div>

                                            </div>
                                    
                                    <!--END VIEW USER LEVEL PERMISIONS-->
                                    <!--***************************-->


                                </div>


                                <!-- Modal -->
                                <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                <h2 class="modal-title">USER LEVELS</h2>
                                            </div>
                                            <div class="modal-body">
                                                <form class="form-horizontal" action="<?php echo base_url(); ?>Master/User_Levels/edit" method="post">
                                                    <div class="form-group col-sm-12">
                                                        <label for="focusedinput" class="col-sm-4 control-label">ID</label>
                                                        <div class="col-sm-8">
                                                            <input value="<?php echo $data->user_level_id; ?>" type="text" class="form-control" readonly="readonly" name="id" id="id" class="m-wrap span3" >
                                                        </div>
                                                    </div>

                                                    <div class="form-group col-sm-12">
                                                        <label for="focusedinput" class="col-sm-4 control-label">NAME</label>
                                                        <div class="col-sm-8">
                                                            <input value="<?php echo $data->user_level_name; ?>" type="text" name="user_level_name" id="user_level_name"  class="form-control m-wrap span6"><br>
                                                        </div>
                                                    </div>
                                            </div>

                                            <br>
                                            <!--<input class="btn green" type="submit" value="submit" id="submit">-->
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                <button type="submit" id="submit" class="btn btn-primary">Save changes</button>
                                            </div>
                                            </form>
                                        </div>

                                    </div><!-- /.modal-content -->
                                </div><!-- /.modal-dialog -->
                            </div><!-- /.modal -->

                        </div> <!-- .container-fluid -->


                        <!--Footer-->
                        <?php $this->load->view('template/footer.php'); ?>	
                        <!--End Footer-->
                    </div>
                </div>
            </div>




            <!-- Load site level scripts -->

            <?php $this->load->view('template/js.php'); ?>							<!-- Initialize scripts for this page-->


            <!-- End loading page level scripts-->

            <!--Ajax-->
            <script src="<?php echo base_url(); ?>system_js/Master/User_Levels.js"></script>
            <script>
    $(document).ready(function () {
        $('#user_level_select').change(function () {
            var user_level_id = $(this).val();
            
            if (user_level_id !== '') {
                $.ajax({
                    url: "<?php echo base_url(); ?>Master/User_Levels/get_permission_data",
                    type: "POST",
                    data: { user_level_select: user_level_id },
                    dataType: "json",
                    success: function (response) {
                        // First uncheck all
                        $('input[type="checkbox"][name="permissions[]"]').prop('checked', false);

                        if (Array.isArray(response) && response.length > 0) {
                            var permissions = response[0]; // Assuming only one row
                            for (const key in permissions) {
                                if (permissions.hasOwnProperty(key) && key !== 'user_p_id' && key !== 'user_level_name') {
                                    var checkbox = $('#perm_' + key);
                                    if (checkbox.length > 0 && permissions[key] == 1) {
                                        checkbox.prop('checked', true);
                                    }
                                }
                            }
                        } else {
                            alert("No permission data found.");
                        }
                    },
                    error: function (xhr, status, error) {
                        alert("An error occurred: " + error);
                    }
                });
            }
        });
    });
</script>



    </body>


</html>