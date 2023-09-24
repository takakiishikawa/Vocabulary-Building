<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WordController extends Controller
{
    /*
    public function index(){
        $words = Word::orderBy('id','asc')->take(10)->pluck('name');
        Log::debug("log",['words'=> $words]);
        return response()->json($words);
    }
    */

    public function index()
    {
        $words = Word::orderBy('id','desc')->take(100)->pluck('name')->toArray();

        $prompt = "Please return the following in json format
        Based on the 100 words provided, create an English paragraph suitable for English learning materials. The paragraph should:
            - Only use words and grammar appropriate for middle school level or below.
            - related to web technology that focus on a specific technology.
            - Incorporate the grammar structure [have to].
            - Use 10 words out of 100 words.
            - Be coherent and revolve around a single theme related to web technology.Make sure the keys have the same name
            Words: " . implode(", ", $words) . "Please return the following three points. \n article_content\n web_technology_name\n grammar_explanation";

        Log::debug("log",["prompt"=>$prompt]);
        $url = "https://api.openai.com/v1/chat/completions";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
        ])->post($url, [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7
        ]);

        Log::info('response:', ["response"=>$response->json()]);

        $output = $response->json();
        Log::info('choices text:', ["text"=>$output['choices'][0]['message']['content']]);

        $x = json_decode($output['choices'][0]['message']['content']  , true  );

        $article = $x['article_content'];
        $webTechName = $x['web_technology_name'];
        $grammarExplanation = $x['grammar_explanation'];
    
        return response()->json([
            'wordList' => $words,
            'article' => $article,
            'webTechName' => $webTechName,
            'grammarExplanation' => $grammarExplanation
        ]);
    }
}
