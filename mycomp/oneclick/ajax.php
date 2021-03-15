<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Sale\Fuser;

class Item extends \Bitrix\Main\Engine\Controller
{
    public function configureActions(): array
    {
        return [
            'addDetail' => [
                '-prefilters' => [
                    Authentication::class,
                ],
            ],
            'addBasket' => [
                '-prefilters' => [
                    Authentication::class,
                ],
            ],
        ];
    }

    public function addDetailAction($product_id, $product_quantity, $phone_number)
    {
        Bitrix\Main\Loader::includeModule('sale');
        Bitrix\Main\Loader::includeModule('catalog');
        $basket = Bitrix\Sale\Basket::create(SITE_ID);
        $item = $basket->createItem("catalog", $product_id);
        $item->setFields(array(
            'QUANTITY' => $product_quantity,
            'CURRENCY' => Bitrix\Currency\CurrencyManager::getBaseCurrency(),
            'LID' => Bitrix\Main\Context::getCurrent()->getSite(),
            'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
        ));
        return $this->zakaz_create($basket, $phone_number);
    }

    public function gen_password($length = 20)
    {
        $chars = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP';
        $size = strlen($chars) - 1;
        $password = '';
        while($length--) {
            $password .= $chars[random_int(0, $size)];
        }
        return $password;
    }

    public function user_id()
    {
        global $USER;
        if($USER->isAuthorized())
        {
            $ID = $USER->GetID();
        }
        else
        {
            $filter = Array(
                "=EMAIL" => "oneclick@component.guest",
            );
            $rsUsers = CUser::GetList(($by="id"), ($order="desc"), $filter, array("FIELDS" => array("ID")));
            if($arUser = $rsUsers->GetNext())
            {
                $ID = $arUser["ID"];
            }
            else
            {
                $password = $this->gen_password();
                $user = new CUser;
                $arFields = Array(
                    "NAME"              => "Guest",
                    "EMAIL"             => "oneclick@component.guest",
                    "LOGIN"             => "oneclick@component.guest",
                    "ACTIVE"            => "Y",
                    "PASSWORD"          => $password,
                    "CONFIRM_PASSWORD"  => $password,
                );
                $ID = $user->Add($arFields);
            }
        }
        return $ID;
    }

    public function zakaz_create($basket, $phone_number)
    {
        $order = Bitrix\Sale\Order::create(SITE_ID, $this->user_id());
        $order->setPersonTypeId(1);
        $order->setBasket($basket);
        $propertyCollection = $order->getPropertyCollection();
        $phonePropValue = $propertyCollection->getPhone();
        $phonePropValue->setValue($phone_number);
        $propertyValue = $propertyCollection->createItem(array(
            "NAME" => GetMessage("MY_COMP_ONE_CLICK_STORE_1_CLICK"),
            "TYPE" => "STRING",
            "CODE" => "ONECLICK",
        ));
        $propertyValue->setField('VALUE', GetMessage("MY_COMP_ONE_CLICK_YES"));
        $result = $order->save();
        return $result->isSuccess();
    }

    public function addBasketAction($phone_number)
    {
        Bitrix\Main\Loader::includeModule('sale');
        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(Fuser::getId(),SITE_ID);
        return $this->zakaz_create($basket, $phone_number);
    }
}