<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use App\Models\Grammar;
use App\Models\Technology;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WordController extends Controller
{
    public function index()
    {
        //記事の生成
        $wordList = Word::orderBy('id','asc')->take(50)->pluck('name')->toArray();
        $grammarList = Grammar::pluck('name')->toArray();
        $selectedGrammar = Grammar::inRandomOrder()->first()->name;
        $selectedTechnology = Technology::inRandomOrder()->first()->name;

        $prompt = "Create English articles suitable for English learning materials. The article must:
            - The English sentences used in this article use grammar (". $selectedGrammar .")
            - Only use words and grammar suitable for Japanese middle school level or below.
            - Maximum length of article is 500 characters.
            - The theme of this article is ". $selectedTechnology ."
            - Utilize 10 words from the given list of 50words.
            Word list: " . implode(", ", $wordList) . "
            
            結果は、json形式で返してください。必要なデータは下記の2点です。
            1.英語の記事
            2.使用した英単語10個

            json形式の期待する構造
            \"article\": \"値\",
            \"selectedWords\": \"値\"
            \"selectedWords\": [\"word1\", \"word2\", ...]";
        
        Log::debug("log",["prompt"=>$prompt]);
        $url = "https://api.openai.com/v1/chat/completions";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
        ])->withOptions(['timeout' => 60])->post($url, [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7
        ]);

        Log::info('response:', ["response"=>$response->json()]);

        $output = $response->json();
        Log::info('choices text:', ["text"=>$output['choices'][0]['message']['content']]);

        $json = json_decode($output['choices'][0]['message']['content']  , true  );
        $articleContent = $x1['article'];
        $selectedWords = $x1['selectedWords'];
        Log::info('selectedWords:', ["selectedWords"=>$selectedWords]);

        //記事に付随するデータの取得
        $prompt2 = "英語教材用に英語記事に対する、下記2つのデータが必要です。json 形式で返してください。返却後、json_decodeを実行します。
        
        記事（英語）
        ".$articleContent."

        grammar_explanation
        英語の記事を丁寧に精読します。そのために、英語の記事に対する文法解説をお願いします。一つの英文ごとに①使用されている文法 ②その文法の解説 を日本語で提供してください。
        
        jp_translation
        英語の記事の日本語訳を作成してください。
        
        json形式の期待する構造
            \"article\": \"$articleContent\",
            \"grammar_explanation\": \"値\",
            \"jp_translation\": \"値\"
        ";
        
        Log::debug("log",["prompt"=>$prompt2]);
        $url2 = "https://api.openai.com/v1/chat/completions";

        $response2 = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
        ])->withOptions(['timeout' => 60])->post($url2, [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt2]
            ],
            'temperature' => 0.7
        ]);

        Log::info('response:', ["response"=>$response2->json()]);

        $output2 = $response2->json();
        Log::info('choices text:', ["text"=>$output2['choices'][0]['message']['content']]);

        $x2 = json_decode($output2['choices'][0]['message']['content']  , true  );
        Log::info('x2:', ["x2"=>$x2]);

        $grammarExplanation = $x2['grammar_explanation'];
        $jpTranslation = $x2['jp_translation'];

        return response()->json([
            'wordList' => $wordList,
            'selectedWords' => $selectedWords,
            'article' => $articleContent,
            'selectedGrammar' => $selectedGrammar,
            'selectedTechnology' => $selectedTechnology,
            'grammarExplanation' => $grammarExplanation,
            'jpTranslation' => $jpTranslation
        ]);
    }
}
