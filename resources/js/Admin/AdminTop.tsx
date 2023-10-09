import React, { useState, useEffect } from "react";
import { Route, Link, Routes } from "react-router-dom";
import Article from "./Article";
import Word from "./Word";
import Technology from "./Technology";

const AdminTop: React.FC = () => {
    const [articleCount, setArticleCount] = useState(0);
    const [wordCount, setWordCount] = useState(0);

    useEffect(() => {
        fetch("http://127.0.0.1:8000/api/word/count")
            .then((res) => res.json())
            .then((data) => {
                setWordCount(data.wordCount);
            });
        fetch("http://127.0.0.1:8000/api/article/count")
            .then((res) => res.json())
            .then((data) => {
                setArticleCount(data.articleCount);
            });
    }, []);

    return (
        <div>
            <div>Article Count:{articleCount}</div>
            <div>Word Count:{wordCount}</div>
            <ul>
                <li>
                    <Link to="/admin/article">Confirm Article Test</Link>
                </li>
                <li>
                    <Link to="/admin/word">Confirm Word Test</Link>
                </li>
                <li>
                    <Link to="/admin/technology">Go To Technology Tags</Link>
                </li>
            </ul>

            <Routes>
                <Route path="/article" element={<Article />} />
                <Route path="/word" element={<Word />} />
                <Route path="/technology" element={<Technology />} />
            </Routes>
        </div>
    );
};

export default AdminTop;
