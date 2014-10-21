<?
if(IS_TEST) {
    define("VERSION", date("Ymd"));
} else {
    define("VERSION", date("Ym"));
}
?>