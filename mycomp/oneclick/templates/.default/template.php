<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<div class="oneclick">
    <div class="button call_form"><?=GetMessage("MY_COMP_ONE_CLICK_BUTTON_NAME")?></div>
</div>

<script>
    $(function() {
        $(document).on("click", ".call_form", function() {
            var form = $(this);
            if(form.hasClass("disabled"))
                return false;
            form.addClass("disabled");
            $.ajax({
                url: "<?=$templateFolder?>/modal.php",
                type: "POST",
                data: {},
                success: function(data) {
                    $("body").append(data);
                    $(".modal_background").css({"display":"flex"});
                    $(".modal_form").fadeIn();
                    form.removeClass("disabled");
                }
            });
        });

        $(document).on("click", ".modal_form", function(e) {
            e.stopPropagation();
        });

        $(document).on("click", ".close_form, .modal_background", function() {
            $(".modal_background").hide().remove();
        });

        <?if($arParams["PLACE_COMP"] == "DETAIL"):?>
        var offers = <?=$arParams["PRODUCT_ID"]?>;
        BX.addCustomEvent('onCatalogStoreProductChange', function(changeID) {
            offers = changeID;
        });
        <?endif;?>

        $(document).on("click", ".submit_button .button", function() {
            var phone_number = $("#phone_number").val();
            if(phone_number.length != 12 ) {
                $(".oneclick .error_form").html("*<?=GetMessage("MY_COMP_ONE_CLICK_ERROR_TEXT")?>");
                return;
            }
            $(".modal_background").hide().remove();

            <?if($arParams["PLACE_COMP"] == "DETAIL"):?>
            var product_quantity = $("input.<?=$arParams["QUANTITY_NAME"]?>").val();
            BX.ajax.runComponentAction('mycomp:oneclick', 'addDetail', {
                data: {
                    product_id: offers,
                    product_quantity: product_quantity,
                    phone_number: phone_number
                }
            }).then(function(response) {
            <?elseif($arParams["PLACE_COMP"] == "BASKET"):?>
            BX.ajax.runComponentAction('mycomp:oneclick', 'addBasket', {
                data: {
                    phone_number: phone_number
                }
            }).then(function(response) {
                BX.Sale.BasketComponent.sendRequest('refreshAjax', {
                    fullRecalculation: 'Y'
                });
            <?endif;?>
                if(response.data) {
                    $("body").append('<div class="oneclick result_form"><?=GetMessage("MY_COMP_ONE_CLICK_ZAKAZ_TRUE")?></div>');
                    $(".result_form").fadeOut({duration: 3000});
                }
                else {
                    $("body").append('<div class="oneclick result_form error"><?=GetMessage("MY_COMP_ONE_CLICK_ZAKAZ_FALSE")?></div>');
                    $(".result_form").fadeOut({duration: 3000});
                }
            });
        });
    });
</script>