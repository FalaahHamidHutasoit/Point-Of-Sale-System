<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * KAMELA Phase 6 - response hardening.
 *
 * Kebijakan CSP masih mengizinkan inline style/script karena UI existing
 * memakai keduanya. Source eksternal dibatasi ke CDN yang memang dipakai UI.
 */
class KamelaSecurityHeaders implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-Permitted-Cross-Domain-Policies', 'none');
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->setHeader('Permissions-Policy', 'geolocation=(), camera=(), microphone=(), payment=(), usb=()');
        $response->setHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $response->setHeader('Cross-Origin-Resource-Policy', 'same-origin');

        $response->setHeader(
            'Content-Security-Policy',
            "default-src 'self'; " .
            "base-uri 'self'; " .
            "object-src 'none'; " .
            "frame-ancestors 'self'; " .
            "form-action 'self'; " .
            "img-src 'self' data:; " .
            "connect-src 'self'; " .
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; " .
            "font-src 'self' data: https://cdn.jsdelivr.net https://fonts.gstatic.com"
        );

        // Halaman authenticated dan halaman payment demo bertoken tidak boleh
        // disimpan browser/proxy cache. Public payment page membawa token sekali pakai
        // pada URL sehingga histori/cache browser tidak perlu menyimpan responsnya.
        $path = trim($request->getUri()->getPath(), '/');
        $isPaymentDemo = str_starts_with($path, 'payment/demo/')
            || str_starts_with($path, 'demo-bank/pay/');

        if (session()->get('logged_in') || $isPaymentDemo) {
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
        }

        if ($isPaymentDemo) {
            // Token payment ada di path URL. Jangan kirim URL bertoken sebagai Referer
            // ke asset/request lain dan jangan izinkan halaman demo terindeks mesin pencari.
            $response->setHeader('Referrer-Policy', 'no-referrer');
            $response->setHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        }

        // HSTS hanya dikirim saat request benar-benar HTTPS agar localhost HTTP tidak terkunci.
        if (method_exists($request, 'isSecure') && $request->isSecure()) {
            $response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
