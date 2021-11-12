<? session_start();

require '../scripts/config.inc.php';
require '../scripts/wampserver.lib.php';

$langue = $wampConf['language'];
include './wamplangues/add_vhost_'.$langue.'.php';

$c_PortToUse = $c_UsedPort;


// Auto correction ?
$automatique = (isset($_POST['correct']) ? true : false);

// Stabdard asset
$message_ok     = '';
$message        = array();
$errors         = false;
$errors_auto    = false;
$vhost_created  = false;
$sub_menu_on    = true;


////// Get the VirtualHostMenu value

$VirtualHostMenu = !empty($wampConf['VirtualHostSubMenu']) ? $wampConf['VirtualHostSubMenu'] : "off";
if($VirtualHostMenu !== "on") { $errors = true; $sub_menu_on = false;  $message[] = '<p class="warning">'.$langues['VirtualSubMenuOn'].'</p>'; }


////// Make a VirtualHost on submit
$_SESSION['makinghost']= (isset($_POST['makinghost']))?"true":"false";
if( !$errors && isset($_POST['makenow']) && $_SESSION['makinghost']=="true" ) {

    // if ( !isset($_POST['selectedhostlist']) )
    // {
    //     $makehostresult = ['<p>UNDEFINED OR LOST HOST TO MAKE LIST</p>'];
    // }

    if($_SESSION['suppressor']=="false")
    {

        $makehostresult = makehost(
            check_virtualhost(),
            $wampConf,
            $wwwDir,
            $c_hostsFile_writable,
            $errors,
            $c_PortToUse,
            $c_apacheVhostConfFile,
            $c_hostsFile,
            $langues
        );
    }

}


////// Suppress VirtualHost on submit

$_SESSION['suppressor'] = (isset($_POST['suppressor']))?$_POST['suppressor']:"false";
if( !$errors && isset($_POST['deletenow']) && $_SESSION['suppressor']=="true" ) {


    $suppressionresult = hostsuppressor(
        check_virtualhost(),
        file_get_contents($c_apacheVhostConfFile),
        file_get_contents($c_hostsFile),
        $c_apacheVhostConfFile,
        $c_hostsFile,
        $wampConf
    );

}



// asset for different type of test and condition via httpd-vhosts.conf

$virtualHost     = check_virtualhost();
$virtualHostlist = getVirtualHostDefindedNames($virtualHost);
$listenPort      = listen_ports();
$authorizedPorts = array();


///// on submitted on load page check errors/maker/what  ?

// if($virtualHost['include_vhosts'] === false && !$errors)
// {
// 	if($automatique) {
// 		$httpConfFileContents = file_get_contents($c_apacheConfFile);
// 		$httpConfFileContents = preg_replace("~^[ \t]*#[ \t]*(Include[ \t]*conf/extra/httpd-vhosts.conf.*)$~m","$1",$httpConfFileContents,1);
// 		$fp = fopen($c_apacheConfFile,'wb');
// 		fwrite($fp,$httpConfFileContents);
// 		fclose($fp);
// 		$virtualHost = check_virtualhost();
// 	}
// 	else {
// 		$message[] = '<p class="warning_auto">'.sprintf($langues['UncommentInclude'],$c_apacheConfFile).'</p>';
// 		$errors = true;
// 		$errors_auto = true;
// 	}
// }
// if($virtualHost['vhosts_exist'] === false && !$errors)
// {
// 	if($automatique) {
// 		$fp = fopen($c_apacheVhostConfFile,'wb');
// 		fclose($fp);
// 		$virtualHost = check_virtualhost();
// 	}
// 	else {
// 		$message[] = '<p class="warning_auto">'.sprintf($langues['FileNotExists'],$c_apacheVhostConfFile).'</p>';
// 		$errors = true;
// 		$errors_auto = true;
// 	}
// }
// if(in_array("dummy", $virtualHost['ServerNameValid'], true) !== false && !$errors)
// {
// 	if($automatique) {
// 		$fp = fopen($c_apacheVhostConfFile,'wb');
// 		fclose($fp);
// 		$virtualHost = check_virtualhost();
// 	}
// 	else {
// 		$message[] = '<p class="warning_auto">'.sprintf($langues['NotCleaned'],$c_apacheVhostConfFile).'</p>';
// 		$errors = true;
// 		$errors_auto = true;
// 	}
// }
//
// if(empty($virtualHost['FirstServerName']) && !$errors)
// {
// 	if($automatique)
//     {
// 		if(substr($wampConf['apacheVersion'],0,3) == '2.2') {
// 		$virtual_localhost = <<< EOFLOCAL
//
// NameVirtualHost *:{$c_PortToUse}
//
// <VirtualHost *:{$c_PortToUse}>
// 	ServerName localhost
// 	DocumentRoot "{$wwwDir}"
// 	<Directory  "{$wwwDir}/">
// 		Options +Indexes +Includes +FollowSymLinks +MultiViews
// 		AllowOverride All
//     Order Deny,Allow
//     Deny from all
//     Allow from localhost ::1 127.0.0.1
// 	</Directory>
// </VirtualHost>
//
// EOFLOCAL;
// 		}
// 		else {
// 		$virtual_localhost = <<< EOFLOCAL
//
// #
// <VirtualHost *:{$c_PortToUse}>
// 	ServerName localhost
// 	DocumentRoot "{$wwwDir}"
// 	<Directory  "{$wwwDir}/">
// 		Options +Indexes +Includes +FollowSymLinks +MultiViews
// 		AllowOverride All
// 		Require local
// 	</Directory>
// </VirtualHost>
//
// EOFLOCAL;
// 		}
//
// 		$fp = fopen($c_apacheVhostConfFile,'wb');
// 		fwrite($fp,$virtual_localhost);
// 		fclose($fp);
// 		$virtualHost = check_virtualhost();
//
// 	}
// 	else {
// 		$message[] = '<p class="warning_auto">'.sprintf($langues['NoVirtualHost'],$c_apacheVhostConfFile).'</p>';
// 		$errors = true;
// 		$errors_auto = true;
// 	}
// }


