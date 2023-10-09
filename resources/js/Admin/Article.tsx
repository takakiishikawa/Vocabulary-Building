import React, { useEffect, useState } from "react";

interface ArticleDate {
    id: number;
    grammar_id: number;
    technology_id: number;
    article: string;
    article_jp: string;
    grammar_explanation: string;
    word_frequency_average: number;
}

const ArticleList: React.FC = () => {
    const [articleList, setArticleList] = useState<ArticleDate[]>([]);

    useEffect(() => {
        fetch("http://127.0.0.1:8000/api/article/list")
            .then((res) => res.json())
            .then((data) => {
                setArticleList(data.articleTestGenerate);
            });
    }, []);

    const fetchArticleList = () => {
        fetch("http://127.0.0.1:8000/api/article/generate")
            .then((res) => res.json())
            .then((data) => {
                console.log(data);
                if (data.articleTestGenerate) {
                    setArticleList(data.articleTestGenerate);
                }
                if (data.message) {
                    alert(data.message);
                } else if (data.error) {
                    alert(data.error);
                }
            });
    };

    const saveArticleList = () => {
        fetch("http://127.0.0.1:8000/api/article/save", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(articleList),
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.message) {
                    alert(data.message);
                }
            });
    };

    return (
        <div>
            <div>
                <button onClick={fetchArticleList}>
                    Generate articleList In Chat GPT
                </button>
            </div>
            {Object.keys(articleList).length === 0
                ? null
                : articleList.map((articleData: any, index: number) => (
                      <ul key={index}>
                          <li>{articleData.id}</li>
                          <ul>
                              <li>{articleData.grammar_id}</li>
                              <li>{articleData.technology_id}</li>
                              <li>{articleData.article}</li>
                              <li>{articleData.article_jp}</li>
                              <li>{articleData.grammar_explanation}</li>
                              <li>{articleData.word_frequency_average}</li>
                          </ul>
                      </ul>
                  ))}
            <div>
                <button onClick={saveArticleList}>Save To DB</button>
            </div>
        </div>
    );
};

export default ArticleList;
