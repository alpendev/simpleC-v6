<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use App\SQLiteConnection as SQLiteConnection;
use App\SQLiteQuerySelect as SQLiteQuerySelect;

$pdo = (new SQLiteConnection())->connect();
$sqlite = new SQLiteQuerySelect($pdo);
$q = $_GET['q'];
$warga = $sqlite->getWargaById($q);
$kolektor = $sqlite->readKolektor($q);
$qrisi = "";
foreach ($kolektor as $klek) {
    if ($klek['petikan_id'] !== '') {
        $c = $klek['petikan_id'];
    } else {
        $c = '';
    }
}
$riwayatAset = $sqlite->getRiwayatByWarga($q, $c);
/*$s = array();
$d = array();
foreach($riwayatAset as $ck){
    $ckl = str_split($ck['kelas']);
    if($ckl[0] == "S"){
        array_push($s,"S");
    }else{
        array_push($d,"D");
    }
}*/


$html = "
<head>
  <title>Simple C</title>

<style type='text/css'>
td {border : 2px solid black ; border-collapse: collapse;}
.td1 {border : 2px solid white ; border-collapse: collapse;}
.tr1 {border : 5px solid black ; border-collapse: collapse;}
th {border : 2px solid white ; border-collapse: collapse;}
</style>
</head>
";
//qrcode


foreach ($warga as $ws) {
    $html .= "
    <div style='margin: -0.1cm -0.75cm; position: absolute; width: 100%'>
<img src='qr" . $ws['warga_id'] . ".png'>

<table width='100%' >
    <tr>
            <th>Nomor C :</th>
            <td class='td1' width='12%' ;>" . $ws['warga_id'] . "</td>
            <th>| Nama :</th>
            <td class='td1' width='30%'>" . $ws['nama'] . "</td>
            <th>| Alamat :</th>
            <td class='td1' width='25%'>" . $ws['alamat'] . "</td>
        </tr>";
}



$html .= "</table>
<div style='width:50%;position: absolute; left: 0'>
<table border='1' width='100%' style='border-collapse: collapse;'>
<tr>
    <td colspan='4'; style='text-align: center;'; >TANAH BASAH</td>
</tr>
<tr>
    <td width='12.5%' height='30px'>Persil</td>
    <td width='12.5%'>Kelas</td>
    <td width='17.5%'>Luas (m)</td>
    <td>Keterangan</td>
</tr>";
$asalsekarang = 0;
foreach ($riwayatAset as $ra) {
    $kl = str_split($ra['kelas']);
    if ($kl[0] == "S") {
        $bDar = $ra['dari'];
        $duar = explode("-", $bDar);
        $bKer = $ra['ke'];
        $kuar = explode("-", $bKer);
        if (isset($duar[1]) or isset($kuar[1])) {
            $aDar = $ra['dari'];
            $dar = explode("-", $aDar);
            if (isset($dar[1])) {
                $warga1 = $sqlite->getpetikById($ra['dari']);
                foreach ($warga1 as $w1) {
                    $wDari = $w1['nama'];
                    $wDariNo = "(Petikan C " . $w1['warga_id'] . ")";
                }
            } else {
                $warga1 = $sqlite->getWargaById($ra['dari']);
                foreach ($warga1 as $w1) {
                    $wDari = $w1['nama'];
                    $wDariNo = "";
                }
            }
            $aKer = $ra['ke'];
            $ker = explode("-", $aKer);
            if (isset($ker[1])) {
                $warga2 = $sqlite->getpetikById($ra['ke']);
                foreach ($warga2 as $w2) {
                    $wKe = $w2['nama'];
                    $wKeNo = "(Petikan C " . $w2['warga_id'] . ")";
                }
            } else {
                $warga2 = $sqlite->getWargaById($ra['ke']);
                foreach ($warga2 as $w2) {
                    $wKe = $w2['nama'];
                    $wKeNo = "";
                }
            }
            $keterangan = "<strong>" . $wDari . " " . $wDariNo . "</strong> " . $ra['sebab'] . " Ke<strong> " . $wKe . " " . $wKeNo . "</strong><br>Tgl : " . $ra['tgl'];
        } else {
            if ($ra['dari'] != '') {
                $warga1 = $sqlite->getWargaById($ra['dari']);
                $warga2 = $sqlite->getWargaById($ra['ke']);
                foreach ($warga1 as $w1) {
                    $wDari = $w1['nama'];
                }
                foreach ($warga2 as $w2) {
                    $wKe = $w2['nama'];
                }
                $keterangan = "<strong>" . $wDari . "</strong> " . $ra['sebab'] . " Ke<strong> " . $wKe . "</strong><br>Tgl : " . $ra['tgl'];
            } else {
                $keterangan = "Pemilik Pertama " . $ws['nama'];
            }
        }
        if ($ra['asal_id'] == $asalsekarang) {
            $html .= "<tr style=''>
                    <td  style=' text-align:center'>" . $ra['persil'] . "</td>
                    <td style='padding: 0 5px; text-align:center'>" . $ra['kelas'] . "</td>
                    <td style='padding: 0 5px; text-align:center'>" . $ra['luas'] . "</td>
                    <td style='padding: 0 5px; font-size:12px;'>" . $keterangan . "</td>
                </tr>";
            $asalsekarang = $ra['asal_id'];
        } else {
            $html .= "<tr style=''>
                    <td  style='border-top:5px solid black; text-align:center'>" . $ra['persil'] . "</td>
                    <td style='border-top:5px solid black; padding: 0 5px; text-align:center'>" . $ra['kelas'] . "</td>
                    <td style='border-top:5px solid black; padding: 0 5px; text-align:center'>" . $ra['luas'] . "</td>
                    <td style='border-top:5px solid black; padding: 0 5px; font-size:12px;'>" . $keterangan . "</td>
            </tr>";
            $asalsekarang = $ra['asal_id'];
        }
    }
}
/*if(count($s) < count($d)){
    $tr = count($d)-count($s);
    for($t=0; $t<$tr; $t++){
        $html .="<tr>
                    <td height='28.5px' style='padding: 0 5px'></td>
                    <td style='padding: 0 5px'></td>
                    <td style='padding: 0 5px'></td>
                    <td style='padding: 0 5px'></td>
                </tr>";
    }
}*/
$html .= "<tr>
    <td height='20px' style='padding: 0 5px'></td>
    <td style='padding: 0 5px'></td>
    <td style='padding: 0 5px'></td>
    <td style='padding: 0 5px'></td>
