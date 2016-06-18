<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pi-hole Admin Console</title>
    <base href="/admin/"/>
    <meta name="description" content="">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <meta name="theme-color" content="#367fa9">
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="images/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="96x96" href="images/favicon-96x96.png">
    <meta name="msapplication-TileColor" content="#367fa9">
    <meta name="msapplication-TileImage" content="images/ms-icon-144x144.png">
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <!-- build:css(.) styles/vendor.css -->
    <!-- bower:css -->
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.css" />
    <link rel="stylesheet" href="bower_components/angular-chart.js/dist/angular-chart.css" />
    <link rel="stylesheet" href="bower_components/datatables/media/css/jquery.dataTables.css" />
    <!-- endbower -->
    <!-- endbuild -->
    <!-- build:css(.tmp) styles/main.css -->
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/AdminLTE.css">
    <link rel="stylesheet" href="styles/skin-blue.css"> 
    <link rel="stylesheet" href="styles/font-awesome-4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="styles/ionicons-2.0.1/css/ionicons.min.css">
    <!-- endbuild -->
</head>
<body ng-app="piholeAdminApp" class="skin-blue sidebar-mini">
<!--[if lte IE 8]>
<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade
    your browser</a> to improve your experience.</p>
<![endif]-->
<div class="wrapper">
    <header class="main-header">
        <!-- Logo -->
        <a href="/admin" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><b>P</b>H</span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b>Pi</b>-hole</span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a  class="dropdown-toggle" data-toggle="dropdown">
                            <img src="images/pihole-160x160.jpg" class="user-image" alt="Pi-hole logo"/>
                            <span class="hidden-xs">Pi-hole</span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="./images/pihole-160x160.jpg" alt="User Image"/>

                                <p>
                                    Open Source Ad Blocker
                                    <small>Designed For Raspberry Pi</small>
                                </p>
                            </li>
                            <!-- Menu Body -->
                            <li class="user-body">
                                <div class="col-xs-4 text-center">
                                    <a href="https://github.com/jacobsalmela/pi-hole">Github</a>
                                </div>
                                <div class="col-xs-4 text-center">
                                    <a href="http://jacobsalmela.com/block-millions-ads-network-wide-with-a-raspberry-pi-hole-2-0/">Details</a>
                                </div>
                                <div class="col-xs-4 text-center">
                                    <a href="https://github.com/pi-hole/pi-hole/releases">Updates</a>
                                </div>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div>
                                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                                        <input type="hidden" name="cmd" value="_s-xclick">
                                        <input type="hidden" name="hosted_button_id" value="3J2L3Z4DHW9UY">
                                        <input style="display: block; margin: 0 auto;" type="image"
                                               src="images/donate.gif" border="0" name="submit"
                                               alt="PayPal - The safer, easier way to pay online!">
                                        <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif"
                                             width="1" height="1">
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar" ng-controller="MenuCtrl">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="images/pihole-160x160.jpg" class="img-circle" alt="Pi-hole logo"/>
                </div>
                <div class="pull-left info">
                    <p>Status</p>
                    <div class="row">
                        <div class="col-xs-5 col-md-5">
                            DNS:
                        </div>
                        <div class="col-xs-3 col-md-3">
                            <span status="status.dnsmasq"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5 col-md-5">
                            Blackhole:
                        </div>
                        <div class="col-xs-3 col-md-3">
                            <span status="status.blackhole"></span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- sidebar menu: : style can be found in sidebar.less -->
            <ul class="sidebar-menu">
                <li class="header">MAIN NAVIGATION</li>
                <!-- Home Page -->
                <li>
                    <a href="#/">
                        <i class="fa fa-home"></i> <span>Main Page</span>
                    </a>
                </li>
                <!-- Query Log -->
                <li>
                    <a ng-href="#/queries">
                        <i class="fa fa-file-text-o"></i> <span>Query Log</span>
                    </a>
                </li>
                <li>
                    <a ng-href="#/whitelist">
                        <i class="fa fa-pencil-square-o"></i> <span>Whitelist</span>
                    </a>
                </li>
                <!-- Blacklist -->
                <li>
                    <a ng-href="#/blacklist">
                        <i class="fa fa-ban"></i> <span>Blacklist</span>
                    </a>
                </li>
                <!-- Donate -->
                <li>
                    <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3J2L3Z4DHW9UY">
                        <i class="fa fa-paypal"></i> <span>Donate</span>
                    </a>
                </li>
            </ul>
        </section>
        <!-- /.sidebar -->
    </aside>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content">
                <div ng-view=""></div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <footer class="main-footer">
        <div class="pull-right hidden-xs">
            <b>Pi-hole Version </b> <?php echo exec("cd /etc/.pihole/ && git describe --tags --abbrev=0"); ?>
            <b>Web Interface
                Version </b> <?php echo exec("cd /var/www/html/admin/ && git describe --tags --abbrev=0"); ?>
        </div>
        <i class="fa fa-github"></i> <strong><a
            href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=3J2L3Z4DHW9UY">Donate</a></strong>
        if you found this useful.
    </footer>
