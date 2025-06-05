<?php

namespace App\Http\Controllers;

use App\Factories\DigitalizesFactory;
use App\Http\Requests\DigitalizerRequest;
use App\Models\Digitalization;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use function auth;
use function json_decode;

class DigitalizerController extends Controller
{
    public function digitalizes(DigitalizerRequest $request)
    {
        $file = $request->file('file');

        $imageUrl = '';
        $originalFilePath = null;
        $tempDisplayPath = 'temp_digitalizations/' . $file->hashName();

        Storage::disk('public')->put($tempDisplayPath, file_get_contents($file->getRealPath()));
        $imageUrl = Storage::url($tempDisplayPath);



        $digitalizer = DigitalizesFactory::make();
        $response = '';

        try {
            $parsedContent = $digitalizer->returnJson($file);

            if ($parsedContent instanceof \Illuminate\Http\RedirectResponse) {
                return $parsedContent;
            }

//            $parsedContent = [
//                "page" => [
//                    "headerTitle" => null,
//        "title" => "Agn\u00e8s Varda",
//        "subtitle" => "... da Venezia",
//        "paragraphs" => [
//                        "C & F\tQual \u00e8 il suo rapporto col naturalismo? Abbiamo l'impressione\n\tche lei tenda ad opporsi alla realt\u00e0.",
//                        "Varda\tMa io non mi oppongo affatto alla realt\u00e0, al contrario, mi sot-\n\tmetto ad essa. E di questo sono assolutamente cosciente. La\n\trealt\u00e0 \u00e8 per me la fonte e il nutrimento dell'ispirazione. \u00c8\n\tproprio il contrario di quello che dite voi.\n\tLo scrittore del mio film si basa sulla realt\u00e0 che vede attorno\n\ta s\u00e9, anche su quella bizzarra la realt\u00e0 di un cavallo che\n\tparla o quella, complicata, della sua immaginazione, e alla\n\tfine approda a una realt\u00e0: un libro, un figlio per sua moglie.\n\tLa realt\u00e0 prende senso dopo la trasformazione.",
//                        "C & F\tTuttavia questa realt\u00e0 \u00e8 portata sullo schermo con una tecnica\n\tnon realistica.",
//                        "Varda\tPu\u00f2 darsi. Per me, comunque, l'importante \u00e8 di andare alla\n\tvita, alla forza vitale, ai legami vitali, anche nel disordine,\n\tanche nell'errore. Il mio \u00e8 un realismo al livello delle associa-\n\tzioni e non a quello dell'esistenza, forse.",
//                        "C & F\tIl suo \u00e8 un film sull'immaginazione. Perch\u00e9, per\u00f2, ha preferito\n\tscegliere come protagonista un personaggio dall'immaginazione\n\tse non mediocre, normale?",
//                        "Varda\tSe lo scrittore del mio film fosse uno come Joyce, il film sarebbe\n\tstato molto pi\u00f9 difficile da farsi: io non ho il talento di Joyce\n\tper comprendere come si \u00e8 sviluppata la sua immaginazione.\n\tQuesto \u00e8 un esempio limite, naturalmente.\n\tUn editore mi ha detto una cosa che mi ha fatto particolar-\n\tmente piacere, mi ha detto che la cosa bella del film era che\n\tsi trattava di un'immaginazione alla portata di tutti. \u00c8 proprio\n\tquesto il mio sentimento. Una cosa che mi interessa molto nel\n\tcinema, a proposito di linguaggio, sono le immagini che posso-\n\tno essere comprese ovunque e da chiunque: non le immagini\n\tdella vita reale ma le immagini profonde. Per esempio, quelle\n\tdel mare che aprono il film, o quelle virate in rosso, quando lo\n\tscrittore si arrabbia, o, ancora, quelle degli animali che par-\n\tlano: sono immagini quasi semplicistiche che per\u00f2 corrispondo-\n\tno a un sentimento autentico e profondamente radicato in\n\tnoi; si dice \u00ab vedere rosso \u00bb, oppure si dice che se si potesse\n\tparlare con gli animali ci si capirebbe meglio. Le favole sono\n\tsempre state piene di animali parlanti. Quello a cui tendo \u00e8\n\tutilizzare un vocabolario di immagini significanti. Io credo che\n\tutilizzando immagini del genere, anche se a volte sono troppo\n\tsemplici, si finisce col dare forza al film. Nozioni come quella\n\tdel bene e del male, che utilizzo nella partita a scacchi, esisto-\n\tno nella realt\u00e0, fra la gente. Il senso di fatalismo che permea\n\til gioco esiste. Se ne pu\u00f2 dire quello che si vuole, che \u00e8 anacro-\n\tnistico, superato ecc., ma esiste profondamente negli uomini. \u00c8\n\tquesto che mi interessa.\n\tLa cosa importante per me \u00e8 che l'immaginazione di questo\n\tscrittore prenda contatto con le cose della realt\u00e0 quotidiana\n\tpasseggiate, incontri dalle quali poi comincia a nascere qual-\n\tcos'altro, che pu\u00f2 essere originale e profondo o, al contrario,\n\tun po' mediocre, come un romanzo di fantascienza ordinario.\n\tPer me \u00e8 indifferente, anzi, direi che mi piace questo scrittore\n\tnon troppo moderno che lavora ancora come un contadino. Ci\u00f2\n\tche mi interessa della creazione \u00e8 la nascita, la fonte dell'ispi-\n\trazione e non la sostanza del romanzo.\n\tNon bisogna d'altra parte dimenticare che il film racconta la\n\tstoria di due creazioni: quella della moglie dello scrittore, com-\n\tpletamente sola nell'attendere il figlio (solitudine accentuata\n\tdal suo mutismo), e quella di lui, completamente solo nel lavo-\n\trare al suo romanzo. La conclusione del film \u00e8 la nascita del\n\tbambino, ma l'idea profonda \u00e8 quella della donna muta che\n\tpu\u00f2 finalmente parlare. Ho realizzato quest'idea a un secondo\n\tlivello perch\u00e9 \u00e8 pi\u00f9 facile avere un'immagine significante come\n\tquella della gravidanza. Ci sono due linee parallele che scor-\n\trono lungo il film: lei che cerca di parlare e lui che cerca di\n\tscrivere."
//                    ],
//        "pageNumber" => "127"
//    ]
//];
            $formatted = $digitalizer->formatJsonToHTMLandPlainText($parsedContent);
            $pageData = $formatted['pageData'];
            $plainText = $formatted['plainText'];


        } catch (Exception $e) {
            Log::error('Erro inesperado no DigitalizerController: ' . $e->getMessage());
            return back()->with(['message' => 'Ocorreu um erro inesperado: ' . $e->getMessage(), 'type' => 'error']);
        }

        $digitalization = null;
        if (auth()->check()) {
            $user = auth()->user();
            $originalFilePath = $file->storeAs('digitalizations', $file->hashName(), 'public');

            $jsonOutputString = json_encode($parsedContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $jsonFileName = $file->hashName() . '.json';
            $transcriptionFilePath = 'digitalizations/json_outputs/' . $jsonFileName;
            Storage::disk('public')->put($transcriptionFilePath, $jsonOutputString);


            $digitalization = $user->digitalizations()->create([
                'original_file_path' => $originalFilePath,
                'transcription_file_path' => $transcriptionFilePath,
                'title' => $pageData['title'] ?? 'undefined title',
            ]);
            $imageUrl = Storage::url($originalFilePath);
        }

        return view('scan-result', compact('pageData', 'plainText', 'imageUrl', 'digitalization'));
    }

    public function downloadPDF(Digitalization $digitalization)
    {
        if (!auth()->user()->can('download', $digitalization)) {
            abort(403, 'Unauthorized');
        }

        try {
            $transcriptionFilePath = $digitalization->transcription_file_path;

            if (!Storage::exists($transcriptionFilePath)) {
                Log::warning('Arquivo de transcrição JSON não encontrado para PDF: ' . $transcriptionFilePath);
                abort(404, 'PDF não pode ser gerado: Arquivo de transcrição não encontrado.');
            }
            $jsonContent = Storage::get($transcriptionFilePath);

            $parsedContent = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Erro ao decodificar JSON do arquivo de transcrição para PDF: ' . json_last_error_msg());
                abort(500, 'PDF não pode ser gerado: Erro de leitura dos dados.');
            }

            $pageData = $parsedContent['page'] ?? [];

            $html = view('pdf.document_template', compact('pageData'))->render();

            $pdf = Pdf::loadHtml($html);

            $baseFileName = pathinfo($transcriptionFilePath, PATHINFO_FILENAME);
            $pdfFileName = str_replace('gemini_response_', 'documento_', $baseFileName) . '.pdf';


            return $pdf->download($pdfFileName);

        } catch (Exception $e) {
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            return back()->withErrors(['pdf_error' => 'Não foi possível gerar o PDF: ' . $e->getMessage()]);
        }
    }
}