</tr>
</table>
</div>
<div style='width:50%;position: absolute; right: 0'>
<table  width='100%' style='border-collapse: collapse; margin-top: 1px'>
<tr>
    <td colspan='4'; style='text-align: center';>TANAH KERING</td>
</tr>
<tr>
<td width='12.5%' height='30px'>Persil</td>
<td width='12.5%'>Kelas</td>
<td width='17.5%'>Luas (m)</td>
<td>Keterangan</td>
</tr>";

foreach ($riwayatAset as $ra) {

    $kl = str_split($ra['kelas']);
    if ($kl[0] == "D") {
        $bDar = $ra['dari'];
        $duar = explode("-", $bDar);
        $bKer = $ra['ke'];
        $kuar = explode("-", $bKer);
        if (isset($duar[1]) or isset($kuar[1])) {
            $aDar = $ra['dari'];
            $dar = explode("-", $aDar);
            if (isset($dar[1])) {
                $warga1 = $sqlite->getpetikById($ra['dari']);
                foreach ($warga1 as $w1) {
                    $wDari = $w1['nama'];
                    $wDariNo = "(Petikan C " . $w1['warga_id'] . ")";
                }
            } else {
                $warga1 = $sqlite->getWargaById($ra['dari']);
                foreach ($warga1 as $w1) {
                    $wDari = $w1['nama'];
                    $wDariNo = "";
                }
            }
            $aKer = $ra['ke'];
            $ker = explode("-", $aKer);
            if (isset($ker[1])) {
                $warga2 = $sqlite->getpetikById($ra['ke']);
                foreach ($warga2 as $w2) {
                    $wKe = $w2['nama'];
                    $wKeNo = "(Petikan C " . $w2['warga_id'] . ")";
                }
            } else {
                $warga2 = $sqlite->getWargaById($ra['ke']);
                foreach ($warga2 as $w2) {
                    $wKe = $w2['nama'];
                    $wKeNo = "";
                }
            }
            $keterangan = "<strong>" . $wDari . " " . $wDariNo . "</strong> " . $ra['sebab'] . " Ke<strong> " . $wKe . " " . $wKeNo . "</strong><br>Tgl : " . $ra['tgl'];
        } else {
            if ($ra['dari'] != '') {
                $warga1 = $sqlite->getWargaById($ra['dari']);
                $warga2 = $sqlite->getWargaById($ra['ke']);
                foreach ($warga1 as $w1) {
                    $wDari = $w1['nama'];
                }
                foreach ($warga2 as $w2) {
                    $wKe = $w2['nama'];
                }
                $keterangan = "<strong>" . $wDari . "</strong> " . $ra['sebab'] . " Ke<strong> " . $wKe . "</strong><br>Tgl : " . $ra['tgl'];
            } else {
                $keterangan = "Pemilik Pertama " . $ws['nama'];
            }
        }
        if ($ra['asal_id'] == $asalsekarang) {
            $html .= "<tr style=''>
                <td  style=' text-align:center'>" . $ra['persil'] . "</td>
                <td style='padding: 0 5px; text-align:center'>" . $ra['kelas'] . "</td>
                <td style='padding: 0 5px; text-align:center'>" . $ra['luas'] . "</td>
                <td style='padding: 0 5px; font-size:12px;'>" . $keterangan . "</td>
            </tr>";
            $asalsekarang = $ra['asal_id'];
        } else {
            $html .= "<tr style=''>
                <td  style='border-top:5px solid black; text-align:center'>" . $ra['persil'] . "</td>
                <td style='border-top:5px solid black; padding: 0 5px; text-align:center'>" . $ra['kelas'] . "</td>
                <td style='border-top:5px solid black; padding: 0 5px; text-align:center'>" . $ra['luas'] . "</td>
                <td style='border-top:5px solid black; padding: 0 5px; font-size:12px;'>" . $keterangan . "</td>
        </tr>";
            $asalsekarang = $ra['asal_id'];
        }
    }
}
/*if(count($s) > count($d)){
    $tr = count($s)-count($d);
    for($t=0; $t<$tr; $t++){
        $html .="<tr>
                    <td height='20px' style='padding: 0 5px'></td>
                    <td style='padding: 0 5px'></td>
                    <td style='padding: 0 5px'></td>
                    <td style='padding: 0 5px'></td>
                </tr>";
    }
}*/
$html .= "<tr>
    <td height='20px' style='padding: 0 5px'></td>
    <td style='padding: 0 5px'></td>
    <td style='padding: 0 5px'></td>
    <td style='padding: 0 5px'></td>
</tr>
</table></div></div>";
$filename = "newpdffile";

// instantiate and use the dompdf class
$dompdf = new Dompdf();
$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
//$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream($filename, array("Attachment" => 0));
