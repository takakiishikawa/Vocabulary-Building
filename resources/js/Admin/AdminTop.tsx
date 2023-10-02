import React from "react";
import { Route, Link, Routes } from "react-router-dom";
import Article from "./Article";
import Word from "./Word";
import Technology from "./Technology";

const AdminTop: React.FC = () => {
    return (
        <div>
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
