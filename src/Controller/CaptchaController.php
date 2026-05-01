<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class CaptchaController extends AbstractController
{
    #[Route('/captcha/image', name: 'app_captcha_image', methods: ['GET'])]
    public function image(SessionInterface $session): Response
    {
        // ── Generate random 5-character code ──────────────
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code  = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $session->set('captcha_code', $code);

        // ── Try GD first, fallback to SVG ─────────────────
        if (extension_loaded('gd') && function_exists('imagecreate')) {
            return $this->generateGdImage($code);
        }

        return $this->generateSvgImage($code);
    }

    private function generateGdImage(string $code): Response
    {
        $width  = 160;
        $height = 50;
        $image  = imagecreate($width, $height);

        imagecolorallocate($image, 240, 240, 248); // background (first = bg)

        for ($i = 0; $i < 5; $i++) {
            $lc = imagecolorallocate($image, random_int(180,220), random_int(180,220), random_int(180,220));
            imageline($image, random_int(0,$width), random_int(0,$height), random_int(0,$width), random_int(0,$height), $lc);
        }
        for ($i = 0; $i < 60; $i++) {
            $dc = imagecolorallocate($image, random_int(180,230), random_int(180,230), random_int(180,230));
            imagesetpixel($image, random_int(0,$width-1), random_int(0,$height-1), $dc);
        }

        $x = 10;
        foreach (str_split($code) as $char) {
            $tc = imagecolorallocate($image, random_int(20,90), random_int(20,90), random_int(130,200));
            imagestring($image, 5, $x, random_int(10, 20), $char, $tc);
            $x += random_int(24, 30);
        }

        ob_start();
        imagepng($image);
        $data = ob_get_clean();
        imagedestroy($image);

        return new Response($data, 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Expires'       => '0',
        ]);
    }

    private function generateSvgImage(string $code): Response
    {
        $colors = ['#4a6cf7','#7c3aed','#0369a1','#065f46','#9f1239'];
        $chars  = str_split($code);
        $items  = '';
        $x      = 18;

        foreach ($chars as $i => $char) {
            $color  = $colors[$i % count($colors)];
            $rotate = random_int(-15, 15);
            $y      = 32 + random_int(-4, 4);
            $size   = random_int(18, 24);
            $items .= "<text x=\"{$x}\" y=\"{$y}\" fill=\"{$color}\" "
                    . "font-size=\"{$size}\" font-weight=\"bold\" "
                    . "font-family=\"monospace\" "
                    . "transform=\"rotate({$rotate},{$x},{$y})\">{$char}</text>";
            $x += 28;
        }

        // Noise lines
        $lines = '';
        for ($i = 0; $i < 4; $i++) {
            $lines .= "<line x1=\"".random_int(0,160)."\" y1=\"".random_int(0,50)."\" "
                    . "x2=\"".random_int(0,160)."\" y2=\"".random_int(0,50)."\" "
                    . "stroke=\"#ccc\" stroke-width=\"1\"/>";
        }

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="160" height="50" viewBox="0 0 160 50">
  <rect width="160" height="50" rx="6" fill="#f0f0f8"/>
  {$lines}
  {$items}
</svg>
SVG;

        return new Response($svg, 200, [
            'Content-Type'  => 'image/svg+xml',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Expires'       => '0',
        ]);
    }

    public static function verify(Request $request): bool
    {
        $session  = $request->getSession();
        $expected = $session->get('captcha_code', '');
        $given    = strtoupper(trim($request->request->get('captcha', '')));
        $session->remove('captcha_code');
        return $expected !== '' && $expected === $given;
    }
}