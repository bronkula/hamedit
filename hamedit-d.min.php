<?php
define('SCRIPTTITLE','HamEdit');
define('SCRIPTVERSION','7.5');
define('CREATOR','Hamilton Cline');
define('CREATOREMAIL','hamdiggy@gmail.com');
define('CREATORWEBSITE','http://www.hamiltondraws.com');
// ****************************************************************************
// Definitions
// ----------------------------------------------------------------------------
define('SCRIPTNAME',substr($_SERVER['SCRIPT_NAME'],1));
define('SCRIPTLOCATION',$_SERVER['DOCUMENT_ROOT']);
define('VIRTUALLOCATION',"http://".$_SERVER['HTTP_HOST']);
define('PASSWORD','');
if(PASSWORD==='') {
die("You must open the script, and change the PASSWORD definition.");
}
// ****************************************************************************
// Handle Actions
// ----------------------------------------------------------------------------
if(isset($_GET['action'])){
// Grab Folder Feed
if($_GET['action']=="getfolder"){
die(json_encode(getFilesFromFolder($_GET['file'])));
}
// Edit Files
elseif($_GET['action']=="getfile"){
die(json_encode(file_get_contents($_GET['file'])));
}
elseif($_GET['action']=="submitfile"){
@file_put_contents($_GET['file'],stripslashes($_POST['body'])) or dieJson("Failed!");
dieJson("Files Saved!");
}
elseif($_GET['action']=="deletefile") {
if(@unlink($_GET['file'])) { dieJson("File Deleted!"); }
else { dieJson("Couldn't delete file!"); }
}
elseif($_GET['action']=="renamefile" || $_GET['action']=="renamefolder") {
// print_r($_GET);
// print_r($_POST);
if(@rename($_GET['file'],$_POST['newname'])) { dieJson("Renamed File!"); }
else { dieJson("Couldn't rename file!"); }
}
elseif($_GET['action']=="uploadfile") {
// print_r($_FILES);
if(@move_uploaded_file($_FILES['media']['tmp_name'], $_GET['file'])) { dieJson("Uploaded File!"); }
else { dieJson("File upload failed!"); }
}
// Edit Folders
elseif($_GET['action']=="deletefolder") {
if(@rmdir($_GET['file'])) { dieJson("Removed Folder!"); }
else { dieJson("Couldn't remove folder!"); }
}
elseif($_GET['action']=="makefolder") {
if(MakeDirectory($_GET['file'])) { dieJson("Made a new Folder!"); }
else { dieJson("Couldn't make folder!"); }
}
// Download File
elseif($_GET['action']=="downloadfile") {
header("Content-type: application/force-download");
header('Content-Disposition: inline; filename="'.$_GET['file'].'"');
header("Content-length: ".filesize($_GET['file']));
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($_GET['file']) . '"');
die(file_get_contents($_GET['file']));
}
die();
}
class LoginWithPassword {
public $password = "password";
public $msg = "";
function __construct($pass=false) {
@session_start();
if($pass!==false) $this->password = $pass;
$this->checkPassword();
$this->checkLoggedin();
}
function makeLogin(){
?>
<form method="post">
<div><?php echo $this->msg;?></div>
<input type="password" name="password">
<input type="submit" value="Login">
</form>
<?php
}
function checkPassword(){
if(isset($_POST['password'])){
if($_POST['password']==$this->password){
$_SESSION['loggedin'] = true;
header("Location:".SCRIPTNAME);
} else {
$this->msg = "That is not the correct password";
}
}
}
function checkLoggedin(){
if(!isset($_SESSION['loggedin'])){
$this->makeLogin(isset($msg)?$msg:"");
die();
}
}
}
function getFilesFromFolder($folder=".",$arr=array(),$recursive=false) {
if ($handle = opendir($folder)) {
while (false !== ($file = readdir($handle))) {
if($file!="." && $file!="..") {
if(is_dir("$folder/$file")){
if($recursive!=false) $arr = getFilesFromFolder("$folder/$file",$arr,$recursive);
$arr[] = (object)array(
"id"=>count($arr),
"type"=>"folder",
"folder"=>$folder,
"file"=>$file
);
}
elseif ($i=@getimagesize("$folder/$file")) {
// echo "$file<br>";
$arr[] = (object)array(
"id"=>count($arr),
"type"=>"binary",
"path"=>"$folder/$file",
"folder"=>$folder,
"file"=>$file,
"size"=>filesize("$folder/$file"),
"added"=>filemtime("$folder/$file"),
"modified"=>filemtime("$folder/$file"),
"width"=>$i[0],
"height"=>$i[1],
"mime"=>$i["mime"],
"title"=>preg_replace(array("/\.\w+$/","/_/"),array(""," "),$file)
);
} else {
$arr[] = (object)array(
"id"=>count($arr),
"type"=>"ascii",
"path"=>"$folder/$file",
"folder"=>$folder,
"file"=>$file,
"size"=>filesize("$folder/$file"),
"added"=>filemtime("$folder/$file"),
"modified"=>filemtime("$folder/$file"),
"title"=>preg_replace(array("/\.\w+$/","/_/"),array(""," "),$file)
);
}
}
}
closedir($handle);
}
return $arr;
}
new LoginWithPassword(PASSWORD);
// ****************************************************************************
// Utility Functions
// ----------------------------------------------------------------------------
function dieJson($str){ die('{"msg":"'.$str.'"}'); }
function MakeDirectory($dir, $mode = 0755) {
if (is_dir($dir) || @mkdir($dir,$mode)) return TRUE;
if (!MakeDirectory(dirname($dir),$mode)) return FALSE;
return @mkdir($dir,$mode);
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=600,initial-scale=1" />
<title><? echo SCRIPTTITLE." v".SCRIPTVERSION; ?></title>
<script src="http://code.jquery.com/jquery-2.1.1.js"></script>
<script src="http://cdn.jsdelivr.net/underscorejs/1.6.0/underscore-min.js"></script>
<style>
* { box-sizing:border-box; }
body,html,.maincontainer {
width:100%;
height:100%;
margin:0;
}
body,html {
font-size:16px;
font-family:Tahoma,Arial,sans-serif;
}
h1,h2,textarea {
margin:0;
}
h1 {
font-size:1rem;
padding-left:.5rem;
}
h2 {
margin-top:.5rem;
margin-left:-.5rem;
font-size:1rem;
cursor:pointer;
}
small {
opacity:.6;
}
div,li,p,h1,h2 {
line-height:1rem;
font-size:.9rem;
}
input,button {
margin-top:0;
margin-bottom:0;
font-family:inherit;
}
button:first-child {
margin-left:0;
}
button:last-child {
margin-right:0;
}
textarea {
font-family:"Consolas",monospace;
border-width:0;
font-size:.9rem;
outline:0;
}
section.folders {
position:absolute;
top:0;
left:0;
height:100%;
width:200px;
background-color:#d5efd1;
overflow-y:auto;
box-shadow: 2px 0 5px rgba(0,0,0,0.2);
z-index:2;
}
section.files {
position:absolute;
top:0;
left:200px;
height:100%;
width:calc(100% - 200px);
background-color:#eee;
z-index:1;
overflow:auto;
}
.folders-header {
height:2.75rem;
padding-top:.25rem;
padding-bottom:.25rem;
}
.magic-box {
margin-top:.25rem;
background-color:white;
height:1rem;
}
.magic-box input {
border-width:0;
padding:.1rem .5rem;
font-size:.8rem;
width:100%;
height:100%;
outline:0;
}
.folders-list {
height:calc(100% - 3.5rem);
width:100%;
margin-top:.25rem;
padding-bottom:100%;
overflow-y:auto;
}
.folders-list ul {
max-width:100%;
}
.folders-list li {
overflow-x:hidden;
max-width:100%;
}
.folders-list li:hover, .folders-list label:hover {
cursor:pointer;
}
.folders-list li:hover {
background-color:rgba(255,255,255,0.3);
max-width:auto;
}
.file-set {
font-size: .9rem;
margin-left:.75rem;
}
.files-header {
height:5.5rem;
background-color:#f9fff9;
padding:.25rem .5rem;
overflow:auto;
}
.files-crumbs {
}
.files-messages {
height:1.5rem;
padding:.25rem 1rem;
background-color:White;
box-shadow: 0 2px 5px rgba(0,0,0,0.2);
overflow: auto;
}
.files-body-message {
display:inline-block;
}
.files-file-tools {
padding:.25rem .5rem 0;
}
.files-filearea {
height:calc(100% - 7rem);
overflow:hidden;
}
.files-body	{
height:calc(100% - 1.5rem);
padding:0 .5rem .75rem;
margin-top:.25rem;
}
.files-body-textarea,.files-body-image {
height:100%;
}
.files-body-textarea textarea {
border-radius:5px;
height:100%;
width:100%;
}
.files-body-image img {
max-width:100%;
max-height:100%;
}
.file-info {
background-color:white;
position:fixed;
left:95%;
padding:10px;
border-radius:0 5px 5px 5px;
}
.breadcrumbs {
list-style-type:none;
margin:0;
padding:0;
}
.breadcrumbs li {
display:inline-block;
}
.breadcrumbs li:before {
content:"/";
margin:.5rem;
text-decoration:none;
}
.breadcrumbs li label:hover,.btn-clear:hover {
text-decoration:underline;
cursor:pointer;
}
.input-group {
overflow:hidden;
vertical-align: top;
}
.input-group-item {
border:1px solid #999;
font-size:.8rem;
padding:.1rem .2rem;
outline:0;
vertical-align: top;
cursor:pointer;
border-radius:3px;
float:left;
}
.input-group-input {
background-color:white;
width:9rem;
cursor:text;
}
.input-group-button {
background-color: #ddd;
border-radius:3px;
}
.input-group-button:hover {
background-color:white;
}
.input-group>.input-group-item:not(:only-child) {
float:left;
border-radius:0;
}
.input-group>.input-group-item~.input-group-item {
border-left-width:0;
}
.input-group>.input-group-item:first-child {
border-top-left-radius: 3px;
border-bottom-left-radius: 3px;
margin-right:0;
}
.input-group>.input-group-item:last-child {
border-top-right-radius: 3px;
border-bottom-right-radius: 3px;
margin-left:0;
}
.icon {
margin:0;
padding:0;
height:1rem;
width:1rem;
max-height:100%;
max-width:100%;
vertical-align:top;
background-repeat:no-repeat;
background-position:center;
background-size:cover;
display:inline-block;
}
.icon-rename {
background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAb0lEQVQ4y2MwMzNjgOIGKGYgIIaC0QWeAfF/qKb/UD4DKQbA8H9CGtENeADV9B+LK3DhBzADHvxHAyAFxACQXgZkxbNmzQJjmBiMj46RLWJAVozsRJgCbBhmyHAzgJJApDgaKU5IVEnKVMlMZGVnABUq1w5W9UYMAAAAAElFTkSuQmCC);
}
.icon-save {
background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAaUlEQVQ4y2P4//8/AyUYTJiZmYHwAyD+TyQGqUUx4MHChQv/k4JBepANAAvOmjXrv5+fH14MUgM14D+GASAFhABIzSA2gOIwIDEW8BuAKw0QZQBUEiuAqSXZAJgYzQx4QIYXHlAvM1GCAag+qy3SmeJPAAAAAElFTkSuQmCC);
}
.icon-filenew {
background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAeklEQVQ4y2P4//8/AwibmZnBcAMQ/8eDD4DUwfQxoBnQ4OTk9N/GxuY/PgAyBJsBYM0gAKPxGPAfmwFgm0GaZ82aRZ4BxAKiDejcMRuMyTZAp9gCjOlrAMjJMI3oGOYd2how8GFA1WgkNSEdIMGAA7hy4wECWRkjOwMAqNPz+sziGnwAAAAASUVORK5CYII=);
}
.icon-fileupload {
background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAiUlEQVQ4y+WTzQnAIAxGg0N58uQ6LtENvLuMbva1CQ2UYIP22sBDD37PHwwBICbGqBwXcBi8TnNkBEfOGSkleMWSmUDCXDo6AswEsjOHW2uysPcuLAtslVKET4JaK0IIAs+3BRpWtgQ2bCWu4Hl0i15l+RFnx/+bgB/N/gFPMLBYb81Ed6tip51PpabODGljTyQAAAAASUVORK5CYII=);
}
.icon-filedelete {
background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAj0lEQVQ4y7WTPQ6AIAyFuZArUydOxuLgFTgNxvu4uLg9LZGEkBZ/iE1eGHjvIwVqABiWtTbLn0JDkX05ZyqAd86BiNAqhkiAFObKawMACZBO5nAI4RtgX2Zs06gGeY89KoANKw0ipNxTAbVRCjdbkAIS8BZQQqSW/gd0tdB1id3P2PuRIh6WNkzmGlW8GecDuf/sFlEVBV4AAAAASUVORK5CYII=);
}
.icon-filedownload {
background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAh0lEQVQ4y+WTwQ2AIAxFG4bixIl1WMINuLMMbPa1jU0MASxebfLihf+EQgkAMd575bjAgsbrNEed4IgxIoSAVbFkJJAwl34XAowE8mcOl1JkYc5ZMAv6cs4JfxNw0zTYow193cFMsHWEWdgseB6lfwvmJqaUhM+3UGsVrIIGY82Gie5Rxc44n9ZezgzLI9gjAAAAAElFTkSuQmCC);
}
.icon-folderdelete {
background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAf0lEQVQ4y2P4//8/AyWYwczMjBjcAMT/0TBIjGgD/qMDqCEMYGcAGQ+w2EAQw7zwAGbqj8MH/3/ubP2PC4DkQGqQXPEAxXkgBe9sdLEagk0OpBfDf9gU4jIYqwHoGvC5CqcByIbg0kxbAyjyAkWBSG40UpaQKEjKD8BJmdLsDAA5v9OY+OaDjgAAAABJRU5ErkJggg==);
}
.icon-foldernew {
background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAaUlEQVQ4y2P4//8/AyWYwczMjBjcAMT/0TBIjGgD/qMDqCEMYGcAGQ+w2EAQw7zw4D+ZAKQXq/NgoHPHbDDGY8B/vAboFFuAMX0NADkZphEdo3uHNgYMfBiQE42UJSQKkvIDcFKmNDsDACiT23ztJwrwAAAAAElFTkSuQmCC);
}
.d-ib {
display:inline-block;
vertical-align: top;
}
.d-b {
display:block;
}
.d-n {
display:none;
}
.collapsed {
list-style-type:none;
margin:0;
padding:0;
}
.pull-right {
float:right;
}
.pull-left {
float:left;
}
.message-warning {
border:red 1px solid;
}</style>
</head>
<body>
<div class="maincontainer">
<section class="folders">
<header class="folders-header">
<h1><? echo SCRIPTTITLE; ?> <small>v<? echo SCRIPTVERSION; ?></small></h1>
<div class="magic-box">
<input type="text">
</div>
</header>
<div class="folders-list"></div>
</section>
<section class="files">
<div class="files-header">
<div class="files-crumbs"></div>
<div class="files-folder-tools"></div>
</div>
<div class="files-messages">
<span class='files-body-message'></span>
<span class="files-body-ellipsis"></span>
</div>
<div class="files-filearea">
<div class="files-file-tools"></div>
<div class="files-body"></div>
</div>
</section>
</div>
</body>
<script>
$(function(){
var he = {
files:[],
folders: [],
ascii: [],
binary: [],
currentFolder: "<?php echo SCRIPTLOCATION; ?>",
virtualLocation: "<?php echo VIRTUALLOCATION; ?>",
scriptLocation: "<?php echo SCRIPTLOCATION; ?>",
scriptName: "<?php echo SCRIPTNAME; ?>",
positiveFolder: "",
fileName: "",
fileContents: "",
messageTimer: false,
messageTimerLength: 2000,
fileList: [],
fileListp: 0
};
he.upPath = function(p,f){ return p.replace(/\/[^\/]+$/,""); };
he.downPath = function(p,f){ return p+"/"+f; };
he.concPath = function(p,f){
// console.log(p,f)
if(f===".."){ return he.upPath(p,f) }
else if(f!=="." && f!==undefined && f!==""){ return he.downPath(p,f); }
else { return p; }
};
he.addFilename = function(f){ return he.concPath(he.currentFolder,f); };
he.currentPath = function(){ return he.addFilename(he.fileName); };
he.virtualPath = function(){
return he.downPath(he.concPath(he.virtualLocation,he.positiveFolder),he.fileName);
};
// --------------------------------------------------------------------
// Getters
// --------------------------------------------------------------------
he.makeGet = function(o){
he.ell.startTimer();
$(".files-body-message").html(o.preMsg||"working");
$.ajax({
url:he.scriptName+"?action="+o.action+"&file="+o.file,
dataType:"json"
})
.done(o.done)
.always(function(){
he.ell.stopTimer();
he.setFilesMessage(o.postMsg||"complete!");
});
};
he.openFolder = function() {
he.makeGet({
// preMsg:"Opening Folder",
postMsg:"Folder Open",
action:"getfolder",
file:he.currentFolder,
done:function(d){
if(d.msg!=undefined) $(".folders-list").html(d.msg);
else {
he.currentFolderName = he.currentFolder.split("/").pop();
he.makeFolders(d);
he.makeFolderTools();
he.makeCrumbs();
}
}
});
};
he.newFolder = function(){
var newfolder = he.addFilename($(".js-tools-makefolder-name").val());
he.makeGet({
action:"makefolder",
file:newfolder,
done:function(d){
if(d.msg!=undefined) {
he.openExactFolder(newfolder);
}
}
});
};
he.deleteFolder = function(){
if(he.currentFolder===he.scriptLocation) return;
if(arguments[0]===undefined) {
he.makeDeleteMsg("folder");
return;
}
he.makeGet({
action:"deletefolder",
file:he.currentFolder,
done:function(d){
if(d.msg!=undefined) {
he.openExactFolder(he.addFilename(".."));
}
}
});
};
he.openAscii = function(file){
he.fileName = file;
he.makeGet({
action:"getfile",
file:he.addFilename(file),
done:function(d){
he.fileContents = d;
he.makeAsciiContents();
}
});
};
he.deleteFile = function(){
if(he.fileName==="") return;
if(arguments[0]===undefined) {
he.makeDeleteMsg("file");
return;
}
he.makeGet({
preMsg:"deleting",
preMsg:"file deleted",
action:"deletefile",
file:he.currentPath(),
done:function(d){
if(d.msg!=undefined) {
he.setFilesMessage(d.msg);
he.clearFile();
he.reloadFolder();
}
}
});
};
he.downloadFile = function(){
he.makeGet({
preMsg:"deleting",
preMsg:"file deleted",
action:"downloadfile",
file:he.currentPath(),
done:function(d){
// d = JSON.parse(d);
if(d.msg!=undefined) {
he.setFilesMessage(d.msg);
}
}
});
};
// --------------------------------------------------------------------
// Posters
// --------------------------------------------------------------------
he.makePost = function(o){
he.ell.startTimer();
$(".files-body-message").html(o.preMsg||"working");
var d = {
url:he.scriptName+"?action="+o.action+
(o.file!==undefined?"&file="+o.file:""),
type:"post",
data:o.data
};
for(var i in o.args) {
d[i] = o.args[i];
}
// console.log(d)
$.ajax(d)
.done(o.done)
.always(function(){
he.ell.stopTimer();
he.setFilesMessage(o.postMsg||"complete!");
});
};
he.submitFile = function(){
he.makePost({
preMsg:"saving",
action:"submitfile",
file:he.addFilename($(".js-files-name").val()),
data:$.param({body:$(".js-files-body").val()}),
done:function(d){
d = JSON.parse(d);
if(d.msg!=undefined) {
he.setFilesMessage(d.msg);
he.reloadFolder();
}
}
});
};
he.renameFile = function(oldname,newname){
he.makePost({
preMsg:"renaming",
action:"renamefile",
file:oldname,
data:$.param({newname:newname}),
done:function(d){
d = JSON.parse(d);
if(d.msg!=undefined) {
he.setFilesMessage(d.msg);
he.reloadFolder();
}
}
});
};
he.uploadFiles = function(e) {
he.fileList = e.originalEvent.target.files;
he.fileListp = 0;
// console.log(he.fileList);
he.uploadFile();
};
he.uploadFile = function(){
if(he.fileListp>=he.fileList.length) return;
var file = he.fileList[he.fileListp++];
var fdata = new FormData($("<form enctype='multipart/form-data'>")[0]);
fdata.append('media',file);
console.log(he.downPath(he.currentFolder,file.name))
he.makePost({
preMsg:"uploading",
postMsg:"upload complete",
action:"uploadfile",
file:he.downPath(he.currentFolder,file.name),
args:{
cache:false,
contentType:false,
processData:false
},
data:fdata,
done:function(d){
d = JSON.parse(d);
if(d.msg!=undefined) {
he.setFilesMessage(d.msg);
}
if(he.fileListp<he.fileList.length) {
he.uploadFile();
} else {
he.reloadFolder();
}
}
});
};
// --------------------------------------------------------------------
// Folder Functions
// --------------------------------------------------------------------
he.makeFolders = function(d){
he.files = _.sortBy(d,function(o){ return o.id; });
he.folders = _.sortBy(_.where(d,{"type":"folder"}),function(o){ return o.file; });
he.ascii = _.sortBy(_.where(d,{"type":"ascii"}),function(o){ return o.file; });
he.binary = _.sortBy(_.where(d,{"type":"binary"}),function(o){ return o.file; });
$(".folders-list").append(
he.makeList(he.folders,"folder","Folders"),
(he.ascii.length?he.makeList(he.ascii,"ascii","Ascii Files"):""),
(he.binary.length?he.makeList(he.binary,"binary","Binary Files"):"")
);
};
he.makeList = function(list,type,title) {
var temp = $("#js-folders-template").html();
return _.template(temp,{
title:title,
type:type,
listlength:list.length,
list:list
});
};
he.makeCrumbs = function(){
var crumbs = he.currentFolder.split("/");
crumbs.shift();
var str = "<ul class='breadcrumbs'>";
var currentfolder = "";
for(var i=0,l=crumbs.length;i<l;i++){
currentfolder+="/"+crumbs[i];
str+="<li class='js-choose-exact-folder' value='"+currentfolder+"'><label>"+crumbs[i]+"</label></li>";
}
$(".files-crumbs").html(str+"</ul>");
};
he.clearFolders = function(){
$(".folders-list").empty();
};
he.changeFolder = function(folder){
he.currentFolder = he.addFilename(folder);
if(he.currentFolder.length>=he.scriptLocation.length) {
he.positiveFolder = he.currentFolder.substr(he.scriptLocation.length+1);
}
else {
he.positiveFolder = null;
}
};
he.openNewFolder = function(file){
if(file==="/") {
he.changeFolder()
}
var crumbs = file.split("/");
for(var i in crumbs) {
he.changeFolder(crumbs[i]);
}
he.reloadFolder();
};
he.openExactFolder = function(folder){
he.clearFile();
he.clearFolders();
he.currentFolder = folder;
he.openFolder();
};
he.reloadFolder = function(){
he.clearFile();
he.clearFolders();
he.openFolder();
};
he.listClick = function(e){
var type = e.currentTarget.attributes.type.value,
file=$(this).find("label").html();
he.openFile(type,file);
};
he.openFile = function(){
he.clearMessage();
if(arguments.length===0) return;
if(arguments[0]==="folder" || arguments[0]==="dir") {
he.openNewFolder(arguments[1]);
}
else
if(arguments[0]==="ascii") {
he.openAscii(arguments[1]);
}
else
if(arguments[0]==="binary" || arguments[0]==="bin") {
he.openBinary(arguments[1]);
}
else {
he.openAscii(arguments[0]);
}
};
he.openBinary = function(file){
he.fileName = file;
he.makeBinaryContents();
};
he.newBlankFile = function(){
he.fileName = "";
he.fileContents = "";
he.makeAsciiContents();
};
// --------------------------------------------------------------------
// Templating
// --------------------------------------------------------------------
he.makeFolderTools = function(){
$(".files-folder-tools").html(_.template(
$("#js-folder-tools-template").html(),
{foldername:he.currentFolderName}
));
};
he.makeFileTools = function(){
$(".files-file-tools").html(_.template(
$("#js-files-tools-template").html(),
{
filename: he.fileName,
fullpath: he.currentPath()
}
));
};
he.makeAsciiContents = function(){
he.makeFileTools();
$(".files-body").html(_.template(
$("#js-files-ascii-template").html(),
{filecontents: he.fileContents}
));
};
he.makeBinaryContents = function(){
he.makeFileTools();
$(".files-body").html(_.template(
$("#js-files-binary-template").html(),
{virtualpath: he.virtualPath()}
));
};
he.clearFile = function(){
$(".files-file-tools").empty();
$(".files-body").empty();
};
he.catchKeys = function(e){
console.log(e);
catchTab($(this)[0],e.originalEvent);
};
// --------------------------------------------------------------------
// Rename Functions
// --------------------------------------------------------------------
he.renameCurrentFile = function(){
he.renameFile(
he.currentPath(),
he.addFilename($(".js-files-name").val())
);
he.fileName = $(".js-files-name").val();
};
he.renameCurrentFolder = function(){
// var fold = he.currentFolder.split("/");
// fold.pop();
he.renameFile(
he.currentFolder,
he.downPath(he.addFilename(".."),$(".js-tools-renamefolder-name").val())
// fold.join("/")+"/"+$(".js-tools-renamefolder-name").val()
);
};
// --------------------------------------------------------------------
// Messenger Functions
// --------------------------------------------------------------------
he.clearMessageTimer = function(){
clearTimeout(he.messageTimer);
he.messageTimer = false;
};
he.clearMessage = function() {
he.clearMessageTimer();
$(".files-body-message").empty();
}
he.setFilesMessage = function(str) {
$(".files-body-message").html(str);
he.clearMessageTimer();
he.messageTimer = setTimeout(function(){
$(".files-body-message").empty();
},he.messageTimerLength);
};
he.makeDeleteMsg = function(type){
$(".files-body-message").empty().append(
"[ ",
$("<a class='btn-clear'>").html("yes").on("click",function(e){
e.preventDefault();
he["delete"+capitalize(type)](true);
}),
" / ",
$("<a class='btn-clear'>").html("no").on("click",function(e){
e.preventDefault();
he.clearMessage();
}),
" ] Are you sure?"
);
}
// --------------------------------------------------------------------
// The Magic Box
// --------------------------------------------------------------------
he.magic = {
contents:"",
commands:[],
commandStack:{}
};
he.makeMagic = function(){
console.log("Magic!");
he.magic.contents = $(".magic-box input").val();
he.magic.commands = he.magic.contents.split(" ");
he.magic.commandStack = {
fn:he.makeMagicFn(he.magic.commands.shift()),
args:[]
};
for(var i in he.magic.commands) {
he.makeMagicArgs(he.magic.commands[i]);
}
he.magic.commandStack.fn.apply(null,he.magic.commandStack.args);
$(".magic-box input").val("");
};
he.makeMagicFn = function(func){
if(func==="cd") {
return he.openNewFolder;
}
else if(func==="open") {
return he.openFile;
}
else {
he.setFilesMessage("Couldn't understand that command");
}
};
he.makeMagicArgs = function(str){
he.magic.commandStack.args.push(str);
};
// --------------------------------------------------------------------
// Event Delegation
// --------------------------------------------------------------------
$("body")
.on("click","h1",function(){console.log(he);})
.on("click",".file-set h2",function(){
$(this).parent().find("ul").toggle();
})
// Folders
.on("click",".js-choose-file",he.listClick)
.on("click",".js-choose-exact-folder",function(){
he.openExactFolder($(this).attr("value"));
})
// Folder Tools
.on("click",".js-tools-blankfile",he.newBlankFile)
.on("click",".js-tools-makefolder-submit",he.newFolder)
.on("click",".js-tools-deletefolder-submit",function(e){
he.deleteFolder();
})
.on("click",".js-tools-renamefolder-submit",he.renameCurrentFolder)
.on("change",".js-tools-fileupload-input",he.uploadFiles)
.on("submit",".js-tools-fileupload-form",function(e){e.preventDefault();})
// File Tools
.on("click",".js-files-submit",he.submitFile)
.on("click",".js-files-removefile",function(e){
he.deleteFile();
})
.on("click",".js-files-downloadfile",he.downloadFile)
.on("click",".js-files-renamefile",he.renameCurrentFile)
.on("keydown",".magic-box",function(e){
// console.log(e);
if(e.keyCode===13) {
e.preventDefault();
he.makeMagic();
}
})
.on("keydown",".js-files-body",he.catchKeys);
he.ell = new Ellipsis($(".files-body-ellipsis"));
he.openFolder();
});
function setSelectionRange(input, selectionStart, selectionEnd) {
if (input.setSelectionRange) {
input.focus();
input.setSelectionRange(selectionStart, selectionEnd);
}
else if (input.createTextRange) {
var range = input.createTextRange();
range.collapse(true);
range.moveEnd('character', selectionEnd);
range.moveStart('character', selectionStart);
range.select();
}
}
function replaceSelection (input, replaceString) {
if (input.setSelectionRange) {
var selectionStart = input.selectionStart;
var selectionEnd = input.selectionEnd;
input.value = input.value.substring(0, selectionStart)+ replaceString + input.value.substring(selectionEnd);
if (selectionStart != selectionEnd){
setSelectionRange(input, selectionStart, selectionStart + replaceString.length);
}else{
setSelectionRange(input, selectionStart + replaceString.length, selectionStart + replaceString.length);
}
}else if (document.selection) {
var range = document.selection.createRange();
if (range.parentElement() == input) {
var isCollapsed = range.text == '';
range.text = replaceString;
if (!isCollapsed) {
range.moveStart('character', -replaceString.length);
range.select();
}
}
}
}
// --------------------------------------------------------------------
// An ellipsis loader generator
// --------------------------------------------------------------------
function Ellipsis(element){
var ell = this;
ell.running = false;
ell.element = element;
ell.ellipses = 0;
ell.maxEllipses = 5;
ell.timer = false;
ell.timerLength = 200;
ell.begin = null;
ell.increaseElls = function(){
ell.ellipses++;
if(ell.ellipses>ell.maxEllipses)ell.ellipses = 1;
}
ell.drawElls = function() {
var str = "";
for(var i=0;i<ell.ellipses;i++) str+=".";
ell.element.html(str);
}
ell.startTimer = function(){
ell.clearTimer();
ell.running = true;
ell.begin = new Date();
ell.timer = setInterval(function(){
if(ell.running===false) {
ell.stopTimer();
return;
}
ell.increaseElls();
ell.drawElls();
},ell.timerLength);
}
ell.clearTimer = function(){
clearInterval(ell.timer);
ell.timer = false;
}
ell.stopTimer = function(){
ell.clearTimer();
ell.element.empty()
.html((((new Date())-ell.begin)/1000)+" seconds to complete")
ell.timer = setTimeout(function(){ell.element.html("");},2000);
ell.begin = false;
ell.ellipses = 0;
ell.running = false;
}
}
// We are going to catch the TAB key so that we can use it, Hooray!
function catchTab(item,e){
if(e.keyCode==9){
replaceSelection(item,String.fromCharCode(9));
(function(item){
setTimeout(function(){ $(item).focus(); }, 0);
})(item);
return false;
}
}
function capitalize(str){
return str.substr(0,1).toUpperCase()+str.substr(1);
}
</script>
<!-- ------------------------------------------------------------------------------
Templates
------------------------------------------------------------------------------- -->
<script type="text/template" id="js-folder-tools-template">
<div class="d-ib pull-right">
<button type="button" class="js-tools-deletefolder-submit input-group-item input-group-button" title="Delete Folder"><i class="icon icon-folderdelete"/></button>
</div>
<div class="d-ib input-group">
<input class="js-tools-renamefolder-name input-group-item input-group-input" value="<%= foldername %>">
<button type="button" class="js-tools-renamefolder-submit input-group-item input-group-button" title="Rename Folder"><i class="icon icon-rename"/></button>
</div>
<div class="d-ib input-group">
<input class="js-tools-makefolder-name input-group-item input-group-input">
<button type="button" class="js-tools-makefolder-submit input-group-item input-group-button" title="Make Folder"><i class="icon icon-foldernew"/></button>
</div>
<div class="d-ib">
<button type="button" class="js-tools-blankfile input-group-item input-group-button" title="New File"><i class="icon icon-filenew"/></button>
</div>
<div class="d-ib">
<form enctype="multipart/form-data" method="post" class="js-tools-fileupload-form">
<input type="file" class="js-tools-fileupload-input d-n" id="uploader" multiple>
<span><label for="uploader" class="js-tools-fileupload-button input-group-item input-group-button" title="Upload File"><i class="icon icon-fileupload"/></label></span>
</form>
</div>
</script>
<script type="text/template" id="js-folders-template">
<div class='file-set'>
<h2><%= title %> <small>(<%= listlength %>)</small></h2>
<ul class='collapsed'>
<% if(type==="folder") { %>
<li class='js-choose-file' type='folder' key='.'><label>.</label></li>
<li class='js-choose-file' type='folder' key='..'><label>..</label></li>
<% } %>
<% _.each(list,function(item){ %>
<li class='js-choose-file' type='<%= type %>' key='<%= item.id %>'><label><%= item.file %></label></li>
<% }) %>
</ul>
</div>
</script>
<script type="text/template" id="js-files-ascii-template">
<div class='files-body-textarea'>
<textarea class='js-files-body' wrap style='resize:none;'><%- filecontents %></textarea>
</div>
</script>
<script type="text/template" id="js-files-binary-template">
<div class='files-body-image'>
<img src="<%= virtualpath %>">
</div>
</script>
<script type="text/template" id="js-files-tools-template">
<div class='d-ib input-group'>
<button type='button' class='js-files-submit input-group-item input-group-button' title="Save File"><i class="icon icon-save"/></button>
<input type='text' class='js-files-name input-group-item input-group-input' value="<%= filename||'newfile.txt' %>">
<button type='button' class='js-files-renamefile input-group-item input-group-button' title="Rename File"><i class="icon icon-rename"/></button>
</div>
<div class="d-ib">
<a href="?action=downloadfile&file=<%= fullpath %>" target="_blank" class="input-group-item input-group-button"><i class="icon icon-filedownload"/></a>
</div>
<div class='d-ib pull-right'>
<button type='button' class='js-files-removefile input-group-item input-group-button' title="Delete File"><i class="icon icon-filedelete"/></button>
</div>
</script>
</html>
