<?php
// src/Service/QrCodeService.php

namespace App\Service;

class QrCodeService
{
    /**
     * Génère le QR code vers le PDF
     */
    public function generateRapportQrCode(int $enfantId): string
    {
        // Utilisez votre IP locale
        $baseUrl = 'http://192.168.1.6:8000';
        
        // URL directe vers le PDF
        $pdfUrl = $baseUrl . '/pdf/rapport/' . $enfantId;
        
        $encodedUrl = urlencode($pdfUrl);
        
        // QR code qui ouvre/télécharge le PDF
        return "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={$encodedUrl}";
    }
}