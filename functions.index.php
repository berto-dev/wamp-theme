<?php

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