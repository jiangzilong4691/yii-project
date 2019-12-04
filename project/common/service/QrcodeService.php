<?php


namespace common\servicer;


use common\base\BaseService;

class QrcodeService extends BaseService
{
    public function creatWithLog()
    {
        \QRcode::png();
    }

    public function createWithText()
    {
        $errorCorrectionLevel = 'H';    //容错级别
        $matrixPointSize = 15;           //生成图片大小
        ob_start();
        \QRcode::png('http://www.zhibo.tv',false,$errorCorrectionLevel,$matrixPointSize,3);
        $contents = ob_get_contents();
        ob_end_clean();

        //创建文字图片
        $textImage = imagecreatetruecolor(200,200);
        //二维码
        $qrcode = imagecreatefromstring($contents);

        $qrcodeW = imagesx($qrcode);

        $logo_qr_w = $qrcodeW / 5;
        $scale = 200/$logo_qr_w;
        $logo_qr_h = 200/$scale;
        $from_width = ($qrcodeW-$logo_qr_w)/2;

        $white = ImageColorAllocate($textImage, 255, 255, 0);
        imagefill($textImage,0,0,$white);
        $green=ImageColorAllocate($textImage, 44, 195, 179);

        imagettftext($textImage, 30, 0, 20, 100, $green, \Yii::getAlias('@common/vendor/ttf/AnkeCalligraph.TTF'), 'zhiboTV');

        imagecopyresampled($qrcode, $textImage, $from_width, $from_width, 0, 0, $logo_qr_w, $logo_qr_h, 200, 200);

        Header("Content-type: image/png");
        imagepng($qrcode); //带文字二维码的文件名
        imagedestroy($qrcode);
    }
}