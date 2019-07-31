<html>

<body
    onload="document.location.replace('<?php if(isset($redirectUrl)) echo $redirectUrl; ?>')">
<form method="get"
      action="<?php if(isset($redirectUrl)) echo $redirectUrl; ?>">
    <input id="iid" name="iid" type="hidden" value="click"/>
</form>
</body>

</html>
