<?php
//程序安装文件
error_reporting(0);
$databaseFile = '../config.php';//数据库配额文件

if(defined('SAE_ACCESSKEY')){
	header('Location: en-saeinstall.php');
	exit();
}

@header('Content-Type: text/html; charset=UTF-8');
$step=isset($_GET['step'])?$_GET['step']:1;
$action=isset($_POST['action'])?$_POST['action']:null;
if(file_exists('install.lock')){
    exit('You have successfully installed, if you need to re-install, please manually delete the install directory under install.lock file!');
}


function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {

	}
	return $hash;
}

if($action=='install'){
    $db_host=isset($_POST['db_host'])?$_POST['db_host']:null;
    $db_port=isset($_POST['db_port'])?$_POST['db_port']:null;
    $db_user=isset($_POST['db_user'])?$_POST['db_user']:null;
    $db_pwd=isset($_POST['db_pwd'])?$_POST['db_pwd']:null;
    $db_name=isset($_POST['db_name'])?$_POST['db_name']:null;
    $admin_user=isset($_POST['admin_user'])?$_POST['admin_user']:null;
    $admin_pwd=isset($_POST['admin_pwd'])?$_POST['admin_pwd']:null;
    if(empty($db_host) || empty($db_port) || empty($db_user) || empty($db_pwd) || empty($db_name)){
        $errorMsg='Please fill out all the database information';
    }elseif(empty($admin_user) || empty($admin_pwd)){
        $errorMsg='Please fill in the administrator information';
    }else{
        try{
            $db=new PDO("mysql:host=".$db_host.";dbname=".$db_name.";port=".$db_port,$db_user,$db_pwd);
        }catch(Exception $e){
            $errorMsg='Linked database failed:'.$e->getMessage();
        }
        if(empty($errorMsg)){
            @file_put_contents($databaseFile,'<?php
/*数据库配置*/
$dbconfig=array(
	"host" => "'.$db_host.'", //Database server
	"port" => '.$db_port.', //Database port
	"user" => "'.$db_user.'", //Database user name
	"pwd" => "'.$db_pwd.'", //Database password
	"dbname" => "'.$db_name.'" //The database name
);
?>');
			date_default_timezone_set("PRC");
			$date = date("Y-m-d");
            $db->exec("set names utf8");
            $sqls=file_get_contents('en-install.sql');
            $sqls=explode(';', $sqls);
			$sqls[]="INSERT INTO `pre_config` VALUES ('syskey', '".random(32)."')";
            $sqls[]="INSERT INTO `pre_config` VALUES ('build', '".$date."')";
            $sqls[]="UPDATE `pre_config` SET `v`='{$admin_user}' WHERE `k`='admin_user'";
            $sqls[]="UPDATE `pre_config` SET `v`='{$admin_pwd}' WHERE `k`='admin_pwd'";
            $success=0;$error=0;$errorMsg=null;
            foreach ($sqls as $value) {
                $value=trim($value);
                if(!empty($value)){
                    if($db->exec($value)===false){
                        $error++;
                        $dberror=$db->errorInfo();
                        $errorMsg.=$dberror[2]."<br>";
                    }else{
                        $success++;
                    }
                }
            }
            $step=3;
			@file_put_contents("install.lock",'Install the lock');
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <title>Cloud tray - The installation program</title>
    <link href="//cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container"><br>
    <div class="row">
        <div class="col-xs-12">
            <pre><h4>Cloud tray - The installation program</h4></pre>
            <div class="col-xs-12 center-block" style="float: none;  margin:0px auto; text-align:center;">
                <h5><a href="../install/">Click here to install Chinese version</a></h5>            
        </div>
        <div class="col-xs-12">
            <div class="panel panel-warning">
                <?php
                if(isset($errorMsg)){
                    echo '<div class="alert alert-danger text-center" role="alert">'.$errorMsg.'</div>';
                }
                if($step==2){
                ?>
                <div class="panel-heading text-center">Please fill in the following information</div>
                <div class="panel-body">
                    <div class="list-group text-success">
                        <form class="form-horizontal" action="#" method="post">
                            <input type="hidden" name="action" class="form-control" value="install">
                            <h4>MYSQL database information configuration</h4>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Database address</label>
                                <div class="col-sm-10">
                                    <input type="text" name="db_host" class="form-control" value="localhost">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Database port</label>
                                <div class="col-sm-10">
                                    <input type="text" name="db_port" class="form-control" value="3306">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Database user name</label>
                                <div class="col-sm-10">
                                    <input type="text" name="db_user" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Database password</label>
                                <div class="col-sm-10">
                                    <input type="text" name="db_pwd" class="form-control">
                                </div>
                            </div>
							<div class="form-group">
                                <label class="col-sm-2 control-label">Database name</label>
                                <div class="col-sm-10">
                                    <input type="text" name="db_name" class="form-control">
                                </div>
                            </div>
                            <hr/>
                            <h4>Administrator information configuration</h4>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Administrator user name</label>
                                <div class="col-sm-10">
                                    <input type="text" name="admin_user" class="form-control" value="admin">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Administrator password</label>
                                <div class="col-sm-10">
                                    <input type="text" name="admin_pwd" class="form-control" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <button type="submit" class="btn btn-success btn-block">Ok, next step</button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
                <?php }elseif($step==3){ ?>
                <div class="panel-heading text-center">Data import completed</div>
                <div class="panel-body">
                    <ul class="list-group">
                        <li class="list-group-item">SQL statement executed successfully<?php echo $success;?>Article, failure<?php echo $error;?>Article!</li>
                        <li class="list-group-item">The system has been installed successfully!</li>
                        <li class="list-group-item">Background Address:<a href="../admin/" target="_blank">/admin/</a></li>
                        <li class="list-group-item">Administrator account:<?php echo $admin_user?>  Password:<?php echo $admin_pwd?></li>
                        <a href="/" class="btn list-group-item">Go to the homepage of the website</a>
                    </ul>
                </div>
                <?php }else{ ?>
                <div class="panel-heading text-center">Installation environment detection</div>
                <div class="panel-body">
                    <?php
                    $install=true;
                    if(!file_exists('./install.lock')){
                        $check[2]='<span class="badge badge-success">unlocked</span>';
                    }else{
                        $check[2]='<span class="badge badge-danger">Has been locked</span>';
                        $install=false;
                    }
                    if(class_exists("PDO")){
                        $check[0]='<span class="badge badge-success">support</span>';
                    }else{
                        $check[0]='<span class="badge badge-danger">Does not support</span>';
                        $install=false;
                    }
                    if($fp = @fopen("../test.txt", 'w')) {
                        @fclose($fp);
                        @unlink("../test.txt");
                        $check[1]='<span class="badge badge-success">support</span>';
                    }else{
                        $check[1]='<span class="badge badge-danger">Does not support</span>';
                        $install=false;
                    }
                    if(version_compare(PHP_VERSION,'5.4.0','<')){
                        $check[3]='<span class="badge badge-danger">Does not support</span>';
						$install=false;
                    }else{
                        $check[3]='<span class="badge badge-success">support</span>';
                    }

                    ?>
                    <ul class="list-group">
                        <li class="list-group-item">Checks if the installation is locked <?php echo $check[2];?></li>
                        <li class="list-group-item">PDO_MYSQL components <?php echo $check[0];?></li>
                        <li class="list-group-item">Home directory write permission <?php echo $check[1];?></li>
                        <li class="list-group-item">PHP version>=5.4 <?php echo $check[3];?></li>
                        <li class="list-group-item">After successful installation, the installation file will be locked. If you need to reinstall, please manually delete the install.lock configuration file in the Install directory!</li>
                        <?php
                        if($install) echo'<a href="?step=2" class="btn list-group-item">Pass the test. Next step</a>';
                        ?>
                    </ul>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <footer class="footer">
        <pre><center>Powered by <a href="https://www.toopan.cn/">Cloud tray</a> !</center></pre>
    </footer>
</div>
</body>
</html>
