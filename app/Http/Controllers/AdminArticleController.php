<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use App\Models\Article;
use App\Models\Grammar;
use App\Models\Technology;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AdminArticleController extends Controller
{
    public function index()
    {
        //記事の生成
        $wordList = Word::whereNull('generated_id')->orderBy('id','asc')->take(100)->pluck('name')->toArray();
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

            json形式の構造
            \"article\": \"値\",
            \"selectedWords\": [\"word1\", \"word2\", ...]";
        
        Log::debug("log",["prompt"=>$prompt]);
        $url = "https://api.openai.com/v1/chat/completions";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
        ])->withOptions(['timeout' => 60])->post($url, [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7
        ]);

        Log::info('response:', ["response"=>$response->json()]);

        $output = $response->json();
        Log::info('choices text:', ["text"=>$output['choices'][0]['message']['content']]);

        $x1 = json_decode($output['choices'][0]['message']['content']  , true  );
        $articleContent = $x1['article'];
        $selectedWords = $x1['selectedWords'];
        Log::info('selectedWords:', ["selectedWords"=>$selectedWords]);

        //記事に付随するデータの取得
        $prompt2 = "英語教材用に英語記事に対する、下記2つのデータが必要です。json 形式で返答お願いします。
        
        記事（英語）
        ".$articleContent."

        grammar_explanation
        ユーザーは、英語の記事を精読します。ユーザーのために、英語の記事に対する文法解説をお願いします。使用されている文法とその文法の解説 を日本語で提供してください。1行ずつ詳細に解説してみてください。回答結果は、grammar_explanationの値にしてください。下位の階層を作成することは禁止です。
        
        article_jp
        英語の記事の日本語訳を作成してください。
        
        json形式の構造
            \"article\": \"$articleContent\",
            \"grammar_explanation\": \"値\",
            \"article_jp\": \"値\"
        ";
        
        Log::debug("log",["prompt"=>$prompt2]);
        $url2 = "https://api.openai.com/v1/chat/completions";

        $response2 = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
        ])->withOptions(['timeout' => 60])->post($url2, [
            'model' => 'gpt-4',
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
        Log::info('grammarExplanation:', ["grammarExplanation"=>$grammarExplanation]);
        $article_jp = $x2['article_jp'];

        return response()->json([
            'wordList' => $wordList,
            'selectedWords' => $selectedWords,
            'article' => $articleContent,
            'selectedGrammar' => $selectedGrammar,
            'selectedTechnology' => $selectedTechnology,
            'grammarExplanation' => $grammarExplanation,
            'article_jp' => $article_jp
        ]);
    }

    public function save(Request $request){
        try{
            $articleData = $request->all();
            Log::info('articleData:', ["articleData"=>$articleData]);

            $grammar = Grammar::where('name', $articleData['selectedGrammar'])->first();
            $technology = Technology::where('name', $articleData['selectedTechnology'])->first();

            $articleModel = new Article();
            $articleModel->grammar_id = $grammar->id;
            $articleModel->technology_id = $technology->id;
            $articleModel->article = $articleData['article'];
            $articleModel->article_jp = $articleData['article_jp'];
            $articleModel->grammar_explanation = $articleData['grammarExplanation'];
            $articleModel->save();

            //Wordテーブルには12000のワードが入っている
            //selectedWordには20の英単語が入っており、その英単語は、Word tableの中からランダムに選ばれたもの
            //選択された、つまり、selectedWordにあるWordはWord tableのgenerated_idに印をつけたい
            //Word tableのgenerated_idに印をつけると、Word tableの中から、selectedWordにないWordのみを抽出することができる
            //下記に、Word tableのgenerated_idに印をつける処理を記述する
            $selectedWords = $articleData['selectedWords'];
            Log::info('selectedWords:', ["selectedWords"=>$selectedWords]);

            foreach($selectedWords as $selectedWord){
                $wordModel = Word::where('name', $selectedWord)->first();
                $wordModel->generated_id = $articleModel->id;
                $wordModel->save();
            }

            return response()->json([
                'message' => 'Article created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }
}
