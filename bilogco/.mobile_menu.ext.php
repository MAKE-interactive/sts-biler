<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arItems = [];

if(array_intersect($getGroups, array(79))) 
{
	array_push($arItems, [
		"title" => "Værksted",
		"useLetterImage" => true,
		"color" => "#ed1c24",
		"params" => [
			"url" => "/bilogco/ddb/vrk/index.php"
		],
		"id" => "custom_ddb1",
	]);
}

if(array_intersect($getGroups, array(104))) 
{
	array_push($arItems, [
		"title" => "Salg",
		"useLetterImage" => true,
		"color" => "#1e3f5e",
		"params" => [
			"url" => "/bilogco/ddb/salg/index.php"
		],
		"id" => "custom_ddb2",
	]);
}

if(array_intersect($getGroups, array(75))) 
{
	array_push($arItems, [
		"title" => "CarPlanner",
		"useLetterImage" => true,
		"color" => "#8cd59f",
		"params" => [
			"url" => "/bilogco/ddb/car/index.php"
		],
		"id" => "custom_ddb3",
	]);
}

if(array_intersect($getGroups, array(74))) 
{
	array_push($arItems, [
		"title" => "BizFone",
		"useLetterImage" => true,
		"color" => "#5658fe",
		"params" => [
			"url" => "/bilogco/ddb/biz/index.php"
		],
		"id" => "custom_ddb4",
	]);
}

if(array_intersect($getGroups, array(47))) 
{
	array_push($arItems, [
		"title" => "HFGD",
		"useLetterImage" => true,
		"color" => "#459151",
		"params" => [
			"url" => "/bilogco/ddb/hfgd/index.php"
		],
		"id" => "custom_ddb5",
	]);
}

if(array_intersect($getGroups, array(116))) 
{
	array_push($arItems, [
		"title" => "Fraværsregistrering",
		"useLetterImage" => true,
		"color" => "#b1612a",
		"params" => [
			"url" => "/bilogco/ddb/tm/index.php"
		],
		"id" => "custom_ddb6",
	]);
}

if(!empty($arItems))
{
	$menuStructure[] = [
		"title" => "BILOGCO",
		"hidden" => false,
		"sort" => $menuSort++,
		"items" => $arItems
	];
}

?>