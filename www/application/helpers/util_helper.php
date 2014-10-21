<?
function template_include($path, $id) {
    echo "<script type='text/html' id='".$id."'>";
    include($path);
    echo "</script>";
}
?>