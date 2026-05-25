<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    public function viewLogs(Request $request)
    {
        try {

            // Only channel required
            $request->validate([
                'channel' => 'required|string'
            ]);

            $channel = $request->channel;

            // Path of log file
            $logPath = storage_path("logs/{$channel}.log");

            if (!File::exists($logPath)) {
                return response()->json([
                    'status'  => false,
                    'message' => "Log file not found for: $channel"
                ], 404);
            }

            // Read full file
            $fileData = File::get($logPath);

            // Convert to array (each line = one log)
            $lines = explode("\n", trim($fileData));

            // Remove blank lines
            $lines = array_filter($lines);

            // Latest first
            $lines = array_reverse($lines);

            return response()->json([
                'status'  => true,
                'channel' => $channel,
                'total'   => count($lines),
                'logs'    => array_values($lines)
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
