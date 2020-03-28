<?php
ob_start();
session_start();
header("Content-type: text/html; charset=utf-8");
error_reporting(0); //ถ้าจอขาว ให้เปลี่ยนค่าเป็น 1 เพื่อแสดงค่า error
set_time_limit(0);
$datenow=date("Y-m-d");
$transaction_leng=14;
$url_api="https://www.tmweasy.com/apiwallet.php";

//-----------------------------------------config----------------------------------------------------
//ข้อมูล https://www.tmweasy.com ต้องสมัครสมาชิกที่เว็บนี้ก่อนแล้วเอา id มาใส
$tmapi_user="xxx"; // Username
$tmpapi_assword="xxxxxx"; // รหัสผ่าน

//ข้อมูล บัญชี True money Wallet ของเว็บ App Truemoneywallet ต้องกรอกให้ถูกต้อง เพราะอาจทำให้บัญชี True money  ของท่านถูกระงับได้
$truewall_email=""; // Email ที่ใช้กับ  App Truemoneywallet
$truewall_phone=""; // เบอร์โทรที่ไว้รับยอดกับ True money Wallet เป็นเบอร์ที่สมัครคู่กับ Email ก่อนหน้า
$truepassword=""; // รหัสผ่าน ต้องนำรหัสผ่าน True money Wallet ของท่านไปเข้ารหัสความปลอดภัยที่ https://www.tmweasy.com/encode.php ก่อนแล้วนำcodeที่ได้หลังเข้ารหัสมาใส่ตรงนี้ได้เลย  รูปแบบ tmpw..................

//config ฐานข้อมูล
$sql_server="localhost";
$sql_user="";
$sql_password="";
$sql_database="";
$database_type=1; //ชนิดการเชื่อมต่อฐานข้อมูล 1 = Mysql, 2 = Mysqli , 3 = Mssql , 4 = Odbc Sqlserver
$database_table="";//ตารางที่ต้องการให้อัพเดทพ้อย
$database_idfield="";//ระเบียนบ้างอิง id ลูกค้าเช่น username
$database_pointfield="";//ระเบียนที่ต้องการให้อัพเดทยอด point ลูกค้า


//ตัวคูณเครดิตรสำหรับทรูวอเลท
$mul=1;

//เชทค่าเครดิตรสำหรับบัตรทรูมันนี่ เปลี่ยนค่าหลัง = 
$truemoney_set[50]=50;
$truemoney_set[90]=90;
$truemoney_set[150]=150;
$truemoney_set[300]=300;
$truemoney_set[500]=500;
$truemoney_set[1000]=1000;

//-----------------------------------------config----------------------------------------------------

