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
    public function generate()
    {
        //記事の生成
        $start_time = microtime(true);

        // ArticleTestWordGroupingに保存する
        $groupCounter = 1;
        $wordCount = 50;
        $groupCount = 1;
        $totalCount = $wordCount * $groupCount;
        $wordList = Word::whereNull('generated_id')->orderBy('id', 'asc')->take($totalCount)->pluck('name')->toArray();
        Log::info('wordList:', ["wordList"=>$wordList]);

        for ($i = 0; $i < $groupCount; $i++) {
            $slice = array_slice($wordList, $i * $wordCount, $wordCount);
            foreach ($slice as $word) {
                $wordGrouping = new ArticleTestWordGrouping();
                $wordGrouping->word_test_group = $groupCounter;
                $wordGrouping->name = $word;
                $wordGrouping->article_test_generate_id = 0;
                $wordGrouping->save_flag = 0;
                Log::info('wordGrouping:', ["wordGrouping"=>$wordGrouping]);
                $wordGrouping->save();
            }
            $groupCounter++;
        }

        //以下、プロセスを分けてAPIをコールする



        //aricleTestWordGroupingのaritcle_test_id毎にpropmtを作成する
        //nameとword_test_groupのみを取得する
        $articleTestWordGrouping = ArticleTestWordGrouping::where('save_flag', 0)->get(['name', 'word_test_group']);
        $articleTestWordGroupingGroupBy = $articleTestWordGrouping->groupBy('word_test_group');
        Log::info('articleTestWordGroupingGroupBy:', ["articleTestWordGroupingGroupBy"=>$articleTestWordGroupingGroupBy]);
        $articleTestWordGroupingGroupByArray = $articleTestWordGroupingGroupBy->toArray();
        Log::info('articleTestWordGroupingGroupByArray:', ["articleTestWordGroupingGroupByArray"=>$articleTestWordGroupingGroupByArray]);
        
        foreach($articleTestWordGroupingGroupByArray as $n){
            $selectedGrammar = Grammar::inRandomOrder()->first()->name;
            $selectedTechnology = Technology::inRandomOrder()->first()->name;
            
            Log::info('n:', ["n"=>$n]);
            //articleTestWordGroupingGroupByArrayのname50個を取得してプロンプトにWordListとして入れる
            $wordList = array_column($n, 'name');
            Log::info('wordList:', ["wordList"=>$wordList]);

            $prompt = "以下の条件に基づいて、自然な英語の記事を作成してください：
            - 文法: 使用する文は、特定の文法（" . $selectedGrammar . "）を使用してください。
            - レベル: 使用する単語や文法は、日本の中学生レベルまたはそれ以下で理解できるものにしてください。
            - 以下の提供される50個の英単語の中から厳密に10個を選び、記事内で使用してください。選ばれた10の単語はすべてこの50の単語の中に存在している必要があります。使用可能な単語リスト: " . implode(", ", $wordList) . "。
            - 文字数制限: 記事は500文字以内にしてください。
            - テーマ: 記事のテーマは " . $selectedTechnology . " としてください。

            作成した記事の内容を以下のJSON形式で返してください:
            {
            \"article\": \"[あなたの記事内容]\"
            }";
            
            Log::debug("log",["prompt"=>$prompt]);
            $url = "https://api.openai.com/v1/chat/completions";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
            ])->withOptions(['timeout' => 300])->post($url, [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7
            ]);

            Log::info('response:', ["response"=>$response->json()]);

            $output = $response->json();
            Log::info('full output',['output'=>$output]);
            Log::info('content', ["content"=>$output['choices'][0]['message']['content']]);

            $cleanedContent = preg_replace('/[\x00-\x1F\x7F]/u', '', $output['choices'][0]['message']['content']);
            $x1 = json_decode($cleanedContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error:', ["error" => json_last_error_msg()]);
            }
            Log::info('x1:', ["x1"=>$x1]);
            $article = $x1['article'];

            //$articleには、articleTestWordGroupingGroupByのnameが10個入っているか確認する
            //その10個を配列として取得する
            $articleWordArray = preg_split('/[\s,\.]+/', $article, -1, PREG_SPLIT_NO_EMPTY);
            Log::info('articleWordArray:', ["articleWordArray"=>$articleWordArray]);
            Log::info('wordList:', ["wordList"=>$wordList]);
            $articleArrayDiff = array_intersect($articleWordArray, $wordList);
            Log::info('articleArrayDiff:', ["articleArrayDiff"=>$articleArrayDiff]);

            //articleTestGenerateのレコードを新規作成し、各カラムを保存する
            $artcileTestGerate = new ArticleTestGenerate();
            $artcileTestGerate->grammar_id = Grammar::where('name', $selectedGrammar)->first()->id;
            $artcileTestGerate->technology_id = Technology::where('name', $selectedTechnology)->first()->id;
            $artcileTestGerate->article = $article;
            $artcileTestGerate->save_flag = 0;
            Log::info('artcileTestGerate:', ["artcileTestGerate"=>$artcileTestGerate]);
            $artcileTestGerate->save();

            foreach($articleArrayDiff as $articleArrayDiffWord){
                $articleTestWordGrouping = ArticleTestWordGrouping::where('name', $articleArrayDiffWord)->first();
                $articleTestWordGrouping->article_test_generate_id = $artcileTestGerate->id;
                $articleTestWordGrouping->save();
            }

            //articleArrayDiffと一致するwordtableのレコードを全て取得する
            //次に、そのレコードのfrequencyを全て取得する
            //次に、そのfrequencyの平均を取得する
            $wordFrequencyArray = [];
            foreach($articleArrayDiff as $articleArrayDiffWord){
                $wordModel = Word::where('name', $articleArrayDiffWord)->first();
                $wordFrequencyArray[] = $wordModel->frequency;
            }
            $wordFrequencyAverage = array_sum($wordFrequencyArray) / count($wordFrequencyArray);
            $artcileTestGerate->word_frequency_average = $wordFrequencyAverage;
            Log::info('artcileTestGerate:', ["artcileTestGerate"=>$artcileTestGerate]);
            $artcileTestGerate->save();
        }

        $articleTestWordGrouping->save_flag = 1;

        //articleTestGenerateでarticle_jpがnullのレコードを全て取得する
        $articleTestGenerateArray = ArticleTestGenerate::whereNull('article_jp')->get();
        Log::info('articleTestGenerateArray:', ["articleTestGenerateArray"=>$articleTestGenerateArray]);

        //articleTestGenerateArrayのarticleを取得する
        //そのarticleを使用し、プロンプトを実行する
        //実行結果をarticleTestGenerateのarticle_jpとgrammar_explanationに保存する
        foreach($articleTestGenerateArray as $articleTestGenerate){
            $article = $articleTestGenerate->article;
            $prompt2 = "英語教材用に英語記事に対する、下記2つのデータが必要です。json 形式のみ返答お願いします。
        
            記事（英語）
            ".$article."
    
            grammar_explanation
            ユーザーは、英語の記事を精読します。ユーザーのために、英語の記事に対する文法解説をお願いします。使用されている文法とその文法の解説 を日本語で提供してください。1行ずつ詳細に解説してみてください。回答結果は、grammar_explanationの値にしてください。下位の階層を作成することは禁止です。
    
            article_jp
            英語の記事の日本語訳を作成してください。
    
            jsonの形式は以下の通りです:
                \"article\": \"$article\",
                \"grammar_explanation\": \"値\",
                \"article_jp\": \"値\"
            ";
            
            Log::debug("log",["prompt2"=>$prompt2]);
            $url2 = "https://api.openai.com/v1/chat/completions";

            $response2 = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
            ])->withOptions(['timeout' => 300])->post($url2, [
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
            Log::info('article_jp:', ["article_jp"=>$article_jp]);

            $articleTestGenerate->grammar_explanation = $grammarExplanation;
            $articleTestGenerate->article_jp = $article_jp;
            $articleTestGenerate->save();
        }

        $end_time = microtime(true);
        $time = floor($end_time - $start_time);

        //articleTestGenerateのsave_flagが0のものを取得する
        $articleTestGenerate = ArticleTestGenerate::where('save_flag', 0)->get();
        Log::info('articleTestGenerate:', ["articleTestGenerate"=>$articleTestGenerate]);

        return response()->json([
            'articleTestGenerate' => $articleTestGenerate,
            'message' => 'Article created successfully! response time is ' . $time . ' seconds.'
        ]);
    }

    public function list(){
        $articleTestGenerate = ArticleTestGenerate::where('save_flag', 0)->get();
        Log::info('articleTestGenerate:', ["articleTestGenerate"=>$articleTestGenerate]);
        return response()->json([
            'articleTestGenerate' => $articleTestGenerate,
        ]);
    }

    public function save(){
        //articleTestGenerateのsave_flagが0であるレコードを全て取得
        $articleTestGenerateList = ArticleTestGenerate::where('save_flag', 0)->get();
        Log::info('articleTestGenerateList:', ["articleTestGenerateList"=>$articleTestGenerateList]);

        //Article Tableに全てのデータをsaveする
        foreach($articleTestGenerateList as $articleTestGenerate){
            $article = new Article();
            $article->grammar_id = $articleTestGenerate->grammar_id;
            $article->technology_id = $articleTestGenerate->technology_id;
            $article->article = $articleTestGenerate->article;
            $article->article_jp = $articleTestGenerate->article_jp;
            $article->grammar_explanation = $articleTestGenerate->grammar_explanation;
            $article->word_frequency_average = $articleTestGenerate->word_frequency_average;
            $article->save();
        }
        //登録後、articleTestGenerateのsave_flagを1に変更する処理
        $articleTestGenerate->save_flag = 1;
        $articleTestGenerate->save();
        
        return response()->json([
            'message' => 'Article created successfully!'
        ]);
    }

    public function count(){
        $articleCount = Article::count();
        Log::info('articleCount:', ["articleCount"=>$articleCount]);
        
        return response()->json([
            'articleCount' => $articleCount,
        ]);

    }
}
