<?php
/**
 * @version    $Id$
 */
class Geek_Captcha_Image extends Zend_Captcha_Word
{
    /**
     * __construct
     * 
     * @param string $namespace 
     * @return void
     */
    public function __construct($namespace = null)
    {
        $this->setSession(new Zend_Session_Namespace(__CLASS__ . '::' . $namespace));
        $this->setTimeout(300);
        $this->getSession()->setExpirationHops(1, null, true);
        $this->getSession()->setExpirationSeconds($this->getTimeout());
    }

    /**
     * isValid
     * 
     * @param string $code 
     * @return bool
     */
    public function isValid($code)
    {
        return is_string($code) && isset($code[3]) && $code === $this->getWord();
    }

    /**
     * Display the captcha
     *
     * @param  Zend_View_Interface $view
     * @param  mixed $element
     * @return string
     */
    public function render(Zend_View_Interface $view = null, $element = null)
    {
        $csid = $this->generate();

        if (rand(1, 10) < 8)
        {
            $code = $this->getWord();
            $l = strlen($code);
            $im = imagecreatetruecolor($w = 10*$l, $h = 18); // 图片尺寸
            imagefill($im, 0, 0, ImageColorAllocate($im, 255, 255, 255)); // 图片背景

            // 写入验证码
            for($i = 0; $i < $l; $i++)
            {
                $font = ImageColorAllocate($im, rand(0, 100), rand(0, 100), rand(0, 255));
                imagestring($im, 5, 2 + $i * 10, 1, $code[$i], $font);
            }

            //加入干扰线;
            $rand = mt_rand(2,10);
            $rand1 = mt_rand(3,15);
            $rand2 = mt_rand(1,5);
            for ($px=-80;$px<=$w;$px+=1)
            {
                $x=$px/$rand1;
                $y=0;
                if ($x!=0)
                {
                    $y=sin($x);
                }
                $py=$y*$rand2;

                imagesetpixel($im, $px+80, $py+$rand, $font);
            }
        }
        else
        {
            $text = $this->getWord();

            $im_y = 40;
            $im_x = strlen($text)*$im_y;
            $im = imagecreatetruecolor($im_x,$im_y);
            $text_c = ImageColorAllocate($im, mt_rand(0,100),mt_rand(0,100),mt_rand(0,100));
            $buttum_c = ImageColorAllocate($im, 255,255,255);
            imagefill($im, 16, 13, $buttum_c);

            $font = APPLICATION_PATH . '/../var/font/captcha.ttf';

            for ($i=0;$i<strlen($text);$i++)
            {
                $tmp =substr($text,$i,1);
                $array = array(-1,1);
                $p = array_rand($array);
                $an = $array[$p]*mt_rand(1,10);//角度
                $size = 28;
                imagettftext($im, $size, $an, 15+$i*$size, 35, $text_c, $font, $tmp);
            }

            $distortion_im = imagecreatetruecolor ($im_x, $im_y);

            imagefill($distortion_im, 16, 13, $buttum_c);
            for ( $i=0; $i<$im_x; $i++) {
                for ( $j=0; $j<$im_y; $j++) {
                    $rgb = imagecolorat($im, $i , $j);
                    if( (int)($i+20+sin($j/$im_y*2*M_PI)*10) <= imagesx($distortion_im)&& (int)($i+20+sin($j/$im_y*2*M_PI)*10) >=0 ) {
                        imagesetpixel ($distortion_im, (int)($i+10+sin($j/$im_y*2*M_PI-M_PI*0.1)*4) , $j , $rgb);
                    }
                }
            }

            //加入干扰象素;
            $rand = mt_rand(5,30);
            $rand1 = mt_rand(15,25);
            $rand2 = mt_rand(5,10);
            for ($yy=$rand; $yy<=+$rand+2; $yy++){
                for ($px=-80;$px<=$im_x;$px=$px+0.1)
                {
                    $x=$px/$rand1;
                    $y=0;
                    if ($x!=0)
                    {
                        $y=sin($x);
                    }
                    $py=$y*$rand2;

                    imagesetpixel($distortion_im, $px+80, $py+$yy, $text_c);
                }
            }

            imagedestroy($im);
            $imResampled = imagecreatetruecolor($im_x/4, 18);
            imagecopyresampled($imResampled, $distortion_im, 0, 0, 0, 0, $im_x/4, 18, $im_x, $im_y);
            imagedestroy($distortion_im);
            $im = $imResampled;
        }


        header("Content-type: image/PNG");
        ImagePNG($im);
        ImageDestroy($im);

        return $csid;
    }
}
// End of file : Image.php
