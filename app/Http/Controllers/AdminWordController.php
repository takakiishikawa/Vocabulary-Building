<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class AdminWordController extends Controller
{
    public function index(){
        //記事の生成
        $wordList = Word::orderBy('id','desc')->take(20)->pluck('name')->toArray();

        $prompt = "英語教材用に、以下の情報を持つ英単語20個のデータが必要です。json 形式で返答お願いします。
        
        1. 英単語20個のリスト". implode(", ", $wordList) ."
        2. core_meaning: 英語初学者向けに、その英単語のコアとなる、本質的な意味を日本語の文章で詳しく解説してください
        3. imegenary: 英語初学者向けに、その英単語が持つイメージをあなたの豊富な語彙を駆使して、日本語でわかりやすく私に伝えてください。例 恥ずかしがったり、緊張したりしたときの顔が赤くなる人
        4. word_jp: 英単語の基本的な(コアとなる)日本語訳（一語） 例 boxならば、箱
        5. parse: その英単語の品詞（英語一語）

        期待するjson構造:
            \"word1\": {\"core_meaning\": \"値\", \"imegenary\": \"値\", \"word_jp\": \"値\", \"parse\": \"値\"}
            \"word2\": {\"core_meaning\": \"値\", \"imegenary\": \"値\", \"word_jp\": \"値\", \"parse\": \"値\"}
            \"word...\": {\"core_meaning\": \"値\", \"imegenary\": \"値\", \"word_jp\": \"値\", \"parse\": \"値\"}
        ";
        
        Log::debug("log",["prompt"=>$prompt]);
        $url = "https://api.openai.com/v1/chat/completions";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
        ])->withOptions(['timeout' => 120])->post($url, [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7
        ]);

        Log::info('response:', ["response"=>$response->json()]);

        $output = $response->json();
        Log::info('choices text:', ["text"=>$output['choices'][0]['message']['content']]);

        $wordGptData = json_decode($output['choices'][0]['message']['content']  , true  );
        Log::info('wordGptData:', ["wordGptData"=>$wordGptData]);

        return response()->json([
            'wordGptData' => $wordGptData
        ]);
    }
}
