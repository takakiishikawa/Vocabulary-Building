import React, { useState } from "react";

const Article: React.FC = () => {
    const [article, setArticle] = useState<any>({});

    const fetchArticle = () => {
        fetch("http://127.0.0.1:8000/api/article")
            .then((res) => res.json())
            .then((data) => {
                setArticle(data);
            });
    };

    const saveArticle = () => {
        fetch("http://127.0.0.1:8000/api/article/save", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(article),
        })
            .then((res) => res.json())
            .then((data) => {
                console.log(data);
            });
    };

    return (
        <div>
            <div>
                <button onClick={fetchArticle}>
                    Generate article In Chat GPT
                </button>
            </div>
            {console.log(article)}
            {Object.keys(article).length === 0 ? null : (
                <div>
                    <div style={{ fontSize: "50px" }}>Word List</div>
                    {article.wordList &&
                        article.wordList.map((word: string, index: number) => (
                            <li key={index}>
                                {index + 1}. {word}
                            </li>
                        ))}
                    <div style={{ fontSize: "50px" }}>Selected Words</div>
                    {article.selectedWords &&
                        article.selectedWords.map(
                            (selectedWord: string, index: number) => (
                                <li key={index}>
                                    {index + 1}. {selectedWord}
                                </li>
                            )
                        )}
                    <div style={{ fontSize: "50px" }}>article</div>
                    {article.article}
                    <div style={{ fontSize: "50px" }}>Grammar Explanation</div>
                    {article.grammarExplanation}
                    <div style={{ fontSize: "50px" }}>Article JP</div>
                    {article.article_jp}
                    <div style={{ fontSize: "50px" }}>Grammar Tag</div>
                    {article.selectedGrammar}
                    <div style={{ fontSize: "50px" }}>Technology Tag</div>
                    {article.selectedTechnology}
                </div>
            )}
            <div>
                <button onClick={saveArticle}>Save To DB</button>
            </div>
        </div>
    );
};

export default Article;
