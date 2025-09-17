<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PoNumberGeneratorService;

class PoNumberController extends Controller
{
    /**
     * Generate PO number via API
     */
    public function generate(Request $request)
    {
        try {
            $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
            $type = $request->get('type', 'surat_jalan');
            
            // Validate type
            if (!in_array($type, ['surat_jalan', 'transaksi'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid type. Must be surat_jalan or transaksi'
                ], 400);
            }
            
            // Generate PO number
            $poNumber = PoNumberGeneratorService::generateForDate($tanggal, $type);
            
            return response()->json([
                'success' => true,
                'po_number' => $poNumber,
                'type' => $type,
                'tanggal' => $tanggal
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating PO number: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get next PO number without saving
     */
    public function getNext(Request $request)
    {
        try {
            $type = $request->get('type', 'surat_jalan');
            
            // Validate type
            if (!in_array($type, ['surat_jalan', 'transaksi'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid type. Must be surat_jalan or transaksi'
                ], 400);
            }
            
            // Get next PO number
            $poNumber = PoNumberGeneratorService::getNextNumber($type);
            
            return response()->json([
                'success' => true,
                'po_number' => $poNumber,
                'type' => $type
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting next PO number: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check if PO number is unique
     */
    public function checkUnique(Request $request)
    {
        try {
            $poNumber = $request->get('po_number');
            $type = $request->get('type', 'surat_jalan');
            
            if (empty($poNumber)) {
                return response()->json([
                    'success' => false,
                    'message' => 'PO number is required'
                ], 400);
            }
            
            // Validate type
            if (!in_array($type, ['surat_jalan', 'transaksi'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid type. Must be surat_jalan or transaksi'
                ], 400);
            }
            
            // Check uniqueness
            $isUnique = PoNumberGeneratorService::isUnique($poNumber, $type);
            
            return response()->json([
                'success' => true,
                'is_unique' => $isUnique,
                'po_number' => $poNumber,
                'type' => $type
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking PO number uniqueness: ' . $e->getMessage()
            ], 500);
        }
    }
}
