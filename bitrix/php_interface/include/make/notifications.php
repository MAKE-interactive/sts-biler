<?php
define("BIRTHDAY_TEMPLATE_ID", 462);
define("ANNIVERSARY_TEMPLATE_ID", 454);

//function trigger by bitrix agents
function activateEmployeeNotification()
{
    GLOBAL $USER;
    GLOBAL $DB;

    if (!is_object($USER))
        $USER = new CUser;

    CModule::IncludeModule('main');
    CModule::IncludeModule("socialnetwork");
    CModule::IncludeModule("blog");
    CModule::IncludeModule("xdimport");


    // EmployeeNotification::activateEmployeeNotification();
    $notification = new EmployeeNotification();
    $notification->activateEmployeeNotification();
    
    // exit();
    return "activateEmployeeNotification();";
}

function notifyAllEmployees($message, $arCodes = array("UA"), $tags){
    global $USER;
    
    CModule::IncludeModule("im");
    CModule::IncludeModule("socialnetwork");
    
    /*$exclude_user = array();
    foreach($arCodes as $key => $arCode){
        if(substr($arCode, 0, 1) === 'U' && $arCode != "UA"){
            unset($arCodes[$key]);
            $exclude_user[] = substr($arCode, 1);
        }
    }*/
    
    $arUsers = CSocNetLogDestination::GetDestinationUsers($arCodes);
    $filtered_user = array_diff($arUsers, $exclude_user);
    
    foreach($filtered_user as $userId){
        $arFieldsIM = array("NOTIFY_MESSAGE" => $message,
            "NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
            "NOTIFY_MODULE" => "blog",
            "NOTIFY_EVENT" => "post",
            "NOTIFY_TAG" => $tags,
            "FROM_USER_ID" => 0,
            "TO_USER_ID" => $userId);
            
        #CIMNotify::Add($arFieldsIM);
    }
}

class EmployeeNotification
{
    public function __construct()
    {
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
        CModule::IncludeModule('main');
        CModule::IncludeModule('iblock');
        CModule::IncludeModule("socialnetwork");
        CModule::IncludeModule("blog");
        CModule::IncludeModule("xdimport");

        GLOBAL $USER;
        GLOBAL $DB;

        if (!is_object($USER)) $USER = new CUser;

        ini_set("memory_limit", -1);
        @set_time_limit(0);
    }

