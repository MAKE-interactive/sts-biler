<?php

function forceMenuDimser(&$arMenu)
{
    if (empty($arMenu) || !$arMenu) {
        return false;
    }

    global $USER;


    $arMenu[] = array(
        'Dimser',
        file_exists($_SERVER["DOCUMENT_ROOT"].SITE_DIR."ddb/index.php") ? SITE_DIR."ddb/" : SITE_DIR,
        array(),
        array(
            "name" => "dimser_list",
            "counter_id" => "dimser-list",
            "menu_item_id" => "menu_dimser_list",
            "my_tools_section" => false,
        ),
        ""
    );
}

/**
 * Get user's blog group ID
 * @return int The blog group ID
 */
function userBlogGroupId()
{
    global $USER;

    $groups = $USER->GetUserGroupArray();

    $group = 1; // DDB social network group

    if (in_array(17, $groups)) {
        $group = 3; // STS social network group
    }

    if (in_array(19, $groups)) {
        $group = 5; // ERA social network group
    }

    if (in_array(21, $groups)) {
        $group = 7; // MFA social network group
    }

    if (in_array(23, $groups)) {
        $group = 9; // Bilogco social network group
    }

    if (in_array(25, $groups)) {
        $group = 11; // Bilogco social network group
    }

    if (in_array(27, $groups)) {
        $group = 13; // Bilogco social network group
    }

    if (in_array(29, $groups)) {
        $group = 15; // SDK social network group
    }

    return $group;
}

function userSiteByGroupId($group)
{
    $group = intval($group);

    switch ($group) {

        // sts
        case 17:
            $site = 'mh';
            break;


        // era
        case 19:
            $site = 'lb';
            break;


        // mfa
        case 21:
            $site = 'py';
            break;


        // bilogco
        case 23:
            $site = 'rc';
            break;


        // nhe
        case 25:
            $site = 'jf';
            break;


        // autohallen
        case 27:
            $site = 'ku';
            break;

        // sdk
        case 29:
            $site = 'qy';
            break;
            
        // ddb
        case 12:
        // default:
            $site = 's1';
            break;
    }

    return $site;
}