?>

<!DOCTYPE html>
<html>

    <head>

    	<title>SERVER :: W.A.M.P</title>
    	<meta charset="UTF-8">
    	<link rel="stylesheet" href="style.css" />
    	<link rel="shortcut icon" href="favicon.ico" type="image/ico" />

    </head>

    <body>

        <div class="page">

            <?if($_SESSION['maker']=="true" && $_SESSION['suppressor']=="true"){ ?>
                <div class="limits"><p>ALERT : Creating and deleting at the same time is not allowed</p></div>
                <div style="margin:10px; width:100%; display:block;"></div>
            <?}elseif( isset($_POST['makenow']) ){?>
                <div class="limits"><? foreach($makehostresult as $message) echo $message ?></div>
                <div style="margin:10px; width:100%; display:block;"></div>
            <?}?>


            <?if( isset($_POST['deletenow']) ){?> <!-- && !empty($suppressionresult)-->
                <div class="limits"><? foreach($suppressionresult as $message) echo $message ?></div>
                <div style="margin:10px; width:100%; display:block;"></div>
            <?}?>


            <div class="limits">

                <div class="wamp-server-logo <?php if($wampConf['apachePortUsed']){echo'wamp-active';}?>">
                    <img alt="-wamp-" src="data:image/png;base64,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" />
                </div>

                <div class="pagetitle">
                    <h1> version <?php echo $c_wampVersion ?> - <?php echo $c_wampMode?></h1>
                    <p>[  <a target="_blank" href="./ori.add_vhost.php">original manager</a> | <a onclick="alert('filepath: C:/wamp64/bin/apache/apache2.4.46/conf/extra/httpd-vhosts.conf')">open virtual hosts config</a>  ]</p>
                </div>

                <div class="mainlinkbox">
                    <a href="./index">Back to homepage</a>
                </div>

                <hr>


                <? if($errors) foreach($message as $value) echo '<p style="margin:10px auto; color:red;"> ERROR ::: '.$value.'</p>'; ?>

                <? if( (!empty($virtualHostlist) || $sub_menu_on!==true) && $wampConf['NotCheckDuplicate'] == "off" && $wampConf['NotCheckVirtualHost'] == 'off') { ?>

                    <form id="deletevhost" method="post" class="deleterbox">
                        <details>

                            <summary><?=$langues['VirtualHostExists']?></summary>

                            <div>

                                <div class="list">

                                    <? foreach( $virtualHostlist as $htmlhost) echo $htmlhost ?>

                                </div>

                				<input type="hidden" name="suppressor" value="true" />
                				<input class="deletealias" type="submit" name="deletenow" value="<?=$langues['suppVhost']?>" />

                            </div>

                        </details>
                    </form>

                <? } else { ?>

                        <div class="nohostbox">
                            <p>NO VIRTUAL HOST DETECTED</p>
                        </div>

                <? } ?>

                <form class="aliasbox" method="post">

                <? if($errors_auto) { ?>

                    <div>
                        <label><?=$langues['GreenErrors']?></label>
                        <input type="submit" name="correct" value="<?=$langues['Correct']?>" />
                    </div>

    			<? } else { ?>

                    <?// $_SESSION['makehost'] = mt_rand(100000001,mt_getrandmax()); ?>

                    <div>
                        <label>
                            <b title="<?=$langues['Required']?> (no space, no underscore)">*</b>&nbsp;
                            Name of the virtual host&nbsp;
                            <i title="It is a good rule to give your local project an acronym to distinguish it from a possible online project. for example my-project.local instead of my-project.com">ex: mylocalproject</i>
                        </label>
                        <input type="text" name="vh_name" required="required">
                    </div>

                    <div>
                        <label>
                            <b title="<?=$langues['Required']?> (no file, only folder)">*</b>&nbsp;
                            Absolute path of project folder&nbsp;
                            <i>ex: C:/mywebsite/</i>
                        </label>
                        <input class="required" type="text" name="vh_folder" required="required"/>
                    </div>

                    <input type="hidden" name="vh_ip"/>
                    <!-- <input type="hidden" name="selectedhostlist" value="<?=$_SESSION['makehost']?>" /> -->
                    <input type="hidden" name="makinghost" value="true"/>

                    <input class="savealias" type="submit" name="makenow" value="<?=$langues['Start']?>">

                </form>

    			<?}?>

            </div>

