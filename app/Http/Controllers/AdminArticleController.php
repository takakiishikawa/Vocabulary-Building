<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use App\Models\Article;
use App\Models\Grammar;
use App\Models\Technology;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\ArticleTestWordGrouping;
use App\Models\ArticleTestGenerate;

class AdminArticleController extends Controller
{
    public function index()
    {
        //記事の生成
        $start_time = microtime(true);

        // ArticleTestWordGroupingに保存する
        $groupCounter = 1;
        $wordCount = 50;
        $groupCount = 3;
        $totalCount = $wordCount * $groupCount;
        $wordList = Word::whereNull('generated_id')->orderBy('id', 'asc')->take($totalCount)->pluck('name')->toArray();

        for ($i = 0; $i < $groupCount; $i++) {
            $slice = array_slice($wordList, $i * $wordCount, $wordCount);
            foreach ($slice as $word) {
                $wordGrouping = new ArticleTestWordGrouping();
                $wordGrouping->word_test_group = $groupCounter;
                $wordGrouping->name = $word->name;
                $wordGrouping->article_test_id = 0;
                $wordGrouping->save_flag = 0;
                $wordGrouping->save();
            }
            $groupCounter++;
        }

        // ArticleTestGenerateに保存する
        $articleTestGenerate = new ArticleTestGenerate();
        $articleTestGenerate->save_flag = 0;

        //以下、プロセスを分けてAPIをコールする
        

        $selectedGrammar = Grammar::inRandomOrder()->first()->name;
        $selectedTechnology = Technology::inRandomOrder()->first()->name;

        $prompt = "以下の指定に従って、自然な英語の記事を作成してください。
        - 使用する英語の文は、特定の文法（" . $selectedGrammar . "）を使用してください。
        - 使用する単語や文法は、日本の中学生レベルまたはそれ以下で理解可能なものに限定してください。
        - 以下の提供される50個の英単語の中から厳密に10個を選び、記事内で使用してください。選ばれた10の単語はすべてこの50の単語の中に存在している必要があります。使用可能な単語リスト: " . implode(", ", $wordList) . "。
        - 記事の文字数は500文字以内にしてください。
        - 記事のテーマは " . $selectedTechnology . " としてください。
    
        下記をjson形式で返してください。
        article: 作成した英語の記事内容
    
        jsonの形式は以下の通りです:
        \"article\": \"値\" ";
        
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
        Log::info('full output',['output'=>$output]);

        $x1 = json_decode($output['choices'][0]['message']['content']  , true  );
        Log::info('x1:', ["x1"=>$x1]);
        $articleContent = $x1['article'];
        $selectedWords = $x1['selectedWords'];
        Log::info('selectedWords:', ["selectedWords"=>$selectedWords]);

        //記事に付随するデータの取得
        $prompt2 = "英語教材用に英語記事に対する、下記2つのデータが必要です。json 形式のみ返答お願いします。
        
        記事（英語）
        ".$articleContent."

        grammar_explanation
        ユーザーは、英語の記事を精読します。ユーザーのために、英語の記事に対する文法解説をお願いします。使用されている文法とその文法の解説 を日本語で提供してください。1行ずつ詳細に解説してみてください。回答結果は、grammar_explanationの値にしてください。下位の階層を作成することは禁止です。
        
        article_jp
        英語の記事の日本語訳を作成してください。
        
        jsonの形式は以下の通りです:
            \"article\": \"$articleContent\",
            \"grammar_explanation\": \"値\",
            \"article_jp\": \"値\"
        ";
        
        Log::debug("log",["prompt"=>$prompt2]);
        $url2 = "https://api.openai.com/v1/chat/completions";

        $response2 = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
        ])->withOptions(['timeout' => 120])->post($url2, [
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

        $end_time = microtime(true);
        $time = floor($end_time - $start_time);

        return response()->json([
            'wordList' => $wordList,
            'selectedWords' => $selectedWords,
            'article' => $articleContent,
            'selectedGrammar' => $selectedGrammar,
            'selectedTechnology' => $selectedTechnology,
            'grammarExplanation' => $grammarExplanation,
            'article_jp' => $article_jp,
            'message' => 'Article created successfully! response time is ' . $time . ' seconds.'
        ]);
    }

    public function save(Request $request){
        try{
            $articleData = $request->all();
            $articleModel = new Article();
            Log::info('articleData:', ["articleData"=>$articleData]);

            //selectedWordsにあるword10個あることをチェック
            if(count($articleData['selectedWords']) != 10){
                return response()->json([
                    'error' => 'selectedWords must be 10 words.'
                ], 500);
            }
            
            //selectedWordsには、wordが10個入っている
            //Word tableには、12000の英単語が入っている
            //そのword10個は、Word tableのnameと全て一致することをチェック
            $wordList = Word::whereNull('generated_id')->orderBy('id','asc')->take(50)->pluck('name')->toArray();
            Log::info('wordList:', ["wordList"=>$wordList]);
            $selectedWords = $articleData['selectedWords'];
            Log::info('selectedWords:', ["selectedWords"=>$selectedWords]);
            $diff = array_diff($selectedWords, $wordList);
            Log::info('diff:', ["diff"=>$diff]);
            if($diff){
                return response()->json([
                    'error' => 'selectedWords must be in wordList.'
                ], 500);
            }

            //selectedWordsと一致するwordを全て取り出す
            //wordのgenerated_idがnullである場合、処理を終了し、エラーを返す
            foreach($selectedWords as $selectedWord){
                $wordModel = Word::where('name', $selectedWord)->first();
                if($wordModel->generated_id != null){
                    return response()->json([
                        'error' => 'generated_id must be null.'
                    ], 500);
                }
            }

            $grammar = Grammar::where('name', $articleData['selectedGrammar'])->first();
            $technology = Technology::where('name', $articleData['selectedTechnology'])->first();

            $articleModel->grammar_id = $grammar->id;
            $articleModel->technology_id = $technology->id;
            $articleModel->article = $articleData['article'];
            $articleModel->article_jp = $articleData['article_jp'];
            $articleModel->grammar_explanation = $articleData['grammarExplanation'];
            $articleModel->save();

            //wordのgenerated_idにarticleのidを入れる処理を記述する
            foreach($selectedWords as $selectedWord){
                $wordModel = Word::where('name', $selectedWord)->first();
                Log::info('wordModel:', ["wordModel"=>$wordModel]);
                $wordModel->generated_id = $articleModel->id;
                Log::info('articleModel->id:', ["articleModel->id"=>$articleModel->id]);
                Log::info('wordModel->generated_id:', ["wordModel->generated_id"=>$wordModel->generated_id]);
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
