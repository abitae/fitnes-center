<?php

namespace App\Services;

use App\Models\Core\Cliente;
use App\Models\Core\EvaluacionMedidasNutricion;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\Response;

class ReporteService
{
    /**
     * Generar reporte PDF de una evaluación
     */
    public function generarReporteEvaluacion(int $evaluacionId): Response
    {
        $evaluacion = EvaluacionMedidasNutricion::with(['cliente', 'nutricionista', 'evaluadoPor'])->find($evaluacionId);

        if (!$evaluacion) {
            throw new \Exception('Evaluación no encontrada');
        }

        $html = view('reportes.evaluacion', [
            'evaluacion' => $evaluacion,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
        ]);

        $mpdf->WriteHTML($html);
        $pdfContent = $mpdf->Output('', 'S');

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="evaluacion_' . $evaluacion->id . '.pdf"',
        ]);
    }

    /**
     * Generar reporte PDF del historial completo de un cliente
     */
    public function generarHistorialCliente(int $clienteId, array $filtros = []): Response
    {
        $cliente = Cliente::find($clienteId);

        if (!$cliente) {
            throw new \Exception('Cliente no encontrado');
        }

        $query = EvaluacionMedidasNutricion::with(['nutricionista', 'evaluadoPor'])
            ->where('cliente_id', $clienteId)
            ->orderBy('created_at', 'desc');

        if (isset($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (isset($filtros['fecha_desde'])) {
            $query->where('created_at', '>=', $filtros['fecha_desde']);
        }

        if (isset($filtros['fecha_hasta'])) {
            $query->where('created_at', '<=', $filtros['fecha_hasta']);
        }

        $evaluaciones = $query->get();

        $html = view('reportes.historial-cliente', [
            'cliente' => $cliente,
            'evaluaciones' => $evaluaciones,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
        ]);

        $mpdf->WriteHTML($html);
        $pdfContent = $mpdf->Output('', 'S');

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="historial_' . $cliente->id . '.pdf"',
        ]);
    }

    /**
     * Generar reporte PDF de composición corporal con gráficos
     */
    public function generarReporteComposicionCorporal(int $clienteId): Response
    {
        $cliente = Cliente::find($clienteId);

        if (!$cliente) {
            throw new \Exception('Cliente no encontrado');
        }

        $evaluaciones = EvaluacionMedidasNutricion::where('cliente_id', $clienteId)
            ->where('estado', 'completada')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $html = view('reportes.composicion-corporal', [
            'cliente' => $cliente,
            'evaluaciones' => $evaluaciones,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
        ]);

        $mpdf->WriteHTML($html);
        $pdfContent = $mpdf->Output('', 'S');

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="composicion_corporal_' . $cliente->id . '.pdf"',
        ]);
    }
}
