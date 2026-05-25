<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Remittance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf as PdfToImage;
use Illuminate\Support\Facades\File;

use setasign\Fpdi\Fpdi;


class eKycController extends Controller
{
public function applyEkyc($id)
    {
        $data = Remittance::find($id);

        //return $data;
        if (!$data) {
            return "Record not found";
        }
    
        return view('admin.eKyc.apply', compact('data'));
    }

  





public function downloadEkycPdf($id)
{
    $data = Remittance::findOrFail($id);

    // ✅ Safe filename
    $brandName = strtolower(preg_replace('/[^A-Za-z0-9]/', '_', $data->brand_name));
    $fileName = $brandName . '_merchant_ekyc.pdf';

    // ✅ Documents
    $documents = [
        'Live Pic' => $data->live_photp,
        'PAN Card' => $data->pan_doc_url,
        'Aadhaar Front' => $data->aadhaar_doc_url,
        'Aadhaar Back' => $data->aadhaar_back_url,
        'Signature' => $data->signatory_url,
        'Bank Proof' => $data->bank_doc_url,
        'Cancelled Cheque' => $data->cancelled_cheque,
        'Bank Passbook' => $data->bank_passbook,
        'Udyam Certificate' => $data->udyam_doc,
        'GST Document' => $data->gst_doc_url ?? null,
        'Company PAN' => $data->company_pan_doc ?? null,
        'COI Document' => $data->coi_doc ?? null,
        'MOA Document' => $data->moa_doc ?? null,
        'COA Document' => $data->coa_doc ?? null,
    ];

    // ✅ Step 1: Main PDF
    $mainPdfPath = storage_path("app/main_$id.pdf");

    Pdf::loadView('admin.eKyc.pdf', compact('data'))->save($mainPdfPath);

    // ✅ Step 2: Merge
    $finalPdf = new Fpdi();

    $pageCount = $finalPdf->setSourceFile($mainPdfPath);

    for ($i = 1; $i <= $pageCount; $i++) {
        $tpl = $finalPdf->importPage($i);
        $size = $finalPdf->getTemplateSize($tpl);

        $finalPdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $finalPdf->useTemplate($tpl);
    }

    // ✅ Step 3: Documents loop
    foreach ($documents as $title => $docUrl) {

        if (!$docUrl) continue;

        try {
            $response = Http::timeout(20)->get($docUrl);

            if (!$response->successful()) continue;

            $contentType = $response->header('Content-Type');

            // ================= PDF =================
            if (str_contains($contentType, 'pdf')) {

                $tempPath = storage_path('app/temp_' . uniqid() . '.pdf');
                file_put_contents($tempPath, $response->body());

                $pageCount = $finalPdf->setSourceFile($tempPath);

                for ($i = 1; $i <= $pageCount; $i++) {

                    $tpl = $finalPdf->importPage($i);
                    $size = $finalPdf->getTemplateSize($tpl);

                    $finalPdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $finalPdf->useTemplate($tpl);

                    $finalPdf->SetFont('Arial', 'B', 10);
                    $finalPdf->SetXY(10, 10);
                    $finalPdf->Cell(0, 5, $title);
                }

                @unlink($tempPath);
            }

            // ================= IMAGE =================
            elseif (
                str_contains($contentType, 'jpeg') ||
                str_contains($contentType, 'jpg') ||
                str_contains($contentType, 'png') ||
                str_contains($contentType, 'webp')
            ) {

                $tempImage = storage_path('app/temp_' . uniqid() . '.jpg');

                // webp convert
                if (str_contains($contentType, 'webp')) {
                    $img = imagecreatefromstring($response->body());
                    imagejpeg($img, $tempImage, 90);
                } else {
                    file_put_contents($tempImage, $response->body());
                }

                $finalPdf->AddPage();

                $pageWidth = $finalPdf->GetPageWidth() - 20;

                $finalPdf->Image($tempImage, 10, 25, $pageWidth);

                // Title
                $finalPdf->SetFont('Arial', 'B', 12);
                $finalPdf->SetXY(10, 10);
                $finalPdf->Cell(0, 5, $title);

                @unlink($tempImage);
            }

        } catch (\Exception $e) {
            continue;
        }
    }

    // ✅ Final output
    $finalPath = storage_path("app/$fileName");
    $finalPdf->Output($finalPath, 'F');

    return response()->download($finalPath)->deleteFileAfterSend(true);
}
}