function tmtopupconnect($tmuser,$tmpassword,$trueemail,$truepassword,$ip,$session,$transactionid,$action,$ref1){
	global $url_api;
	$urlconnect=$url_api."?username=$tmuser&password=$tmpassword&action=$action&tmemail=$trueemail&truepassword=$truepassword&session=$session&transactionid=$transactionid&clientip=$ip&ref1=$ref1&json=1";
	$ch = curl_init($urlconnect);
	//curl_setopt($ch, CURLOPT_SSLVERSION,3);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; th; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	@curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	return $doc=curl_exec($ch);
	return curl_error($ch);
	curl_close($ch);
}
function capchar($ip,$tmuser){
	return md5($tmuser.$ip);
}
function my_ip(){
	if ($_SERVER['HTTP_CLIENT_IP']) { 
		$IP = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (preg_match("[0-9]",$_SERVER["HTTP_X_FORWARDED_FOR"] )) { 
		$IP = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} else { 
		$IP = $_SERVER["REMOTE_ADDR"];
	}
		return $IP;
}

switch ($database_type) {
	case 1:
		$conn=mysql_connect($sql_server,$sql_user,$sql_password) or die("connect database error!");
		mysql_select_db($sql_database) or die("select database error!");
	break;
		
	case 2:
		$conn=mysqli_connect($sql_server,$sql_user,$sql_password,$sql_database) or die("Error Database is not connect!");
	break;
		
	case 3:
		$conn=mysqli_connect($sql_server,$sql_user,$sql_password,$sql_database) or die("Error Database is not connect!");
	break;
		
	case 4:
		$connect_db=odbc_connect('Driver={SQL Server};Server=' .$sql_server. ';Database=' . $sql_database. ';' ,$sql_user, $sql_password) or die('ไม่สามรถเชื่อมต่อฐานข้อมูลได้');
	break;
}

$conn=mysql_connect($sql_server,$sql_user,$sql_password) or die("connect database error!");
mysql_select_db($sql_database) or die("select database error!");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="KeyWords" content="True money,ทรูมันนี่ ,ตัดบัตรทรู ,auto truemoney" />
<META content="Copyright (c) 2010 tmweasy.com All Rights Reserved. tmweasy.com V.1" name=copyright>
<meta name="robots" content="all" />
<meta content='index, follow, all' name='robots'/>
<META Name="Googlebot" Content="index,follow">
<meta name="revisit-after" content="1 days">
<meta name="MSSmartTagsPreventParsing" content="True" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<center>
	<h1>ระบบแจ้งโอนผ่าน True money Wallet อัตโนมัติ</h1>
<font size="2">
<?php
if($_POST[send]){
	if(strlen($_POST[transactionid])<$transaction_leng){
		echo "<script>alert('กรุณากรอก เลขที่อ้างอิง ให้ครบ!');location='';</script>";
	}else{
	$returnserver=tmtopupconnect($tmapi_user,$tmpapi_assword,$truewall_email,$truepassword,my_ip(),$_POST[session],$_POST[transactionid],"yes",$_POST[ref1]);
	$returnserver=json_decode($returnserver,true);
	}
	if($returnserver[Status]=="check_success"){
		$money_total=$returnserver[Amount]; //จำนวนเงินที่ได้รับ
		if($returnserver[Type]=="truewallet"){
			//ยอดสำเร็จที่ถูกเช็คจากทรูวอเลท
			$point=$money_total*$mul;
		}else{
			//ยอดสำเร็จที่ถูกเช็คจากบัตรทรูมันนี่
			$point=$truemoney_set[$money_total];
		}
		switch ($database_type) {
			case 1:
				mysql_query("update $database_table set $database_pointfield = $database_pointfield + $point where $database_idfield='$_POST[ref1]' ");
			break;
		
			case 2:
				mysqli_query($conn,"update $database_table set $database_pointfield = $database_pointfield + $point where $database_idfield='$_POST[ref1]' ");
			break;
		
			case 3:
				mssql_query("update $database_table set $database_pointfield = $database_pointfield + $point where $database_idfield='$_POST[ref1]' ");
			break;
		
			case 4:
				odbc_exec($connect_db,"update $database_table set $database_pointfield = $database_pointfield + $point where $database_idfield='$_POST[ref1]' ");
			break;
		}
		
		echo "<p><h4 style='color:green'>เรียบร้อย</h4></p>
		<p>จำนวนเงิน คือ $money_total บาท ได้รับ $point เครดิตร</p>
		<p>ขอบคุณที่ใช้บริการครับ !  [ ปิดหน้านี้ได้เลย ]</p>";
		//-------------------------------------------------------------------------------------------
	}else{
		$error=$returnserver[Msg];//ค่าผิดพลาด ที่ส่งกลับมา
		
		//-------------------------------------------------------------------------------------------
		echo "<p><h4>ไม่สำเร็จ </h4></p>
		<p>$error</p>
		<p><a href='tmwallet.php'>[กลับไปลองอีกครั้ง]</a> </p>";
		//-------------------------------------------------------------------------------------------
	}
} else{
	$returnserver=tmtopupconnect($tmapi_user,$tmpapi_assword,"","","","","","","");
	$returnserver=json_decode($returnserver,true);
	if($returnserver[Status]=="ready"){
?>
<script>
co=0;
function loading(){
	co=co+1;
	switch(co)
	{
		case 1:
		char_load="โปรดรอสักครู่ ครับ |";
		break;
		case 2:
		char_load="โปรดรอสักครู่ ครับ /";
		break;
		case 3:
		char_load="โปรดรอสักครู่ ครับ -";
		break;
		case 4:
		char_load="โปรดรอสักครู่ ครับ \\";
		co=0;
		break;
	}
	document.getElementById("loadvip").innerHTML=char_load;
	setTimeout("loading()", 100);
}	

</script>


	<hr>
	<div align="left">
	<form method="POST" name="tmtopup">
		<INPUT TYPE="hidden" NAME="send" value="ok">
		<table align="center" cellpadding="0" cellspacing="0">
			<tr bgcolor="#F4F2F7"><td colspan="2" align="center"><img src="http://tmwallet.thaighost.net/images/support.jpg"></td></tr>
			<tr bgcolor="#ff9900"><td align="center"><h2>Step 1</h2></td><td align="center"><p><b>โอน - เติมยอดเข้า บัญชี True Money Wallet</b></p><h2><?php echo $truewall_phone; ?></h2>
			<p>แล้วนำเลขอ้างอิงที่ได้รับ มาใส่ใน step2</p></td></tr>
			
			<tr bgcolor="#009966"><td rowspan="2" align="center" bgcolor="#009966"><h2>Step 2</h2></td>
			<td align="center"><br><INPUT TYPE="text" NAME="ref1" placeholder="Username" value="<?php echo $_GET[ref1]; ?>" style="width:95%;height:30px;font-size:20px"></td></tr>

			<tr bgcolor="#009966"><td align="center"><br><input name="transactionid" value="" maxlength="<?php echo $transaction_leng; ?>" placeholder="เลขที่อ้างอิง - บัตรทรูมันนี่" style="width:95%;height:30px;font-size:20px">
			<div><a href="http://tmwallet.thaighost.net/images/transactionid.jpg" target="_transactionid">ตัวอย่างการดู เลขที่อ้างอิง</a></div></font>
			
			<tr bgcolor="#ff0000"><td colspan="2" align="center"><BR><div id="loadvip"></div>
			<input type="submit" value="แจ้งโอน" name="send" onClick="this.disabled=1;this.value='รอสักครู่กำลังตรวจสอบเลขบัตร...';document.forms[0].submit();loading()" style="height:30px;font-size:20px"></td></tr>
		</table>
	</form>
	</div>
<?php 
	}else if($returnserver[Status]=="noready"){
		echo "<p><img src='https://www.tmweasy.com/images/busy.png'></p><p><b>กำลังมีผู้ทำรายการอยู่ โปรดรอประมาณ 20 วินาที</b> </p>
		<p><a href='tmwallet.php'>คลิกเพื่อลองใหม่อีกครั้ง</a></p>";
	}else if($returnserver[Status]=="not_connect"){
		echo "<p><img src='https://www.tmweasy.com/images/notcon.png'></p><p><b>ไม่สามารถติดต่อ Server True Money ได้ โปรดรอสักครู่..</b> </p>
		<p><a href='tmwallet.php'>คลิกเพื่อลองใหม่อีกครั้ง</a></p>";
	}else if($returnserver[Status]=="block_ip"){
		echo "<p><img src='https://www.tmweasy.com/images/block_ip.png'></p><p><b>ถูก block ip ชั่วคราว เนื่องจากทำรายการไม่ถูกต้อง เกิน 6 ครั้ง</b> </p>
		<p><a href='tmwallet.php'>คลิกเพื่อลองใหม่อีกครั้ง</a></p>";
	}else{
		echo "<p>ยังไม่พร้อมใช้งาน โปรดติดต่อผู้ดูแลระบบ </p>";
	}
}
?>
<hr>
</body>
</html>