    public function activateEmployeeNotification()
    {
        GLOBAL $DB;

        // allowed stream(s) for posting (department IDs)
        $divisionDepartments = [
            51, // ERA
            55, // NHE
            // 414, // -Det Digitale Bilhus- // DDB not included in streams posted
            54, // Bil og Co
            50, // STS
            52, // SDK
            53, // MFA
            56, // Autohallen
        ];

        //$strSql = "SELECT U.ID FROM b_user U JOIN b_uts_user UTS ON U.ID = UTS.VALUE_ID WHERE DATE_FORMAT(U.PERSONAL_BIRTHDAY,'%m-%d') = DATE_FORMAT(NOW(),'%m-%d') OR  DATE_FORMAT(UTS.UF_WORK_START,'%m-%d') = DATE_FORMAT(NOW(),'%m-%d')";

        $strSql = 'SELECT U.ID FROM b_user U '
        . ' JOIN b_uts_user UTS ON U.ID = UTS.VALUE_ID '
        . ' WHERE (DATE_FORMAT(U.PERSONAL_BIRTHDAY,"%m-%d") = DATE_FORMAT(NOW(),"%m-%d") '
        . ' OR  DATE_FORMAT(UTS.UF_WORK_START,"%m-%d") = DATE_FORMAT(NOW(),"%m-%d")) AND U.ACTIVE="Y"';

        $res = $DB->Query($strSql, false, $err_mess. __LINE__ );


        $arElements = array();
        while ($arRes = $res->Fetch())
        {
            $userID = $arRes["ID"];

            /*
            $userGroups = CUser::GetUserGroup($userID);
            if(!in_array(12, $userGroups)){
                continue;
            }
            */

            $user = CUser::GetByID($userID)->Fetch();
            $fullname = trim($user["NAME"] . " " . $user["LAST_NAME"]);
            $jobtitle = $user["WORK_POSITION"];
            $department = $user["WORK_DEPARTMENT"];

            // socnet permission
            $socnetPerms = array();
            foreach($user["UF_DEPARTMENT"] as $dept){
                
                // skip posting to main DDB stream
                if($dept == "1"){
                    continue;
                }

                // get department's top section
                $topDepartment = CIntranetUtils::GetIBlockTopSection($dept);

                if (in_array($topDepartment, $divisionDepartments)) {
                    $socnetPerms[] = "DR".$topDepartment;
                }
            }

            $user_photo_id = $user["PERSONAL_PHOTO"];
            $user_photo = "";
            if(!empty($user_photo_id)){
                $img = CFile::ResizeImageGet($user_photo_id, array('width'=>250, 'height'=>250), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                #$img['src'] = str_replace(" ", "%20", $img['src']);
                $filename = rawurlencode(basename($img['src']));
                $img['src'] = dirname($img['src'])."/".$filename;
                $user_photo = '<img src="'.$img['src'].'" width="'.$img['width'].'" height="'.$img['height'].'" />';
            }

            $user_flag = $user["WORK_COUNTRY"];
            $user_flag = '<img src="/assets/img/user-flag.gif" border="0" style="margin-left: 20px;">';
            
            $curr_day = date("d");
            $curr_month = date("m");
            $curr_year = date("Y");

            $array_keys = array(
                "#FULLNAME#",
                "#JOBTITLE#",
                "#DEPARTMENT#",
                "#USER_PHOTO#",
                "#USER_FLAG#",
                "#YEARS#"
            );

            #use birthday template
            if(!empty($user["PERSONAL_BIRTHDAY"]))
            {
                $day = date("d", strtotime($user["PERSONAL_BIRTHDAY"]));
                $month = date("m", strtotime($user["PERSONAL_BIRTHDAY"]));
                $year = date("Y", strtotime($user["PERSONAL_BIRTHDAY"]));
                $year_diff = $curr_year - $year;
                
                $array_values = array(
                    $fullname,
                    $jobtitle,
                    $department,
                    $user_photo,
                    $user_flag,
                    $year_diff
                );
                
                if($day == $curr_day && $month == $curr_month && $year_diff>0)
                {
                    $rsEM = CEventMessage::GetByID(BIRTHDAY_TEMPLATE_ID);
                    $arEM = $rsEM->Fetch();

                    $subject = str_replace("#FULLNAME#", $fullname, $arEM["SUBJECT"]);
                    $message = str_replace($array_keys, $array_values, $arEM["MESSAGE"]);

                    self::notifyEmployees($subject, $message, $socnetPerms, $userID);
                }
            }

            #use anniversary template
            if(!empty($user["UF_WORK_START"]))
            {
                $day = date("d", strtotime($user["UF_WORK_START"]));
                $month = date("m", strtotime($user["UF_WORK_START"]));
                $year = date("Y", strtotime($user["UF_WORK_START"]));
                $year_diff = $curr_year - $year;
                $arAnniversaryYears = array(10, 20, 25, 30, 40, 45, 50, 55, 60);
                
                if(!in_array($year_diff, $arAnniversaryYears)){
                    continue;
                }
                
                $array_values = array(
                    $fullname,
                    $jobtitle,
                    $department,
                    $user_photo,
                    $user_flag,
                    $year_diff
                );
            
                if($day == $curr_day && $month == $curr_month && $year_diff>0)
                {
                    $rsEM = CEventMessage::GetByID(ANNIVERSARY_TEMPLATE_ID);
                    $arEM = $rsEM->Fetch();

                    $subject = str_replace("#FULLNAME#", $fullname, $arEM["SUBJECT"]);
                    $message = str_replace($array_keys, $array_values, $arEM["MESSAGE"]);

                    self::notifyEmployees($subject, $message, $socnetPerms, $userID);
                }
            }
        }
    }

    function notifyEmployees($subject, $message, $socnetPerms = null, $userID)
    {
        global $DB;
        
        $CTextParser = new \CTextParser();
        $message = $CTextParser->convertHTMLToBB($message);

        if (!empty($blogId)) {
            $blog = CBlog::GetByID($blogId);
        } else {
            $BLOG_OWNER_ID = "1";
            $blog = CBlog::GetByOwnerID($BLOG_OWNER_ID);
        }

        //$socnetPerms = array("DR50");

        $arBlogFields = array(
            "TITLE" => $subject,
            "DETAIL_TEXT" => (!empty($message)) ? $message : " ",
            "DETAIL_TEXT_TYPE" => "text",
            "BLOG_ID" => $blog["ID"],
            "AUTHOR_ID" => $blog["OWNER_ID"],
            "=DATE_CREATE" => $DB->GetNowFunction(),
            "=DATE_PUBLISH" => $DB->GetNowFunction(),
            "PATH" => "/company/personal/user/" . $blog["OWNER_ID"] . "/blog/#post_id#/",
            "URL" => $blog["URL"],
            "ENABLE_COMMENTS" => $blog["ENABLE_COMMENTS"],
            "HAS_SOCNET_ALL" => "N",
            "PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
            "PERMS_POST" => array(),
            "PERMS_COMMENT" => array(),
            "SOCNET_RIGHTS" => $socnetPerms,
            "MICRO" => "Y",
            "SKIP_POST_HANDLER" => "Y",
        );

        $blogPostID = CBlogPost::Add($arBlogFields);
        
        $arBlogFields["ID"] = $blogPostID;
        $arParamsNotify = Array(
            "bSoNet" => true,
            "UserID" => $blog["OWNER_ID"],
            "user_id" => $blog["OWNER_ID"],
            "PATH_TO_POST" => $arBlogFields["PATH"],
        );

        $groups = CUser::GetUserGroup($userID);
        
        foreach($groups as $group_id) {
            
            $siteId = userSiteByGroupId($group_id);
            
            // prevent posting on main DDB site/stream
            if($siteId == "s1"){
                continue;
            }
            
            if($siteId){
                $arParamsNotify["SITE_ID"] = $siteId;
                $log_id = CBlogPost::Notify($arBlogFields, array(), $arParamsNotify);
            }
        }

        #$log_id = CBlogPost::Notify($arBlogFields, array(), $arParamsNotify);

        /*
        //notify all employees
        $url = str_replace("#post_id#", $blogPostID, $arBlogFields["PATH"]);
        $new_subject .= '<a href="'.$url.'">'.$subject.'</a>';

        $tag = "BLOG|POST|".$blogPostID;

        notifyAllEmployees($new_subject, $socnetPerms, $tag);
        */

        return $blogPostID;
    }
}

AddEventHandler("blog", "OnPostAdd", "OnPostAddHandler");
#AddEventHandler("blog", "OnBeforePostAdd", "OnPostAddHandler");
function OnPostAddHandler($ID, &$arFields)
{
    if(!isset($arFields["SKIP_POST_HANDLER"]))
    {
        $url = str_replace("#post_id#", $ID, $arFields["PATH"]);
        $subject = "Et nyt opslag er blevet tilføjet: ";
        $subject .= '<a href="'.$url.'">'.$arFields["TITLE"].'</a>';

        $tag = "BLOG|POST|".$ID;

        notifyAllEmployees($subject, $arFields["SOCNET_RIGHTS"], $tag);
    }
    
    return $arFields;
}
