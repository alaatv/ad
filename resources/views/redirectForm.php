<html>
<?php if(isset($redirectUrl)) { ?>
    <body onload="document.location.replace('<?php echo $redirectUrl; ?>')">
    <form method="get"
          action="<?php echo $redirectUrl; ?>">
        <input id="iid" name="iid" type="hidden" value="click"/>
    </form>
<?php }else{ ?>
    <body>
        <p>No redirect Url found</p>
<?php } ?>

    </body>
</html>
