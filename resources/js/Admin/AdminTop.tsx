import React from "react";
import { Route, Link, Routes } from "react-router-dom";
import Article from "./Article";
import Word from "./Word";

const AdminTop: React.FC = () => {
    return (
        <div>
            <ul>
                <li>
                    <Link to="/admin/article">Article</Link>
                </li>
                <li>
                    <Link to="/admin/word">Word</Link>
                </li>
            </ul>

            <Routes>
                <Route path="/article" element={<Article />} />
                <Route path="/word" element={<Word />} />
            </Routes>
        </div>
    );
};

export default AdminTop;
