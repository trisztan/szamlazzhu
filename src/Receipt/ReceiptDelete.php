<?php

namespace FerencBalogh\Szamlazzhu\Receipt;
use Illuminate\Support\Facades\Log;
use FerencBalogh\Szamlazzhu\Traits\XmlHelper;

class ReceiptDelete
{
    use XmlHelper;

    private $receipt;

    public function __construct($receipt) {
        $this->receipt = receipt;
    }

    public function deleteReceipt()
    {
        $szamla = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><xmlnyugtacreate xmlns="http://www.szamlazz.hu/xmlnyugtast" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.szamlazz.hu/xmlnyugtast xmlnyugtast.xsd"></xmlnyugtacreate>');

        $beallitasok = $szamla->addChild('beallitasok');
        $beallitasok->addChild('felhasznalo', env('SZAMLAZZ_USERNAME'));
        $beallitasok->addChild('jelszo', env('SZAMLAZZ_PASSWORD'));
        $beallitasok->addChild('pdfLetoltes', 'true');

        $fejlec = $szamla->addChild('fejlec');
        $fejlec->addChild('nyugtaszam', $nyugtaszam);

        $date = date('Ym');

        if (!file_exists(storage_path('data/nyugta'))) {
            mkdir(storage_path('data/nyugta'), 0755, true);
        }

        if (!file_exists(storage_path('data/nyugta/' . $date))) {
            mkdir(storage_path('data/nyugta/' . $date), 0755, true);
        }

        $file = fopen(storage_path('data/nyugta/' . $date . '/' . $this->receipt . '_storno.xml'), 'w+');
        fwrite($file, $xml);
        fclose($file);

        return $data = $this->sendXML(storage_path('data/nyugta/' . $date . '/' . $this->receipt . '_storno.xml'),
            $this->receipt, $date);

    }

    private function sendEmail($xmlfile = 'nyugta.xml', $receipt, $date)
    {
        if (!file_exists(storage_path('data/nyugta/' . $date . '/pdf'))) {
            mkdir(storage_path('data/nyugta/' . $date . '/pdf', 0755, true));
        }

        $ch = curl_init("https://www.szamlazz.hu/szamla/");
        $pdf = storage_path('data/nyugta/' . $date . '/pdf/' . $receipt . '_storno.pdf');
        $cookie_file = storage_path('data/nyugta/nyugta_email_storno_cookie.txt');
        if (!file_exists($cookie_file)) {
            $cookie = fopen($cookie_file, 'w');
            fwrite($cookie, '');
            fclose($cookie);
        }
        $fp = fopen($pdf, "w");
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            array('action-szamla_agent_nyugta_storno' => new \CURLFile(realpath($xmlfile))));
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        if (file_exists($cookie_file) && filesize($cookie_file) > 0) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        }
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        if (mime_content_type($pdf) == 'text/plain') {
            $result = false;
        } else {
            $result = true;
        }
        $response = array(
            'result' => $result,
            'body'   => $pdf
        );

        return $response;
    }
}