</div>
<!-- ./wrapper -->


<!-- build:js(.) scripts/vendor.js -->
<!-- bower:js -->
<script src="bower_components/jquery/dist/jquery.js"></script>
<script src="bower_components/angular/angular.js"></script>
<script src="bower_components/bootstrap/dist/js/bootstrap.js"></script>
<script src="bower_components/angular-animate/angular-animate.js"></script>
<script src="bower_components/angular-cookies/angular-cookies.js"></script>
<script src="bower_components/angular-resource/angular-resource.js"></script>
<script src="bower_components/angular-route/angular-route.js"></script>
<script src="bower_components/angular-sanitize/angular-sanitize.js"></script>
<script src="bower_components/angular-touch/angular-touch.js"></script>
<script src="bower_components/Chart.js/Chart.js"></script>
<script src="bower_components/angular-chart.js/dist/angular-chart.js"></script>
<script src="bower_components/datatables/media/js/jquery.dataTables.js"></script>
<script src="bower_components/angular-datatables/dist/angular-datatables.js"></script>
<script src="bower_components/angular-datatables/dist/plugins/bootstrap/angular-datatables.bootstrap.js"></script>
<script src="bower_components/angular-datatables/dist/plugins/colreorder/angular-datatables.colreorder.js"></script>
<script src="bower_components/angular-datatables/dist/plugins/columnfilter/angular-datatables.columnfilter.js"></script>
<script src="bower_components/angular-datatables/dist/plugins/light-columnfilter/angular-datatables.light-columnfilter.js"></script>
<script src="bower_components/angular-datatables/dist/plugins/colvis/angular-datatables.colvis.js"></script>
<script src="bower_components/angular-datatables/dist/plugins/fixedcolumns/angular-datatables.fixedcolumns.js"></script>
<script src="bower_components/angular-datatables/dist/plugins/fixedheader/angular-datatables.fixedheader.js"></script>
<script src="bower_components/angular-datatables/dist/plugins/scroller/angular-datatables.scroller.js"></script>
<script src="bower_components/angular-datatables/dist/plugins/tabletools/angular-datatables.tabletools.js"></script>
<script src="bower_components/angular-datatables/dist/plugins/buttons/angular-datatables.buttons.js"></script>
<script src="bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js"></script>
<!-- endbower -->
<!-- endbuild -->

<!-- build:js({.tmp,app}) scripts/scripts.js -->
<script src="scripts/app.js"></script>
<script src="scripts/controllers/menu.js"></script>
<script src="scripts/controllers/dashboard.js"></script>
<script src="scripts/controllers/querylog.js"></script>
<script src="scripts/services/api.js"></script>
<script src="scripts/directives/status.js"></script>
<script src="scripts/directives/glow.js"></script>
<script src="scripts/controllers/list.js"></script>
<script src="scripts/legacy/app.min.js"></script>
<script src="scripts/services/cacheservice.js"></script>
<script src="scripts/directives/ngenter.js"></script>
<!-- endbuild -->
</body>
</html>
