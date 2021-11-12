<?php

require '../scripts/config.inc.php';
require '../scripts/wampserver.lib.php';

$langue = $wampConf['language'];
include './wamplangues/index_'.$langue.'.php';

$issql   = (isset($wampConf['SupportMySQL']) && $wampConf['SupportMySQL'] =='on') ? true : false;
$ismaria = (isset($wampConf['SupportMariaDB']) && $wampConf['SupportMariaDB'] =='on') ? true : false;

$WampPhpVersion     = $wampConf['phpVersion'];
$WampApacheVersion  = $wampConf['apacheVersion'];
$WampMySqlVersion   = $wampConf['mysqlVersion'];

$WampVirtualHostSubMenu = $wampConf['VirtualHostSubMenu'];
$WampVirtualHost = check_virtualhost();

$systemlink = getSystemLink('../alias/');


?>


<!DOCTYPE html>
<html>
<head>

	<title> SERVER :: W.A.M.P  </title>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="style.css" />
	<link rel="shortcut icon" href="favicon.ico" type="image/ico" />

</head>

<body>

    <div class="page">

        <div class="limits">

            <div class="wamp-server-logo <?php if($wampConf['apachePortUsed']){echo'wamp-active';}?>">
                <img alt="-wamp-" src="data:image/png;base64,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" />
            </div>

            <div class="pagetitle">
                <h1> version <?php echo $c_wampVersion ?> - <?php echo $c_wampMode?></h1>
                <p>[ <?php // echo ''.$_SERVER['SERVER_SOFTWARE']?> <?php echo'PHP '.$WampPhpVersion.' - APACHE '.$WampApacheVersion; if($issql){echo' - MYSQL '.$WampMySqlVersion;} if($ismaria) {echo' & MariaDB';}?> ]</p>
            </div>

            <div class="mainlinkbox">
                <? echo $systemlink ?>
            </div>

            <hr>

            <div class="newaliasbox">
                <a class="newalias" href="<?php echo 'add_vhost.php?lang='.$langue; ?>"><?php echo $langues['txtAddVhost']; ?> ✚</a>
            </div>

            <div class="aliasbox">
                <input type="search" placeholder="search a site via name..." value="">
                <div class="list">
                    <?
                        $invirtualhost = getvirtualhost($langues,$WampVirtualHostSubMenu,$WampVirtualHost,$WampApacheVersion);
                        foreach ($invirtualhost as &$value) echo $value;
                    ?>
                </div>
            </div>

        </div>

    </div>

</body>

<?

    function getSystemLink()
    {
        $systemlinks = '';

        if (is_dir('../alias/'))
        {

            $handle = opendir('../alias/');
            $noDBMS = (empty($MySQLdb) && empty($MariaDB)) ? true : false;
            $phpmyadminTool = $noDBMS ? '' : '<a href="phpmyadmin/">phpmyadmin</a>';

            while (($file = readdir($handle))!==false)
                if (is_file('../alias/'.$file) && strstr($file, '.conf'))
                    if(!($noDBMS && ($file == 'phpmyadmin.conf' || $file == 'adminer.conf')))
                        $systemlinks .= '<a target="_blank" href="'.str_replace('.conf','',$file).'/">⚒ SYSTEM INFO ('.str_replace('.conf','',$file).')</a>';

            closedir($handle);

        }

        return $systemlinks;
    }

    function getvirtualhost($langues,$WampVirtualHostSubMenu,$WampVirtualHost,$WampApacheVersion)
    {

        $HostList = array();

        if($WampVirtualHostSubMenu == "on")
        {

            $data = '';

            // Virtual Host is no active
            if($WampVirtualHost['include_vhosts'] === false)
            {

                $data = `
                    <div class="error" data-sercher="No Include in Apache">
                        <div><p><span>⛛</span> No Include in Apache</p></div>
                        <div><p>`.sprintf('Uncomment or add <i>Include conf/extra/httpd-vhosts.conf</i> in file wamp/bin/apache/apache%s/conf/httpd.conf',$WampApacheVersion ).`</p></div>
                    </div>
                `; array_push($HostList,$data);

            }

            // Virtual Host is no exist
            else if($WampVirtualHost['vhosts_exist'] === false)
            {

                $data = `
                    <div class="error" data-sercher="No vitrual hosts file">
                        <div><p><span>⛛</span> No vitrual hosts file</p></div>
                        <div><p>`.sprintf('The file: %s does not exists',$WampVirtualHost['vhosts_file']).`</p></div>
                    </div>
                `; array_push($HostList,$data);

            }

            // Virtual Host exist but...
            else if($WampVirtualHost['nb_Server'] > 0)
            {

                    ///// all virtual host checks:

                    $Error = false;
                    foreach($WampVirtualHost['ServerName'] as $value)
                    // foreach($WampVirtualHost['ServerName'] as $key => $value)
                    {

                        if($value!=$_SERVER["REMOTE_ADDR"] && $value!=$_SERVER['HTTP_HOST'])
                        {

                            //check wrong name of VirtualHost
                            if($WampVirtualHost['ServerNameValid'][$value] === false)
                            {

                                $Error     = true;
                                $errortype = 'syntax error';
                                $errormex  = '<p>'.sprintf('The ServerName %s has syntax error in file %s','<span style="color:black;">'.$value.'</span>',$WampVirtualHost['vhosts_file']).'</p>';

                            }
                            elseif($WampVirtualHost['ServerNameValid'][$value] === true)
                            {


                                $UrlPortVH = ($WampVirtualHost['ServerNamePort'][$value] != '80') ? ':'.$WampVirtualHost['ServerNamePort'][$value] : '';

                                if(!$WampVirtualHost['port_listen'] && $WampVirtualHost['ServerNamePortListen'][$value] !== true || $WampVirtualHost['ServerNamePortApacheVar'][$value] !== true)
                                {

                                    $value_url = ((strpos($value, ':') !== false) ? strstr($value,':',true) : $value);
                                    $apache_port_listener = ($WampVirtualHost['ServerNamePortListen'][$value] !== true) ?  ' not an Apache Listen port' : ' not an Apache define variable';

                                    if(!$Error)
                                    {

                                        $Error     = true;
                                        $errortype = 'Not a Listen port on : '.'( '.$value_url.$UrlPortVH.' )';
                                        $errormex  = '<p>Port '.$UrlPortVH.' used for the VirtualHost is '.$apache_port_listener.'</p>';

                                    }

                                }

                                elseif($WampVirtualHost['ServerNameIp'][$value] !== false)
                                {

                                    $vh_ip = $WampVirtualHost['ServerNameIp'][$value];

                                    if($WampVirtualHost['ServerNameIpValid'][$value] !== false)
                                    {

                                        $Error     = true;
                                        $errortype = 'syntax error';
                                        $errormex  = '<p><a href="http://'.$vh_ip.$UrlPortVH.'">'.$vh_ip.'</a> <i>('.$value.')</i></p>';


                                    }
                                    else
                                    {

                                        $Error = true;
                                        $errortype = 'IP not valid';
                                        $errormex  = '<p>'.$vh_ip.' for '.$value.' - <i style="color:red;">IP not valid</i></p>';

                                    }

                                }

                                elseif($WampVirtualHost['DocRootNotwww'][$value] === false)
                                {

                                    $Error = true;
                                    $errortype = 'DocumentRoot error';
                                    $errormex = '<p>'.sprintf($langues['txtDocRoot'],"<span>".$value."</span>","<span>".$wwwDir."</span>").'</p>';

                                }

                                elseif($WampVirtualHost['ServerNameDev'][$value] === true)
                                {

                                    $Error = true;
                                    $errortype = 'TLD error';
                                    $errormex = '<p>'.sprintf($langues['txtTLDdev'],'<span>'.$value.'</span>','<span>.dev</span>').'</p>';

                                }

                                else
                                {
                                    $value_url = ((strpos($value, ':') !== false) ? strstr($value,':',true) : $value);
                                    $valueaff = ($WampVirtualHost['ServerNameIDNA'][$value] === true) ? "<p style='margin:-8px 0 -8px 25px;'><small>IDNA-> ".$WampVirtualHost['ServerNameUTF8'][$value]."</small></p>" : '';
                                    $errortype = 'IDNA error';
                                    $errormex = '<p><a target="_blank" href="http://'.$value_url.$UrlPortVH.'">'.$valueaff.'</a></p>';

                                }

                            }
                            else
                            {

                                $Error = true;
                                $errortype = 'no clean error';
                                $errormex = '<p>'.sprintf($langues['txtVhostNotClean'],$WampVirtualHost['vhosts_file']).'</a></p>';

                            }

                            //save VirtualHost
                            if($Error===false)
                            {
                                //test alert:
                                // $data = '
                                // <div class="alert" data-sercher="document root error">
                                //     <div><p><span>⚠</span> alert : Document root error</p></div>
                                //     <div><p>'.sprintf($langues['txtNbNotEqual'],"DocumentRoot","ServerName",$WampVirtualHost['vhosts_file']).'</p></div>
                                // </div>';

                                //test error:
                                // $errormex  = '<p>'.sprintf('The ServerName %s has syntax error in file %s','<span style="color:black;">'.$value.'</span>',$WampVirtualHost['vhosts_file']).'</p>';
                                // $data = '
                                // <div class="error" data-sercher="'.$value.' : '.$errortype.'">
                                //     <div><p><span>⛛</span> '.$value.' : <span>'.$errortype.'</span></p></div>
                                //     <div>'.$errormex.'</div>
                                // </div>';

                                $data = '
                                <a data-sercher="'.$value.'" class="virtual-host-link" target="_blank" href="http://'.$value.'">
                                    <span>➜</span> '.$value.'
                                </a>';

                            }
                            else
                            {

                                $data = '
                                <div class="error" data-sercher="'.$value.' : '.$errortype.'">
                                    <div><p><span>⛛</span> '.$value.' : '.$errortype.'</p></div>
                                    <div>'.$errormex.'</div>
                                </div>';

                            }

                            array_push($HostList,$data);

                        }

                    }



                    ///// all server checks:

                    $nb_Server = $WampVirtualHost['nb_Server'];
                    $nb_Virtual = $WampVirtualHost['nb_Virtual'];
                    $nb_Document = $WampVirtualHost['nb_Document'];
                    $nb_Directory = $WampVirtualHost['nb_Directory'];
                    $nb_End_Directory = $WampVirtualHost['nb_End_Directory'];
                    $port_number = true;


                    //Check number of <Directory equals </Directory
                    if($nb_End_Directory != $nb_Directory)
                    {

                        $data = `
                            <div class="alert" data-sercher="dir error">
                                <div><p><span>⚠</span> alert : dir error</p></div>
                                <div><p>`.sprintf($langues['txtNbNotEqual'],"&lt;Directory ....&gt;","&lt;/Directory&gt;",$WampVirtualHost['vhosts_file']).`</p></div>
                            </div>
                        `; array_push($HostList,$data);


                    }

                    //Check number of DocumentRoot equals to number of ServerName
                    if($nb_Document != $nb_Server)
                    {

                        $data ='
                        <div class="alert" data-sercher="document root error">
                            <div><p><span>⚠</span> alert : Document root error</p></div>
                            <div><p>'.sprintf($langues['txtNbNotEqual'],"DocumentRoot","ServerName",$WampVirtualHost['vhosts_file']).'</p></div>
                        </div>';
                        array_push($HostList,$data);

                    }

                    //Check validity of DocumentRoot
                    if($WampVirtualHost['document'] === false)
                    {

                        foreach($WampVirtualHost['documentPath'] as $value) {
                            if($WampVirtualHost['documentPathValid'][$value] === false) {

                                $data ='
                                <div class="error" data-sercher="'.$value.' error">
                                    <div><p><span>⚠</span> alert : '.$value.' error</p></div>
                                    <div><p>'. sprintf($langues['txtNoPath'],"<span class='pops'>".$value."</span>", "DocumentRoot", $WampVirtualHost['vhosts_file']).'</p></div>
                                </div>';
                                array_push($HostList,$data);

                                break;

                            }
                        }

                    }

                    //Check validity of Directory Path
                    if($WampVirtualHost['directory'] === false) {
                        foreach($WampVirtualHost['directoryPath'] as $value) {
                            if($WampVirtualHost['directoryPathValid'][$value] === false) {

                                $data = '
                                <div class="alert" data-sercher="'.$value.' error">
                                    <div><p><span>⚠</span> alert : '.$value.' error</p></div>
                                    <div><p>'.sprintf($langues['txtNoPath'],"<span style='color:black;'>".$value."</span>", "&lt;Directory ...", $WampVirtualHost['vhosts_file']).'</p></div>
                                </div>';
                                array_push($HostList,$data);

                                break;

                            }
                        }
                    }

                    //Check number of <VirtualHost equals or > to number of ServerName
                    if($nb_Server != $nb_Virtual && $wampConf['NotCheckDuplicate'] == 'off') {

                        $port_number = false;

                        $data = '
                        <div class="alert" data-sercher="error no check dubplicated">
                            <div><p><span>⚠</span> alert : error no check dubplicated</p></div>
                            <div><p>'.sprintf($langues['txtNbNotEqual'],"&lt;VirtualHost","ServerName",$WampVirtualHost['vhosts_file']).'</p></div>
                        </div>';
                        array_push($HostList,$data);

                    }

                    //Check number of port definition of <VirtualHost *:xx> equals to number of ServerName
                    if($WampVirtualHost['nb_Virtual_Port'] != $nb_Virtual && $wampConf['NotCheckDuplicate'] == 'off') {

                        $port_number = false;

                        $data = '
                        <div class="alert" data-sercher="virtual port error">
                            <div><p><span>⚠</span> alert : virtual port error</p></div>
                            <div><p>'.sprintf($langues['txtNbNotEqual'],"port definition of &lt;VirtualHost *:xx&gt;","ServerName",$WampVirtualHost['vhosts_file']).'</p></div>
                        </div>';
                        array_push($HostList,$data);

                    }

                    //Check validity of port number
                    if($port_number && $WampVirtualHost['port_number'] === false) {

                        $port_number = false;
                        $data = '
                        <div class="alert" data-sercher="port number error">
                            <div><p><span>⚠</span> alert : port number error</p></div>
                            <div><p>'.sprintf($langues['txtPortNumber'],"&lt;VirtualHost *:port&gt;",$WampVirtualHost['vhosts_file']).'</p></div>
                        </div>';
                        array_push($HostList,$data);

                    }

                    //Check if duplicate ServerName
                    if($WampVirtualHost['nb_duplicate'] > 0) {

                        $DuplicateNames = ''; foreach($WampVirtualHost['duplicate'] as $NameValue) $DuplicateNames .= " ".$NameValue;

                        $data = '
                        <div class="alert" data-sercher="duplicated names error">
                            <div><p><span>⚠</span> alert: duplicated names error</p></div>
                            <div><p>'."Duplicate ServerName <i>".$DuplicateNames."</i> into ".$WampVirtualHost['vhosts_file'].'</p></div>
                        </div>';
                        array_push($HostList,$data);

                    }

                    //Check if duplicate Server IP
                    if($WampVirtualHost['nb_duplicateIp'] > 0) {

                        $DuplicateNames = ''; foreach($WampVirtualHost['duplicateIp'] as $NameValue) $DuplicateNames .= " ".$NameValue;

                        $data = '
                        <div class="alert" data-sercher="duplicated names error">
                            <div><p><span>⚠</span> alert: duplicated names error</p></div>
                            <div><p>'."Duplicate IP <i>".$DuplicateNames."</i> into ".$WampVirtualHost['vhosts_file'].'</p></div>
                        </div>';
                        array_push($HostList,$data);

                    }


            }


            if(empty($HostList))
            {
                $data = `<div><p>No VirtualHost</p></div>`;
                array_push($HostList,$data);
            }

        }

        return $HostList;
    }

?>