</html>
<?

    ////// VirtualHost defined names

    function getVirtualHostDefindedNames($virtualHost)
    {

        $hostlist = array();

        if($virtualHost['nb_Server'] > 0)
        {

            $i = 0;
            foreach( $virtualHost['ServerName'] as $value)
            {

                if($value!=$_SERVER["REMOTE_ADDR"] && $value!=$_SERVER['HTTP_HOST']) //if not 127.0.0.1 localhost
                {

                    $html_content = '<span class="hosted"> <input type="checkbox" name="virtual_del[]" value="'.$value.'"/> <span>'.$value.' </span></span>';

                        //if wrong port
                        $PortError = ($virtualHost['ServerNamePort'][$value] != '80') ? $virtualHost['ServerNamePort'][$value] : false;
                        //UTF8 error
                        $UTF8name = ( $virtualHost['ServerNameIDNA'][$value] === true ) ? $virtualHost['ServerNameUTF8'][$value] : false;

                        if($virtualHost['ServerNameValid'][$value] === false)
                        {
                            $html_content = '<span style="color:orangered"> <input type="checkbox" name="virtual_del[]" value="'.$value.'"/><span>error on : '.$value.' - ServerName syntax error</span></span>';
                        }
                        else if($PortError)
                        {
                            $html_content = '<span style="color:orangered"> <input type="checkbox" name="virtual_del[]" value="'.$value.'"/><span>error on : '.$value.' - Wrong Port <code>'.$PortError.'</code> - on dir : {'.$virtualHost['documentPath'][$i].'}</span></span>';
                        }
                        else if($UTF8name)
                        {
                            $html_content = '<span style="color:rose"> <input type="checkbox" name="virtual_del[]" value="'.$value.'"/><span>error on : '.$value.' - UTF8 name not valid : <code>'.$UTF8name.'</code></span></span>';
                        }


                    array_push($hostlist,$html_content);

                }

                $i++;

        	}

        }

        return $hostlist;

    }


    ////// Delete one or more defined VirtualHost

    function hostsuppressor($virtualHost,$virtualHostcontents,$hostscontents,$xapacheVirtualHostConfigFile,$hostfile,$wampconf)
    {

            $_SESSION['suppressor']="false";

            $message = array();

            $nb = count($_POST['virtual_del']);
            $replaceVhosts = $replaceHosts = false;

            //b1-----
            for($i=0;$i<$nb;$i++)
            {

                $value = strip_tags(trim($_POST['virtual_del'][$i]));

                if(!in_array($value, $virtualHost) || $value == 'localhost')
                {
                    $value = '';
                    break;
                }

                $p_value = preg_quote($value);

                //Check a port number
                $ApacheVar = '';
                $value_url = $port = '';
                if(strpos($value, ':') !== false)
                {

                    $value_url = strstr($value,':',true);
                    $port = substr(strstr($value,':'),1);

                    if(in_array($port,$c_ApacheDefine) || ($wampconf['apacheUseOtherPort'] == 'on' && $port == $c_UsedPort))
                    {
                        $ApacheVar = array_search($port,$c_ApacheDefine);
                        $p_value = preg_quote($value_url);
                    }
                    else
                    {
                        error_log("Value ".$port." does not exist in array \$c_ApacheDefine. It is not a value of an Apache Variable");
                    }

                }


    			if(in_array($value, $virtualHost['ServerName']))
                {

                    //Extract <VirtualHost... </VirtualHost>
                    $mask = "{
                        <VirtualHost                         # beginning of VirtualHost
                        [^<]*(?:<(?!/VirtualHost)[^<]*)*     # avoid premature end
                        \n\s*ServerName\s+${p_value}\s*\n    # Test server name
                        .*?                                  # we stop as soon as possible
                        </VirtualHost>\s*\n                  # end of VirtualHost
                    }isx";

                    $countName = 0;
                    $countName = preg_match_all($mask,$virtualHostcontents,$matches);
                    $found = false;
                    if($countName > 0)
                    {

                        for($j=0;$j<$countName;$j++)
                        {

                            if(empty($ApacheVar))
                            {
                                if(strpos($matches[0][$j],'MYPORT') === false)
                                {
        							$found = $j;  break;
                                }
        					}

                            else
                            {
                                if(strpos($matches[0][$j],$ApacheVar) !== false)
                                {
                                    $found = $j; break;
                                }
                            }

                        }

                    }

                    if($found !== false)
                    {
                        $virtualHostcontents = str_replace($matches[0][$found],'',$virtualHostcontents, $count);
                        if($count > 0) $replaceVhosts = true;
                    }

                    if($countName == 1)
                    {
                        //Suppress ServerName into hosts file
                        $count = $count1 = 0;
                        $hostscontents = preg_replace("~^[0-9\.:]+\s+".$p_value."\r?$~mi",'',$hostscontents,-1, $count);
                        $hostscontents = str_ireplace($value,'',$hostscontents,$count1);
                        if($count > 0 || $count1 > 0 ) $replaceHosts = true;
                    }

                    array_push($message,'<div class="aliaspassed"><p>'.$value.' > actions done</p></div>');

                }

                else
                {
                    array_push($message,'<div class="aliaswarning"><p>The host: '.$value.' doesn\'t exist</p></div>');
                    $errors = true;
                }

            }

            //-----

            if($replaceVhosts)
            {
                //Cleaning of httpd-vhosts.conf file
                $virtualHostcontents = clean_file_contents($virtualHostcontents);
                $fp = fopen($xapacheVirtualHostConfigFile, 'wb');
                fwrite($fp, $virtualHostcontents);
                fclose($fp);
            }

    		if($replaceHosts)
            {

                if($wampconf['BackupHosts'] == 'on')
                {
                    @copy($hostfile,$hostfile."_wampsave.".$next_hosts_save);
                    $next_hosts_save++;
                }

    			//Cleaning of hosts file
                $hostscontents = clean_file_contents($hostscontents,array(2,0),true);
                $fp = fopen($hostfile, 'r+b');
                if(flock($fp, LOCK_EX)) // acquire an exclusive lock
                {
                    ftruncate($fp, 0);      // truncate file
                    fwrite($fp, $hostscontents);
                    fflush($fp);            // flush output before releasing the lock
                    flock($fp, LOCK_UN);    // release the lock
                }
                else
                {
                    array_push($message,'<p class="warning">Alert: Unable to write to '.$hostfile.' file</p>');
                    $errors = true;
                }
                fclose($fp);
            }

            $virtualHost = check_virtualhost();

            return $message;


    }

    ////// Make a defined VirtualHost

    function makehost($virtualHost,$wampconf,$wwwdir,$hostsfilewritable,$errors,$c_PortToUse,$c_apacheVhostConfFile,$c_hostsFile,$langues)
    {

        $_SESSION['makinghost']="false";


        $message = array();

        // strip_tags(trim($_POST['selectedhostlist']));

        // Escape any backslashes used in the path to the file
        // $c_apacheVhostConfFile = str_replace('\\', '\\\\', $c_apacheVhostConfFile);
        $vh_name = strip_tags(trim($_POST['vh_name']));
        $vh_ip = strip_tags(trim($_POST['vh_ip']));
        $vh_port = '';

        if(isset($_POST['vh_port_on']) && strip_tags(trim($_POST['vh_port_on'])) == 'on') $vh_port = strip_tags(trim($_POST['vh_port']));

        $vh_folder = str_replace(array('\\','//'), '/',strip_tags(trim($_POST['vh_folder'])));

        if(substr($vh_folder,-1) == "/") $vh_folder = substr($vh_folder,0,-1);

        $vh_folder = strtolower($vh_folder);

        //3.0.6 - Check / at first character
        if(substr($vh_folder,0,1) == "/" && substr($vh_folder,0,2) != "//") $vh_folder = "/".$vh_folder;

        if($virtualHost['FirstServerName'] !== "localhost" && !$errors)
        {
            array_push($message,'<div class="aliaswarning"><p>'.sprintf($langues['NoFirst'],$c_apacheVhostConfFile).'</p><div>');
            $errors = true;
        }

        /* Validité du nom de domaine */
        clearstatcache(); // added for update 3.1.4

        //Check if IDN is needed
        $vh_nameIDN = idn_to_ascii($vh_name,IDNA_DEFAULT,INTL_IDNA_VARIANT_UTS46);
        if($vh_nameIDN !== $vh_name) $vh_name = $vh_nameIDN;

        // IDNA (Punycode) 3.2.3 - improve regex
        $regexIDNA = '#^([\w-]+://?|www[\.])?xn--[a-z0-9]+[a-z0-9\-\.]*[a-z0-9]+(\.[a-z]{2,7})?$#';

        // Not IDNA  /^[A-Za-z]+([-.](?![-.])|[A-Za-z0-9]){1,60}[A-Za-z0-9]$/
        if(preg_match($regexIDNA,$vh_name,$matchesIDNA) == 0 && preg_match('/^
            (?=.*[A-Za-z])  # at least one letter somewhere
            [A-Za-z0-9]+    # letter or number in first place
            ([-.](?![-.])   # a . or - not followed by . or -
            |               # or
            [A-Za-z0-9]     # a letter or a number
            ){0,60}         # this, repeated from 0 to 60 times - at least two characters
            [A-Za-z0-9]     # letter or number at the end
            $/x',$vh_name) == 0)
        {
            array_push($message,'<p class="warning">'.sprintf($langues['ServerNameInvalid'],$vh_name).'</p>');
        	$errors = true;
        }

        elseif($wampconf['NotVerifyTLD'] == 'off' && substr($vh_name,-4) !== false && (strtolower(substr($vh_name,-4) == '.dev')))
        {
            array_push($message,'<div class="aliaswarning"><p>'.sprintf($langues['txtTLDdev'],$vh_name,".dev").'</p></div>');
        	$errors = true;
        }

        elseif((!file_exists($vh_folder) || !is_dir($vh_folder)))
        {
            array_push($message,'<div class="aliaswarning"><p>'.sprintf($langues['DirNotExists'],$vh_folder).'</p></div>');
        	$errors = true;
        }

        elseif(strtolower($vh_folder) == strtolower($wwwdir))
        {
            array_push($message,'<div class="aliaswarning"><p>'.sprintf($langues['NotwwwDir'],$vh_folder).'</p></div>');
        	$errors = true;
        }

        elseif($hostsfilewritable !== true)
        {
            array_push($message,'<div class="aliaswarning"><p>'.sprintf($langues['FileNotWritable'],$c_hostsFile).'</p></div>');
        	$errors = true;
        }

        elseif($wampconf['NotCheckDuplicate'] == 'off' && array_key_exists(strtolower($vh_name), array_change_key_case($virtualHost['ServerName'], CASE_LOWER)))
        {
        	if(empty($vh_port) || !in_array($vh_port, $authorizedPorts))
            {
                array_push($message,'<div class="aliaswarning"><p>'.sprintf($langues['VirtualAlreadyExist'],$vh_name).'</p></div>');
        		$errors = true;
        	}
        }

        $c_UsedIp = '*';
        $c_HostIp = '127.0.0.1';
        if(!$errors && !empty($vh_ip))
        {

            if($vh_ip == '127.0.0.0' || $vh_ip == '127.0.0.1' )
            {
                array_push($message,'<div class="aliaswarning"><p>'.sprintf($langues['VirtualIpAlreadyUsed'],$vh_ip).'</p></div>');
                $errors = true;
            }

            elseif(check_IP($vh_ip) === false) // Validité IP locale
            {
                array_push($message,'<div class="aliaswarning"><p>'.sprintf($langues['LocalIpInvalid'],$vh_ip).'</p></div>');
            	$errors = true;
            }

            elseif(in_array($vh_ip, $virtualHost['virtual_ip']) && $wampconf['NotCheckDuplicate'] == 'off')
            {
                array_push($message,'<div class="aliaswarning"><p>'.sprintf($langues['VirtualIpAlreadyUsed'],$vh_ip).'</p></div>');
            	$errors = true;
            }

            else
            {
                $c_UsedIp = $c_HostIp = $vh_ip;
            }

        }

        if(!$errors && !empty($vh_port))
        {

            if($vh_port == '80' || $vh_port == $c_UsedPort)
            {
                array_push($message,'<div class="aliaswarning"><p>'.sprintf($langues['VirtualPortExist'],$vh_port).'</p></div>');
                $errors = true;
            }

            elseif(!in_array($vh_port, $authorizedPorts))
            {
                array_push($message,'<div class="aliaswarning"><p>'.sprintf($langues['VirtualPortNotExist'],$vh_port).'</p></div>');
                $errors = true;
            }

            else
            {
                $key = array_search($vh_port, $c_ApacheDefine);
                $c_PortToUse = '${'.$key.'}';
            }

        }


        if($errors === false)
        {

            // Préparation du contenu des fichiers

            if(substr($wampconf['apacheVersion'],0,3) == '2.2')
            {

                $httpd_vhosts_add =
                <<< EOFNEWVHOST
                #
                #
                <VirtualHost {$c_UsedIp}:{$c_PortToUse}>
                	ServerName {$vh_name}
                	DocumentRoot "{$vh_folder}"
                	<Directory  "{$vh_folder}/">
                		Options +Indexes +Includes +FollowSymLinks +MultiViews
                		AllowOverride All
                        Order Deny,Allow
                        Deny from all
                        Allow from localhost ::1 127.0.0.1
                	</Directory>
                </VirtualHost>
                #
                #
                EOFNEWVHOST;

    		}

            else
            {
                $httpd_vhosts_add =
                <<< EOFNEWVHOST
                #
                #
                <VirtualHost {$c_UsedIp}:{$c_PortToUse}>
                    ServerName {$vh_name}
                    DocumentRoot "{$vh_folder}"
                    <Directory  "{$vh_folder}/">
                        Options +Indexes +Includes +FollowSymLinks +MultiViews
                        AllowOverride All
                        Require local
                    </Directory>
                </VirtualHost>
                #
                #
                EOFNEWVHOST;
            }

            $hosts_add =
            <<< EOFHOSTS
            {$c_HostIp}{$vh_name}::1 {$vh_name}
            EOFHOSTS;

            // Ouverture des fichiers pour ajout des lignes
            if($wampconf['BackupHosts'] == 'on')
            {
                @copy($c_hostsFile,$c_hostsFile."_wampsave.".$next_hosts_save);
                $next_hosts_save++;
            }

            $fp1 = fopen($c_apacheVhostConfFile, 'a+b');
            $fp2 = fopen($c_hostsFile, 'a+b');

            if (fwrite($fp1, $httpd_vhosts_add) && fwrite($fp2, $hosts_add))
            {

                $dns_refresh_message = "";
                $vhost_created = true;


                array_push($message,
                    '
                        <div class="aliascreated">
                            <p>'.$vh_name.' : '.$langues['VirtualCreated'].'</p>
                            <p><i>Restart DNS from Wampmanager icon</i></p>
                        </div>
                    '
                );


            }

            else
            {
                array_push($message,'<div class="aliaswarning"><p>'.$langues['NoModify'].'</p></div>');
            }

            fclose($fp1);
            fclose($fp2);

        }



        return $message;

    }

?>
