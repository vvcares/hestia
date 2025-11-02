<?php
error_reporting(NULL);
$TAB = ' CSF';

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

include($_SERVER['DOCUMENT_ROOT'].'/templates/header.php');

render_page($user, $TAB, "");

?>
<div class="toolbar"></div>
    <!-- /.l-separator -->
	<div class="l-center units" style="margin:1em;text-align: center;">

<iframe  scrolling='auto' name='myiframe' id='myiframe' src='frame.php' frameborder='0' width='70%' onload='resizeIframe(this);'></iframe>
<script>
function resizeIframe(obj) {
 obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
 window.scrollTo(0,0);
 window.parent.scrollTo(0,0);
 window.parent.parent.scrollTo(0,0);
}
</script>
</div>
<?php

//include($_SERVER['DOCUMENT_ROOT'].'/templates/includes/js.php');
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.php');
