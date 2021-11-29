<?php

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