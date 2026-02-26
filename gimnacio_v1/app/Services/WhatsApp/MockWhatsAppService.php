<?php

namespace App\Services\WhatsApp;

class MockWhatsAppService implements WhatsAppServiceInterface
{
    public function enviar(string $destino, string $contenido): array
    {
        // Simula envío exitoso; en producción reemplazar por Twilio/WhatsApp Business API
        return [
            'success' => true,
            'message_id' => 'mock_' . uniqid(),
        ];
    }
}
