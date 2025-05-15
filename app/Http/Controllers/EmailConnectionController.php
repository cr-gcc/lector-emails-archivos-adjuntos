<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Webklex\IMAP\Facades\Client;
use Smalot\PdfParser\Parser;
use App\Libraries\EmailOperations;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class EmailConnectionController extends Controller
{
    public function connection_test() {
        try {
            $client = Client::account();
            $client->connect();
            return response()->json([
                'message' => 'Conexión exitosa'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function email_info() {
        $subject_email_plan = config('emailkeyworks.subject_email_plan');
        $subject = EmailOperations::normalizeText($subject_email_plan);
        $info = ['message' => "No se encontraron mensajes con el asunto requerido."];
        $message_info = [];
        $status = 400;
        //  Conexion con el cliente de correo
        $client = Client::account();
        $client->connect();
        $folder = $client->getFolder('INBOX');
        $messages = $folder->query()->all()->get();
        $client->disconnect();
        //  No. de correos
        if (count($messages)) {
            foreach ($messages as $message) {
                //  Normalizacion de texto para las comparaciones
                $clean_tmp_subject = iconv_mime_decode($message->getSubject(), 0, "UTF-8");
                $clean_tmp_subject = EmailOperations::normalizeText($clean_tmp_subject);
                //  Coincidencias
                if (str_contains($clean_tmp_subject, $subject)) {
                    $body = $message->getTextBody();
                    $clean_body = preg_replace('/\s+/', ' ', $body);
                    $start = stripos($clean_body, 'plan de trabajo del mtro');
                    if ($start !== false) {
                        $fragment = substr($clean_body, $start, 300);
                        $fragment = mb_convert_encoding($fragment, 'UTF-8', 'UTF-8');
                        $fragment = str_replace(['“', '”', '‘', '’'], '"', $fragment); 
                        Log::info("Fragmento para inspección: " . $fragment);
                        $pattern_linea = '/plan de trabajo del mtr[oa],\s*([^,]+),\s*([^,]+),\s*m[oó]dulo\s+([IVXLCDM]+)\s+"([^"]+)"/i';


                        if (preg_match($pattern_linea, $fragment, $matches)) {
                            Log::info("coincidencias");
                            $nombre = trim($matches[1]);
                            $diplomado = trim($matches[2]);
                            $grupo = trim($matches[3]);
                            $modulo = trim($matches[4]);

                            $message_info[] = [
                                'profesor' => $nombre,
                                'diplomado' => $diplomado,
                                'grupo' => $grupo,
                                'modulo' => $modulo,
                            ];
                        }
                    }
                }
            }

            if (count($message_info)){
                $info = [
                    'data' => $message_info,
                    'message' => "Información encontrada."
                ];
                $status = 200;
            }
        }
        
        return response()->json($info, $status);
    }
    
    public function email_pdf_letters(){
        $subject_email_letter = config('emailkeyworks.subject_email_letter');
        $subject_pdf_letter = config('emailkeyworks.subject_pdf_letter');
        $subject = EmailOperations::normalizeText($subject_email_letter);
        $pdf_name = EmailOperations::normalizeText($subject_pdf_letter);
        $pdf_extension = 'pdf';
        $save_path = storage_path('app/public/');
        $success_flag = 0;
        try {
            //  Conexion con el servidor de gmail mediante la configuracion de .env
            $client = Client::account();
            $client->connect();
            //  Obtencion de los email
            $folder = $client->getFolder('INBOX');
            $messages = $folder->query()
                ->since(now()->subDays(7))
                ->get();
            
            foreach ($messages as $message) {
                //  Normalizacion de texto para las comparaciones
                $clean_tmp_subject = iconv_mime_decode($message->getSubject(), 0, "UTF-8");
                $clean_tmp_subject = EmailOperations::normalizeText($clean_tmp_subject);

                if (str_contains($clean_tmp_subject, $subject)) {
                    foreach ($message->getAttachments() as $attachment) {
                        $filename = $attachment->getName();
                        //  Comparacion de nombres de los pdf
                        $clean_filename = EmailOperations::normalizeText($filename);
                        $extension = pathinfo($clean_filename, PATHINFO_EXTENSION);
                        //  Depuracion de extenciones erroneas
                        if (strpos($extension, ' ') !== false) {
                            $extension = str_replace(' ', '', $extension);
                            $filename = str_replace('pd f', $extension, $filename);
                        }
                        
                        if ($extension==$pdf_extension){
                            //  Guardado de archivo
                            if (str_contains($clean_filename, $pdf_name)) {
                                $attachment->save($save_path, $filename);
                                $full_path = $save_path . '/' . $filename;
                                //  PDF a TXT
                                $parser = new Parser();
                                $pdf = $parser->parseFile($full_path);
                                $text = $pdf->getText();
                                //  Asesor(a)
                                $asesor = EmailOperations::getProfessor($text);
                                //  Modulo
                                $modulo_entero = EmailOperations::getModuleNumber($text);
                                //  Diplomado
                                $diplomado = EmailOperations::getDiploma($text);
                                //  Extraer fechas de inicio y fin
                                $dates = EmailOperations::getDates($text);
                                $inicio = $dates[0];
                                $fin = $dates[1];
                                // Mostrar resultados
                                Log::info("{$diplomado} {$modulo_entero} {$asesor} {$inicio} {$fin}");
                                $success_flag+=1;
                                //$message->setFlag('Seen');
                            }
                        }
                    }
                }
            }
            //  Cerramos conexion
            $client->disconnect();

            if ($success_flag) {
                return response()->json([
                    'message' => "Se agregaron {$success_flag} registro(s)"
                ], 200);    
            }
            else {
                return response()->json([
                    'message' => 'No se encontró un correo o archivo PDF que coincida.'
                ], 404);    
            }
            
        } 
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Error de conexión: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function delete_pdf(){
        $folder = storage_path('app/public');
        $deletedFiles = 0;

        foreach (glob($folder . '/*.pdf') as $file) {
            if (unlink($file)) {
                $deletedFiles++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deletedFiles} archivo(s) PDF temporales."
        ]);
    }
    
}
