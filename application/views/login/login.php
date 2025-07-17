<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Favicon icon-->
    <link rel="shortcut icon" type="image/png" href="<?php echo base_url(); ?>assets/images/vft_footer.png" />

    <!-- Core Css -->
    <!-- <link rel="stylesheet" href="https://bootstrapdemos.adminmart.com/matdash/dist/assets/css/styles.css" /> -->
    <link href="<?php echo base_url(); ?>assets/css/styles1.css" type="text/css" rel="stylesheet">

    <title>VFT PAYROLL SYSTEM</title>
    <style>
        /* Snowflake styles */
        .snowflake {
            position: fixed;
            top: -10px;
            z-index: 9999;
            color: white;
            font-size: 1em;
            animation: fall linear infinite;
        }

        @keyframes fall {
            0% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(100vh);
            }
        }
        input::placeholder {
            color: white; /* Change to any color you want */
            opacity: 1; /* Optional: default is 0.5 in most browsers */
        }
    </style>
</head>

<body>
    <!-- Preloader -->
    <div class="preloader">
        <img src="<?php echo base_url(); ?>assets/images/vft_footer.png" alt="loader" class="lds-ripple img-fluid" />
    </div>
    <div id="main-wrapper">
        <div class="position-relative overflow-hidden auth-bg min-vh-100 w-100 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100 my-5 my-xl-0">
                    <div class="col-md-9 col-lg-4 d-flex flex-column justify-content-center">
                        <!-- ✅ Updated this card div with semi-transparent background -->
                        <div class="card mb-0 auth-login m-auto w-100" style="background-color: rgb(0 19 41 / 86%);">
                            <div class="row gx-0">
                                <div class="col-xl-12">
                                    <div class="row justify-content-center py-4">
                                        <div class="col-lg-11 col-md-12">
                                            <div class="card-body text-center">
                                                <!-- Logo -->
                                                <a href="../main/index.html" class="text-nowrap logo-img d-block mb-4 w-100">
                                                    <img src="assets/images/company/rc-logo-top.svg" class="dark-logo" alt="Logo-Dark" style="width: 400px;" />
                                                </a>
                                                <h3 class="lh-base mb-4" style="font-size: 18px;color: white;">Let's get you signed in</h3>

                                                <form class="form-horizontal" id="frmLogin" name="frmLogin" action="<?php echo base_url() ?>login/verifyUser" method="POST">
                                                    <div class="mb-4">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <label class="form-label" style="color: #ffffff;">Username</label>
                                                        </div>
                                                        <input type="text" class="form-control" id="txt_username" name="txt_username" placeholder="Username" data-parsley-minlength="6" required="" aria-describedby="emailHelp">
                                                    </div>
                                                    <div class="mb-4">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <label class="form-label" style="color: #ffffff;">Password</label>
                                                        </div>
                                                        <input type="password" class="form-control" id="txt_password" name="txt_password" placeholder="Password" required="">
                                                    </div>
                                                    <div id="divmessage">
                                                        <center>
                                                            <div id="spnmessage" style="color: #002640;font-weight: bold; margin-top: 0px;"></div>
                                                        </center>
                                                    </div>
                                                    <button type="submit" id="btnSubmit" name="btnSubmit" class="btn btn-dark w-100 py-8 mb-4 rounded-1" style="background-color: #ffffff;border-color: #ffffff;color: #021133;font-weight: bold;">Sign In</button>
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <p class="fs-12 mb-0 fw-medium" style="color: #ffffff;">VFT PAYROLL SYSTEM V0.2</p>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- End card -->
                    </div><!-- End col-md-6 -->
                </div>

            </div>
        </div>
    </div>
    <!-- Snowflake Container -->
    <div id="snow-container"></div>
    <div class="dark-transparent sidebartoggler"></div>
    <!-- Import Js Files -->
    <script src="<?php echo base_url(); ?>assets/js/newjs/bootstrap.bundle.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/newjs/simplebar.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/newjs/app.init.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/newjs/theme.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/newjs/app.min.js"></script>


    <!-- <script src="https://bootstrapdemos.adminmart.com/matdash/dist/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script> -->
    <!-- <script src="https://bootstrapdemos.adminmart.com/matdash/dist/assets/libs/simplebar/dist/simplebar.min.js"></script> -->
    <!-- <script src="https://bootstrapdemos.adminmart.com/matdash/dist/assets/js/theme/app.init.js"></script> -->
    <!-- <script src="https://bootstrapdemos.adminmart.com/matdash/dist/assets/js/theme/theme.js"></script> -->
    <!-- <script src="https://bootstrapdemos.adminmart.com/matdash/dist/assets/js/theme/app.min.js"></script> -->

    <!-- solar icons -->
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

    <script src="<?php echo base_url(); ?>assets/js/jquery-1.10.2.min.js"></script> <!-- Load jQuery -->
    <script src="<?php echo base_url(); ?>assets/js/jqueryui-1.9.2.min.js"></script> <!-- Load jQueryUI -->
    <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script> <!-- Load Bootstrap -->
    <script src="<?php echo base_url(); ?>assets/plugins/easypiechart/jquery.easypiechart.js"></script> <!-- EasyPieChart-->
    <script src="<?php echo base_url(); ?>assets/plugins/sparklines/jquery.sparklines.min.js"></script> <!-- Sparkline -->
    <script src="<?php echo base_url(); ?>assets/plugins/jstree/dist/jstree.min.js"></script> <!-- jsTree -->
    <script src="<?php echo base_url(); ?>assets/plugins/codeprettifier/prettify.js"></script> <!-- Code Prettifier  -->
    <script src="<?php echo base_url(); ?>assets/plugins/bootstrap-switch/bootstrap-switch.js"></script> <!-- Swith/Toggle Button -->
    <script src="<?php echo base_url(); ?>assets/plugins/bootstrap-tabdrop/js/bootstrap-tabdrop.js"></script> <!-- Bootstrap Tabdrop -->
    <script src="<?php echo base_url(); ?>assets/plugins/iCheck/icheck.min.js"></script> <!-- iCheck -->
    <script src="<?php echo base_url(); ?>assets/js/enquire.min.js"></script> <!-- Enquire for Responsiveness -->
    <script src="<?php echo base_url(); ?>assets/plugins/bootbox/bootbox.js"></script> <!-- Bootbox -->
    <script src="<?php echo base_url(); ?>assets/plugins/simpleWeather/jquery.simpleWeather.min.js"></script> <!-- Weather plugin-->
    <script src="<?php echo base_url(); ?>assets/plugins/nanoScroller/js/jquery.nanoscroller.min.js"></script> <!-- nano scroller -->
    <script src="<?php echo base_url(); ?>assets/plugins/jquery-mousewheel/jquery.mousewheel.min.js"></script> <!-- Mousewheel support needed for jScrollPane -->
    <script src="<?php echo base_url(); ?>assets/js/application.js"></script>
    <script src="<?php echo base_url(); ?>assets/demo/demo.js"></script>
    <script src="<?php echo base_url(); ?>assets/demo/demo-switcher.js"></script>
    <script src="<?php echo base_url(); ?>system_js/utility.js" type="text/javascript"></script>

    <!--Ajax-->
    <script src="<?php echo base_url(); ?>system_js/login/login.js"></script>

    <!--Jquary Validation-->
    <script src="<?php echo base_url(); ?>assets/plugins/validation/jquery.validate.js"></script>

    <!--JQuary Validation-->
    <script type="text/javascript">
        $(document).ready(function() {
            $("#frmLogin").validate({
                rules: {
                    txt_username: {
                        required: true,
                        minlength: 1
                    },
                    txt_password: {
                        required: true,
                        minlength: 1
                    },
                },
                messages: {
                    txt_username: {
                        required: "<i class='fa fa-user'></i>  Please enter Username !",
                        minlength: "Your username must consist of at least 1 characters"
                    },
                    txt_password: {
                        required: "<i class='fa fa-key'></i> Please enter your Password !",
                        minlength: "Your password must be at least 1 characters long"
                    }

                }
            });
        });
    </script>

    <script>
        // Function to hide preloader after 10 seconds
        setTimeout(function() {
            document.querySelector('.preloader').classList.add('hidden');
            document.querySelector('.content').style.display = 'block';
        }, 1000); // 10000 milliseconds = 10 seconds
    </script>
    <!--Clear Text Boxes-->
    <script type="text/javascript">
        $("#cancel").click(function() {

            $("#txt_username").val("");
            $("#txt_password").val("");

        });
    </script>
    <script src="<?php echo base_url(); ?>assets/js/newjs/bootstrap.bundle.min.js"></script>
    <script>
        // Snowflake generator
        function createSnowflake() {
            // Get the current date
            const currentDate = new Date();

            // Check if the current date is in December
            if (currentDate.getMonth() === 11) { // December is month 11 (0-indexed)
                const snowflake = document.createElement('div');
                snowflake.classList.add('snowflake');
                snowflake.style.left = Math.random() * 100 + 'vw';
                snowflake.style.animationDuration = Math.random() * 3 + 2 + 's';
                snowflake.style.fontSize = Math.random() * 10 + 10 + 'px';
                snowflake.style.color = 'white'; // Ensure snowflakes are white
                snowflake.textContent = '❆';
                document.body.appendChild(snowflake);

                setTimeout(() => {
                    snowflake.remove();
                }, 5000);
            }
        }

        // Run the snowflake generator every 200ms only in December
        setInterval(createSnowflake, 200);
    </script>
</body>

</html>