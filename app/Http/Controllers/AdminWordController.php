<?php

namespace App\Http\Controllers;

use App\Models\Word;
use App\Models\WordTest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class AdminWordController extends Controller
{
    public function generate(){
        //記事の生成
        $start_time = microtime(true);
        $wordList = Word::whereNull('parse')->orderBy('id','asc')->take(20)->pluck('name')->toArray();
        Log::info('wordList:', ["wordList"=>$wordList]);

        $prompt = "英語教材用に、以下の情報を持つ英単語20個のデータが必要です。json 形式のみ返答お願いします。
        
        1. 英単語20個のリスト". implode(", ", $wordList) ."
        2. core_meaning: 英語初学者向けに、その英単語のコアとなる、本質的な意味を日本語の文章で詳しく解説してください
        3. imagery: 英語初学者向けに、その英単語が持つイメージをあなたの豊富な語彙を駆使して、日本語でわかりやすく私に伝えてください。例 恥ずかしがったり、緊張したりしたときの顔が赤くなる人
        4. word_jp: 英単語の基本的な日本語訳を1単語で記載 例 boxならば、箱
        5. parse: その英単語の品詞を英単語1単語で記載 例 名詞ならば、norn

        jsonの形式は以下の通りです:
            \"word1\": {\"core_meaning\": \"値\", \"imagery\": \"値\", \"word_jp\": \"値\", \"parse\": \"値\"}
            \"word2\": {\"core_meaning\": \"値\", \"imagery\": \"値\", \"word_jp\": \"値\", \"parse\": \"値\"}
            \"word...\": {\"core_meaning\": \"値\", \"imagery\": \"値\", \"word_jp\": \"値\", \"parse\": \"値\"}
        ";
        
        Log::debug("log",["prompt"=>$prompt]);
        $url = "https://api.openai.com/v1/chat/completions";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
        ])->withOptions(['timeout' => 200])->post($url, [
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

        $end_time = microtime(true);
        $time = floor($end_time - $start_time);

        //プロセス処理記載




        //WordTestへ保存
        foreach($wordGptData as $wordName => $wordDetail){
            $wordTest = new WordTest();
            $wordTest->name = $wordName;
            $wordTest->core_meaning = $wordDetail['core_meaning'];
            $wordTest->imagery = $wordDetail['imagery'];
            $wordTest->word_jp = $wordDetail['word_jp'];
            $wordTest->parse = $wordDetail['parse'];
            $wordTest->save_flag = 0;
            $wordTest->save();
        }

        //save_flagが0のものを取得
        $wordTestList = WordTest::where('save_flag', 0)->get();
        Log::info('wordTestList:', ["wordTestList"=>$wordTestList]);

        return response()->json([
            'wordTestList' => $wordTestList,
            'message' => 'Word created successfully! response time is ' . $time . ' seconds.',
        ]);
    }
    
    public function list(){
        $wordTestList = WordTest::where('save_flag', 0)->get();
        Log::info('wordTestList:', ["wordTestList"=>$wordTestList]);

        return response()->json([
            'wordTestList' => $wordTestList,
        ]);
    }
        
    public function save(){
        //wordTestListのsave_flagが0である英単語を取得
        //wordTableに登録
        //登録後、wordTestListのsave_flagを1に変更する処理
        $wordTestList = WordTest::where('save_flag', 0)->get();
        Log::info('wordTestList:', ["wordTestList"=>$wordTestList]);
        foreach($wordTestList as $wordTest){
            $word = Word::where('name', $wordTest->name)->first();
            if($word){
                $word->core_meaning = $wordTest->core_meaning;
                $word->imagery = $wordTest->imagery;
                $word->word_jp = $wordTest->word_jp;
                $word->parse = $wordTest->parse;
                $word->save();
            }
            $wordTest->save_flag = 1;
            $wordTest->save();
        }
        return response()->json([
            'message' => 'Word saved successfully!',
        ]);
    }

    public function count(){
        $wordCount = Word::whereNotNull('parse')->count();
        Log::info('wordCount:', ["wordCount"=>$wordCount]);

        return response()->json([
            'wordCount' => $wordCount,
        ]);
    }
}